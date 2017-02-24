<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('../../lib/Global.lib.php');

$module = 'CMSlideShow';
$pageid = $_REQUEST['pageid'];
$blockid = $_REQUEST['blockid'] | 0;
$layout = $_REQUEST['layout'];
$col = $_REQUEST['col'];
$pos = $_REQUEST['pos'] | 0;

if(!empty($_REQUEST['submit'])){
	cleanUserInput(array_keys($_REQUEST));
	$properties = array();
	$properties['indicator'] = $_REQUEST['indicator'];
	$properties['duration'] = $_REQUEST['duration'];
	$properties['transition'] = $_REQUEST['transition'];
	$properties['speed'] = $_REQUEST['speed'];
	$properties['aspect'] = $_REQUEST['aspect'];
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
	$indicator = $block->indicator;
	$duration = $block->duration;
	$transition = $block->transition;
	$speed = $block->speed;
	$aspect = $block->aspect;
}else{
	$indicator = 'none';
	$duration = 6;
	$transition = 'fade';
	$speed = 'medium';
	$aspect = 'short';
}

function drawGallery(){
	global $block;
	$thumbwidth = 80;
	$thumbheight = round(($block->height / $block->width) * $thumbwidth);
	$windowwidth = max((5*($thumbwidth+26))+20,550);
	$windowheight = 2*($thumbheight+32);
	$gallery = new GalleryView($block->id,'/content/CMSlideShow/galleries/'.$block->id,$windowwidth,$windowheight,$thumbwidth,$thumbheight,2,4,4);
	$gallery->prepareEdit(true,$block->width,$block->height);
	$gallery->resizestyle = IMAGERESIZETYPE_CROP;
	$gallery->addMetaDataField('url','Click URL','link');
	$gallery->addMetaDataField('title','Title','string');
	$gallery->storage = 'serialised';
	$gallery->prefix = 'gvsl';
	
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
		<?=$agent->init();?>
	</head>
	<body>
		<form method="post">
			<input type="hidden" name="layout" value="<?=$layout?>">
			<input type="hidden" name="col" value="<?=$col?>">
			<input type="hidden" name="pos" value="<?=$pos?>">
			<input type="hidden" name="blockid" value="<?=$blockid?>">
			<input type="hidden" name="pageid" value="<?=$pageid?>">
			
			<?if(empty($block)){?>
			<table class="edt_table" style="width: 550px;">
				<tr>
					<td class="label_right">Indicator:</td>
					<td class="field">
						<select name="indicator" id="indicator" style="width: 180px;">
							<option value="none"<?=$indicator=='none'?' selected':''?>>No indicator</option>
							<option value="dot"<?=$indicator=='dot'?' selected':''?>>Discreet dots</option>
							<option value="thumb"<?=$indicator=='thumb'?' selected':''?>>Thumbnails</option>
						</select>
					</td>
					<td class="label_left" nowrap>Show each slide for <input type="text" name="duration" id="duration" style="width: 30px;" value="<?=$duration?>" /> seconds</td>
				</tr>
				<tr>
					<td class="label_right">Transition:</td>
					<td class="field">
						<select name="transition" id="transition" style="width: 180px;">
							<option value="fade"<?=$transition=='fade'?' selected':''?>>Fade</option>
							<option value="slidetop"<?=$transition=='slidetop'?' selected':''?>>Slide in from the top</option>
							<option value="slideright"<?=$transition=='slideright'?' selected':''?>>Slide in from the right</option>
							<option value="slidebottom"<?=$transition=='slidebottom'?' selected':''?>>Slide in from the bottom</option>
							<option value="slideleft"<?=$transition=='slideleft'?' selected':''?>>Slide in from the left</option>
						</select>
					</td>
					<td class="label_left" nowrap>Transitions should be 
						<select name="speed" id="speed">
							<option value="fast"<?=$speed=='fast'?' selected':''?>>Fast</option>
							<option value="medium"<?=$speed=='medium'?' selected':''?>>Medium</option>
							<option value="slow"<?=$speed=='slow'?' selected':''?>>Slow</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="label_right" nowrap>Image Height:</td>
					<td class="field">
						<select name="aspect" id="aspect" style="width: 180px;">
							<option value="short"<?=$aspect=='short'?' selected':''?>>Short</option>
							<option value="medium"<?=$aspect=='medium'?' selected':''?>>Medium</option>
							<option value="long"<?=$aspect=='long'?' selected':''?>>Long</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="label_left" colspan="3">New images cannot be added to the slideshow until it has been created. Please create the slideshow, then edit it to add new images.</td>
				</tr>
				<tr>
					<td colspan="3"><input type="submit" class="edt_button" name="submit" value="Create" /></td>
				</tr>
			</table>
			<script>if(parent && parent.PopupManager) parent.PopupManager.prepare('<?=$module?>',0,0,'SlideShow Gallery Editor');</script>
			<?}else{?>
			<input type="hidden" name="aspect" value="<?=$aspect?>">
			<script src="<?= $GLOBALS['webroot'] ?>controls/galleryview/GalleryView.js"></script>
			<link rel="stylesheet" href="<?=$GLOBALS['webroot']?>controls/galleryview/galleryview.css" />
			<table class="edt_table" style="width: 550px;">
				<tr>
					<td class="label_right">Indicator:</td>
					<td class="field">
						<select name="indicator" id="indicator" style="width: 180px;">
							<option value="none"<?=$indicator=='none'?' selected':''?>>No indicator</option>
							<option value="dot"<?=$indicator=='dot'?' selected':''?>>Discreet dots</option>
							<option value="thumb"<?=$indicator=='thumb'?' selected':''?>>Thumbnails</option>
						</select>
					</td>
					<td class="label_left">Show each slide for <input type="text" name="duration" id="duration" style="width: 30px;" value="<?=$duration?>" /> seconds</td>
				</tr>
				<tr>
					<td class="label_right">Transition:</td>
					<td class="field">
						<select name="transition" id="transition" style="width: 180px;">
							<option value="fade"<?=$transition=='fade'?' selected':''?>>Fade</option>
							<option value="slidetop"<?=$transition=='slidetop'?' selected':''?>>Slide in from the top</option>
							<option value="slideright"<?=$transition=='slideright'?' selected':''?>>Slide in from the right</option>
							<option value="slidebottom"<?=$transition=='slidebottom'?' selected':''?>>Slide in from the bottom</option>
							<option value="slideleft"<?=$transition=='slideleft'?' selected':''?>>Slide in from the left</option>
						</select>
					</td>
					<td class="label_left">Transitions should be 
						<select name="speed" id="speed">
							<option value="fast"<?=$speed=='fast'?' selected':''?>>Fast</option>
							<option value="medium"<?=$speed=='medium'?' selected':''?>>Medium</option>
							<option value="slow"<?=$speed=='slow'?' selected':''?>>Slow</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="label_left" colspan="3"><input type="button" name="addimage" id="addimage" value="Add an Image" onclick="gvsl.CreateImage();" class="greenbutton addbutton" style="width: 200px; margin-right: 40px;" />Images should be <?=$block->width?>px wide by <?=$block->height?>px tall.</td>
				</tr>
				<tr>
					<td style="padding: 20px 0px 0px 0px;" colspan="3"><?=drawGallery();?></td>
				</tr>
				<tr>
					<td colspan="3"><input type="submit" class="edt_button" name="submit" value="Submit" /></td>
				</tr>
			</table>
			<script>if(parent && parent.PopupManager) parent.PopupManager.prepare('<?=$module?>',0,0,'SlideShow Gallery Editor');</script>
			<?}?>
			
		</form>
	</body>
</html>