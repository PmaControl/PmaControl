#!/usr/bin/env bash
set -Eeuo pipefail

TARGET_OS="${1:?missing target os}"
WORKSPACE="${GITHUB_WORKSPACE:-$(pwd)}"
STORAGE="${PMACTRL_CI_STORAGE:-AS6510T-PVE1}"
BRIDGE="${PMACTRL_CI_BRIDGE:-vmbr0}"
SSH_OPTS=(-o BatchMode=yes -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -o ConnectTimeout=5)
PVE_SSH_OPTS=(-o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=10)
VM_NAME_PREFIX="ci-pmacontrol"
RESULT="failure"
LOCK_WAIT_SECONDS="${PMACTRL_CI_LOCK_WAIT_SECONDS:-21600}"
MIN_FREE_KIB="${PMACTRL_CI_MIN_FREE_KIB:-5242880}"
KEEP_FAILED_VM="${PMACTRL_CI_KEEP_FAILED_VM:-0}"
SSH_PUBLIC_KEY_FILE="${PMACTRL_CI_SSH_PUBLIC_KEY_FILE:-/root/.ssh/id_rsa.pub}"
LOCAL_NODE="$(hostname -s)"
RUN_NODE=""
RUN_HOST=""
LOCK_FILE=""
VMID=""
VM_NAME=""
VM_IP=""
CI_SNIPPET=""

log() {
    printf '[ci:%s:%s] %s\n' "${TARGET_OS}" "${RUN_NODE:-unassigned}" "$*"
}

require_cmd() {
    command -v "$1" >/dev/null 2>&1 || {
        echo "Missing command: $1" >&2
        exit 1
    }
}

for cmd in qm ssh python3 tar git flock pvesm install; do
    require_cmd "${cmd}"
done

case "${TARGET_OS}" in
    debian12)
        TEMPLATE_ID=920
        VMID_START=9300
        VMID_END=9399
        RUN_NODE="${PMACTRL_CI_NODE:-pve-2}"
        ;;
    debian13)
        TEMPLATE_ID=921
        VMID_START=9400
        VMID_END=9499
        RUN_NODE="${PMACTRL_CI_NODE:-pve-3}"
        ;;
    ubuntu2404)
        TEMPLATE_ID=922
        VMID_START=9500
        VMID_END=9599
        RUN_NODE="${PMACTRL_CI_NODE:-pve-2}"
        ;;
    ubuntu2604)
        TEMPLATE_ID="${PMACTRL_CI_UBUNTU2604_TEMPLATE_ID:-923}"
        VMID_START=9600
        VMID_END=9699
        RUN_NODE="${PMACTRL_CI_NODE:-pve-3}"
        ;;
    *)
        echo "Unsupported target OS: ${TARGET_OS}" >&2
        exit 1
        ;;
esac

LOCK_FILE="${PMACTRL_CI_LOCK_FILE:-/run/lock/pmacontrol-proxmox-install-${RUN_NODE}.lock}"
case "${RUN_NODE}" in
    pve-2)
        RUN_HOST="${PMACTRL_CI_NODE_HOST:-10.68.68.121}"
        ;;
    pve-3)
        RUN_HOST="${PMACTRL_CI_NODE_HOST:-10.68.68.122}"
        ;;
    *)
        RUN_HOST="${PMACTRL_CI_NODE_HOST:-${RUN_NODE}}"
        ;;
esac

if [[ "${PMACTRL_CI_LOCK_HELD:-0}" != "1" ]]; then
    log "waiting for CI lock ${LOCK_FILE}"
    export PMACTRL_CI_LOCK_HELD=1
    export PMACTRL_CI_NODE="${RUN_NODE}"
    exec flock --close -w "${LOCK_WAIT_SECONDS}" "${LOCK_FILE}" bash "$0" "${TARGET_OS}"
fi

should_destroy_vm() {
    [[ "${RESULT}" == "success" || "${KEEP_FAILED_VM}" != "1" ]]
}

