<?php

declare(strict_types=1);

use App\Console\Context\SurfaceDifference;

it('finds no difference in an exact copy', function (): void {
    expect(SurfaceDifference::between('AGENTS.md', "a\nb\n", "a\nb\n"))->toBeNull();
});

it('reports the first differing line, 1-based', function (): void {
    $difference = SurfaceDifference::between('AGENTS.md', "a\nb\nc\n", "a\nB\nC\n");

    expect($difference)->toEqual(new SurfaceDifference('AGENTS.md', 2, 'b', 'B'));
});

it('reports a surface that ends early', function (): void {
    $difference = SurfaceDifference::between('AGENTS.md', "a\nb", 'a');

    expect($difference)->toEqual(new SurfaceDifference('AGENTS.md', 2, 'b', SurfaceDifference::END_OF_FILE));
});

it('reports a surface with extra lines', function (): void {
    $difference = SurfaceDifference::between('AGENTS.md', 'a', "a\nextra");

    expect($difference)->toEqual(new SurfaceDifference('AGENTS.md', 2, SurfaceDifference::END_OF_FILE, 'extra'));
});

it('does not normalise line endings', function (): void {
    $difference = SurfaceDifference::between('AGENTS.md', "a\nb\n", "a\r\nb\r\n");

    expect($difference?->line)->toBe(1);
});

it('reports a missing surface on its first line', function (): void {
    $difference = SurfaceDifference::between('AGENTS.md', "# Index\n", null);

    expect($difference)->toEqual(new SurfaceDifference('AGENTS.md', 1, '# Index', SurfaceDifference::MISSING_FILE));
});
