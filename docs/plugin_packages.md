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

## Catalog Integrity

The local plugin catalog (`plugins/plugin.json`) carries the trust metadata used
before the ZIP is opened or extracted. New catalog entries should publish:

```json
{
  "mysql-sys": {
    "v1.3": {
      "Picture": "{LINK}plugins/extracted/mysql-sys-1.3/logo.svg",
      "MD5": "0123456789abcdef0123456789abcdef",
      "SHA256": "0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef",
      "Signature": "base64-ed25519-detached-signature",
      "CreationDate": "02/05/2026",
      "Contributor": "publisher",
      "LicenceType": "GPL-3.0",
      "Description": "Plugin description",
      "URL": "https://example.invalid/plugins/mysql-sys/v1.3.zip"
    }
  }
}
```

Rules:

- `SHA256` is computed on the exact ZIP bytes published at `URL`.
- `Signature` is a base64 Ed25519 detached signature over the same ZIP bytes.
- `MD5` is accepted only for legacy transition entries. If both `MD5` and
  `SHA256` are present, both checks must pass.
- A catalog entry with `SHA256` but no valid signature is rejected before
  extraction.
- Trusted Ed25519 public keys are loaded from
  `PMACONTROL_PLUGIN_SIGNATURE_PUBLIC_KEYS` as either a JSON object
  (`{"key-id":"base64-public-key"}`) or a comma/whitespace separated list of
  base64 raw public keys. Production deployments should pin the publisher key in
  configuration before enabling signed packages.

## Reference Package

`plugins/extracted/mysql-sys-1.2/` is the in-repository example package. It
contains:

- `plugin.json` with explicit `source` / `destination` file mappings.
- `logo.svg`, referenced by the package manifest and by the local plugin catalog.
- `sql/install.sql` and `sql/uninstall.sql` declared in the `sql` phase.
- Legacy `install.php` menu registration, kept compatible with existing plugin packages.

Plugin install/uninstall clears `tmp/acl/acl.ser` after menu or controller changes so
new routes are visible immediately.
