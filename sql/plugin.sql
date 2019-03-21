

ALTER TABLE `plugin_main` CHANGE `repertoire` `fichier` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
ALTER TABLE `plugin_main` CHANGE `description` `description` TEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;

ALTER TABLE `pmacontrol`.`plugin_main` DROP INDEX `nom`, ADD UNIQUE `nom` (`nom`, `version`) USING BTREE;

ALTER TABLE `plugin_main` ADD `auteur` VARCHAR(100) NOT NULL AFTER `description`;
ALTER TABLE `plugin_main` ADD `type_licence` VARCHAR(50) NOT NULL AFTER `est_actif`;

ALTER TABLE `plugin_main` CHANGE `est_actif` `est_actif` INT(11) NOT NULL DEFAULT '0';
ALTER TABLE `plugin_main` CHANGE `numero_licence` `numero_licence` INT(11) NOT NULL DEFAULT '0';

ALTER TABLE `plugin_main` CHANGE `numero_licence` `numero_licence` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL;
ALTER TABLE `plugin_main` CHANGE `md5_zip` `md5_zip` VARCHAR(32) NOT NULL;