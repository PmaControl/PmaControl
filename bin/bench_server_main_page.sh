#!/bin/bash
# Bench the full /Server/main page (HTTP, authenticated) on the same
# worktree URL with two code variants:
#
#   - "master"   : Server.php reverted to origin/master (Extraction2 only)
#   - "worktree" : current branch HEAD              (GlobalVariable + Extraction2)
#
# Average of N runs (default 10), curl time_total.
#
# Usage:
#     bin/bench_server_main_page.sh [iterations]

set -euo pipefail

ITER=${1:-10}
BASE="http://localhost"
PREFIX="/pmacontrol-worktrees/server-main-versions-from-global-variable"
URL="${BASE}${PREFIX}/en/server/main/"
LOGIN_URL="${BASE}${PREFIX}/en/user/connection/"

USER_LOGIN="claude"
USER_PASS="pcPEUByN1aXHLLjHiCxPnP6m"

if ! git rev-parse --git-dir > /dev/null 2>&1; then
    echo "Run from inside a git worktree." >&2
    exit 1
fi
if ! git diff --quiet HEAD --; then
    echo "Working tree has uncommitted changes. Commit or stash first." >&2
    exit 1
fi

CONTROLLER="App/Controller/Server.php"

restore_controller() {
    git checkout HEAD -- "$CONTROLLER" >/dev/null 2>&1 || true
}
trap restore_controller EXIT INT TERM

login_jar() {
    local jar="$1"
    local html
    html=$(curl -sS -c "$jar" -b "$jar" "$LOGIN_URL")
    local token
    token=$(printf '%s' "$html" | grep -oE 'name="_csrf_token" value="[^"]+"' | head -1 | sed -E 's/.*value="([^"]+)"/\1/')
    if [ -z "$token" ]; then
        echo "Could not extract CSRF token from $LOGIN_URL" >&2
        return 1
    fi
    local code
    code=$(curl -sS -o /dev/null -w '%{http_code}' \
        -c "$jar" -b "$jar" -L \
        -H "Origin: ${BASE}" \
        -H "Referer: ${LOGIN_URL}" \
        -d "_csrf_token=${token}" \
        -d "loginForm=loginForm" \
        --data-urlencode "user_main[login]=${USER_LOGIN}" \
        --data-urlencode "user_main[password]=${USER_PASS}" \
        -d "login=Connexion" \
        "$LOGIN_URL")
    [ "$code" = "200" ] || [ "$code" = "302" ]
}

run_bench() {
    local label="$1"
    local jar
    jar=$(mktemp)
    if ! login_jar "$jar"; then
        echo "$label: login failed" >&2
        rm -f "$jar"
        return 1
    fi

    # Warm-up so opcache + ts_max_date caches are populated.
    curl -sS -o /dev/null -b "$jar" -c "$jar" "$URL" || true

    local times=()
    for i in $(seq 1 "$ITER"); do
        local t
        t=$(curl -sS -o /dev/null -b "$jar" -c "$jar" -w '%{time_total}' "$URL")
        times+=("$t")
    done

    awk -v label="$label" '
        BEGIN { min = 1e9; max = 0; sum = 0 }
        { v = $1 * 1000; sum += v; if (v < min) min = v; if (v > max) max = v; n++ }
        END {
            printf "%-46s  (n=%d)  min %.0f ms | mean %.0f ms | max %.0f ms\n",
                   label, n, min, sum / n, max
        }
    ' <(printf '%s\n' "${times[@]}")

    rm -f "$jar"
}

echo "Iterations per side: $ITER"
echo "URL: $URL"
echo

# Side A: master code on Server.php (Extraction2 only)
git checkout origin/master -- "$CONTROLLER" >/dev/null 2>&1
run_bench "master code      (Extraction2(17))"

# Side B: branch HEAD code (GlobalVariable + Extraction2)
git checkout HEAD -- "$CONTROLLER" >/dev/null 2>&1
run_bench "worktree code    (GlobalVariable(9)+Extraction2(8))"
