/**
 * CSS checks, including the brand rules from CLAUDE.md:
 * colours only through tokens (no raw hex, rgb() or named colours outside tokens.css),
 * no gradients, no italics, fonts only through the --font-* tokens.
 */
export default {
	rules: {
		// Correctness.
		'block-no-empty': true,
		'color-no-invalid-hex': true,
		'declaration-block-no-duplicate-properties': [ true, { ignore: [ 'consecutive-duplicates-with-different-values' ] } ],
		'font-family-no-missing-generic-family-keyword': true,
		'no-duplicate-selectors': true,
		'property-no-unknown': true,
		'selector-pseudo-class-no-unknown': true,
		'unit-no-unknown': true,

		// Brand: colours only through tokens.
		'color-no-hex': [ true, { message: 'Use a colour token (var(--…)) from tokens.css, not a raw hex value.' } ],
		'color-named': [ 'never', { message: 'Use a colour token, not a named colour.' } ],
		'function-disallowed-list': [
			[ 'rgb', 'rgba', 'hsl', 'hsla', 'hwb', 'lab', 'lch', 'oklab', 'oklch', 'linear-gradient', 'radial-gradient', 'conic-gradient', 'repeating-linear-gradient', 'repeating-radial-gradient', 'repeating-conic-gradient' ],
			{ message: 'Brand rule: colours only through tokens (use color-mix with a token for transparency), no gradients.' },
		],

		// Brand: no italics, fonts only through tokens.
		'declaration-property-value-disallowed-list': [
			{ 'font-style': [ 'italic', 'oblique' ] },
			{ message: 'Brand rule: no italics.' },
		],
		'property-disallowed-list': [ [ 'font' ], { message: 'Set font-family, font-size and font-weight separately so the font tokens stay visible.' } ],
		'declaration-property-value-allowed-list': [
			{ 'font-family': [ '/^var\\(--font-(head|body|label)\\)$/', 'inherit' ] },
			{ message: 'Brand rule: fonts only through var(--font-head), var(--font-body) or var(--font-label).' },
		],
	},
	overrides: [
		{
			// The token file is the one place where raw colours and font names live.
			files: [ '**/tokens.css', '**/fonts.css' ],
			rules: {
				'color-no-hex': null,
				'function-disallowed-list': null,
				'declaration-property-value-allowed-list': null,
			},
		},
	],
};
