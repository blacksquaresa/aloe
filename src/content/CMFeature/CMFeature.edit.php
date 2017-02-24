<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('../../lib/Global.lib.php');

$module = 'CMFeature';
$pageid = $_REQUEST['pageid'];
$blockid = $_REQUEST['blockid'] | 0;
$layout = $_REQUEST['layout'];
$col = $_REQUEST['col'];
$pos = $_REQUEST['pos'] | 0;

if(!empty($_REQUEST['submit'])){
	cleanUserInput(array_keys($_REQUEST));
	$properties = array();
	$properties['heading'] = $_REQUEST['heading'];
	$properties['subtitle'] = $_REQUEST['subtitle'];
	$content = $_REQUEST['content'];
	$properties['image'] = $_REQUEST['image'];
	$properties['link'] = $_REQUEST['link'];
	$properties['linktext'] = $_REQUEST['linktext'];
	$properties['drawline'] = $_REQUEST['drawline']?1:0;
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

if($blockid && is_numeric($blockid)){
	$block = ContentModule::getContentBlock($blockid,true);
	$heading = clean($block->heading);
	$subtitle = clean($block->subtitle);
	$content = $block->content;
	$image = clean($block->image);
	$orientation = $block->orientation;
	$link = clean($block->link);
	$linktext = clean($block->linktext);
	$drawline = $block->drawline;
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
					<td class="field"><input type="text" id="heading" name="heading" class="edt_textbox" value="<?=$heading?>" /></td>
				</tr>
				<tr>
					<td class="label_right" nowrap>Sub Title:</td>
					<td class="field"><input type="text" id="subtitle" name="subtitle" class="edt_textbox" value="<?=$subtitle?>" /></td>
				</tr>
				<tr>
					<td class="label_right label_top">Text:</td>
					<td class="field"><textarea id="content" name="content" rows="12" class="edt_textarea"><?=$content?></textarea></td>
				</tr>
				<tr>
					<td class="label_right">Image:</td>
					<td class="field" colspan="2"><input type="text" id="image" name="image" class="edt_textbox230" value="<?=$image?>"><a href="javascript:parent.PopupManager.showImageSelector(null,'image','PopupManager.popups.<?=$module?>.GetOwnerDocument()');"><img align="top" src="../../images/admin/common/select.png"></a></td>	
				</tr>
				<tr>
					<td class="label_right" nowrap>Link URL:</td>
					<td class="field"><input type="text" id="link" name="link" class="edt_textbox230" value="<?=$link?>"><a href="javascript:parent.PopupManager.showLinkSelector(null,'link','PopupManager.popups.<?=$module?>.GetOwnerDocument()');"><img align="top" src="../../images/admin/common/select.png"></a></td>
				</tr>
				<tr>
					<td class="label_right" nowrap>Link Text:</td>
					<td class="field"><input type="text" id="linktext" name="linktext" class="edt_textbox" value="<?=$linktext?>"></td>
				</tr>
				<tr>
					<td class="field" colspan="2">
						<input type="checkbox" id="drawline" name="drawline"<?=$drawline?' checked':''?>/> <label for="drawline" class="label_left">Draw dividing line?</label>
					</td>
				</tr>
				<tr>
					<td colspan="3"><input type="submit" name="submit" value="Submit" class="edt_button"></td>
				</tr>
			</table>
		</form>
		<script>if(parent && parent.PopupManager) parent.PopupManager.prepare('<?=$module?>',0,0,'Basic Feature Editor');</script>
	</body>
</html>