-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- HÃ´te : localhost
-- GÃ©nÃ©rÃ© le :  mar. 12 fÃ©v. 2019 Ã  22:38
-- Version du serveur :  10.3.12-MariaDB-1:10.3.12+maria~bionic-log
-- Version de PHP :  7.2.10-0ubuntu0.18.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de donnÃ©es :  `pmacontrol3`
--

--
-- DÃ©chargement des donnÃ©es de la table `menu`
--

INSERT INTO `menu` (`id`, `parent_id`, `bg`, `bd`, `active`, `icon`, `title`, `url`, `class`, `method`, `position`, `group_id`, `level`) VALUES
(60, 0, 163, 164, 1, '<span class=\"glyphicon glyphicon-off\"></span>', 'Login', '{LINK}user/connection/', 'user', 'connection', 0, 3, ''),
(61, 0, 165, 166, 1, '<span class=\"glyphicon glyphicon-user\"></span>', 'Register', '{LINK}user/register/', 'user', 'register', 0, 3, ''),
(62, 0, 167, 168, 1, '<span class=\"glyphicon glyphicon-envelope\"></span>', 'Lost password', '{LINK}user/lost_password/', 'user', 'lost_password', 0, 3, ''),
(92, NULL, 1, 130, 1, '<span class=\"glyphicon glyphicon glyphicon-home\"></span>', 'Home', '{LINK}home/index', 'home', 'index', 0, 1, ''),
(95, 92, 4, 19, 1, '<span class=\"glyphicon glyphicon glyphicon-home\"></span>', 'Dashboard', '', 'dashboard', 'index', 0, 1, ''),
(96, 95, 5, 8, 1, '<i class=\"fa fa-server\" aria-hidden=\"true\" style=\"font-size:14px\"></i>', 'Servers', '{LINK}server/main', 'server', 'main', 0, 1, ''),
(97, 95, 9, 10, 1, '<span class=\"glyphicon glyphicon-hdd\" style=\"font-size:12px\"></span>', 'Hardware', '{LINK}server/hardware', 'server', 'hardware', 0, 1, ''),
(98, 95, 11, 12, 1, '<span class=\"glyphicon glyphicon-signal\" style=\"font-size:12px\"></span>', 'Statistics', '{LINK}server/statistics', 'server', 'statistics', 0, 1, ''),
(99, 95, 13, 14, 1, '<span class=\"glyphicon glyphicon-floppy-disk\" style=\"font-size:12px\"></span>', 'Memory', '{LINK}server/memory', 'server', 'memory', 0, 1, ''),
(100, 95, 15, 16, 1, '<span class=\"glyphicon glyphicon-th-list\" style=\"font-size:12px\"></span>', 'Index', '{LINK}server/index', 'server', 'index', 0, 1, ''),
(101, 95, 17, 18, 1, '<i class=\"fa fa-line-chart\" aria-hidden=\"true\"></i>', 'Graphs', '{LINK}server/id', 'server', 'id', 0, 1, ''),
(102, 92, 20, 27, 1, '<i class=\"fa fa-object-group\" style=\"font-size:14px\"></i>', 'Architecture', '', '', '', 0, 1, ''),
(103, 92, 28, 51, 1, '<span class=\"glyphicon glyphicon-wrench\" aria-hidden=\"true\"></span>', 'Tools', '', '', '', 0, 1, ''),
(104, 103, 29, 30, 1, '<span class=\"glyphicon glyphicon-list-alt\" style=\"font-size:12px\"></span>', 'Query Analyzer', '{LINK}monitoring/query/', 'monitoring', 'query', 0, 1, ''),
(105, 103, 31, 34, 1, '<i class=\"glyphicon glyphicon-erase\"></i>', 'Cleaner', '{LINK}cleaner/index/', 'cleaner', 'index', 0, 1, ''),
(108, 92, 52, 67, 1, '<span class=\"glyphicon glyphicon-floppy-disk\" style=\"font-size:12px\"></span>', 'Backups', '', '', '', 0, 1, ''),
(109, 92, 68, 73, 1, '<i style=\"font-size: 16px\" class=\"fa fa-puzzle-piece\"></i>', 'Plugins', '', '', '', 0, 1, ''),
(110, 109, 69, 70, 1, '<span class=\"glyphicon glyphicon-th-list\" aria-hidden=\"true\"></span>', 'sys Schema', '{LINK}mysqlsys/index/', 'mysqlsys', 'index', 0, 1, ''),
(111, 109, 71, 72, 1, '<i class=\"fa fa-tachometer\" aria-hidden=\"true\"></i>', 'BenchMark', '{LINK}benchmark/index/', 'benchmark', 'index', 0, 1, ''),
(112, 103, 35, 36, 1, '<i class=\"fa fa-key\" style=\"font-size:16px\"  aria-hidden=\"true\"></i>', 'Deploy RSA key', '{LINK}DeployRsaKey/index/', 'DeployRsaKey', 'index', 0, 1, ''),
(113, 92, 74, 109, 1, '<span class=\"glyphicon glyphicon-cog\" style=\"font-size:12px\"></span>', 'Settings', '', '', '', 0, 1, ''),
(114, 108, 53, 58, 1, '<span class=\"glyphicon glyphicon-hdd\" style=\"font-size:12px\"></span>', 'Storage area', '{LINK}StorageArea/index/', 'StorageArea', 'index', 0, 1, ''),
(115, 108, 59, 64, 1, '<span class=\"glyphicon glyphicon-book\" style=\"font-size:12px\" aria-hidden=\"true\"></span>', 'Archives', '{LINK}Archives/index/', 'Archives', 'index', 0, 1, ''),
(116, 92, 110, 119, 1, '<i class=\"fa fa-question\" style=\"font-size:16px\" aria-hidden=\"true\"></i>', 'Help', '', '', '', 0, 1, ''),
(117, 116, 111, 112, 1, '<i class=\"fa fa-book\" style=\"font-size:16px\"></i>', 'Online docs and support', 'https://github.com/Glial/PmaControl/wiki', '', '', 0, 1, ''),
(118, 116, 113, 114, 1, '<i class=\"fa fa-refresh\" style=\"font-size:16px\"></i>', 'Check for update', 'https://github.com/PmaControl/PmaControl', 'update', 'index', 0, 1, ''),
(119, 116, 115, 116, 1, '<i class=\"fa fa-bug\" style=\"font-size:16px\"></i>', 'Report issue', 'https://github.com/PmaControl/PmaControl/issues', '', '', 0, 1, ''),
(120, 116, 117, 118, 1, '<i class=\"fa fa-info-circle\" style=\"font-size:16px\"></i>', 'About', '{LINK}About/index', 'about', 'index', 0, 1, ''),
(121, 113, 75, 76, 1, '<span class=\"glyphicon glyphicon-user\" style=\"font-size:12px\"></span>', 'Users', '{LINK}user/index/', 'user', 'index', 0, 1, ''),
(122, 113, 77, 78, 1, '<span class=\"glyphicon glyphicon-user\" style=\"font-size:12px\"></span>', 'Groups', '{LINK}group/index/', 'group', 'index', 0, 1, ''),
(123, 113, 79, 82, 1, '<span class=\"glyphicon glyphicon-user\" style=\"font-size:12px\"></span>', 'Client', '{LINK}client/index/', 'client', 'index', 0, 1, ''),
(124, 113, 83, 84, 1, '<span class=\"glyphicon glyphicon-user\" style=\"font-size:12px\"></span>', 'Environment', '{LINK}environment/index/', 'environment', 'index', 0, 1, ''),
(125, 113, 85, 86, 1, '<span class=\"glyphicon glyphicon-calendar\" style=\"font-size:12px\"></span>', 'Daemon', '{LINK}daemon/index', 'daemon', 'index', 0, 1, ''),
(126, 113, 87, 92, 1, ' <i class=\"fa fa-server\" aria-hidden=\"true\" style=\"font-size:14px\"></i>', 'Servers', '{LINK}server/settings', 'server', 'settings', 0, 1, ''),
(127, 103, 37, 38, 1, '<i class=\"glyphicon glyphicon-transfer\" style=\"font-size:12px\"></i>', 'Compare', '{LINK}compare/index/', 'compare', 'index', 0, 1, ''),
(128, 103, 39, 40, 1, '<span class=\"glyphicon glyphicon-search\" aria-hidden=\"true\"></span>', 'Scan network', '{LINK}scan/index/', 'scan', 'index', 0, 1, ''),
(129, 92, 2, 3, 1, '<span class=\"glyphicon glyphicon glyphicon-home\"></span>', 'Home', '{LINK}home/index', 'home', 'index', 0, 1, ''),
(131, 113, 93, 94, 1, '<i class=\"fa fa-address-book\" aria-hidden=\"true\"></i>', 'LDAP', '{LINK}ldap/index/', 'ldap', 'index', 0, 1, ''),
(132, 113, 95, 96, 1, '<i class=\"fa fa-puzzle-piece\" aria-hidden=\"true\"></i>', 'Plugins', '{LInK}plugin/index', 'plugin', 'index', 0, 1, ''),
(133, 113, 97, 98, 1, '<i class=\"fa fa-key\" aria-hidden=\"true\"></i>', 'SSH keys', '{LINK}ssh/index', 'ssh', 'index', 0, 1, ''),
(136, 103, 41, 42, 1, '<i class=\"fa fa-wpforms\" aria-hidden=\"true\"></i>', 'Format SQL', '{LINK}format/index/', 'format', 'index', 0, 1, ''),
(137, 92, 120, 121, 1, '<span class=\"glyphicon glyphicon-off\" aria-hidden=\"true\"></span>', 'Logout', '{LINK}user/logout/', 'user', 'logout', 0, 1, ''),
(138, 113, 99, 104, 1, '<span class=\"glyphicon glyphicon-import\"></span>', 'Import / Export', '{LINK}export/index', 'Export', 'index', 0, 1, ''),
(139, 103, 43, 44, 1, '<i class=\"fa fa-wrench\" aria-hidden=\"true\"></i>', 'Check Config', '{LINK}CheckConfig/index/', 'CheckConfig', 'index', 0, 1, ''),
(140, 108, 65, 66, 1, '<span class=\"glyphicon glyphicon-cog\" style=\"font-size:12px\"></span>', 'Settings', '{LINK}backup/settings/', 'backup', 'settings', 0, 1, ''),
(141, 113, 105, 106, 1, '<span class=\"glyphicon glyphicon-globe\" aria-hidden=\"true\"></span>', 'Alias DNS', '{LINK}alias/index', 'alias', 'index', 0, 1, ''),
(142, 92, 122, 129, 1, '<i class=\"fa fa-id-card-o\" aria-hidden=\"true\"></i>', 'Developer', '', '', '', 0, 1, ''),
(143, 142, 123, 124, 1, '<i class=\"fa fa-terminal\" aria-hidden=\"true\"></i>', 'PHP Live REGEX', '{LINK}PhpLiveRegex/index', 'PhpLiveRegex', 'index', 0, 1, ''),
(144, 142, 125, 128, 1, '<span class=\"glyphicon glyphicon-menu-hamburger\" aria-hidden=\"true\"></span>', 'Manage menu', '{LINK}tree/index', 'tree', 'index', 0, 1, ''),
(145, 113, 107, 108, 1, '<span class=\"glyphicon glyphicon-tags\" aria-hidden=\"true\"></span>', 'Tags', '{LINK}tag/index', 'tag', 'index', 0, 1, ''),
(146, 103, 45, 46, 1, '<i class=\"fa fa-address-card\" aria-hidden=\"true\"></i>', 'MySQL User', '{LINK}MysqlUser/index/', 'MysqlUser', 'index', 0, 1, ''),
(147, 102, 21, 22, 1, '<i class=\"glyphicon glyphicon-th\"></i>', 'Topology', '{LINK}architecture/index/', 'architecture', 'index', 0, 1, ''),
(148, 102, 23, 24, 1, '<i class=\"fa fa-sitemap\"></i>', 'Master / Slave', '{LINK}slave/index/', 'slave', 'index', 0, 1, ''),
(149, 102, 25, 26, 1, '<i class=\"glyphicon glyphicon-th-large\"></i>', 'Galera Cluster', '{LINK}GaleraCluster/index/', 'GaleraCluster', 'index', 0, 1, ''),
(150, 103, 47, 50, 1, '<i class=\"fa fa-database fa-lg\"></i>', 'Database', '{LINK}database/index', 'database', 'index', 0, 1, ''),
(153, 144, 126, 127, 0, '<span class=\"glyphicon glyphicon-plus\"></span>', 'Add menu entry', '{LINK}tree/add', 'tree', 'add', 0, 1, ''),
(154, 96, 6, 7, 0, '<i class=\"fa fa-server\" aria-hidden=\"true\" style=\"font-size:14px\"></i>', 'Servers listing', '{LINK}Server/listing', 'Server', 'listing', 0, 1, ''),
(155, 105, 32, 33, 0, '<span class=\"glyphicon glyphicon-plus\"></span>', 'Add a cleaner', '{LINK}cleaner/add/', 'cleaner', 'add', 0, 1, ''),
(156, 114, 54, 55, 0, '<span class=\"glyphicon glyphicon-plus\"></span>', 'Add a storage area', '{LINK}StorageArea/add', 'StorageArea', 'add', 0, 1, ''),
(157, 114, 56, 57, 0, '<span class=\"glyphicon glyphicon-hdd\" style=\"font-size:12px\"></span>', 'List all storage area', '{LINK}StorageArea/listStorage', 'StorageArea', 'listStorage', 0, 1, ''),
(158, 115, 60, 61, 0, '<span class=\"glyphicon glyphicon-book\" style=\"font-size:12px\" aria-hidden=\"true\"></span>', 'Restoration history', '{LINK}Archives/history', 'Archives', 'history', 0, 1, ''),
(159, 115, 62, 63, 0, '<span class=\"glyphicon glyphicon-book\" style=\"font-size:12px\" aria-hidden=\"true\"></span>', 'Restoration detail', '{LINK}Archives/detail/', 'Archives', 'detail', 0, 1, ''),
(160, 150, 48, 49, 0, '<span class=\"glyphicon glyphicon-plus\"></span>', 'Create database', '{LINK}database/create', 'database', 'create', 0, 1, ''),
(161, 126, 88, 89, 0, '<i class=\"fa fa-key\" aria-hidden=\"true\"></i>', 'Change server password', '{LINK}server/password', 'server', 'password', 0, 1, ''),
(162, 126, 90, 91, 0, '<span class=\"glyphicon glyphicon-plus\"></span>', 'Add a new server', '{LINK}mysql/add', 'mysql', 'add', 0, 1, ''),
(163, 123, 80, 81, 0, '<span class=\"glyphicon glyphicon-plus\"></span>', 'Add a client', '{LINK}client/add', 'client', 'add', 0, 1, ''),
(164, 138, 100, 101, 0, '<span class=\"glyphicon glyphicon-floppy-disk\"></span>', 'Import / Export configuration', '{LINK}export/export_conf/', 'export', 'export_conf', 0, 1, ''),
(165, 138, 102, 103, 0, '<span class=\"glyphicon glyphicon-floppy-disk\"></span>', 'Import / Export configuration', '{LINK}export/import_conf/', 'export', 'import_conf', 0, 1, '');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
