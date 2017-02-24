<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';

require_once('../lib/Global.lib.php');
require_once('../lib/Email.lib.php');
if($_SESSION['user']){
	header('Location: account.php');
	exit;	
}
$message = '';
$pagetitle = 'Reset your Password';


if(!empty($_REQUEST['submit'])){
	if(empty($_REQUEST['username'])) $message .= ' - Please provide your username<br>';
	if(empty($_REQUEST['email'])) $message .= ' - Please provide your email address<br>';
	if(!empty($message)){
		$message = "There were errors with the password reset form:<br>" . $message;	
	}else{
		cleanUserInput(array('email'));
		$usr = $usermanager->getUserByUsername($_REQUEST['username']);
		if(empty($usr)){
			$message = 'There is no user with that username.';
			$usermanager->logout();
		}elseif(strtolower($usr['email']) != strtolower($_REQUEST['email'])){
			$message = 'The username and email address you provided do not correspond.';
			$usermanager->logout();
		}else{	
			if(empty($usr['remembercode'])){
				$code = createRandomCode(9,16);
				$res = dta_update('user',array('remembercode'=>$code),array('id'=>$usr['id']));
				$usr['remembercode'] = $code;				
			}
			if(sendUserResetPassword($usr)){
				header('Location: resetpasswordthank.php');
				exit;
			}else{
				$message = 'There was a problem sending the automatic email. Please contact <a href="mailto:' . $GLOBALS['settings']->adminemail . '">the system administrator</a> to reset your password manually.';
			}
		}
	}
}

?>

<? include('templatetop.php'); ?>
	<script language=javascript>
		<!--
		function validate(sForm) {
			ErrorMsg = "";
						
			non_blank(sForm, "username", "Please provide your username.");
			non_blank(sForm, "email", "Please provide your e-mail address.");	
			valid_email(sForm, "email", "Please supply a valid email address.");		

			if (ErrorMsg != "") {
				ErrorMsg = "The form could not be submitted for the following reasons:" + ErrorMsg;
				window.alert(ErrorMsg);
				return false;
			}
		}
		//-->
	</script>
	<script language="javascript" src="/js/validation.js"></script>

<div class="edt_heading_div"><div class="edt_heading">Forgotten your Password?</div></div>
<? if(!empty($message)){ ?><div class="error"><?= $message ?></div><? } ?>
<div class="edt_heading3">Enter your Username and Email address below and we'll send you a link to reset your password.</div><br />

<form name="SubLogin" method="post" action="resetpassword.php">
<input type="hidden" name="ret" value="<?= $_REQUEST['ret'] ?>">

<div class="label_left">Username:</div>
<div class="field"><input class="edt_textbox" type="text" name="username" value="<?= $_REQUEST['username'] ?>" /></div>
<div class="label_left">Email Address:</div>
<div class="field"><input class="edt_textbox" type="text" name="email" value="<?= $_REQUEST['email'] ?>" /></div>
<div class="field"><input type="submit" value="Request Reset" name="submit" class="greenbutton updatebutton" onClick="return(validate(this.form.name))" /></div>
</form>

<? include('templatebottom.php'); ?>