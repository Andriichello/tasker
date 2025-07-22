import { defineConfig } from 'orval';

export default defineConfig({
	api: {
		input: './storage/api-docs/api-docs.json',
		output: {
			workspace: './resources/js/api',
			clean: true,
			mode: 'tags',
			target: './services',
			schemas: './models',
			httpClient: 'axios',
		},
		hooks: {
			afterAllFilesWrite: 'prettier --write "./resources/js/api/**/*.ts',
		},
	},
});
