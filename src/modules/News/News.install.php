<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
$path = dirname(dirname(dirname(__FILE__)));
require_once($path.'/lib/Global.lib.php');
$usermanager->authenticateUser();
require_once($path.'/lib/Install.lib.php');

global $message;

// Perform reversable actions first, within a transaction
$GLOBALS['db']->begintransaction();
// Create News Category entries:
$listid = createList('News Categories','news','category',400,array(
			array('label'=>'Title','type'=>'text','position'=>1,'data'=>'','name'=>'title'),
			array('label'=>'Description','type'=>'text','position'=>2,'data'=>'multiple','name'=>'description'),
			array('label'=>'Keywords','type'=>'text','position'=>3,'data'=>'','name'=>'keywords')
			),$message) or die($message);

addListItem($listid,'General News',array(
			'title'=>'General News',
			'description'=>'General news articles, without any particular focus.',
			'keywords'=>'general,news'
			),$message) or die($message);

//Create Menu Item
addPage(PAGE_MAINMENU,'News','','','News','special','','news.php',0,0,0,'news','',null,$message) or die($message);

//Create Modules Table Entry
($right = addUserRight('editnews','Manage news articles',$message)) or die($message);
addAdminLink('News','News','/modules/News/allnews.admin.php','',50,$right,$message) or die($message);

// Add Event Handlers
addEventHandler('linkSelectorLoading','function','','linkSelectorLoadingHandler','modules/News/News.lib.php',$message) or die($message);
addEventHandler('sitemapLoading','function','','sitemapLoadingHandler','modules/News/News.lib.php',$message) or die($message);
addEventHandler('htmpathResetting','function','','resetHTMPathHandler','modules/News/News.lib.php',$message) or die($message);

// Flush out the HTMPaths table
flushHTMCache('news.php',null,null,null,$message) or die($message);

// move basic file to the root
// This is within the transaction because it is the only file action. If it fails, everything before it can be rolled back, and if it works, commit everything.
copyFile($GLOBALS['documentroot'].'/modules/News/news.php',$GLOBALS['documentroot'].'/news.php',$message) or die($message);
$GLOBALS['db']->committransaction();

// Now non-reversable actions, that cannot be within a transaction.
// Drop existing tables, if they exist
dropTable('newscatlink',$message) or die($message);
dropTable('news',$message) or die($message);

// Create News tables:
executeSQL("CREATE TABLE `news` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`title` varchar(128) NOT NULL DEFAULT '',
			`keywords` varchar(255) NOT NULL DEFAULT '',
			`description` varchar(255) NOT NULL DEFAULT '',
			`content` text NOT NULL,
			`date` int(10) unsigned NOT NULL DEFAULT '0',
			`pubinfo` varchar(128) NOT NULL DEFAULT '',
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB;",$message) or die($message);

executeSQL("CREATE TABLE `newscatlink` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`newsid` int(10) unsigned NOT NULL DEFAULT '0',
			`catid` int(10) unsigned NOT NULL DEFAULT '0',
			PRIMARY KEY (`id`),
			KEY `FK_newscatlink_news` (`newsid`),
			KEY `FK_newscatlink_cat` (`catid`),
			CONSTRAINT `FK_newscatlink_cat` FOREIGN KEY (`catid`) REFERENCES `listitems` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
			CONSTRAINT `FK_newscatlink_news` FOREIGN KEY (`newsid`) REFERENCES `news` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
			) ENGINE=InnoDB;",$message) or die($message);

$message .= 'Installation completed.<br />';	
echo $message;
?>