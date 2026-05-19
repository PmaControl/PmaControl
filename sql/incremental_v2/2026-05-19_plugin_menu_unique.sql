-- Issue #1316 — plugin_menu accumulated duplicate (id_plugin_main, url)
-- rows because Plugin::install had no ON DUPLICATE guard and no
-- UNIQUE constraint on the table. The duplicates made
-- Plugin::removeCore call Tree::delete twice for the same menu_id
-- and crash on the second call.
--
-- 1. Dedup existing rows, keep the lowest id per (plugin, url).
-- 2. Add the missing UNIQUE constraint so it can't drift again.
--
-- Safe on a clean install (no duplicate rows, ALTER adds the index
-- without complaint). Safe on a corrupt install (the DELETE clears
-- the duplicates first).

DELETE pm1 FROM `plugin_menu` pm1
INNER JOIN `plugin_menu` pm2
        ON pm1.`id_plugin_main` = pm2.`id_plugin_main`
       AND pm1.`url`            = pm2.`url`
       AND pm1.`id`            > pm2.`id`;

ALTER TABLE `plugin_menu`
    ADD UNIQUE KEY `uniq_id_plugin_main_url` (`id_plugin_main`, `url`);
