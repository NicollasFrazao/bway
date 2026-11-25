<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

/**
 * The framework's CSRF check with its test bypass turned off, so a test can prove the web routes
 * refuse a form sent without the token.
 */
final class EnforcedRequestForgery extends PreventRequestForgery
{
    /**
     * Never skip the check, even under the test runner.
     */
    protected function runningUnitTests(): bool
    {
        return false;
    }
}
