<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('../lib/Global.lib.php');
$usermanager->authenticateUser();

$blockid = $_REQUEST['id'];
$width = empty($_REQUEST['w'])?'100%':$_REQUEST['w'].'px';
$height = empty($_REQUEST['h'])?'100%':$_REQUEST['h'].'px';
$module = ContentModule::getContentBlock($blockid,true);
$content = $module->drawContentBlock();
$css = file_exists($GLOBALS['settings']->contentpath . $module->modulename . '/' . $module->modulename . '.css')?'<link rel="StyleSheet" href="'.$GLOBALS['settings']->contentpathweb . $module->modulename . '/' . $module->modulename . '.css'.'" type="text/css" />':'';
$skincss = file_exists($GLOBALS['skin']->path . '/content/' . $module->modulename . '/' . $module->modulename . '.css')?'<link rel="StyleSheet" href="'.$GLOBALS['skin']->webpath . '/content/' . $module->modulename . '/' . $module->modulename . '.css'.'" type="text/css" />':'';
$column = ContentColumn::GetColumn($module->columnid);
$sitewidth = $column->width.'px';
include($GLOBALS['settings']->contentpath . $module->modulename . '/' . $module->modulename . '.config.php');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<?=getGlobalSkinCSS();?>
		<?=$css?>
		<?=$skincss?>
		<link rel="StyleSheet" href="../css/contentframe.css" type="text/css" />
		<?if(!empty($GLOBALS['settings']->typekit)){?>
		<script type="text/javascript" src="//use.typekit.net/<?=$GLOBALS['settings']->typekit?>.js"></script>
		<script type="text/javascript">try{Typekit.load();}catch(e){}</script>
		<?}?>
	</head>
	<body onload="parent.ContentBlockFrameLoaded(<?=$blockid?>);">
		<div id="<?=$blockid?>_loading" class="cbl_loading" style="width: <?=$width?>; height: <?=$height?>;"></div>
		<div id="<?=$blockid?>_cover" class="cbl_invisible" style="width: <?=$width?>; height: <?=$height?>;">
			<div class="cbl_name" id="<?=$blockid?>_name"><?=$ContentModules[$module->modulename]['name']?></div>
		</div>
		<div id="site" style="width: <?=$sitewidth?>;">
			<div id="ContentColumn_<?=$module->columnid?>">
			<?=$content?>
			</div>
			<div style="clear: both; height: 1px; font-size: 1px;"></div>
		</div>
	</body>
</html>