<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';

require_once('../lib/Global.lib.php');
$usermanager->authenticateUser(RIGHT_SYSTEM);
$message = '';
$sets = $settings->getEditableSettings();
$inputcontrol = new InputControl(280);

if(!empty($_REQUEST['save'])){
	global $settings, $sets;
	$values = array();
	foreach($sets as $set){
		if(!in_array($set['type'],array('hidden','readonly'))){
			$values[$set['name']] = $inputcontrol->processControl($_POST['setting'],$set['name'],$set['type']);
		}
	}
	$res = $settings->updateSettings($values,$error);
	if($res){
		header('Location: editsettings.php?msg=upd');
		exit;
	}else{
		$message = 'There seems to have been a problem updating your settings.<br />' . $error;	
	}
}

function drawSettings(){
	global $settings, $sets, $inputcontrol;
	$cols = array('','','','');
	$colheights = array(0,0,0,0);
	$currentcol = 0;
	$groups = array();
	$currentgrouptitle = '';
	$currentgroup = array();
	foreach($sets as $set){
		if($set['group'] != $currentgrouptitle){
			if(count($currentgroup)) $groups[$currentgrouptitle] = $currentgroup;	
			$currentgroup = array();
			$currentgrouptitle = $set['group'];
		}
		$currentgroup[] = $set;
	}
	if(count($currentgroup)) $groups[$currentgrouptitle] = $currentgroup;
	
	foreach($groups as $title=>$group){
		$blockheight = 40;
		$block = '';
		$block .= '<div class="settings_group">';
		$block .= '<div class="edt_heading2" style="margin-bottom: 10px;">'.$title.'</div>';
		foreach($group as $set){
			$block .= '<div class="label_left">'.$set['title'].'</div>';
			if(!empty($set['description'])) $block .= '<div class="note">'.$set['description'].'</div>';
			$blockheight += 21 + (ceil(strlen($set['description']))/50*19);
			$block .= '<div class="field">'.$inputcontrol->drawStandardControl('setting',$set['name'],$set['type'],$set['data'],$set['value']).'</div>';
			switch($set['type']){
				case 'array':
				case 'multiline':
					$data = (int)$set['data'];
					$rows = empty($data)?4:$data;
					$blockheight += ($rows*20);
					break;
				default:
					$blockheight += 20;
					break;
			}
		}
		$block .= '</div>';	
		
		$usecol = $currentcol;
		$checkheight = $colheights[$usecol] - $blockheight;
		foreach($colheights as $colid=>$h){
			if($h<$checkheight){
				$usecol = $colid;
				break;
			}
		}
		
		if(!empty($cols[$usecol])) $cols[$usecol] .= '<hr />';
		$cols[$usecol] .= $block;
		$colheights[$usecol] += $blockheight;
		$currentcol++;
		if($currentcol==4) $currentcol = 0;
	}
	
	$res .= '<table cellpadding="0" cellspacing="0"><tr>';
	$res .= '<td rowspan="2" style="width: 280px; padding-right: 19px; border-right: solid 1px #aab1a0" valign="top">'.$cols[0].'</td>';
	if(!empty($cols[1])) $res .= '<td rowspan="2" style="width: 280px; padding: 0 19px 0 20px; border-right: solid 1px #aab1a0" valign="top">'.$cols[1].'</td>';
	if(!empty($cols[2])) $res .= '<td rowspan="2" style="width: 280px; padding: 0 19px 0 20px; border-right: solid 1px #aab1a0" valign="top">'.$cols[2].'</td>';
	$res .= '<td style="width: 280px; padding-left: 20px;" valign="top">'.$cols[3].'</td>';
	$res .= '</tr><tr><td style="padding-left: 20px;" valign="bottom"><input type="submit" name="save" value="Save Changes" class="greenbutton updatebutton" id="savebutton" /></td>';
	$res .= '</tr></table>';
	return $res;
}

$pagetitle = 'Website Settings - General';
$cmslinkpageid = 'System';
include('templatetop.php');
?>
<div class="edt_heading_div">
	<table id="optionTabs" class="tabtable" cellpadding="0" cellspacing="0">
		<tr>
			<td class="tabselected"><a href="editsettings.php">Global Settings</a></td>
			<td class="tabseparator">&nbsp;</td>
			<td class="tab"><a href="installation.php">Installation</a></td>
		</tr>
	</table>
</div>
<form action="editsettings.php" method="post" onsubmit="PopupManager.showLoading();">
<? if($_REQUEST['msg']=='upd'){?><script>PopupManager.showCompleted();</script><?}?>
<? if($message!=''){?><div class="error"><?= $message ?></div><?}?>
<div><?= drawSettings() ?></div>
</form>

<? include('templatebottom.php'); ?>