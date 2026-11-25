# 0006. Accounts and authorisation: the account in the domain, the owner from the session, a gate for store management

- Status: accepted
- Date: 2026-11-25
- Issue: [#8](https://github.com/NicollasFrazao/bway/issues/8)

## Context

v1's accounts (`LoginController`, `UsuarioController`, `tb_usuario`) stored the password in plain
text and compared it in the query (`where('cd_senha', $request->senhaUsuario)`), kept the user row in
the session by hand, had no password recovery, no rate limit and no roles. Every list route took the
user id from the URL (`usuario/{usuario}/listaCompras`, `usuario/{usuario}/carrinho`) and never
compared it with the session: anyone signed in could read, change or clear anyone's list.

B4 left the skeleton's `User` model in `Infrastructure` with no domain account (TD-02), `app/Http`
empty (TD-01), and a shopping list that, by [ADR 0002](0002-domain-model.md), has no id of its own:
a user has one list, identified by its owner.

## Decision

- **The account is part of the domain** (`app/Domain/Account/`): `Account` (a `UserId`, a `Name`,
  `Credentials` and a `Role`), with value objects that check what a person types: `EmailAddress`
  (trimmed, lower-cased, at most 254 characters), `PlainPassword` (8 characters to **72 bytes**, the
  part bcrypt reads) and `HashedPassword`. A refused value is `InvalidAccountData` naming its
  `AccountField`, so the edge puts the message on the right form field. `Role` is `Shopper` or
  `Administrator`; `Account::canManageStores()` is the one rule about it.
- **Use cases** in `app/Application/Account/`: `RegisterAccount` (always a shopper), `SignIn`,
  `ResetPassword` and `AuthoriseStoreManagement`; in `app/Application/ShoppingList/`:
  `ShowShoppingList` (by product name, as v1 showed it), `AddProductToList`, `RemoveProductFromList`,
  `ClearShoppingList` and `SearchProducts`; in `app/Application/Store/`: `ListStores`.
- **A user only reaches their own list because no route names a list or a user.** The list routes
  (`/list`, `/list/items`, `/list/items/{product}`) sit behind `auth`, and the controllers take the
  owner from the session only (`SignedInUser::id()`). There is no list id to authorise, so there is
  no list policy: a `user_id` in a form or a query is ignored, and v1's URLs answer 404. The feature
  tests send other users' ids every way v1 accepted them and check that only the session's list
  changes.
- **Store management is a gate, `manage-stores`,** defined in `AccountServiceProvider` and answered by
  `AuthoriseStoreManagement`, which loads the account and asks the domain. The `admin/` routes require
  `can:manage-stores`; the role is read from the database on every request, never from the request or
  the session. A gate rather than a policy class: a policy is per Eloquent model, and a store is a
  domain aggregate, not a model the controllers see. B5 lists the stores (`/admin/stores`); B7 (#10)
  adds the screens that create and map them behind the same gate.
- **A sign-in does not tell which emails have an account:** an unknown, a malformed and a known email
  with a wrong password are the same `InvalidCredentials`, and an unknown email still spends one hash
  check (`PasswordHasher::spendCheck()`), so it takes as long. The recovery form answers the same text
  whether or not the email has an account.
- **The framework stays at the edge.** The account lives in the framework's `users` table, with a new
  `role` column (`EloquentAccountRepository`, `AccountRows`), so the session guard, the remember-me
  cookie and the password broker (`password_reset_tokens`, hashed tokens that expire in an hour) work
  unchanged; once the broker has checked a token, the controller calls `ResetPassword`. A new
  password replaces the remember-me token, so old "keep me signed in" cookies stop working.
- **Rate limits and CSRF:** sign-in allows **5 failed attempts per email and address** a minute
  (`RateLimiter`, cleared by a successful sign-in); sign-up and both recovery forms are limited to 6
  requests a minute. Every state-changing route is in the `web` group, so the framework's CSRF check
  covers it; a test turns off the check's test-runner bypass (`EnforcedRequestForgery`) to prove a
  form without its token gets 419. The session id is renewed on sign-in and the session invalidated
  on sign-out.
- **No email verification.** v1 had none and no feature depends on a confirmed address; the column
  stays, filled by the factory.
- **The pages are plain Blade, unstyled beyond the basics:** sign-up, sign-in, recovery, the list with
  a product search, and the store list. B6 (#9) rebuilds the interface.

## Alternatives considered

- **A starter kit (Breeze, Fortify, Jetstream).** It brings its own controllers, validation and user
  model, with the rules in the framework layer and Tailwind screens B6 would replace. The parts worth
  having (guard, broker, rate limiter) are used directly.
- **Keep v1's URLs and add a policy** (`usuario/{usuario}/...` with `ShoppingListPolicy::view`). It
  works, but every route then carries an id that can only ever be the session's: the URL invites the
  mistake the policy exists to catch. Without it, there is nothing to get wrong.
- **An `is_admin` boolean.** Two roles fit in a boolean, but the enum names them in the domain and in
  the column, and a third role (a store's own staff, if B7 needs one) is a new case, not a migration
  from a boolean.
- **The role in the session or a signed cookie.** Cheaper per request, but a demoted administrator
  would keep the role until signing out.

## Consequences

- `app/Http` has its first controllers (TD-01 narrows to the visit and the mapping, which B6 and B7
  call); the skeleton's user model maps to a domain account (TD-02 closed).
- The demo seed adds an administrator (`admin@example.com`, password `password`). There is no screen
  to grant the role: an account becomes an administrator through the seed or the database (TD-14).
- B6 (#9) builds its screens on the routes and use cases of the list; B7 (#10) puts store creation
  behind `can:manage-stores`.
