'use strict';

// Run against an already running local PHP server:
//   node tests/quote_checkout_browser_test.js
// Optional environment: QUOTE_TEST_BASE_URL, PLAYWRIGHT_MODULE,
// BROWSER_EXECUTABLE_PATH. This test never starts a server or sends a real quote.

const assert = require('node:assert/strict');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');

const baseUrl = new URL(process.env.QUOTE_TEST_BASE_URL || 'http://127.0.0.1:8765/');
assert.ok(['localhost', '127.0.0.1', '[::1]'].includes(baseUrl.hostname),
    'QUOTE_TEST_BASE_URL must target a local server, never production.');
assert.ok(['http:', 'https:'].includes(baseUrl.protocol), 'Use an HTTP(S) local server.');
if (!baseUrl.pathname.endsWith('/')) baseUrl.pathname += '/';

const cartUrl = new URL('cart.php', baseUrl).href;
const product = {
    idarticulo: 990001,
    nombre: 'Perfil metalico de prueba E2E',
    precio: 125.50,
    imagen: 'assets/img/404.png',
    descripcion: 'Producto ficticio exclusivo de la prueba local.'
};
const paymentLabels = {
    transferencia: /transferencia bancaria/i,
    efectivo: /efectivo al recoger/i,
    tarjeta: /tarjeta al recoger/i
};
const successResponse = (method) => ({
    success: true,
    idcotizacion: '900001',
    numero_cotizacion: '900001',
    reference_number: 'IC-E2E-' + method,
    email_sent: false,
    email_status: 'disabled'
});

async function replyJson(route, body, status = 200) {
    await route.fulfill({ status, contentType: 'application/json', body: JSON.stringify(body) });
}

async function createCase(browser, { viewport = { width: 1440, height: 1000 }, responder } = {}) {
    const context = await browser.newContext({ viewport, serviceWorkers: 'block' });
    const quotes = [];
    const forbiddenWrites = [];
    const gatewayRequests = [];
    const routeErrors = [];
    const pageErrors = [];

    // Every mutation is handled here before navigation. No request can reach the
    // real quote handler, a payment provider, WhatsApp, or another write endpoint.
    await context.route('**/*', async (route) => {
        const request = route.request();
        const url = new URL(request.url());
        const safeUrl = url.origin + url.pathname;
        const method = request.method().toUpperCase();
        if (/\/api\/cybersource(?:\/|$)/i.test(url.pathname)
            || /cybersource|neonet|visanet|secureacceptance/i.test(url.hostname)) {
            gatewayRequests.push(safeUrl);
            await route.abort('blockedbyclient');
            return;
        }
        if (/\/api\/orders\/create_quote\.php$/.test(url.pathname)) {
            if (method !== 'POST' || url.origin !== baseUrl.origin) {
                forbiddenWrites.push(method + ' ' + safeUrl);
                await route.abort('blockedbyclient');
                return;
            }
            try {
                const payload = request.postDataJSON();
                quotes.push(payload);
                if (responder) await responder(route, payload, quotes.length);
                else await replyJson(route, successResponse(payload.formData.formaPago));
            } catch (error) {
                routeErrors.push(error.message);
                await route.abort('failed').catch(() => {});
            }
            return;
        }
        if (!['GET', 'HEAD', 'OPTIONS'].includes(method)) {
            forbiddenWrites.push(method + ' ' + safeUrl);
            await route.abort('blockedbyclient');
            return;
        }
        if (url.origin !== baseUrl.origin) {
            await route.abort('blockedbyclient');
            return;
        }
        await route.continue();
    });

    await context.addInitScript(() => {
        window.__quoteTestOpenedUrls = [];
        const originalOpen = window.open;
        window.open = function (url, ...options) {
            window.__quoteTestOpenedUrls.push(String(url || ''));
            return originalOpen.call(window, url, ...options);
        };
    });
    const page = await context.newPage();
    page.on('dialog', (dialog) => dialog.accept());
    page.on('pageerror', (error) => pageErrors.push(error.message));
    page.setDefaultTimeout(10000);
    await page.goto(cartUrl, { waitUntil: 'load' });
    await page.waitForFunction(() => window.Carrito && typeof cargarCarrito === 'function'
        && typeof abrirModalPedido === 'function');

    async function checkSafety() {
        assert.deepEqual(forbiddenWrites, [], 'Unexpected write endpoint; the request was blocked.');
        assert.deepEqual(gatewayRequests, [], 'The storefront attempted to contact a payment gateway.');
        assert.deepEqual(routeErrors, [], 'A mocked quote request could not be handled.');
        assert.deepEqual(pageErrors, [], 'New JavaScript errors occurred in the cart.');
        const opened = await page.evaluate(() => window.__quoteTestOpenedUrls);
        for (const value of opened) {
            assert.match(value, /^https:\/\/wa\.me\/50230888818(?:[/?]|$)/,
                'A popup other than the prepared Industrial Collectors WhatsApp message was opened.');
        }
        for (const openPage of context.pages()) {
            if (openPage === page) continue;
            assert.ok(!/cybersource|neonet|secureacceptance/i.test(openPage.url()),
                'A gateway popup was opened.');
        }
    }
    return { context, page, quotes, checkSafety };
}

