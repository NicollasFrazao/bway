---
type: skill
name: Test Generation
description: Add Pest tests for a new class, route or bug fix in this repository. Use when writing tests for new functionality, adding regression tests, or raising coverage
skillSlug: test-generation
phases: [E, V]
generated: 2026-11-01
status: filled
scaffoldVersion: "2.0.0"
---

# Test generation

1. Decide the suite: a class from `app/Domain` or `app/Application` → `tests/Unit/` mirroring its
   path; a route, command or repository → `tests/Feature/`.
2. List the behaviours from the issue's checklist and the class's doc comment (`@throws` lines are
   behaviours too). One `it()` per behaviour.
3. For a bug fix, write the failing test first and keep it as the regression test.
4. Use datasets for the same behaviour over many inputs (grid shapes, product names).
5. Run `make coverage`; then `make analyse` and `make lint`, because tests are analysed at PHPStan
   max and sniffed like the rest of the code.

See [agents/test-writer.md](../../agents/test-writer.md) for the patterns and helpers.
