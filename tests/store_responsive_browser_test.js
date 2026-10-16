'use strict';

// Run with an already running local PHP server: node tests/store_responsive_browser_test.js
// Optional: STORE_TEST_BASE_URL, PLAYWRIGHT_MODULE, BROWSER_EXECUTABLE_PATH.
// Read-only browser checks: no screenshots, submitted forms, real quotes, or messages.
const assert = require('node:assert/strict');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const base = new URL(process.env.STORE_TEST_BASE_URL || 'http://127.0.0.1:8765/');
assert.ok(['localhost', '127.0.0.1', '[::1]'].includes(base.hostname), 'Use a local server, never production.');
assert.ok(['http:', 'https:'].includes(base.protocol), 'Use an HTTP(S) local server.');
if (!base.pathname.endsWith('/')) base.pathname += '/';

const widths = [320, 360, 390, 768, 1024, 1440, 1920];
const pages = [
    ['index.php', '.bm-home-hero'],
    ['contact.php', '.contact-redesign-page'],
    ['cart.php', '.cart-area'],
    ['tienda.php', '.shop-area'],
    ['ofertas.php', '.shop-area'],
    ['menosde100.php', '.shop-area'],
    ['nosotros.php', '.about-layout'],
    ['metodos_de_pago.php', '.story-area'],
    ['preguntas_frecuentes.php', '.story-area'],
    ['terminos.php', '.story-area'],
    ['politicas_de_garantia.php', '.story-area'],
    ['catalogo.php', 'main.container'],
    ['derechos.php', '.story-area']
];

async function checkLayout(page, file, width, contentSelector) {
    const result = await page.evaluate((selector) => {
        const visible = (e) => {
            const s = getComputedStyle(e), r = e.getBoundingClientRect();
            return r.width > 0 && r.height > 0 && s.display !== 'none'
                && s.visibility !== 'hidden' && Number(s.opacity) !== 0;
        };
        const ignored = '.owl-stage-outer, .bm-home-featured-products, .bm-home-featured-tabs-wrap, '
            + '.table-responsive, .bm-mobile-menu-panel, .bm-mobile-menu-overlay, '
            + '.contact-hidden-field, [hidden], [aria-hidden="true"]';
        const label = (e) => e.tagName.toLowerCase() + (e.id ? '#' + e.id : '')
            + (typeof e.className === 'string' && e.className.trim() ? '.' + e.className.trim().split(/\s+/).join('.') : '');
        const content = document.querySelector(selector);
        const outside = [...document.body.querySelectorAll('*')]
            .filter((e) => visible(e) && !e.closest(ignored))
            .filter((e) => { const r = e.getBoundingClientRect(); return r.left < -1 || r.right > innerWidth + 1; })
            .slice(0, 8).map((e) => ({ selector: label(e), left: e.getBoundingClientRect().left, right: e.getBoundingClientRect().right }));
        const clipped = content ? [...content.querySelectorAll('h1,h2,h3,h4,p,a,button,label')]
            .filter((e) => visible(e) && !e.closest(ignored))
            .filter((e) => {
                const s = getComputedStyle(e);
                // Product titles may intentionally use ellipsis/line clamp.
                return e.textContent.trim() && !e.closest('.product-name,.arrivals-products-content,.shop-products-content')
                    && ['hidden', 'clip'].includes(s.overflowX) && e.scrollWidth > e.clientWidth + 2;
            }).slice(0, 5).map((e) => label(e)) : [];
        const search = [...document.querySelectorAll('input.bm-search-input')].filter(visible)
            .map((e) => ({ selector: label(e), font: parseFloat(getComputedStyle(e).fontSize) }));
        return {
            documentWidth: Math.max(document.documentElement.scrollWidth, document.body.scrollWidth),
            viewport: innerWidth, outside, clipped, search,
            contentVisible: Boolean(content && visible(content) && content.innerText.trim().length > 10)
        };
    }, contentSelector);
    const name = file + ' @ ' + width + 'px';
    assert.ok(result.contentVisible, name + ': primary content must be visible.');
    assert.ok(result.documentWidth <= result.viewport + 1, name + ': document overflow ' + JSON.stringify(result));
    assert.deepEqual(result.outside, [], name + ': visible elements extend beyond the viewport.');
    assert.deepEqual(result.clipped, [], name + ': text is horizontally clipped.');
    if (width < 768) {
        assert.ok(result.search.length > 0, name + ': mobile search must remain available.');
        for (const input of result.search) assert.ok(input.font >= 16, name + ': mobile search text is under 16px.');
    }
}