async function seedCart(page, products = [{ ...product, quantity: 2 }]) {
    // A fresh context has no expiration timestamp. The storefront's existing
    // startup cleanup briefly sets _limpiando; wait until it finishes so the
    // fixture does not race the real cart's initialization.
    await page.waitForFunction(() => window.Carrito && !window.Carrito._limpiando);
    await page.evaluate((items) => {
        if (window.Carrito.obtenerCarrito().length !== 0) throw new Error('The context is not fresh.');
        for (const item of items) {
            if (!window.Carrito.agregarProducto(item, item.quantity)) throw new Error('Cannot seed the test cart.');
        }
        const stored = window.Carrito.obtenerCarrito();
        if (stored.length !== items.length) throw new Error('The fixture was not retained by the cart.');
        cargarCarrito();
        if (window.Swal) window.Swal.close();
    }, products);
    await page.locator('#cantidad-' + products[0].idarticulo).waitFor({ state: 'visible' });
}

async function prepareForm(page, method) {
    await page.evaluate(() => abrirModalPedido());
    await page.locator('#modalPedido').waitFor({ state: 'visible' });
    await page.locator('#nombreCompleto').fill('Cliente Prueba Local');
    await page.locator('#direccion').fill('Direccion ficticia 123 zona 1');
    await page.locator('#telefono').fill('55555555');
    await page.locator('#correo').fill('cliente-e2e@example.invalid');
    // niceSelect intentionally hides the native select; selectOption still fires
    // the real input/change events and the storefront's summary handlers.
    await page.locator('#formaPago').selectOption(method, { force: true });
    const delivery = await page.locator('#modalidadEntrega').evaluate((element) => ({
        tag: element.tagName, type: element.type, value: element.value
    }));
    assert.deepEqual(delivery, { tag: 'INPUT', type: 'hidden', value: 'recoge_tienda' },
        'Delivery must stay fixed to pickup for all three methods.');
    const methods = await page.locator('#formaPago option').evaluateAll((items) =>
        items.map((item) => item.value).filter(Boolean));
    assert.deepEqual(methods.sort(), Object.keys(paymentLabels).sort(), 'Only the three approved methods are available.');
    const cardFields = await page.locator('input, iframe').evaluateAll((elements) => elements
        .filter((element) => /^cc-/i.test(element.getAttribute('autocomplete') || '')
            || [element.id, element.getAttribute('name')].some((value) =>
                /^(pan|cvv|cvc|cardnumber|numerotarjeta|numerodetarjeta)(input|field|value)?$/i.test(
                    String(value || '').replace(/[^a-z0-9]/gi, '')))
            || /cybersource|neonet|secureacceptance/i.test(element.getAttribute('src') || ''))
        .map((element) => element.id || element.getAttribute('name') || element.tagName));
    assert.deepEqual(cardFields, [], 'No card number, CVV, or hosted payment fields may be present.');
    await page.waitForFunction(() => /251[.,]00/.test(document.getElementById('pedidoTotalModal').textContent));
}

