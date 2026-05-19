# Parallel worktree deployments

PmaControl serves multiple checkouts side-by-side through a single Apache
vhost. Two flavours coexist:

| URL pattern | Filesystem root | Naming | Use case |
|---|---|---|---|
| `/pmacontrol-reviews/<hash>/` | `/srv/www/pmacontrol-reviews/worktrees/<hash>/` | hex commit hash (`[a-f0-9]{6,40}`) | Automated review snapshots created by `/usr/local/sbin/pmacontrol-{master,review-consensus}-review-watch.sh` |
| `/pmacontrol-worktrees/<branch>/` | `/srv/www/pmacontrol-worktrees/<branch>/` | branch slug (`[A-Za-z0-9._-]+`) | Manual feature/fix worktrees per `AGENTS.md` workspace policy |

The two trees are siblings of `/srv/www/pmacontrol/` (the canonical
`master` checkout) so `www-data` can traverse them without changing
ownership on the protected ai-review root.

## Apache configuration

The vhost lives in `/etc/apache2/sites-enabled/000-default.conf`. Each
flavour needs one `AliasMatch`, one `<Directory>` block, and one
front-controller `RewriteRule`:

```apache
# /pmacontrol-worktrees/<branch>/...
AliasMatch "^/pmacontrol-worktrees/([A-Za-z0-9._-]+)(/.*)?$" \
    "/srv/www/pmacontrol-worktrees/$1/App/Webroot$2"
<Directory /srv/www/pmacontrol-worktrees>
    AllowOverride None
    Options FollowSymLinks
    DirectoryIndex index.php
    Require all granted
</Directory>
RewriteCond %{LA-U:REQUEST_FILENAME} !-d
RewriteCond %{LA-U:REQUEST_FILENAME} !-f
RewriteRule ^/pmacontrol-worktrees/([A-Za-z0-9._-]+)/(.*)$ \
    /pmacontrol-worktrees/$1/index.php?glial_path=$2 [QSA,PT,L,NS]
```

Why the regex matters: `pmacontrol-reviews/` uses `[a-f0-9]{6,40}`
because review trees are named after a commit hash. Branch worktrees
(`issue-1285-eol-plugin`, `master-cve-detail-page`, …) mix letters,
digits, hyphens, underscores and dots — a hex-only regex returns
`403 Forbidden` (autoindex denied) because the alias never fires.

`LA-U:REQUEST_FILENAME` performs a look-ahead sub-request through
`mod_alias` so static assets that `AliasMatch` maps to real files
(`css/`, `js/`, `image/`, `fonts/`, …) bypass the rewrite. `[NS]`
prevents the look-ahead from re-firing the rule against itself.

## Per-worktree bootstrap

A fresh worktree (created with `git worktree add /srv/www/pmacontrol-worktrees/<branch> <branch>`)
needs three things before the URL serves anything but 500s:

1. **`configuration/webroot.config.php`** — derives `WWW_ROOT` from the
   directory name so Glial generates matching links, and points
   `PLUGIN_STORAGE_DIR` at the canonical cache shared with master so the
   worktree does not allocate its own `<branch>-plugin/.cache/` next door:

   ```php
   <?php
   if (! defined('WWW_ROOT')) {
       $branch = basename(dirname(__DIR__));
       define('WWW_ROOT', '/pmacontrol-worktrees/'.$branch.'/');
   }

   // Share the plugin install cache with /srv/www/pmacontrol/ rather
   // than letting each worktree allocate its own
   //   /srv/www/pmacontrol-worktrees/<branch>-plugin/.cache/
   // (the default in App/Webroot/index.php). Saves redundant downloads
   // and extra chowns when juggling several worktrees.
   if (! defined('PLUGIN_STORAGE_DIR')) {
       define('PLUGIN_STORAGE_DIR', '/srv/www/pmacontrol-plugin/.cache/');
   }
   ```

2. **`configuration/*.php` and `*.ini.php`** — symlinks back to the
   master checkout so the worktree shares the live DB/auth/LDAP/log
   credentials instead of carrying its own copy:

   ```bash
   for f in /srv/www/pmacontrol/configuration/*; do
       ln -s "$f" "configuration/$(basename "$f")"
   done
   ```