async function checkHero(page, width) {
    const hero = await page.evaluate(() => {
        const section = document.querySelector('.bm-home-hero');
        const carousel = section.querySelector('.bm-home-hero-carousel');
        const active = carousel.querySelector('.owl-item.active .bm-hero-slide') || carousel.querySelector('.bm-hero-slide');
        const content = active.querySelector('.bm-hero-content');
        const title = content.querySelector('h1'), paragraph = content.querySelector('p'), cta = content.querySelector('.bm-home-btn');
        const benefits = section.querySelector('.bm-hero-benefits');
        const rect = (e) => { const r = e.getBoundingClientRect(); return { left: r.left, right: r.right, top: r.top, bottom: r.bottom, height: r.height }; };
        return {
            section: rect(section), carousel: rect(carousel), benefits: benefits ? rect(benefits) : null,
            count: benefits ? benefits.querySelectorAll('.bm-hero-benefits-list > li').length : 0,
            title: title ? { text: title.textContent.trim(), font: parseFloat(getComputedStyle(title).fontSize), ...rect(title) } : null,
            paragraph: paragraph ? { font: parseFloat(getComputedStyle(paragraph).fontSize), ...rect(paragraph) } : null,
            cta: cta ? rect(cta) : null,
            media: rect(active.querySelector('.bm-hero-media')), content: rect(content)
        };
    });
    const name = 'hero @ ' + width + 'px';
    assert.ok(hero.benefits && hero.benefits.height > 0, name + ': benefits must remain inside the hero.');
    assert.equal(hero.count, 3, name + ': all three benefits must remain present.');
    assert.ok(Math.abs(hero.benefits.top - hero.carousel.bottom) <= 2, name + ': a gap separates the carousel and benefits.');
    assert.ok(Math.abs(hero.benefits.bottom - hero.section.bottom) <= 2, name + ': benefits escape their container.');
    if (width < 768) {
        assert.ok(hero.title && hero.title.text && hero.title.font >= 24 && hero.title.height > 0, name + ': mobile heading is too small or hidden.');
        if (hero.paragraph) assert.ok(hero.paragraph.font >= 14 && hero.paragraph.height > 0, name + ': mobile description is too small or hidden.');
        assert.ok(hero.cta && hero.cta.height >= 44, name + ': mobile CTA needs a 44px target.');
        assert.ok(hero.content.top >= hero.media.bottom - 1, name + ': mobile copy must sit below the image.');
    }
}

async function checkBranches(page, width) {
    const cards = await page.locator('.contact-branch-panel > .contact-branch-card').evaluateAll((elements) => elements.map((e) => {
        const r = e.getBoundingClientRect();
        const photo = e.querySelector('.contact-branch-photo').getBoundingClientRect();
        const info = e.querySelector('.contact-branch-info').getBoundingClientRect();
        return { left: r.left, right: r.right, top: r.top, bottom: r.bottom, photoRight: photo.right, infoLeft: info.left };
    }));
    assert.equal(cards.length, 2, 'Both branch cards must remain available.');
    if (width >= 768) {
        assert.ok(Math.abs(cards[0].top - cards[1].top) <= 1, 'Branch cards must share a row at ' + width + 'px.');
        assert.ok(cards[1].left >= cards[0].right - 1, 'Branch cards overlap at ' + width + 'px.');
    } else {
        assert.ok(Math.abs(cards[0].left - cards[1].left) <= 1 && cards[1].top >= cards[0].bottom - 1,
            'Branch cards must stack on a phone at ' + width + 'px.');
    }
    for (const card of cards) assert.ok(card.photoRight <= card.infoLeft + 1,
        'The branch photo must stay to the left of the details at ' + width + 'px.');
}

