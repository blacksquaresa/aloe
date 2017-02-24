<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('../../lib/Global.lib.php');
require_once('../../lib/Content.lib.php');

$content = stripslashes($_REQUEST['content']);
$rowid = $_REQUEST['rowid'];
$colid = $_REQUEST['colid'];
$columnid = $_REQUEST['columnid'];
$align = $_REQUEST['align'];
$css = $GLOBALS['skin']->getFile('content/CMTable/CMTable.css',null,'web');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<?=getGlobalSkinCSS();?>
		<link rel="StyleSheet" href="<?=$css?>" type="text/css" />
		<link rel="StyleSheet" href="../../css/contentframe.css" type="text/css" />
		<style>p:last-child { margin-bottom: 0px; }</style>
		<?if(!empty($GLOBALS['settings']->typekit)){?>
		<script type="text/javascript" src="//use.typekit.net/<?=$GLOBALS['settings']->typekit?>.js"></script>
		<script type="text/javascript">try{Typekit.load();}catch(e){}</script>
		<?}?>
	</head>
	<body onload="parent.HTMLFrameLoaded(<?=$rowid?>,<?=$colid?>);">
		<div id="cover" class="cbl_invisible" style="width: 100%; height: 100%;cursor: pointer;" onclick="parent.clickHTMLCell(<?=$rowid?>,<?=$colid?>);"></div>
		<div id="site" style="padding: 0px;text-align: <?=$align?>">
			<div id="ContentColumn_<?=$columnid?>">
				<?=$content?>		
			</div>
			<div style="clear: both; height: 1px; font-size: 1px;"></div>
		</div>
	</body>
</html>