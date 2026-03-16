# Tree

- Type: class
- Namespace: `App\Library`
- Source: `App/Library/Tree.php`

- `__construct($db_link, $table_name, $fields, $options)`: Handle tree state through `__construct`.
- `delete($id)`: Delete tree state through `delete`.
- `extraWhere()`: Handle tree state through `extraWhere`.
- `add($leaf, $id_parent)`: Create tree state through `add`.
- `up($id)`: Handle tree state through `up`.
- `countFather($id)`: Handle tree state through `countFather`.
- `getInterval($id)`: Retrieve tree state through `getInterval`.
- `getfather($id)`: Retrieve tree state through `getfather`.
- `left($id)`: Handle tree state through `left`.
- `removeaclfile()`: Delete tree state through `removeaclfile`.
- `getFirstFather($id)`: Retrieve tree state through `getFirstFather`.
