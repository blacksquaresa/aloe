<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';

require_once('../lib/Global.lib.php');
$usermanager->authenticateUser();
$pagetitle = 'Change Password';
$message = '';

if(!empty($_REQUEST['submit'])){
	if(empty($_REQUEST['oldpassword'])) $message .= " - Please provide your old password.<br>";
	if(empty($_REQUEST['newpassword'])) $message .= " - Please provide your new password.<br>";
	if($_REQUEST['newpassword'] != $_REQUEST['newpassword_confirm']) $message .= " - The password confirmation does not match the password.<br>";
	
	if(!empty($message)){
		$message = "There were problems with the form:<br>" . $message;
	}else{
		$res = $usermanager->resetUserPassword($_SESSION['user']['id'],$_REQUEST['oldpassword'],$_REQUEST['newpassword'],$error);
		if($res){				
			$usermanager->refreshSessionUser();
			header('Location: index.php?mes=updp');
			exit;
		}else{
			$message = 'There was an error updating your password. Please try again.';	
		}
	}
}

?>
<? include('templatetop.php'); ?>
	<script language=javascript>
		<!--
		function validate(sForm) {
			ErrorMsg = "";
						
			non_blank(sForm, "oldpassword", "Please provide your old password.");
			non_blank(sForm, "newpassword", "Please provide your new password.");	
			is_equal(sForm, "newpassword", "newpassword_confirm", "The password confirmation does not match the password.");		

			if (ErrorMsg != "") {
				ErrorMsg = "The form could not be submitted for the following reasons:" + ErrorMsg;
				window.alert(ErrorMsg);
				return false;
			}
		}
		//-->
	</script>
	<script language="javascript" src="../js/validation.js"></script>
<div class="edt_heading_div"><div class="edt_heading">Change your Password</div></div>
<? if(!empty($message)){ ?><div class="error"><?= $message ?></div><? } ?>

<form method="post" action="changepassword.php" name="frmregister">
<input type="hidden" name="ret" value="<?= $_REQUEST['ret'] ?>">

<div class="label_left">Old Password:</div>
<div class="field"><input class="edt_textbox" type="password" name="oldpassword" /></div>
<div class="label_left">New Password:</div>
<div class="field"><input class="edt_textbox" type="password" name="newpassword" /></div>
<div class="label_left">Confirm Password:</div>
<div class="field"><input class="edt_textbox" type="password" name="newpassword_confirm" /></div>
<div class="field"><input type="submit" value="Change Password" name="submit" class="greenbutton updatebutton" onclick="return(validate(this.form.name))" /></div>
</form>

<? include('templatebottom.php'); ?>