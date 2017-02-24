<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';

require_once('../lib/Global.lib.php');
$usermanager->authenticateUser(RIGHT_ADMUSER);
require_once('../lib/Text.lib.php');
$userid = (empty($_REQUEST['id'])?-1:$_REQUEST['id']);
$usr = $usermanager->getUserById($userid);
if(empty($usr)){
	header('Location: allusers.php');
	exit;
}
if(empty($_REQUEST['source'])) $_REQUEST['source'] = 'allusers.php';

$message = '';
$code = '?' . createRandomCode(8,12);

if(!empty($_REQUEST['do'])){
	switch($_REQUEST['do']){	
		case 'upd': 
			if(empty($_REQUEST['contactname'])) $message .= " - Please provide a name.<br>";
			if(!empty($message)){
				$message = "There were problems with the form:<br>" . $message;
			}else{
				cleanUserInput(array_keys($_REQUEST));
				if($usermanager->updateUser($usr['id'],$_REQUEST['contactname'],$_REQUEST['email'],$_REQUEST['phone'],$_REQUEST['status'],$_REQUEST['rights'],$error)){
					header('Location: allusers.php');
					exit;
				}else{
					$message = 'There was an error updating this user.';
					$usr['name'] = $_REQUEST['contactname'];
					$usr['email'] = $_REQUEST['email'];
					$usr['phone'] = $_REQUEST['phone'];
				}
			}
			break;	
		case 'act':
			$res = $usermanager->updateUserStatus($usr['id'],'active',$error);
			if($res == false){
				$message = "There were problems with the form:<br>" . $error;
			}else{
				header('Location: user.php?id='.$userid);
				exit;
			}
			break;
		case 'del':
			$res  = $usermanager->deleteUser($usr['id'],$error);
			if($res == false){
				$message = "There were problems with the form:<br>" . $message;
			}else{
				header('Location: allusers.php');
				exit;
			}
			break;
		case 'sus':
			$res = $usermanager->updateUserStatus($usr['id'],'suspended',$error);
			if($res == false){
				$message = "There were problems with the form:<br>" . $message;
			}else{
				header('Location: user.php?id='.$userid);
				exit;
			}
			break;
	}	
}

function drawStatus(){	
	global $usr;
	$ret = '';
	if($usr['id'] == ADM_USERID){
		$ret .= 'Active';
	}else{
		switch($usr['status']){
			case 'active':
				$ret .= 'Active &nbsp; <a href="user.php?id=' . $usr['id'] . '&do=sus">Suspend</a>';
				break;
			case 'suspended':
				$ret .= 'Suspended &nbsp; <a href="user.php?id=' . $usr['id'] . '&do=act">Activate</a>';
				break;
		} 
	}
	return $ret;	
}

function drawAdminRights(){
	global $usr, $usermanager;
	$admcols = 2;
	$hasadminrights = $usermanager->checkUserRights(RIGHT_ADMADMIN);
	$rights = $usermanager->getUserRights();
	$ind = 0;
	$ret = '<table width="100%" style="border-collapse: collapse;"><tr>';
	foreach($rights as $right){
		if($right['id'] > 0){
			if($ind && $ind%$admcols==0){
				$ret .= '</tr><tr>';	
			}
			$checked = (($usr['rights'] & intval($right['id'])) == intval($right['id']))?' checked':'';
			$ret .= '<td nowrap style="padding-right: 20px;">';
			if($hasadminrights && $usr['id'] != ADM_USERID){
				$ret .= '<input type="checkbox" name="rights[]" value="' . $right['id'] . '" id="rights_' . $right['id'] . '"' . $checked . ' align="absmiddle" />&nbsp;';
			}else{
				$ret .= '<img src="../images/admin/common/' . (empty($checked)?'delete.png':'active.png') . '" align="absmiddle">&nbsp;';	
			}
			$ret .= '<label for="rights_' . $right['id'] . '">' . $right['description'] . '</label></td>';	
			$ind++;
		}
	}
	$ret .= '</tr></table>';
	if(!$hasadminrights){
		$ret .= '<input type="hidden" name="rights" value="'.$usr['rights'].'" />';
	}
	return $ret;
}

$pagetitle = 'User';
$cmslinkpageid = 'User';
include('templatetop.php');

?>

<script language="javascript"> 
	<!--
	function checkDelete(id){
		if(confirm('Are you sure you want to delete this user?')){
			location.href = 'allusers.php?id=' + id + '&do=del';
			return true;
		}
		return false;
	}
	// -->
</script>

<? if($message!=''){?><div class="error"><?= $message ?></div><?}?>

<div>
	<div class="edt_heading_div">
		<div style="float:right;"><a href="<?= $_REQUEST['source'] ?>" class="backbutton" >back</a></div>
		<div class="edt_heading">Update User Details</div>
	</div>
	
	<form method="POST" name="userdetails" id="userdetails" action="user.php" enctype="multipart/form-data">
		<input type="hidden" name="id" value="<?= $userid ?>">
		<input type="hidden" name="do" value="upd">				
		<table cellpadding="0" cellspacing="0">
			<tr>
				<td valign="top" class="label_list">
					<div class="label_left"><span class="label_label">Username: </span><?= $usr['username'] ?></div>
					<div class="label_left"><span class="label_label">Date Created: </span><?= (empty($usr['datecreated'])?'not recorded':date('d M Y',$usr['datecreated'])) ?></div>
					<div class="label_left"><span class="label_label">Last Login: </span><?= (empty($usr['lastlogin'])?'never':date('d M Y H:i:s',$usr['lastlogin'])) ?></div>
					<div class="label_left"><span class="label_label">Status: </span><?= drawStatus(); ?></div>
				</td>
				<td valign="top" style="padding-left: 40px;">
					<div class="label_left">Name: </div>
					<div class="field"><input type="text" name="contactname" class="semiwidth" value="<?= clean($usr['name']) ?>" /></div>
					<div class="label_left">Email: </div>
					<div class="field"><input type="text" name="email" class="semiwidth" value="<?= clean($usr['email']) ?>" /></div>
					<div class="label_left">Phone: </div>
					<div class="field"><input type="text" name="phone" class="semiwidth" value="<?= clean($usr['phone']) ?>" /></div>
					<div class="edt_submit" align="right"><input type="submit" value="Save Changes" class="greenbutton updatebutton" /></div>
				</td>
				<?if($GLOBALS['settings']->useadminrights){?>
				<td valign="top" style="padding-left: 40px;"><?=drawAdminRights();?></td>
				<?}?>
			</tr>
		</table>						
	</form>	
</div>
<? include('templatebottom.php'); ?>