<?php

namespace Glial\Auth;

function ldap_get_entries($ldap, $result)
{
    $entries = \ldap_get_entries($ldap, $result);

    if (!is_array($entries)) {
        return $entries;
    }

    return normalizeLdapEntriesEncoding($entries);
}

function normalizeLdapEntriesEncoding(array $entries): array
{
    $textAttributes = array(
        'c',
        'givenname',
        'l',
        'mail',
        'samaccountname',
        'sn',
        'whencreated',
    );

    foreach ($entries as $entryKey => $entry) {
        if (!is_int($entryKey) || !is_array($entry)) {
            continue;
        }

        foreach ($textAttributes as $attribute) {
            if (empty($entry[$attribute]) || !is_array($entry[$attribute])) {
                continue;
            }

            foreach ($entry[$attribute] as $valueKey => $value) {
                if ($valueKey === 'count') {
                    continue;
                }

                $entries[$entryKey][$attribute][$valueKey] = normalizeLdapTextValue($value);
            }
        }
    }

    return $entries;
}

function normalizeLdapTextValue($value)
{
    if (!is_string($value) || $value === '' || mb_check_encoding($value, 'UTF-8')) {
        return $value;
    }

    foreach (array('Windows-1252', 'ISO-8859-1') as $encoding) {
        $converted = @mb_convert_encoding($value, 'UTF-8', $encoding);

        if (is_string($converted) && mb_check_encoding($converted, 'UTF-8')) {
            return $converted;
        }
    }

    return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
}
