#!/usr/bin/env bash
set -Eeuo pipefail

TARGET_OS="${1:?missing target os}"
WORKSPACE="${GITHUB_WORKSPACE:-$(pwd)}"
STORAGE="${PMACTRL_CI_STORAGE:-AS6510T-PVE1}"
BRIDGE="${PMACTRL_CI_BRIDGE:-vmbr0}"
SSH_OPTS=(-o BatchMode=yes -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -o ConnectTimeout=5)
PVE_SSH_OPTS=(-o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=10)
VM_NAME_PREFIX="ci-pmacontrol"
VM_MEMORY_MB="${PMACTRL_CI_VM_MEMORY_MB:-4096}"
VM_NETMASK="${PMACTRL_CI_NETMASK:-24}"
VM_GATEWAY="${PMACTRL_CI_GATEWAY:-10.68.68.1}"
VM_FIREWALL="${PMACTRL_CI_FIREWALL:-0}"
CI_STATIC_IP_PREFIX="${PMACTRL_CI_STATIC_IP_PREFIX:-10.68.68}"
CI_STATIC_IP_START="${PMACTRL_CI_STATIC_IP_START:-39}"
CI_STATIC_IP_COUNT="${PMACTRL_CI_STATIC_IP_COUNT:-8}"
CI_STATIC_IPS_PER_TARGET="${PMACTRL_CI_STATIC_IPS_PER_TARGET:-2}"
CI_CANDIDATE_NODES="${PMACTRL_CI_NODES:-pve-2 pve-3}"
CI_MAX_RAM_PCT="${PMACTRL_CI_MAX_RAM_PCT:-80}"
CI_MAX_CPU_PCT="${PMACTRL_CI_MAX_CPU_PCT:-60}"
CI_SCHEDULER_LOCK_FILE="${PMACTRL_CI_SCHEDULER_LOCK_FILE:-/run/lock/pmacontrol-proxmox-scheduler.lock}"
RESULT="failure"
LOCK_WAIT_SECONDS="${PMACTRL_CI_LOCK_WAIT_SECONDS:-21600}"
MIN_FREE_KIB="${PMACTRL_CI_MIN_FREE_KIB:-5242880}"
KEEP_FAILED_VM="${PMACTRL_CI_KEEP_FAILED_VM:-0}"
SSH_PUBLIC_KEY_FILE="${PMACTRL_CI_SSH_PUBLIC_KEY_FILE:-/root/.ssh/id_rsa.pub}"
LOCAL_NODE="$(hostname -s)"
RUN_NODE=""
RUN_HOST=""
STATIC_VM_IP=""
STATIC_VM_IP_SLOT_START=""
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

for cmd in qm ssh python3 tar git flock pvesm pvesh install; do
    require_cmd "${cmd}"
done

ci_static_ip() {
    local offset="$1"
    local last_octet

    if (( offset < 0 || offset >= CI_STATIC_IP_COUNT )); then
        echo "Static CI IP slot ${offset} is outside configured pool size ${CI_STATIC_IP_COUNT}" >&2
        exit 1
    fi

    last_octet=$((CI_STATIC_IP_START + offset))
    if (( last_octet < 1 || last_octet > 254 )); then
        echo "Static CI IP ${CI_STATIC_IP_PREFIX}.${last_octet} is outside a usable IPv4 host range" >&2
        exit 1
    fi

    printf '%s.%d' "${CI_STATIC_IP_PREFIX}" "${last_octet}"
}

resolve_static_vm_ip() {
    local vmid_offset
    local slot

    if [[ -n "${STATIC_VM_IP}" ]]; then
        return 0
    fi

    vmid_offset=$((VMID - VMID_START))
    slot=$((STATIC_VM_IP_SLOT_START + (vmid_offset % CI_STATIC_IPS_PER_TARGET)))
    STATIC_VM_IP="$(ci_static_ip "${slot}")"
}

