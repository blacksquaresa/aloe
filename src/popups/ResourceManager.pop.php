<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('../lib/Global.lib.php');
$usermanager->authenticateUser();
require_once('../lib/Agent.lib.php');

$selected = $_REQUEST['selected'];
if(!empty($selected) && pathinfo($selected,PATHINFO_EXTENSION)!='') $selected = dirname($selected);
$sourceid = $_REQUEST['sourceid'];
$owner = $_REQUEST['owner'];
$type = $_REQUEST['type'];
$display = $_REQUEST['display']=='details'?'details':'icons';

$rm = ResourceManager::getResourceManager($type,$display);
$rm->prefix = 'cis';
$rm->selectedpath = $selected;
$rm->linkformat = 'javascript:parent.setElementValue(\'' . $sourceid . '\',\'%s\',\'' . $owner . '\');parent.PopupManager.hideResourceManager(\''.$type.'\');';
$rm->selectedlinkformat = 'javascript:parent.setElementValue(\'' . $sourceid . '\',\'%s\',\'' . $owner . '\');parent.PopupManager.hideResourceManager(\''.$type.'\');';


function drawResourceManager($sourceid,$owner='',$path='',$type='all',$display='details',$mode='select'){	
	$rm = ResourceManager::getResourceManager($type,$display);
	$rm->prefix = 'cis';
	$rm->mode = $mode;
	$rm->linkformat = 'javascript:parent.setElementValue(\'' . $sourceid . '\',\'%s\',\'' . $owner . '\');parent.PopupManager.hideResourceManager(\''.$type.'\');';
	$rm->selectedlinkformat = 'javascript:parent.setElementValue(\'' . $sourceid . '\',\'%s\',\'' . $owner . '\');parent.PopupManager.hideResourceManager(\''.$type.'\');';
	return $rm->_drawFiles($path);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="StyleSheet" href="../css/resourcemanager.css" type="text/css" />
		<script language="javascript" src="../js/Common.js"></script>
		<script language="javascript" src="../js/ResourceManager.js"></script>
		<script language="javascript" src="../js/aim.ajax.js"></script>
		<?=$agent->init();?>
	</head>
	<body>
		<div style="overflow:auto; width:976px; height:380px; padding:0px; text-align:left;">
			<?=$rm->drawResourceManager(true)?>
			<script language="javascript">
				var currenttype = '<?=$type?>';
				var currentdisplay = '<?=$display?>';
				var currentmode = 'select';
				var currentpath = 'resources/';
				var currentuid = 1;
				var currentrights = '<?=ResourceManager::getPathRights($selected,$_SESSION['user']['id'])?>';
				var rsv_ajaxpath = '../ajax/ResourceManager.ajax.php';
				var rsv_webroot = '<?=$GLOBALS['webroot']?>';
				var rsv_prefix = 'cis';
				rs_init();
			</script>
		</div>
	</body>
</html>