<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';

require_once('../lib/Global.lib.php');
require_once('../lib/Email.lib.php');
$pagetitle = 'Reset your Password - Thank You';
$message = '';

if($_SESSION['user']){
	$usermanager->logout();	
}

if(!empty($_REQUEST['code'])){
	$usr = $usermanager->getUserByCode($_REQUEST['code']);
	if(empty($usr)){
		$message = 'No user could be found for the supplied code.';
	}else{
		$newpassword = $usermanager->resetUserPasswordByCode($usr,$error);
		if(empty($newpassword)){
			$message = 'The system failed to reset your password. Please contact <a href="mailto:' . $GLOBALS['settings']->adminemail . '">the system administrator</a> to reset your password manually.';
		}
	}
}

?>

<? include('templatetop.php'); ?>
<div class="edt_heading_div"><div class="edt_heading">Reset Password</div></div>
<? if(!empty($message)){ ?><div class="error"><?= $message ?></div><? } ?>
<div class="edt_heading2">Your password has been reset.<br />Please take note of the new password, then login using the form below.<br />We strongly recommend that you change your password immediately!</div><br />

<form name="SubLogin" method="post" action="login.php">
<input type="hidden" name="ret" value="/admin/changepassword.php">

<div class="label_left">Username:</div>
<div class="field"><input class="edt_textbox" type="text" name="username" value="<?= $_REQUEST['username'] ?>" /></div>
<div class="label_left">Password: <b><?= $newpassword ?></b></div>
<div class="field"><input class="edt_textbox" type="password" name="password" /></div>
<div class="label_left"><input type="checkbox" name="remember" id="remember" /><label for="remember">Remember my login</label></div>
<div class="field"><input type="submit" value="Login" name="submit" class="greenbutton updatebutton" /></div>
</form>

<? include('templatebottom.php'); ?>