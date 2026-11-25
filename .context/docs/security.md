---
type: doc
name: security
description: What v1 got wrong, what v2 guarantees, the checks that guard it, and how to report a vulnerability
category: security
generated: 2026-11-01
status: filled
scaffoldVersion: "2.0.0"
---

# Security

## What v1 got wrong

The 2020 code (tag `v1`) is kept as submitted and is not deployed anywhere. Its known flaws, each
fixed by a phase of the rewrite:

| v1 flaw | Fixed in |
| --- | --- |
| Passwords stored and compared in plain text | B5 (#8): bcrypt through the `PasswordHasher` port, checked in PHP, never in a query |
| Routes take the user id from the URL and never check it against the session | B5 (#8): no route names a user; the owner of a list comes from the session, and store management is behind the `manage-stores` gate |
| No rate limiting on login or sign-up | B5 (#8): 5 failed sign-ins per email and address a minute; sign-up and recovery 6 a minute |
| No password recovery; nothing ends a stolen session | B5 (#8): reset links from the password broker (hashed, one hour); a new password replaces the remember-me token |
| Database credentials hard-coded in the Python service | B2 (#5): the Python service is gone; configuration comes from `.env` |
| 214 Dependabot alerts on the Laravel 7 / Laravel Mix dependencies | B0 (#3): every dependency replaced |

## What v2 guarantees

- Secrets live only in `.env` (git-ignored). `.env.example` holds development values for the local
  Docker stack and nothing else.
- Passwords are hashed by Laravel (bcrypt); the user model hides `password` and `remember_token`
  from serialization.
- CSRF protection on every state-changing web route (Laravel's default middleware).
- Every query goes through Eloquent or the query builder with bindings; no SQL is built from
  strings.
- A user only reads and changes their own list: no route takes a user or list id, and the owner
  comes from the session ([ADR 0006](/docs/adr/0006-accounts-and-authorisation.md)).
- Store management only for administrators: the `admin/` routes require the `manage-stores` gate,
  which reads the account's role from the database on every request.
- A sign-in answers the same, and takes as long, for an unknown email and a wrong password; the
  recovery form answers the same whether or not the email has an account.
- The session id is renewed on sign-in and the session invalidated on sign-out.

## Checks

| Check | Where |
| --- | --- |
| Known vulnerabilities in Composer packages | `audit` CI job (`composer audit`) |
| Known high or critical vulnerabilities in runtime npm packages | `npm-audit` CI job (`--omit=dev`); development packages are covered by Dependabot alerts |
| Static analysis of the JavaScript and the workflows | CodeQL (`.github/workflows/codeql.yml`), on every push, pull request and weekly |
| Dangerous PHP (`eval`, `exit`, debug output) | PHP_CodeSniffer `Generic.PHP.ForbiddenFunctions`, PHPMD design rules |
| Type confusion, loose comparisons | PHPStan max + `phpstan-strict-rules` |
| Dependency updates | Dependabot (Composer, npm, GitHub Actions), weekly |
| Secrets pushed by mistake | GitHub secret scanning and push protection (repository setting) |

CodeQL has no PHP analyser; the PHP code is covered by the static analysis above.

## Reporting a vulnerability

Privately, through GitHub's private vulnerability reporting; see [SECURITY.md](/SECURITY.md).
