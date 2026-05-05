# Issue workflow

Use this workflow when processing a tracked Forgejo/Git issue, regardless of topic.
Topic-specific docs may add extra checks, but they do not replace this process.

## General process

1. Inventory matching open issues when processing a series. Exclude issues that already have an open PR or are already handled. Process newest first unless an explicit priority says otherwise.
2. For each issue, post an initial handling summary, create a dedicated branch from an up-to-date target branch, and never modify the target branch directly. Use `commercial-issue-XXX` when the target branch is `commercial`.
3. Reproduce or prove the problem before changing code when realistic. For performance issues, capture the slow query, timing, table/index shape and `EXPLAIN`; for security issues, capture the accepted unsafe request; for bugs, capture the failing behavior.
4. For each meaningful action, run the Codex pass first and the Claude pass second when the action benefits from an independent view. Each pass must produce its own issue comment.
5. Compare the Codex and Claude outputs, state which version is best for each point, then propose a mixed V3. Review the V3 with both passes; if both agree, continue to the next step. If not, propose another V3 and repeat the review loop until there is agreement or a clear blocker.
6. Keep issue comments action-based and technical. Add comments after analysis, after the independent Claude pass, after comparison/V3, after implementation with touched files, with test results, with commit SHA, with the PR link, and finally with the merged PR link.
7. Push the branch, open the PR against the target branch, link the PR in the issue, merge only after both reviews agree, post the final PR and merge commit links, close the issue, then continue with the next issue in the series.

## Internal two-pass rule

For internal execution, the first pass is handled by Codex and the independent pass by Claude. Claude should post its own issue comment whenever possible. Avoid pass-through comments; only relay Claude's technical result if direct posting is blocked.

Issue comments for this workflow may identify the Codex and Claude passes so the interaction is auditable. Commit messages, PR descriptions, changelogs, release notes, screenshots and functional documentation remain focused on the technical diagnosis, changes, tests and links.
