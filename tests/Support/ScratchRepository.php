<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Console\Context\ContextSurfaces;
use Illuminate\Filesystem\Filesystem;

/**
 * A throwaway repository root in the system temp directory, holding a context index.
 *
 * Every instance is deleted by {@see self::deleteAll()}, which tests/Pest.php runs after each test.
 */
final class ScratchRepository
{
    /**
     * The repositories to delete after the current test.
     *
     * @var list<self>
     */
    private static array $created = [];

    /**
     * Wraps an existing directory.
     *
     * @param  string  $root  Absolute path of the directory.
     */
    private function __construct(
        public readonly string $root,
        private readonly Filesystem $files,
    ) {}

    /**
     * Creates a scratch repository whose `.context/docs/README.md` holds the given index.
     */
    public static function withIndex(string $index): self
    {
        $repository = new self(sys_get_temp_dir().'/bway-context-'.bin2hex(random_bytes(6)), new Filesystem);
        $repository->write(ContextSurfaces::SOURCE, $index);
        self::$created[] = $repository;

        return $repository;
    }

    /**
     * Deletes every scratch repository created so far.
     */
    public static function deleteAll(): void
    {
        foreach (self::$created as $repository) {
            $repository->files->deleteDirectory($repository->root);
        }

        self::$created = [];
    }

    /**
     * Writes a file, creating its directory.
     */
    public function write(string $relative, string $contents): void
    {
        $this->files->ensureDirectoryExists(dirname($this->path($relative)));
        $this->files->put($this->path($relative), $contents);
    }

    /**
     * Reads a file.
     */
    public function read(string $relative): string
    {
        return $this->files->get($this->path($relative));
    }

    /**
     * Deletes a file.
     */
    public function delete(string $relative): void
    {
        $this->files->delete($this->path($relative));
    }

    /**
     * The surfaces of this repository.
     */
    public function surfaces(): ContextSurfaces
    {
        return new ContextSurfaces($this->files, $this->root);
    }

    /**
     * Resolves a repository-relative path.
     */
    private function path(string $relative): string
    {
        return $this->root.'/'.$relative;
    }
}
