<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

function Testing_drawContactDetails(){
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

function Testing_drawSiteSettings(){
	global $settings;
	$res .= '<div style="padding: 10px;">';
	$res .= '<h2>Site Settings</h2>';
	$res .= '<div>Site name: '.$settings->sitename.'</div>';
	$res .= '<div>Float value: '.$settings->floatvalue.'</div>';
	$res .= '<div>Integer value: '.$settings->intvalue.'</div>';
	$res .= '<div>More details: <a href="'.$settings->document.'" target="_blank">'.basename($settings->document).'</a></div>';
	$res .= '<div>Random download: <a href="'.$settings->file.'" target="_blank">'.basename($settings->file).'</a></div>';
	$res .= '<div>Further Reading: <a href="'.$settings->link.'" target="_blank">'.basename($settings->link).'</a></div>';
	$res .= '<div>Description: '.$settings->description.'</div>';
	if(is_array($settings->authors)) $res .= '<div>Authors: '.implode(',',$settings->authors).'</div>';
	$res .= '</div>';
	return $res;
}

function Testing_drawPageSettings(){
	global $pageobject;
	$res .= '<div style="padding: 10px;">';
	$res .= '<h2>Page Settings</h2>';
	$res .= '<div>Page name: '.$pageobject->custom['pagename'].'</div>';
	$res .= '<div>Float value: '.$pageobject->custom['floatvalue'].'</div>';
	$res .= '<div>Integer value: '.$pageobject->custom['intvalue'].'</div>';
	$res .= '<div>More details: <a href="'.$pageobject->custom['document'].'" target="_blank">'.basename($pageobject->custom['document']).'</a></div>';
	$res .= '<div>Random download: <a href="'.$pageobject->custom['file'].'" target="_blank">'.basename($pageobject->custom['file']).'</a></div>';
	$res .= '<div>Further Reading: <a href="'.$pageobject->custom['link'].'" target="_blank">'.basename($pageobject->custom['link']).'</a></div>';
	$res .= '<div>Description: '.$pageobject->custom['description'].'</div>';
	if(is_array($pageobject->custom['authors'])) $res .= '<div>Authors: '.implode(',',$pageobject->custom['authors']).'</div>';
	$res .= '</div>';
	return $res;
}

function Testing_drawLayoutSettings($layout){
	$res .= '<div style="padding: 10px;background-color: '.$layout->custom['headerback'].';border: dotted 1px #888888;margin-bottom: 20px;">';
	$res .= '<h2>Layout Settings</h2>';
	$res .= '<table><tr><td valign="top" style="padding-right: 20px;">';
	$res .= '<div>Layout name: '.$layout->custom['layoutname'].'</div>';
	$res .= '<div>Float value: '.$layout->custom['floatvalue'].'</div>';
	$res .= '<div>Integer value: '.$layout->custom['intvalue'].'</div>';
	$res .= '</td><td valign="top" style="padding-right: 20px;">';
	$res .= '<div>More details: <a href="'.$layout->custom['document'].'" target="_blank">'.basename($pageobject->custom['document']).'</a></div>';
	$res .= '<div>Random download: <a href="'.$layout->custom['file'].'" target="_blank">'.basename($pageobject->custom['file']).'</a></div>';
	$res .= '<div>Further Reading: <a href="'.$layout->custom['link'].'" target="_blank">'.basename($pageobject->custom['link']).'</a></div>';
	$res .= '</td><td valign="top">';
	if(is_array($layout->custom['authors'])) $res .= '<div>Authors: '.implode(',',$layout->custom['authors']).'</div>';
	$res .= '<div>Description: '.$layout->custom['description'].'</div>';
	$res .= '</td></tr></table>';
	$res .= '</div>';
	return $res;
}

?>