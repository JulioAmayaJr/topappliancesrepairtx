// Compile theme assets: src/css → assets/css (bundled @imports, minified) and src/js → assets/js (ESM + lazy chunk).
import * as esbuild from 'esbuild';

const theme = 'wp-content/themes/top-appliance';
const watch = process.argv.includes('--watch');

const common = {
	bundle: true,
	minify: !watch,
	sourcemap: watch ? 'inline' : false,
	logLevel: 'info',
	legalComments: 'none',
};

const builds = [
	{
		...common,
		entryPoints: { critical: `${theme}/src/css/critical.css`, main: `${theme}/src/css/main.css` },
		outdir: `${theme}/assets/css`,
		target: ['chrome111', 'safari16.4', 'firefox115'],
		external: ['*.woff2'],
	},
	{
		...common,
		entryPoints: { main: `${theme}/src/js/main.js` },
		outdir: `${theme}/assets/js`,
		format: 'esm',
		splitting: true,
		chunkNames: 'chunks/[name]-[hash]',
		target: ['es2020'],
	},
];

if (watch) {
	for (const b of builds) (await esbuild.context(b)).watch();
} else {
	await Promise.all(builds.map((b) => esbuild.build(b)));
}
