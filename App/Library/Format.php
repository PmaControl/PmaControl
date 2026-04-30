<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

/**
 * Class responsible for format workflows.
 *
 * This class belongs to the PmaControl application layer and documents the
 * public surface consumed by controllers, services, static analysis tools and IDEs.
 *
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
class Format
{

/**
 * Handle format state through `bytes`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $bytes Input value for `bytes`.
 * @phpstan-param mixed $bytes
 * @psalm-param mixed $bytes
 * @param mixed $decimals Input value for `decimals`.
 * @phpstan-param mixed $decimals
 * @psalm-param mixed $decimals
 * @return mixed Returned value for bytes.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @see self::bytes()
 * @example /fr/format/bytes
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function bytes($bytes, $decimals = 2, $style = 'fr', array $options = array())
    {
        if (is_array($style)) {
            $options = $style;
            $style = isset($options['style']) ? (string)$options['style'] : 'fr';
        }

        if ($bytes === null || $bytes === '') {
            return (string)($options['empty'] ?? '');
        }

        if (!is_numeric($bytes)) {
            return (string)($options['not_numeric'] ?? '');
        }

        $bytes = (float)$bytes;
        if ($bytes == 0.0 && array_key_exists('zero', $options) && $options['zero'] !== null) {
            return (string)$options['zero'];
        }

        $parts = self::byteParts($bytes, (string)$style, $options);
        if ($parts === null) {
            return '';
        }

        $decimalSeparator = isset($options['decimal_separator']) ? (string)$options['decimal_separator'] : '.';
        $thousandsSeparator = isset($options['thousands_separator']) ? (string)$options['thousands_separator'] : '';
        $value = number_format($parts['value'], (int)$decimals, $decimalSeparator, $thousandsSeparator);
        if (!empty($options['trim_trailing_zeros']) && (int)$decimals > 0) {
            $value = rtrim(rtrim($value, '0'), $decimalSeparator);
        }

        return $value.' '.$parts['unit'];
    }

    static public function bytesOrEmpty($bytes, $decimals = 2, $style = 'fr', array $options = array())
    {
        if (empty($bytes)) {
            return '';
        }

        $options['empty'] = '';
        $options['not_numeric'] = '';

        return self::bytes($bytes, $decimals, $style, $options);
    }

    static public function bytesOrNa($bytes, $decimals = 2, $style = 'si', array $options = array())
    {
        $options['empty'] = 'n/a';
        $options['not_numeric'] = 'n/a';

        return self::bytes($bytes, $decimals, $style, $options);
    }

    static public function bytesZero($bytes, $decimals = 2, $style = 'fr', array $options = array())
    {
        if (!is_numeric($bytes) || (float)$bytes <= 0) {
            return (string)($options['zero'] ?? '0 o');
        }

        return self::bytes($bytes, $decimals, $style, $options);
    }

    static public function duration($seconds)
    {
        if ($seconds === null || $seconds === 'NULL' || $seconds === '') {
            return 'NULL';
        }

        $seconds = max(0, (int)$seconds);
        if ($seconds < 60) {
            return $seconds.'s';
        }
        if ($seconds < 3600) {
            return floor($seconds / 60).'m '.($seconds % 60).'s';
        }
        if ($seconds < 86400) {
            return floor($seconds / 3600).'h '.floor(($seconds % 3600) / 60).'m '.($seconds % 60).'s';
        }

        return floor($seconds / 86400).'d '.floor(($seconds % 86400) / 3600).'h '.floor(($seconds % 3600) / 60).'m '.($seconds % 60).'s';
    }

    static public function elapsedCompact($seconds)
    {
        $seconds = max(0, (int)$seconds);
        if ($seconds < 60) {
            return $seconds.'s';
        }
        if ($seconds < 3600) {
            return sprintf('%dm %02ds', intdiv($seconds, 60), $seconds % 60);
        }
        if ($seconds < 86400) {
            return sprintf('%dh %02dm', intdiv($seconds, 3600), intdiv($seconds % 3600, 60));
        }

        return sprintf('%dd %02dh', intdiv($seconds, 86400), intdiv($seconds % 86400, 3600));
    }

    static public function durationLong($seconds)
    {
        $seconds = max(0, (int)$seconds);

        return sprintf(
            '%d days, %d hours, %d minutes and %d seconds',
            intdiv($seconds, 86400),
            intdiv($seconds % 86400, 3600),
            intdiv($seconds % 3600, 60),
            $seconds % 60
        );
    }

    static public function byteParts($bytes, $style = 'fr', array $options = array())
    {
        if (!is_numeric($bytes)) {
            return null;
        }

        $bytes = (float)$bytes;
        $units = self::byteUnits((string)$style);
        $minUnit = isset($options['min_unit']) ? max(0, (int)$options['min_unit']) : 0;
        $factor = $bytes == 0.0 ? $minUnit : (int)floor(log(abs($bytes), 1024));
        $factor = min(count($units) - 1, max($minUnit, $factor));

        return array(
            'factor' => $factor,
            'unit' => $units[$factor],
            'value' => $bytes / pow(1024, $factor),
            'arrondi' => ceil($bytes / pow(1024, $factor)) * pow(1024, $factor),
        );
    }

    private static function byteUnits($style)
    {
        switch (strtolower($style)) {
            case 'si':
                return array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
            case 'iec':
                return array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
            case 'mysql':
                return array('', 'K', 'M', 'G', 'T', 'P', 'E');
            case 'fr':
            default:
                return array('o', 'Ko', 'Mo', 'Go', 'To', 'Po');
        }
    }

/**
 * Handle `mysqlVersion`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $version Input value for `version`.
 * @phpstan-param mixed $version
 * @psalm-param mixed $version
 * @param mixed $comment Input value for `comment`.
 * @phpstan-param mixed $comment
 * @psalm-param mixed $comment
 * @param bool $is_proxysql Input value for `is_proxysql`.
 * @phpstan-param bool $is_proxysql
 * @psalm-param bool $is_proxysql
 * @return mixed Returned value for mysqlVersion.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @example mysqlVersion(...);
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function mysqlVersion($version, $comment, $is_proxysql=0)
    {
        $format = self::getMySQLNumVersion($version, $comment);

        $fork = $format['fork'];
        $number = $format['number'];
        $enter = $format['enterprise'];

        $name = '';

        switch (strtolower($fork)) {
            case 'mariadb':
                $name .= '<span class="geek">&#xF130;</span>';
		        $name .=  ' MariaDB';
                if ($enter === true)
                {
                    $name .= ' (Enterprise)';
                }
                break;

            case 'percona':
                $name .= '<img title="Percona Server" alt="Percona Server" height="16" width="16" src="'.IMG.'/icon/percona.svg"/>';
		        $name .= ' Percona Server';
                //$name = 'percona';
                break;

            case 'proxysql':
                $name .= '<img title="ProxySQL" alt="ProxySQL" height="14" width="14" src="'.IMG.'/icon/proxysql.png"/>';
                $name .= ' ProxySQL';
                break;

            case 'mysql router':
                $name .= '<img title="MySQL Router" alt="MySQL Router" height="16" width="16" src="'.IMG.'/icon/router.svg"/>';
                $name .= ' MySQL Router';
                break;

            case 'maxscale':
                $name .= '<img title="MaxScale Server" alt="MaxScale Server" height="16" width="16" src="'.IMG.'/icon/maxscale.svg"/>';
                $name .= ' MaxScale';
                break; 

            case 'singlestore':
                $name .= '<img title="SingleStore" alt="SingleStore" height="16" width="16" src="'.IMG.'/icon/singlestore.svg"/>';
                $name .= ' SingleStore';
                break;

            default:
                $name .= '<span class="geek">&#xF137;</span>';
                $name .= ' MySQL';
        }

        return $name." ".$number;
    }


/**
 * Retrieve `getLogo`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $fork Input value for `fork`.
 * @phpstan-param mixed $fork
 * @psalm-param mixed $fork
 * @return mixed Returned value for getLogo.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @example getLogo(...);
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static function getLogo($fork)
    {

        switch($fork)
        {

            case 'mysql':
                $logo = '<span class="geek">&#xF137;</span>';
                break;

            case 'mariadb':
                $logo = '<span class="geek">&#xF130;</span>';
                break;
            case 'percona':
                $logo = '<img title="Percona Server" alt="Percona Server" height="16" width="16" src="'.IMG.'/icon/percona.svg"/>';
                break;

            case 'proxysql':
                $logo = '<img title="ProxySQL" alt="ProxySQL" height="14" width="14" src="'.IMG.'/icon/proxysql.png"/>';
                break;

            case 'mysql router':
                $logo = '<img title="MySQL Router" alt="MySQL Router" height="16" width="16" src="'.IMG.'/icon/router.svg"/>';
                break;

            case 'maxscale':
                $logo = '<img title="MaxScale" alt="MaxScale" height="16" width="16" src="'.IMG.'/icon/maxscale.png"/>';
                break;

            case 'singlestore':
                $logo = '<img title="SingleStore" alt="SingleStore" height="16" width="16" src="'.IMG.'/icon/singlestore.svg"/>';
                break;
        }

        return $logo;
    }

/**
 * Handle `ping`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $microtime Input value for `microtime`.
 * @phpstan-param mixed $microtime
 * @psalm-param mixed $microtime
 * @param mixed $precision Input value for `precision`.
 * @phpstan-param mixed $precision
 * @psalm-param mixed $precision
 * @return mixed Returned value for ping.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @example ping(...);
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function ping($microtime, $precision = 2)
    {
        $units = array('ms', 's');

        $microtime = $microtime * 1000;

        if ($microtime > 1000) {
            $microtime = $microtime / 1000;
            $pow       = 1;
        } else {
            $pow = 0;
        }

        return round($microtime, $precision).' '.$units[$pow];
    }

/**
 * Retrieve `getMySQLNumVersion`.
 *
 * This routine may read or mutate framework state, superglobals or persistence layers.
 *
 * @param mixed $version Input value for `version`.
 * @phpstan-param mixed $version
 * @psalm-param mixed $version
 * @param mixed $comment Input value for `comment`.
 * @phpstan-param mixed $comment
 * @psalm-param mixed $comment
 * @return mixed Returned value for getMySQLNumVersion.
 * @phpstan-return mixed
 * @psalm-return mixed
 * @example getMySQLNumVersion(...);
 * @category PmaControl
 * @package App
 * @subpackage Library
 * @author Aurélien LEQUOY <pmacontrol@68koncept.com>
 * @license GPL-3.0
 * @since 5.0
 * @version 1.0
 */
    static public function getMySQLNumVersion($version, $comment)
    {
        //10.6.19-15-MariaDB-enterprise-log
        // need make test with that
        $enterprise = false;

        if (strpos($version, "-") !== false) {
            $versionParts = explode("-", $version);
            $number = $versionParts[0];
            $second = $versionParts[1] ?? 'MySQL';

            if (preg_match('/^\d+$/', $second)) {
                $enterprise = true;
                $fork = !empty($versionParts[2]) ? $versionParts[2] : $second;
            } else {
                $fork = $second;
            }
        } else {
            $number = $version;
            $fork = 'MySQL';
        }

        $pos = strpos(strtolower($comment), "percona");
        if ($pos !== false) {
            $fork = "Percona";
        }

        $pos = strpos(strtolower($comment), "proxysql");
        if ($pos !== false) {
            $fork = "ProxySQL";
        }

        $pos = strpos(strtolower($comment), "mysql router");
        if ($pos !== false) {
            $fork = "MySQL Router";
        }

        $pos = strpos(strtolower($comment), "-router");
        if ($pos !== false) {
            $fork = "MySQL Router";
        }

        $pos = strpos(strtolower($comment), "maxscale");
        if ($pos !== false) {
            $fork = "MaxScale";
        }

        $pos = strpos(strtolower($comment), "singlestore");
        if ($pos !== false) {
            $fork = "SingleStore";
        }

        return array('number'=>$number, 'fork'=> $fork, 'enterprise'=> $enterprise);
    }
}
