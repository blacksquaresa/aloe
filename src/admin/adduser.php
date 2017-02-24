<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';

/***********************************************************************
adduser.php
Creates a new user
************************************************************************/

require_once('../lib/Global.lib.php');
$usermanager->authenticateUser(RIGHT_ADMUSER);
if(empty($_REQUEST['source'])) $_REQUEST['source'] = 'allusers.php';
$message = '';

// Create a new user, and transfer to users list if successful
if(!empty($_REQUEST['submit'])){
	if(empty($_REQUEST['contactname'])) $message .= " - Please provide a name.<br>";	
	if(empty($_REQUEST['email'])) $message .= " - Please provide an e-mail address.<br>";
	elseif(!validateEmail($_REQUEST['email'])) $message .= " - Please provide a valid e-mail address.<br>";
	if(empty($_REQUEST['username'])) $message .= " - Please provide a username.<br>";
	if(empty($_REQUEST['password'])) $message .= " - Please provide a password for this account.<br>";
	if(!empty($message)){
		$message = "There were problems with the form:<br>" . $message;
	}else{
		cleanUserInput(array_keys($_REQUEST));
		$id = $usermanager->createUser($_REQUEST['contactname'],$_REQUEST['username'],$_REQUEST['email'],$_REQUEST['phone'],$_REQUEST['password'],'active',$_REQUEST['rights'],$error);
		if($id){
			header('Location: allusers.php');
			exit;
		}else{
			$message = 'There was an error creating this administrator.<br />' . $error;
		}	
	}
}

function drawAdminRights(){
	global $usr, $usermanager;
	$admcols = 2;
	if($usermanager->checkUserRights(RIGHT_ADMADMIN)){
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
				$ret .= '<input type="checkbox" name="rights[]" value="' . $right['id'] . '" id="rights_' . $right['id'] . '"' . $checked . ' align="absmiddle" />&nbsp;';
				$ret .= '<label for="rights_' . $right['id'] . '">' . $right['description'] . '</label></td>';	
				$ind++;
			}
		}
		$ret .= '</tr></table>';
	}
	return $ret;
}


$pagetitle = 'User';
$cmslinkpageid = 'User';
include('templatetop.php');
?>	
	<script language=javascript>
		<!--
		function validate(sForm) {
			ErrorMsg = "";
						
			non_blank(sForm, "contactname", "Please provide a name.");
			non_blank(sForm, "username", "Please provide a username.");
			non_blank(sForm, "password", "Please provide a password.");	
			valid_email(sForm, "email", "Please provide a valid email address.");	
			valid_phone(sForm, "phone", "Please provide a valid phone number.");	

			if (ErrorMsg != "") {
				ErrorMsg = "The form could not be submitted for the following reasons:" + ErrorMsg;
				window.alert(ErrorMsg);
				return false;
			}
		}
		//-->
	</script>
	<script language="javascript" src="../js/validation.js"></script>
	
<? if($message!=''){?>	<div class="error"><?= $message ?></div><?}?>

<div>
	<div class="edt_heading_div">
		<div style="float:right;"><a href="<?= $_REQUEST['source'] ?>" class="backbutton" >back</a></div>
		<div class="edt_heading">Create a new User</div>
	</div>

	<form method="POST" name="userdetails" id="userdetails" action="adduser.php">		
		<input type="hidden" name="do" value="add">				
		<table cellpadding="0" cellspacing="0" border="0" style="margin: 16px 0px 0px 16px;">
			<tr>
				<td class="label_right">Name: </td>
				<td class="field"><input type="text" name="contactname" class="semiwidth" value="<?= clean($_REQUEST['contactname']) ?>" tabindex="1"></td>
				<td style="padding-left: 32px;" class="label_right">Username: </td>
				<td class="field"><input type="text" name="username" class="mediumwidth" value="<?= clean($_REQUEST['username']) ?>" tabindex="4"></td>		
				<?if($GLOBALS['settings']->useadminrights){?>
				<td valign="top" style="padding-left: 40px;" rowspan="3"><?=drawAdminRights();?></td>
				<?}?>				
			</tr>
			<tr>
				<td class="label_right">Email: </td>
				<td class="field"><input type="text" name="email" class="semiwidth" value="<?= clean($_REQUEST['email']) ?>" tabindex="2"></td>
				<td  class="label_right">Password: </td>
				<td class="field"><input type="text" name="password" class="mediumwidth" value="<?= clean($_REQUEST['password']) ?>" tabindex="5"></td>
			</tr>
			<tr>
				<td  class="label_right">Phone: </td>
				<td class="field"><input type="text" name="phone" class="semiwidth" value="<?= clean($_REQUEST['phone']) ?>" tabindex="3"></td>
				<td valign="bottom" colspan="2" align="right"><input type="submit" class="greenbutton updatebutton" value="Create User" name="submit" onClick="return(validate(this.form.name))" /></td>
			</tr>
		</table>				
	</form>
</div>
<? include('templatebottom.php'); ?>