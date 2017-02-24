<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';

require_once('../lib/Global.lib.php');
if($_SESSION['user']){
	if(empty($_REQUEST['ret'])) $_REQUEST['ret'] = 'index.php';
	$_REQUEST['ret'] = preg_replace('|[\?\&]mes=\w+|si','',$_REQUEST['ret']);
	header('Location: ' . $_REQUEST['ret']);
	exit;	
}
$message = '';
$pagetitle = 'Login';
if(empty($_REQUEST['ret'])) $_REQUEST['ret'] = $_SERVER['HTTP_REFERER'];
$_REQUEST['ret'] = preg_replace('|[\?\&]mes=\w+|si','',$_REQUEST['ret']);

switch($_REQUEST['mes']){
	case 'usrsus':
		$message = 'Your user account has been suspended.  Please check your email for the reasons for this, or contact <a href="mailto:' . $GLOBALS['settings']->adminemail . '">the system administrator</a> if you feel that this is in error.';
		break;
	case 'rgtno':
		$message = 'Your account does not have rights to perform this action.';
		break;
}


if(!empty($_REQUEST['submit'])){
	if(empty($_REQUEST['username'])) $message .= ' - Please provide your username<br>';
	if(empty($_REQUEST['password'])) $message .= ' - Please provide your password<br>';	
	if(!empty($message)){
		$message = "There were errors with the login process:<br>" . $message;	
	}else{
		$usr = $usermanager->validateUser($_REQUEST['username'],$_REQUEST['password']);	
		if(empty($usr)){
			$message = 'Login failed. Are you sure you entered the correct password?';
			$usermanager->logout();
		}else{
			switch($usr['status']){	
				case 'suspended':
					$message = 'This account has been suspended. Please contact <a href="mailto:' . $GLOBALS['settings']->adminemail . '">the system administrator</a> if you feel that this is in error.';
					$usermanager->logout();
					break;	
				default:
					$_SESSION['user'] = $usr;
					if($_REQUEST['remember']){						
						$usermanager->setRememberCookie($usr);
					}
					header('Location: ' . $_REQUEST['ret']);
					exit();	
			}
		}
	}
}

?>

<? include('templatetop.php') ?>

<div class="edt_heading_div"><div class="edt_heading">Login</div></div>
<? if(!empty($message)){ ?><div class="error"><?= $message ?></div><? } ?>
<div class="edt_heading2">Please enter your Username and Password</div><br />
<form method="post" action="login.php" name="details" id="details">
<input type="hidden" name="ret" value="<?= $_REQUEST['ret'] ?>">
<div class="label_left">Username:</div>
<div class="field"><input class="edt_textbox" type="text" name="username" value="<?= $_REQUEST['username'] ?>" /></div>
<div class="label_left">Password:</div>
<div class="field"><input class="edt_textbox" type="password" name="password" /></div>
<div class="label_left"><input type="checkbox" name="remember" id="remember" /><label for="remember">Remember my login</label></div>
<div class="field"><input type="submit" value="Login" name="submit" class="greenbutton updatebutton" /></div>
</form>
<div class="label_left"><a href="resetpassword.php">Forgotten your password?</a></div>
<script>document.details.username.focus();</script>
<? include('templatebottom.php') ?>