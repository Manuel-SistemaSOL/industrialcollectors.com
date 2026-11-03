'use strict';

// Run with Node and Playwright. Optional environment: PLAYWRIGHT_MODULE,
// BROWSER_EXECUTABLE_PATH, IMAGE_TEST_BASE_URL (localhost only).
// Every browser request is fulfilled locally or blocked. No PHP handler, API,
// production image, quote, or order is contacted, and no server is started.

const assert = require('node:assert/strict');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');

function loadPlaywright() {
    if (process.env.PLAYWRIGHT_MODULE) return require(process.env.PLAYWRIGHT_MODULE);
    try {
        return require('playwright');
    } catch (error) {
        if (error.code !== 'MODULE_NOT_FOUND') throw error;
        return require(path.join(os.homedir(), '.cache', 'codex-runtimes', 'codex-primary-runtime',
            'dependencies', 'node', 'node_modules', 'playwright'));
    }
}

const { chromium } = loadPlaywright();

const baseUrl = new URL(process.env.IMAGE_TEST_BASE_URL || 'http://127.0.0.1:8765/');
assert.ok(['localhost', '127.0.0.1', '[::1]'].includes(baseUrl.hostname),
    'IMAGE_TEST_BASE_URL must target localhost, never production.');
assert.ok(['http:', 'https:'].includes(baseUrl.protocol), 'Use an HTTP(S) local fixture URL.');
if (!baseUrl.pathname.endsWith('/')) baseUrl.pathname += '/';

const scriptSource = fs.readFileSync(path.join(__dirname, '..', 'assets', 'js', 'product-image.js'), 'utf8');
const scriptUrl = new URL('assets/js/product-image.js', baseUrl).href;
const fixtureUrl = new URL('__image_fallback_test__.html', baseUrl).href;
const fallbackUrl = new URL('assets/img/ImagenTemporalINDUSTRIALCOLLECTORS.png', baseUrl).href;
const apiBase = 'https://ssl.sol.sistemasolgt.com/brovic/files/articulos/';
const png = Buffer.from(
    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Y9ZQmcAAAAASUVORK5CYII=',
    'base64'
);

async function createFixture(browser, { brokenFallback = false } = {}) {
    const context = await browser.newContext({ serviceWorkers: 'block' });
    const page = await context.newPage();
    const requests = [];
    const unexpected = [];
    const pageErrors = [];
    let recoveringImageAvailable = false;
    page.on('pageerror', error => pageErrors.push(error.message));

    await context.route('**/*', async route => {
        const request = route.request();
        const url = request.url();
        requests.push({ method: request.method(), url });
        if (request.method() !== 'GET') {
            unexpected.push(request.method() + ' ' + new URL(url).pathname);
            await route.abort('blockedbyclient');
            return;
        }
        if (url === fixtureUrl) {
            await route.fulfill({
                contentType: 'text/html',
                body: '<!doctype html><html><head><meta charset="utf-8">'
                    + '<title>Isolated product image test</title></head><body>'
                    + '<script>window.fixtureImageErrors=[];window.addEventListener("error",function(e){'
                    + 'if(e.target instanceof HTMLImageElement)window.fixtureImageErrors.push(e.target.id);},true);</script>'
                    + '<script src="' + scriptUrl + '"></script></body></html>'
            });
            return;
        }
        if (url === scriptUrl) {
            await route.fulfill({ contentType: 'application/javascript', body: scriptSource });
            return;
        }
        if (request.resourceType() === 'image') {
            if ((url === fallbackUrl && brokenFallback)
                || /(?:broken-404|local-missing|unrelated-missing)\.png$/.test(url)
                || (url === apiBase + 'recovering.png' && !recoveringImageAvailable)) {
                await route.fulfill({ status: 404, contentType: 'text/plain', body: 'Image unavailable in fixture' });
                return;
            }
            if (/broken-decode\.png$/.test(url)) {
                await route.fulfill({ status: 200, contentType: 'image/png', body: 'Invalid PNG fixture bytes' });
                return;
            }
            await route.fulfill({ contentType: 'image/png', body: png });
            return;
        }
        unexpected.push(request.method() + ' ' + new URL(url).pathname);
        await route.abort('blockedbyclient');
    });

    await page.goto(fixtureUrl, { waitUntil: 'load' });
    await page.waitForFunction(() => typeof window.BM_resolveProductImage === 'function');
    return {
        context, page, requests, unexpected, pageErrors,
        makeRecoveringImageAvailable() { recoveringImageAvailable = true; }
    };
}

