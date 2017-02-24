<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
$path = dirname(dirname(dirname(__FILE__)));
require_once($path.'/lib/Global.lib.php');
$usermanager->authenticateUser();
require_once($path.'/lib/Install.lib.php');

global $message;

$GLOBALS['db']->begintransaction();
removeList('news',$message) or die($message);
removePagesBySpecial('news.php',$message) or die($message);
removeAdminLink('News',$message) or die($message);
removeUserRight('editnews',$message) or die($message);
removeEventHandler('linkSelectorLoading','function','','linkSelectorLoadingHandler','modules/News/News.lib.php',$message) or die($message);
removeEventHandler('sitemapLoading','function','','sitemapLoadingHandler','modules/News/News.lib.php',$message) or die($message);
removeEventHandler('htmpathResetting','function','','resetHTMPathHandler','modules/News/News.lib.php',$message) or die($message);
flushHTMCache('news.php',null,null,null,$message) or die($message);
removeFile($GLOBALS['documentroot'].'/news.php',$message) or die($message);
$GLOBALS['db']->committransaction();
dropTable('newscatlink',$message) or die($message);
dropTable('news',$message) or die($message);
$message .= 'Removal completed.<br />';
echo $message;
?>