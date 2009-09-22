-- Eresus 2 MySQL 4.x dump
--
-- $Id$
-- --------------------------------------------------------

CREATE TABLE `pages` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(32) NOT NULL default '',
  `owner` int(10) unsigned NOT NULL default '0',
  `title` text NOT NULL,
  `caption` varchar(64) NOT NULL default '',
  `description` text NOT NULL,
  `hint` text NOT NULL,
  `keywords` text NOT NULL,
  `position` smallint(5) unsigned NOT NULL default '0',
  `active` tinyint(1) NOT NULL default '0',
  `access` tinyint(1) unsigned NOT NULL default '5',
  `visible` tinyint(1) unsigned NOT NULL default '1',
  `template` varchar(64) NOT NULL default '',
  `type` varchar(32) NOT NULL default 'default',
  `content` longtext NOT NULL,
  `options` text NOT NULL,
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`),
  KEY `owner` (`owner`),
  KEY `position` (`position`),
  KEY `active` (`active`),
  KEY `access` (`access`),
  KEY `visible` (`visible`),
  KEY `created` (`created`),
  KEY `updated` (`updated`)
) TYPE=MyISAM;

INSERT INTO `pages` VALUES (1, 'main', 0, 'Главная', 'Главная', '', 'Главная страница', '', 0, 1, 5, 1, '', 'default', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- --------------------------------------------------------

CREATE TABLE `plugins` (
  `name` varchar(32) NOT NULL default '',
  `type` set('client','admin','content','ondemand') default NULL,
  `active` tinyint(1) unsigned NOT NULL default '1',
  `position` int(10) unsigned default NULL,
  `settings` text,
  `title` varchar(64) default NULL,
  `version` varchar(16) default NULL,
  `description` varchar(255) default NULL,
  PRIMARY KEY  (`name`),
  KEY `active` (`active`),
  KEY `position` (`position`),
  KEY `type` (`type`)
) TYPE=MyISAM;

-- --------------------------------------------------------

CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `login` varchar(16) NOT NULL default '',
  `hash` varchar(32) NOT NULL default '',
  `active` tinyint(1) unsigned NOT NULL default '1',
  `lastVisit` datetime default NULL,
  `lastLoginTime` int(10) unsigned default NULL,
  `loginErrors` int(10) unsigned default NULL,
  `access` tinyint(3) unsigned default NULL,
  `name` varchar(64) default NULL,
  `mail` varchar(64) default NULL,
  `profile` text,
  PRIMARY KEY  (`id`),
  KEY `login` (`login`),
  KEY `active` (`active`)
) TYPE=MyISAM;

INSERT INTO `users` VALUES (1, 'root', '74be16979710d4c4e7c6647856088456', 1, '0000-00-00 00:00:00', 0, 0, 1, 'Служба поддержки', 'support@example.org', NULL);
