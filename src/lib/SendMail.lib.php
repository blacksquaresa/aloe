<?php
/**
 * The SendMail Library provides method to send emails
 * 
 * In future versions, this library will be merged with the Email library
 * 
 * @package Library
 * @subpackage Email
 * @since 2.0
 **/
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

/**
 * Send an HTML formatted email, with attachments if required
 * 
 * Each attachment should be an array containing three fields: 'content-type', 'filename' and 'content'
 * Multiple email addresses may be submitted for each of the To, Reply-To, CC and BCC parameters. Addresses should be separtated by either a comma (,) or a semi-colon (;).
 * If the site is not live, the To, CC and BCC addresses are ignored, and the email is sent to the admin email address. 
 * This is to prevent emails from a test environment being sent to actual customers.
 * The function will assign all simple CSS styles found in an inline STYLE tag to the respective elements. 
 * This is not a full CSS parser, so only first level tag, class or ID directives will be expanded. For example, if the STYLE tag contains ".myclass: padding: 10px;",
 * this function will find all elements with the "myclass" class, and add a style element with "padding: 10px;" to them.
 *
 * @param string $to The email address to whom to send the email
 * @param string $subject The subject of the email
 * @param string $message A plain text version of the email
 * @param string $html An HTML version of the email
 * @param array $attachments An array containing attachment information
 * @param string $replyto An optional reply-to address
 * @param string $cc An optional CC address
 * @param string $bcc An optional BCC address
 * @return bool True if the email was submitted to the sendmail program (not necessarily a successful delivery), false otherwise.
 *
 */
function sendHTMLemail($to, $subject, $message, $html, $attachments=null, $replyto=null, $cc=null, $bcc=null){
	$fileatt_type = "application/octet-stream";
	$semi_rand = md5(time());  
	$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
	$to = str_replace(';',',',$to);
	$replyto = str_replace(';',',',$replyto);
	$cc = str_replace(';',',',$cc);
	$bcc = str_replace(';',',',$bcc);
	
	// Only send the email to the actual recipient if this is the live site.
	if(!$GLOBALS['settings']->islivesite){
		$to = $GLOBALS['settings']->adminemail;
		$cc = null;
		$bcc = null;
	}
	
	// convert all css classes into inline styles
	if(preg_match('|<style[^>]*>(.*)</style>|si',$html,$matches)){
		$styleblock = $matches[1];
		$styles = explode("\n",$styleblock);
		foreach($styles as $style){
			$style = trim($style);
			if(!empty($style)){
				if(preg_match('|([^{]+){([^}]+)}|si',$style,$matches)){
					$class = trim($matches[1]);
					$code = trim($matches[2]);
					$id = substr($class,0,1);
					switch($id){
						case '.':
							$class = substr($class,1);
							$html = str_replace('class="'.$class.'"','style="'.$code.'"',$html);
							break;
						case '#':
							$class = substr($class,1);
							$html = str_replace('id="'.$class.'"','id="'.$class.'" style="'.$code.'"',$html);
							break;
						default:
							$html = str_replace('<'.$class,'<'.$class.' style="'.$code.'"',$html);
							break;	
					}	
				}	
			}	
		}	
	}
	
	$attach = '';
	if($attachments){
		foreach($attachments as $attachmnent){
			if(!empty($attachmnent['content-type']) && !empty($attachmnent['filename'])){
				$attach .= "--{$mime_boundary}\n" .  
					"Content-Type:" . $attachmnent['content-type'] . "; name=\"" . $attachmnent['filename'] . "\"\n" .   
					"Content-Transfer-Encoding: base64\n\n" . 
					base64_encode($attachment['content']) . "\n";
			}
		}
	}
	
	$headers = "From: ". $GLOBALS['settings']->sourceemail;
	if(!empty($replyto)) $headers .= "\nReply-To: $replyto";
	if(!empty($cc)) $headers .= "\nCC: $cc";
	if(!empty($bcc)) $headers .= "\nBCC: $bcc";
	$headers .= "\nMIME-Version: 1.0\n" .  
		"Content-Type: multipart/mixed;\n" .  
		" boundary=\"{$mime_boundary}\""; 
	$email_message = "$message.\n\n" .  
		"--{$mime_boundary}\n" .  
		"Content-Type:text/html; charset=\"UTF-8\"\n" .  
		"Content-Transfer-Encoding: 7bit\n\n" .  
		$html . "\n\n" . 
		$attach . 
		"--{$mime_boundary}--\n";
	
	$ok = @mail($to, $subject, $email_message, $headers);
	return $ok;
}

/**
 * Send a simple, plain text email
 *
 * @param string $to The email adress to which this email should be sent
 * @param string $subject The subject of the email
 * @param string $message The plain text email
 * @return bool The result of the "mail" call - true if successful, otherwise false.
 *
 */
function sendemail($to,$subject,$message){
	$headers = "From: ". $GLOBALS['settings']->sourceemail;
	$to = str_replace(';',',',$to);
	return mail($to,$subject,$message,$headers);
}


?>