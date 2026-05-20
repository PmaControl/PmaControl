# AGENTS.md

See **[CLAUDE.md](CLAUDE.md)** — the single source of truth for repository guidance.

It is a thin index: per-topic rules live in `docs/`, deep references in `documentation/`. Load only what the current task needs.

## Workspace Policy

- Keep `/srv/www/pmacontrol` on `master` by default.
- Do not use `/srv/www/pmacontrol` as the active implementation workspace unless the user explicitly says so.
- Use a dedicated worktree under `/srv/www/pmacontrol-worktrees/` for feature work, fixes, reviews, PR preparation and experiments.
- Only leave `/srv/www/pmacontrol` on a non-`master` branch when the user gives a clear contrary instruction, and switch it back to `master` as soon as that exception is no longer needed.

### Bootstrap a new worktree

After `git worktree add`, run the one-shot bootstrap from inside the
new tree so Apache can serve it:

```bash
git -C /srv/www/pmacontrol worktree add -b <branch> \
    /srv/www/pmacontrol-worktrees/<branch> origin/master
cd /srv/www/pmacontrol-worktrees/<branch>
sudo php dev/worktree-install.php
```

The script generates `configuration/webroot.config.php`, symlinks
configuration files, builds the `vendor/` hybrid layout (Composer
PSR-4-safe), drops the `App/Webroot/plugins` symlink, creates
`App/model/IdentifierPmacontrol/`, chowns the tree to `www-data`, and
runs `./glial administration all`. It is idempotent and self-contained
(no Glial bootstrap required), so it stays usable in the half-broken
state it is designed to repair. Full reference + flags:
[docs/parallel_worktree_deployments.md](docs/parallel_worktree_deployments.md).

## Claude Code

Claude Code is available locally through the `claude` CLI.

Use Claude as an independent reviewer or implementation assistant when the task explicitly asks for Claude/double validation:

```bash
claude -p \
  --permission-mode dontAsk \
  --tools Read,Grep,Glob,Bash \
  --model sonnet \
  --max-budget-usd 2 \
  "Review request here"
```

Rules:

- Run Claude from the target repository/worktree, for example `/srv/www/pmacontrol`.
- Use `-p/--print` for non-interactive runs so the result is captured in the terminal.
- For reviews, keep Claude read-only with `--tools Read,Grep,Glob,Bash`; do not allow `Edit` unless the user explicitly asks Claude to modify files.
- Ask Claude to report file/line references, findings, risks, implementation plan and tests.
- Treat Claude's output as a second opinion: Codex still verifies, integrates and is responsible for final changes.
- Do not use force-push. Do not bypass permissions unless the workspace is intentionally disposable.
- If Claude must post on Forgejo/Git, make it do so only when its CLI/session is authenticated and the user explicitly asked for Claude to post itself.
