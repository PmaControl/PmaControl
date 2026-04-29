#!/usr/bin/env bash

pmactrl_network_log()
{
    if declare -F log >/dev/null 2>&1; then
        log "$*"
        return 0
    fi

    printf '[pmacontrol-network] %s\n' "$*"
}

pmactrl_network_run()
{
    if declare -F run >/dev/null 2>&1; then
        run "$@"
        return $?
    fi

    "$@"
}

pmactrl_network_is_dry_run()
{
    [[ "${PMACTRL_DRY_RUN:-0}" == "1" ]]
}

pmactrl_network_is_local_db_host()
{
    local db_host="${PMACTRL_DB_HOST:-127.0.0.1}"

    case "${db_host}" in
        ""|"127.0.0.1"|"localhost"|"::1"|"[::1]")
            return 0
        ;;
    esac

    return 1
}

pmactrl_network_should_harden_db_bind()
{
    case "${PMACTRL_HARDEN_DB_BIND:-1}" in
        "1"|"yes"|"true"|"on")
            return 0
        ;;
        "0"|"no"|"false"|"off")
            return 1
        ;;
        "auto"|"")
            pmactrl_network_is_local_db_host
            return $?
        ;;
        *)
            echo "Invalid PMACTRL_HARDEN_DB_BIND=${PMACTRL_HARDEN_DB_BIND}. Use auto, 1, or 0." >&2
            exit 1
        ;;
    esac
}

pmactrl_network_validate_bind_address()
{
    local bind_address="${PMACTRL_DB_BIND_ADDRESS:-127.0.0.1,::1}"

    if [[ ! "${bind_address}" =~ ^(\*|[A-Za-z0-9_.:-]+)(,(\*|[A-Za-z0-9_.:-]+))*$ ]]; then
        echo "Invalid PMACTRL_DB_BIND_ADDRESS=${bind_address}. Use comma-separated IPs/hosts without spaces." >&2
        exit 1
    fi
}

pmactrl_network_write_file_if_changed()
{
    local target="$1"
    local content="$2"
    local tmp_file

    tmp_file=$(mktemp)
    printf '%s' "${content}" > "${tmp_file}"

    if [[ -f "${target}" ]] && cmp -s "${tmp_file}" "${target}"; then
        rm -f "${tmp_file}"
        return 1
    fi

    pmactrl_network_run install -d -m 0755 "$(dirname "${target}")"
    pmactrl_network_run install -m 0644 "${tmp_file}" "${target}"
    rm -f "${tmp_file}"
    return 0
}

pmactrl_harden_mariadb_bind()
{
    local bind_address="${PMACTRL_DB_BIND_ADDRESS:-127.0.0.1,::1}"
    local target="/etc/mysql/mariadb.conf.d/90-pmacontrol-network.cnf"
    local content

    if ! pmactrl_network_should_harden_db_bind; then
        pmactrl_network_log "skipping MariaDB bind-address hardening for PMACTRL_DB_HOST=${PMACTRL_DB_HOST:-127.0.0.1}"
        return 0
    fi

    pmactrl_network_validate_bind_address
    content="[mysqld]
bind-address = ${bind_address}
"

    if pmactrl_network_is_dry_run; then
        pmactrl_network_run install -d -m 0755 "$(dirname "${target}")"
        pmactrl_network_log "would write ${target} with bind-address ${bind_address}"
        pmactrl_network_run systemctl restart mariadb
        return 0
    fi

    if pmactrl_network_write_file_if_changed "${target}" "${content}"; then
        pmactrl_network_log "updated ${target}; restarting MariaDB"
        pmactrl_network_run systemctl restart mariadb
        return 0
    fi

    pmactrl_network_log "${target} already up to date; MariaDB restart skipped"
}

pmactrl_network_rpcbind_units()
{
    local unit

    for unit in rpcbind.socket rpcbind.service; do
        if systemctl list-unit-files --no-legend "${unit}" 2>/dev/null | awk '{print $1}' | grep -Fxq "${unit}"; then
            printf '%s\n' "${unit}"
        fi
    done
}

pmactrl_network_has_nfs_common()
{
    dpkg-query -W -f='${Status}' nfs-common 2>/dev/null | grep -Fxq 'install ok installed'
}

pmactrl_harden_rpcbind()
{
    local policy="${PMACTRL_RPCBIND_POLICY:-disable}"
    local rpcbind_units=()
    local unit

    case "${policy}" in
        "leave")
            pmactrl_network_log "leaving rpcbind unchanged because PMACTRL_RPCBIND_POLICY=leave"
            return 0
        ;;
        "disable"|"mask")
        ;;
        *)
            echo "Invalid PMACTRL_RPCBIND_POLICY=${policy}. Use disable, mask, or leave." >&2
            exit 1
        ;;
    esac

    if pmactrl_network_is_dry_run; then
        if [[ "${policy}" == "mask" ]]; then
            pmactrl_network_run systemctl mask --now rpcbind.socket rpcbind.service
        else
            pmactrl_network_run systemctl disable --now rpcbind.socket rpcbind.service
        fi
        return 0
    fi

    while IFS= read -r unit; do
        rpcbind_units+=("${unit}")
    done < <(pmactrl_network_rpcbind_units)

    if [[ ${#rpcbind_units[@]} -eq 0 ]]; then
        pmactrl_network_log "rpcbind systemd units not installed; nothing to harden"
        return 0
    fi

    if [[ "${policy}" == "disable" ]] && pmactrl_network_has_nfs_common; then
        pmactrl_network_log "nfs-common is installed; leaving rpcbind enabled. Set PMACTRL_RPCBIND_POLICY=mask to override."
        return 0
    fi

    if [[ "${policy}" == "mask" ]]; then
        pmactrl_network_run systemctl mask --now "${rpcbind_units[@]}" || true
        return 0
    fi

    pmactrl_network_run systemctl disable --now "${rpcbind_units[@]}" || true
}
