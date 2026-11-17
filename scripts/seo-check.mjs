// Site-wide SEO check: crawls the sitemap (plus any extra paths) and reports
// duplicate titles, titles over 60 characters, meta descriptions outside 140-160,
// pages without exactly one H1, images without an alt attribute, JSON-LD problems
// and noindex pages that are still listed in the sitemap.
//
// Usage: node scripts/seo-check.mjs [base-url] [--extra /path,/path]
//   node scripts/seo-check.mjs http://localhost:8089
//   node scripts/seo-check.mjs https://topappliancesrepairtx.com
const base = (process.argv[2] || 'http://localhost:8089').replace(/\/$/, '');
const extraArg = process.argv.indexOf('--extra');
const extra = extraArg > -1 ? process.argv[extraArg + 1].split(',') : ['/brands/page/2/', '/service-areas/page/2/'];
const TITLE_MAX = 60, DESC_MIN = 140, DESC_MAX = 160;

const get = async (u) => { const r = await fetch(u, { redirect: 'follow' }); return { status: r.status, body: await r.text(), url: r.url }; };
const locs = (xml) => [...xml.matchAll(/<loc>([^<]+)<\/loc>/g)].map((m) => m[1]);

const index = await get(`${base}/sitemap_index.xml`);
if (index.status !== 200) { console.error(`sitemap_index.xml -> ${index.status}`); process.exit(1); }
let urls = [];
for (const sm of locs(index.body)) urls.push(...locs((await get(sm.replace(/^https?:\/\/[^/]+/, base))).body));
urls = [...new Set(urls.map((u) => u.replace(/^https?:\/\/[^/]+/, base)))];
const sitemapUrls = new Set(urls);
urls.push(...extra.map((p) => base + p));

const pages = [];
for (const u of [...new Set(urls)]) {
	const r = await get(u);
	const pick = (re) => { const m = r.body.match(re); return m ? m[1].trim() : ''; };
	const title = pick(/<title>([\s\S]*?)<\/title>/).replace(/\s+/g, ' ');
	pages.push({
		url: u, status: r.status, title,
		tlen: title.length,
		desc: pick(/<meta name="description" content="([^"]*)"/).length,
		robots: pick(/<meta name="robots" content="([^"]*)"/),
		h1: (r.body.match(/<h1[\s>]/g) || []).length,
		noAlt: (r.body.match(/<img\b(?![^>]*\balt=)[^>]*>/g) || []).length,
		ld: [...r.body.matchAll(/<script[^>]*application\/ld\+json[^>]*>([\s\S]*?)<\/script>/g)].map((m) => m[1]),
	});
}

let fails = 0;
const fail = (m) => { fails++; console.log('  ✗ ' + m); };
const ok = (m) => console.log('  ✓ ' + m);
console.log(`SEO check: ${base} — ${pages.length} URLs\n`);

const bad = pages.filter((p) => p.status !== 200);
bad.length ? bad.forEach((p) => fail(`HTTP ${p.status} ${p.url}`)) : ok('every URL returns 200');

const byTitle = {};
for (const p of pages) (byTitle[p.title] ||= []).push(p.url);
const dupes = Object.entries(byTitle).filter(([, v]) => v.length > 1);
dupes.length ? dupes.forEach(([t, v]) => fail(`duplicate title "${t}" on ${v.length}: ${v.join(', ')}`)) : ok('all titles unique');

const long = pages.filter((p) => p.tlen > TITLE_MAX);
long.length ? long.forEach((p) => fail(`title ${p.tlen} chars: ${p.url} — ${p.title}`)) : ok(`all titles ≤ ${TITLE_MAX} chars`);

const desc = pages.filter((p) => !p.robots.includes('noindex') && (p.desc < DESC_MIN || p.desc > DESC_MAX));
desc.length ? desc.forEach((p) => fail(`meta description ${p.desc} chars: ${p.url}`)) : ok(`indexable meta descriptions ${DESC_MIN}-${DESC_MAX} chars`);

const h1 = pages.filter((p) => p.h1 !== 1);
h1.length ? h1.forEach((p) => fail(`${p.h1} H1 on ${p.url}`)) : ok('exactly one H1 per page');

const alt = pages.filter((p) => p.noAlt > 0);
alt.length ? alt.forEach((p) => fail(`${p.noAlt} image(s) with no alt attribute: ${p.url}`)) : ok('every image has an alt attribute');

const noindexInSitemap = pages.filter((p) => p.robots.includes('noindex') && sitemapUrls.has(p.url));
noindexInSitemap.length ? noindexInSitemap.forEach((p) => fail(`noindex page listed in sitemap: ${p.url}`)) : ok('no noindex page is listed in the sitemap');

let schemaBad = 0, bizNames = new Set(), emptyProps = [];
for (const p of pages) {
	if (p.ld.length !== 1) { fail(`${p.ld.length} JSON-LD blocks on ${p.url}`); schemaBad++; continue; }
	let data;
	try { data = JSON.parse(p.ld[0]); } catch (e) { fail(`invalid JSON-LD on ${p.url}: ${e.message}`); schemaBad++; continue; }
	const nodes = data['@graph'] || [data];
	const biz = nodes.find((n) => [].concat(n['@type']).some((t) => /Business$/.test(t)));
	if (!biz) { fail(`no business node on ${p.url}`); schemaBad++; continue; }
	bizNames.add(biz.name);
	if (biz['@id'] !== `${base}/#business`.replace(base, biz['@id'].replace(/\/#business$/, ''))) { /* id checked below */ }
	for (const n of nodes) for (const [k, v] of Object.entries(n)) if (v === '' || (Array.isArray(v) && !v.length)) emptyProps.push(`${p.url} ${n['@type']}.${k}`);
	const svc = nodes.find((n) => n['@type'] === 'Service');
	if (svc && svc.provider?.['@id'] !== biz['@id']) fail(`Service.provider does not point at the business @id: ${p.url}`);
	if (svc && svc.name === biz.name) fail(`Service name equals the business name: ${p.url}`);
}
if (!schemaBad) ok('exactly one valid JSON-LD graph with a business node on every page');
bizNames.size === 1 ? ok(`one business name across the site: "${[...bizNames][0]}"`) : fail(`business name differs across pages: ${[...bizNames].join(' | ')}`);
const ids = new Set(pages.flatMap((p) => { try { const n = JSON.parse(p.ld[0] || '{}')['@graph'] || []; return n.filter((x) => /Business$/.test([].concat(x['@type'])[0] || '')).map((x) => x['@id']); } catch { return []; } }));
ids.size === 1 ? ok(`one business @id across the site: ${[...ids][0]}`) : fail(`business @id differs: ${[...ids].join(' | ')}`);
emptyProps.length ? emptyProps.slice(0, 10).forEach((e) => fail(`empty schema property: ${e}`)) : ok('no empty properties in any schema node');

for (const extraFile of ['/robots.txt', '/llms.txt', '/llms-full.txt']) {
	const r = await get(base + extraFile);
	r.status === 200 ? ok(`${extraFile} -> 200 (${r.body.length} bytes)`) : fail(`${extraFile} -> ${r.status}`);
}

console.log(`\n${fails ? `${fails} problem(s)` : 'All checks passed'}`);
process.exit(fails ? 1 : 0);
