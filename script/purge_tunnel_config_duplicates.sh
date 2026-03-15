#!/usr/bin/env bash
set -euo pipefail

usage() {
    cat <<'EOF'
Usage:
  purge_tunnel_config_duplicates.sh [--dry-run] /path/to/.tunnel.xxx --local HOST:PORT [--remote HOST:PORT]
  purge_tunnel_config_duplicates.sh [--dry-run] /path/to/.tunnel.xxx --spec HOST:PORT=HOST:PORT

Examples:
  purge_tunnel_config_duplicates.sh ~/.tunnel.prod --local 127.0.0.1:3307 --remote 10.105.2.8:3306
  purge_tunnel_config_duplicates.sh --dry-run ~/.tunnel.prod --spec 127.0.0.1:3307=10.105.2.8:3306

Behavior:
  - creates a backup next to the file before modifying it
  - removes every line whose SSH tunnel matches the given local and/or remote endpoint
  - supports both `-L` and `-R`
EOF
}

dry_run=0
file=""
local_spec=""
remote_spec=""

while (($# > 0)); do
    case "$1" in
        --dry-run)
            dry_run=1
            shift
            ;;
        --local)
            local_spec="${2:-}"
            shift 2
            ;;
        --remote)
            remote_spec="${2:-}"
            shift 2
            ;;
        --spec)
            spec="${2:-}"
            if [[ "$spec" != *=* ]]; then
                echo "Invalid --spec value: $spec" >&2
                exit 1
            fi
            local_spec="${spec%%=*}"
            remote_spec="${spec#*=}"
            shift 2
            ;;
        -h|--help)
            usage
            exit 0
            ;;
        -*)
            echo "Unknown option: $1" >&2
            usage >&2
            exit 1
            ;;
        *)
            if [[ -z "$file" ]]; then
                file="$1"
            else
                echo "Unexpected argument: $1" >&2
                usage >&2
                exit 1
            fi
            shift
            ;;
    esac
done

if [[ -z "$file" || ( -z "$local_spec" && -z "$remote_spec" ) ]]; then
    usage >&2
    exit 1
fi

if [[ ! -f "$file" ]]; then
    echo "File not found: $file" >&2
    exit 1
fi

tmp_file="$(mktemp)"
trap 'rm -f "$tmp_file"' EXIT

awk -v wanted_local="$local_spec" -v wanted_remote="$remote_spec" '
function trim(s) {
    sub(/^[[:space:]]+/, "", s)
    sub(/[[:space:]]+$/, "", s)
    return s
}

function split_spec(spec, arr,  count) {
    count = split(spec, arr, ":")
    if (count == 3) {
        return arr[1] ":" arr[2] ":" arr[3]
    }
    if (count == 4) {
        return arr[1] ":" arr[2] ":" arr[3] ":" arr[4]
    }
    return ""
}

function parse_forward(line, flag,   regex, raw, parts, count) {
    regex = "-" flag "[[:space:]]+([^[:space:]]+)"
    if (match(line, regex)) {
        raw = substr(line, RSTART, RLENGTH)
        sub("^-" flag "[[:space:]]+", "", raw)
        return raw
    }
    return ""
}

function endpoint_local(spec,   p, count) {
    count = split(spec, p, ":")
    if (count == 3) {
        return "127.0.0.1:" p[1]
    }
    if (count == 4) {
        return p[1] ":" p[2]
    }
    return ""
}

function endpoint_remote(spec,   p, count) {
    count = split(spec, p, ":")
    if (count == 3) {
        return p[2] ":" p[3]
    }
    if (count == 4) {
        return p[3] ":" p[4]
    }
    return ""
}

function matches(line,   spec, local_ep, remote_ep) {
    spec = parse_forward(line, "L")
    if (spec != "") {
        local_ep = endpoint_local(spec)
        remote_ep = endpoint_remote(spec)
        if ((wanted_local == "" || wanted_local == local_ep) && (wanted_remote == "" || wanted_remote == remote_ep)) {
            return 1
        }
    }

    spec = parse_forward(line, "R")
    if (spec != "") {
        local_ep = endpoint_remote(spec)
        remote_ep = endpoint_local(spec)
        if ((wanted_local == "" || wanted_local == local_ep) && (wanted_remote == "" || wanted_remote == remote_ep)) {
            return 1
        }
    }

    return 0
}

{
    if (matches($0)) {
        print "REMOVE\t" $0 > "/dev/stderr"
        next
    }

    print $0
}
' "$file" > "$tmp_file"

if cmp -s "$file" "$tmp_file"; then
    echo "No matching tunnel line found in $file"
    exit 0
fi

if [[ "$dry_run" -eq 1 ]]; then
    echo "Dry-run only, no file modified: $file"
    exit 0
fi

backup_file="${file}.bak.$(date +%Y%m%d%H%M%S)"
cp "$file" "$backup_file"
mv "$tmp_file" "$file"
trap - EXIT
echo "Updated: $file"
echo "Backup : $backup_file"
