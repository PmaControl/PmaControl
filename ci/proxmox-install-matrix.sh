#!/usr/bin/env bash
set -Eeuo pipefail

TARGET_OS="${1:?missing target os}"
WORKSPACE="${GITHUB_WORKSPACE:-$(pwd)}"
STORAGE="${PMACTRL_CI_STORAGE:-AS6510T-PVE1}"
BRIDGE="${PMACTRL_CI_BRIDGE:-vmbr0}"
SSH_OPTS=(-o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -o ConnectTimeout=5)
VM_NAME_PREFIX="ci-pmacontrol"
RESULT="failure"
KEEP_VM=1
VMID=""
VM_NAME=""
VM_IP=""

log() {
    printf '[ci:%s] %s\n' "${TARGET_OS}" "$*"
}

require_cmd() {
    command -v "$1" >/dev/null 2>&1 || {
        echo "Missing command: $1" >&2
        exit 1
    }
}

for cmd in qm ssh python3 tar git; do
    require_cmd "${cmd}"
done

case "${TARGET_OS}" in
    debian12)
        TEMPLATE_ID=920
        VMID_START=9300
        VMID_END=9399
        ;;
    debian13)
        TEMPLATE_ID=921
        VMID_START=9400
        VMID_END=9499
        ;;
    ubuntu2404)
        TEMPLATE_ID=922
        VMID_START=9500
        VMID_END=9599
        ;;
    *)
        echo "Unsupported target OS: ${TARGET_OS}" >&2
        exit 1
        ;;
esac

cleanup() {
    local exit_code=$?
    if [[ "${RESULT}" == "success" && -n "${VMID}" ]]; then
        log "destroying VM ${VMID}"
        qm stop "${VMID}" --skiplock 1 >/dev/null 2>&1 || true
        qm destroy "${VMID}" --purge 1 >/dev/null 2>&1 || true
    elif [[ -n "${VMID}" ]]; then
        log "keeping failed VM ${VMID} (${VM_NAME})"
        if [[ -n "${VM_IP}" ]]; then
            log "failed VM IP: ${VM_IP}"
        fi
    fi
    exit "${exit_code}"
}
trap cleanup EXIT

find_free_vmid() {
    local candidate
    for candidate in $(seq "${VMID_START}" "${VMID_END}"); do
        if ! qm config "${candidate}" >/dev/null 2>&1; then
            echo "${candidate}"
            return 0
        fi
    done
    return 1
}

wait_for_agent() {
    local count
    for count in $(seq 1 60); do
        if qm agent "${VMID}" ping >/dev/null 2>&1; then
            return 0
        fi
        sleep 5
    done
    return 1
}

get_vm_ip() {
    local payload
    payload="$(qm agent "${VMID}" network-get-interfaces)"
    python3 - <<'PY' "${payload}"
import json
import sys

data = json.loads(sys.argv[1])
for iface in data.get("result", []):
    for addr in iface.get("ip-addresses", []):
        ip = addr.get("ip-address", "")
        if addr.get("ip-address-type") == "ipv4" and ip.startswith("10.68.68."):
            print(ip)
            raise SystemExit(0)
raise SystemExit(1)
PY
}

wait_for_ssh() {
    local count
    for count in $(seq 1 60); do
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
    ssh "${SSH_OPTS[@]}" root@"${VM_IP}" \
        "TARGET_OS='${TARGET_OS}' GIT_COMMIT='${GITHUB_SHA:-manual}' bash /srv/www/pmacontrol/ci/remote-install-and-test.sh"
}

VMID="$(find_free_vmid)"
VM_NAME="${VM_NAME_PREFIX}-${TARGET_OS}-${VMID}"

log "cloning template ${TEMPLATE_ID} to VM ${VMID}"
qm clone "${TEMPLATE_ID}" "${VMID}" --name "${VM_NAME}" --full 1 --storage "${STORAGE}" >/dev/null
qm set "${VMID}" --memory 4096 --cores 4 --balloon 0 --net0 "virtio,bridge=${BRIDGE},firewall=1" >/dev/null
qm start "${VMID}" >/dev/null

log "waiting for guest agent"
wait_for_agent

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
wait_for_ssh

log "copying workspace"
copy_workspace

log "running install and phpunit"
run_remote_install

RESULT="success"
KEEP_VM=0
log "target ${TARGET_OS} completed successfully"
