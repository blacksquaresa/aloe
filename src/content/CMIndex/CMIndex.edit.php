<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('../../lib/Global.lib.php');

$module = 'CMIndex';
$pageid = $_REQUEST['pageid'];
$blockid = $_REQUEST['blockid'] | 0;
$layout = $_REQUEST['layout'];
$col = $_REQUEST['col'];
$pos = $_REQUEST['pos'] | 0;

if(!empty($_REQUEST['submit'])){
	cleanUserInput(array_keys($_REQUEST));
	$properties = array();
	$content = '';
	$properties['heading'] = $_REQUEST['heading'];
	$properties['relationship'] = $_REQUEST['relationship'];
	$properties['filter'] = $_REQUEST['filter'];
	$properties['display'] = $_REQUEST['display'];
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
	$relationship = clean($block->relationship);
	$filter = clean($block->filter);
	$display = $block->display;
}

function drawContentModules(){
	global $filter;
	$blocks = $GLOBALS['skin']->getEditorNames();
	sort($blocks);
	$res .= '<select name="filter" id="filter">';
	$res .= '<option value="">any content block</option>';
	foreach($blocks as $block){
		include($GLOBALS['settings']->contentpath . $block . '/'  . $block . '.config.php');
		$sel = $block==$filter?' selected':'';
		$res .= '<option value="'.$block.'"'.$sel.'>'.($ContentModules[$block]['name']).'</option>';
	}
	$res .= '</select>';
	return $res;
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
			<table class="edt_table" cellpadding="0"  cellspacing="0" border="0">
				<tr>
					<td class="label_left" nowrap>Heading: <input type="text" id="heading" name="heading" class="edt_textbox" value="<?=$heading?>" style="width: 270px;" /></td>
				</tr>
				<tr>
					<td class="edt_heading2" style="padding-top: 10px;" nowrap>Settings:</td>
				</tr>
				<tr>
					<td class="label_left" nowrap>
						<ul>
							<li>List pages that are: 
								<select name="relationship" id="relationship">
									<option value="child"<?=$relationship!='sibling'?' selected':''?>>children</option>
									<option value="sibling"<?=$relationship=='sibling'?' selected':''?>>siblings</option>
								</select>
								 of this page
							</li>
							<li>Include pages which contain: <?=drawContentModules()?></li>
							<li>Display list as: 
								<select name="display" id="display">
									<option value="list"<?=$display!='item'?' selected':''?>>a list of links</option>
									<option value="item"<?=$display=='item'?' selected':''?>>items, with a descripion</option>
								</select>
							</li>					
						</ul>
					</td>
				</tr>
				<tr>
					<td class="edt_submit" colspan="3"><input type="submit" name="submit" value="Submit" class="edt_button"></td>
				</tr>
			</table>
		</form>
		<script>if(parent && parent.PopupManager) parent.PopupManager.prepare('<?=$module?>',0,0,'Page Index Editor');</script>
	</body>
</html>