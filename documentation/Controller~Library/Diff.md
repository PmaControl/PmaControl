# Diff

- Type: class
- Namespace: `App\Library`
- Source: `App/Library/Diff.php`

- `compare($string1, $string2, $compareCharacters)`: Handle diff state through `compare`.
- `compareFiles($file1, $file2, $compareCharacters)`: Handle diff state through `compareFiles`.
- `computeTable($sequence1, $sequence2, $start, $end1, $end2)`: Handle diff state through `computeTable`.
- `generatePartialDiff($table, $sequence1, $sequence2, $start)`: Handle diff state through `generatePartialDiff`.
- `toString($diff, $separator)`: Handle diff state through `toString`.
- `toHTML($diff, $separator)`: Handle diff state through `toHTML`.
- `toTable($diff, $indentation, $separator)`: Handle diff state through `toTable`.
- `getCellContent($diff, $indentation, $separator, $index, $type)`: Retrieve diff state through `getCellContent`.
- `toSql($diff, $indentation, $separator)`: Handle diff state through `toSql`.
- `addFormat($sql, $diff)`: Create diff state through `addFormat`.
- `toBootstrap($diff, $indentation, $separator)`: Handle diff state through `toBootstrap`.
- `getNext($diff, $index, $k)`: Retrieve diff state through `getNext`.