3. **`vendor/`** — do **not** symlink the whole `vendor/` directory.
   Composer's PSR-4 autoload computes
   `$baseDir = dirname($vendorDir)` from inside
   `vendor/composer/autoload_psr4.php`; a whole-tree symlink resolves
   `__DIR__` through the symlink to the master path, so `App\\Controller\\…`
   classes load from `/srv/www/pmacontrol/App/Controller/…` instead of
   the worktree's own copy — your edits to controllers / libraries
   simply do not take effect, even though the URL routes through the
   worktree.

   Mirror the `pmacontrol-reviews/` pattern: keep `vendor/composer/`
   and `vendor/autoload.php` locally, and symlink each package
   directory back to the master:

   ```bash
   mkdir vendor
   cp -a /srv/www/pmacontrol/vendor/autoload.php vendor/
   cp -a /srv/www/pmacontrol/vendor/composer    vendor/
   for d in /srv/www/pmacontrol/vendor/*/; do
       base=$(basename "$d")
       [ "$base" = "composer" ] && continue
       ln -s "$d" "vendor/$base"
   done
   ```

   Verify with:

   ```bash
   sudo -u www-data php -r 'require "vendor/autoload.php";
       echo (new ReflectionClass("App\\Controller\\Plugin"))->getFileName()."\n";'
   ```

   It must print the worktree path, not `/srv/www/pmacontrol/…`.

4. **Ownership** — `git worktree add` creates everything as root, but
   Apache runs as `www-data` and must write into several places at
   runtime: `tmp/` (Glial table cache, ACL cache, lockfiles, logs),
   `App/model/` (regenerated by `./glial administration all`),
   `App/Controller/` + `App/Library/` + `App/view/` + `App/Webroot/image/`
   (Plugin install copies files into these), and `plugins/<name>/`
   (zip download target). Chown the whole worktree once:

   ```bash
   chown -R www-data:www-data .
   ```

   Otherwise the Plugin install action fails with
   `mkdir(): Permission denied` followed by
   `fopen(plugins/<name>/<version>.zip): Failed to open stream:
   No such file or directory`.

5. **`tmp/database/` table cache + `App/model/IdentifierPmacontrol/`** —
   `App/model/.gitignore` ignores `*.php`, so the worktree starts with
   an empty `App/model/` and the Glial table cache is missing
   (`GLI-003 : the file cash of "user_main" … doent exist`).
   Recreate the model directory and regenerate both via the Glial CLI:

   ```bash
   mkdir -p App/model/IdentifierPmacontrol
   chown -R www-data:www-data App/model
   sudo -u www-data php ./glial administration all
   ```

   This populates `tmp/database/<table>.table.txt` (one serialised
   field list per table) and `App/model/IdentifierPmacontrol/<table>.php`
   (one Model class per table). Re-run whenever the schema changes.

6. **Plugin static assets** — the plugin catalog page references logos
   under `plugins/extracted/<name>/logo.svg`, but Apache's `AliasMatch`
   maps every URL to `App/Webroot/…`, which means `plugins/` sits
   outside the served tree. Add a symlink so plugin logos resolve
   before the plugin is installed:

   ```bash
   ln -s ../../plugins App/Webroot/plugins
   ```

   The catalog placeholder `{WWW}` (and the legacy `{LINK}` alias) is
   substituted with `WWW_ROOT` — *not* `LINK` — by
   `App/view/Plugin/index.view.php` so the URL has no `/<lang>/`
   prefix and matches the symlink target.

After those steps, `http://<host>/pmacontrol-worktrees/<branch>/`
redirects to `/<lang>/server/main` and serves the same UI as the
canonical checkout, against the same database.

## Pitfalls

- ACL cache (`tmp/acl/acl.ser`) is per-tree: see
  [feedback_acl_cache](feedback_acl_cache.md). If a worktree adds a new
  controller/action, drop its own `tmp/acl/acl.ser`, not master's.
- Static assets are served by `AliasMatch` only when they exist on disk
  in the worktree. Branch worktrees inherit `App/Webroot/css|js|image`
  from the branch checkout — no symlinks needed.
- Apache reload after editing `000-default.conf`:
  `apache2ctl configtest && systemctl reload apache2`.
