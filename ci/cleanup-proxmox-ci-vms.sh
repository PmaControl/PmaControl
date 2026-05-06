#!/usr/bin/env bash
set -Eeuo pipefail

AGE_MINUTES="${PMACTRL_CI_CLEANUP_AGE_MINUTES:-30}"
PREFIX="${PMACTRL_CI_VM_NAME_PREFIX:-ci-pmacontrol-}"
NODE="${PMACTRL_CI_NODE:-$(hostname -s)}"
DRY_RUN=0
INCLUDE_RUNNING=1

usage() {
    cat <<'USAGE'
Usage: ci/cleanup-proxmox-ci-vms.sh [options]

Remove stale Proxmox VMs whose names start with ci-pmacontrol-.

Options:
  --age-minutes N  Minimum VM config age before removal (default: 30)
  --node NODE      Proxmox node to clean (default: local hostname)
  --dry-run        Only print what would be removed
  --stopped-only   Do not remove running VMs
  -h, --help       Show this help
USAGE
}

while [[ $# -gt 0 ]]; do
    case "$1" in
        --age-minutes)
            AGE_MINUTES="${2:?missing value for --age-minutes}"
            shift 2
            ;;
        --node)
            NODE="${2:?missing value for --node}"
            shift 2
            ;;
        --dry-run)
            DRY_RUN=1
            shift
            ;;
        --stopped-only)
            INCLUDE_RUNNING=0
            shift
            ;;
        -h|--help)
            usage
            exit 0
            ;;
        *)
            echo "Unknown option: $1" >&2
            usage >&2
            exit 2
            ;;
    esac
done

if [[ ! "${AGE_MINUTES}" =~ ^[0-9]+$ ]]; then
    echo "--age-minutes must be an integer" >&2
    exit 2
fi

cleanup_local() {
    local now node_name
    now="$(date +%s)"
    node_name="$(hostname -s)"

    qm list | awk -v prefix="${PREFIX}" 'NR > 1 && index($2, prefix) == 1 {print $1 "\t" $2 "\t" $3}' |
    while IFS=$'\t' read -r vmid name status; do
        [[ -n "${vmid}" ]] || continue

        local conf mtime age_seconds age_minutes
        conf="/etc/pve/nodes/${node_name}/qemu-server/${vmid}.conf"
        if [[ ! -e "${conf}" ]]; then
            conf="/etc/pve/qemu-server/${vmid}.conf"
        fi
        if [[ ! -e "${conf}" ]]; then
            printf '[cleanup:%s] skip %s %s: config not found\n' "${node_name}" "${vmid}" "${name}" >&2
            continue
        fi

        if qm config "${vmid}" | grep -q '^template: 1'; then
            printf '[cleanup:%s] keep %s %s: template\n' "${node_name}" "${vmid}" "${name}"
            continue
        fi
        if qm config "${vmid}" | grep -q '^protection: 1'; then
            printf '[cleanup:%s] keep %s %s: protected\n' "${node_name}" "${vmid}" "${name}"
            continue
        fi

        mtime="$(stat -c %Y "${conf}")"
        age_seconds=$((now - mtime))
        if (( age_seconds < 0 )); then
            age_seconds=0
        fi
        age_minutes=$((age_seconds / 60))

        if (( age_minutes < AGE_MINUTES )); then
            printf '[cleanup:%s] keep %s %s: age=%sm status=%s\n' "${node_name}" "${vmid}" "${name}" "${age_minutes}" "${status}"
            continue
        fi

        if [[ "${status}" == "running" && "${INCLUDE_RUNNING}" != "1" ]]; then
            printf '[cleanup:%s] keep %s %s: running age=%sm\n' "${node_name}" "${vmid}" "${name}" "${age_minutes}"
            continue
        fi

        if (( DRY_RUN )); then
            printf '[cleanup:%s] dry-run remove %s %s: age=%sm status=%s\n' "${node_name}" "${vmid}" "${name}" "${age_minutes}" "${status}"
            continue
        fi

        printf '[cleanup:%s] remove %s %s: age=%sm status=%s\n' "${node_name}" "${vmid}" "${name}" "${age_minutes}" "${status}"
        qm unlock "${vmid}" >/dev/null 2>&1 || true
        if [[ "${status}" == "running" ]]; then
            qm stop "${vmid}" --skiplock 1 --timeout 60 >/dev/null 2>&1 || qm stop "${vmid}" --skiplock 1 >/dev/null 2>&1 || true
        fi
        qm destroy "${vmid}" --purge 1 >/dev/null
    done
}

if [[ "${NODE}" == "$(hostname -s)" ]]; then
    cleanup_local
else
    {
        printf 'AGE_MINUTES=%q\n' "${AGE_MINUTES}"
        printf 'PREFIX=%q\n' "${PREFIX}"
        printf 'DRY_RUN=%q\n' "${DRY_RUN}"
        printf 'INCLUDE_RUNNING=%q\n' "${INCLUDE_RUNNING}"
        declare -f cleanup_local
        printf 'cleanup_local\n'
    } | ssh -o BatchMode=yes -o StrictHostKeyChecking=accept-new root@"${NODE}" \
        'bash -seuo pipefail'
fi
