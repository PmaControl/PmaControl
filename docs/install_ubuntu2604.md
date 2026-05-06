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
| `PMACTRL_HARDEN_DB_BIND` | `1` | Harden MariaDB bind address. Use `auto` to do this only when the app DB host is local, or `0` to skip. |
| `PMACTRL_DB_BIND_ADDRESS` | `127.0.0.1,::1` | MariaDB addresses written to `/etc/mysql/mariadb.conf.d/90-pmacontrol-network.cnf`. |
| `PMACTRL_RPCBIND_POLICY` | `disable` | `disable`, `mask`, or `leave` for `rpcbind.socket` / `rpcbind.service`. |
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

## Network Hardening

The installer uses the shared `install/lib/harden_network.sh` helper after
MariaDB configuration.

On local DB installs, the default writes
`/etc/mysql/mariadb.conf.d/90-pmacontrol-network.cnf` with:

```ini
[mysqld]
bind-address = 127.0.0.1,::1
```

Set `PMACTRL_HARDEN_DB_BIND=0` when the host intentionally serves MariaDB to
remote clients, set `PMACTRL_HARDEN_DB_BIND=auto` to harden only local DB
installs, or set `PMACTRL_DB_BIND_ADDRESS` to an explicit allowlist.

`rpcbind` is not required by PmaControl. The default
`PMACTRL_RPCBIND_POLICY=disable` stops and disables existing `rpcbind` units. If
`nfs-common` is installed, the helper leaves `rpcbind` enabled unless
`PMACTRL_RPCBIND_POLICY=mask` is set, to avoid breaking NFS clients.

Validate listeners after installation:

```bash
ss -lntup | egrep ':(111|3306)\b' || true
```

From a separate test container, repeat the network scan and expect ports `111`
and `3306` to be closed or filtered unless explicitly allowed:

```bash
nmap -Pn -n -sT -T2 --max-rate 20 -p 111,3306 <pmacontrol-host-ip>
```

## CI

Forgejo CI includes the `ubuntu2604` target in
`.forgejo/workflows/proxmox-install-matrix.yml`.

The Proxmox matrix runs two jobs in parallel. `debian12` and `ubuntu2404` are
created on `pve-2`; `debian13` and `ubuntu2604` are created on `pve-3`.
Before each target starts, `ci/cleanup-proxmox-ci-vms.sh` removes stale
`ci-pmacontrol-*` VMs older than 30 minutes on the target node.

CI VMs use a reserved static IPv4 pool to avoid depending on dynamic DHCP
discovery during guest boot. The default pool is `10.68.68.39-46`, with two
slots per target so a re-run can keep a failed VM without immediately reusing
the same address:

- `debian12`: `10.68.68.39-40`
- `debian13`: `10.68.68.41-42`
- `ubuntu2404`: `10.68.68.43-44`
- `ubuntu2604`: `10.68.68.45-46`

The pool can be shifted with `PMACTRL_CI_STATIC_IP_PREFIX`,
`PMACTRL_CI_STATIC_IP_START`, `PMACTRL_CI_STATIC_IP_COUNT`, and
`PMACTRL_CI_STATIC_IPS_PER_TARGET`. Per-target overrides still take precedence,
for example `PMACTRL_CI_UBUNTU2604_IP`.

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