function assertQuotePayload(payload, method) {
    assert.ok(typeof payload.csrf_token === 'string' && payload.csrf_token.trim().length > 0,
        'The quote must include the real page CSRF token.');
    const { formData, apiData } = payload;
    assert.equal(formData.formaPago, method);
    assert.equal(formData.modalidadEntrega, 'recoge_tienda');
    assert.equal(formData.costoEnvio, 0);
    assert.equal(formData.total, 251);
    assert.equal(formData.subtotalNormal, 251);
    assert.equal(formData.carrito.length, 1);
    assert.equal(Number(formData.carrito[0].idarticulo), product.idarticulo);
    assert.equal(formData.carrito[0].cantidad, 2);
    assert.equal(apiData.valor_tarjeta, 0, 'A quote must never claim a card payment was collected.');
    assert.equal(apiData.no_auto_tarjeta, '');
    assert.equal(apiData.tipo_pagoBacVisaNet, '');
    assert.equal(apiData.total_venta, 251);
    assert.equal(apiData.total_ventades, 251);
    assert.match(apiData.forma_pago, paymentLabels[method]);
    assert.match(apiData.comentario_cotizacion, /pendiente/i, 'The SOL comment must describe a pending quote.');
    assert.doesNotMatch(apiData.comentario_cotizacion, /pago aprobado|cobrado|neonet|cybersource/i);
    assert.deepEqual(apiData.datosArticulos.articulos.idarticulo, [product.idarticulo]);
    assert.deepEqual(apiData.datosArticulos.articulos.cantidad, [2]);
    assert.deepEqual(apiData.datosArticulos.articulos.precio_venta, [125.5]);
    assert.deepEqual(apiData.datosArticulos.articulos.subtotal1, [251]);
    assert.deepEqual(apiData.datosArticulos.articulos.subtotaldes1, [251]);
}

async function assertSuccess(page, method) {
    const confirmation = page.locator('#cotizacion-confirmacion');
    await confirmation.waitFor({ state: 'visible' });
    const copy = await confirmation.innerText();
    assert.match(copy, /900001/);
    assert.doesNotMatch(copy, /pago aprobado|pago completado|cobro realizado/i);
    const link = page.locator('#cotizacion-whatsapp');
    await link.waitFor({ state: 'visible' });
    const url = new URL(await link.getAttribute('href'), page.url());
    assert.equal(url.protocol, 'https:');
    assert.equal(url.hostname, 'wa.me');
    assert.equal(url.pathname.replace(/\/$/, ''), '/50230888818');
    assert.match(url.search, /%[0-9A-F]{2}/i, 'The prepared message must be URL encoded.');
    const message = url.searchParams.get('text') || '';
    assert.match(message, /900001/, 'WhatsApp must include the saved quote number.');
    assert.ok(message.includes('IC-E2E-' + method), 'WhatsApp must preserve the server quote reference.');
    assert.ok(message.includes(product.nombre), 'WhatsApp must include the requested product.');
    assert.match(message, paymentLabels[method]);
    assert.match(message, /recog|retiro/i);
    assert.match(message, /251[.,]00/);
    assert.match(message, /pendiente|confirmaci[oó]n|cotizaci[oó]n/i);
    assert.equal(await page.evaluate(() => window.Carrito.obtenerCarrito().length), 0,
        'The cart must clear only after the quote was accepted.');
    assert.ok(!page.url().includes('/api/'), 'Success must remain on the storefront confirmation.');
}

async function waitForRetry(page) {
    await page.waitForFunction(() => {
        const confirm = document.querySelector('.swal2-confirm');
        const submit = document.querySelector('.mp-btn-enviar');
        return (confirm && confirm.getBoundingClientRect().width > 0) || (submit && !submit.disabled);
    });
    const confirm = page.locator('.swal2-confirm');
    if (await confirm.isVisible()) await confirm.click();
    await page.waitForFunction(() => !document.querySelector('.mp-btn-enviar').disabled);
}

async function runCase(browser, name, options, operation) {
    const state = await createCase(browser, options);
    try {
        await operation(state);
        await state.checkSafety();
        process.stdout.write('OK: ' + name + '\n');
    } finally {
        await state.context.close();
    }
}

