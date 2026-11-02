## Description

<!-- What changes and why. If it fixes a defect, say what happened before. -->

## Issue

<!--
The keyword must be one GitHub recognizes: `Closes`, `Fixes` or `Resolves`
(or `closed` / `fixed` / `resolved`). Anything else is a plain mention, and the
issue stays open after the merge without anyone noticing.

Pasting the issue URL does not link it either. Use the number: `Closes #123`.

Check after opening, because the failure is silent:
  gh pr view <N> --json closingIssuesReferences
-->

Closes #

## Verification

<!-- Results, not "tested": "18/18 tests, coverage 100%". -->

- [ ] `make gate` (Pint, PHP_CodeSniffer, PHPStan, PHPMD, magic numbers, Rector, Deptrac, composer-unused, Pest + coverage, audits, context, ESLint, knip)
- [ ] `make up` serves the app

## Risks and caveats

<!--
What the review must look at closely, and what was left out on purpose.
If it touches a layer boundary, the routing or the visit order, say how the tests prove it.
-->
