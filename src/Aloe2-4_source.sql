-- MySQL Administrator dump 1.4
--
-- ------------------------------------------------------
-- Server version	5.5.27


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


--
-- Create schema blacksq_aloe
--

CREATE DATABASE IF NOT EXISTS blacksq_aloe;
USE blacksq_aloe;

--
-- Definition of table `adminlinks`
--

DROP TABLE IF EXISTS `adminlinks`;
CREATE TABLE `adminlinks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL DEFAULT '',
  `path` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `code` varchar(45) NOT NULL DEFAULT '',
  `position` int(10) unsigned NOT NULL DEFAULT '50',
  `rights` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `adminlinks`
--

/*!40000 ALTER TABLE `adminlinks` DISABLE KEYS */;
INSERT INTO `adminlinks` (`id`,`name`,`path`,`description`,`code`,`position`,`rights`) VALUES 
 (5,'Users','/admin/allusers.php','','User',10,1),
 (6,'Content','/admin/editcontent.php','','Content',20,8),
 (8,'Resources','/admin/resources.php','','Resources',90,32);
/*!40000 ALTER TABLE `adminlinks` ENABLE KEYS */;


--
-- Definition of table `content`
--

DROP TABLE IF EXISTS `content`;
CREATE TABLE `content` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `layout` int(10) unsigned NOT NULL DEFAULT '0',
  `columnid` int(10) unsigned NOT NULL DEFAULT '0',
  `position` int(10) unsigned NOT NULL DEFAULT '0',
  `content` text NOT NULL,
  `module` varchar(45) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE` (`layout`,`columnid`,`position`) USING BTREE,
  KEY `FK_content_column` (`columnid`),
  CONSTRAINT `FK_content_layout` FOREIGN KEY (`layout`) REFERENCES `layouts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=254 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `content`
--

/*!40000 ALTER TABLE `content` DISABLE KEYS */;
INSERT INTO `content` (`id`,`layout`,`columnid`,`position`,`content`,`module`) VALUES 
 (159,5,1,6,'<h1>Privacy Policy</h1>\r\n<p>This privacy policy sets out how we use and protect any information that you give us when you use this website.<br />We are committed to ensuring that your privacy is protected. Should we ask you to provide certain information by which you can be identified when using this website, then you can be assured that it will only be used in accordance with this privacy statement.<br />We may change this policy from time to time by updating this page. You should check this page from time to time to ensure that you are happy with any changes.</p>\r\n<h3>What we collect</h3>\r\n<p>We may collect the following information:<br />&bull; name and job title<br />&bull; contact information including email address<br />&bull; demographic information such as postcode, preferences and interests<br />&bull; other information relevant to customer surveys and/or offers</p>\r\n<h3>What we do with the information we gather</h3>\r\n<p>We require this information to understand your needs and provide you with a better service, and in particular for the following reasons:<br />&bull; Internal record keeping.&nbsp;<br />&bull; We may use the information to improve our products and services.&nbsp;<br />&bull; We may periodically send promotional email about new products, special offers or other information which we think you may find interesting using the email address which you have provided.&nbsp;<br />&bull; From time to time, we may also use your information to contact you for market research purposes. We may contact you by email, phone, fax or mail. We may use the information to customise the website according to your interests.</p>\r\n<h3>Security</h3>\r\n<p>We are committed to ensuring that your information is secure. In order to prevent unauthorised access or disclosure we have put in place suitable physical, electronic and managerial procedures to safeguard and secure the information we collect online.</p>\r\n<h3>How we use cookies</h3>\r\n<p>A cookie is a small file which asks permission to be placed on your computer\'s hard drive. Once you agree, the file is added and the cookie helps analyse web traffic or lets you know when you visit a particular site. Cookies allow web applications to respond to you as an individual. The web application can tailor its operations to your needs, likes and dislikes by gathering and remembering information about your preferences.&nbsp;<br />We use traffic log cookies to identify which pages are being used. This helps us analyse data about web page traffic and improve our website in order to tailor it to customer needs. We only use this information for statistical analysis purposes and then the data is removed from the system.&nbsp;<br />Overall, cookies help us provide you with a better website, by enabling us to monitor which pages you find useful and which you do not. A cookie in no way gives us access to your computer or any information about you, other than the data you choose to share with us.&nbsp;<br />You can choose to accept or decline cookies. Most web browsers automatically accept cookies, but you can usually modify your browser setting to decline cookies if you prefer. This may prevent you from taking full advantage of the website.</p>\r\n<h3>Links to other websites</h3>\r\n<p>Our website may contain links to other websites of interest. However, once you have used these links to leave our site, you should note that we do not have any control over that other website. Therefore, we cannot be responsible for the protection and privacy of any information which you provide whilst visiting such sites and such sites are not governed by this privacy statement. You should exercise caution and look at the privacy statement applicable to the website in question.</p>\r\n<h3>Controlling your personal information</h3>\r\n<p>We will not sell, distribute or lease your personal information to third parties unless we have your permission or are required by law to do so. We may use your personal information to send you promotional information about third parties which we think you may find interesting if you tell us that you wish this to happen.<br />If you believe that any information we are holding on you is incorrect or incomplete, please email us as soon as possible.</p>\r\n<p>&nbsp;</p>','CMStandard'),
 (160,4,1,6,'<h1>Disclaimer</h1>\r\n<p><span>The information contained in this website is for general information purposes only. The information is provided by us and while we endeavour to keep the information up to date and correct, we make no representations or warranties of any kind, express or implied, about the completeness, accuracy, reliability, suitability or availability with respect to the website or the information, products, services, or related graphics contained on the website for any purpose. Any reliance you place on such information is therefore strictly at your own risk.<br />In no event will we be liable for any loss or damage including without limitation, indirect or consequential loss or damage, or any loss or damage whatsoever arising from loss of data or profits arising out of, or in connection with, the use of this website.<br />Through this website you are able to link to other websites which are not under our control</span><span>. We have no control over the nature, content and availability of those sites. The inclusion of any links does not necessarily imply a recommendation or endorse the views expressed within them.<br />Every effort is made to keep the website up and running smoothly. However, </span>we <span>take no responsibility for, and will not be liable for, the website being temporarily unavailable due to technical issues beyond our control.</span></p>','CMStandard'),
 (233,10,1,6,'<h1>Heading 1</h1>\r\n<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Maecenas porttitor congue massa. Fusce posuere, magna sed pulvinar ultricies, purus lectus malesuada libero, sit amet commodo magna eros quis urna. <a href=\"/home.htm\">Nunc viverra imperdiet enim</a>. Fusce est. Vivamus a tellus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Proin pharetra nonummy pede.</p>\r\n<p><a class=\"arrowlink\" href=\"/home.htm\">Mauris et orci. Aenean nec lorem.</a></p>\r\n<hr />\r\n<h2>Heading 2</h2>\r\n<p>In porttitor. Donec laoreet nonummy augue. Suspendisse dui purus, scelerisque at, vulputate vitae, pretium mattis, nunc. Mauris eget neque at sem venenatis eleifend. Ut nonummy. Fusce aliquet pede non pede. Suspendisse dapibus lorem pellentesque magna. Integer nulla. Donec blandit feugiat ligula. Donec hendrerit, felis et imperdiet euismod, purus ipsum pretium metus, in lacinia nulla nisl eget sapien.</p>\r\n<h3>Heading 3</h3>\r\n<p>Donec ut est in lectus consequat consequat. Etiam eget dui. Aliquam erat volutpat. Sed at lorem in nunc porta tristique. Proin nec augue. Quisque aliquam tempor magna. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nunc ac magna. Maecenas odio dolor, vulputate vel, auctor ac, accumsan id, felis. Pellentesque cursus sagittis felis.</p>\r\n<h4>Heading 4</h4>\r\n<p>Pellentesque porttitor, velit lacinia egestas auctor, diam eros tempus arcu, nec vulputate augue magna vel risus. Cras non magna vel ante adipiscing rhoncus. Vivamus a mi. Morbi neque. Aliquam erat volutpat. Integer ultrices lobortis eros. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Proin semper, ante vitae sollicitudin posuere, metus quam iaculis nibh, vitae scelerisque nunc massa eget pede. Sed velit urna, interdum vel, ultricies vel, faucibus at, quam. Donec elit est, consectetuer eget, consequat quis, tempus quis, wisi.</p>\r\n<h5>Heading 5</h5>\r\n<p>In in nunc. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos hymenaeos. Donec ullamcorper fringilla eros. Fusce in sapien eu purus dapibus commodo. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Cras faucibus condimentum odio. Sed ac ligula. Aliquam at eros. Etiam at ligula et tellus ullamcorper ultrices. In fermentum, lorem non cursus porttitor, diam urna accumsan lacus, sed interdum wisi nibh nec nisl.</p>\r\n<h6>Heading 6</h6>\r\n<p>Ut tincidunt volutpat urna. Mauris eleifend nulla eget mauris. Sed cursus quam id felis. Curabitur posuere quam vel nibh. Cras dapibus dapibus nisl. Vestibulum quis dolor a felis congue vehicula. Maecenas pede purus, tristique ac, tempus eget, egestas quis, mauris. Curabitur non eros. Nullam hendrerit bibendum justo. Fusce iaculis, est quis lacinia pretium, pede metus molestie lacus, at gravida wisi ante at libero.</p>\r\n<blockquote>\r\n<p>Quisque ornare placerat risus. Ut molestie magna at mi. Integer aliquet mauris et nibh. Ut mattis ligula posuere velit. Nunc sagittis. Curabitur varius fringilla nisl. Duis pretium mi euismod erat. Maecenas id augue. Nam vulputate. Duis a quam non neque lobortis malesuada.</p>\r\n</blockquote>\r\n<ul>\r\n<li>Praesent euismod.</li>\r\n<li>Donec nulla augue, venenatis scelerisque, dapibus a, consequat at, leo.</li>\r\n<li>Pellentesque libero lectus, tristique ac, consectetuer sit amet, imperdiet ut, justo.</li>\r\n<li>Sed aliquam odio vitae tortor.</li>\r\n<li>Proin hendrerit tempus arcu.</li>\r\n</ul>\r\n<ol>\r\n<li>In hac habitasse platea dictumst.</li>\r\n<li>Suspendisse potenti.</li>\r\n<li>Vivamus vitae massa adipiscing est lacinia sodales.</li>\r\n<li>Donec metus massa, mollis vel, tempus placerat, vestibulum condimentum, ligula.</li>\r\n<li>Nunc lacus metus, posuere eget, lacinia eu, varius quis, libero.</li>\r\n</ol>\r\n<p>Aliquam nonummy adipiscing augue. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Maecenas porttitor congue massa. Fusce posuere, magna sed pulvinar ultricies, purus lectus malesuada libero, sit amet commodo magna eros quis urna. Nunc viverra imperdiet enim. Fusce est. Vivamus a tellus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Proin pharetra nonummy pede. Mauris et orci.</p>\r\n<p>Aenean nec lorem. In porttitor. Donec laoreet nonummy augue. Suspendisse dui purus, scelerisque at, vulputate vitae, pretium mattis, nunc. Mauris eget neque at sem venenatis eleifend. Ut nonummy. Fusce aliquet pede non pede. Suspendisse dapibus lorem pellentesque magna. Integer nulla. Donec blandit feugiat ligula.</p>','CMStandard'),
 (252,9,5,1,'','CMEnquiry');
/*!40000 ALTER TABLE `content` ENABLE KEYS */;


--
-- Definition of table `contentproperties`
--

DROP TABLE IF EXISTS `contentproperties`;
CREATE TABLE `contentproperties` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contentid` int(10) unsigned NOT NULL DEFAULT '0',
  `property` varchar(45) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_contentproperties_content` (`contentid`),
  CONSTRAINT `FK_contentproperties_content` FOREIGN KEY (`contentid`) REFERENCES `content` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `contentproperties`
--

/*!40000 ALTER TABLE `contentproperties` DISABLE KEYS */;
INSERT INTO `contentproperties` (`id`,`contentid`,`property`,`value`) VALUES 
 (30,252,'heading','Enquiry Form'),
 (31,252,'target','gareth@blacksquare.co.za');
/*!40000 ALTER TABLE `contentproperties` ENABLE KEYS */;


--
-- Definition of table `handlers`
--

DROP TABLE IF EXISTS `handlers`;
CREATE TABLE `handlers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `event` varchar(45) NOT NULL DEFAULT '',
  `type` enum('function','class','instance') NOT NULL DEFAULT 'function',
  `classname` varchar(128) NOT NULL DEFAULT '',
  `function` varchar(128) NOT NULL DEFAULT '',
  `path` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `EVENT` (`event`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `handlers`
--

/*!40000 ALTER TABLE `handlers` DISABLE KEYS */;
/*!40000 ALTER TABLE `handlers` ENABLE KEYS */;


--
-- Definition of table `htmpath`
--

DROP TABLE IF EXISTS `htmpath`;
CREATE TABLE `htmpath` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `htmpath` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `filename` varchar(45) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `att1` varchar(45) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `val1` varchar(45) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `att2` varchar(45) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `val2` varchar(45) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `att3` varchar(45) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `val3` varchar(45) CHARACTER SET latin1 NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQUE` (`filename`,`att1`,`val1`,`att2`,`val2`,`att3`,`val3`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;

--
-- Dumping data for table `htmpath`
--

/*!40000 ALTER TABLE `htmpath` DISABLE KEYS */;
INSERT INTO `htmpath` (`id`,`htmpath`,`filename`,`att1`,`val1`,`att2`,`val2`,`att3`,`val3`) VALUES 
 (1,'index.htm','index.php','id','3','','','',''),
 (2,'disclaimer.htm','index.php','id','4','','','',''),
 (3,'about_us.htm','index.php','id','7','','','',''),
 (4,'services.htm','index.php','id','9','','','',''),
 (6,'contact_us.htm','index.php','id','28','','','',''),
 (7,'main_menu.htm','index.php','id','1','','','',''),
 (8,'privacy_policy.htm','index.php','id','5','','','',''),
 (9,'style_test.htm','index.php','id','29','','','',''),
 (10,'widows_and_orphans.htm','index.php','id','2','','','',''),
 (14,'portfolio.htm','index.php','id','21','','','','');
/*!40000 ALTER TABLE `htmpath` ENABLE KEYS */;


--
-- Definition of table `layouts`
--

DROP TABLE IF EXISTS `layouts`;
CREATE TABLE `layouts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pageid` int(10) unsigned NOT NULL DEFAULT '0',
  `position` int(10) unsigned NOT NULL DEFAULT '0',
  `classname` varchar(45) NOT NULL DEFAULT 'OneColumn',
  PRIMARY KEY (`id`),
  KEY `FK_layouts_pages` (`pageid`),
  CONSTRAINT `FK_layouts_pages` FOREIGN KEY (`pageid`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `layouts`
--

/*!40000 ALTER TABLE `layouts` DISABLE KEYS */;
INSERT INTO `layouts` (`id`,`pageid`,`position`,`classname`) VALUES 
 (4,4,1,'OneColumn'),
 (5,5,1,'OneColumn'),
 (6,7,1,'OneColumn'),
 (7,9,1,'OneColumn'),
 (9,28,1,'TwoColumnLeft'),
 (10,29,1,'OneColumn'),
 (42,3,1,'OneColumn'),
 (51,21,1,'TwoColumnLeft');
/*!40000 ALTER TABLE `layouts` ENABLE KEYS */;


--
-- Definition of table `layoutsettings`
--

DROP TABLE IF EXISTS `layoutsettings`;
CREATE TABLE `layoutsettings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL DEFAULT '',
  `type` varchar(45) NOT NULL DEFAULT '',
  `data` varchar(1024) NOT NULL DEFAULT '',
  `default` varchar(1024) NOT NULL DEFAULT '',
  `label` varchar(45) NOT NULL DEFAULT '',
  `description` varchar(1024) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `layoutsettings`
--

/*!40000 ALTER TABLE `layoutsettings` DISABLE KEYS */;
/*!40000 ALTER TABLE `layoutsettings` ENABLE KEYS */;


--
-- Definition of table `layoutvalues`
--

DROP TABLE IF EXISTS `layoutvalues`;
CREATE TABLE `layoutvalues` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `layoutid` int(10) unsigned NOT NULL DEFAULT '0',
  `settingid` int(10) unsigned NOT NULL DEFAULT '0',
  `value` varchar(1024) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `FK_layoutvalues_layout` (`layoutid`),
  KEY `FK_layoutvalues_setting` (`settingid`),
  CONSTRAINT `FK_layoutvalues_layout` FOREIGN KEY (`layoutid`) REFERENCES `layouts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_layoutvalues_setting` FOREIGN KEY (`settingid`) REFERENCES `layoutsettings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `layoutvalues`
--

/*!40000 ALTER TABLE `layoutvalues` DISABLE KEYS */;
/*!40000 ALTER TABLE `layoutvalues` ENABLE KEYS */;


--
-- Definition of table `listfields`
--

DROP TABLE IF EXISTS `listfields`;
CREATE TABLE `listfields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `listid` int(10) unsigned NOT NULL DEFAULT '0',
  `label` varchar(128) NOT NULL DEFAULT '',
  `type` varchar(45) NOT NULL DEFAULT '',
  `position` int(10) unsigned NOT NULL DEFAULT '0',
  `data` text,
  `name` varchar(45) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `FK_listfields_list` (`listid`),
  CONSTRAINT `FK_listfields_list` FOREIGN KEY (`listid`) REFERENCES `lists` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `listfields`
--

/*!40000 ALTER TABLE `listfields` DISABLE KEYS */;
/*!40000 ALTER TABLE `listfields` ENABLE KEYS */;


--
-- Definition of table `listitemfields`
--

DROP TABLE IF EXISTS `listitemfields`;
CREATE TABLE `listitemfields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemid` int(10) unsigned NOT NULL DEFAULT '0',
  `fieldid` int(10) unsigned NOT NULL DEFAULT '0',
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_listitemfields_item` (`itemid`),
  KEY `FK_listitemfields_field` (`fieldid`),
  CONSTRAINT `FK_listitemfields_field` FOREIGN KEY (`fieldid`) REFERENCES `listfields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_listitemfields_item` FOREIGN KEY (`itemid`) REFERENCES `listitems` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `listitemfields`
--

/*!40000 ALTER TABLE `listitemfields` DISABLE KEYS */;
/*!40000 ALTER TABLE `listitemfields` ENABLE KEYS */;


--
-- Definition of table `listitems`
--

DROP TABLE IF EXISTS `listitems`;
CREATE TABLE `listitems` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `listid` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `position` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_listitems_lists` (`listid`),
  CONSTRAINT `FK_listitems_lists` FOREIGN KEY (`listid`) REFERENCES `lists` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `listitems`
--

/*!40000 ALTER TABLE `listitems` DISABLE KEYS */;
/*!40000 ALTER TABLE `listitems` ENABLE KEYS */;


--
-- Definition of table `lists`
--

DROP TABLE IF EXISTS `lists`;
CREATE TABLE `lists` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL DEFAULT '',
  `code` varchar(45) NOT NULL DEFAULT '',
  `itemname` varchar(45) NOT NULL DEFAULT 'entry',
  `width` int(10) unsigned NOT NULL DEFAULT '400',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `lists`
--

/*!40000 ALTER TABLE `lists` DISABLE KEYS */;
/*!40000 ALTER TABLE `lists` ENABLE KEYS */;


--
-- Definition of table `modules`
--

DROP TABLE IF EXISTS `modules`;
CREATE TABLE `modules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(45) NOT NULL DEFAULT '',
  `name` varchar(45) NOT NULL DEFAULT '',
  `path` varchar(255) NOT NULL DEFAULT '',
  `classname` varchar(45) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `modules`
--

/*!40000 ALTER TABLE `modules` DISABLE KEYS */;
/*!40000 ALTER TABLE `modules` ENABLE KEYS */;


--
-- Definition of table `pages`
--

DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent` int(10) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `keywords` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `menuname` varchar(45) NOT NULL DEFAULT '',
  `type` varchar(45) NOT NULL DEFAULT 'content',
  `candelete` tinyint(1) NOT NULL DEFAULT '0',
  `forwardurl` varchar(255) NOT NULL DEFAULT '',
  `position` int(10) unsigned NOT NULL DEFAULT '0',
  `specialpage` varchar(45) NOT NULL DEFAULT '',
  `date` int(10) unsigned NOT NULL DEFAULT '0',
  `canedit` tinyint(1) NOT NULL DEFAULT '0',
  `published` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `pathstub` varchar(64) NOT NULL DEFAULT '',
  `updated` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_pages_parent` (`parent`) USING BTREE,
  CONSTRAINT `FK_pages_parent` FOREIGN KEY (`parent`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `pages`
--

/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` (`id`,`parent`,`title`,`keywords`,`description`,`menuname`,`type`,`candelete`,`forwardurl`,`position`,`specialpage`,`date`,`canedit`,`published`,`pathstub`,`updated`) VALUES 
 (1,NULL,'Main Menu','','','Main Menu','special',0,'',1,'',0,0,1,'',1362896127),
 (2,NULL,'Widows and Orphans','','','widows and orphans','special',0,'',3,'',0,0,1,'',1362896127),
 (3,1,'Home','','','Home','content',0,'',1,'',1240838873,1,1,'home',1368430396),
 (4,2,'Disclaimer','','','Disclaimer','content',1,'',1,'',1240838873,1,2,'disclaimer',1363339469),
 (5,2,'Privacy Policy','','','Privacy Policy','content',1,'',2,'',1240838873,1,1,'privacy_policy',1368430764),
 (7,1,'About Us','','','About Us','content',1,'',2,'',1297754934,1,1,'about_us',1362896127),
 (9,1,'Services','','','Services','content',1,'',3,'',1297776189,1,1,'services',1368430425),
 (21,1,'Portfolio','','','Portfolio','content',1,'',4,'',1299074323,1,1,'portfolio',1368190158),
 (28,1,'Contact Us','','','Contact Us','content',1,'',6,'',1333100099,1,1,'contact_us',1368430451),
 (29,2,'Style Test','','','Style Test','content',1,'',3,'',1333100167,1,0,'',1363339756);
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;


--
-- Definition of table `pagesettings`
--

DROP TABLE IF EXISTS `pagesettings`;
CREATE TABLE `pagesettings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL DEFAULT '',
  `type` varchar(45) NOT NULL DEFAULT '',
  `data` varchar(1024) NOT NULL DEFAULT '',
  `default` varchar(1024) NOT NULL DEFAULT '',
  `label` varchar(45) NOT NULL DEFAULT '',
  `description` varchar(1024) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `NAME` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `pagesettings`
--

/*!40000 ALTER TABLE `pagesettings` DISABLE KEYS */;
/*!40000 ALTER TABLE `pagesettings` ENABLE KEYS */;


--
-- Definition of table `pagevalues`
--

DROP TABLE IF EXISTS `pagevalues`;
CREATE TABLE `pagevalues` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pageid` int(10) unsigned NOT NULL DEFAULT '0',
  `settingid` int(10) unsigned NOT NULL DEFAULT '0',
  `value` varchar(1024) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `FK_pagevalues_page` (`pageid`),
  KEY `FK_pagevalues_settings` (`settingid`),
  CONSTRAINT `FK_pagevalues_page` FOREIGN KEY (`pageid`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_pagevalues_settings` FOREIGN KEY (`settingid`) REFERENCES `pagesettings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `pagevalues`
--

/*!40000 ALTER TABLE `pagevalues` DISABLE KEYS */;
/*!40000 ALTER TABLE `pagevalues` ENABLE KEYS */;


--
-- Definition of table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL DEFAULT '',
  `title` varchar(45) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `value` varchar(1024) NOT NULL DEFAULT '',
  `type` varchar(45) NOT NULL DEFAULT '',
  `group` varchar(45) NOT NULL DEFAULT '',
  `data` varchar(1024) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `settings`
--

/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` (`id`,`name`,`title`,`description`,`value`,`type`,`group`,`data`) VALUES 
 (1,'defaulttitle','Default Page Title','This is used by search engines. You should enter a default page title that will be used in case you forget to give new pages of the website an appropriate title.','','text','Meta Data',''),
 (2,'defaultkeywords','Default Keywords','When you create a page you give it keywords so search engines will find it. Default keywords will be used if you do not enter any keywords on a new page.','','text','Meta Data',''),
 (3,'defaultdescription','Default Description','When you create a page you give it a description that appears under its listing in a search result. The default description will be used if you do not enter any description on a new page.','','text','Meta Data',''),
 (4,'adminemail','Administrator Email','This should be the email address of the person who will handle any queries or automated emails from the website.','admin@blacksquare.co.za','text','Contact Details',''),
 (10,'titleprefix','Page Title Prefix','You can give your site a Page Title Prefix so that search engines see each page with the prefix first, such as \'ABC Company - About Us\', \'ABC Company - Our products\'.','','text','Meta Data',''),
 (11,'contactemail','Contact Email','Enter the email address, to be used on the contact block.','admin@blacksquare.co.za','text','Contact Details',''),
 (12,'contactphone','Contact Phone','This should be the phone number for general enquiries.','+27 (0)31 201 3913','text','Contact Details',''),
 (14,'contactfax','Contact Fax','Enter the fax number, to be used on the contact block','','text','Contact Details',''),
 (15,'contactcell','Contact Cell','Enter the cell number, to be used on the contact block','','text','Contact Details',''),
 (16,'typekit','Typekit Code','Enter the code for the Typekit Font Kit','','text','External Accounts',''),
 (17,'googlecode','Google Analytics Code','Enter the UA number for Google Analytics','','text','External Accounts',''),
 (18,'googleverification','Google Webmaster Verification','Enter the verification code for Google Webmaster Tools','','text','External Accounts',''),
 (19,'bingverification','Bing Webmaster Tools','Enter the verification code for Bing Webmaster Tools','','text','External Accounts',''),
 (20,'skin','Skin','','AloeBlue','hidden','',''),
 (32,'useadminrights','Use Admin Rights','Whether or not to use Admin rights. If not, all logged in users will have access to the CMS.','1','hidden','','');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;


--
-- Definition of table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(45) NOT NULL DEFAULT '',
  `username` varchar(45) NOT NULL DEFAULT '',
  `password` varchar(45) NOT NULL DEFAULT '',
  `status` enum('active','suspended') NOT NULL DEFAULT 'active',
  `datecreated` int(10) unsigned NOT NULL DEFAULT '0',
  `lastlogin` int(10) unsigned NOT NULL DEFAULT '0',
  `remembercode` varchar(45) NOT NULL DEFAULT '',
  `rights` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Username` (`username`),
  UNIQUE KEY `Email` (`email`),
  KEY `Status` (`status`),
  KEY `RememberCode` (`remembercode`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user`
--

/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` (`id`,`name`,`email`,`phone`,`username`,`password`,`status`,`datecreated`,`lastlogin`,`remembercode`,`rights`) VALUES 
 (1,'Administrator','gareth@blacksquare.co.za','031 811 4147','admin','$1$ZU/.ur1.$/ry7TB5iHW.j3z6rB/JQX1','active',0,1368430295,'McKUXDYHH4hdaDS',4294967295);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;


--
-- Definition of table `user_rights`
--

DROP TABLE IF EXISTS `user_rights`;
CREATE TABLE `user_rights` (
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `const` varchar(128) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user_rights`
--

/*!40000 ALTER TABLE `user_rights` DISABLE KEYS */;
INSERT INTO `user_rights` (`id`,`const`,`description`) VALUES 
 (0,'none','User has no rights'),
 (1,'admuser','Administer users'),
 (2,'admadmin','Create and administer administrators'),
 (4,'system','Perform system functions'),
 (8,'editcontent','Edit content'),
 (16,'editlists','Edit lists'),
 (32,'editresources','Edit resources');
/*!40000 ALTER TABLE `user_rights` ENABLE KEYS */;




/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
