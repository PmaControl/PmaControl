# Compare

- Type: class
- Namespace: `App\Controller`
- Source: `App/Controller/Compare.php`

- `index($params)`: Render compare state and delegate schema comparison to `App\Library\Compare\SchemaCompareEngine`.
- `evaluateIndexRequest($get, $server)`: Validate the compare selection with `App\Library\Security\CompareMainSelection`.
- `menu($param)`: Render the compare object menu.
- `generateGet()`: Rebuild the compare route from the normalized selection.
- `getObjectDiff($param)`: Render the selected object diff through `SchemaCompareEngine`.
- `getDatabaseByServer($param)`: Return database choices for the selected server.
