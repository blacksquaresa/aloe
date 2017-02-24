<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';

require_once('../lib/Global.lib.php');
$usermanager->authenticateUser(RIGHT_EDITLISTS);
$listmanager = ListManager::getListManager();
require_once('../lib/Agent.lib.php');
$message = '';
$lists = $listmanager->getLists();
if(empty($lists)){
	include('templatetop.php');
	echo '<div class="error">Sorry, there are currently no lists available in the system</div>';
	include('templatebottom.php');
	exit;
}
$list = null;
foreach($lists as $check){
	if($check['code'] == $_REQUEST['type']){
		$list = $listmanager->getList($check['code']);
		break;
	}
}
if(empty($list)) $list = $listmanager->getList($lists[0]['code']);
$entry = $list['items'][$_REQUEST['entryid']];
if(empty($entry)) $entry = reset($list['items']);
$type = $list['code'];
$fieldpadding = 12;
$fieldwidth = $list['width'];
foreach($list['fields'] as $field){
	if($field['type']=='tinymce'){
		$fieldwidth+=30;
		$hastinymce = true;
		break;	
	}	
}

if(!empty($_REQUEST['action'])){
	switch($_REQUEST['action']){
		case 'add':
			if(empty($_REQUEST['add_name'])) $message .= " - Please provide name for this entry.<br>";
			cleanUserInput(array('add_name','add_data'));
			$res = $listmanager->createListEntry($list['id'],$_REQUEST['add_name'],$_REQUEST['add_data'],$error);		
			if($res){
				header('Location: editlists.php?type='.$type.'&entryid='.$res.'&success=1');
				exit;
			}else{
				$message = 'There was an error updating the selected entry.<br />' . $error;
			}
			break;
		case 'update':
			if(empty($_REQUEST['upd_name'])) $message .= " - Please provide name for this entry.<br>";
			cleanUserInput(array('upd_name','upd_data'));
			$res = $listmanager->updateListEntry($entry['id'],$_REQUEST['upd_name'],$_REQUEST['upd_data'],$error);		
			if($res){
				header('Location: editlists.php?type='.$type.'&entryid='.$entry['id'].'&success=1');
				exit;
			}else{
				$message = 'There was an error updating the selected entry.<br />' . $error;
			}
			break;
		case 'del':
			$res = $listmanager->deleteListEntry($_REQUEST['entryid'],$error);		
			if($res){
				header('Location: editlists.php?type='.$type.'&success=1');
				exit;
			}else{
				$message = 'There was an error deleting the selected entry.<br />' . $error;
			}
			break;
	}
}

function drawTabs(){
	global $lists,$type;
	$res .= '<table id="optionTabs" class="tabtable" cellpadding="0" cellspacing="0"><tr>';
	foreach($lists as $ind=>$list){
		$res .= '<td class="tab'.($list['code']==$type?'selected':'').'"><a href="editlists.php?type='.$list['code'].'">'.$list['name'].'</a></td>
				<td class="tabseparator">&nbsp;</td>';		
	}
	$res .= '</tr></table>';
	return $res;	
}

function drawList($listid,$selected){
	global $list,$listmanager;
	if(empty($list)) $list = $listmanager->getList($listid);
	$maxpos = $listmanager->getMaxListPosition($list['id']);
	$ind= 1;
	foreach($list['items'] as $cat){
		$class = ($selected==$cat['id'])?'list_entryselected':'list_entry';
		$res .= '<div id="entry_'.$cat['id'].'" class="'.$class.'">';
		$res .='<table cellpadding="0" cellspacing="0" border="0" width="100%"><tr>';
		$res .= '<td width="20" align="center" class="imagecontainer" style="padding-left: 7px;"><a href="javascript:deleteEntry('.$cat['id'].');"><img src="/../images/admin/common/delete.png" /></a></td>';
		if($cat['position']==1){
			$res .= '<td width="20" align="center"><img src="/../images/spacer.gif" width="20" height="20" /></td>';
		}else{
			$res .= '<td width="20" align="center" class="imagecontainer"><a href="javascript:moveEntry('.$cat['id'].',-1)"><img src="/../images/admin/common/up.png" /></a></td>';
		}
		if($cat['position']==$maxpos){
			$res .= '<td width="20" align="center"><img src="/../images/spacer.gif" width="20" height="20" /></td>';
		}else{
			$res .= '<td width="20" align="center" class="imagecontainer"><a href="javascript:moveEntry('.$cat['id'].',1)"><img src="/../images/admin/common/down.png" /></a></td>';
		}
		$res .= '<td ><a class="listentry_text" href="javascript:openEntry(' . $cat['id'] . ')">' . $cat['name'] . '</a></td>';
		$res .= '</tr></table></div>';
		$ind++;
	}
	return $res;
}

function drawEntry($selected){
	global $list,$entry,$fieldwidth,$listmanager;
	if(empty($entry)) $entry = $listmanager->getListItem($selected);
	if(empty($list)) $list = $listmanager->getList($entry['listid']);	
	$res .= '<div class="entry_label">'.ucwords($list['itemname']).' Name:</div>';
	$res .= '<div class="entry_field"><input type="text" name="upd_name" style="width:'.$fieldwidth.'px;" id="upd_name" value="'.$entry['name'].'"></div>';
	foreach($list['fields'] as $field){
		$res .= drawField($field,$entry['values'][$field['id']]['value'],'upd_');
	}
	$res .= '<div align="right"><input type="submit" class="greenbutton updatebutton" value="Update '.ucwords($list['itemname']).'" /></div>';
	return $res;
}

