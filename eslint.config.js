import js from '@eslint/js';
import globals from 'globals';

export default [
	{
		ignores: [
			'node_modules/**',
			'vendor/**',
			'design/**',
			'prototype/**',
			'private/**',
			'playwright-report/**',
			'test-results/**',
		],
	},
	js.configs.recommended,
	{
		files: [ 'wp-content/**/*.js' ],
		languageOptions: {
			ecmaVersion: 2024,
			sourceType: 'module',
			globals: { ...globals.browser },
		},
	},
	{
		files: [ 'tests/**/*.js', '*.config.js' ],
		languageOptions: {
			ecmaVersion: 2024,
			sourceType: 'module',
			// Tests are Node code, but page.evaluate() callbacks run in the browser.
			globals: { ...globals.node, ...globals.browser },
		},
	},
];
