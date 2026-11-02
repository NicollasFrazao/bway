<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Context\ContextSurfaces;
use App\Console\Context\SourceMissing;
use App\Console\Context\SurfaceDifference;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

/**
 * Fails when an agent surface is not a byte copy of `.context/docs/README.md` (`php artisan context:check`).
 *
 * A failure means a surface drifted from its source, never that the content is wrong: the fix is
 * always `php artisan context:export`.
 */
final class CheckContextCommand extends Command
{
    /**
     * How much of each differing line is printed.
     */
    private const int PREVIEW_LENGTH = 96;

    /**
     * The command name, as typed after `php artisan`.
     */
    protected $signature = 'context:check';

    /**
     * The line `php artisan list` shows.
     */
    protected $description = 'Check that the agent surfaces are byte copies of .context/docs/README.md';

    /**
     * Compares every surface with the index and reports the first difference of each one.
     */
    public function handle(Filesystem $files): int
    {
        try {
            $differences = new ContextSurfaces($files, $this->laravel->basePath())->differences();
        } catch (SourceMissing $error) {
            $this->error($error->getMessage());

            return self::FAILURE;
        }

        if ($differences === []) {
            $this->info(sprintf('OK: %s are copies of %s.', implode(', ', ContextSurfaces::SURFACES), ContextSurfaces::SOURCE));

            return self::SUCCESS;
        }

        $this->error('The agent surfaces drifted from their source:');
        array_walk($differences, $this->report(...));
        $this->line('Run `php artisan context:export` and commit the result.');

        return self::FAILURE;
    }

    /**
     * Prints one difference.
     */
    private function report(SurfaceDifference $difference): void
    {
        $this->line(sprintf('  - %s is not a copy of %s, line %d:', $difference->surface, ContextSurfaces::SOURCE, $difference->line));
        $this->line(sprintf('      source:  %s', $this->preview($difference->sourceLine)));
        $this->line(sprintf('      surface: %s', $this->preview($difference->surfaceLine)));
    }

    /**
     * Shortens a line for the report.
     */
    private function preview(string $line): string
    {
        return mb_substr(trim($line), 0, self::PREVIEW_LENGTH);
    }
}
