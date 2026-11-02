---
type: skill
name: Code Review
description: Review a change in this repository against its layer rules, doc-comment rules and gate. Use when reviewing a pull request or a local diff before merge
skillSlug: code-review
phases: [R, V]
generated: 2026-11-01
status: filled
scaffoldVersion: "2.0.0"
---

# Code review

1. Read the issue the PR closes and its checklist; the PR must do what the checklist says and no
   more.
2. Run `make gate` (or read the CI jobs). Any red job ends the review.
3. Read the diff layer by layer, `Domain` first: does each class sit where
   [architecture.md](../../docs/architecture.md) says it belongs?
4. Walk the list in [agents/code-reviewer.md](../../agents/code-reviewer.md), "Where bugs hide".
5. Read the tests before the code they test: each rule of the issue needs a test that fails without
   the change.
6. Check the docs moved with the code: glossary for new terms, architecture for new folders,
   tech-debt for anything deferred, an ADR for a hard-to-reverse decision.
