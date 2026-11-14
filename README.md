# Kiss Portal Extension

A configurable portal extension for phpBB.

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
