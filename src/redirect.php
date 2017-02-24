<?php

// Ensure that local paths are accurate
$testpath = dirname($_REQUEST['path']);
if(substr($testpath,0,6) == 'admin/' || substr($testpath,0,26) == 'controls/tinymce/jscripts/'){
	include($_REQUEST['path']);
	exit;	
}
$GLOBALS['webroot'] = '';
while(!file_exists($testpath . DIRECTORY_SEPARATOR . 'config.php') && trim($testpath,'. ') != ''){
	$testpath = dirname($testpath);
	$GLOBALS['webroot'] = '../' . $GLOBALS['webroot'];
}
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('lib/Global.lib.php');

// check to see that the file doesn't actually exist in PHP form
$phppath = substr($_REQUEST['path'],0,-3) . 'php';
if(file_exists($phppath)){
	include($phppath);
	exit;	
}

// check the HTMPath database to find our target
require_once('lib/HTMPaths.lib.php');
$phppath = getPHPPath($_REQUEST['path']);
if($phppath){
	include($phppath);
	exit;	
}

// file not found - 404 error
include('404.php');
exit;	

?>