async function appendImage(page, id, source, { api = true, marker = api } = {}) {
    await page.evaluate(({ id, source, api, marker }) => {
        const image = document.createElement('img');
        image.id = id;
        image.alt = 'Isolated test image';
        if (marker) image.setAttribute('data-bm-api-image', 'true');
        document.body.appendChild(image);
        image.src = api ? window.BM_resolveProductImage(source) : source;
    }, { id, source, api, marker });
}

async function imageState(page, id) {
    return page.evaluate(id => {
        const image = document.getElementById(id);
        return {
            src: image.src,
            rawSrc: image.getAttribute('src'),
            fallback: image.classList.contains('bm-product-image-fallback'),
            complete: image.complete,
            naturalWidth: image.naturalWidth,
            errors: window.fixtureImageErrors.filter(value => value === id).length
        };
    }, id);
}

async function waitForImage(page, id, expectedSource, { fallback = false, loaded = true } = {}) {
    try {
        await page.waitForFunction(({ id, expectedSource, fallback, loaded }) => {
            const image = document.getElementById(id);
            return image && image.src === expectedSource && image.complete
                && (!loaded || image.naturalWidth > 0)
                && image.classList.contains('bm-product-image-fallback') === fallback;
        }, { id, expectedSource, fallback, loaded }, { timeout: 5000 });
    } catch (error) {
        error.message += '\nFixture image ' + id + ': ' + JSON.stringify(await imageState(page, id));
        throw error;
    }
    return imageState(page, id);
}

async function testValuesAndRecovery(browser) {
    const fixture = await createFixture(browser);
    const { page } = fixture;
    try {
        const normalized = await page.evaluate(() => {
            const invalid = ['', null, undefined, false, true, {}, [], 0, '0', 404, '404', '404.png', 'null.jpg', 'undefined'];
            const snapshot = Object.freeze({
                nombre: 'Unchanged API product',
                imagen: 'broken-404.png',
                foto: null,
                precio_venta: '85.50'
            });
            const before = JSON.stringify(snapshot);
            const candidates = Object.freeze([null, '0', 'acero galvanizado.png']);
            const candidatesBefore = JSON.stringify(candidates);
            const resolved = invalid.map(value => window.BM_resolveProductImage(value));
            const first = window.BM_resolveFirstProductImage(candidates);
            window.BM_resolveProductImage(snapshot.imagen);
            return {
                fallback: window.BM_PRODUCT_IMAGE_FALLBACK,
                resolved,
                first,
                productUnchanged: before === JSON.stringify(snapshot),
                candidatesUnchanged: candidatesBefore === JSON.stringify(candidates),
                relative: window.BM_resolveProductImage('acero galvanizado.png'),
                absolute: window.BM_resolveProductImage('https://ssl.sol.sistemasolgt.com/brovic/files/articulos/original.png')
            };
        });
        assert.ok(normalized.resolved.every(value => value === normalized.fallback),
            'Missing values and API placeholder filenames must resolve to the temporary image.');
        assert.equal(normalized.relative, apiBase + 'acero%20galvanizado.png');
        assert.equal(normalized.absolute, apiBase + 'original.png');
        assert.equal(normalized.first, apiBase + 'acero%20galvanizado.png');
        assert.equal(normalized.productUnchanged, true, 'Resolving an image must not mutate its API product.');
        assert.equal(normalized.candidatesUnchanged, true, 'Resolving candidates must not mutate the API image list.');

        await appendImage(page, 'missing-api-image', 'null.jpg');
        await waitForImage(page, 'missing-api-image', fallbackUrl, { fallback: true });
        await appendImage(page, 'valid-relative', 'original.png');
        await waitForImage(page, 'valid-relative', apiBase + 'original.png');
        await appendImage(page, 'valid-absolute', apiBase + 'original-absolute.png');
        await waitForImage(page, 'valid-absolute', apiBase + 'original-absolute.png');

        await appendImage(page, 'broken-api-image', 'broken-404.png');
        await waitForImage(page, 'broken-api-image', fallbackUrl, { fallback: true });
        assert.equal(await page.evaluate(() => window.BM_getProductImageSource(document.getElementById('broken-api-image'))),
            apiBase + 'broken-404.png', 'The temporary presentation must retain the original API source.');
        await appendImage(page, 'bad-decode-api-image', 'broken-decode.png');
        await waitForImage(page, 'bad-decode-api-image', fallbackUrl, { fallback: true });
        await appendImage(page, 'unmarked-remote-api-image', apiBase + 'broken-404.png', { marker: false });
        await waitForImage(page, 'unmarked-remote-api-image', fallbackUrl, { fallback: true });

        // Reusing the same modal/gallery <img> for a subsequent valid product
        // must restore its API photograph and remove the fallback-only styling.
        await page.evaluate(source => {
            document.getElementById('broken-api-image').src = window.BM_resolveProductImage(source);
        }, 'replacement.png');
        await waitForImage(page, 'broken-api-image', apiBase + 'replacement.png');

        // Failed network requests must not poison the resolver or future API
        // responses, including a photograph that becomes available at the same URL.
        await appendImage(page, 'recovering-old-response', 'recovering.png');
        await waitForImage(page, 'recovering-old-response', fallbackUrl, { fallback: true });
        fixture.makeRecoveringImageAvailable();
        assert.equal(await page.evaluate(() => window.BM_resolveProductImage('recovering.png')),
            apiBase + 'recovering.png', 'A past error must not persist the fallback as the API photograph.');
        await appendImage(page, 'recovering-new-response', 'recovering.png');
        await waitForImage(page, 'recovering-new-response', apiBase + 'recovering.png');

        for (const [id, source] of [
            ['local-logo', 'assets/img/logo-original.png'],
            ['local-branch', 'assets/img/sucursal-original.png'],
            ['local-broken', 'assets/img/local-missing.png'],
            ['unrelated-remote', 'https://unrelated.example.test/unrelated-missing.png']
        ]) {
            await appendImage(page, id, source, { api: false });
            const expected = new URL(source, baseUrl).href;
            await waitForImage(page, id, expected, { loaded: !/missing/.test(source) });
            assert.equal((await imageState(page, id)).fallback, false,
                'Local site imagery and unrelated remote images must retain their original source and styling.');
        }
        assert.deepEqual(fixture.unexpected, [], 'No non-fixture or write request may escape the browser routes.');
        assert.deepEqual(fixture.pageErrors, [], 'The shared image handler must not throw JavaScript errors.');
    } finally {
        await fixture.context.close();
    }
}