async function main() {
    const launchOptions = { headless: true };
    if (process.env.BROWSER_EXECUTABLE_PATH) launchOptions.executablePath = process.env.BROWSER_EXECUTABLE_PATH;
    const browser = await chromium.launch(launchOptions);
    try {
        for (const method of Object.keys(paymentLabels)) {
            await runCase(browser, 'quote + WhatsApp: ' + method, {}, async ({ page, quotes }) => {
                await seedCart(page);
                await prepareForm(page, method);
                await page.locator('.mp-btn-enviar').click();
                await assertSuccess(page, method);
                assert.equal(quotes.length, 1);
                assertQuotePayload(quotes[0], method);
            });
        }

        for (const invoice of [
            { option: 'cf', number: 'C/F', apiType: 'NIT' },
            { option: 'nit', number: '1234567-8', apiType: 'NIT' },
            { option: 'dpi', number: '1234567890123', apiType: 'DPI' }
        ]) {
            await runCase(browser, 'invoice document: ' + invoice.option.toUpperCase(), {}, async ({ page, quotes }) => {
                await seedCart(page);
                await prepareForm(page, 'transferencia');
                await page.locator('#necesitaFactura').check();
                await page.locator('#tipoDocumento').selectOption(invoice.option, { force: true });
                if (invoice.option === 'cf') {
                    assert.equal(await page.locator('#numeroDocumento').inputValue(), 'C/F',
                        'The CF change handler must preserve C/F.');
                    assert.equal(await page.locator('#numeroDocumento').evaluate((input) => input.required), false);
                    assert.equal(await page.locator('#numeroDocumento-group').isVisible(), false);
                } else {
                    await page.locator('#numeroDocumento').fill(invoice.number);
                }
                await page.locator('.mp-btn-enviar').click();
                await assertSuccess(page, 'transferencia');
                assert.equal(quotes.length, 1);
                assertQuotePayload(quotes[0], 'transferencia');
                assert.equal(quotes[0].formData.necesitaFactura, true);
                assert.equal(quotes[0].formData.numeroDocumento, invoice.number);
                assert.equal(quotes[0].formData.tipoDocumento, invoice.apiType);
                assert.equal(quotes[0].apiData.nit, invoice.number);
                assert.equal(quotes[0].apiData.tipo_documento_cliente, invoice.apiType);
            });
        }

        await runCase(browser, 'reopening after NIT clears hidden invoice requirements', {}, async ({ page, quotes }) => {
            await seedCart(page);
            await prepareForm(page, 'tarjeta');
            await page.locator('#necesitaFactura').check();
            await page.locator('#tipoDocumento').selectOption('nit', { force: true });
            await page.locator('#numeroDocumento').fill('1234567-8');
            assert.equal(await page.locator('#numeroDocumento').evaluate((input) => input.required), true);
            await page.locator('.mp-btn-cancelar').click();
            await page.locator('#modalPedido').waitFor({ state: 'hidden' });
            await page.evaluate(() => abrirModalPedido());
            await page.locator('#modalPedido').waitFor({ state: 'visible' });
            assert.equal(await page.locator('#necesitaFactura').isChecked(), false);
            assert.equal(await page.locator('#tipoDocumento').inputValue(), '');
            assert.equal(await page.locator('#numeroDocumento').inputValue(), '');
            assert.equal(await page.locator('#tipoDocumento').evaluate((input) => input.required), false);
            assert.equal(await page.locator('#numeroDocumento').evaluate((input) => input.required), false);
            assert.equal(await page.locator('#nit-group').isVisible(), false);
            assert.equal(await page.locator('#numeroDocumento-group').isVisible(), false);
            await prepareForm(page, 'efectivo');
            await page.locator('.mp-btn-enviar').click();
            await assertSuccess(page, 'efectivo');
            assert.equal(quotes.length, 1);
            assertQuotePayload(quotes[0], 'efectivo');
            assert.equal(quotes[0].formData.necesitaFactura, false);
            assert.equal(quotes[0].formData.numeroDocumento, 'C/F');
        });

        await runCase(browser, 'HTTP 422 preserves cart and allows retry', {
            responder: (route, payload, attempt) => attempt === 1
                ? replyJson(route, { success: false, message: 'Cotizacion rechazada por la prueba local.' }, 422)
                : replyJson(route, successResponse(payload.formData.formaPago))
        }, async ({ page, quotes }) => {
            await seedCart(page);
            await prepareForm(page, 'transferencia');
            await page.locator('.mp-btn-enviar').click();
            await waitForRetry(page);
            assert.equal(quotes.length, 1);
            assert.equal(await page.evaluate(() => window.Carrito.obtenerCarrito()[0].cantidad), 2);
            assert.equal(await page.locator('#cotizacion-confirmacion').isVisible(), false);
            assert.equal(await page.locator('#correo').inputValue(), 'cliente-e2e@example.invalid');
            await page.locator('.mp-btn-enviar').click();
            await assertSuccess(page, 'transferencia');
            assert.equal(quotes.length, 2);
            quotes.forEach((payload) => assertQuotePayload(payload, 'transferencia'));
        });

        let releasePending;
        const pending = new Promise((resolve) => { releasePending = resolve; });
        try {
            await runCase(browser, 'double click submits once while pending', {
                responder: async (route, payload) => {
                    await pending;
                    await replyJson(route, successResponse(payload.formData.formaPago));
                }
            }, async ({ page, quotes }) => {
                try {
                    await seedCart(page);
                    await prepareForm(page, 'tarjeta');
                    const submit = page.locator('.mp-btn-enviar');
                    await submit.scrollIntoViewIfNeeded();
                    const box = await submit.boundingBox();
                    assert.ok(box, 'The submit button must be visible.');
                    const requestStarted = page.waitForRequest((request) => request.method() === 'POST'
                        && /\/api\/orders\/create_quote\.php$/.test(new URL(request.url()).pathname));
                    await page.mouse.dblclick(box.x + box.width / 2, box.y + box.height / 2, { delay: 35 });
                    await requestStarted;
                    await page.waitForFunction(() => document.querySelector('.mp-btn-enviar').disabled);
                    assert.match(await submit.innerText(), /envi|registr|proces/i);
                    await page.waitForTimeout(200); // Keep the mocked POST in flight for both clicks.
                    assert.equal(quotes.length, 1, 'Repeated clicks must not create duplicate requests.');
                    assert.equal(await page.evaluate(() => window.Carrito.obtenerCarrito().length), 1);
                } finally {
                    releasePending();
                }
                await assertSuccess(page, 'tarjeta');
                assertQuotePayload(quotes[0], 'tarjeta');
            });
        } finally {
            releasePending();
        }

        let releaseLocked;
        const lockedResponse = new Promise((resolve) => { releaseLocked = resolve; });
        try {
            await runCase(browser, 'pending quote prevents closing, resetting, or editing the cart', {
                responder: async (route, payload) => {
                    await lockedResponse;
                    await replyJson(route, successResponse(payload.formData.formaPago));
                }
            }, async ({ page, quotes }) => {
                try {
                    await seedCart(page);
                    await prepareForm(page, 'efectivo');
                    const requestStarted = page.waitForRequest((request) => request.method() === 'POST'
                        && /\/api\/orders\/create_quote\.php$/.test(new URL(request.url()).pathname));
                    await page.locator('.mp-btn-enviar').click();
                    await requestStarted;
                    await page.waitForFunction(() => document.getElementById('modalPedido').getAttribute('aria-busy') === 'true');
                    assert.equal(await page.locator('#modalPedido .mp-close').isDisabled(), true);
                    assert.equal(await page.locator('.mp-btn-cancelar').isDisabled(), true);
                    await page.locator('#modalPedido .mp-close').evaluate((button) => button.click());
                    await page.locator('.mp-btn-cancelar').evaluate((button) => button.click());
                    await page.locator('#modalPedido').click({ position: { x: 1, y: 1 }, force: true });
                    await page.evaluate((id) => {
                        cerrarModalPedido();
                        abrirModalPedido();
                        cambiarCantidad(String(id), 1);
                        document.getElementById('cantidad-' + id).value = '9';
                        actualizarCantidadInput(String(id), '9');
                        eliminarDelCarrito(String(id));
                    }, product.idarticulo);
                    assert.equal(await page.locator('#modalPedido').isVisible(), true,
                        'Pending requests must keep the quote modal open.');
                    assert.equal(await page.locator('#nombreCompleto').inputValue(), 'Cliente Prueba Local',
                        'Opening again must not reset a request in flight.');
                    const items = await page.evaluate(() => window.Carrito.obtenerCarrito());
                    assert.equal(items.length, 1, 'Pending requests must prevent removing cart items.');
                    assert.equal(items[0].cantidad, 2, 'Pending requests must preserve submitted quantities.');
                    assert.equal(await page.locator('#cantidad-990001').inputValue(), '2');
                    assert.equal(quotes.length, 1);
                } finally {
                    releaseLocked();
                }
                await assertSuccess(page, 'efectivo');
                assertQuotePayload(quotes[0], 'efectivo');
                assert.equal(await page.locator('#modalPedido').getAttribute('aria-busy'), 'false');
                assert.equal(await page.locator('.mp-btn-cancelar').isDisabled(), false);
            });
        } finally {
            releaseLocked();
        }

        await runCase(browser, 'cart quantity and removal controls', {}, async ({ page, quotes }) => {
            const extra = { ...product, idarticulo: 990002, nombre: 'Tubo ficticio de prueba E2E', precio: 20, quantity: 1 };
            await seedCart(page, [{ ...product, quantity: 2 }, extra]);
            const row = page.locator('.cart-table tbody tr').filter({ has: page.locator('#cantidad-990001') });
            await row.locator('.plus-btn').click();
            assert.equal(await page.locator('#cantidad-990001').inputValue(), '3');
            assert.equal(await page.evaluate(() => window.Carrito.obtenerSubtotal()), 396.5);
            await row.locator('.minus-btn').click();
            assert.equal(await page.locator('#cantidad-990001').inputValue(), '2');
            assert.equal(await page.evaluate(() => window.Carrito.obtenerSubtotal()), 271);
            await page.locator('.cart-table tbody tr').filter({ has: page.locator('#cantidad-990002') }).locator('.remove').click();
            assert.equal(await page.locator('.cart-table tbody tr').count(), 1);
            assert.equal(await page.evaluate(() => window.Carrito.obtenerSubtotal()), 251);
            await row.locator('.remove').click();
            assert.equal(await page.evaluate(() => window.Carrito.obtenerCarrito().length), 0);
            await page.locator('.cart-empty-state').waitFor({ state: 'visible' });
            assert.equal(quotes.length, 0, 'Editing the cart must not send a quote.');
        });

        await runCase(browser, '390px form fits without horizontal overflow', {
            viewport: { width: 390, height: 844 }
        }, async ({ page, quotes }) => {
            await seedCart(page);
            await prepareForm(page, 'efectivo');
            const dimensions = await page.evaluate(() => {
                const modal = document.querySelector('#modalPedido .mp-modal');
                const form = document.getElementById('formPedido');
                const rect = modal.getBoundingClientRect();
                return {
                    viewport: window.innerWidth,
                    documentWidth: Math.max(document.documentElement.scrollWidth, document.body.scrollWidth),
                    left: rect.left, right: rect.right,
                    modalScroll: modal.scrollWidth, modalClient: modal.clientWidth,
                    formScroll: form.scrollWidth, formClient: form.clientWidth
                };
            });
            assert.ok(dimensions.documentWidth <= dimensions.viewport + 1, JSON.stringify(dimensions));
            assert.ok(dimensions.left >= -1 && dimensions.right <= dimensions.viewport + 1, JSON.stringify(dimensions));
            assert.ok(dimensions.modalScroll <= dimensions.modalClient + 1, JSON.stringify(dimensions));
            assert.ok(dimensions.formScroll <= dimensions.formClient + 1, JSON.stringify(dimensions));
            await page.locator('.mp-btn-enviar').click();
            await assertSuccess(page, 'efectivo');
            assert.equal(quotes.length, 1);
            assertQuotePayload(quotes[0], 'efectivo');
        });
        process.stdout.write('PASS: all browser cases used mocked requests; no real quotes or WhatsApp messages were sent.\n');
    } finally {
        await browser.close();
    }
}

main().catch((error) => {
    process.stderr.write((error.stack || String(error)) + '\n');
    process.exitCode = 1;
});
