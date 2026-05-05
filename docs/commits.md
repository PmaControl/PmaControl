# Commits, PRs, tooling visibility, secrets

## Commit messages

Short, imperative descriptions — matches the repo history:

- `add schema history`
- `fix ProxySQL IPv6`
- `patch ProxySQL for IPv6`

Group related changes per commit. Avoid WIP messages. **Do NOT add `Co-Authored-By` trailers.**

## Pull requests

- Describe intent.
- List the commands / tests you ran.
- Link any tracked issue.
- Include screenshots for UI tweaks.
- Mention configuration or schema changes explicitly so operators can rehearse upgrades.

## Tooling visibility — strict

Do **not** leave any trace of AI tooling in user-visible artifacts. Never mention `ChatGPT`, `Codex`, `Claude`, `connector`, `assistant`, or the fact that an automated tool helped produce the change, in:

- commit messages
- pull requests
- GitHub issues / comments
- changelogs, release notes
- screenshots, functional documentation

When a pushed commit fixes a GitHub ticket, add the exact commit URL to the ticket after pushing so the issue is explicitly linked to the delivered fix.

## Secrets & configuration

- Never commit concrete credentials from `config_sample/`. Create environment-specific copies under `configuration/` and rely on `.gitignore`.
- Treat `data/`, `tmp/`, and backup outputs as ephemeral — never commit them.
- When touching SSH / backup code, validate permissions inside `bin/` scripts and document any new required Linux capabilities in the PR.