async function testBrokenFallbackStops(browser) {
    const fixture = await createFixture(browser, { brokenFallback: true });
    try {
        await appendImage(fixture.page, 'both-images-broken', 'broken-404.png');
        await waitForImage(fixture.page, 'both-images-broken', fallbackUrl, { fallback: true, loaded: false });
        await fixture.page.waitForFunction(() => window.fixtureImageErrors
            .filter(value => value === 'both-images-broken').length >= 2, null, { timeout: 5000 });
        const first = await imageState(fixture.page, 'both-images-broken');
        await fixture.page.waitForTimeout(250);
        const after = await imageState(fixture.page, 'both-images-broken');
        assert.equal(after.errors, first.errors, 'A broken temporary image must not restart an error loop.');
        assert.equal(fixture.requests.filter(request => request.url === fallbackUrl).length, 1,
            'Each failed API image may attempt the temporary image once.');
        assert.equal(after.naturalWidth, 0);
        assert.deepEqual(fixture.unexpected, []);
        assert.deepEqual(fixture.pageErrors, []);
    } finally {
        await fixture.context.close();
    }
}

async function testModalAndCartRefresh(browser) {
    const fixture = await createFixture(browser);
    const { page } = fixture;
    try {
        const main = fs.readFileSync(path.join(__dirname, '..', 'assets', 'js', 'main.js'), 'utf8');
        const start = main.indexOf('(function (window, document)');
        const end = main.indexOf('})(window, document);', start) + '})(window, document);'.length;
        assert.ok(start >= 0 && end > start, 'Use the real modal module from main.js.');
        await page.evaluate(() => {
            document.body.insertAdjacentHTML('beforeend', '<div id="modalProducto">'
                + '<div class="mp-left"><img id="mp-imagen-principal"></div><div id="mp-thumbs"></div>'
                + '<h2 id="mp-titulo"></h2><p id="mp-precio"></p><p id="mp-descripcion"></p>'
                + '<span id="mp-stock"></span><span id="mp-sku"></span><input id="mp-cantidad" value="1"></div>');
        });
        await page.addScriptTag({ content: main.slice(start, end) });
        await page.addScriptTag({ content: fs.readFileSync(path.join(__dirname, '..', 'assets', 'js', 'carrito.js'), 'utf8') });
        await page.evaluate(() => {
            window.Carrito.mostrarNotificacion = () => {};
            window.Carrito.limpiarCarrito();
        });
        await page.waitForFunction(() => !window.Carrito._limpiando);
        const cart = await page.evaluate(() => {
            const item = { idarticulo: 990001, nombre: 'Test API product', precio: 100, imagen: null };
            window.Carrito.agregarProducto(item, 1);
            const missing = window.Carrito.obtenerCarrito()[0].imagen;
            window.Carrito.agregarProducto({ ...item, precio: 500, foto: 'replacement.png' }, 2);
            const updated = { ...window.Carrito.obtenerCarrito()[0] };
            window.Carrito.agregarProducto(item, 1);
            return { missing, updated, afterMissing: window.Carrito.obtenerCarrito()[0].imagen };
        });
        assert.equal(new URL(cart.missing, baseUrl).href, fallbackUrl);
        assert.equal(cart.updated.imagen, apiBase + 'replacement.png');
        assert.equal(cart.updated.precio, 100, 'Refreshing a photo must preserve the existing cart price.');
        assert.equal(cart.updated.cantidad, 3, 'Adding the same SKU must preserve the existing quantity behavior.');
        assert.equal(cart.afterMissing, apiBase + 'replacement.png', 'A later missing photo must not replace a valid cart photo.');

        await page.evaluate(() => window.abrirModalProducto({
            idarticulo: 990002, nombre: 'Test modal', precio: 125, imagenes: ['', '0', null], foto: 'original.png'
        }));
        await waitForImage(page, 'mp-imagen-principal', apiBase + 'original.png');
        await page.evaluate(() => window.abrirModalProducto({
            idarticulo: 990002, nombre: 'Test modal', precio: 125, imagenes: ['broken-404.png', 'replacement.png']
        }));
        await waitForImage(page, 'mp-imagen-principal', fallbackUrl, { fallback: true });
        await page.evaluate(() => window.agregarAlCarritoDesdeModal());
        const saved = await page.evaluate(() => window.Carrito.obtenerCarrito().find(item => item.idarticulo == 990002));
        assert.equal(saved.imagen, apiBase + 'broken-404.png', 'Adding from the modal must preserve the API URL for future retries.');
        await page.evaluate(() => window.mpMostrar(1));
        await waitForImage(page, 'mp-imagen-principal', apiBase + 'replacement.png');
        await page.evaluate(() => window.mpMostrar(0));
        await waitForImage(page, 'mp-imagen-principal', fallbackUrl, { fallback: true });
        await page.evaluate(() => window.abrirModalProducto({ idarticulo: 990003, nombre: 'No photo', precio: 50 }));
        await waitForImage(page, 'mp-imagen-principal', fallbackUrl, { fallback: true });
        assert.equal(await page.evaluate(() => window.BM_getProductImageSource(document.getElementById('mp-imagen-principal'))),
            await page.evaluate(() => window.BM_PRODUCT_IMAGE_FALLBACK), 'A new product with no photo must not reuse the previous product source.');
        assert.deepEqual(fixture.unexpected, []);
        assert.deepEqual(fixture.pageErrors, []);
    } finally {
        await fixture.context.close();
    }
}

async function main() {
    const localChrome = path.join(process.env.ProgramFiles || 'C:/Program Files',
        'Google', 'Chrome', 'Application', 'chrome.exe');
    const executablePath = process.env.BROWSER_EXECUTABLE_PATH
        || (process.platform === 'win32' && fs.existsSync(localChrome) ? localChrome : undefined);
    const browser = await chromium.launch({
        headless: true,
        ...(executablePath ? { executablePath } : {})
    });
    try {
        await testValuesAndRecovery(browser);
        console.log('PASS: API values, real photographs, failures, recovery, immutable data, and preserved local images.');
        await testBrokenFallbackStops(browser);
        console.log('PASS: a failed temporary image stops without repeated requests.');
        await testModalAndCartRefresh(browser);
        console.log('PASS: modal recovery and fresh cart photographs preserve original sources, quantities and prices.');
    } finally {
        await browser.close();
    }
}

main().catch(error => {
    console.error(error);
    process.exitCode = 1;
});
