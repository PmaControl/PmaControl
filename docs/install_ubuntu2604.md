# Ubuntu 26.04 Installer

`install/ubuntu26.04.sh` provisions a fresh Ubuntu 26.04 host and then runs the
repository root `install.sh` application installer.

Run it as root:

```bash
sudo PMACTRL_GIT_BRANCH=commercial bash install/ubuntu26.04.sh
```

The script is non-interactive and sets the usual APT non-interactive variables.
It installs Apache, PHP, Composer, MariaDB, the PHP extensions used by
PmaControl, Graphviz, cron, and the small command-line utilities expected by the
existing installer.

## Important Variables

| Variable | Default | Purpose |
|---|---|---|
| `PMACTRL_INSTALL_DIR` | `/srv/www/pmacontrol` | Target checkout path. |
| `PMACTRL_REPO_URL` | `https://github.com/PmaControl/PmaControl.git` | Git repository to clone. |
| `PMACTRL_GIT_BRANCH` | `commercial` | Branch to clone or update. |
| `PMACTRL_WEBROOT` | `/pmacontrol/` | Web path written into the install config. |
| `PMACTRL_DB_NAME` | `pmacontrol` | Application database name. |
| `PMACTRL_DB_USER` | `pmacontrol` | MariaDB user created for the app. |
| `PMACTRL_DB_PASSWORD` | generated | MariaDB password. |
| `PMACTRL_ADMIN_LOGIN` | `admin` | Initial admin login. |
| `PMACTRL_ADMIN_PASSWORD` | generated | Initial admin password. |
| `PMACTRL_ORGANIZATION` | `PmaControl` | Initial organization name. |
| `PMACTRL_FORCE_REINSTALL` | `0` | Remove/reinstall the checkout and rerun app install when set to `1`. |
| `PMACTRL_DRY_RUN` | `0` | Print commands without changing the system when set to `1`. |
| `PMACTRL_KEEP_CONFIG` | `0` | Keep the generated JSON config when set to `1`. |
| `PMACTRL_RUN_PHPUNIT` | `0` | Run the PHPUnit suite after install when set to `1`. |
| `PMACTRL_SKIP_OS_CHECK` | `0` | Skip the Ubuntu 26.04 guard for local syntax/dry-run validation. |
| `PMACTRL_SKIP_UPGRADE` | `0` | Skip `apt-get upgrade` when set to `1`. |
| `PMACTRL_CREDENTIALS_FILE` | `/root/pmacontrol-credentials.txt` | File receiving generated credentials with mode `0600`. |

## Re-runs

By default, the script is conservative on an existing installation:

- it does not remove `PMACTRL_INSTALL_DIR`;
- it updates an existing Git checkout with a fast-forward pull;
- it skips the application `install.sh` step if
  `configuration/db.config.ini.php` already exists.

Use `PMACTRL_FORCE_REINSTALL=1` only on disposable hosts or test containers.

## CI

Forgejo CI includes the `ubuntu2604` target in
`.forgejo/workflows/proxmox-install-matrix.yml`.

The Proxmox runner maps it to template `923` by default:

```bash
PMACTRL_CI_UBUNTU2604_TEMPLATE_ID=923 bash ci/proxmox-install-matrix.sh ubuntu2604
```

The remote test path runs `install/ubuntu26.04.sh`, checks that
`http://127.0.0.1/pmacontrol/` returns `200`, `301`, or `302`, then runs the
PHPUnit suite.

## Private Forgejo Checkout

For a private Forgejo repository, provide the repository URL explicitly:

```bash
PMACTRL_REPO_URL="https://git.istosia.com/pmacontrol/pmacontrol.git" \
PMACTRL_GIT_BRANCH=commercial \
sudo -E bash install/ubuntu26.04.sh
```

Use the normal Git credential mechanism for the host running the installer.
