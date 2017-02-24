<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('../../lib/Global.lib.php');

$module = 'CMTable';
$pageid = $_REQUEST['pageid'];
$blockid = $_REQUEST['blockid'] | 0;
$layout = $_REQUEST['layout'];
$col = $_REQUEST['col'];
$pos = $_REQUEST['pos'] | 0;

if(!empty($_REQUEST['create'])){
	cleanUserInput(array_keys($_REQUEST));
	$properties = array();
	$properties['headerlead'] = $_REQUEST['headerlead'];
	$cols = $_REQUEST['columns'];
	$rows = $_REQUEST['rows'];
	if(!is_numeric($cols)||$cols<1) $cols = 3;
	if(!is_numeric($rows)||$rows<1) $rows = 3;
	for($i=1;$i<=$rows;$i++){
		for($j=1;$j<=$cols;$j++){
			$properties['data'][$i][$j] = '';
		}
	}
	$content = '';
	$page = Page::GetNewPage($pageid,true);
	$blockid = @$page->createContentBlock($col, $layout, $module, $content, $properties, $pos, $error);
	$error = str_replace("'","&apos;",$error);
	if(!$blockid){
		echo "<script>parent.ContentBlockCompleteEdit('false',0,$col,$layout,'$module',$pos,'$error');</script>";
		exit;
	}else{
		$script = "<script>parent.contentBlockManager.layouts[$layout].columns[$col].AppendContentBlock($blockid,$pos);</script>";
	}
}

if(!empty($_REQUEST['submit'])){
	cleanUserInput(array('header','layout','rowstyles','columnstyles','columntypes','columnwidths'));
	$properties = array();
	$properties['header'] = $_REQUEST['header'];
	$properties['layout'] = $_REQUEST['layout'];
	if(get_magic_quotes_gpc()){
		$properties['rowstyles'] = json_decode(stripslashes($_REQUEST['rowstyles']));
		$properties['columnstyles'] = json_decode(stripslashes($_REQUEST['columnstyles']));
		$properties['columntypes'] = json_decode(stripslashes($_REQUEST['columntypes']));
		$properties['data'] = json_decode(stripslashes($_REQUEST['data']));
	}else{
		$properties['rowstyles'] = json_decode($_REQUEST['rowstyles']);
		$properties['columnstyles'] = json_decode($_REQUEST['columnstyles']);
		$properties['columntypes'] = json_decode($_REQUEST['columntypes']);
		$properties['data'] = json_decode($_REQUEST['data']);
	}
	$properties['columnwidths'] = json_decode($_REQUEST['columnwidths']);
	$content = '';
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
	$header = clean($block->header);
}

function buildTable(){
	global $block,$col;
	$column = ContentColumn::GetColumn($block->columnid);
	$width = $column->width - $block->tableborder;
	$res .= '<script>';
	$res .= "CMTable.init($width,{$block->cellspacing},".json_encode($block->data).",".json_encode($block->rowstyles).",".json_encode($block->columnstyles).",".json_encode($block->columntypes).",".json_encode($block->columnwidths).",$col,'{$block->headerlead}');";
	$res .= "CMTable.draw();";
	$res .= '</script>';
	return $res;
}

function drawHeaderLeadOptions(){
	global $block;
	$selected = empty($block)?'none':$block->headerlead;
	$res .= '<div style="height: 31px;width: 235px;">';
	$res .= '<div class="headerlead'.($selected=='none'?' selected':'').'" id="headerlead_none" onclick="setLayout(\'none\');">&nbsp;</div>';
	$res .= '<div class="headerlead'.($selected=='header'?' selected':'').'" id="headerlead_header" onclick="setLayout(\'header\');">&nbsp;</div>';
	$res .= '<div class="headerlead'.($selected=='lead'?' selected':'').'" id="headerlead_lead" onclick="setLayout(\'lead\');">&nbsp;</div>';
	$res .= '<div class="headerlead'.($selected=='both'?' selected':'').'" id="headerlead_both" onclick="setLayout(\'both\');">&nbsp;</div>';
	$res .= '<input type="hidden" name="headerlead" id="headerlead" value="'.$selected.'" />';
	$res .= '</div>';
	return $res;
}

