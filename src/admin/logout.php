<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';

session_start();
foreach(array_keys($_SESSION) as $key){
	unset($_SESSION[$key]);
}
setcookie('userid','',time(),'/');
header('Location: login.php');
exit;

?>