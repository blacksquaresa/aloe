<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('../../lib/Global.lib.php');

$rowid = $_REQUEST['rowid'];
$colid = $_REQUEST['colid'];
$colwidth = $_REQUEST['width'];
switch($colwidth){
	default:
		$width = $colwidth+42;
		$boxwidth = $colwidth;
		$popwidth = max($width,540);
		$style = $colwidth>820?'long':($colwidth<290?'compact':($colwidth<430?'short':'standard'));
		break;	
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="StyleSheet" href="/css/editors.css" type="text/css" />
		<link rel="StyleSheet" href="/css/contentframe.css" type="text/css" />
		<script src="../../js/Common.js" language="javascript"></script>
	</head>
	<body>
<?
$tiny = new TinyMCE();
$tiny->Init($style,$boxwidth);
		?>
		<form method="post">
			<input type="hidden" name="colid" id="colid" value="<?=$colid?>">
			<input type="hidden" name="rowid" id="rowid" value="<?=$rowid?>">

			<table class="edt_table" cellpadding="0" cellspacing="0" border="0" width="<?=$popwidth?>px" style="margin: 0px;">
				<tr>
					<td class="field" align="center">
						<textarea name="content" id="content" class="mceEditor" style="width: <?=$width?>px; height: 430px;"><?=$content?></textarea>
					</td>
				</tr>
				<tr>		
					<td align="right"><input type="button" name="submit" class="edt_button" value="Submit" onclick="parent.CMTable.selectHTML();" /></td>
				</tr>
			</table>
		</form>
		<script>
			if(parent && parent.PopupManager) parent.PopupManager.prepare('CMTable_html',<?=$popwidth+20?>,485,'HTML Cell Editor');
			var content = parent.CMTable.rows[<?=$rowid?>].cells[<?=$colid?>];
			setElementValue('content',content);
		</script>
	</body>
</html>