async function checkMenu(page, file) {
    await page.locator('.bm-menu-toggle').click();
    await page.waitForFunction(() => document.body.classList.contains('bm-mobile-menu-open')
        && document.querySelector('.bm-mobile-menu-panel').getBoundingClientRect().left >= -1);
    assert.equal(await page.locator('.bm-menu-toggle').getAttribute('aria-expanded'), 'true', file + ': menu did not open.');
    const panel = await page.locator('.bm-mobile-menu-panel').boundingBox();
    assert.ok(panel && panel.x >= -1 && panel.x + panel.width <= 391, file + ': mobile menu escapes the viewport.');
    await page.locator('.bm-mobile-menu-close').click();
    await page.waitForFunction(() => !document.body.classList.contains('bm-mobile-menu-open'));
    assert.equal(await page.locator('.bm-menu-toggle').getAttribute('aria-expanded'), 'false', file + ': menu did not close.');
}

async function main() {
    const options = { headless: true };
    if (process.env.BROWSER_EXECUTABLE_PATH) options.executablePath = process.env.BROWSER_EXECUTABLE_PATH;
    const browser = await chromium.launch(options);
    const context = await browser.newContext({ viewport: { width: 1440, height: 1000 }, serviceWorkers: 'block' });
    const forbiddenWrites = [], pageErrors = [];
    let views = 0, menus = 0, mockedReads = 0;
    await context.route('**/*', async (route) => {
        const request = route.request(), url = new URL(request.url()), method = request.method();
        const catalogRead = (url.origin === base.origin && /\/api\/shop_products\.php$/.test(url.pathname))
            || (url.hostname === 'ssl.sol.sistemasolgt.com'
                && /^\/induscollesitio\/api\/api_tienda_(ofertas|menosde100)\.php$/.test(url.pathname));
        if (catalogRead && ['GET', 'POST'].includes(method)) {
            mockedReads++;
            await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({
                success: true, data: [], meta: { total: 0, page: 1, per_page: 30, total_pages: 0, from: 0, to: 0 }
            }) });
        } else if (!['GET', 'HEAD', 'OPTIONS'].includes(method)) {
            forbiddenWrites.push(method + ' ' + url.origin + url.pathname);
            await route.abort('blockedbyclient');
        } else if (/wa\.me|whatsapp\.com/i.test(url.hostname) || /\/api\/orders\//.test(url.pathname)
            || /\/assets\/php\/form-process\.php$/.test(url.pathname)) {
            await route.abort('blockedbyclient');
        } else await route.continue();
    });
    const page = await context.newPage();
    page.setDefaultTimeout(60000);
    page.on('pageerror', (error) => pageErrors.push(error.message));
    try {
        for (const [file, content] of pages) {
            await page.setViewportSize({ width: 1440, height: 1000 });
            const response = await page.goto(new URL(file, base).href, { waitUntil: 'load', timeout: 60000 });
            assert.equal(response.status(), 200, file + ': page did not load.');
            await page.evaluate(() => Promise.race([document.fonts.ready, new Promise((resolve) => setTimeout(resolve, 2500))]));
            if (file === 'index.php') await page.evaluate(() => {
                if (window.jQuery) window.jQuery('.bm-home-hero-carousel').trigger('stop.owl.autoplay');
            });
            for (const width of widths) {
                await page.setViewportSize({ width, height: 1000 });
                await page.waitForTimeout(250); // Owl refreshes its item widths after a resize debounce.
                await checkLayout(page, file, width, content);
                if (file === 'index.php') await checkHero(page, width);
                if (file === 'contact.php') await checkBranches(page, width);
                if (width === 390) { await checkMenu(page, file); menus++; }
                views++;
            }
            process.stdout.write('OK: ' + file + ' at ' + widths.join('/') + 'px\n');
        }
        assert.deepEqual(forbiddenWrites, [], 'A write endpoint was attempted and blocked.');
        assert.deepEqual(pageErrors, [], 'A page raised an unhandled JavaScript error.');
        process.stdout.write('PASS: ' + views + ' responsive views, ' + menus + ' mobile menus, '
            + mockedReads + ' mocked catalog reads; no submitted forms or real quotes/messages.\n');
    } finally {
        await context.close();
        await browser.close();
    }
}

main().catch((error) => { process.stderr.write((error.stack || String(error)) + '\n'); process.exitCode = 1; });
