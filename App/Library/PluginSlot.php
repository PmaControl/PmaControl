<?php

namespace App\Library;

use Glial\Sgbd\Sgbd;

/**
 * Plugin extension point registry.
 *
 * Plugins declare hooks under a named slot (e.g. `server.main.column.head`)
 * via their `plugin.json` "extensions" field. PluginPackage::install routes
 * each entry through self::register; PluginPackage::uninstall calls
 * self::unregisterAll($pluginId). Core views read the slot at render time:
 *
 *   foreach (PluginSlot::partials('server.main.column.head') as $p) include $p;
 *   $data = PluginSlot::apply('server.main.data', $data);
 *
 * The registry is backed by the `plugin_extension` table; rows are removed
 * when the plugin is uninstalled (FK ON DELETE CASCADE on `plugin_main`).
 */
class PluginSlot
{
    const KIND_PARTIAL = 'partial';
    const KIND_CALLBACK = 'callback';

    /** @var array<string, array<int, array{kind:string,payload:string,plugin_id:int}>> */
    private static $cache = array();

    public static function register($pluginId, $slot, $kind, $payload, $order = 100)
    {
        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "INSERT INTO plugin_extension (plugin_id, slot, kind, payload, sort_order) VALUES ("
            .((int)$pluginId).", '"
            .$db->sql_real_escape_string((string)$slot)."', '"
            .$db->sql_real_escape_string((string)$kind)."', '"
            .$db->sql_real_escape_string((string)$payload)."', "
            .((int)$order).")";
        $db->sql_query($sql);
        self::$cache = array();
    }

    public static function unregisterAll($pluginId)
    {
        $db = Sgbd::sql(DB_DEFAULT);
        $db->sql_query("DELETE FROM plugin_extension WHERE plugin_id = ".(int)$pluginId);
        self::$cache = array();
    }

    /**
     * @return array<int, string> absolute filesystem paths of partial view fragments
     */
    public static function partials($slot)
    {
        $entries = self::entries($slot, self::KIND_PARTIAL);
        $paths = array();
        foreach ($entries as $entry) {
            $path = self::resolvePath($entry['payload']);
            if ($path !== '' && is_file($path)) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    /**
     * @return array<int, callable>
     */
    public static function callbacks($slot)
    {
        $entries = self::entries($slot, self::KIND_CALLBACK);
        $callables = array();
        foreach ($entries as $entry) {
            $callable = self::resolveCallable($entry['payload']);
            if (is_callable($callable)) {
                $callables[] = $callable;
            }
        }

        return $callables;
    }

    /**
     * Chains every registered callback on a slot, passing the running value
     * along. Callbacks receive (mixed $value, array $context) and must return
     * the updated $value.
     *
     * @param string $slot
     * @param mixed  $value
     * @param array<string,mixed> $context
     * @return mixed
     */
    public static function apply($slot, $value, array $context = array())
    {
        foreach (self::callbacks($slot) as $callable) {
            $value = call_user_func($callable, $value, $context);
        }

        return $value;
    }

    /**
     * @return array<int, array{kind:string,payload:string,plugin_id:int}>
     */
    private static function entries($slot, $kind = null)
    {
        if (!array_key_exists($slot, self::$cache)) {
            self::$cache[$slot] = self::loadSlot($slot);
        }

        if ($kind === null) {
            return self::$cache[$slot];
        }

        $rows = array();
        foreach (self::$cache[$slot] as $row) {
            if ($row['kind'] === $kind) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * @return array<int, array{kind:string,payload:string,plugin_id:int}>
     */
    private static function loadSlot($slot)
    {
        $db = Sgbd::sql(DB_DEFAULT);
        $sql = "SELECT pe.plugin_id, pe.kind, pe.payload, pe.sort_order
                FROM plugin_extension pe
                INNER JOIN plugin_main pm ON pm.id = pe.plugin_id
                WHERE pe.slot = '".$db->sql_real_escape_string((string)$slot)."'
                  AND pm.est_actif = 1
                ORDER BY pe.sort_order ASC, pe.id ASC";
        $res = $db->sql_query($sql);

        $rows = array();
        while ($row = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $rows[] = array(
                'plugin_id' => (int)$row['plugin_id'],
                'kind' => (string)$row['kind'],
                'payload' => (string)$row['payload'],
            );
        }

        return $rows;
    }

    private static function resolvePath($payload)
    {
        $payload = trim((string)$payload);
        if ($payload === '') {
            return '';
        }

        if ($payload[0] === '/') {
            return $payload;
        }

        if (defined('ROOT')) {
            return rtrim(ROOT, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$payload;
        }

        return $payload;
    }

    private static function resolveCallable($payload)
    {
        $payload = trim((string)$payload);
        if ($payload === '') {
            return null;
        }

        if (strpos($payload, '::') !== false) {
            list($class, $method) = explode('::', $payload, 2);
            return array($class, $method);
        }

        return $payload;
    }
}
