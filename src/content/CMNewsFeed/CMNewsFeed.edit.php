<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('../../lib/Global.lib.php');
$listmanager = ListManager::getListManager();

$module = 'CMNewsFeed';
$pageid = $_REQUEST['pageid'];
$blockid = $_REQUEST['blockid'] | 0;
$layout = $_REQUEST['layout'];
$col = $_REQUEST['col'];
$pos = $_REQUEST['pos'] | 0;

if(!empty($_REQUEST['submit'])){
	cleanUserInput(array_keys($_REQUEST));
	$properties = array();
	$content = $_REQUEST['content'];
	$properties['category'] = $_REQUEST['category'];
	$properties['items'] = $_REQUEST['items'];
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
	$category = clean($block->category);
	$content = clean($block->content);
	$items = clean($block->items);
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
				<table class="edt_table" cellpadding="0" cellspacing="0" width="328" border="0">
					<tr>
						<td class="label_right">Title:</td>
						<td class="field"><input type="text" id="content" name="content" class="edt_textbox" value="<?=$content?>" /></td>	
					</tr>
					<tr>
						<td class="label_right" nowrap="true">Category:</td>
						<td class="field"><?=$listmanager->drawListSingleOption('news','category',$category,'Any Category','class="edt_select"');?></td>						
					</tr>
					<tr>
						<td class="label_right">Display:</td>
						<td class="field" valign="top">
							<table>
								<tr>
									<td valign="top"><input type="text" id="items" name="items" size="3" value="<?=$items?>" /></td>
									<td class="label_left" style="padding-left: 4px;">articles in this feed</td>
								<tr>
							</table>
						</td>
					</tr>
					<tr>
						<td colspan="2"><input type="submit" name="submit" value="Submit" class="edt_button" /></td>
					</tr>
				</table>
		</form>
		<script>if(parent && parent.PopupManager) parent.PopupManager.prepare('<?=$module?>',0,0,'News Feed Editor');</script>
	</body>
</html>