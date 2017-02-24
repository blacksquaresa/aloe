<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';

require_once('../lib/Global.lib.php');
$usermanager->authenticateUser(RIGHT_SYSTEM);
$message = '';

if(!empty($_REQUEST['action'])){
	require_once('../lib/Install.lib.php');
	cleanUserInput(array_keys($_REQUEST));
	header('Location: installation.php');
	$_SESSION['installresult'] = &$message;
	switch($_REQUEST['type']){
		case 'skin':
			$removecurrent = $GLOBALS['documentroot'].'/skins/'.$GLOBALS['settings']->skin.'/'.$GLOBALS['settings']->skin.'.remove.php';
			if(file_exists($removecurrent)){
				$message .= 'Uninstalling old skin<br />';
				include($removecurrent);
			}
			$installnew = $GLOBALS['documentroot'].'/skins/'.$_REQUEST['class'].'/'.$_REQUEST['class'].'.install.php';
			if(file_exists($installnew)){
				$message .= 'Installing new skin<br />';
				include($installnew);
			}
			addGlobalSetting('skin','hidden','',$_REQUEST['class'],'','Skin','',$message) or die($message);
			break;	
		case 'module':
			$processfile = $GLOBALS['documentroot'].'/modules/'.$_REQUEST['class'].'/'.$_REQUEST['class'].'.'.($_REQUEST['action']=='remove'?'remove':'install').'.php';
			if(file_exists($processfile)){
				$message .= rtrim(ucwords($_REQUEST['action']),'e').'ing '.$_REQUEST['class'].' module<br />';
				include($processfile);
			}else{
				$message .= ucwords($_REQUEST['action']).' file for '.$_REQUEST['class'].' could not be found<br />';
			}
			break;
		case 'block':
			$processfile = $GLOBALS['documentroot'].'/content/'.$_REQUEST['class'].'/'.$_REQUEST['class'].'.'.($_REQUEST['action']=='remove'?'remove':'install').'.php';
			if(file_exists($processfile)){
				$message .= rtrim(ucwords($_REQUEST['action']),'e').'ing '.$_REQUEST['class'].' module<br />';
				include($processfile);
			}else{
				$message .= ucwords($_REQUEST['action']).' file for '.$_REQUEST['class'].' could not be found<br />';
			}
			break;
	}	
	exit;
}

function drawSkins(){
	$skins = glob($GLOBALS['documentroot'].'/skins/*',GLOB_ONLYDIR);
	if(is_array($skins)){
		$res .= '<table cellpadding="2" cellspacing="0" width="100%">';
		foreach($skins as $skinroot){
			try{
				$classname = basename($skinroot);
				$classpath = $skinroot . '/' . $classname . '.skin.php';
				require_once($classpath);
				eval('$skin = new '.$classname.'();');
				$res .= '<tr><td class="label_left">'.$skin->name.'</td>';
				$res .= '<td class="label_left" style="width: 80px;">';
				if($GLOBALS['settings']->skin == $classname) $res .= 'Current Skin';
				else $res .= '<a href="installation.php?action=install&type=skin&class='.$classname.'" class="install_install">Install now</a>';
				$res .= '</td></tr>';
			}catch(exception $err){}
		}
		$res .= '</table>';
	}
	return $res;
}

function drawModules(){
	$modules = glob($GLOBALS['documentroot'].'/modules/*',GLOB_ONLYDIR);
	if(is_array($modules)){
		$res .= '<table cellpadding="2" cellspacing="0" width="100%">';
		foreach($modules as $modroot){
			try{
				$classname = basename($modroot);
				$installpath = $modroot . '/' . $classname . '.install.php';
				$removepath = $modroot . '/' . $classname . '.remove.php';
				$res .= '<tr><td class="label_left">'.$classname.'</td>';
				$res .= '<td class="label_left" style="width: 60px;">';
				if(file_exists($installpath)) $res .= ' <a href="installation.php?action=install&type=module&class='.$classname.'" class="install_install">Install</a>';
				$res .= '</td><td class="label_left" style="width: 60px;">';
				if(file_exists($removepath)) $res .= ' <a href="installation.php?action=remove&type=module&class='.$classname.'" class="install_remove">Remove</a>';
				$res .= '</td></tr>';
			}catch(exception $err){}
		}
		$res .= '</table>';
	}
	return $res;
}