case "${TARGET_OS}" in
    debian12)
        TEMPLATE_ID=920
        VMID_START=9300
        VMID_END=9399
        STATIC_VM_IP="${PMACTRL_CI_DEBIAN12_IP:-}"
        STATIC_VM_IP_SLOT_START=0
        ;;
    debian13)
        TEMPLATE_ID=921
        VMID_START=9400
        VMID_END=9499
        STATIC_VM_IP="${PMACTRL_CI_DEBIAN13_IP:-}"
        STATIC_VM_IP_SLOT_START=2
        ;;
    ubuntu2404)
        TEMPLATE_ID=922
        VMID_START=9500
        VMID_END=9599
        STATIC_VM_IP="${PMACTRL_CI_UBUNTU2404_IP:-}"
        STATIC_VM_IP_SLOT_START=4
        ;;
    ubuntu2604)
        TEMPLATE_ID="${PMACTRL_CI_UBUNTU2604_TEMPLATE_ID:-923}"
        VMID_START=9600
        VMID_END=9699
        STATIC_VM_IP="${PMACTRL_CI_UBUNTU2604_IP:-}"
        STATIC_VM_IP_SLOT_START=6
        ;;
    *)
        echo "Unsupported target OS: ${TARGET_OS}" >&2
        exit 1
        ;;
esac

node_host() {
    local node="$1"

    if [[ -n "${PMACTRL_CI_NODE_HOST:-}" && "${node}" == "${PMACTRL_CI_NODE:-}" ]]; then
        printf '%s\n' "${PMACTRL_CI_NODE_HOST}"
        return 0
    fi

    case "${node}" in
        pve-1)
            printf '%s\n' "${PMACTRL_CI_PVE1_HOST:-10.68.68.120}"
            ;;
        pve-2)
            printf '%s\n' "${PMACTRL_CI_PVE2_HOST:-10.68.68.121}"
            ;;
        pve-3)
            printf '%s\n' "${PMACTRL_CI_PVE3_HOST:-10.68.68.122}"
            ;;
        *)
            printf '%s\n' "${node}"
            ;;
    esac
}

set_run_host() {
    RUN_HOST="$(node_host "${RUN_NODE}")"
}

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
    local candidate slot used_slots
    used_slots=" $(target_active_slots) "

    for slot in $(seq 0 $((CI_STATIC_IPS_PER_TARGET - 1))); do
        if [[ "${used_slots}" == *" ${slot} "* ]]; then
            continue
        fi

        for ((candidate = VMID_START + slot; candidate <= VMID_END; candidate += CI_STATIC_IPS_PER_TARGET)); do
            if vmid_has_config "${candidate}"; then
                continue
            fi
            if vmid_has_storage_images "${candidate}"; then
                printf '[ci:%s:%s] skipping VMID %s: storage %s still has image volumes\n' \
                    "${TARGET_OS}" "${RUN_NODE:-unassigned}" "${candidate}" "${STORAGE}" >&2
                continue
            fi
            echo "${candidate}"
            return 0
        done
    done

    return 1
}

vmid_has_config() {
    local candidate="$1"

    compgen -G "/etc/pve/nodes/*/qemu-server/${candidate}.conf" >/dev/null
}

vmid_has_storage_images() {
    local candidate="$1"

    pvesm list "${STORAGE}" --vmid "${candidate}" 2>/dev/null |
        awk 'NR > 1 && $3 == "images" {found = 1} END {exit found ? 0 : 1}'
}

candidate_nodes() {
    local node nodes

    if [[ -n "${PMACTRL_CI_NODE:-}" ]]; then
        printf '%s\n' "${PMACTRL_CI_NODE}"
    else
        nodes="${CI_CANDIDATE_NODES//,/ }"
        for node in ${nodes}; do
            printf '%s\n' "${node}"
        done
    fi
}

cleanup_stale_ci_vms() {
    local node host

    if [[ ! -r "${WORKSPACE}/ci/cleanup-proxmox-ci-vms.sh" ]]; then
        printf '[ci:%s:scheduler] cleanup script not readable; skipping stale CI VM cleanup\n' "${TARGET_OS}" >&2
        return 0
    fi

    for node in $(candidate_nodes); do
        host="$(node_host "${node}")"
        printf '[ci:%s:scheduler] cleaning stale CI VMs on %s (%s)\n' "${TARGET_OS}" "${node}" "${host}"
        if ! bash "${WORKSPACE}/ci/cleanup-proxmox-ci-vms.sh" \
            --node "${host}" \
            --age-minutes "${PMACTRL_CI_CLEANUP_AGE_MINUTES:-30}"; then
            printf '[ci:%s:scheduler] cleanup warning on %s (%s); continuing with scheduler checks\n' "${TARGET_OS}" "${node}" "${host}" >&2
        fi
    done
}

