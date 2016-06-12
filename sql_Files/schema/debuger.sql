DROP TABLE IF EXISTS `issues`;
CREATE TABLE `issues` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `user` int(11) unsigned NOT NULL,
  `status` int(1) NOT NULL DEFAULT '0',
  `priority` int(1) NOT NULL DEFAULT '1',
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `fixed_in` int(11) unsigned NOT NULL,
  `duplicated_of` int(11) unsigned NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` int(11) unsigned NOT NULL,
  `category` int(10) unsigned NOT NULL,
  `subCategory` int(10) unsigned NOT NULL DEFAULT '1',
  `page` int(10) unsigned NOT NULL,
  `version` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `issues_comments`;
CREATE TABLE `issues_comments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `issue_id` int(11) NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `description` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `issues_watchers`;
CREATE TABLE `issues_watchers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `issue_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(32) NOT NULL,
  `access` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `data` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(128) DEFAULT NULL,
  `username_clean` varchar(128) NOT NULL,
  `real_name` varchar(128) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `lang` varchar(5) DEFAULT NULL,
  `level` int(1) NOT NULL DEFAULT '0',
  `gender` varchar(1) NOT NULL DEFAULT 'n',
  `status` int(1) NOT NULL DEFAULT '0',
  `team` int(2) NOT NULL DEFAULT '0',
  `activated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`email`,`username_clean`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `users_changeEmail_log`;
CREATE TABLE `users_changeEmail_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `old_email` varchar(45) NOT NULL,
  `new_email` varchar(128) NOT NULL,
  `token` varchar(128) NOT NULL DEFAULT '',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `users_creation_log`;
CREATE TABLE `users_creation_log` (
  `user_id` int(11) NOT NULL,
  `email` varchar(45) NOT NULL,
  `token` varchar(128) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `users_recoveryPassword_log`;
CREATE TABLE `users_recoveryPassword_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `email` varchar(45) NOT NULL,
  `token` varchar(128) DEFAULT '',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
