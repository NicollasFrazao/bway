# 0007. Web interface: server-rendered Blade pages, the map drawn as SVG from the store's layout and its routes

- Status: accepted
- Date: 2026-11-25
- Issue: [#9](https://github.com/NicollasFrazao/bway/issues/9)

## Context

v1's screens were Blade views with inline AngularJS 1.8 controllers that called JSON routes taking the
user id from the URL. The store was always store 1, and the map was a static image of store 1's floor
plan with nothing drawn on it. B1 to B5 built the use cases (`PlanVisit`, `FindRouteBetweenSectors`,
the list use cases) and the account rules. B5 also added plain list pages behind `auth`.

## Decision

- **Server-rendered Blade, no client framework.**
  - Every screen is a page answered by a controller that calls a use case.
  - Every action is a form (`POST` with the CSRF token), so the flow works without JavaScript.
  - There are no AngularJS controllers, no Livewire and no Alpine to keep in step with the domain.
  - The pages are the shopping list with product search, the store choice, the visit and the map.
- **The visit** (`/stores/{store}/visit`) shows:
  - the sectors in walking order, each with its items;
  - a "Got it" button per item, which acquires it: `AcquireItem` takes it off the list, and the plan is
    computed again;
  - the items the store does not sell.
- **The map** (`/stores/{store}/map`) is an SVG drawn from data the domain already has, with no image
  file:
  - the store's sectors (the entrance in its own colour), its partitions, and one line per walk;
  - each walk comes from `MapVisit`, which plans the visit and asks `FindRouteBetweenSectors` for each
    pair, served from the cache the plan filled;
  - a walk keeps only its corners, so a polyline has a handful of points instead of hundreds of cells;
  - the viewBox is the grid's size and the strokes do not scale, so the same drawing fits a phone and a
    desktop;
  - one walk is highlighted at a time (`?leg=N`, previous and next links): "sector by sector" without
    script;
  - the SVG is an image with a title and a description that lists the stops.
- **Errors:**
  - a store that does not exist is 404;
  - a store whose plan is not mapped, or whose sector cannot be reached, gets a page that says the map
    is not ready, instead of an error.
- **Ids in URLs** match `[1-9][0-9]*` (`Route::pattern`), so a zero or a word is 404 before any
  domain identifier refuses it.
- **Layout and accessibility:**
  - one layout for every page, with a navigation bar, a visible focus ring, a 1 rem base font and
    targets at least 2.75 rem tall;
  - labels on every field, an `aria-label` on each per-item button, and status lines with
    `role="status"`.
  - The skeleton's welcome page is replaced by a home page.

## Alternatives considered

- **Livewire or Alpine** (the issue's suggestion). Both add a moving part whose only gain here is
  avoiding a page load after a tick; the flow is three pages and a few forms.
- **The floor-plan image with the route painted over it** (v1's picture plus a canvas). The image is a
  seed file, not part of the domain, and a new store (B7) would need its image kept and scaled to the
  grid. The layout the domain holds is enough to find one's way.
- **Every cell of the grid in the SVG.** Store 1's grid has hundreds of thousands of cells; the sectors
  and partitions draw the same picture with a few dozen rectangles.

## Consequences

- The tests of the flow are feature tests on the seeded store 1 (`tests/Feature/Visit/`). There are
  no browser tests: nothing on the pages runs in the browser.
- B7 (#10) reuses the layout for the store-management screens.
- A visit still starts at the entrance (TD-12).
