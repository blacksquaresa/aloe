<?php
/**
 * The Text library contains useful functions to manipulate strings
 * 
 * @package Library
 * @subpackage Text
 * @since 2.0
 **/
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');
/**
 * The key to use when encrypting strings. In future versions, this will be moved to settings
 **/
define('ENCRYPT_KEY','PHv2Jtr5kXJca41ArTyK7FlyR5sfE3Lk');
/**
 * The IV to use when encrypting strings. In future versions, this will be moved to settings
 **/
define('ENCRYPT_IV','2nnawCAK7A3YkmAhcYVJmIDQtJlCGVMQ');

/**
 * Encrypt a string using the system key
 *
 * @param string $text The text to be encrypted
 * @return string The encrypted text
 *
 */
function encrypt($text){
	return mcrypt_encrypt(MCRYPT_RIJNDAEL_256, ENCRYPT_KEY, $text, MCRYPT_MODE_ECB, ENCRYPT_IV);
}

/**
 * Decrypt a string encrypted with the "encrypt" method
 *
 * @param string $text The encrypted text
 * @return string The decrypted version
 *
 */
function decrypt($text){
	return mcrypt_decrypt(MCRYPT_RIJNDAEL_256, ENCRYPT_KEY, $text, MCRYPT_MODE_ECB, ENCRYPT_IV);
}

/**
 * Clean the specified entries in the $_REQUEST superglobal. see cleanSingleUserInput.
 *
 * @param array $vars A list of the keys to be processed
 * @return void 
 *
 */
function cleanUserInput($vars){
	foreach($vars as $var){
		if(!empty($_REQUEST[$var])){
			cleanSingleUserInput($_REQUEST[$var]);
		}	
	}	
}

/**
 * Clean a single user input variable. This wills trip out all HTML tags, then convert < and > into their respective entities
 *
 * @param mixed $var The variable to process. If this is an array, all the entries in the array will be processed.
 * @return void 
 *
 */
function cleanSingleUserInput(&$var){
	if(is_array($var)){
		foreach($var as &$val){
			cleanSingleUserInput($val);
		}
	}else{
		$var = preg_replace('|<[^>]*?>|si','',$var);	
		$var = str_replace('<','&lt;',$var);
		$var = str_replace('>','&gt;',$var);
	}
}

/**
 * Convert a string into a unix timestamp. Uses strtotime, but with some additional processing
 *
 * @param string $date The string to parse
 * @return int The unix timestamp
 *
 */
function strtodate($date){
	if(is_numeric($date)) return $date;
	if(empty($date)) return 0;
	$date = preg_replace('/[^\w\d\s]/si','',$date);
 	$ed = strtotime($date);
	if($ed==-1) return false;
	return $ed;
}

/**
 * Creates a random code of a random length between the min and max lengths. The code is made up of digits (0-9) and ASCII letters (a-z), both upper and lower case.
 *
 * @param int $min The minimum number of characters in the code. If no maximum is supplied, the code will be exactly this length
 * @param int $max The maximum length of the code
 * @return string The resulting code
 *
 */
function createRandomCode($min,$max=null){
	if($max===null) $max = $min;
	$code = '';
	for($i=0;$i<mt_rand($min,$max);$i++){
		$ind = mt_rand(48,109);
		if($ind > 57) $ind += 7;
		if($ind > 90) $ind += 6;
		$code .= chr($ind);		
	}
	return $code;
}

/**
 * Clean a string to be used as the value in a textbox. Replaces all inverted commas (") with the appropriate entity
 *
 * @param string $inp The string to be processed
 * @return string The cleaned string
 *
 */
function clean($inp){
	return stripslashes(str_replace('"','&#34;',$inp));
}

/**
 * Calculate the number of months between two dates, represented by unix timestamps
 *
 * @param int $date1 The unix timestamp for the first date
 * @param int $date2 The unix timestamp for the second date
 * @return int The number of months between them.
 *
 */
function dateDifferenceMonths($date1,$date2){
	if($date1 > $date2){
		$d1 = $date2;
		$d2 = $date1;
	}else{
		$d1 = $date2;
		$d2 = $date1;
	}
	$dy1 = date('d',$d1);
	$dy2 = date('d',$d2);
	$m1 = date('m',$d1);
	$m2 = date('m',$d2);
	$y1 = date('Y',$d1);
	$y2 = date('Y',$d2);
	
	$diff = $m1 - $m2 + (12 * ($y1 - $y2)) - ($dy1 < $dy2?1:0);
	return $diff;
}

/**
 * Validate an email address
 *
 * @param string $email The email address to be validated
 * @param bool $canhavemultiple Whether or not the string can contain multiple email addresses, separated by commas or semi-colons. Default no.
 * @return bool Whether or not the supplied string is a valid email address
 *
 */

function validateEmail($email,$canhavemultiple=false){
	if($canhavemultiple){
		$address = strtok($email,",;");		
		while ($address !== false) {
			if(!preg_match('/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/',trim($address))) return false;
			$address = strtok(",;");
		}
		return true;
	}else{
		return preg_match('/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/',$email);
	}
}

/**
 * Obfuscates an email address (or any other string) by replacing all characters with HTML entities. 
 * Designed to allow email addresses to be displayed on websites without automated email scrapers being able to collect them.
 *
 * @param string $email The email address to obfuscate
 * @return string The obfuscated string
 *
 */
function obfuscateEmail($email){
	for($i=0;$i<strlen($email);++$i){ 
		$n = rand(0,1); 
		if($n) 
			$res.='&#x'.sprintf("%X",ord($email{$i})).';'; 
		else 
			$res.='&#'.ord($email{$i}).';'; 
	} 
	return $res;	
}

