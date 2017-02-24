<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once(dirname(dirname(__FILE__)) . '/lib/Global.lib.php');
$usermanager->authenticateUser();
$pagetitle = 'Administration';
$cmslinkpageid = 'index';

switch($_REQUEST['mes']){
	case 'rgtno':
		$message = 'Sorry, your account does not have rights to perform this action.';
		break;
}
?>
<? include('templatetop.php') ?>
<link rel="StyleSheet" href="<?= $cr ?>index.css" type="text/css" />
<? if(!empty($message)){ ?><div class="error"><?= $message ?></div><? } ?>

<div class="homeblock">
	<div class="homelogo"><img src="<?=$ir?>global/welcome.png" alt="Welcome to the Aloe CMS" /></div>
	<div class="homesitename"><?=$GLOBALS['settings']->sitename?></div>
	<div class="homelinks">
		<a href="../resources/CMS_Manual.pdf" target="_blank">Your Aloe CMS Manual (PDF)</a>
	</div>
</div>
<? include('templatebottom.php') ?>