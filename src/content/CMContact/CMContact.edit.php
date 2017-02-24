<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('../../lib/Global.lib.php');

$module = 'CMContact';
$pageid = $_REQUEST['pageid'];
$blockid = $_REQUEST['blockid'] | 0;
$layout = $_REQUEST['layout'];
$col = $_REQUEST['col'];
$pos = $_REQUEST['pos'] | 0;

if(!empty($_REQUEST['submit'])){
	cleanUserInput(array('content','email','phone','fax','cell','physical','postal'));
	$properties = array();
	$content = $_REQUEST['content'];
	$properties['email'] = $_REQUEST['email'];
	$properties['phone'] = $_REQUEST['phone'];
	$properties['fax'] = $_REQUEST['fax'];
	$properties['cell'] = $_REQUEST['cell'];
	$properties['physical'] = $_REQUEST['physical'];
	$properties['postal'] = $_REQUEST['postal'];
	$properties['map'] = $_REQUEST['map'];
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
	$content = clean($block->content);
	$email = clean($block->email);
	$phone = clean($block->phone);
	$fax = clean($block->fax);
	$cell = clean($block->cell);
	$physical = $block->physical;
	$postal = $block->postal;
	$map = $block->map;
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
			<table class="edt_table" style="border-collapse: collapse;" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td class="label_right">Heading:</td>
					<td class="field"><input type="text" name="content" id="content" class="edt_textbox" value="<?=$content?>" /></td>
					<td class="label_right" style="padding-left: 20px;" nowrap>Physical Address:</td>
					<td class="field" rowspan="2"><textarea name="physical" id="physical" class="edt_textbox"><?=$physical?></textarea></td>
				</tr>
				<tr>
					<td class="label_right">Email:</td>
					<td class="field"><input type="text" name="email" id="email" class="edt_textbox" value="<?=$email?>" /></td>
				</tr>
				<tr>
					<td class="label_right">Phone:</td>
					<td class="field"><input type="text" name="phone" id="phone" class="edt_textbox" value="<?=$phone?>" /></td>
					<td class="label_right">Postal Address:</td>
					<td class="field" rowspan="2"><textarea name="postal" id="postal" class="edt_textbox"><?=$postal?></textarea></td>
				</tr>
				<tr>
					<td class="label_right">Fax:</td>
					<td class="field"><input type="text" name="fax" id="fax" class="edt_textbox" value="<?=$fax?>" /></td>
				</tr>
				<tr>
					<td class="label_right">Cell:</td>
					<td class="field" valign="top"><input type="text" name="cell" id="cell" class="edt_textbox" value="<?=$cell?>" /></td>
					<td class="label_right" valign="top" style="padding-top: 3px;">Google Map:</td>
					<td class="field"><textarea name="map" id="map" class="edt_textbox"><?=$map?></textarea></td>
				</tr>
				<tr>
					<td colspan="3"><input type="submit" name="submit" value="Submit" class="edt_button"></td>
				</tr>
			</table>
		</form>
		<script>if(parent && parent.PopupManager) parent.PopupManager.prepare('<?=$module?>',0,0,'Contact Details Editor');</script>
	</body>
</html>