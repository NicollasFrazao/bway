<?php

declare(strict_types=1);

final class Compliant
{
    public function tracked(): void
    {
        // TODO(#12): tracked work is allowed.
    }

    /**
     * FIXME(#12): a tracked marker in a doc comment is allowed too.
     */
    public function trackedInDocComment(): void {}
}
