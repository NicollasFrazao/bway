<?php

declare(strict_types=1);

namespace App\Console\Context;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;

/**
 * Keeps the agent surfaces byte-for-byte equal to the context index.
 *
 * The index (`.context/docs/README.md`) tells an agent to read the conventions before editing; the
 * rules themselves are not copied, so copying the file is the whole generation step and there is a
 * single place to change them.
 */
final readonly class ContextSurfaces
{
    /**
     * The file every surface is a copy of, relative to the repository root.
     */
    public const string SOURCE = '.context/docs/README.md';

    /**
     * Where each coding agent looks for its instructions, relative to the repository root.
     */
    public const array SURFACES = [
        'AGENTS.md',
        '.claude/CLAUDE.md',
        '.github/copilot-instructions.md',
    ];

    /**
     * Binds the surfaces to a repository root.
     *
     * @param  string  $root  Absolute path of the repository root.
     */
    public function __construct(
        private Filesystem $files,
        private string $root,
    ) {}

    /**
     * Copies the index over every surface, creating missing directories.
     *
     * @throws SourceMissing when the index does not exist.
     */
    public function export(): void
    {
        $source = $this->source();

        foreach (self::SURFACES as $surface) {
            $path = $this->path($surface);

            // The directory may be gone: deleting it is how someone recovers from a surface broken by hand.
            $this->files->ensureDirectoryExists(dirname($path));
            $this->files->put($path, $source);
        }
    }

    /**
     * Compares every surface with the index and returns the first differing line of each one.
     *
     * @return list<SurfaceDifference> Empty when every surface is an exact copy.
     *
     * @throws SourceMissing when the index does not exist.
     */
    public function differences(): array
    {
        $source = $this->source();
        $differences = [];

        foreach (self::SURFACES as $surface) {
            $difference = SurfaceDifference::between($surface, $source, $this->read($surface));

            if (! $difference instanceof SurfaceDifference) {
                continue;
            }

            $differences[] = $difference;
        }

        return $differences;
    }

    /**
     * Reads the index.
     *
     * @throws SourceMissing when the index does not exist.
     */
    private function source(): string
    {
        try {
            return $this->files->get($this->path(self::SOURCE));
        } catch (FileNotFoundException) {
            throw SourceMissing::at(self::SOURCE);
        }
    }

    /**
     * Reads a surface; null when it does not exist.
     */
    private function read(string $surface): ?string
    {
        $path = $this->path($surface);

        return $this->files->exists($path) ? $this->files->get($path) : null;
    }

    /**
     * Resolves a repository-relative path.
     */
    private function path(string $relative): string
    {
        return $this->root.DIRECTORY_SEPARATOR.$relative;
    }
}
