<?php

declare(strict_types=1);

namespace Bway\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * A deferred-work marker names the issue that tracks it: `TODO(#12): ...`, `FIXME(#12): ...`.
 *
 * Deferred work without an issue is invisible to planning; the issue number makes it findable
 * and lets the comment go when the issue closes.
 */
final class TodoWithIssueSniff implements Sniff
{
    private const string UNTRACKED_MARKER = '/\b(TODO|FIXME)\b(?!\(#\d+\))/';

    /**
     * Comment tokens this sniff reads.
     *
     * @return list<int|string>
     */
    public function register(): array
    {
        return [T_COMMENT, T_DOC_COMMENT_STRING, T_DOC_COMMENT_TAG];
    }

    /**
     * Reports a marker that is not followed by `(#<issue number>)`.
     *
     * The `Sniff` interface declares `$stackPtr` without a type, and PHP does not let an
     * implementation narrow it, so the type stays in the doc comment.
     *
     * @param  int  $stackPtr  Position of the comment token.
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $content = $phpcsFile->getTokens()[$stackPtr]['content'];

        if (preg_match(self::UNTRACKED_MARKER, $content, $matches) !== 1) {
            return;
        }

        $phpcsFile->addError('Write %s(#<issue>): deferred work needs an issue number.', $stackPtr, 'MissingIssue', [$matches[1]]);
    }
}