cleanup() {
    local exit_code=$?
    if [[ -n "${VMID}" ]] && should_destroy_vm; then
        if [[ "${RESULT}" == "success" ]]; then
            log "destroying VM ${VMID}"
        else
            log "destroying failed VM ${VMID} (${VM_NAME}); set PMACTRL_CI_KEEP_FAILED_VM=1 to keep it"
        fi
        pve_node_cmd qm stop "${VMID}" --skiplock 1 >/dev/null 2>&1 || true
        pve_node_cmd qm destroy "${VMID}" --purge 1 >/dev/null 2>&1 || true
    elif [[ -n "${VMID}" ]]; then
        log "keeping failed VM ${VMID} (${VM_NAME})"
        if [[ -n "${VM_IP}" ]]; then
            log "failed VM IP: ${VM_IP}"
        fi
    fi
    if should_destroy_vm && [[ -n "${CI_SNIPPET}" ]]; then
        pve_node_cmd rm -f "${CI_SNIPPET}" >/dev/null 2>&1 || true
    fi
    exit "${exit_code}"
}
trap cleanup EXIT

pve_node_cmd() {
    if [[ "${RUN_NODE}" == "${LOCAL_NODE}" ]]; then
        "$@"
    else
        ssh "${PVE_SSH_OPTS[@]}" root@"${RUN_HOST}" "$@"
    fi
}

ensure_storage_free() {
    local available_kib
    available_kib="$(pve_node_cmd pvesm status --storage "${STORAGE}" | awk 'NR == 2 {print $6}')"
    if [[ -z "${available_kib}" || ! "${available_kib}" =~ ^[0-9]+$ ]]; then
        echo "Unable to determine free space for storage ${STORAGE}" >&2
        exit 1
    fi
    if (( available_kib < MIN_FREE_KIB )); then
        echo "Storage ${STORAGE} has ${available_kib} KiB free; need at least ${MIN_FREE_KIB} KiB" >&2
        exit 1
    fi
}

find_free_vmid() {
    local candidate
    for candidate in $(seq "${VMID_START}" "${VMID_END}"); do
        if ! compgen -G "/etc/pve/nodes/*/qemu-server/${candidate}.conf" >/dev/null; then
            echo "${candidate}"
            return 0
        fi
    done
    return 1
}

wait_for_agent() {
    for _ in $(seq 1 120); do
        if pve_node_cmd qm agent "${VMID}" ping >/dev/null 2>&1; then
            return 0
        fi
        sleep 5
    done
    return 1
}

get_vm_ip() {
    local payload
    payload="$(pve_node_cmd qm agent "${VMID}" network-get-interfaces)"
    python3 - <<'PY' "${payload}"
import json
import sys

data = json.loads(sys.argv[1])
if isinstance(data, dict):
    data = data.get("result", [])
for iface in data:
    for addr in iface.get("ip-addresses", []):
        ip = addr.get("ip-address", "")
        if addr.get("ip-address-type") == "ipv4" and ip.startswith("10.68.68."):
            print(ip)
            raise SystemExit(0)
raise SystemExit(1)
PY
}

create_ci_cloudinit_snippet() {
    if [[ ! -r "${SSH_PUBLIC_KEY_FILE}" ]]; then
        echo "Missing readable SSH public key: ${SSH_PUBLIC_KEY_FILE}" >&2
        exit 1
    fi

    CI_SNIPPET="/var/lib/vz/snippets/${VM_NAME}.yaml"
    pve_node_cmd install -d -m 0755 /var/lib/vz/snippets
    {
        printf '#cloud-config\n'
        printf 'user: root\n'
        printf 'disable_root: false\n'
        printf 'ssh_pwauth: false\n'
        printf 'ssh_authorized_keys:\n'
        printf '  - %s\n' "$(cat "${SSH_PUBLIC_KEY_FILE}")"
        printf 'package_update: true\n'
        printf 'packages:\n'
        printf '  - qemu-guest-agent\n'
        printf 'runcmd:\n'
        printf '  - systemctl enable --now qemu-guest-agent\n'
        printf '  - sed -i "s/^#\\?PermitRootLogin .*/PermitRootLogin prohibit-password/" /etc/ssh/sshd_config || true\n'
        printf '  - systemctl restart ssh || systemctl restart sshd || true\n'
    } | if [[ "${RUN_NODE}" == "${LOCAL_NODE}" ]]; then
        cat > "${CI_SNIPPET}"
        chmod 0644 "${CI_SNIPPET}"
    else
        ssh "${PVE_SSH_OPTS[@]}" root@"${RUN_HOST}" "cat > '${CI_SNIPPET}' && chmod 0644 '${CI_SNIPPET}'"
    fi
}

