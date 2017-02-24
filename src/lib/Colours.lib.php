<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

require_once('Global.lib.php');

function rgb_hex($red,$green=0,$blue=0){
	if(is_array($red)){
		$green = $red['g'];	
		$blue = $red['b'];
		$red = $red['r'];
	}
	$hex = '#' . str_pad(dechex(round($red)),2,'0',STR_PAD_LEFT) . str_pad(dechex(round($green)),2,'0',STR_PAD_LEFT) . str_pad(dechex(round($blue)),2,'0',STR_PAD_LEFT);
	return $hex;
}

function hex_rgb($hex){
	$hex = trim($hex,'#');
	$red = hexdec(substr($hex,0,2));
	$green = hexdec(substr($hex,2,2));
	$blue = hexdec(substr($hex,4,2));
	return array('r'=>$red,'g'=>$green,'b'=>$blue);
}

function rgb_hsb($red,$green,$blue){
	$red = $red / 255;
	$green = $green / 255;
	$blue = $blue / 255;
	
	$min = min($red, $green, $blue);
	$max = max($red, $green, $blue);
	$diff = $max - $min;
	
	$brightness = $max;
	
	if($diff == 0){
		$hue = 0;
		$saturation = 0;
	}else{
		$saturation = $diff / $max;
		
		$dr = ((($max - $red) / 6) + ($diff / 2)) / $diff;
		$dg = ((($max - $green) / 6) + ($diff / 2)) / $diff;
		$db = ((($max - $blue) / 6) + ($diff / 2)) / $diff;
		
		if($max == $red) $hue = $db - $dg;
		elseif($max == $green) $hue = (1/3) + $dr - $db;
		else $hue = (2/3) + $dg - $dr;
		
		if($hue < 0) $hue += 1;
		if($hue > 1) $hue -= 1;
	}
	
	$brightness = $brightness * 100;
	$hue = $hue * 360;
	$saturation = $saturation * 100;
	return array('h'=>$hue,'s'=>$saturation,'b'=>$brightness);
}

function hsb_rgb($hue,$saturation,$brightness){
	$hue = $hue%360;
	if($hue < 0) $hue = 360 - $hue;
	$hue = $hue/360;
	$brightness = $brightness / 100;
	$saturation = $saturation / 100;
	if($saturation==0) return array('r'=>$brightness*255,'g'=>$brightness*255,'b'=>$brightness*255);
	
	$h = $hue * 6;
	$i = floor($h);
	
	$val1 = $brightness * (1 - $saturation);
	$val2 = $brightness * (1 - ($saturation * ($h - $i)));
	$val3 = $brightness * (1 - ($saturation * (1- ($h - $i))));
	
	if($i == 0){
		$red = $brightness;
		$green = $val3;
		$blue = $val1;	
	}elseif($i == 1){
		$red = $val2;
		$green = $brightness;
		$blue = $val1;	
	}elseif($i == 2){
		$red = $val1;
		$green = $brightness;
		$blue = $val3;	
	}elseif($i == 3){
		$red = $val1;
		$green = $val2;
		$blue = $brightness;	
	}elseif($i == 4){
		$red = $val3;
		$green = $val1;
		$blue = $brightness;	
	}else{
		$red = $brightness;
		$green = $val1;
		$blue = $val2;	
	}
	$red = $red * 255;
	$green = $green * 255;
	$blue = $blue * 255;
	return array('r'=>$red,'g'=>$green,'b'=>$blue);
}

?>