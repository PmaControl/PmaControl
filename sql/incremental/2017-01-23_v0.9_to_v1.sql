UPDATE menu SET bg=bg+1 WHERE  bg > 60;
UPDATE menu SET bd=bd+1 WHERE  bd > 60;
INSERT INTO `menu` (`id`, `parent_id`, `bg`, `bd`, `active`, `icon`, `title`, `url`, `class`, `position`, `group_id`) VALUES (NULL, '50', '61', '62', '1', '<i class="fa fa-tachometer" aria-hidden="true" style="font-size: 16px"></i>', 'BenchMark', '{LINK}benchmark/index/', '', '6', '1');


ALTER TABLE `benchmark_main` ADD `progression` FLOAT NOT NULL AFTER `status`;


ALTER TABLE `benchmark_main` DROP FOREIGN KEY `benchmark_main_ibfk_1`; ALTER TABLE `benchmark_main` ADD CONSTRAINT `benchmark_main_ibfk_1` FOREIGN KEY (`id_mysql_server`) REFERENCES `mysql_server`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE TABLE `sharding` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `name` varchar(250) NOT NULL,
 `prefix` varchar(20) NOT NULL,
 `date` datetime NOT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `sharding` ADD UNIQUE(`prefix`);


