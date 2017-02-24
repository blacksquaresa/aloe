<?php
/**
 * The Global library builds the initial framework for each request.
 * 
 * This file should be included first into every page on the site. It performs the following:
 *  - Defines the document and we roots for this page
 *  - Sets the include path to include all class, ajax, control, content, module, include and library files
 *  - Builds a new Settings module to hold all system settings. This in turn builds a global Database object as well
 *  - Sets error reporting and timezone based on settings
 *  - Starts the session
 *  - Autmatically logs in new users who have the correct cookies set
 * 
 * @package Library
 * @subpackage Global
 * @since 2.0
 **/
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

/**
 * The current version of Aloe
 */
define('ALOE_VERSION','2.4');

// Set global paths
$GLOBALS['documentroot'] = dirname($_SERVER['SCRIPT_FILENAME']);
if(empty($GLOBALS['webroot'])) $GLOBALS['webroot'] = '';
while(!file_exists($GLOBALS['documentroot'] . (substr($GLOBALS['documentroot'],-1) == DIRECTORY_SEPARATOR?'':DIRECTORY_SEPARATOR) . 'config.php') && trim($GLOBALS['documentroot'],'\/') != trim($_SERVER['DOCUMENT_ROOT'],'\/')){
	$GLOBALS['documentroot'] = dirname($GLOBALS['documentroot']);
	$GLOBALS['webroot'] = '../' . $GLOBALS['webroot'];
}
set_include_path(get_include_path() . PATH_SEPARATOR . $GLOBALS['documentroot'] . '/lib' . PATH_SEPARATOR . $GLOBALS['documentroot'] . '/includes' . PATH_SEPARATOR . $GLOBALS['documentroot'] . '/class' . PATH_SEPARATOR . $GLOBALS['documentroot'] . '/ajax' . PATH_SEPARATOR . $GLOBALS['documentroot'] . '/controls' . PATH_SEPARATOR . $GLOBALS['documentroot'] . '/content' . PATH_SEPARATOR . $GLOBALS['documentroot'] . '/modules');

// Register a class autoloader function
spl_autoload_register('Globals_autoloader');

require_once('Events.lib.php');
$settings = new Settings();
$agent = new Agent();
loadEventHandlers();
$usermanager = UserManager::getUserManager();

eval("\$err = " . $GLOBALS['settings']->errorlevel . ";");
error_reporting($err);
date_default_timezone_set($GLOBALS['settings']->timezone);
$GLOBALS['skin'] = Skin::getSkin($GLOBALS['settings']->skin);

session_start();

// Login user if they selected "Remember Me"
if(!isset($_SESSION['user'])){
	$usr = $usermanager->checkRememberCookie();
	if($usr){
		$_SESSION['user'] = $usr;
	}
}

if($settings->useadminrights){
	$rights = $GLOBALS['db']->select("select * from user_rights");
	foreach($rights as $right){
		define('RIGHT_'.strtoupper($right['const']),intval($right['id']));	
	}
}

function Globals_autoloader($classname){
	$filename = $classname.'.class.php';
	if(file_exists($GLOBALS['documentroot'].'/class/'.$filename)){
		include_once($GLOBALS['documentroot'].'/class/'.$filename);
		return;
	}
	if(file_exists($GLOBALS['documentroot'].'/content/'.$classname.'/'.$filename)){
		include_once($GLOBALS['documentroot'].'/content/'.$classname.'/'.$filename);
		return;
	}
	if(file_exists($GLOBALS['documentroot'].'/popups/'.$classname.'.pop.php')){
		include_once($GLOBALS['documentroot'].'/popups/'.$classname.'.pop.php');
		return;
	}
	$controlpaths = glob($GLOBALS['documentroot'].'/controls/*',GLOB_ONLYDIR);
	foreach($controlpaths as $conpath){
		if(file_exists($conpath . '/' . $filename)){
			include_once($conpath . '/' . $filename);
			return;
		}
	}
	$modulepaths = glob($GLOBALS['documentroot'].'/modules/*',GLOB_ONLYDIR);
	foreach($modulepaths as $modpath){
		if(file_exists($modpath . '/' . $filename)){
			include_once($modpath . '/' . $filename);
			return;
		}
		if(file_exists($modpath . '/' . $classname . '.mod.php')){
			include_once($modpath . '/' . $classname . '.mod.php');
			return;
		}
	}
	$skinpaths = glob($GLOBALS['documentroot'].'/skins/*',GLOB_ONLYDIR);
	foreach($skinpaths as $skinpath){
		if(file_exists($skinpath . '/' . $filename)){
			include_once($skinpath . '/' . $filename);
			return;
		}
		if(file_exists($skinpath . '/' . $classname . '.skin.php')){
			include_once($skinpath . '/' . $classname . '.skin.php');
			return;
		}
		if(file_exists($skinpath . '/layouts/' . $classname . '.lay.php')){
			include_once($skinpath . '/layouts/' . $classname . '.lay.php');
			return;
		}
	}
}
?>