#!/usr/bin/env bash

pmactrl_install_generate_password()
{
    local generated=""

    if command -v openssl >/dev/null 2>&1; then
        generated=$(openssl rand -base64 48 | tr -d '/+=' | tr -d '\n' | head -c 32 || true)
    fi

    if [[ ${#generated} -lt 32 ]]; then
        generated=$(LC_ALL=C tr -dc 'A-Za-z0-9' < /dev/urandom | head -c 32 || true)
    fi

    if [[ ${#generated} -lt 32 ]]; then
        echo "Unable to generate a secure password." >&2
        exit 1
    fi

    printf '%s\n' "${generated}"
}

pmactrl_install_cleanup_ssh_key()
{
    local key_dir="${PMACTRL_INSTALL_SSH_KEY_DIR:-${SSH_KEY_DIR:-}}"

    if [[ -n "${key_dir}" && -d "${key_dir}" ]]; then
        rm -rf "${key_dir}"
    fi

    PMACTRL_INSTALL_SSH_KEY_DIR=""
    PMACTRL_INSTALL_SSH_PRIVATE_KEY_FILE=""
    PMACTRL_INSTALL_SSH_PUBLIC_KEY_FILE=""
    SSH_KEY_DIR=""
    SSH_PRIVATE_KEY_FILE=""
    SSH_PUBLIC_KEY_FILE=""
}

pmactrl_install_json_escape_string()
{
    printf '%s' "$1" | jq -Rs .
}

pmactrl_install_json_escape_file()
{
    jq -Rs . < "$1"
}

pmactrl_install_generate_ssh_key()
{
    local hostname_value

    pmactrl_install_cleanup_ssh_key
    PMACTRL_INSTALL_SSH_KEY_DIR=$(mktemp -d /tmp/pmacontrol-install-ssh.XXXXXX)
    chmod 700 "${PMACTRL_INSTALL_SSH_KEY_DIR}"
    PMACTRL_INSTALL_SSH_PRIVATE_KEY_FILE="${PMACTRL_INSTALL_SSH_KEY_DIR}/id_rsa"
    PMACTRL_INSTALL_SSH_PUBLIC_KEY_FILE="${PMACTRL_INSTALL_SSH_PRIVATE_KEY_FILE}.pub"
    hostname_value=$(hostname -f 2>/dev/null || hostname)

    ssh-keygen -q -t rsa -b 4096 -m PEM -N "" -C "pmacontrol@${hostname_value}" -f "${PMACTRL_INSTALL_SSH_PRIVATE_KEY_FILE}"
    chmod 600 "${PMACTRL_INSTALL_SSH_PRIVATE_KEY_FILE}"
    chmod 644 "${PMACTRL_INSTALL_SSH_PUBLIC_KEY_FILE}"

    # shellcheck disable=SC2034
    SSH_KEY_DIR="${PMACTRL_INSTALL_SSH_KEY_DIR}"
    # shellcheck disable=SC2034
    SSH_PRIVATE_KEY_FILE="${PMACTRL_INSTALL_SSH_PRIVATE_KEY_FILE}"
    # shellcheck disable=SC2034
    SSH_PUBLIC_KEY_FILE="${PMACTRL_INSTALL_SSH_PUBLIC_KEY_FILE}"
}

generate_password()
{
    pmactrl_install_generate_password
}

cleanup_install_ssh_key()
{
    pmactrl_install_cleanup_ssh_key
}

json_escape_string()
{
    pmactrl_install_json_escape_string "$1"
}

json_escape_file()
{
    pmactrl_install_json_escape_file "$1"
}

generate_install_ssh_key()
{
    pmactrl_install_generate_ssh_key
}
