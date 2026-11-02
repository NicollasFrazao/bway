<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

/**
 * Runs the local PHP_CodeSniffer standard on a fixture and returns "line:code" for each error.
 *
 * @return list<string>
 */
function sniffErrors(string $fixture): array
{
    $process = new Process([
        PHP_BINARY,
        'vendor/bin/phpcs',
        '--standard=phpcs/Bway',
        '--report=json',
        '--no-cache',
        "phpcs/tests/fixtures/{$fixture}",
    ], dirname(__DIR__, 2));
    $process->run();

    /**
     * PHP_CodeSniffer's JSON report: the messages of each file it checked.
     *
     * @var array{files: array<string, array{messages: list<array{line: int, source: string}>}>} $report
     */
    $report = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
    $messages = array_merge(...array_column(array_values($report['files']), 'messages'));

    return array_map(
        fn (array $message): string => $message['line'].':'.$message['source'],
        $messages,
    );
}

it('reports a TODO or FIXME without an issue number', function (): void {
    expect(sniffErrors('violations.php'))->toBe([
        '9:Bway.Commenting.TodoWithIssue.MissingIssue',
        '14:Bway.Commenting.TodoWithIssue.MissingIssue',
    ]);
});

it('accepts markers that name their issue, in comments and doc comments', function (): void {
    expect(sniffErrors('compliant.php'))->toBe([]);
});
