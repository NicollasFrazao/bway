<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

/*
 * Run as a dry run in CI: any change Rector would make fails the build. `composer fix` applies them.
 * The sets are the upgrade path (PHP 8.4) plus the ones that remove dead code and tighten types.
 */
return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/bootstrap/app.php',
        __DIR__.'/config',
        __DIR__.'/database',
        __DIR__.'/phpcs/Bway',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ])
    ->withCache(__DIR__.'/storage/framework/cache/rector')
    ->withPhpSets(php84: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        privatization: true,
        earlyReturn: true,
    )
    ->withImportNames(importShortClasses: false, removeUnusedImports: true);