function drawContentBlocks(){
	$blocks = glob($GLOBALS['documentroot'].'/content/*',GLOB_ONLYDIR);
	$skinblocks = $GLOBALS['skin']->getEditorNames();
	if(is_array($blocks)){
		$res .= '<table cellpadding="2" cellspacing="0" width="100%">';
		foreach($blocks as $blockroot){
			try{
				$classname = basename($blockroot);
				$installpath = $blockroot . '/' . $classname . '.install.php';
				$removepath = $blockroot . '/' . $classname . '.remove.php';
				$configpath = $blockroot . '/' . $classname . '.config.php';
				include($configpath);
				$style = in_array($classname,$skinblocks)?'label_left':'label_soft';
				$res .= '<tr><td class="'.$style.'">'.$ContentModules[$classname]['name'].'</td>';
				$res .= '<td class="label_left" style="width: 60px;">';
				if(file_exists($installpath)) $res .= ' <a href="installation.php?action=install&type=block&class='.$classname.'" class="install_install" target="installwindow">Install</a>';
				$res .= '</td><td class="label_left" style="width: 60px;">';
				if(file_exists($removepath)) $res .= ' <a href="installation.php?action=remove&type=block&class='.$classname.'" class="install_remove" target="installwindow">Remove</a>';
				$res .= '</td></tr>';
			}catch(exception $err){}
		}
		$res .= '</table>';
	}
	return $res;
}

function drawResults(){
	if(!empty($_SESSION['installresult'])){
		$res = $_SESSION['installresult'];
		unset($_SESSION['installresult']);
	}
	return $res;
}

function drawDataCollection(){
	if(!empty($_SESSION['installdatafields'])){
		$inputcontrol = new InputControl(460);
		$res .= '<div id="install_variables" class="content_popupcontent" style="display: none;">';
		$res .= '<form action="installation.php" method="post">';
		$res .= '<input type="hidden" name="action" value="'.$_SESSION['installparameters']['action'].'" />';
		$res .= '<input type="hidden" name="type" value="'.$_SESSION['installparameters']['type'].'" />';
		$res .= '<input type="hidden" name="class" value="'.$_SESSION['installparameters']['class'].'" />';
		foreach($_SESSION['installdatafields'] as $field){
			$res .= '<div class="label_left">'.(empty($field['label'])?ucwords($field['name']):$field['label']).'</div>';
			$res .= '<div class="field">'.$inputcontrol->drawStandardControl('installvariables',$field['name'],$field['type'],$field['data'],'').'</div>';
		}
		$res .= '<div><input type="submit" value="Continue" class="greenbutton updatebutton" /></div>';
		$res .= '</form>';
		$res .= '</div>';
		
		$res .= '<script language="javascript">';
		$res .= "PopupManager.showLoading();\r\n";
		$res .= "var pop = PopupManager.createOrFetchPopup('install_variables','Installation Data',0,0,'div','install_variables','loading');\r\n";
		$res .= "pop.Show();\r\n";
		$res .= '</script>';
		unset($_SESSION['installdatafields']);
		unset($_SESSION['installparameters']);
	}	
	return $res;
}

$pagetitle = 'System - Install or Uninstall modules, skins and content blocks';
$cmslinkpageid = 'System';
include('templatetop.php');
?>
<link rel="StyleSheet" href="../css/system.css" type="text/css" />
<div class="edt_heading_div">
	<table id="optionTabs" class="tabtable" cellpadding="0" cellspacing="0">
		<tr>
			<td class="tab"><a href="editsettings.php">Global Settings</a></td>
			<td class="tabseparator">&nbsp;</td>
			<td class="tabselected"><a href="installation.php">Installation</a></td>
		</tr>
	</table>
</div>
<div>
	<table cellpadding="0" cellspacing="0">
		<tr>
			<td style="width: 280px; padding-right: 19px; border-right: solid 1px #aab1a0" valign="top">
				<div class="edt_heading2" style="margin-bottom: 10px;">Skins</div>
				<?=drawSkins();?>
			</td>
			<td style="width: 280px; padding: 0 19px 0 20px; border-right: solid 1px #aab1a0" valign="top">
				<div class="edt_heading2" style="margin-bottom: 10px;">Modules</div>
				<?=drawModules();?>
			</td>
			<td style="width: 280px; padding: 0 19px 0 20px; border-right: solid 1px #aab1a0" valign="top">
				<div class="edt_heading2" style="margin-bottom: 10px;">Content Blocks</div>
				<?=drawContentBlocks();?>
			</td>
			<td style="width: 280px; padding-left: 20px;" valign="top">
				<div class="edt_heading2" style="margin-bottom: 10px;">Results</div>
				<div id="installwindow" class="install_window"><?=drawResults();?></div>
			</td>
		</tr>
	</table>
</div>
<?=drawDataCollection();?>

<? include('templatebottom.php'); ?>