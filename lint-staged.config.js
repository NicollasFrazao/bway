// Fast first filter on commit, on the staged files only; the full gate is `make gate`.
// PHP runs inside the app container when it is up (the host may not have PHP 8.4), so paths are
// passed relative to the repository root, which is /app in the container.
const php = (command) => (files) => `scripts/php ${command} ${files.join(' ')}`;

const CONTEXT_FILES = '{.context/docs/README.md,AGENTS.md,.claude/CLAUDE.md,.github/copilot-instructions.md}';

export default {
  '*.php': [php('vendor/bin/pint --test'), php('vendor/bin/phpcs')],
  '*.js': 'eslint --max-warnings 0',
  // The check reads the files from disk, so it takes no file arguments.
  [CONTEXT_FILES]: () => 'scripts/php artisan context:check',
};
