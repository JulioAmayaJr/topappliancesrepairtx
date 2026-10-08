// Full-page + above-the-fold screenshots of a page at 390px and 1440px.
// Usage: node scripts/screenshots.mjs [url] [outdir]
import { chromium } from 'playwright';

const url = process.argv[2] || 'http://localhost:8080/';
const out = process.argv[3] || 'docs/screenshots';
const sizes = [
	{ name: 'mobile-390', width: 390, height: 844, mobile: true },
	{ name: 'desktop-1440', width: 1440, height: 900, mobile: false },
];

const browser = await chromium.launch();
for (const s of sizes) {
	const ctx = await browser.newContext({ viewport: { width: s.width, height: s.height }, deviceScaleFactor: s.mobile ? 2 : 1, isMobile: s.mobile, hasTouch: s.mobile });
	const page = await ctx.newPage();
	const errors = [];
	page.on('console', (m) => m.type() === 'error' && errors.push(m.text()));
	page.on('pageerror', (e) => errors.push(e.message));
	await page.goto(url, { waitUntil: 'networkidle' });
	await page.waitForTimeout(1800); // let the idle-loaded motion chunk initialise
	await page.screenshot({ path: `${out}/home-${s.name}-fold.png` });
	// Scroll through so lazy images and scroll reveals settle, then capture the whole page.
	await page.evaluate(async () => {
		for (let y = 0; y < document.body.scrollHeight; y += 300) {
			window.scrollTo(0, y);
			await new Promise((r) => setTimeout(r, 140));
		}
		window.scrollTo(0, 0);
	});
	await page.waitForTimeout(1200);
	await page.screenshot({ path: `${out}/home-${s.name}-full.png`, fullPage: true });
	const overflow = await page.evaluate(() => {
		const w = document.documentElement.clientWidth;
		return [...document.querySelectorAll('body *')]
			.filter((el) => { const r = el.getBoundingClientRect(); return r.width && (r.right > w + 1 || r.left < -1); })
			.filter((el) => !el.closest('.ta-mega, .ta-drawer, .screen-reader-text, .skip-link'))
			.slice(0, 10)
			.map((el) => `${el.tagName.toLowerCase()}.${[...el.classList].join('.')} → ${Math.round(el.getBoundingClientRect().right)}px`);
	});
	console.log(`${s.name}: scrollWidth=${await page.evaluate(() => document.documentElement.scrollWidth)} overflow=${JSON.stringify(overflow)} errors=${JSON.stringify(errors)}`);
	await ctx.close();
}
await browser.close();
