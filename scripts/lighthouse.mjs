// Lighthouse (mobile + desktop) against the local site using Playwright's Chrome for Testing.
// Usage: node scripts/lighthouse.mjs [url]   → lighthouse/<form>.json|html + a score summary
import { execFileSync } from 'node:child_process';
import { readFileSync, readdirSync, existsSync } from 'node:fs';
import { homedir } from 'node:os';
import { join } from 'node:path';

const url = process.argv[2] || 'http://localhost:8080/';
const cache = join(homedir(), 'Library/Caches/ms-playwright');
const chromeDir = readdirSync(cache).filter((d) => /^chromium-\d+$/.test(d)).sort().pop();
const chrome = process.env.CHROME_PATH || join(cache, chromeDir, 'chrome-mac-arm64/Google Chrome for Testing.app/Contents/MacOS/Google Chrome for Testing');
if (!existsSync(chrome)) throw new Error(`Chrome not found: ${chrome}`);

const runs = [
	{ name: 'mobile', flags: [] },
	{ name: 'desktop', flags: ['--preset=desktop'] },
];
for (const r of runs) {
	execFileSync('npx', ['--yes', 'lighthouse@12', url, ...r.flags, '--quiet', '--output=json', '--output=html', `--output-path=lighthouse/${r.name}`, '--chrome-flags=--headless=new --no-sandbox'], {
		stdio: 'inherit',
		env: { ...process.env, CHROME_PATH: chrome },
	});
	const lhr = JSON.parse(readFileSync(`lighthouse/${r.name}.report.json`, 'utf8'));
	const cat = Object.fromEntries(Object.entries(lhr.categories).map(([k, v]) => [k, Math.round(v.score * 100)]));
	const a = lhr.audits;
	console.log(`\n${r.name.toUpperCase()}  perf ${cat.performance} · a11y ${cat.accessibility} · best ${cat['best-practices']} · seo ${cat.seo}`);
	console.log(`  LCP ${a['largest-contentful-paint'].displayValue} · CLS ${a['cumulative-layout-shift'].displayValue} · TBT ${a['total-blocking-time'].displayValue} · FCP ${a['first-contentful-paint'].displayValue} · SI ${a['speed-index'].displayValue}`);
	const failing = Object.values(a).filter((x) => x.score !== null && x.score < 0.9 && x.scoreDisplayMode !== 'informative' && x.scoreDisplayMode !== 'manual' && x.scoreDisplayMode !== 'notApplicable');
	for (const f of failing) console.log(`  ✗ ${f.id}: ${f.title}${f.displayValue ? ' — ' + f.displayValue : ''}`);
}
