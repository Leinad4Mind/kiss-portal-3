# Kiss Portal Extension

A configurable portal extension for phpBB.

## Project lineage

This extension is the modern phpBB extension branch of the KISS/Stargate
Portal family developed by Michael O'Toole and phpBB Ireland:

- [K2 Phoenix](https://github.com/phpbbireland/k2_phoenix) is the simplified
  KISS II/Stargate Portal 1.0.21 MOD for phpBB 3.0.12.
- [Kiss Portal Engine](https://github.com/phpbbireland/kiss_portal_engine)
  contains the legacy 2.0.0 MOD for phpBB 3.0.11 and a separate `2026`
  snapshot of the extension tested on phpBB 3.3.15.
- [KISS Portal 3](https://github.com/leinad4mind/kiss-portal-3) preserves the
  original `phpbbireland/portal` extension history and is the maintained branch
  for phpBB 3.3.19.

The phpBB 3.0 repositories are historical references only. Their MODX/UMIL
packages modify phpBB core files and must not be installed over this extension.

### 2026 snapshot review

The `2026/phpbbireland/portal` snapshot was compared file by file on
2026-11-15. It shares the same KISS Portal codebase, but is an experimental
upload whose README does not recommend production use without manual review.

The review found that migration 1.0.2 referenced a missing `www.github.gif`.
The snapshot's file with that name was actually an unrelated, mislabelled
"phpBB3 Portal" banner, so it was not imported. Release 1.1.1 instead corrects
the GitHub URL and uses an existing KISS/phpBB Ireland portal image.

The snapshot's unfinished clock, site-specific `lsf.css`, fixed-width styles,
duplicate jQuery/jQuery UI copies, Prototype/Scriptaculous, old drag-and-drop
implementation and obsolete templates were deliberately not imported. The
current dependency-free and responsive implementations supersede them.

## Requirements

- phpBB 3.3.19
- PHP 7.4 or newer

The extension currently targets phpBB 3.3 only and will refuse to enable on
older phpBB versions.

## Installation

1. Copy this directory to `ext/phpbbireland/portal/` in the phpBB installation.
2. In the ACP, open **Customise > Manage extensions**.
3. Find **Kiss Portal Extension** and select **Enable**.
4. Purge the phpBB cache after changing styles or templates.

For an existing installation, back up the database and extension files before
updating. Disable the extension, replace its files, enable it again, then purge
the cache. Do not select **Delete data** unless all portal configuration and
content may be removed.

## Configuration

Portal blocks, menus, pages and permissions are managed under **ACP > Portal**.
Review group visibility after installation because group IDs can differ between
phpBB installations.

## Feature status

- Signed-in users with the portal permission can arrange blocks from the UCP.
  The layout is saved to their phpBB user profile after drag and drop.
- The optional poll block displays results from the post ID configured in its
  ACP variables. Voting is deliberately handled by the linked phpBB topic so
  phpBB keeps control of permissions, CSRF checks and vote changes.
- The random-image block supports GIF, JPEG, PNG and WebP files placed in
  `images/random_images/`.
- The style selector provides a temporary preview. Permanent style selection
  remains in phpBB's UCP preferences.
- The links-menu block remains disabled by default because it is optional site
  content; it can be enabled and configured in the ACP.
- The obsolete fixed-width selector, Highslide integration and bundled legacy
  JavaScript libraries were removed. Current phpBB styles are responsive.

## Security notes

- Administrative changes use phpBB form keys or confirmation tokens.
- Portal layout values and dynamic block/template filenames are validated before
  they are used in database queries or filesystem paths.
- Menu URLs are restricted to safe relative paths or HTTP(S) links.
- Block arrangement and visibility use dependency-free JavaScript. Obsolete
  remote jQuery UI, Prototype/Scriptaculous, Highslide and `eval`-based page
  initialisation have been removed.

## Uninstallation

Disable the extension from **Customise > Manage extensions**. Selecting
**Delete data** permanently removes the extension's database data. The files can
then be removed from `ext/phpbbireland/portal/`.

## License

[GPL-2.0](license.txt)
