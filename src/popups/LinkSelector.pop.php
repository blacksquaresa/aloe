<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('../lib/Global.lib.php');
require_once('../lib/HTMPaths.lib.php');

$selected = $_REQUEST['selected'];
$sourceid = $_REQUEST['sourceid'];
$frameid = $_REQUEST['pageid'];
$owner = stripslashes($_REQUEST['owner']);
$seltab = 0;
if(substr($selected,0,4) == 'http'){
	$url = $selected;
	$seltab = 1;
}

if(substr($selected,0,7) == 'mailto:'){
	$email = substr($selected,7);
	$test = preg_match('!([^\?]+)\??(.*)!',$email,$matches);
	if($test){
		$email = $matches[1];
		if(!empty($matches[2])){
			$query = $matches[2];
			$parts = explode('&',$query);
			foreach($parts as $part){
				list($key,$value) = explode('=',$part,2);
				if(strtolower($key) == 'subject') $emailsubject = urldecode($value);
				if(strtolower($key) == 'body') $emailbody = urldecode($value);
			}
		}
	}
	$seltab = 3;
}

function drawLinkSelector(){	
	global $selected,$sourceid,$owner;
	$root = getLinkSelectorMenu($selected,$sourceid,false);
	
	// Build Tree
	$mtree = new TreeView($root,'ajgls');
	$mtree->selectedid = $selectedid;
	$mtree->showroot = false;
	$mtree->att_name = 'title';
	$mtree->att_id = 'id';
	
	$res .= $mtree->drawTree();
	return $res;
}

function getLinkSelectorMenu(){
	global $selected,$sourceid,$owner;
	$sql = "select * from pages where parent is null and published > 0 order by position asc";
	$root = array();
	$root['menuname'] = 'Menu System';
	$root['id'] = 0;
	$root['icon'] = 'tree_menu.gif';
	$root['children'] = $GLOBALS['db']->select($sql);
	foreach($root['children'] as &$item){
		if(empty($item['menuname'])) $item['menuname'] = '[no name]';
		getLinkSelectorMenuRecursive($item);
		$item['icon'] = (count($item['children'])?'tree_folder.gif':'tree_page.gif');
		$link = '/'.trim(getHTMPath(null,'index.php','id',$item['id']),'./');
		$item['link'] = "javascript:parent.setElementValue('$sourceid','$link','$owner');parent.PopupManager.hideLinkSelector();";
	}
	return $root;	
}

function getLinkSelectorMenuRecursive(&$item){
	global $selected,$sourceid,$owner;
	require_once('../lib/HTMPaths.lib.php');
	$sql = "select * from pages where parent = " . $item['id'] . " order by position asc";
	$item['children'] = $GLOBALS['db']->select($sql);
	foreach($item['children'] as &$sub){
		getLinkSelectorMenuRecursive($sub);	
		$sub['icon'] = (count($sub['children'])?'tree_folder.gif':'tree_page.gif');
		$link = '/'.trim(getHTMPath(null,'index.php','id',$sub['id']),'./');
		$sub['link'] = "javascript:parent.setElementValue('$sourceid','$link','$owner');parent.PopupManager.hideLinkSelector();";
	}	
}

function drawModuleSelectors(){
	global $selected,$sourceid,$owner,$seltab;
	$id = 4;
	$tabs = array();
	$data = array('selected'=>$selected,'sourceid'=>$sourceid,'owner'=>$owner,'tabs'=>&$tabs);
	fireEvent('linkSelectorLoading',$data);
	if(is_array($tabs)){
		foreach($tabs as $tab){
			$html = $tab['html'];
			$description = $tab['description'];
			$res .= '<div class="accbutton'.($seltab==$id?'selected':'').'" id="tab'.$id.'" onclick="openAccordion('.$id.');">'.$description.'</div>';
			$res .= '<div class="acctab'.($seltab==$id?'selected':'').'" id="tbp'.$id.'">'.$html.'</div>';
			$id++;
		}	
	}
	return $res;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="StyleSheet" href="../css/admin.css" type="text/css" />
		<link rel="StyleSheet" href="../css/linkselector.css" type="text/css" />
		<script language="javascript" src="../js/Common.js"></script>
		<script language="javascript" src="../js/accordion.js"></script>
		<script language="javascript" src="../js/linkselector.js"></script>
		<script language="javascript" src="../js/aim.ajax.js"></script>
		<script language="javascript" src="../js/base64.js"></script>
		<?=$agent->init();?>
		<script language="javascript">
			function openResources(){
				parent.PopupManager.showDocSelector('<?=$selected?>','<?=$sourceid?>','<?=$owner?>');
				parent.PopupManager.hideLinkSelector();
			}
		</script>
	</head>
	<body>
		<input type="hidden" id="selected" name="selected" value="<?=$selected?>" />
		<input type="hidden" id="sourceid" name="sourceid" value="<?=$sourceid?>" />
		<input type="hidden" id="owner" name="owner" value="<?=$owner?>" />
		<div style="width:500px; padding:0px;">
		
			<div class="accbutton<?=$seltab==0?'selected':''?>" id="tab0" onclick="openAccordion(0);">Link to a page in this Site</div>
			<div class="acctab<?=$seltab==0?'selected':''?>" id="tbp0"><?=drawLinkSelector();?></div>

			<div class="accbutton<?=$seltab==1?'selected':''?>" id="tab1" onclick="openAccordion(1);">Link to a page an another website</div>
			<div class="acctab<?=$seltab==1?'selected':''?>" id="tbp1">
				<div class="ls_formblock">
					<div>Enter the URL of the webpage you'd like to link to:</div>
					<textarea name="url" id="url" style="width: 488px; height: 50px" /><?=$url?></textarea><br />
				</div>
				<div><a class="greenbutton" href="javascript:linkselector_submiturl();" />Insert Link</a></div>
			</div>

			<div class="accbutton<?=$seltab==2?'selected':''?>" id="tab2" onclick="openAccordion(2);">Send an Email</div>						
			<div class="acctab<?=$seltab==2?'selected':''?>" id="tbp2">
				<div class="ls_formblock">
					<div>Email Address:</div>
					<input type="text" name="email" id="email" value="<?=$email?>" style="width: 488px;" /><br /><br />
					<div>Subject:</div>
					<input type="text" name="subject" id="subject" value="<?=$emailsubject?>" style="width: 488px;" /><br /><br />
					<div>Body Message:</div>
					<textarea name="body" id="body" style="width: 488px; height: 96px" /><?=$emailbody?></textarea><br />
				</div>
				<div><a class="greenbutton" href="javascript:linkselector_submitemail();" />Insert Email Address</a></div>
			</div>

			<div class="accbutton" id="tab3" onclick="openAccordion(3);">Link to an Image, PDF or media file in Resources</div>
			<div class="acctab" id="tbp3" style="text-align: center;">
				<a type="button" name="resources" class="greenbutton" style="font-size: 16px; margin-top: 20px; padding: 20px 30px;" href="javascript:openResources();">Open Resource Selector</a>
				<br /><br />
				<div>Open the resource selector to select a document from the resources section. You can upload a resource to use, if you need to do so.</div>
			</div>
			
			<?=drawModuleSelectors();?>
		</div>
	</body>
</html>