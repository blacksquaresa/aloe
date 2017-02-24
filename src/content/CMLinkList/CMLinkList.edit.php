<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('../../lib/Global.lib.php');

$module = 'CMLinkList';
$pageid = $_REQUEST['pageid'];
$blockid = $_REQUEST['blockid'] | 0;
try{ $block = ContentModule::getContentBlock($blockid); }catch(Exception $e){}
$layout = $_REQUEST['layout'];
$col = $_REQUEST['col'];
$pos = $_REQUEST['pos'] | 0;

if(!empty($_REQUEST['submit'])){
	cleanUserInput(array_keys($_REQUEST));
	$properties = array();
	$properties['header'] = $_REQUEST['header'];
	$properties['urls'] = array_filter($_REQUEST['url']);
	$properties['texts'] = array_filter($_REQUEST['text'],'cleantext');
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

function cleantext(&$val){
	if(empty($val)) return false;
	$val = stripslashes($val);
	return true;	
}

function drawLinksList(){
	global $block,$blockid;
	$res .= '<table cellspacing="0" cellpadding="2">';
	$i=0;
	if($block && is_array($block->urls) && count($block->urls)){
		for(; $i<count($block->urls); $i++){
			$url = 	$block->urls[$i];
			$text = $block->texts[$i];
			$samewindow = ($target=="same_window")? "selected":"";
			$newwindow = ($target=="new_window")? "selected":"";
			if(empty($text)) $text = '[no text defined]';
			$res .= '<tr id="linkrow_'.$i.'"><td>';
			$res .= '<input type="text" name="text['.$i.']" id="text_'.$i.'" class="edt_textbox" value="'.clean($text).'" />';
			$res .= '</td><td>';
			$res .= '<input type="text" name="url['.$i.']" id="url_'.$i.'" class="edt_textbox" value="'.clean($url).'" />';
			$res .= '<a href="javascript:parent.PopupManager.showLinkSelector(null,\'url_'.$i.'\',\'PopupManager.popups.CMLinkList.GetOwnerDocument()\');"><img src="../../images/admin/common/select.png" align="top" /></a>';
			$res .= '</td>';
			$res .= '<td width="20"><a href="javascript:objCMLinkList.DeleteLinkRow('.$i.')" ><img src="../../images/admin/common/delete.png" /></a>';
			$res .= '</td></tr>';
		}
	}
	$res .= '<tr id="linkrow_'.$i.'"><td>';
	$res .= '<input type="text" name="text['.$i.']" id="text_'.$i.'" class="edt_textbox" />';
	$res .= '</td><td>';
	$res .= '<input type="text" name="url['.$i.']" id="url_'.$i.'" class="edt_textbox" />';
	$res .= '<a href="javascript:parent.PopupManager.showLinkSelector(null,\'url_'.$i.'\',\'PopupManager.popups.CMLinkList.GetOwnerDocument()\');"><img src="../../images/admin/common/select.png" align="top" /></a>';
	$res .= '</td>';
	$res .= '<td width="20"><a href="javascript:objCMLinkList.AddNewLinkRow('.$blockid.','.($i+1).');"><img src="../../images/admin/common/add.png" /></a>';
	$res .= '</td></tr>';
	$res .= '</table>';
	return $res;
}

if($blockid && is_numeric($blockid)){
	$header = clean($block->header);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="StyleSheet" href="/css/editors.css" type="text/css" />
		<link rel="StyleSheet" href="/css/contentframe.css" type="text/css" />
		<link rel="StyleSheet" href="<?=$module?>.css" type="text/css" />
		<script src="CMLinkList.js" language="javascript"></script>
	</head>
	<body>
		<form method="post" name="details">
			<input type="hidden" name="layout" value="<?=$layout?>">
			<input type="hidden" name="col" value="<?=$col?>">
			<input type="hidden" name="pos" value="<?=$pos?>">
			<input type="hidden" name="blockid" value="<?=$blockid?>">
			<input type="hidden" name="pageid" value="<?=$pageid?>">
			<table cellpadding="0" cellspacing="0" style="width: 600px;">
				<tr>
					<td class="label_left" style="padding-left: 2px;">Heading:</td>
					<td class="field"><input type="text" id="header" name="header" class="edt_textbox" style="width: 524px;" value="<?=$header?>"></td>
				</tr>
				<tr>
					<td colspan="2" valign="top">
						<table cellspacing="0" cellpadding="2">
							<tr>
								<td class="label_left" style="width: 261px;padding-left: 2px;">Link text:</td>
								<td class="label_left" style="width: 261px;padding-left: 2px;">Link URL:</td>
							</tr>			
						</table>
					</td>
				</tr>
				<tr>
					<td colspan="2" valign="top">
						<div id="Linklist" style="width: 600px; height: 156px; overflow: auto;"><?=drawLinksList()?></div>			
					</td>
				</tr>
				<tr>
					<td style="padding-top: 7px;" colspan="2"><input type="submit" name="submit" value="Submit" class="edt_button"></td>
				</tr>
			</table>
		</form>
		<script>if(parent && parent.PopupManager) parent.PopupManager.prepare('<?=$module?>',0,0,'Links List Editor');</script>
	</body>
</html>