<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

function Standard_drawContactDetails(){
	global $ir;
	$res = '<div class="contact_details">';
	$res .= '<h2 class="contact_header">Contact Details</h2>';
	$res .= '<div class="menu_divider"><img src="'.$ir.'menudivider.png" /></div>';
	if(!empty($GLOBALS['settings']->contactphone)) $res .= '<div class="contact_detail"><span class="contact_label">Phone:</span> '.$GLOBALS['settings']->contactphone.'</div>';
	if(!empty($GLOBALS['settings']->contactfax)) $res .= '<div class="contact_detail"><span class="contact_label">Fax:</span> '.$GLOBALS['settings']->contactfax.'</div>';
	if(!empty($GLOBALS['settings']->contactcell)) $res .= '<div class="contact_detail"><span class="contact_label">Cell:</span> '.$GLOBALS['settings']->contactcell.'</div>';
	if(!empty($GLOBALS['settings']->contactemail)){
		$email = strlen($GLOBALS['settings']->contactemail)>30?str_replace('@','@ ',$GLOBALS['settings']->contactemail):$GLOBALS['settings']->contactemail;
		$res .= '<div class="contact_detail"><a href="mailto:'.obfuscateEmail($GLOBALS['settings']->contactemail).'">'.obfuscateEmail($email).'</a></div>';
	}
	$res .= '</div>';	
	
	return $res;
}

?>