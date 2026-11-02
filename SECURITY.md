# Security policy

## Reporting a vulnerability

Please do not open a public issue. Report it privately through GitHub's
[private vulnerability reporting](https://github.com/NicollasFrazao/bway/security/advisories/new)
with:

- what an attacker can do (for example: read or change another user's shopping list, sign in as
  someone else, run code on the server);
- the requests or the test that reproduce it;
- the commit you tested.

You can expect an acknowledgement within a week. Fixes are released on `master` and credited in the
advisory unless you prefer otherwise.

## Supported versions

Only the latest commit on `master` is supported. The 2020 code (tag `v1`, branch `legacy/v1`) is
archived as submitted, is not deployed anywhere and receives no fixes; its known flaws are listed in
[`.context/docs/security.md`](.context/docs/security.md).
