<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
$path = dirname(dirname(dirname(__FILE__)));
require_once($path.'/lib/Global.lib.php');
$usermanager->authenticateUser();
require_once($path.'/lib/Install.lib.php');
global $message;

removeGlobalSetting('sitename',$message) or die($message);
removeGlobalSetting('menualignment',$message) or die($message);
removeGlobalSetting('floatvalue',$message) or die($message);
removeGlobalSetting('intvalue',$message) or die($message);
removeGlobalSetting('logo',$message) or die($message);
removeGlobalSetting('document',$message) or die($message);
removeGlobalSetting('file',$message) or die($message);
removeGlobalSetting('link',$message) or die($message);
removeGlobalSetting('headerback',$message) or die($message);
removeGlobalSetting('description',$message) or die($message);
removeGlobalSetting('authors',$message) or die($message);

removePageSetting('pagename',$message) or die($message);
removePageSetting('menualignment',$message) or die($message);
removePageSetting('floatvalue',$message) or die($message);
removePageSetting('intvalue',$message) or die($message);
removePageSetting('logo',$message) or die($message);
removePageSetting('document',$message) or die($message);
removePageSetting('file',$message) or die($message);
removePageSetting('link',$message) or die($message);
removePageSetting('headertext',$message) or die($message);
removePageSetting('description',$message) or die($message);
removePageSetting('authors',$message) or die($message);

removeLayoutSetting('layoutname',$message) or die($message);
removeLayoutSetting('style',$message) or die($message);
removeLayoutSetting('floatvalue',$message) or die($message);
removeLayoutSetting('intvalue',$message) or die($message);
removeLayoutSetting('background',$message) or die($message);
removeLayoutSetting('document',$message) or die($message);
removeLayoutSetting('file',$message) or die($message);
removeLayoutSetting('link',$message) or die($message);
removeLayoutSetting('headerback',$message) or die($message);
removeLayoutSetting('description',$message) or die($message);
removeLayoutSetting('authors',$message) or die($message);

?>