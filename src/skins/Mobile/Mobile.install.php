<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
$path = dirname(dirname(dirname(__FILE__)));
require_once($path.'/lib/Global.lib.php');
$usermanager->authenticateUser();
require_once($path.'/lib/Install.lib.php');

?>