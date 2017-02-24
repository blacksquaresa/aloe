<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('../lib/Global.lib.php');
$usermanager->authenticateUser();

$clipboard = $_SESSION['ContentBlockClipboard'];
$blockid = $clipboard['id'];
$onload = ' onload="parent.PopupManager.setSize(\'clipboard\','.$clipboard['width'].',400)"';
$module = ContentModule::getContentBlock($blockid,true);
$content = $module->drawContentBlock();
$css = file_exists($GLOBALS['settings']->contentpath . $module->modulename . '/' . $module->modulename . '.css')?'<link rel="StyleSheet" href="'.$GLOBALS['settings']->contentpathweb . $module->modulename . '/' . $module->modulename . '.css'.'" type="text/css" />':'';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="StyleSheet" href="../css/style.css" type="text/css" />
		<?=$css?>
		<link rel="StyleSheet" href="../css/contentframe.css" type="text/css" />
		<link rel="StyleSheet" href="../css/content.css" type="text/css" />
	</head>
	<body<?=$onload?>>
		<?=$content?>
	</body>
</html>