function drawIcons(){
	$files = glob(dirname(__FILE__).'/icons/*.{jpg,png,gif,jpeg}',	GLOB_BRACE);
	$res .= '<div class="icon_select">';
	foreach($files as $file){
		$webname = 'icons/'.basename($file);
		$res .= '<span class="icon_selector" onclick="selectIcon(\''.basename($file).'\');"><img src="'.$webname.'" /></span>';	
	}
	$res .= '</div>';
	return $res;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="StyleSheet" href="/css/editors.css" type="text/css" />
		<link rel="StyleSheet" href="/css/contentframe.css" type="text/css" />
		<link rel="StyleSheet" href="CMTable_editor.css" type="text/css" />
		<script src="CMTable.js" language="javascript"></script>
		<script src="../../js/Common.js" language="javascript"></script>
		<?=$script?>
	</head>
	<body>
		<script src="../../js/Popups.js" language="javascript"></script>
		<?if(empty($block)){?>
		<form method="post" name="details">
			<input type="hidden" name="layout" value="<?=$layout?>">
			<input type="hidden" name="col" value="<?=$col?>">
			<input type="hidden" name="pos" value="<?=$pos?>">
			<input type="hidden" name="blockid" value="<?=$blockid?>">
			<input type="hidden" name="pageid" value="<?=$pageid?>">
			<table cellpadding="0" cellspacing="0" border="0" class="edt_table">
				<tr>
					<td class="label_left" nowrap>Number of rows:</td>
					<td class="field"><input type="text" id="rows" name="rows" class="edt_textboxsmall" value="3" /></td>
					<td class="label_left" style="padding-left: 20px;" nowrap>Number of columns:</td>
					<td class="field"><input type="text" id="columns" name="columns" class="edt_textboxsmall" value="3" /></td>
				</tr>
				<tr>
					<td class="label_left" colspan="4">Header rows and Lead columns:</td>
				</tr>
				<tr>
					<td class="field" colspan="4"><?=drawHeaderLeadOptions();?></td>
				</tr>
				<tr>
					<td class="edt_submit" colspan="4"><input type="submit" name="create" value="Submit" class="edt_button"></td>
				</tr>
			</table>
		<?}else{?>
		<form method="post" name="details" onsubmit="CMTable.buildDataInputs()">
			<input type="hidden" name="col" value="<?=$col?>">
			<input type="hidden" name="pos" value="<?=$pos?>">
			<input type="hidden" name="blockid" value="<?=$blockid?>">
			<input type="hidden" name="pageid" value="<?=$pageid?>">
			<input type="hidden" name="data" id="data" value="">
			<input type="hidden" name="columnstyles" id="columnstyles" value="">
			<input type="hidden" name="rowstyles" id="rowstyles" value="">
			<input type="hidden" name="columntypes" id="columntypes" value="">
			<input type="hidden" name="columnwidths" id="columnwidths" value="">
			<table cellpadding="0" cellspacing="0" border="0" class="edt_table">
				<tr>
					<td class="label_left">Table title:</td>
					<td class="field" rowspan="2" valign="bottom" style="width: 235px;height: 41px;"><?=drawHeaderLeadOptions();?></td>
				</tr>
				<tr>
					<td class="field"><input type="text" id="header" name="header" style="width: 90%;" value="<?=$header?>" /></td>
				</tr>
				<tr>
					<td colspan="2"><div id="widths" style="height: 14px;margin: 20px 0px 10px 19px;"></div></td>
				</tr>
				<tr>
					<td colspan="2"><div id="columns" style="height: 20px;"></div></td>
				</tr>
				<tr>
					<td colspan="2" valign="top"><div id="table" style="margin: 0px; height: 220px; overflow: auto;"></div></td>
				</tr>
				<tr>
					<td class="edt_submit" colspan="2"><input type="submit" name="submit" value="Submit" class="edt_button"></td>
				</tr>
			</table>
		</form>
		<?=buildTable();?>
		<?}?>
		<script>if(parent && parent.PopupManager) parent.PopupManager.prepare('<?=$module?>',0,0,'Table Editor');</script>
		
		<div class="" style="display: none;" id="icon_selector">
			<input type="hidden" name="icon_selector_rowid" id="icon_selector_rowid" />
			<input type="hidden" name="icon_selector_colid" id="icon_selector_colid" />
			<?=drawIcons();?>
			<a class="icon_selector_remove" href="javascript:selectIcon('');">No icon</a>
		</div>
	</body>
</html>