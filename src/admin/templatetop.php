<?php 
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

$wr = $GLOBALS['webroot']; 
$ir = $wr . 'images/admin/'; 
$cr = $wr . 'css/';
$jr = $wr . 'js/';

function drawMenuItem($cmslinkid, $url, $title){
	global $cmslinkpageid;
	$class = $cmslinkpageid==$cmslinkid?'topmenuselected':'topmenuitem';	
	$res = '<span class="' . $class . '">';
	$res .= '<a href="' . $url . '">';
	$res .= $title;
	$res .= '</a>';
	$res .= '</span>';
	return $res;
}

function drawListMenu(){
	global $wr;
	if((!$GLOBALS['settings']->useadminrights || $GLOBALS['usermanager']->checkUserRights(RIGHT_EDITLISTS)) && $GLOBALS['db']->selectsingle("select count(id) from lists")) return drawMenuItem('Lists',$wr.'admin/editlists.php','Lists');
}

function drawModuleMenu(){
	$links = $GLOBALS['db']->select("select * from adminlinks order by position");
	if(is_array($links)){
		foreach($links as $link){
			if(!$GLOBALS['settings']->useadminrights || $GLOBALS['usermanager']->checkUserRights($link['rights'])){
				$res .= drawMenuItem($link['code'],$GLOBALS['webroot'].ltrim($link['path'],'/'),$link['name']);
			}
		}
	}
	return $res;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title><?=$GLOBALS['settings']->sitename?> Website Administration - <?=$pagetitle?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="StyleSheet" href="<?=$cr?>admin.css" type="text/css" />
	<link rel="StyleSheet" href="<?=$cr?>editors.css" type="text/css" />
	<script language="javascript" src="<?=$jr?>Common.js"></script>
	<script language="javascript" src="<?=$jr?>aim.ajax.js"></script>
	<?if(!empty($GLOBALS['settings']->typekit)){?>
	<script type="text/javascript" src="//use.typekit.net/<?=$GLOBALS['settings']->typekit?>.js"></script>
	<script type="text/javascript">try{Typekit.load();}catch(e){}</script>
	<?}?>
	<?=$agent->init();?>
</head>
<body>
	<div id="site">
		<? if(!$GLOBALS['settings']->islivesite){ ?>
		<!-- debug block --><script language=javascript>function debug(text){document.getElementById('debug').innerHTML += '<br />' + text;}</script>
		<div id="debug" style="position: fixed; background-color: #eee; color: #333; padding: 5px; border: solid 1px #333; top: 0px; left: 0px; font-size: 11px;z-index:9999999;"></div>
		<!-- status block --><script language="javascript">function status(text){document.getElementById('status').innerHTML = text; }</script>
		<div id="status" style="position: fixed; background-color: #eee; color: #333; padding: 5px; border: solid 1px #333; top: 0px; right: 0px; font-size: 11px;z-index:9999999;"></div>
		<div id="testsitealert">Test Site - Content and data may be altered or deleted without warning.</div>
		<? } ?>
		<script language=javascript src="<?=$jr?>Popups.js"></script>
		<div id="top">
			<div id="topcontainer">
				<div id="userarea">
					<table cellpadding="0" cellspacing="0">
						<tr>							
							<? if(isset($_SESSION['user'])){ ?>		
							<td style="padding-right: 14px;">Welcome, <?= $_SESSION['user']['name'] ?></td>					
							<td style="padding: 0px 12px 0px 12px;"><a href="<?=$wr?>index.php" target="_blank">Website</a></td>
							<td style="padding: 0px 12px 0px 12px;"><a href="<?=$wr?>admin/changepassword.php">Change Password</a></td>
							<td style="padding: 0px 12px 0px 12px;"><a href="<?=$wr?>admin/logout.php">Logout</a></td>
							<?}else{?>
							<td style="padding: 0px 12px 0px 12px;"><a href="<?=$wr?>index.php" target="_blank">Website</a></td>
							<?}?>
						</tr>
					</table>
				</div>
				<div class="cms_header" >
					<table cellpadding="0" cellspacing="0" >
						<tr>
							<td><img src="<?=$ir?>global/aloelogo.png" alt="Aloe CMS" /></td>
							<td style="padding-top: 12px;">Website Management System for <span class="webtitle"><?=$GLOBALS['settings']->sitename?></span></td>
						</tr>
					</table>
				</div>
			</div>
			<div id="topmenu">	
				<? if(isset($_SESSION['user'])){ ?>	
					<?= drawMenuItem('index',$wr.'admin/index.php','Home'); ?>
					<?= drawModuleMenu(); ?>
					<?= drawListMenu(); ?>
					<? if(!$GLOBALS['settings']->useadminrights || $GLOBALS['usermanager']->checkUserRights(RIGHT_SYSTEM)) { echo drawMenuItem('System',$wr.'admin/editsettings.php','System'); }?>		
				<?}?>
			</div>
		</div>

		<div id="main">