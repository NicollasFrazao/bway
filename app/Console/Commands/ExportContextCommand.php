<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Context\ContextSurfaces;
use App\Console\Context\SourceMissing;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

/**
 * Writes the agent surfaces as copies of `.context/docs/README.md` (`php artisan context:export`).
 */
final class ExportContextCommand extends Command
{
    /**
     * The command name, as typed after `php artisan`.
     */
    protected $signature = 'context:export';

    /**
     * The line `php artisan list` shows.
     */
    protected $description = 'Write AGENTS.md, .claude/CLAUDE.md and .github/copilot-instructions.md from .context/docs/README.md';

    /**
     * Copies the index over every surface.
     */
    public function handle(Filesystem $files): int
    {
        try {
            new ContextSurfaces($files, $this->laravel->basePath())->export();
        } catch (SourceMissing $error) {
            $this->error($error->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf('Wrote %s from %s.', implode(', ', ContextSurfaces::SURFACES), ContextSurfaces::SOURCE));

        return self::SUCCESS;
    }
}
