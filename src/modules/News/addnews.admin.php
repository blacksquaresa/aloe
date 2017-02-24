<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';

/***********************************************************************
addnews.php
Creates a new news article
************************************************************************/

require_once('../../lib/Global.lib.php');
$usermanager->authenticateUser(RIGHT_EDITNEWS);
require_once('News.lib.php');
if(empty($_REQUEST['source'])) $_REQUEST['source'] = 'allnews.admin.php';
$message = '';

// Create a new news article, and transfer to news list if successful
if(!empty($_REQUEST['submit'])){
	if(empty($_REQUEST['title'])) $message .= " - Please provide a title.<br />";
	if(empty($_REQUEST['content'])) $message .= " - Please provide content for this news article.<br />";
	if(!empty($message)){
		$message = "There were problems with the form:<br>" . $message;
	}else{
		cleanUserInput(array('title','creationdate','pubinfo','description','keywords','category'));
		$id = createNewsItem($_REQUEST['title'],$_REQUEST['creationdate'],$_REQUEST['pubinfo'],$_REQUEST['description'],$_REQUEST['keywords'],$_REQUEST['content'],$_REQUEST['category'],$_FILES['image'],$error);
		if($id){
			header('Location: allnews.admin.php');
			exit;
		}else{
			$message = 'There was an error creating this news item.<br />' . $error;
		}	
	}
}

/*
==================================
   Create Calendars
==================================
*/

$creationdate = new Calendar('creationdate',time());
$creationdate->positionOffset->setOffset(-2,0,-1,0);
$creationdate->positionOffsetNS->setOffset(0,0,-6,-8);

$categories = new MultiList('category',getNewsCategories(),$_REQUEST['category'],'category','categories',228,100);

$pagetitle = 'Create a new News Article';
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

<div id="news">
	<div class="edt_heading_div">
		<div style="float:right;"><a href="<?= $_REQUEST['source'] ?>" class="backbutton" >back</a></div>
		<div class="edt_heading">Create a new News Article</div>
	</div>

	<form method="POST" name="details" id="details" action="addnews.admin.php" enctype="multipart/form-data">	
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>
				<td style="width: 300px;" rowspan="2" valign="top">
					<div class="edt_heading2" style="margin-bottom: 20px;">Article Details</div>
					<div class="label_left">Date:</div>
					<div class="field"><?= $creationdate->drawCalendar() ?></div>
					<div class="label_left">Title:</div>
					<div class="field"><input type="text" name="title" style="width: 288px;" value="<?= clean($_REQUEST['title']) ?>" tabindex="1" /></div>
					<div class="label_left">Publication Info:</div>
					<div class="field"><input type="text" name="pubinfo" style="width: 288px;" value="<?= clean($_REQUEST['pubinfo']) ?>" tabindex="2" /></div>
					<div class="label_left">Short Copy:</div>
					<div class="field"><textarea name="description" id="description" style="width: 288px;height: 96px;" maxlength="255" tabindex="3"><?= clean($_REQUEST['description']) ?></textarea><div class="note">Maximum 255 characters</div></div>
					<div class="label_left">Thumbnail:</div>
					<div class="field"><input type="file" name="image" id="image" style="width: 288px;" tabindex="5"><div class="note">This image should be 100px by 100px, but will be resized if it is larger. If no image is supplied here, the first image found in the content will be used.</div></div>
				</td>
				<td style="width: 672px;padding: 0px 20px;" rowspan="2" valign="top">
					<div class="edt_heading2" style="margin-bottom: 20px;">Article</div>
					<textarea name="content" class="mceEditor" style="width: 672px; height: 420px; padding: 0px; border:0px;" tabindex="6"><?= $_REQUEST['content'] ?></textarea>
				</td>
				<td valign="top">
					<div class="edt_heading2" style="margin-bottom: 20px;">Categories</div>
					<div class="field"><?= $categories->drawMultiList(false); ?></div>
					<div class="edt_heading2" style="margin: 20px 0px;">SEO</div>
					<div class="field note">Title and Description metadata is taken from the article details</div>
					<div class="label_left">Keywords:</div>
					<div class="field"><input type="text" name="keywords" style="width: 216px;" value="<?= clean($_REQUEST['keywords']) ?>" tabindex="8" /></div>
				</td>
			</tr>
			<tr>
				<td valign="bottom">
					<input type="submit" value="Create Article" style="margin-top: 10px;" class="greenbutton addbutton" name="submit" onClick="return(validate(this.form.name))">
				</td>
			</tr>
		</table>
	</form>
</div>

<? include('../../admin/templatebottom.php'); ?>