<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('../../lib/Global.lib.php');

$module = 'CMRSSFeed';
$pageid = $_REQUEST['pageid'];
$blockid = $_REQUEST['blockid'] | 0;
$layout = $_REQUEST['layout'];
$col = $_REQUEST['col'];
$pos = $_REQUEST['pos'] | 0;

if(!empty($_REQUEST['submit'])){
	cleanUserInput(array_keys($_REQUEST));
	$properties = array();
	$properties['heading'] = $_REQUEST['heading'];
	$content = $_REQUEST['content'];
	$properties['items'] = $_REQUEST['items'];
	$properties['link'] = $_REQUEST['link'];
	$properties['linktext'] = $_REQUEST['linktext'];
	$page = Page::GetNewPage($pageid,true);
	if($blockid){
		$res = @$page->updateContentBlock($blockid, $content, $properties, $error);
	}else{
		$res = @$page->createContentBlock($col, $layout, $module, $content, $properties, $pos, $error);
	}
	$error = str_replace("'","&apos;",$error);
	$res = $res?$res:'false';
	echo "<script>parent.ContentBlockCompleteEdit('$res',$blockid,$col, $layout,'$module',$pos,'$error');</script>";
	exit;
}

if($blockid && is_numeric($blockid)){
	$block = ContentModule::getContentBlock($blockid,true);
	$heading = clean($block->heading);
	$content = $block->content;
	$items = $block->items;
	$link = clean($block->link);
	$linktext = clean($block->linktext);
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
		<form method="post" name="details">
			<input type="hidden" name="layout" value="<?=$layout?>">
			<input type="hidden" name="col" value="<?=$col?>">
			<input type="hidden" name="pos" value="<?=$pos?>">
			<input type="hidden" name="blockid" value="<?=$blockid?>">
			<input type="hidden" name="pageid" value="<?=$pageid?>">
			<table class="edt_table" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td class="label_right">Heading:</td>
					<td class="field" colspan="2"><input type="text" id="heading" name="heading" class="edt_textboxlong" value="<?=$heading?>" /></td>
				</tr>
				<tr>
					<td class="label_right" nowrap>RSS Feed URL:</td>
					<td class="field" colspan="2"><input type="text" id="content" name="content" class="edt_textboxlong" value="<?=$content?>" /></td>
				</tr>
				<tr>
					<td class="label_right" nowrap>Display:</td>
					<td class="label_left field" colspan="2"><input type="text" id="items" name="items" class="edt_textboxshort" value="<?=$items?>"> articles in the feed</td>
				</tr>
				<tr>
					<td class="label_right" nowrap>Link Text:</td>
					<td class="field" colspan="2"><input type="text" id="linktext" name="linktext" class="edt_textboxlong" value="<?=$linktext?>"></td>
				</tr>
				<tr>
					<td class="label_right" nowrap>Link URL:</td>
					<td class="field" colspan="2"><input type="text" id="link" name="link" class="edt_textbox350" value="<?=$link?>" /><a href="javascript:parent.PopupManager.showLinkSelector(null,'link','PopupManager.popups.<?=$module?>.GetOwnerDocument()');"><img src="../../images/admin/common/select.png" align="top" /></a></td>
				</tr>
				<tr>
					<td colspan="3"><input type="submit" name="submit" value="Submit" class="edt_button"></td>
				</tr>
			</table>
		</form>
		<script>if(parent && parent.PopupManager) parent.PopupManager.prepare('<?=$module?>',0,0,'RSS Feed Editor');</script>
	</body>
</html>