function drawPopup(){
	global $list,$fieldwidth;	
	$res .= '<div class="entry_label">'.ucwords($list['itemname']).' Name:</div>';
	$res .= '<div class="entry_field"><input type="text" name="add_name" style="width:'.$fieldwidth.'px;" id="add_name" value=""></div>';
	foreach($list['fields'] as $field){
		$res .= drawField($field,null,'add_');
	}
	$res .= '<div align="right"><input type="submit" class="greenbutton addbutton" value="Create New '.ucwords($list['itemname']).'" /></div>';
	return $res;
}

function drawField($field,$value,$prefix){
	global $list,$fieldpadding,$fieldwidth;	
	$res .= '<div class="entry_label">'.$field['label'].':</div>';
	switch($field['type']){
		case 'select':
			$res .= '<div class="entry_field"><select name="'.$prefix.'data['.$field['id'].']" id="'.$prefix.$field['name'].'" style="width: '.($fieldwidth+$fieldpadding).'px;">';
			$options = explode('|',$field['data']);
			foreach($options as $option){
				$res .= '<option value="'.$option.'">'.$option.'</option>';	
			}
			$res .= '</select></div>';
			break;
		case 'resource':	
			$res .= '<div class="entry_field"><input type="text" name="'.$prefix.'data['.$field['id'].']" style="width: '.($fieldwidth-20).'px;" id="'.$prefix.$field['name'].'" value="'.$value.'"><a href="javascript:PopupManager.showResourceManager(null,\''.$prefix.$field['name'].'\');"><img src="../images/admin/common/select.png" align="top" /></a></div>';
			break;
		case 'link':	
			$res .= '<div class="entry_field"><input type="text" name="'.$prefix.'data['.$field['id'].']" style="width: '.($fieldwidth-20).'px;" id="'.$prefix.$field['name'].'" value="'.$value.'"><a href="javascript:PopupManager.showLinkSelector(null,\''.$prefix.$field['name'].'\');"><img src="../images/admin/common/select.png" align="top" /></a></div>';
			break;
		case 'tinymce':	
			$res .= '<div class="entry_field" style="min-height: 313px;"><textarea name="'.$prefix.'data['.$field['id'].']" class="mceEditor" style="width:'.($list['width']+42).'px;height: 300px;padding: 0px;border: 0px;" id="'.$prefix.$field['name'].'">'.$value.'</textarea></div>';
			break;
		case 'text':
			switch($field['data']){
				case 'multiple':
					$res .= '<div class="entry_field"><textarea name="'.$prefix.'data['.$field['id'].']" style="height: 42px;width:'.$fieldwidth.'px;" id="'.$prefix.$field['name'].'">'.$value.'</textarea></div>';
					break;
				default:
					$res .= '<div class="entry_field"><input type="text" name="'.$prefix.'data['.$field['id'].']" style="width:'.$fieldwidth.'px;" id="'.$prefix.$field['name'].'" value="'.$value.'"></div>';
					break;
			}
			break;
		default:	
			$res .= '<div class="entry_field"><input type="text" name="'.$prefix.'_data['.$field['id'].']" style="width:'.$fieldwidth.'px;" id="'.$prefix.$field['name'].'" value="'.$value.'"></div>';
			break;
	}
	return $res;
}

$pagetitle = 'Edit ' . $list['name'] . ' ';
$cmslinkpageid='Lists';
include('templatetop.php');
if($hastinymce){
	$tiny = new TinyMCE();
	$tiny->Init('standard',$list['width']);
}
?>
<script language=javascript>
	<!--	
	// Page constants
	var list = <?=json_encode($list)?>;
	var currententry = <?=json_encode($entry)?>;
	var type = '<?=$type?>';
	PopupManager.showLoading();
	attachEventHandler(window,'load',function(){
	PopupManager.hideLoading();
	<? if($message!=''){?>PopupManager.showError('<?= str_replace("'","&apos;",$message) ?>');<?}?>
	<? if(!empty($success)||!empty($_REQUEST['success'])){?>PopupManager.showCompleted();<?}?>
	});
	//-->
</script>
<link rel="StyleSheet" href="../css/editors.css" type="text/css" />
<link rel="StyleSheet" href="../css/listmanager.css" type="text/css" />
<script language=javascript src="../js/ListManager.js"></script>

<?=drawTabs();?>

<table cellpadding="0" cellspacing="0" style="margin: 20px 0 20px 0;">
	<tr>
		<td valign="top" class="listcontainer">
			<a href="javascript:openCreateEntry();" class="greenbutton addbutton">Add New <?=ucwords($list['itemname'])?></a>
			<br />
			<div id="entry_list"><?= drawList($list['id'],$entry['id']); ?></div>
		</td>
		<td valign="top" style="padding-left: 30px;">
			<div class="edt_heading2" style="margin: 10px 0 20px;"><?=ucwords($list['itemname'])?> details</div>
			<form action="editlists.php" method="post" onsubmit="return updateEntry();">
				<input type="hidden" name="type" value="<?=$type?>" />
				<input type="hidden" name="action" value="update" />
				<input type="hidden" id="upd_entryid" name="entryid" value="<?=$entry['id']?>" />
				<?=drawEntry($entry['id']);?>	
			</form>
		</td>
	</tr>
</table>

<!-- Popups -->

<!-- Create New Category Wizard -->
<div id="create_entry" class="content_popupcontent" style="width: <?=($fieldwidth+$fieldpadding)?>px;display: none;">
	<form action="editlists.php" method="post" onsubmit="return createEntry();">
	<input type="hidden" name="type" value="<?=$type?>" />
	<input type="hidden" name="action" value="add" />
	<?=drawPopup();?>
	</form>
</div>
<? include('templatebottom.php'); ?>