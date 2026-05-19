#!/bin/bash
# Bench the full /Server/main page (HTTP, authenticated) on master vs worktree.
# Average of N runs (default 10) for each side, using time-total from curl.
#
# Usage:
#     bin/bench_server_main_page.sh [iterations]

set -euo pipefail

ITER=${1:-10}
BASE="http://localhost"
MASTER_PREFIX=""
WORKTREE_PREFIX="/pmacontrol-worktrees/server-main-versions-from-global-variable"

USER="claude"
PASS="pcPEUByN1aXHLLjHiCxPnP6m"

login_and_time() {
    local prefix="$1"
    local label="$2"
    local jar
    jar=$(mktemp)

    local conn_url="${BASE}${prefix}/en/user/connection/"
    local main_url="${BASE}${prefix}/en/server/main/"

    # Step 1: GET login page to seat session + grab CSRF token.
    local html
    html=$(curl -sS -c "$jar" -b "$jar" "$conn_url")
    local token
    token=$(printf '%s' "$html" | grep -oE 'name="_csrf_token" value="[^"]+"' | head -1 | sed -E 's/.*value="([^"]+)"/\1/')
    if [ -z "$token" ]; then
        echo "$label: could not extract CSRF token from $conn_url" >&2
        rm -f "$jar"
        return 1
    fi

    # Step 2: POST creds.
    local post_status
    post_status=$(curl -sS -o /dev/null -w '%{http_code}' \
        -c "$jar" -b "$jar" -L \
        -d "_csrf_token=${token}" \
        -d "loginForm=loginForm" \
        --data-urlencode "user_main[login]=${USER}" \
        --data-urlencode "user_main[password]=${PASS}" \
        -d "login=Connexion" \
        "$conn_url")
    if [ "$post_status" != "200" ] && [ "$post_status" != "302" ]; then
        echo "$label: login POST returned $post_status" >&2
        rm -f "$jar"
        return 1
    fi

    # Warm-up: hit /Server/main once so server-side caches (opcache,
    # ts_max_date etc.) are populated for both sides.
    curl -sS -o /dev/null -b "$jar" -c "$jar" "$main_url" || true

    local total=0
    local times=()
    for i in $(seq 1 "$ITER"); do
        local t
        t=$(curl -sS -o /dev/null -b "$jar" -c "$jar" -w '%{time_total}' "$main_url")
        times+=("$t")
    done

    # Mean / min / max in milliseconds.
    awk -v label="$label" -v iter="$ITER" '
        BEGIN { min = 1e9; max = 0; sum = 0 }
        { v = $1 * 1000; sum += v; if (v < min) min = v; if (v > max) max = v; n++ }
        END {
            printf "%s  (n=%d)  min %.0f ms | mean %.0f ms | max %.0f ms\n",
                   label, n, min, sum / n, max
        }
    ' <(printf '%s\n' "${times[@]}")

    rm -f "$jar"
}

echo "Iterations per side: $ITER"
echo

login_and_time "$MASTER_PREFIX"   "master    (Extraction2 only)"
login_and_time "$WORKTREE_PREFIX" "worktree  (GlobalVariable + Extraction2)"
