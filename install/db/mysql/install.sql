DROP TABLE IF EXISTS `t_color`;
CREATE TABLE `t_color` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_minutes` int(11) DEFAULT NULL,
  `from_seconds` int(11) DEFAULT NULL,
  `to_minutes` int(11) DEFAULT NULL,
  `to_seconds` int(11) DEFAULT NULL,
  `color_hex` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
  `t_type_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB;

INSERT INTO `t_color` (`id`, `from_minutes`, `from_seconds`, `to_minutes`, `to_seconds`, `color_hex`, `t_type_id`) VALUES
(1,	0,	0,	0,	0,	'#9f9f9f',	1),
(2,	0,	1,	2,	59,	'#ffffff',	1),
(3,	3,	0,	7,	59,	'#fdffec',	1),
(4,	8,	0,	10,	59,	'#49b502',	1),
(5,	11,	0,	24,	0,	'#ffff80',	1),
(56, 1,	1,	2,	0,	'#1ee718',	2),
(60, 0,	0,	0,	0,	'#effb86',	2),
(61, 0,	1,	1,	0,	'#035b7c',	2),
(62, 2,	1,	24,	0,	'#fa7275',	2);

DROP TABLE IF EXISTS `t_options`;
CREATE TABLE `t_options` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `is_show_turn` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

INSERT INTO `t_options` (`id`, `is_show_turn`) VALUES
(1,	1), (2, 20);

DROP TABLE IF EXISTS `t_type`;
CREATE TABLE `t_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `allias` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

INSERT INTO `t_type` (`id`, `title`, `allias`) VALUES
(1,	'Учет времени',	'time_tracking'),
(2,	'Учет перерывов',	'pause_tracking');
