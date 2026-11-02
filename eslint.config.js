// ESLint for the JavaScript of the repository: the Vite entry points under resources/js and the
// tooling configs. The Sonar rules run here, inside the linter, at error level.
import js from '@eslint/js';
import sonarjs from 'eslint-plugin-sonarjs';
import globals from 'globals';

export default [
  {
    ignores: ['vendor/', 'node_modules/', 'public/', 'storage/', 'bootstrap/cache/', 'coverage/'],
  },
  js.configs.recommended,
  sonarjs.configs.recommended,
  {
    languageOptions: {
      ecmaVersion: 'latest',
      sourceType: 'module',
      globals: { ...globals.browser },
    },
    linterOptions: {
      reportUnusedDisableDirectives: 'error',
    },
    rules: {
      'sonarjs/cognitive-complexity': ['error', 15],
      'max-depth': ['error', 3],
      'max-params': ['error', 4],
      'no-unused-vars': ['error', { args: 'all', caughtErrors: 'all' }],
      curly: ['error', 'all'],
      eqeqeq: ['error', 'always'],
    },
  },
  {
    files: ['*.config.js'],
    languageOptions: {
      globals: { ...globals.node },
    },
  },
];