wait_for_ssh() {
    for _ in $(seq 1 60); do
        if ssh "${SSH_OPTS[@]}" root@"${VM_IP}" 'echo ok' >/dev/null 2>&1; then
            return 0
        fi
        sleep 5
    done
    return 1
}

copy_workspace() {
    ssh "${SSH_OPTS[@]}" root@"${VM_IP}" 'rm -rf /srv/www/pmacontrol && mkdir -p /srv/www/pmacontrol'
    if git -C "${WORKSPACE}" rev-parse --is-inside-work-tree >/dev/null 2>&1; then
        git -C "${WORKSPACE}" archive --format=tar HEAD \
            | ssh "${SSH_OPTS[@]}" root@"${VM_IP}" 'tar -xf - -C /srv/www/pmacontrol'
    else
        tar \
            --exclude=".git" \
            --exclude="vendor" \
            --exclude="tmp" \
            --exclude=".phpunit.result.cache" \
            -C "${WORKSPACE}" \
            -czf - . \
            | ssh "${SSH_OPTS[@]}" root@"${VM_IP}" 'tar -xzf - -C /srv/www/pmacontrol'
    fi
}

run_remote_install() {
    # shellcheck disable=SC2029
    ssh "${SSH_OPTS[@]}" root@"${VM_IP}" \
        "TARGET_OS='${TARGET_OS}' GIT_COMMIT='${GITHUB_SHA:-manual}' bash /srv/www/pmacontrol/ci/remote-install-and-test.sh"
}

if [[ -x "${WORKSPACE}/ci/cleanup-proxmox-ci-vms.sh" ]]; then
    "${WORKSPACE}/ci/cleanup-proxmox-ci-vms.sh" \
        --node "${RUN_HOST}" \
        --age-minutes "${PMACTRL_CI_CLEANUP_AGE_MINUTES:-30}"
fi

ensure_storage_free
VMID="$(find_free_vmid)"
VM_NAME="${VM_NAME_PREFIX}-${TARGET_OS}-${VMID}"
create_ci_cloudinit_snippet

log "cloning template ${TEMPLATE_ID} to VM ${VMID}"
clone_args=(qm clone "${TEMPLATE_ID}" "${VMID}" --name "${VM_NAME}" --full 1 --storage "${STORAGE}")
if [[ "${RUN_NODE}" != "${LOCAL_NODE}" ]]; then
    clone_args+=(--target "${RUN_NODE}")
fi
"${clone_args[@]}" >/dev/null
pve_node_cmd qm set "${VMID}" \
    --memory 4096 \
    --cores 4 \
    --balloon 0 \
    --net0 "virtio,bridge=${BRIDGE},firewall=1" \
    --ciuser root \
    --ipconfig0 ip=dhcp \
    --cicustom "user=local:snippets/${VM_NAME}.yaml" >/dev/null
pve_node_cmd qm start "${VMID}" >/dev/null

log "waiting for guest agent"
if ! wait_for_agent; then
    echo "Guest agent did not become ready for ${VMID}" >&2
    exit 1
fi

log "detecting VM IP"
for _ in $(seq 1 30); do
    if VM_IP="$(get_vm_ip 2>/dev/null)"; then
        break
    fi
    sleep 5
done

if [[ -z "${VM_IP}" ]]; then
    echo "Unable to determine VM IP for ${VMID}" >&2
    exit 1
fi

log "VM IP is ${VM_IP}"
log "waiting for SSH"
if ! wait_for_ssh; then
    echo "SSH did not become ready for ${VM_IP} (${VMID})" >&2
    exit 1
fi
log "SSH is ready"

log "copying workspace"
copy_workspace

log "running install and phpunit"
run_remote_install

RESULT="success"
log "target ${TARGET_OS} completed successfully"
