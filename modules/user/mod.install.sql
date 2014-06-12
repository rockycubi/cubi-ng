DROP TABLE IF EXISTS `user_pref`;
CREATE TABLE `user_pref` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  `value` varchar(255) NOT NULL,
  `type` varchar(128) DEFAULT NULL,
  `create_by` int(11) DEFAULT NULL,
  `create_time` datetime DEFAULT NULL,
  `update_by` int(11) DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;