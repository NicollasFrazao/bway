<?php

declare(strict_types=1);

use Tests\Support\ScratchRepository;

it('passes on this repository: the committed surfaces are copies of the index', function (): void {
    command('context:check')
        ->expectsOutputToContain('OK: AGENTS.md, .claude/CLAUDE.md, .github/copilot-instructions.md')
        ->assertSuccessful();
});

it('exports the surfaces and then checks them', function (): void {
    app()->setBasePath(ScratchRepository::withIndex("# Index\n")->root);

    command('context:export')->expectsOutputToContain('Wrote AGENTS.md')->assertSuccessful();
    command('context:check')->assertSuccessful();
});

it('fails and names the surface and line that drifted', function (): void {
    $repository = ScratchRepository::withIndex("# Index\n");
    app()->setBasePath($repository->root);
    command('context:export')->assertSuccessful();
    $repository->write('.claude/CLAUDE.md', "# Edited\n");

    command('context:check')
        ->expectsOutputToContain('.claude/CLAUDE.md is not a copy of .context/docs/README.md, line 1:')
        ->expectsOutputToContain('Run `php artisan context:export`')
        ->assertFailed();
});

it('fails without the index', function (string $command): void {
    $repository = ScratchRepository::withIndex("# Index\n");
    $repository->delete('.context/docs/README.md');
    app()->setBasePath($repository->root);

    command($command)->expectsOutputToContain('.context/docs/README.md is missing')->assertFailed();
})->with(['context:export', 'context:check']);
