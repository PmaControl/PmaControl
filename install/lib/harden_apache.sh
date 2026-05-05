#!/usr/bin/env bash

pmactrl_apache_log()
{
    if declare -F log >/dev/null 2>&1; then
        log "$*"
        return 0
    fi

    printf '[pmacontrol-apache] %s\n' "$*"
}

pmactrl_apache_run()
{
    if declare -F run >/dev/null 2>&1; then
        run "$@"
        return $?
    fi

    if pmactrl_apache_is_dry_run; then
        printf '[pmacontrol-apache:dry-run]'
        printf ' %q' "$@"
        printf '\n'
        return 0
    fi

    "$@"
}

pmactrl_apache_is_dry_run()
{
    [[ "${PMACTRL_DRY_RUN:-0}" == "1" ]]
}

pmactrl_apache_should_harden_docroot()
{
    case "${PMACTRL_HARDEN_APACHE_DOCROOT:-1}" in
        "1"|"yes"|"true"|"on")
            return 0
        ;;
        "0"|"no"|"false"|"off")
            return 1
        ;;
        *)
            echo "Invalid PMACTRL_HARDEN_APACHE_DOCROOT=${PMACTRL_HARDEN_APACHE_DOCROOT}. Use 1 or 0." >&2
            exit 1
        ;;
    esac
}

pmactrl_apache_docroot_conf_source()
{
    local helper_dir
    helper_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

    printf '%s\n' "${PMACTRL_APACHE_DOCROOT_CONF_SOURCE:-${helper_dir}/../apache/pmacontrol-docroot.conf}"
}

pmactrl_apache_install_conf()
{
    local source="$1"
    local target="$2"

    if [[ ! -f "${source}" ]]; then
        echo "Missing Apache hardening template: ${source}" >&2
        exit 1
    fi

    if pmactrl_apache_is_dry_run; then
        pmactrl_apache_run install -d -m 0755 "$(dirname "${target}")"
        pmactrl_apache_run install -m 0644 "${source}" "${target}"
        pmactrl_apache_log "would install ${target}"
        return 0
    fi

    pmactrl_apache_run install -d -m 0755 "$(dirname "${target}")"
    pmactrl_apache_run install -m 0644 "${source}" "${target}"
    pmactrl_apache_log "installed ${target}"
}

pmactrl_harden_apache_docroot()
{
    local source
    local target="/etc/apache2/conf-available/pmacontrol-docroot.conf"

    if ! pmactrl_apache_should_harden_docroot; then
        pmactrl_apache_log "skipping Apache docroot hardening because PMACTRL_HARDEN_APACHE_DOCROOT=0"
        return 0
    fi

    source="$(pmactrl_apache_docroot_conf_source)"
    pmactrl_apache_install_conf "${source}" "${target}"
    pmactrl_apache_run a2enconf "pmacontrol-docroot"
}

pmactrl_harden_httpd_docroot()
{
    local source
    local target="/etc/httpd/conf.d/pmacontrol-docroot.conf"

    if ! pmactrl_apache_should_harden_docroot; then
        pmactrl_apache_log "skipping Apache docroot hardening because PMACTRL_HARDEN_APACHE_DOCROOT=0"
        return 0
    fi

    source="$(pmactrl_apache_docroot_conf_source)"
    pmactrl_apache_install_conf "${source}" "${target}"
}
