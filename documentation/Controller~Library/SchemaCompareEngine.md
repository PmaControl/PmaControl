# SchemaCompareEngine

- Type: class
- Namespace: `App\Library\Compare`
- Source: `App/Library/Compare/SchemaCompareEngine.php`

- `checkConfig($idServer1, $db1, $idServer2, $db2)`: Validate selected servers and databases.
- `analyse($idServer1, $db1, $idServer2, $db2)`: List comparable schema objects.
- `compareDiffForMenu($idServer1, $idServer2, $menu, $db1, $db2, $data)`: Build diffs for the selected object menu.
- `compareTable($original, $compare, $data)`: Generate table comparison scripts.
- `execMulti($queries, $dbLink)`: Execute batched metadata queries.
- `compareListObject($db1, $db2, $typeObject)`: List object names on both databases.
- `compareObject($menu, $db1, $db2, $data)`: Generate non-table object comparison scripts.
- `getDbLinkFromId($idDb)`: Resolve the legacy Compare DB link with #561 semantics.
