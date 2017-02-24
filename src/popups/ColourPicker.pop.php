<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('../lib/Global.lib.php');
require_once('../lib/Colours.lib.php');

$selected = $_REQUEST['selected'];
$sourceid = $_REQUEST['sourceid'];
$owner = $_REQUEST['owner'];
$frameid = $_REQUEST['pageid'];

if(empty($selected)) $selected == 'transparent';
if($selected=='transparent'){
	$style = 'border: dotted 1px #ccc;';
}else{
	$style = 'background-color: ' . $selected;	
	$rgb = hex_rgb($selected);
	$red = $rgb['r'];$green = $rgb['g'];$blue = $rgb['b'];
	$hexvalue = strtolower(substr($selected,1));
	$hsb = rgb_hsb($red,$green,$blue);
	$selectorhue = rgb_hex(hsb_rgb($hsb['h'],100,100));
	$posstyle = 'bottom: ' . (round($hsb['h']/360*256)-3) . 'px;';
	$spotstyle = 'bottom: ' . (round($hsb['b']/100*256)-7) . 'px;left: ' . (round($hsb['s']/100*256)-7) . 'px;';
}

function drawCurrentColours(){
	$colours = array();	
	$styles = $GLOBALS['skin']->getGlobalCSS();
	foreach($styles as $stylepath){
		$style = file_get_contents($stylepath);
		$found = preg_match_all('/#[0-9a-f]{3,6}/i',$style,$matches);
		if($found){
			foreach($matches[0] as $match){
				if(strlen($match) < 7){
					if(strlen($match) == 4) $match = '#' . substr($match,1,1) . substr($match,1,1) . substr($match,2,1) . substr($match,2,1) . substr($match,3,1) . substr($match,3,1);
					else $match = str_pad($match,7,'0',STR_PAD_RIGHT);
				}
				$colours[$match]++;	
			}
		}
	}
	asort($colours,SORT_NUMERIC);
	$colours = array_reverse(array_keys($colours));
	
	for($i=0;$i<10;$i++){
		$colour = isset($colours[$i])?$colours[$i]:'transparent';
		if($colour=='transparent'){
			$res .= '<div class="transblock" onclick="setColour(\'transparent\')">&nbsp;</div>';
		}else{
			$res .= '<div class="colourblock" style="background-color: '.$colour.';" onclick="setColour(\''.$colour.'\')">&nbsp;</div>';
		}	
	}
	return $res;
}

function drawSimpleColours(){
	$rows = array(
			array('s'=>100,'b'=>100,'g'=>100),
			array('s'=>100,'b'=>80,'g'=>90),
			array('s'=>100,'b'=>60,'g'=>80),
			array('s'=>100,'b'=>40,'g'=>70),
			array('s'=>100,'b'=>20,'g'=>56),
			array('s'=>25,'b'=>100,'g'=>44),
			array('s'=>29,'b'=>85,'g'=>33),
			array('s'=>36,'b'=>70,'g'=>20),
			array('s'=>46,'b'=>55,'g'=>11),
			array('s'=>63,'b'=>40,'g'=>3),
			);
	foreach($rows as $row){
		$res .= '<div class="colourblock" style="background-color: #000000;" onclick="setColour(\'#000000\')">&nbsp;</div>';
		$rgb = hsb_rgb(0,0,$row['g']);
		$colour = rgb_hex($rgb['r'],$rgb['g'],$rgb['b']);
		$res .= '<div class="colourblock" style="background-color: '.$colour.';" onclick="setColour(\''.$colour.'\')">&nbsp;</div>';
		foreach(array(0,12,24,36,48,60,72,84,96,108,144,168,180,192,204,216,228,240,264,276,288,300,312,336,348) as $hue){
			$rgb = hsb_rgb($hue,$row['s'],$row['b']);
			$colour = rgb_hex($rgb['r'],$rgb['g'],$rgb['b']);
			$res .= '<div class="colourblock" style="background-color: '.$colour.';" onclick="setColour(\''.$colour.'\')">&nbsp;</div>';
		}
	}
	return $res;
}

function drawColourSelector($colour){
	global $red,$green,$blue,$selected;
	$res .= '<input type="text" name="'.$colour.'" id="'.$colour.'" value="'.$$colour.'" onchange="selectColour(\''.$colour.'\');" class="shadepicker"'.($selected=='transparent'?' disabled':'').' />';
	return $res;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="StyleSheet" href="../css/contentframe.css" type="text/css" />
		<link rel="StyleSheet" href="../css/colourpicker.css" type="text/css" />
		<link rel="StyleSheet" href="../css/editors.css" type="text/css" />
		<link rel="StyleSheet" href="../css/popups.css" type="text/css" />
		<script language="javascript" src="../js/Common.js"></script>
		<script language="javascript" src="../js/ColourPicker.js"></script>
		<script language="javascript">
			var currentcolour = '<?=$selected?>';
			var sourceid = '<?=$sourceid?>';
			var owner = '<?=$owner?>';
		</script>
	</head>
	<body>
		<table cellpadding="0" cellspacing="0" width="540">
			<tr>
				<td style="width: 200px; padding-right: 20px;" valign="top">
					<div class="colourcontainer">
						Common Site Colours:<br />
						<?=drawCurrentColours();?>
						<div style="clear:both;"></div>
					</div>
					<hr />	
					<div>#<input type="text" name="hex" id="hex" value="<?=$hexvalue?>" class="hexbox" onchange="selectHex();" onkeyup="textHex();" /></div>
					<hr />				
					<table cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td nowrap>R <?=drawColourSelector('red')?></td>
							<td nowrap align="center">G <?=drawColourSelector('green')?></td>
							<td nowrap align="right">B <?=drawColourSelector('blue')?></td>
						</tr>
					</table>
					<hr />	
					<div><input type="checkbox" name="transparent" id="transparent" onclick="toggleTransparent();"<?=($selected=='transparent'?' checked':'')?> /> <label for="transparent">Transparent</label></div>
					<hr />	
					<table cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td>New</td>
							<td align="right">Current</td>
						</tr>
						<tr>
							<td><div id="newcolour" class="largecolourblock" style="<?=$style?>"></div></td>
							<td><div id="currentcolour" class="largecolourblock" style="<?=$style?>; cursor: pointer;" onclick="setColour('<?=$selected?>')"></div></td>
						</tr>
					</table>
				</td>
				<td align="right">
					<table cellpadding="0" cellspacing="0" width="326">
						<tr>
							<td valign="top">
								<div id="hueslider" onmousedown="HueMouseDown(event);">
									<img id="colourpos" src="../images/popups/colour-pos.png" style="<?=$posstyle?>" ondragstart="return false;" />
									<img src="../images/popups/colour-hue.png" ondragstart="return false;" />
								</div>
							</td>
							<td valign="top">
								<div id="selector" style="background-color: <?=$selectorhue?>" onmousedown="SpotMouseDown(event);">
									<img src="../images/popups/black-white-overlay.png" ondragstart="return false;" />
									<img id="colourspot" src="../images/popups/colour-spot.png" style="<?=$spotstyle?>" ondragstart="return false;" />
								</div>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td style="width: 540px;padding-top: 14px;" valign="top" colspan="2">
					<div class="colourcontainer">
						<?=drawSimpleColours();?>
						<div style="clear:both;"></div>
					</div>				
				</td>
			</tr>
			<tr>
				<td valign="bottom"></td>
				<td valign="bottom" align="right"><a class="edt_button" style="margin-right: 6px;" href="javascript:returnColour();">Save</a></td>
			</tr>
		</table>
	</body>
</html>