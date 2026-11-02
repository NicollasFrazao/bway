---
type: agent
name: Code Reviewer
description: Review a diff against the layer rules, the doc-comment and readability rules, and the quality gate
agentType: code-reviewer
phases: [R, V]
generated: 2026-11-01
status: filled
scaffoldVersion: "2.0.0"
---

# Code reviewer

## Before approving

- Every CI job is green (the list is in [development-conventions.md](../docs/development-conventions.md),
  "Quality gate"). The PR's Verification section states results ("18/18 tests, coverage 100%"), not
  "tested".
- No new `@phpstan-ignore`, `phpcs:ignore`, `@SuppressWarnings`, baseline or exclude entry without a
  same-line reason you agree with. No `TODO`.
- The PR closes its issue with an English keyword (`Closes #N`).

## Where bugs hide in this code

1. **Framework in the domain.** `Illuminate\*`, `Carbon`, `app()`, `config()` or an Eloquent model in
   `app/Domain` or `app/Application`. Deptrac catches imports; read for helpers too.
2. **Eloquent leaking.** A repository returning a model, a collection of models or a builder instead
   of domain objects.
3. **Mutable value objects.** A value object that is not `final readonly`, or a "with" method that
   changes `$this`.
4. **Routing off by one.** Grid coordinates are (x, y) with x the column; the v1 Python code indexed
   `[y][x]`. A golden test against the v1 routes is the only proof (B2).
5. **Route length 0 for a missing route.** v1's bug; a missing route must be an explicit case.
6. **Silent drops.** Products not sold in the chosen store must be reported, not filtered away (B3).
7. **Doc comments that repeat the signature.** The summary says what the method does for the caller;
   `@param`/`@return` only add what the type does not say.
8. **An edited migration.** Once merged, a migration is never edited; a change is a new file.

## Tests to ask for

A domain rule: a unit test per invariant and per exception, without Laravel. A route or command: a
feature test of the success and the failure path. Routing: the golden test for store 1.
