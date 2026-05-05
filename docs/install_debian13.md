# Debian 13 Installer

`install/debian13.sh` provisions a Debian 13 host with MariaDB, PHP, Apache, and
the PmaControl application checkout.

Run it as root:

```bash
sudo bash install/debian13.sh
```

The installer pins the requested MariaDB repository version, generates install
credentials at runtime, and keeps the checkout update conservative unless
explicit reset or reinstall flags are provided.

## Network Hardening

The installer uses the shared `install/lib/harden_network.sh` helper after
MariaDB installation.

| Variable | Default | Purpose |
|---|---|---|
| `PMACTRL_HARDEN_DB_BIND` | `1` | Harden MariaDB bind address. Use `auto` to do this only when the app DB host is local, or `0` to skip. |
| `PMACTRL_DB_BIND_ADDRESS` | `127.0.0.1,::1` | MariaDB addresses written to `/etc/mysql/mariadb.conf.d/90-pmacontrol-network.cnf`. |
| `PMACTRL_RPCBIND_POLICY` | `disable` | `disable`, `mask`, or `leave` for `rpcbind.socket` / `rpcbind.service`. |

The default closes MariaDB to non-loopback clients. If the same host is
intentionally used as a Galera/replica node or needs remote MariaDB clients, set
`PMACTRL_HARDEN_DB_BIND=0`, use `PMACTRL_HARDEN_DB_BIND=auto`, or provide an explicit
`PMACTRL_DB_BIND_ADDRESS`.

`rpcbind` is not required by PmaControl. The default policy disables it when the
systemd units exist. If `nfs-common` is installed, the helper leaves `rpcbind`
enabled unless `PMACTRL_RPCBIND_POLICY=mask` is set, to avoid breaking NFS
clients unexpectedly.

Validate listeners after installation:

```bash
ss -lntup | egrep ':(111|3306)\b' || true
```

From a separate test container, repeat the network scan and expect ports `111`
and `3306` to be closed or filtered unless explicitly allowed:

```bash
nmap -Pn -n -sT -T2 --max-rate 20 -p 111,3306 <pmacontrol-host-ip>
```
