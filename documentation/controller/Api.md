# Api

- Type: class
- Namespace: `App\Controller`
- Source: `App/Controller/Api.php`

- `config($param)`: Handle api state through `config`.
- `openApi($param)`: Handle api state through `openApi`.
- `getResourceMap()`: Retrieve api state through `getResourceMap`.
- `getResourceDefinition($resource)`: Retrieve api state through `getResourceDefinition`.
- `normalizePayload($resource, $payload, $isUpdate)`: Handle api state through `normalizePayload`.
- `getOpenApiDocument()`: Retrieve api state through `getOpenApiDocument`.
- `normalizeBoolean($value)`: Handle api state through `normalizeBoolean`.
- `handleGet($db, $definition, $id)`: Handle api state through `handleGet`.
- `handleCreate($db, $resource, $definition, $payload)`: Handle api state through `handleCreate`.
- `handleUpdate($db, $resource, $definition, $id, $payload)`: Handle api state through `handleUpdate`.
- `handleDelete($db, $definition, $id)`: Handle api state through `handleDelete`.
- `readJsonInput()`: Handle api state through `readJsonInput`.
- `respondJson($payload, $status)`: Handle api state through `respondJson`.
