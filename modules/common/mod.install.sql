insert  into `role`(`name`,`description`,`status`,`default`,`startpage`) values ('Data Assigner','Can manage data assignment between groups',1,1,'/myaccount/my_profile');
insert  into `role`(`name`,`description`,`status`,`default`,`startpage`) values ('Data Manager','Can manage all data in entire system',1,0,'/myaccount/my_profile');



DROP TABLE IF EXISTS `data_acl`;
CREATE TABLE IF NOT EXISTS `data_acl` (
  `id` int(11) NOT NULL auto_increment,
  `record_table` varchar(255) NOT NULL,
  `record_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_perm` int(11) NOT NULL,
  `create_by` int(11) NOT NULL,
  `create_time` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `record_table` (`record_table`),
  KEY `record_id` (`record_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8  AUTO_INCREMENT=1 ;