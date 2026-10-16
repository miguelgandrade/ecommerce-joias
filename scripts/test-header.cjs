/** Navegador opcional: requer Playwright disponível no NODE_PATH ou instalado. */
const { chromium } = require('playwright');
const fs = require('node:fs');

(async () => {
	const browser = await chromium.launch({ executablePath: process.env.CHROME_PATH || undefined, args: ['--no-sandbox'] });
	const results = [];
	try {
		for (const width of [320, 390, 640, 641, 768, 1024, 1366]) {
			const page = await browser.newPage({ viewport: { width, height: 844 }, hasTouch: width <= 640 });
			const errors = [];
			page.on('pageerror', error => errors.push(error.message));
			await page.goto('http://localhost:8080/?post_type=product&nc_vitrine=novidades', { waitUntil: 'networkidle' });
			await page.evaluate(() => window.scrollTo(0, 800));
			await page.waitForTimeout(150);
			const layout = await page.evaluate(() => {
				const header = document.querySelector('.nc-header');
				const menu = document.querySelector('.nc-header__menu');
				const brand = document.querySelector('.nc-header__brand').getBoundingClientRect();
				const utilities = document.querySelector('.nc-header__utilities').getBoundingClientRect();
				const rect = header.getBoundingClientRect();
				const hit = (a, b) => a.left < b.right && a.right > b.left && a.top < b.bottom && a.bottom > b.top;
				const links = [...menu.children].map(item => item.getBoundingClientRect());
				return {
					headerHeight: rect.height,
					staysAtTop: Math.abs(rect.top) < 1,
					oneMenuRow: new Set(links.map(item => Math.round(item.top))).size === 1,
					noCollisions: !links.some(item => hit(item, brand) || hit(item, utilities)),
					noPageOverflow: document.documentElement.scrollWidth <= innerWidth + 1,
					opaqueHeader: !getComputedStyle(header).backgroundColor.includes('/'),
					oneMainLandmark: document.querySelectorAll('main,[role="main"]').length === 1,
					metaDescription: !!document.querySelector('meta[name="description"]')?.content,
				};
			});
			const summary = page.locator('.nc-header__category-dropdown > summary');
			if (width <= 640) await summary.tap();
			else { await summary.focus(); await page.keyboard.press('Enter'); }
			layout.categoryClickable = await page.locator('.nc-header__category-submenu a').first().evaluate(link => {
				const rect = link.getBoundingClientRect();
				return !!rect.height && link.contains(document.elementFromPoint(rect.left + 5, rect.top + rect.height / 2));
			});
			await page.keyboard.press('Escape');
			layout.escapeCloses = await page.locator('.nc-header__category-dropdown').evaluate(menu => !menu.open);
			layout.noJavaScriptErrors = errors.length === 0;
			results.push({ width, ...layout });
			if (process.env.NC_SCREENSHOT_DIR) {
				fs.mkdirSync(process.env.NC_SCREENSHOT_DIR, { recursive: true });
				await page.screenshot({ path: `${process.env.NC_SCREENSHOT_DIR}/header-${width}.png` });
			}
			await page.close();
		}
		// Reproduz também o CSS da barra administrativa: móvel no celular,
		// fixa no tablet. Não exige nem expõe uma sessão administrativa real.
		const routes = ['/', '/?post_type=product&nc_vitrine=mais-vendidos', '/?post_type=product&nc_vitrine=novidades', '/?post_type=product', '/?page_id=19', '/?page_id=21', '/?page_id=7', '/?page_id=9'];
		for (const width of [390, 601, 768]) {
			for (const route of routes) {
				const page = await browser.newPage({ viewport: { width, height: 844 }, hasTouch: true });
				await page.goto('http://localhost:8080' + route, { waitUntil: 'networkidle' });
				await page.evaluate(width => {
					document.body.classList.add('admin-bar');
					document.documentElement.style.marginTop = '46px';
					const toolbar = document.createElement('div');
					toolbar.id = 'wpadminbar';
					toolbar.style.cssText = `position:${width <= 600 ? 'absolute' : 'fixed'};top:0;left:0;width:100%;height:46px;z-index:99999;background:#23282d`;
					document.body.prepend(toolbar);
				}, width);
				const initial = await page.locator('.nc-header').evaluate(header => header.getBoundingClientRect().top);
				await page.evaluate(() => window.scrollTo(0, 300));
				await page.waitForTimeout(150);
				const scrolled = await page.evaluate(() => ({ top: document.querySelector('.nc-header').getBoundingClientRect().top, toolbarBottom: document.querySelector('#wpadminbar').getBoundingClientRect().bottom }));
				results.push({ width, route, adminToolbar: true, initialPositionCorrect: Math.abs(initial - 46) < 1, scrollPositionCorrect: Math.abs(scrolled.top - Math.max(0, scrolled.toolbarBottom)) < 1 });
				await page.close();
			}
		}
	} finally {
		await browser.close();
	}
	console.log(JSON.stringify(results, null, 2));
	if (results.some(result => Object.values(result).includes(false))) process.exitCode = 1;
})().catch(error => { console.error(error.message); process.exitCode = 1; });
