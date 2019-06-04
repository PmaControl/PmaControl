-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le :  mar. 04 juin 2019 à 10:28
-- Version du serveur :  10.4.3-MariaDB-1:10.4.3+maria~bionic-log
-- Version de PHP :  7.2.17-0ubuntu0.18.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `pmacontrol`
--

-- --------------------------------------------------------

--
-- Structure de la table `plugin_file`
--

CREATE TABLE `plugin_file` (
  `id` int(11) NOT NULL,
  `id_plugin_main` int(11) NOT NULL,
  `file` varchar(255) NOT NULL,
  `md5` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `plugin_main`
--

CREATE TABLE `plugin_main` (
  `id` int(11) NOT NULL,
  `nom` varchar(32) NOT NULL,
  `description` text NOT NULL,
  `auteur` varchar(100) NOT NULL,
  `image` varchar(255) NOT NULL COMMENT 'le lien de l''image',
  `fichier` varchar(255) NOT NULL,
  `date_installation` datetime NOT NULL,
  `md5_zip` varchar(32) NOT NULL,
  `version` char(10) NOT NULL,
  `est_actif` int(11) NOT NULL DEFAULT 0,
  `type_licence` varchar(50) NOT NULL,
  `numero_licence` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Structure de la table `plugin_menu`
--

CREATE TABLE `plugin_menu` (
  `id` int(11) NOT NULL,
  `id_plugin_main` int(11) NOT NULL,
  `url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `plugin_file`
--
ALTER TABLE `plugin_file`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_plugin_main` (`id_plugin_main`);

--
-- Index pour la table `plugin_main`
--
ALTER TABLE `plugin_main`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nom` (`nom`,`version`);

--
-- Index pour la table `plugin_menu`
--
ALTER TABLE `plugin_menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_plugin_main` (`id_plugin_main`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `plugin_file`
--
ALTER TABLE `plugin_file`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `plugin_main`
--
ALTER TABLE `plugin_main`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `plugin_menu`
--
ALTER TABLE `plugin_menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `plugin_file`
--
ALTER TABLE `plugin_file`
  ADD CONSTRAINT `plugin_file_ibfk_1` FOREIGN KEY (`id_plugin_main`) REFERENCES `plugin_main` (`id`);

--
-- Contraintes pour la table `plugin_menu`
--
ALTER TABLE `plugin_menu`
  ADD CONSTRAINT `plugin_menu_ibfk_1` FOREIGN KEY (`id_plugin_main`) REFERENCES `plugin_main` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;