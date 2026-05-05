# QueryGraphExtractor

- Type: class
- Namespace: `App\Controller`
- Source: `App/Controller/QueryGraphExtractor.php`

- `extractSelectBlock($sql)`: Handle query graph extractor state through `extractSelectBlock`.
- `splitSelectFields($selectBlock)`: Handle query graph extractor state through `splitSelectFields`.
- `normalizeSelectField($field, $order)`: Handle query graph extractor state through `normalizeSelectField`.
- `extractSelectFields($sql)`: Handle query graph extractor state through `extractSelectFields`.
- `extractTables($sql)`: Handle query graph extractor state through `extractTables`.
- `extractJoinBlocks($sql)`: Handle query graph extractor state through `extractJoinBlocks`.
- `extractJoinConditions($condBlock)`: Handle query graph extractor state through `extractJoinConditions`.
- `extractJoins($sql)`: Handle query graph extractor state through `extractJoins`.
- `extractWhereBlock($sql)`: Handle query graph extractor state through `extractWhereBlock`.
- `extractWhereConditions($sql)`: Handle query graph extractor state through `extractWhereConditions`.
- `extract($sql)`: Handle query graph extractor state through `extract`.
- `extractSubqueryBlocks($sql)`: Handle query graph extractor state through `extractSubqueryBlocks`.
- `extractSubquery($sql, $alias)`: Handle query graph extractor state through `extractSubquery`.
- `extractSubqueries($sql)`: Handle query graph extractor state through `extractSubqueries`.
- `extractGroupBy($sql)`: Handle query graph extractor state through `extractGroupBy`.
