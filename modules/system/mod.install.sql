/*Table structure for table `acl_action` */

DROP TABLE IF EXISTS `acl_action`;

CREATE TABLE `acl_action` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `module` varchar(64) NOT NULL default '',
  `resource` varchar(64) NOT NULL default '',
  `action` varchar(64) NOT NULL default '',
  `description` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `acl_role_action` */

DROP TABLE IF EXISTS `acl_role_action`;

CREATE TABLE `acl_role_action` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `role_id` int(10) unsigned NOT NULL default '0',
  `action_id` int(10) unsigned NOT NULL default '0',
  `access_level` varchar(4) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `role_id` (`role_id`),
  KEY `action_id` (`action_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `meta_do` */

DROP TABLE IF EXISTS `meta_do`;

CREATE TABLE `meta_do` (
  `name` varchar(100) NOT NULL,
  `module` varchar(100) NOT NULL,
  `class` varchar(100) NOT NULL,
  `dbname` varchar(100) default NULL,
  `table` varchar(100) default NULL,
  `data` text,
  `fields` text,
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `meta_form` */

DROP TABLE IF EXISTS `meta_form`;

CREATE TABLE `meta_form` (
  `name` varchar(100) NOT NULL,
  `module` varchar(100) NOT NULL,
  `class` varchar(100) NOT NULL,
  `dataobj` varchar(100) default NULL,
  `template` varchar(100) default NULL,
  `data` text,
  `elements` text,
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `meta_view` */

DROP TABLE IF EXISTS `meta_view`;

CREATE TABLE `meta_view` (
  `name` varchar(100) NOT NULL,
  `module` varchar(100) NOT NULL,
  `class` varchar(100) NOT NULL,
  `template` varchar(100) default NULL,
  `data` text,
  `forms` text,
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `module` */

DROP TABLE IF EXISTS `module`;

CREATE TABLE `module` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(64) NOT NULL default '',
  `description` varchar(255) default NULL,
  `status` int(2) default '1',
  `author` varchar(64) default NULL,
  `version` varchar(64) default NULL,
  `openbiz_version` varchar(64) default NULL,
  `depend_on` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `role` */

DROP TABLE IF EXISTS `role`;

CREATE TABLE `role` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(64) NOT NULL default '',
  `description` varchar(255) default NULL,
  `status` int(2) default '1',
  `default` int(2) default '0',
  `startpage` varchar( 255 ) NOT NULL,
  `create_by` int(10) default 1,
  `create_time` datetime default NULL,
  `update_by` int(10) default 1,
  `update_time` datetime default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  INDEX (  `default` ),
  INDEX (  `status` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `role` */

insert  into `role`(`id`,`name`,`description`,`status`,`default`,`startpage`) values (1,'Cubi Administrator','System administrator',1,0,'/system/general_default');
insert  into `role`(`id`,`name`,`description`,`status`,`default`,`startpage`) values (2,'Cubi Member','General registered users',1,1,'/myaccount/my_profile');
insert  into `role`(`id`,`name`,`description`,`status`,`default`,`startpage`) values (3,'Cubi Guest','Guest users are unregistered users',1,0,'/system/general_default');
/*Table structure for table `user` */


DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `username` varchar(64) NOT NULL default '',
  `password` varchar(64) NOT NULL default '',
  `enctype` varchar(64) NOT NULL default 'SHA1',
  `email` varchar(64) default '',
  `smartcard` varchar(255) default NULL,
  `status` int(2) default '1',
  `lastlogin` datetime default NULL,
  `lastlogout` datetime default NULL,
  `create_by` int(10) default '1',
  `create_time` datetime default NULL,
  `update_by` int(10) default '1',
  `update_time` datetime default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `smartcard` (`smartcard`),
  KEY `email` (`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

/*Data for the table `user` */

insert  into `user`(`id`,`username`,`password`,`enctype`,`email`,`status`,`lastlogin`,`lastlogout`,`create_by`,`create_time`,`update_by`,`update_time`) values (1,'admin','d033e22ae348aeb5660fc2140aec35850c4da997','SHA1','admin@yourcompany.com',1,'2010-05-16 18:20:40','2009-08-24 13:24:14',1,'2010-05-01 01:19:57',1,'2010-05-01 01:19:57');

/*Table structure for table `user_role` */

DROP TABLE IF EXISTS `user_role`;

CREATE TABLE `user_role` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL default '0',
  `role_id` int(10) unsigned NOT NULL default '0',
  `default` int(2) default 0,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`),
  KEY `role_id` (`role_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `user_role` */

insert  into `user_role`(`id`,`user_id`,`role_id`) values (1,1,1);
insert  into `user_role`(`id`,`user_id`,`role_id`) values (2,1,2);

/*Table structure for table `group` */

DROP TABLE IF EXISTS `group`;

CREATE TABLE `group` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(64) NOT NULL default '',
  `description` varchar(255) default NULL,
  `default` int(2) default 0,
  `status` int(2) default '1',
  `create_by` int(10) default 1,
  `create_time` datetime default NULL,
  `update_by` int(10) default 1,
  `update_time` datetime default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `group` */

insert  into `group`(`id`,`name`,`description`,`default`,`status`,`create_by`,`create_time`,`update_by`,`update_time`) values (1,'Default Group',NULL,1,1,1,'2011-07-06 18:33:15',1,'2011-07-06 18:33:15');

/*Table structure for table `group_role` */

DROP TABLE IF EXISTS `user_group`;

CREATE TABLE `user_group` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL default '0',
  `group_id` int(10) unsigned NOT NULL default '0',
  `default` int(2) default 0,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Data for the table `user_group` */

insert into `user_group`(`id`,`user_id`,`group_id`) values (1,1,1);

DROP TABLE IF EXISTS `pass_token`;
CREATE TABLE IF NOT EXISTS `pass_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expiration` datetime DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `token` (`token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

/*Table structure for table `menu` */

DROP TABLE IF EXISTS `menu`;


CREATE TABLE IF NOT EXISTS `menu` (
  `name` varchar(100) NOT NULL DEFAULT '',
  `module` varchar(100) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `url_match` varchar(255) DEFAULT NULL,
  `view` varchar(255) DEFAULT NULL,
  `type` varchar(50) NOT NULL DEFAULT '',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `parent` varchar(255) DEFAULT '',
  `ordering` int(4) DEFAULT '10',
  `access` varchar(100) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `icon_css` varchar(100) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `create_by` int(10) DEFAULT '1',
  `create_time` datetime DEFAULT NULL,
  `update_by` int(10) DEFAULT '1',
  `update_time` datetime DEFAULT NULL,
  PRIMARY KEY (`name`),
  KEY `parent` (`parent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
  
/*Data for the table `menu` */


/*Table structure of table `widget` */

DROP TABLE IF EXISTS `widget`;

CREATE TABLE `widget` (                                 
  `name` varchar(100) NOT NULL default '',      
  `module` varchar(100) default NULL,           
  `title` varchar(100) default NULL,                      
  `description` varchar(255) default NULL,
  `configable` tinyint(1) NOT NULL default '0', 
  `published` tinyint(1) NOT NULL default '1',  
  `ordering` INT NOT NULL DEFAULT '10' , 
  `create_by` int(10) default 1,
  `create_time` datetime default NULL,
  `update_by` int(10) default 1,
  `update_time` datetime default NULL,
  PRIMARY KEY  (`name`)                         
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


/*Table structure for table `module` */

DROP TABLE IF EXISTS `module_changelog`;

CREATE TABLE `module_changelog` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `module` varchar(255) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `description` varchar(255) default NULL,
  `status` varchar(255) default NULL,
  `type` varchar(255) default NULL,
  `version` varchar(64) default NULL,
  `publish_date` varchar(64) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `session`;
CREATE TABLE IF NOT EXISTS `session` (
  `id` varchar(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `user_id` int(11) NOT NULL,
  `expiration` int(10) unsigned NOT NULL,
  `data` LONGTEXT COLLATE utf8_unicode_ci NOT NULL,
  `ipaddr` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_url` text COLLATE utf8_unicode_ci NOT NULL,
  `create_time` datetime NOT NULL,
  `update_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `expiration` (`expiration`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `user_oauth`;
CREATE TABLE IF NOT EXISTS `user_oauth` (
 `id` int(11) NOT NULL auto_increment,
  `user_id` int(10) NOT NULL,
  `oauth_uid` varchar(255) NOT NULL default '',
  `oauth_class`  char(80) NOT NULL,
  `oauth_token` varchar(255) default NULL,
  `oauth_token_secret` varchar(255) default NULL,
  `oauth_user_info` longtext default NULL,
  `oauth_rawdata` longtext NOT NULL,  
  `is_sync` tinyint(1) NOT NULL,  
  `status` int(11) NOT NULL,  
  `create_by` int(11) NOT NULL,
  `create_time` datetime NOT NULL,
  `update_by` int(11) NOT NULL,
  `update_time` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

