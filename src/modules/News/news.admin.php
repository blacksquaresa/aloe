<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';

/***********************************************************************
news.php
Update a news article
************************************************************************/

require_once('../../lib/Global.lib.php');
$usermanager->authenticateUser(RIGHT_EDITNEWS);
require_once('News.lib.php');
$newsid = (empty($_REQUEST['id'])?-1:$_REQUEST['id']);
$itm = getNewsItem($newsid);
if(empty($itm)){
	header('Location: allnews.admin.php');
	exit;
}
if(empty($_REQUEST['source'])) $_REQUEST['source'] = 'allnews.admin.php';
$message = '';


// Update the news article, and transfer to news list if successful
if(!empty($_REQUEST['submit'])){
	if(empty($_REQUEST['title'])) $message .= " - Please provide a title.<br />";
	if(empty($_REQUEST['content'])) $message .= " - Please provide content for this news article.<br />";
	if(!empty($message)){
		$message = "There were problems with the form:<br>" . $message;
	}else{
		cleanUserInput(array('title','creationdate','pubinfo','description','keywords','category'));
		$id = updateNewsItem($newsid, $_REQUEST['title'], $_REQUEST['creationdate'], $_REQUEST['pubinfo'], $_REQUEST['description'], $_REQUEST['keywords'],$_REQUEST['content'],$_REQUEST['category'],$_FILES['image'],$error);
		if($id){
			header('Location: allnews.admin.php');
			exit;
		}else{
			$message = 'There was an error updating this news item.<br />' . $error;
		}	
	}
}

if(!empty($_REQUEST['action'])){
	switch($_REQUEST['action']){
		case 'del':
			$path = getNewsThumbnail($newsid);
			if(unlink($path)){
				$notice = 'Thank you. The thumbnail image has been deleted';
			}else{				
				$message = 'There was an error deleting the thumbnail image for this item.';
			}
			break;	
	}	
}

function drawThumbnail(){
	global $newsid;
	$path = getNewsThumbnail($newsid);
	$code = '?'.createRandomCode(8);
	if(file_exists($path)){
		$webpath = getNewsThumbnail($newsid,true);
		$res .= '<img src="'.$webpath.$code.'" />';
		$res .= '<a href="news.admin.php?id='.$newsid.'&action=del" class="note">delete thumb</a>';	
	}
	return $res;
}

$title = empty($_REQUEST['title'])?$itm['title']:clean($_REQUEST['title']);
$pubinfo = empty($_REQUEST['pubinfo'])?$itm['pubinfo']:clean($_REQUEST['pubinfo']);
$keywords = empty($_REQUEST['keywords'])?$itm['keywords']:clean($_REQUEST['keywords']);
$description = empty($_REQUEST['description'])?$itm['description']:clean($_REQUEST['description']);
$content = empty($_REQUEST['content'])?$itm['content']:$_REQUEST['content'];
$creationdate = empty($_REQUEST['creationdate'])?$itm['date']:$_REQUEST['creationdate'];
$categories = empty($_REQUEST['category'])?$itm['categories']:$_REQUEST['category'];
$thumbsize = $GLOBALS['skin']->getSetting('News','sizematrix',array('width'=>100,'height'=>100));

/*
==================================
   Create Calendars
==================================
*/

$creationdate = new Calendar('creationdate',$creationdate);
$creationdate->positionOffset->setOffset(-2,0,-1,0);
$creationdate->positionOffsetNS->setOffset(0,0,-6,-8);

$categories = new MultiList('category',getNewsCategories(),$categories,'category','categories',228,100);

$pagetitle = 'Update a News Article';
$cmslinkpageid = 'News';
include('../../admin/templatetop.php');
$tiny = new TinyMCE();
$tiny->Init('standard',630);
?>	
<script language=javascript>
	<!--
	function validate(sForm) {
		ErrorMsg = "";
					
		non_blank(sForm, "title", "Please provide a title.");
		if(tinyMCE.getInstanceById('content').getContent() == '') ErrorMsg += '\r\n  -  Please supply content for this article.';

		if (ErrorMsg != "") {
			ErrorMsg = "The form could not be submitted for the following reasons:" + ErrorMsg;
			window.alert(ErrorMsg);
			return false;
		}
	}
	//-->
</script>
<script language="javascript" src="../../js/validation.js"></script>

<? if($message!=''){?><div class="error"><?= $message ?></div><?}?>
<? if($notice!=''){?><div class="success"><?= $notice ?></div><?}?>

<div id="news">
	<div class="edt_heading_div">
		<div style="float:right;"><a href="<?= $_REQUEST['source'] ?>" class="backbutton" >back</a></div>
		<div class="edt_heading">Update News Article</div>
	</div>

	<form method="POST" name="details" id="details" action="news.admin.php" enctype="multipart/form-data">
		<input type="hidden" name="id" value="<?=$newsid?>" />	
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>
				<td style="width: 300px;" rowspan="2" valign="top">
					<div class="edt_heading2" style="margin-bottom: 20px;">Article Details</div>
					<div class="label_left">Date:</div>
					<div class="field"><?= $creationdate->drawCalendar() ?></div>
					<div class="label_left">Title:</div>
					<div class="field"><input type="text" name="title" style="width: 288px;" value="<?= $title ?>" tabindex="1" /></div>
					<div class="label_left">Publication Info:</div>
					<div class="field"><input type="text" name="pubinfo" style="width: 288px;" value="<?= $pubinfo ?>" tabindex="2" /></div>
					<div class="label_left">Short Copy:</div>
					<div class="field"><textarea name="description" id="description" style="width: 288px;height: 96px;" maxlength="255" tabindex="3"><?= $description ?></textarea><div class="note">Maximum 255 characters</div></div>
					<div class="label_left">Thumbnail:</div>
					<div class="field"><div style="margin-bottom: 10px;"><?=drawThumbnail();?></div><input type="file" name="image" id="image" style="width: 168px;" tabindex="5"><div class="note">This image should be <?=$thumbsize['width']?>px by <?=$thumbsize['height']?>px, but will be resized if it is larger. If no image is supplied here, the first image found in the content will be used.</div></div>
				</td>
				<td style="width: 672px;padding: 0px 20px;" rowspan="2" valign="top">
					<div class="edt_heading2" style="margin-bottom: 20px;">Article</div>
					<textarea name="content" class="mceEditor" style="width: 672px; height: 420px; padding: 0px; border:0px;" tabindex="6"><?= $content ?></textarea>
				</td>
				<td valign="top">
					<div class="edt_heading2" style="margin-bottom: 20px;">Categories</div>
					<div class="field"><?= $categories->drawMultiList(false); ?></div>
					<div class="edt_heading2" style="margin: 20px 0px;">SEO</div>
					<div class="field note">Title and Description metadata is taken from the article details</div>
					<div class="label_left">Keywords:</div>
					<div class="field"><input type="text" name="keywords" style="width: 216px;" value="<?= $keywords ?>" tabindex="8" /></div>
				</td>
			</tr>
			<tr>
				<td valign="bottom">
					<input type="submit" value="Update Article" style="margin-top: 10px;" class="greenbutton updatebutton" name="submit" onClick="return(validate(this.form.name))">
				</td>
			</tr>
		</table>
	</form>	
</div>

<? include('../../admin/templatebottom.php'); ?>