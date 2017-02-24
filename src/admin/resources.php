<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('../lib/Global.lib.php');
$usermanager->authenticateUser(RIGHT_EDITRESOURCES);
require_once('Agent.lib.php'); 

$resman = ResourceManager::getResourceManager('all','icons');
$resman->prefix = 'rm';
$resman->mode = 'section';

function drawResourceManager($sourceid,$owner='',$path='',$type='all',$display='details',$mode='select'){	
	$rm = ResourceManager::getResourceManager($type,$display);
	$rm->prefix = 'rm';
	$rm->mode = $mode;
	return $rm->_drawFiles($path);
}

$pagetitle = 'Resource Management';
$cmslinkpageid = 'Resources';
include('templatetop.php');
?>
<link rel="StyleSheet" href="../css/contenteditor.css" type="text/css" />
<link rel="StyleSheet" href="../css/popups.css" type="text/css" />
<link rel="StyleSheet" href="../css/contenttreeview.css" type="text/css" />
<link rel="StyleSheet" href="../css/resourcemanager.css" type="text/css" />
<link rel="StyleSheet" href="../css/resources.css" type="text/css" />
<script language="javascript" src="../js/ResourceManager.js"></script>

<? echo $resman->drawResourceManager(); ?>
<script language="javascript">
	var currenttype = 'all';
	var currentdisplay = 'icons';
	var currentmode = 'manage';
	var currentpath = 'resources/';
	var currentuid = 1;
	var currentrights = 'rwt';
	var rsv_ajaxpath = '../ajax/ResourceManager.ajax.php';
	var rsv_webroot = '<?=$GLOBALS['webroot']?>';
	var rsv_prefix = 'rm';
	rs_init();
</script>
<? include('templatebottom.php') ?>