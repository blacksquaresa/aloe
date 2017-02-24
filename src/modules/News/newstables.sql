-- MySQL Administrator dump 1.4
--
-- ------------------------------------------------------
-- Server version	5.1.32-community


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;


DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL DEFAULT '',
  `keywords` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `date` int(10) unsigned NOT NULL DEFAULT '0',
  `pubinfo` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `newscatlink`;
CREATE TABLE `newscatlink` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `newsid` int(10) unsigned NOT NULL DEFAULT '0',
  `catid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_newscatlink_news` (`newsid`),
  KEY `FK_newscatlink_cat` (`catid`),
  CONSTRAINT `FK_newscatlink_cat` FOREIGN KEY (`catid`) REFERENCES `listitems` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_newscatlink_news` FOREIGN KEY (`newsid`) REFERENCES `news` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=latin1;

