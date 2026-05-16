-- Dot3 legend: dedicated colour for ProxySQL-to-ProxySQL cluster links.
-- Previously these edges reused REPLICATION_OK (green) in
-- Dot3::buildLinkBetweenProxySQL(), which is wrong — they are not MySQL
-- replication, and rendering them green let them blend in with healthy
-- primary→replica edges in /Cluster/svg/<id>/. Render them red instead so
-- the ProxySQL cluster topology stands out.
--
-- dot3_legend has no UNIQUE index on `const`, so guard with NOT EXISTS to
-- keep the migration idempotent.

INSERT INTO `dot3_legend` (`const`, `name`, `font`, `color`, `background`, `style`, `order`, `type`, `condition`)
SELECT 'PROXYSQL_LINK', 'ProxySQL cluster link', '#FFFFFF', '#FF0000', '#FF0000', 'solid', 5, 'PROXYSQL', ''
FROM dual
WHERE NOT EXISTS (SELECT 1 FROM `dot3_legend` WHERE `const` = 'PROXYSQL_LINK');
