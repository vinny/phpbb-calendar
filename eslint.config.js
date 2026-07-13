module.exports = [
	{
		ignores: [
			"styles/all/template/assets/**",
			"vendor/**",
			"node_modules/**"
		]
	},
	{
		files: ["styles/all/template/*.js"],
		languageOptions: {
			ecmaVersion: 2021,
			sourceType: "script",
			globals: {
				window: "readonly",
				document: "readonly",
				console: "readonly",
				setTimeout: "readonly",
				clearTimeout: "readonly",
				navigator: "readonly",
				encodeURIComponent: "readonly",
				Array: "readonly",
				Error: "readonly"
			}
		},
		rules: {
			"semi": ["error", "always"],
			"quotes": ["error", "single"],
			"no-unused-vars": ["warn", { "vars": "all", "args": "after-used", "ignoreRestSiblings": false }],
			"no-undef": "error",
			"eqeqeq": ["error", "always"],
			"curly": "error",
			"no-console": "warn",
		}
	}
];
