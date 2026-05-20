# Public Marketing Routes

Issue #513 classifies the marketing/public pages that live outside the PmaControl admin menu.
They are not considered admin dead code only because the menu closure cannot reach them.

## Manifest

`App\Library\PublicRouteCatalog` is the source of truth inside the application repo for public marketing routes that are intentionally outside the admin menu.

It currently tracks two internal Glial controllers:

| Candidate | Scope | Strength | Debt |
|---|---|---|---|
| `App/Controller/Site.php` | Internal public front using `App/layout/site.layout.php` and `App/view/Site/*` | Central public menu, reusable language helper pattern, CSRF-protected demo POST | Smaller SEO/content surface |
| `App/Controller/Pmacontrol.php` | Internal public front using `App/layout/pmacontrol.layout.php` and `App/view/Pmacontrol/*` | Wider product/SEO page catalog and per-page meta payloads | Some views are still wireframes, contact is GET-only, navigation is duplicated in views |

The infrastructure notes also document a third candidate, the separate website repository `pmacontrol/www.pmacontrol.com`, sourced from `/srv/www/site`.
That repository may be the real production website target, so issue #522 must decide whether either internal controller should survive.
The historical audit `/srv/www/infra/docs/pmacontrol-dead-code-audit.md` is outside this application repository; issue comments carry the cross-reference until #522 updates the external audit after the website decision.

## Decision Boundary

Issue #513 only clarifies classification:

- Keep `Site` and `Pmacontrol` out of the admin dead-code deletion list while issue #522 is open.
- Declare the routes in `PublicRouteCatalog`.
- Keep tests that ensure every declared view-backed route still has a controller action and view.
- Avoid deleting or merging either front in this ticket.

Issue #522 owns the product decision:

- choose `Site`, `Pmacontrol`, or `pmacontrol/www.pmacontrol.com` as the canonical website;
- map the active vhost and reverse proxy target;
- migrate reusable content;
- archive or delete the losing implementation;
- add strict smoke tests for the retained public routes.

## Current Technical Reading

`Site` is the better application base because it has a central public menu and already uses the shared CSRF guard for the demo form.
`Pmacontrol` has the broader content inventory but several pages still expose specification text such as hero variants or SEO packs directly in markup.

The likely future merge path is to keep `Site`-style structure, move only production-ready `Pmacontrol` content into the selected website target, and remove the unused internal front after #522.
