# Dot3 SVG Icon Root Cause - 2026-03-21

## Scope

Investigate why the Dot3 node displayed as `10.68.68.134` looked like a PNG instead of a pure SVG, reproduce the issue, identify the root cause with certainty, apply the fix, and re-test on the real Dot3 generation flow.

## Root Cause

The graph itself was already rendered as SVG.

The problem was inside the node icon selection in `App/Library/Graphviz.php`:

- `is_proxysql = 1` forced `proxysql.png`
- `is_maxscale = 1` forced `maxscale.png`
- routers already used `router.svg`

So Graphviz generated a valid SVG wrapper, but embedded the proxy icons as raster images. In the final SVG this appeared as:

- `<image xlink:href="data:image/png;base64,...">`

That is why the node looked "PNG inside SVG".

## Reproduction

### Before fix

The live cluster graph for MySQL server `224` pointed to:

- `dot3_graph.id = 12690`
- file: `/srv/www/pmacontrol/tmp/dot/e6d0273c88a5af87d03cef587830dd50.svg`

Evidence before the fix:

- SQL check on stored SVG:
  - `INSTR(c.svg, 'data:image/png;base64') = 30738`
- direct SVG excerpt showed:
  - `MaxScale : 24.02.8`
  - followed by `<image xlink:href="data:image/png;base64,...">`

The same pattern was visible on the older ProxySQL cluster SVG containing:

- `10.68.68.134:6033`
- `ProxySQL : 3.0.5`
- and an embedded base64 PNG icon.

### Commands used

- `./glial Dot3 run --debug`
- SQL on `dot3_cluster__mysql_server`, `dot3_cluster`, `dot3_graph`
- direct inspection of generated SVG files in `tmp/dot/`

## Fix Applied

### Code

Updated `App/Library/Graphviz.php`:

- `proxysql.png` -> `proxysql.svg`
- `maxscale.png` -> `maxscale.svg`

Added a local vector icon:

- `App/Webroot/image/icon/proxysql.svg`

Also fixed an unrelated warning found during the reproduction:

- `Undefined array key "is_maxscale"` in `Graphviz::generateServer()`

The guard is now:

- `!empty($server['is_maxscale']) && $server['is_maxscale'] == "1"`

## Verification After Fix

After regenerating Dot3:

- `./glial Dot3 run --debug`

The live cluster graph for MySQL server `224` now points to:

- `dot3_graph.id = 12790`
- file: `/srv/www/pmacontrol/tmp/dot/e6d0273c88a5af87d03cef587830dd50.svg`

Stored SVG checks after the fix:

- `INSTR(c.svg, 'data:image/png;base64') = 0`
- `INSTR(c.svg, 'xlink:href="#pmac-icon-') = 0`

And the regenerated SVG no longer contains any embedded PNG payload for that graph.

For the ProxySQL path, the current stored graph for server `181` also showed:

- `INSTR(c.svg, 'data:image/png;base64') = 0`

So the issue was not in `Cluster/svg`, not in the browser, and not in Graphviz falling back from SVG to PNG for the whole graph.

## Conclusion

The issue is identified at 100%:

- the outer graph was always SVG
- the proxy node icons were raster assets selected by application code
- replacing the proxy icons with SVG assets removes the embedded PNG payload from the generated Dot3 SVG

## Remaining Notes

`Dot3 run` still logs unrelated topology issues:

- `This master was not found : 10.105.2.8:3306`
- `This master was not found : 10.68.68.223:3306`
- `This master was not found : 10.68.68.224:3306`
- `This master was not found : 10.68.68.225:3306`

These do not affect the SVG/PNG root cause above.
