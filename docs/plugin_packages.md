# Plugin Packages

Plugins may expose a `plugin.json` manifest at the root of the extracted package. The manifest gives PmaControl an explicit install/uninstall plan instead of relying on a blind directory scan.

```json
{
  "name": "example",
  "version": "1.0.0",
  "files": [
    {
      "source": "src/App/Controller/Example.php",
      "destination": "App/Controller/Example.php"
    },
    {
      "source": "src/App/view/Example",
      "destination": "App/view/Example"
    }
  ],
  "ddl": {
    "install": ["sql/install_schema.sql"],
    "uninstall": ["sql/uninstall_schema.sql"]
  },
  "data": {
    "install": ["sql/install_data.sql"],
    "uninstall": ["sql/uninstall_data.sql"]
  },
  "scripts": {
    "install": ["install.php"],
    "uninstall": ["uninstall.php"]
  }
}
```

Rules:

- `source` paths are relative to the plugin package root.
- `destination` paths are relative to the PmaControl project root.
- Absolute paths and `..` traversal are rejected.
- `ddl`, `data` and `sql` files are executed in that order for each phase.
- Scripts ending in `.php` run through the current PHP binary; other scripts must be executable by the system shell.

Legacy plugins without `plugin.json` continue to use the historical directory scan plus `sql/install.sql`, `sql/uninstall.sql`, `install.php` menu registration and plugin file tracking.

## Reference Package

`plugins/extracted/mysql-sys-1.2/` is the in-repository example package. It
contains:

- `plugin.json` with explicit `source` / `destination` file mappings.
- `logo.svg`, referenced by the package manifest and by the local plugin catalog.
- `sql/install.sql` and `sql/uninstall.sql` declared in the `sql` phase.
- Legacy `install.php` menu registration, kept compatible with existing plugin packages.

Plugin install/uninstall clears `tmp/acl/acl.ser` after menu or controller changes so
new routes are visible immediately.
