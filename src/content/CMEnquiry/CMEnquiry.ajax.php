<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('../../lib/Global.lib.php');
require_once('../../lib/Text.lib.php');
require_once('../../lib/Agent.lib.php');
$agent->init(); 

function CMEnquiry_checkCaptcha($question,$answer){
	$check = checkCaptcha($question,$answer);	
	return $check;
}

function CMEnquiry_sendForm($blockid,$name,$email,$phone,$enquiry){
	require_once('../../lib/Email.lib.php');	
	$block = ContentModule::getContentBlock($blockid,false);
	$target = validateEmail($block->target)?$block->target:$GLOBALS['settings']->adminemail;
	if(empty($res)) $res = sendEnquiryForm($target,$name,$email,$phone,$enquiry,$msg);
	if($res===false){
		$res ="Oops something went wrong\n\r
				Please try again or send your enquiry directly to ".$GLOBALS['settings']->contactemail;	
	}else $res = 'success';
	return $res;
}
?>