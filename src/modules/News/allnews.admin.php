<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';

/***********************************************************************
allnews.php
Searchable list of all users in the system. 
************************************************************************/

require_once('../../lib/Global.lib.php');
$usermanager->authenticateUser(RIGHT_EDITNEWS);
require_once('News.lib.php');
require_once('../../lib/Text.lib.php');
require_once('../../lib/Agent.lib.php');
$message = '';

// Sets the current state of the search for this page. The state persists across the session.
// PageState::ClearPageState('newsstate');
$state = PageState::SetPageState('newsstate');
function BuildPageState(){
	$state = new PageState('newsstate','getNews','countNews',dirname(__FILE__).'/News.lib.php');
	$state->AddStateItem('string','title');
	$state->AddStateItem('integer','category');
	$state->AddStateItem('integer','project');
	$state->AddStateItem('string','section');
	$state->AddStateItem('date','sdate');
	$state->AddStateItem('date','edate');
	$state->AddStateItem('string','sort');
	$state->AddStateItem('integer','start');
	$state->AddStateItem('integer','lim');
	$state->SetStateItems(array('lim'=>20));
	return $state;
}

if(!empty($_REQUEST['do'])){
	switch($_REQUEST['do']){
		// Delete a news item
		case 'del':		
				$res = deleteNewsItem($_REQUEST['id'],$error);
				if($res === false){
					$message = "The News article deleting has a problem".$error;
				}else{				
					$success = "The News article deleted successful";
					
				}			
			break;
	}	
}

/*=========  Create List View  =============================*/
function drawListView(){
	global $state;
	if(empty($state)){
		$state = PageState::GetPageState('newsstate');
	}
	
	$listview = new ListView();
	$listview->InitFromState($state);
	$listview->usepaging = true;
	$listview->itemsperpage = 20;
	$listview->selectioncolumn = 'title';
	$listview->selectionurl = 'news.admin.php?id=';
	
	$listview->SetAjax(true,'drawListView','listview_items');
	
	$listview->AddColumn('Title','title',null,'lv_string',null,null,'lvbold');
	$listview->AddColumn('Categories','id',null,'category_callback',null,null,null,null,'none');
	$listview->AddColumn('Created','date',null,'lv_date');
	$listview->AddColumn('','id',20,'delete_callback',null,null,null,null,'none');
	
	$listview->listiscomplete = false;
	
	return $listview->DrawListView();
}

function delete_callback($item){
	return '<a href="allnews.admin.php?do=del&id=' . $item['id'] . '" onclick="return checkDelete(\'' . $item['email'] . '\')";><img src="../../images/admin/common/delete.png" border="0" alt="Delete this news article"></a>';
}

function category_callback($item){
	$cats = getNewsCategoriesForItem($item['id']);
	foreach($cats as $cat){
		if(!empty($res)) $res .= ', ';
		$res .= $cat['name'];	
	}
	return $res;
}

/*==============   Create Calendars ==========================*/

$sdate = new Calendar('sdate',$state->values['sdate']);
$sdate->positionOffset->setOffset(-2,2,-1,0);
$sdate->positionOffsetNS->setOffset(0,-2,-6,-8);

$edate = new Calendar('edate',$state->values['edate']);
$edate->positionOffset->setOffset(-2,2,-1,0);
$edate->positionOffsetNS->setOffset(0,-2,-6,-8);

$pagetitle = 'News Management';
$cmslinkpageid = 'News';
include('../../admin/templatetop.php');
$agent->init();
?>
<script language="javascript">
	<!--
	function checkDelete(){
		return confirm('Are you sure you want to delete this news article?');
	}
	function reloadlistview(){
		_listview_drawloading('listview_items');
		var status = new Array();
		agent.call('../../ajax/PageState.ajax.php','SetStateItems','','newsstate',
			{
				'title':document.details.elements['name'].value,
				'category':document.details.category[document.details.category.selectedIndex].value,
				'sdate':document.details.sdate.value,
				'edate':document.details.edate.value
			}
		);
		agent.call('','drawListView','listview_items');
	}
	// -->
</script>

<? if($message!=''){?><div class="error"><?= $message ?></div><?}?>		
<? if($success!=''){?><div class="success"><?= $success ?></div><?}?>


<table cellpadding="0" cellspacing="0" width="100%">
	<tr>
		<td class="page_leftcolumn" valign="top">
			<a href="addnews.admin.php" class="greenbutton addbutton">Add a new Article</a>
			<hr class="dotted" />
			<form method="post" name="details" id="details" action="allusers.php">
				<table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 20px;">
					<tr>
						<td class="label_head"><img src="../../images/admin/common/search.png" align="absmiddle" alt="Search" /> Search for News Articles</td>
					</tr>
					<tr>
						<td class="label_left">Title: </td>
					</tr>
					<tr>
						<td class="field"><input type="text" name="name" style="width: 231px;" value="<?= $state->values['title'] ?>"></td>
					</tr>
					<tr>
						<td class="label_left">Category: </td>
					</tr>
					<tr>
						<td class="field"><?=drawNewsCategories('category',$state->values['category'])?></td>
					</tr>
					<tr>
						<td class="label_left">Date:</td>		
					</tr>
					<tr>					
						<td class="label_soft">Articles created after:</td>
					</tr>
					<tr>
						<td class="field"><?=$sdate->drawCalendar();?></td>	
					</tr>
					<tr>							
						<td class="label_soft">And before:</td>
					</tr>
					<tr>
						<td class="field"><?=$edate->drawCalendar();?></td>
					</tr>
					<tr>
						<td class="field"><input type="button" class="greybutton" name="search" value="Search" onclick="reloadlistview();" /></div></td>
					</tr>
				</table>
			</form>
		</td>
		<td valign="top">
			<div class="page_listview"><?=drawListView();?></div>
			<div class="downloaddiv"><a class="smallbutton" href="downloadcsv.php?state=newsstate&name=News%20Archive" target="_blank">download csv</a></div>	
		</td>
	</tr>
</table>					

<? include('../../admin/templatebottom.php'); ?>