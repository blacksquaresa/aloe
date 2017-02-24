<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('../../lib/Global.lib.php');

$module = 'CMStandard';
$pageid = $_REQUEST['pageid'];
$blockid = $_REQUEST['blockid'] | 0;
$layout = $_REQUEST['layout'];
$col = $_REQUEST['col'];
$pos = $_REQUEST['pos'] | 0;

if(!empty($_REQUEST['submit'])){
	$content = $_REQUEST['content'];
	$page = Page::GetNewPage($pageid,true);
	if($blockid){
		$res = @$page->updateContentBlock($blockid, $content, $properties, $error);
	}else{
		$res = @$page->createContentBlock($col, $layout, $module, $content, $properties, $pos, $error);
	}
	$error = str_replace("'","&apos;",$error);
	$res = $res?$res:'false';
	echo "<script>parent.ContentBlockCompleteEdit('$res',$blockid,$col,$layout,'$module',$pos,'$error');</script>";
	exit;
}

$column = ContentColumn::GetColumn($col);
switch($column->width){
	default:
		$width = $column->width+42;
		$boxwidth = $column->width;
		$popwidth = max($width,540);
		$style = $column->width>820?'long':($column->width<290?'compact':($column->width<430?'short':'standard'));
		break;	
}

if($blockid && is_numeric($blockid)){
	$block = ContentModule::getContentBlock($blockid,true);
	$content = $block->content;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="StyleSheet" href="/css/editors.css" type="text/css" />
		<link rel="StyleSheet" href="/css/contentframe.css" type="text/css" />
		<link rel="StyleSheet" href="<?=$module?>.css" type="text/css" />
	</head>
	<body>
		<?
			$tiny = new TinyMCE();
			$tiny->Init($style,$boxwidth);
		?>
		<form method="post">
			<input type="hidden" name="layout" value="<?=$layout?>">
			<input type="hidden" name="col" value="<?=$col?>">
			<input type="hidden" name="pos" value="<?=$pos?>">
			<input type="hidden" name="blockid" value="<?=$blockid?>">
			<input type="hidden" name="pageid" value="<?=$pageid?>">

			<table class="edt_table" cellpadding="0"  cellspacing="0" border="0" width="<?=$popwidth?>px">
				<tr>
					<td class="field" align="center">
						<textarea name="content" id="content" class="mceEditor" style="width: <?=$width?>px; height: 430px;"><?=$content?></textarea>
					</td>
				</tr>
				<tr>		
					<td align="right"><input type="submit" name="submit" class="edt_button" value="Submit" /></td>
				</tr>
			</table>
		</form>
		<script>if(parent && parent.PopupManager) parent.PopupManager.prepare('<?=$module?>',<?=$popwidth+20?>,505,'Standard Content Editor');</script>
	</body>
</html>