/**
 * Make sure a file name is clean - has no spaces or other invalid characters
 *
 * @param string $file The file name
 * @param bool $check Whether or not to check for the existance of a file with the processed name. If true, and an existing file is found, the file name is updated with a numeric suffix
 * @param int $maxlen The maximum length of the file name
 * @return string The resulting file name
 *
 */
function getCleanFilename($file,$check=false,$maxlen=60){
	$parts = pathinfo($file);
	$name = getCleanRoot($parts['filename'],$maxlen,'-',true);
	$newfile = $parts['dirname'] . '/' . $name . '.' . strtolower($parts['extension']);
	if($check){
		$ind = 2;
		while(file_exists($newfile)){
			$newfile = $parts['dirname'] . '/' . $name . '_' . $ind . '.' . strtolower($parts['extension']);
			$ind++;
		}	
	}
	return $newfile;
}

/**
 * Clean a string to ensure it can be used for a file or URL. 
 * All spaces are removed, all non-ASCII characters are either removed or replaced with similar ASCII characters. The file name is trimmed to the maximum size
 *
 * @param string $name The string to be cleaned
 * @param int $maxlen The maximum length of the resulting string
 * @param string $replacechar The character to use to replace invalid characters
 * @param bool $casesensitive Whether or not the rtesult should be case sensitive. If not, a lowerrcase string will be returned
 * @return string The cleaned string
 *
 */
function getCleanRoot($name,$maxlen=60,$replacechar='_',$casesensitive=false){
	$name = trim($name);
	if(!$casesensitive) $name = strtolower($name);
	$encoding =  mb_detect_encoding($name);
	$ts = array("/[À-Å]/","/Æ/","/Ç/","/[È-Ë]/","/[Ì-Ï]/","/Ð/","/Ñ/","/[Ò-ÖØ]/","/×/","/[Ù-Ü]/","/[Ý-ß]/","/[à-å]/","/æ/","/ç/","/[è-ë]/","/[ì-ï]/","/ð/","/ñ/","/[ò-öø]/","/÷/","/[ù-ü]/","/[ý-ÿ]/","|['’`“”\"\.,]+|si",'|[^\w\d]+|si');
	$tn = array("A","AE","C","E","I","D","N","O","X","U","Y","a","ae","c","e","i","d","n","o","x","u","y",'',$replacechar);
	$name = iconv($encoding, 'latin1',$name);
	$name = preg_replace($ts,$tn, $name);
	$name = trim($name,$replacechar);
	if($maxlen && strlen($name) > $maxlen){
		$name = trim(substr($name,0,$maxlen),$replacechar);
		$pos = strrpos($name,$replacechar);
		if($pos > $maxlen/2) $name  = trim(substr($name,0,$pos),$replacechar);
	}
	return $name;
}

/**
 * Convert a byte count into a human readable string (in Kilobytes, Megabytes, etc.)
 *
 * @param int $size The number of bytes
 * @param int $decimals The number of decimal places to display
 * @return string The resulting string
 *
 */
function human_size($size, $decimals = 1) {
	$suffix = array('Bytes','KB','MB','GB','TB','PB','EB','ZB','YB','NB','DB');
	$i = 0;
	while ($size >= 1024 && ($i < count($suffix) - 1)){
		$size /= 1024;
		$i++;
	}
	return round($size, $decimals).' '.$suffix[$i];
}

/**
 * Convert newline characters to BR tags
 *
 * @param string $str The string to be processed
 * @return string The resulting string
 * @deprecated use the nl2br PHP function instead
 *
 */
function newLine2Br($str){
	return  !empty($str)? preg_replace("/\r\n|\n|\r/",'<br />',$str):'';	
}

/**
 * Draws a captcha question, and includes a hidden INPUT tag containing the encrypted result
 *
 * @param string $fieldname The field name of the CAPTCHA field
 * @return string The resulting HTML fragment to be inserted into the page
 *
 */
function drawCaptcha($fieldname='question'){
	$values = array(1=>'one',2=>'two',3=>'three',4=>'four',5=>'five',6=>'six',7=>'seven',8=>'eight',9=>'nine');	
	$first = array_rand($values);
	$second = array_rand($values);
	$res .= $values[$first] . ' + ' . $values[$second] . ' = ';
	$result = $first + $second;
	$result = base64_encode(encrypt($result));
	$res .= '<input type="hidden" name="' . $fieldname . '" id="' . $fieldname . '" value="' . $result . '" />';
	return $res;
}

/**
 * Check the answer to a CAPTCHA question
 *
 * @param string $question The encrypted question - the value included in the hidden INPUT tag by the drawCapthcha method
 * @param string $answer The answer provided by the user
 * @return bool True if the answer matches the question, false otherwise
 *
 */
function checkCaptcha($question, $answer){
	if(empty($answer) || !is_numeric($answer)) return false;
	if(empty($question)) return false;
	$question = decrypt(base64_decode($question));
	$question = intval($question);
	if(empty($question) || !is_numeric($question)) return false;
	return $question == $answer;	
}

/**
 * Identifies the target of a link. Links to external sites always return "_blank" as the target, while links to pages within the site return no target.
 *
 * @param string $link The link to check
 * @param bool $includeattribute Whether to include the 'target=' with the result
 * @return string The target to use for the link, or an empty string if the link is to a page within the site.
 *
 */
function getLinkTarget($link,$includeattribute=true){
	$target = '';
	if(strlen($link) > 4){
		if(substr($link,0,7) == 'http://' || substr($link,0,8) == 'https://' || !in_array(pathinfo($link,PATHINFO_EXTENSION),$GLOBALS['settings']->validpagetypes)){
			$target = '_blank';
			if($includeattribute) $target = ' target="'.$target.'"';
		}
	}
	return $target;
}
?>