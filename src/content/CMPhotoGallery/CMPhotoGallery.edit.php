<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('../../lib/Global.lib.php');

$module = 'CMPhotoGallery';
$pageid = $_REQUEST['pageid'];
$blockid = $_REQUEST['blockid'] | 0;
$layout = $_REQUEST['layout'];
$col = $_REQUEST['col'];
$pos = $_REQUEST['pos'] | 0;

if(!empty($_REQUEST['submit'])){
	cleanUserInput(array_keys($_REQUEST));
	$properties = array();
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
}elseif(empty($blockid)){
	$page = Page::GetNewPage($pageid,true);
	$blockid = @$page->createContentBlock($col, $layout, $module, '', null, $pos, $error);
	$error = str_replace("'","&apos;",$error);
	if(!$blockid){
		echo "<script>parent.ContentBlockCompleteEdit('false',0,$col,$layout,'$module',$pos,'$error');</script>";
		exit;
	}else{
		$script = "<script>parent.contentBlockManager.layouts[$layout].columns[$col].AppendContentBlock($blockid,$pos);</script>";
	}
}

$block = ContentModule::getContentBlock($blockid,true);
$windowwidth = max((5*($block->thumbwidth+26))+20,550);
$windowheight = 2*($block->thumbheight+32);

function drawGallery(){
	global $block,$windowwidth,$windowheight;
	$gallery = new GalleryView($block->id,'/content/CMPhotoGallery/galleries/'.$block->id,$windowwidth,$windowheight,$block->thumbwidth,$block->thumbheight,2,4,4);
	$gallery->prepareEdit(true,$block->width,$block->height);
	$gallery->resizestyle = IMAGERESIZETYPE_CROP;
	$gallery->addMetaDataField('caption','Caption','string');
	$gallery->storage = 'serialised';
	return $gallery->drawGallery();
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="StyleSheet" href="/css/editors.css" type="text/css" />
		<link rel="StyleSheet" href="/css/contentframe.css" type="text/css" />
		<link rel="StyleSheet" href="<?=$module?>.css" type="text/css" />
		<script src="../../js/aim.ajax.js"></script>
		<script src="../../js/Common.js"></script>
		<?=$script?>
		<?=$agent->init();?>
	</head>
	<body>
		<form method="post" name="details">
			<input type="hidden" name="layout" value="<?=$layout?>">
			<input type="hidden" name="col" value="<?=$col?>">
			<input type="hidden" name="pos" value="<?=$pos?>">
			<input type="hidden" name="blockid" value="<?=$blockid?>">
			<input type="hidden" name="pageid" value="<?=$pageid?>">
			<script src="<?= $GLOBALS['webroot'] ?>controls/galleryview/GalleryView.js"></script>
			<link rel="stylesheet" href="<?=$GLOBALS['webroot']?>controls/galleryview/galleryview.css" />
			<table class="edt_table" style="width: 550px;">
				<tr>
					<td class="label_left"><input type="button" name="addimage" id="addimage" value="Add an Image" onclick="gv.CreateImage();" class="greenbutton addbutton" style="width: 200px; margin-right: 40px;" />Images should be <?=$block->width?>px wide and <?=$block->height?>px tall.</td>
				</tr>
				<tr>
					<td style="padding: 20px 0px;"><?=drawGallery();?></td>
				</tr>
				<tr>
					<td><input type="submit" class="edt_button" name="submit" value="Submit" /></td>
				</tr>
			</table>
			<script>if(parent && parent.PopupManager) parent.PopupManager.prepare('<?=$module?>',0,0,'Photo Gallery Editor');</script>
		</form>
	</body>
</html>