target_ci_vm_count() {
    local resources_file
    resources_file="$(mktemp)"
    pvesh get /cluster/resources --type vm --output-format json > "${resources_file}"
    python3 - "${resources_file}" "${VM_NAME_PREFIX}" "${TARGET_OS}" <<'PY'
import json
import sys

with open(sys.argv[1], "r", encoding="utf-8") as handle:
    resources = json.load(handle)
prefix = f"{sys.argv[2]}-{sys.argv[3]}-"
count = 0
for item in resources:
    name = item.get("name") or ""
    if name.startswith(prefix) and not item.get("template") and item.get("status") != "stopped":
        count += 1
print(count)
PY
    rm -f "${resources_file}"
}

target_active_slots() {
    local resources_file
    resources_file="$(mktemp)"
    pvesh get /cluster/resources --type vm --output-format json > "${resources_file}"
    python3 - \
        "${resources_file}" \
        "${VM_NAME_PREFIX}" \
        "${TARGET_OS}" \
        "${VMID_START}" \
        "${VMID_END}" \
        "${CI_STATIC_IPS_PER_TARGET}" <<'PY'
import json
import sys

with open(sys.argv[1], "r", encoding="utf-8") as handle:
    resources = json.load(handle)
prefix = f"{sys.argv[2]}-{sys.argv[3]}-"
vmid_start = int(sys.argv[4])
vmid_end = int(sys.argv[5])
slots_per_target = int(sys.argv[6])
slots = set()

for item in resources:
    name = item.get("name") or ""
    if not name.startswith(prefix) or item.get("template") or item.get("status") == "stopped":
        continue
    vmid = int(item.get("vmid") or 0)
    if vmid_start <= vmid <= vmid_end:
        slots.add((vmid - vmid_start) % slots_per_target)

print(" ".join(str(slot) for slot in sorted(slots)))
PY
    rm -f "${resources_file}"
}

ensure_target_slot_available() {
    local count
    count="$(target_ci_vm_count)"
    if (( count >= CI_STATIC_IPS_PER_TARGET )); then
        echo "Target ${TARGET_OS} already has ${count} active CI VM(s); static IP slots available: ${CI_STATIC_IPS_PER_TARGET}" >&2
        exit 1
    fi
}

select_run_node() {
    local status_file resources_file selection node_cpu_pct node_ram_pct node_projected_ram_pct node_ci_count node_score

    status_file="$(mktemp)"
    resources_file="$(mktemp)"
    pvesh get /nodes --output-format json > "${status_file}"
    pvesh get /cluster/resources --type vm --output-format json > "${resources_file}"
    selection="$(
        python3 - \
            "${status_file}" \
            "${resources_file}" \
            "${CI_CANDIDATE_NODES}" \
            "${PMACTRL_CI_NODE:-}" \
            "${CI_MAX_RAM_PCT}" \
            "${CI_MAX_CPU_PCT}" \
            "${VM_NAME_PREFIX}" \
            "${VM_MEMORY_MB}" <<'PY'
import json
import sys

with open(sys.argv[1], "r", encoding="utf-8") as handle:
    nodes = {item.get("node"): item for item in json.load(handle)}
with open(sys.argv[2], "r", encoding="utf-8") as handle:
    resources = json.load(handle)
configured_candidates = [node for node in sys.argv[3].replace(",", " ").split() if node]
forced_node = sys.argv[4].strip()
max_ram_pct = float(sys.argv[5])
max_cpu_pct = float(sys.argv[6])
prefix = f"{sys.argv[7]}-"
vm_memory_bytes = float(sys.argv[8]) * 1024.0 * 1024.0
candidates = [forced_node] if forced_node else configured_candidates

if not candidates:
    print("No Proxmox candidate nodes configured", file=sys.stderr)
    raise SystemExit(1)

active_ci_by_node = {node: 0 for node in candidates}
for item in resources:
    name = item.get("name") or ""
    node = item.get("node")
    if node in active_ci_by_node and name.startswith(prefix) and item.get("status") != "stopped":
        active_ci_by_node[node] += 1

eligible = []
rejected = []
for node in candidates:
    info = nodes.get(node)
    if not info:
        rejected.append(f"{node}: missing node status")
        continue
    if info.get("status") != "online":
        rejected.append(f"{node}: status={info.get('status')}")
        continue

    maxmem = float(info.get("maxmem") or 0)
    mem = float(info.get("mem") or 0)
    cpu_pct = float(info.get("cpu") or 0) * 100.0
    ram_pct = (mem / maxmem * 100.0) if maxmem > 0 else 100.0
    projected_ram_pct = ((mem + vm_memory_bytes) / maxmem * 100.0) if maxmem > 0 else 100.0
    active_ci = active_ci_by_node.get(node, 0)

    if ram_pct > max_ram_pct:
        rejected.append(f"{node}: RAM {ram_pct:.1f}% > {max_ram_pct:.1f}%")
        continue
    if projected_ram_pct > max_ram_pct:
        rejected.append(f"{node}: RAM after CI VM {projected_ram_pct:.1f}% > {max_ram_pct:.1f}%")
        continue
    if cpu_pct > max_cpu_pct:
        rejected.append(f"{node}: CPU {cpu_pct:.1f}% > {max_cpu_pct:.1f}%")
        continue

    score = (active_ci * 1000.0) + projected_ram_pct + cpu_pct
    eligible.append((score, projected_ram_pct, ram_pct, cpu_pct, active_ci, node))

if not eligible:
    print("No eligible Proxmox node for CI launch", file=sys.stderr)
    for reason in rejected:
        print(f" - {reason}", file=sys.stderr)
    raise SystemExit(1)

score, projected_ram_pct, ram_pct, cpu_pct, active_ci, node = min(eligible)
print(f"{node} {cpu_pct:.1f} {ram_pct:.1f} {projected_ram_pct:.1f} {active_ci} {score:.1f}")
PY
    )"
    rm -f "${status_file}" "${resources_file}"

    read -r RUN_NODE node_cpu_pct node_ram_pct node_projected_ram_pct node_ci_count node_score <<< "${selection}"
    set_run_host
    log "selected Proxmox node ${RUN_NODE} (${RUN_HOST}): cpu=${node_cpu_pct}%, ram=${node_ram_pct}% projected=${node_projected_ram_pct}%, active_ci=${node_ci_count}, score=${node_score}"
}

