<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

function AloeBlue_drawContactDetails(){
	if(!empty($GLOBALS['settings']->contactphone)) $res .= '<div class="contact_detail contact_phone">'.$GLOBALS['settings']->contactphone.'</div>';
	if(!empty($GLOBALS['settings']->contactemail)){
		$sizes = array(21=>20,23=>19,25=>18,26=>17,28=>16,30=>15);
		foreach($sizes as $chars=>$size){
			if(strlen($GLOBALS['settings']->contactemail) <= $chars){
				$style = ' style="font-size: '.$size.'px;"';
				break;
			}
		}
		$obfuscated = obfuscateEmail(strtolower($GLOBALS['settings']->contactemail));
		if(empty($style)){
			$res .= '<div class="contact_detail contact_email"><a href="mailto:'.$obfuscated.'">Email Us</a></div>';
		}else{
			$res .= '<div class="contact_detail contact_email"'.$style.'><a href="mailto:'.$obfuscated.'">'.$obfuscated.'</a></div>';
		}
	}	
	return $res;
}

?>