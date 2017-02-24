<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';

/***********************************************************************
allusers.php
Searchable list of all users in the system. 
************************************************************************/

require_once('../lib/Global.lib.php');
$usermanager->authenticateUser(RIGHT_ADMUSER);
require_once('../lib/Text.lib.php');
require_once('../lib/Agent.lib.php');
$message = '';

// Sets the current state of the search for this page. The state persists across the session.
PageState::ClearPageState('userstate');
$state = PageState::SetPageState('userstate');
function BuildPageState(){
	global $usermanager;
	$state = new PageState('userstate',array($usermanager,'getUsers'),array($usermanager,'countUsers'));
	$state->AddStateItem('string','name');
	$state->AddStateItem('string','email');
	$state->AddStateItem('string','username');
	$state->AddStateItem('array','status');
	$state->AddStateItem('string','sort');
	$state->AddStateItem('integer','start');
	$state->AddStateItem('integer','lim');
	$state->SetStateItems(array('lim'=>20));
	return $state;
}

if(!empty($_REQUEST['do'])){
	switch($_REQUEST['do']){
		// Activate a locked or suspended user
		case 'act':
			$res = $usermanager->updateUserStatus($_REQUEST['id'],'active',$error);
			if(!$res){
				$message = "The was a problem activating this user<br />" . $error;
			}else{					
				$success = "User status updated successfully";
			}
			break;
		// Delete a user
		case 'del':		
			$res = $usermanager->deleteUser($_REQUEST['id'],$error);
			if($res === false){
				$message = "The was a problem deleting this user<br />" . $error;
			}else{					
				$success = "User deleted successfully";				
			}		
			break;
		// Suspend an active user
		case 'sus':			
			$res = $usermanager->updateUserStatus($_REQUEST['id'],'suspended',$error);
			if($res === false){
				$message = "The was a problem suspending this user<br />" . $error;
			}else{					
				$success = "User status updated successfully";	
			}
			break;
	}
}


/*
==================================
   Create List View
==================================
*/
function drawListView(){
	global $state;
	if(empty($state)){
		$state = PageState::GetPageState('userstate');
	}
	
	$listview = new ListView();
	$listview->InitFromState($state);
	$listview->usepaging = true;
	$listview->selectioncolumn = 'name';
	$listview->selectionurl = 'user.php?id=';
	
	$listview->SetAjax(true,'drawListView','listview_items');
	
	$listview->AddColumn('Name','name',null,'lv_string',null,null,'lvbold');
	$listview->AddColumn('Email','email',null,'lv_email');
	$listview->AddColumn('Username','username');
	$listview->AddColumn('Created','datecreated',130,'lv_date');
	$listview->AddColumn('Last Login','lastlogin',100,'lv_date');
	$listview->AddColumn('Status','status',100,'status_callback');
	$listview->AddColumn('','id',40,'toolbar_callback',null,null,null,null,'none');
	
	$listview->listiscomplete = false;
	
	return $listview->DrawListView();
}

function status_callback($item){
	$ret = '';
	$status = $item['status'];
	switch($item['status']){
		case 'active':
			$ret .= '<span style="color: #686868;margin-top: 1px;"><img src="../images/admin/common/active.png" alt="Active" align="absmiddle" /> Active</span>';
			break;
		case 'suspended':
			$ret .= '<span style="color: #686868;margin-top: 1px;"><img src="../images/admin/common/suspend.png" alt="Suspended" align="absmiddle" /> Suspended</span>';
			break;
	}
	return $ret;
}

function toolbar_callback($item){
	$ret = '';
	if($item['id'] == ADM_USERID){
		$ret .= '<img src="../images/admin/common/suspend_ex.png" border="0" alt="This user cannot be suspended!" />';
		$ret .= '<img src="../images/admin/common/delete_ex.png" border="0" alt="This user cannot be deleted!" />';
	}else{
		switch($item['status']){
			case 'active':
				$ret .= '<a href="allusers.php?do=sus&id=' . $item['id'] . '" onclick="return checkSuspend()"><img src="../images/admin/common/suspend.png" border="0" alt="Suspend this user" align="absmiddle" /></a>';
				break;
			case 'suspended':
				$ret .= '<a href="allusers.php?do=act&id=' . $item['id'] . '"><img src="../images/admin/common/active.png" border="0" alt="Activate this user" align="absmiddle" /></a>';
				break;
		}
		$ret .= '<a href="allusers.php?&do=del&id=' . $item['id'] . '" onclick="return checkDelete()";><img src="../images/admin/common/delete.png" border="0" alt="Delete this user" align="absmiddle" /></a>';
	}
	return $ret;
}

$cmslinkpageid = 'User';
$pagetitle = 'User';
include('templatetop.php');

?>
<script language="javascript" src="../js/SearchFilter.js"></script>
<script language="javascript">
	<!--
	function checkDelete(){
		return confirm('Are you sure you want to delete this user?');
	}
	function checkSuspend(){
		return confirm('Are you sure you want to suspend this user?');
	}
	function reloadlistview(){
		_listview_drawloading('listview_items');
		var status = new Array();
		if(document.getElementById('status_0').checked) status[status.length] = 'active';
		if(document.getElementById('status_1').checked) status[status.length] = 'suspended';
		agent.call('../../ajax/PageState.ajax.php','SetStateItems','','userstate',
			{
				'name':document.details.elements['name'].value,
				'email':document.details.email.value,
				'username':document.details.username.value,
				'status':status
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
			<a href="adduser.php" class="greenbutton addbutton" id="newuserbutton">Add a new User</a>
			<hr class="dotted" />
			<form name="details" method="post">
				<table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 20px;">
					<tr>
						<td class="label_head"><img src="../images/admin/common/search.png" align="absmiddle" alt="Search" /> Search for Users</td>
					</tr>
					<tr>
						<td class="label_left">Name:</td>
					</tr>
					<tr>
						<td class="field"><input type="text" name="name" class="page_textbox" value="<?= $state->values['name'] ?>"></td>
					</tr>
					<tr>
						<td class="label_left">Email: </td>
					</tr>
					<tr>
						<td class="field"><input type="text" name="email" class="page_textbox" value="<?= $state->values['email'] ?>"></td>
					</tr>
					<tr>
						<td class="label_left">Username: </td>
					</tr>
					<tr>
						<td class="field"><input type="text" name="username" class="page_textbox" value="<?= $state->values['username'] ?>"></td>
					</tr>
					<tr>
						<td class="label_left">
							<input type="checkbox" name="status[]" value="active" id="status_0"<?= (in_array('active',$state->values['status'])?' checked':'') ?>> 
							<label for="status_0">Active</label>
						</td>	
					</tr>
					<tr>
						<td class="label_left">	
							<input type="checkbox" name="status[]" value="suspended" id="status_1"<?= (in_array('suspended',$state->values['status'])?' checked':'') ?>> 
							<label for="status_1">Suspended</label>
						</td>
					</tr>
					<tr>
						<td class="field"><input name="search" type="button" class="greybutton" value="Search" onclick="reloadlistview();" /></td>
					</tr>
				</table>
			</form>
		</td>
		<td valign="top">
			<div class="page_listview"><?= drawListView(); ?></div>
			<div class="downloaddiv"><a class="smallbutton" href="downloadcsv.php?state=userstate&name=Users" target="_blank">download csv</a></div>
		</td>
	</tr>
</table>

<? include('templatebottom.php'); ?>