allocate_and_start_vm() {
    log "waiting for CI scheduler lock ${CI_SCHEDULER_LOCK_FILE}"
    exec 9>"${CI_SCHEDULER_LOCK_FILE}"
    if ! flock -w "${LOCK_WAIT_SECONDS}" 9; then
        echo "Unable to acquire CI scheduler lock ${CI_SCHEDULER_LOCK_FILE}" >&2
        exit 1
    fi

    cleanup_stale_ci_vms
    select_run_node
    ensure_storage_free
    ensure_target_slot_available

    if ! VMID="$(find_free_vmid)"; then
        echo "Unable to find a free VMID/IP slot for target ${TARGET_OS}" >&2
        exit 1
    fi
    VM_NAME="${VM_NAME_PREFIX}-${TARGET_OS}-${VMID}"
    resolve_static_vm_ip
    create_ci_cloudinit_snippet

    log "cloning template ${TEMPLATE_ID} to VM ${VMID}"
    clone_args=(qm clone "${TEMPLATE_ID}" "${VMID}" --name "${VM_NAME}" --full 1 --storage "${STORAGE}")
    if [[ "${RUN_NODE}" != "${LOCAL_NODE}" ]]; then
        clone_args+=(--target "${RUN_NODE}")
    fi
    "${clone_args[@]}" >/dev/null
    log "using static IPv4 ${STATIC_VM_IP}/${VM_NETMASK} via ${VM_GATEWAY}"
    pve_node_cmd qm set "${VMID}" \
        --memory "${VM_MEMORY_MB}" \
        --cores 4 \
        --balloon 0 \
        --net0 "virtio,bridge=${BRIDGE},firewall=${VM_FIREWALL}" \
        --ciuser root \
        --ipconfig0 "ip=${STATIC_VM_IP}/${VM_NETMASK},gw=${VM_GATEWAY}" \
        --cicustom "user=local:snippets/${VM_NAME}.yaml" >/dev/null
    pve_node_cmd qm start "${VMID}" >/dev/null

    flock -u 9
    exec 9>&-
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
    python3 - <<'PY' "${payload}" "${STATIC_VM_IP}"
import json
import sys

data = json.loads(sys.argv[1])
expected_ip = sys.argv[2]
if isinstance(data, dict):
    data = data.get("result", [])
for iface in data:
    for addr in iface.get("ip-addresses", []):
        ip = addr.get("ip-address", "")
        if addr.get("ip-address-type") == "ipv4" and ip == expected_ip:
            print(ip)
            raise SystemExit(0)
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

allocate_and_start_vm

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
