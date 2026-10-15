// Validate the JSON-LD on a page: valid JSON, required @types, key fields, no self-serving review markup,
// and only one schema source (Rank Math's must be off).
// Usage: node scripts/jsonld-check.mjs [url] [comma-separated required @types]
const url = process.argv[2] || 'http://localhost:8080/';
const html = await (await fetch(url)).text();
const blocks = [...html.matchAll(/<script[^>]*type="application\/ld\+json"[^>]*>([\s\S]*?)<\/script>/g)];
let ok = true;
const fail = (m) => { ok = false; console.log('  ✗ ' + m); };
const pass = (m) => console.log('  ✓ ' + m);

console.log(`JSON-LD check: ${url}`);
blocks.length === 1 ? pass('exactly one JSON-LD block (Rank Math schema disabled)') : fail(`${blocks.length} JSON-LD blocks found (expected 1)`);

const nodes = [];
for (const [, raw] of blocks) {
	try {
		const data = JSON.parse(raw);
		pass('valid JSON');
		nodes.push(...(data['@graph'] || [data]));
	} catch (e) {
		fail('invalid JSON: ' + e.message);
	}
}
const byType = (t) => nodes.find((n) => [].concat(n['@type']).includes(t));

const required = process.argv[3] ? process.argv[3].split(',') : ['HomeAndConstructionBusiness', 'WebSite', 'BreadcrumbList', 'FAQPage'];
for (const t of required) {
	byType(t) ? pass(`@type ${t}`) : fail(`missing @type ${t}`);
}
const biz = byType('HomeAndConstructionBusiness') || {};
for (const k of ['name', 'telephone', 'address', 'geo', 'openingHoursSpecification', 'areaServed', 'sameAs', 'logo', 'image', 'hasOfferCatalog', 'url']) {
	biz[k] ? pass(`business.${k}`) : fail(`business.${k} missing`);
}
const service = byType('Service');
if (service) {
	service.provider?.['@id'] === biz['@id'] ? pass(`Service "${service.name}": provider = business, ${service.areaServed?.length || 0} cities, serviceType "${service.serviceType}"`) : fail('Service.provider is not the business');
}
const crumbs = byType('BreadcrumbList');
if (crumbs) pass('breadcrumb: ' + crumbs.itemListElement.map((i) => i.name).join(' › '));
if (biz.address) {
	const a = biz.address;
	['streetAddress', 'addressLocality', 'addressRegion', 'postalCode', 'addressCountry'].every((k) => a[k]) ? pass(`address: ${a.streetAddress}, ${a.addressLocality}, ${a.addressRegion} ${a.postalCode}`) : fail('address incomplete');
}
if (biz.hasOfferCatalog) pass(`offer catalog: ${biz.hasOfferCatalog.itemListElement.length} services`);
if (biz.areaServed) pass(`areaServed: ${biz.areaServed.length} cities`);
const faq = byType('FAQPage');
if (faq) {
	const empty = faq.mainEntity.filter((q) => !q.name || !q.acceptedAnswer?.text);
	empty.length ? fail(`${empty.length} FAQ entries without question/answer`) : pass(`FAQ: ${faq.mainEntity.length} questions, all answered`);
}
const text = JSON.stringify(nodes);
/aggregateRating|"Review"|"reviewRating"/.test(text) ? fail('contains aggregateRating/Review markup') : pass('no aggregateRating / Review markup');

// NAP consistency: the phone in schema must match the phone printed on the page.
const phone = (biz.telephone || '').replace(/\D/g, '').slice(-10);
const telLinks = [...html.matchAll(/href="tel:\+?(\d+)"/g)].map((m) => m[1].slice(-10));
telLinks.length && telLinks.every((t) => t === phone) ? pass(`NAP phone identical in ${telLinks.length} tel: links and schema`) : fail('phone mismatch between page and schema');

process.exit(ok ? 0 : 1);
