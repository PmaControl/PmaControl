

ALTER TABLE `plugin_main` CHANGE `repertoire` `fichier` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `plugin_main` CHANGE `description` `description` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;

ALTER TABLE `pmacontrol`.`plugin_main` DROP INDEX `nom`, ADD UNIQUE `nom` (`nom`, `version`) USING BTREE;

ALTER TABLE `plugin_main` ADD `auteur` VARCHAR(100) NOT NULL AFTER `description`;
ALTER TABLE `plugin_main` ADD `type_licence` VARCHAR(50) NOT NULL AFTER `est_actif`;

ALTER TABLE `plugin_main` CHANGE `est_actif` `est_actif` INT(11) NOT NULL DEFAULT '0';
ALTER TABLE `plugin_main` CHANGE `numero_licence` `numero_licence` INT(11) NOT NULL DEFAULT '0';

ALTER TABLE `plugin_main` CHANGE `numero_licence` `numero_licence` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL;
ALTER TABLE `plugin_main` CHANGE `md5_zip` `md5_zip` VARCHAR(32) NOT NULL;

ALTER TABLE `plugin_file` CHANGE `md5` `md5` VARCHAR(32) NOT NULL;



CREATE TABLE `plugin_menu` (
  `id` int(11) NOT NULL,
  `id_plugin_main` int(11) NOT NULL,
  `url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE `plugin_menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_plugin_main` (`id_plugin_main`);
ALTER TABLE `plugin_menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `plugin_menu`
  ADD CONSTRAINT `plugin_menu_ibfk_1` FOREIGN KEY (`id_plugin_main`) REFERENCES `plugin_main` (`id`);


