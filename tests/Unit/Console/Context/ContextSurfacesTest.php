<?php

declare(strict_types=1);

use App\Console\Context\ContextSurfaces;
use App\Console\Context\SourceMissing;
use App\Console\Context\SurfaceDifference;
use Tests\Support\ScratchRepository;

const INDEX = "# Index\n\nRead the conventions.\n";

it('writes every surface as a byte copy of the index, creating directories', function (): void {
    $repository = ScratchRepository::withIndex(INDEX);

    $repository->surfaces()->export();

    foreach (ContextSurfaces::SURFACES as $surface) {
        expect($repository->read($surface))->toBe(INDEX);
    }
});

it('finds no differences right after an export', function (): void {
    $surfaces = ScratchRepository::withIndex(INDEX)->surfaces();

    $surfaces->export();

    expect($surfaces->differences())->toBe([]);
});

it('reports a surface edited by hand and a missing one', function (): void {
    $repository = ScratchRepository::withIndex(INDEX);
    $repository->surfaces()->export();
    $repository->write('AGENTS.md', "# Index\n\nIgnore the conventions.\n");
    $repository->delete('.github/copilot-instructions.md');

    expect($repository->surfaces()->differences())->toEqual([
        new SurfaceDifference('AGENTS.md', 3, 'Read the conventions.', 'Ignore the conventions.'),
        new SurfaceDifference('.github/copilot-instructions.md', 1, '# Index', SurfaceDifference::MISSING_FILE),
    ]);
});

it('refuses to export without the index', function (): void {
    $repository = ScratchRepository::withIndex(INDEX);
    $repository->delete(ContextSurfaces::SOURCE);

    expect(static fn () => $repository->surfaces()->export())
        ->toThrow(SourceMissing::class, ContextSurfaces::SOURCE.' is missing');
});

it('refuses to check without the index', function (): void {
    $repository = ScratchRepository::withIndex(INDEX);
    $repository->delete(ContextSurfaces::SOURCE);

    expect(static fn (): array => $repository->surfaces()->differences())
        ->toThrow(SourceMissing::class, ContextSurfaces::SOURCE.' is missing');
});
