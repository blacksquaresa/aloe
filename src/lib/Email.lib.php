<?php
/**
 * The Email library provides methods to build and send specific emails, interacting with the SendMail library
 * 
 * This library is likely to be depracated in future. Useful functions will be moved to the SendMail library, and individual email 
 * constructors will become the responsibility of other modules
 * 
 * @package Library
 * @subpackage Email
 * @since 2.0
 */
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');
require_once('Global.lib.php');
require_once('SendMail.lib.php');

/**
 * Loads the HTML and plain text templates of each email into the referenced strings.
 * 
 * Templates are stored in the "email" directory, and should be named identically, except the HTML version should have a ".htm" extension 
 * while the plain text version should have a ".txt" extension.
 *
 * @param string $name The name of the email template, without a file extension
 * @param string $message An empty string passed by reference, so be populated by the contens of the plain text version
 * @param string $html An empty string passed by reference, so be populated by the contens of the HTML version
 * @return void
 *
 */
function loadTemplates($name, &$message, &$html){
	$message = file_get_contents($GLOBALS['documentroot'] . '/email/' . $name . '.txt');
	$html = file_get_contents($GLOBALS['documentroot'] . '/email/email.htm');
	$temp = file_get_contents($GLOBALS['documentroot'] . '/email/' . $name . '.htm');
	$html = str_replace('<!--## Content ##-->',$temp,$html);
}

/**
 * Replaces a named variable with the correspoinding string in both the plain text and HTML versions, which are passed by reference.
 * 
 * Templates may have string variables. These are created by surrounding the variable name with pound signs (eg: #variablename#)
 *
 * @param string $varname The name of the variable
 * @param string $varvalue The value that should replace the variable
 * @param string $message The plain text version, passed by reference
 * @param string $html The HTML version, passed by reference
 * @param bool $addnewline Whether or not to add <br /> tags for new lines in the HTML version
 * @return void 
 *
 */
function replace($varname, $varvalue, &$message, &$html, $addnewline=false){
	$message = str_replace('#'.$varname.'#',$varvalue,$message);
	if($addnewline){
		$varvalue = str_replace("\n","<br />\n",$varvalue);	
	}
	$html = str_replace('#'.$varname.'#',$varvalue,$html);	
}

/**
 * Constructs and sends an email to a user when they've asked to have their password reset. This email will allow them to confirm that they actually want their password reset.
 *
 * @param array $usr An array representing the User whose account should be reset
 * @return bool The result of the email sending
 *
 */
function sendUserResetPassword($usr){
	loadTemplates('userresetpassword',$message, $html);
	$title = "Password Reset Request";
	replace('remembercode',$usr['remembercode'],$message,$html);
	replace('name',$usr['name'],$message,$html);
	replace('siteroot',$GLOBALS['settings']->siteroot,$message,$html);
	replace('title',$title,$message,$html);
	return sendHTMLemail($usr['email'],$title,$message,$html);
}

/**
 * Constructs and sends a user an email confirming the change of password. The email includes the new password.
 *
 * @param array $usr An array representing the User whose password has been reset
 * @param string $newpassword The new password. The password is stored with one-way encryption in the user data, so this plain text version must be passed separately.
 * @return bool The result of the email sending
 *
 */
function sendUserNewPassword($usr, $newpassword){
	loadTemplates('usernewpassword',$message, $html);
	$title = "New Password";
	replace('newpassword',$newpassword,$message,$html);
	replace('name',$usr['name'],$message,$html);
	replace('siteroot',$GLOBALS['settings']->siteroot,$message,$html);
	replace('title',$title,$message,$html);
	return sendHTMLemail($usr['email'],$title,$message,$html);
}

/**
 * Constructs and sends an enquiry form to the given target
 *
 * @param string $target The email address to which the email should be sent
 * @param string $name The name field of the form
 * @param string $email The email field of the form. Also used for the reply-to address
 * @param string $phone The phone field of the form
 * @param string $enquiry The actual enquiry
 * @return bool The result of sending the email
 *
 */
function sendEnquiryForm($target,$name,$email,$phone,$enquiry){
	loadTemplates('enquiryform',$message, $html);
	$title ='Enquiry Form';
	$target = validateEmail($target)?$target:$GLOBALS['settings']->adminemail;
	replace('name',$name,$message,$html);
	replace('email',$email,$message,$html);
	replace('phone',$phone,$message,$html);
	replace('enquiry',$enquiry,$message,$html,true);
	replace('siteroot',$GLOBALS['settings']->siteroot,$message,$html);
	replace('sitename',$GLOBALS['settings']->sitename,$message,$html);
	replace('title',$title,$message,$html);
	return sendHTMLemail($target,$title,$message,$html,null,$email);
}
?>