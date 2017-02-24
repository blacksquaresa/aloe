<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('../lib/Global.lib.php');
$usermanager->authenticateUser(RIGHT_EDITCONTENT);
require_once('../lib/Content.lib.php');
require_once('../lib/HTMPaths.lib.php');

// Sets the current state of the search for this page. The state persists across the session.
// PageState::ClearPageState('contentstate');
$state = PageState::SetPageState('contentstate');
function BuildPageState(){
	return new PageState('contentstate');
}
$pageid = empty($state->values['currentpage'])?PAGE_HOME:$state->values['currentpage'];

// Refresh the Global CSS cache, to ensure all content blocks are displayed with the latest CSS
unset($_SESSION['globalcss']);

// Load Content Block configurations
$editors = $GLOBALS['skin']->getEditorNames();
foreach($editors as $classname){
	include($GLOBALS['settings']->contentpath . $classname . '/'  . $classname . '.config.php');
}

cleanUserInput(array('title','keywords','description','menutitle','showinmenu'));
$code = '?'.createRandomCode(8,8);
$inputcontrol = new InputControl(460);

function drawTree($id){
	try{
		$tree = new ContentTreeView(true);
		$tree->selectedid = $id;
		$res = $tree->drawTree();
		return  $res;
	}catch(exception $e){
		return $e->getMessage();	
	}
}

function drawColumnOptions(){
	global $ContentModules,$skin;
	foreach($skin->columns as $column){
		$i = $column->id;
		$classes = $column->contentblocks;
		$groups = array();
		foreach($classes as $class) $groups[$ContentModules[$class]['group']][] = $class;
		$ind = 1;
		$coldata = '';
		foreach($groups as $group=>$classes){
			$style = empty($coldata)?' style="border: 0px; padding-left: 0px;"':(count($groups)==$ind?' style="padding-right: 0px;"':'');
			$coldata .= '<td class="ccl_selectorcolumn" valign="top"'.$style.'>';
			switch($group){
				case 'list':
					$coldata .= '<div class="ccl_selectorgroup"><img src="../images/admin/content/blocks/lists.png" align="absmiddle" /> Lists</div>';
					break;	
				case 'gallery':
					$coldata .= '<div class="ccl_selectorgroup"><img src="../images/admin/content/blocks/galleries.png" align="absmiddle" /> Galleries</div>';
					break;	
				case 'feature':
					$coldata .= '<div class="ccl_selectorgroup"><img src="../images/admin/content/blocks/features.png" align="absmiddle" /> Features</div>';
					break;	
				default:
					$coldata .= '<div class="ccl_selectorgroup"><img src="../images/admin/content/blocks/blocks.png" align="absmiddle" /> Basic Blocks</div>';
					break;	
			}
			foreach($classes as $classname){
				$coldata .= '
						<div class="ccl_selectorblock" onclick="contentBlockManager.layouts[document.getElementById(\'layoutid_'.$i.'\').value].columns[' . $i . '].CreateBlock(\'' . $classname . '\');">
						<div class="ccl_selectorheading">'.$ContentModules[$classname]['name'].'</div>
						<div class="ccl_selectortext">'.$ContentModules[$classname]['description'].'</div>
						</div>';	
			}
			$coldata .= '</td>';
			$ind ++;
		}
		$res .= '
				<!-- Create Content Block -->
				<div id="createblock_' . $i . '" style="display: none;">
				<input type="hidden" name="createblock_' . $i . '_after" id="createblock_' . $i . '_after" value="" />
				<input type="hidden" name="layoutid_' . $i . '" id="layoutid_' . $i . '" value="" />
				<table class="ccl_selectortable"><tr>' . $coldata . '</tr></table>
				</div>';
	}
	return $res;
}

function drawLayoutList($id){
	global $pageid;
	$templates = Layout::getLayoutList($pageid);
	$res .= '<input type="hidden" name="'.$id.'" id="'.$id.'" value="OneColumn" />';
	$res .= '<div class="template_container '.$id.'_container" id="'.$id.'_container">';
	foreach($templates as $temp){
		$class = $temp->name == $templates[0]->name?'template_selected':'template_block';
		$res .= '<div class="'.$class.'" id="'.$id.'_'.$temp->classname.'">';
		$res .= '<a href="javascript:selectTemplate(\''.$id.'\',\''.$temp->classname.'\');" title="'.$temp->name.'">';
		$res .= '<img src="'.$temp->getIconPath().'" width="100" height="50" align="top" />';
		$res .= '</a></div>';
	}
	$res .= '</div>';
	return $res;
}

function drawContentBlockCSS(){
	global $editors;
	foreach($editors as $module){
		if(file_exists($GLOBALS['settings']->contentpath.$module.'/'.$module.'.css')){
			$res .= '<link href="'.$GLOBALS['settings']->contentpathweb.$module.'/'.$module.'.css" rel="stylesheet" type="text/css" />'."\r\n";	
		}
		if(file_exists($GLOBALS['settings']->contentpath.$module.'/'.$module.'.admin.css')){
			$res .= '<link href="'.$GLOBALS['settings']->contentpathweb.$module.'/'.$module.'.admin.css" rel="stylesheet" type="text/css" />'."\r\n";	
		}
	}
	return $res;
}

function drawCustomSettings($id='cpd_custom'){
	global $inputcontrol;
	$settings = Page::getCustomSettings();
	if(is_array($settings) && count($settings)){
		$res .= '<div class="label_head">Custom Settings</div>';
		$res .= '<div class="'.$id.'_container" id="'.$id.'_container">';
		foreach($settings as $setting){
			$res .= '<div class="label_left">'.$setting['label'].':</div>';	
			$res .= '<div class="field">';
			$res .= $inputcontrol->drawStandardControl($id,$setting['name'],$setting['type'],$setting['data'],$setting['default']);
			$res .= '</div>';
		}
			$res .= '</div>';
	}
	return $res;
}

function drawLayoutSettings(){
	global $inputcontrol;
	$settings = Layout::getCustomSettings();
	if(is_array($settings) && count($settings)){
		$res .= '<div class="layout_custom_container" id="layout_custom_container">';
		foreach($settings as $setting){
			$res .= '<div class="label_left">'.$setting['label'].':</div>';	
			$res .= '<div class="field">';
			$res .= $inputcontrol->drawStandardControl('layout_custom',$setting['name'],$setting['type'],$setting['data'],$setting['default']);
			$res .= '</div>';
		}
			$res .= '</div>';
	}
	return $res;
}

/*=====================================
  Include headers, AJAX and Editor code
=======================================*/
$cmslinkpageid = 'Content';
$pagetitle = 'Edit Content';
include('templatetop.php');
?>
<link rel="StyleSheet" href="../css/contenteditor.css" type="text/css" />
<link rel="StyleSheet" href="../css/contentcolumns.css.php" type="text/css" />
<?= drawContentBlockCSS(); ?>
<script language=javascript src="../js/ContentPages.js"></script>
<script language=javascript src="../js/ContentBlocks.js"></script>
<script language=javascript src="../js/tab.js"></script>
<script language=javascript src="../js/base64.js"></script>
<script language=javascript>
	<!--
	// Page constants
	var pageid = '<?=$pageid?>';
	var pagename = '';
	var coverheightpadding = 2;
	var coverwidthpadding = 2;
	PopupManager.showLoading();
	//-->
</script>
<? if($message!=''){?><div class="error"><?= $message ?></div><?}?>

<? if($notice!=''){?><div class="error"><?= $notice ?></div><?}?>

<table width="100%" cellpadding="0" cellspacing="0" border="0" id="content_container">
	<tr>
		<td valign="top" style="width: 240px;">		
			<div class="menusystem_title">Menu System</div>					
		</td>
		<td valign="bottom" width="1000">
			<table id="optionTabs" class="tabtable" width="100%" height="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td id="tab0" class="tab"><a href="javascript:showTab(0);">Page Details</a></td>
					<td class="tabseparator">&nbsp;</td>
					<td id="tab1" class="tabselected" nowrap><nobr><a href="javascript:showTab(1);">Content</a></nobr></td>
					<td class="tabseparator">&nbsp;</td>
					<td style="width: 100%;"><input type="checkbox" name="cpd_published" id="cpd_published" onclick="togglePublishPage();" /> <label for="cpd_published">Publish this page</label></td>	
					<td style="text-align: right;padding-left: 10px;" nowrap><a href="javascript:ClipboardPreview()" id="clipboard_previewbutton" title="view the content currently on the clipboard" style="display: none;" />Clipboard</a></td>
					<td style="text-align: right;padding-left: 10px;" nowrap class="content_dates">Created: <span id="cpd_date"></span> &nbsp; | &nbsp; Modified: <span id="cpd_updated"></span></td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<div id="treeview">
				<?= drawTree($pageid); ?>
			</div><!-- close treeview -->
		</td>
		<td valign="top" width="1000">
			<div id="tbp0" class="tabpage" style="width: 998px; padding: 20px 0;">
				<input type="hidden" name="cpd_id" id="cpd_id">
				<table cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td valign="top" style="padding: 0px 34px 0px 0px; width: 460px;">
							<div class="label_head">Page Setup</div>
							<div class="label_left">Menu Name:</div>
							<div class="field"><input type="text" style="width:448px;" name="cpd_menuname" id="cpd_menuname" /></div>
							<div id="cpd_row_typeselect">
								<div class="label_left">Type: </div>
								<div class="field">
									<select name="cpd_type" style="width:460px;" id="cpd_type" onchange="cpd_type_change(this)">
										<option value="content">Content</option>
										<option value="link">Link</option>
										<option value="label">Label</option>
									</select>
									<script language="javascript">
									function cpd_type_change(obj){
										var val = obj[obj.selectedIndex].value;
										document.getElementById('cpd_row_forward').style.display = val=='link'?'':'none';
										document.getElementById('cpd_row_special').style.display = val=='link'?'':'none';
										document.getElementById('cpd_row_friendly').style.display = val=='content'||val=='special'?'':'none';
										document.getElementById('cpd_reseturlbutton').style.visibility = (val=='link'||val=='label'?'hidden':'visible');
									}
									</script>
								</div>
							</div>
							<div id="cpd_row_typespecial" style="display: none;">
								<div class="label_left">Type: Special</div>
							</div>
							<div id="cpd_row_forward">
								<div class="label_left">Forward URL:</div>
								<div class="field"><input type="text" style="width:448px;" name="cpd_forwardurl" id="cpd_forwardurl" /></div>
							</div>
							<div id="cpd_row_special">
								<div class="label_left"><input type="checkbox" name="cpd_specialpage" id="cpd_specialpage" value="_blank" /> <label for="cpd_specialpage">Open link in new window?</label></div>
							</div>
							<div id="cpd_row_friendly">
								<div class="label_head">Meta-Data</div>
								<div class="label_left">Title:</div>
								<div class="field"><input type="text" style="width:448px;" name="cpd_title" id="cpd_title" /></div>
								<div class="label_left">Description:</div>
								<div class="field"><textarea style="width:448px;height:42px" name="cpd_description" id="cpd_description"></textarea></div>
								<div class="label_left">Keywords:</div>
								<div class="field"><input type="text" style="width:448px;" name="cpd_keywords" id="cpd_keywords" /></div>
								<div class="label_left"><a name="cpd_reseturlbutton" id="cpd_reseturlbutton" href="javascript:resetURL_Open();">Edit URL</a>Friendly URL:</div>
								<div class="field"><div id="cpd_friendlyurl"></div></div>
							</div>						
						</td>
						<td style="padding: 0px 0px 0px 34px; width: 460px;" valign="top">
							<div id="cpd_row_custom">
								<?=drawCustomSettings();?>								
							</div>
						</td>
					</tr>
					<tr>
						<td colspan="2" align="right"><a class="greenbutton updatebutton" href="javascript:updatePage_Save();">Update Details</a></td>
					</tr>
				</table>				
			</div>
			<div id="tbp1" class="tabpageselected" style="width: 998px; padding: 20px 0;">
			</div>
		</td>
		<td width="1"><img src="../images/spacer.gif" width="1" height="580" /></td>
	</tr>
</table>

<!-- Popups -->

<!-- Create Page -->
<div id="content_createpage" class="content_popupcontent" style="display: none;">
	<input type="hidden" name="crp_parent" id="crp_parent">
	
	<table cellpadding="0" cellspacing="0" border="0" width="100%">
		<tr>
			<td valign="top" style="padding: 0px 10px 0px 0px; width: 460px;">
				<div class="label_head">Page Setup</div>
				<div class="label_left">Menu Name:</div>
				<div class="field"><input type="text" style="width:448px;" name="crp_menuname" id="crp_menuname" /></div>
				<div id="crp_row_typeselect">
					<div class="label_left">Type: </div>
					<div class="field">
						<select name="crp_type" style="width:460px;" id="crp_type" onchange="crp_type_change(this)">
							<option value="content">Content</option>
							<option value="link">Link</option>
							<option value="label">Label</option>
						</select>
						<script language="javascript">
						function crp_type_change(obj){
							var val = obj[obj.selectedIndex].value;
							document.getElementById('crp_row_forward').style.display = val=='link'?'':'none';
							document.getElementById('crp_row_special').style.display = val=='link'?'':'none';
							document.getElementById('crp_row_template').style.display = val=='content'?'':'none';
						}
						</script>
					</div>
				</div>
				<div id="crp_row_forward" style="display: none;">
					<div class="label_left">Forward URL:</div>
					<div class="field"><input type="text" style="width:448px;" name="crp_forwardurl" id="crp_forwardurl" /></div>
				</div>
				<div id="crp_row_special" style="display: none;">
					<div class="label_left"><input type="checkbox" name="crp_specialpage" id="crp_specialpage" value="_blank" /> <label for="crp_specialpage">Open link in new window?</label></div>
				</div>
				<div class="label_head">Meta-Data</div>
				<div class="label_left">Title:</div>
				<div class="field"><input type="text" style="width:448px;" name="crp_title" id="crp_title" /></div>
				<div class="label_left">Description:</div>
				<div class="field"><textarea style="width:448px;height:42px" name="crp_description" id="crp_description"></textarea></div>
				<div class="label_left">Keywords:</div>
				<div class="field"><input type="text" style="width:448px;" name="crp_keywords" id="crp_keywords" /></div>
			</td>
			<td style="padding: 0px 0px 0px 10px; width: 480px;" valign="top">
				<?=drawCustomSettings('crp_custom');?>
				<div id="crp_row_template">
					<div class="field">Initial Layout Template: <br /><div class="template_window"><?= drawLayoutList('crp_template'); ?></div></div>
				</div>
				<div class="label_left"><div class="button_row"><input type="checkbox" name="crp_published" id="crp_published" /> <label for="crp_published">Publish this page</label></div></div>					
			</td>
		</tr>
		<tr>
			<td colspan="2" align="right"><a class="greenbutton addbutton" href="javascript:createPage_Save();">Create Page</a></td>
		</tr>
	</table>
</div>

<!-- Reset URL -->
<div id="content_reseturl" class="content_popupcontent" style="width: 460px;display: none;">
	<table width="460" cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td colspan="3"><div class="error">WARNING: Changing the URL path stub for this page may cause links from other sites to stop working. This should only be done for new pages, or when the title and content of the page has changed significantly.</div></td>
		</tr>
		<tr>
			<td class="label_right" style="width: 80px;">Parent Path: </td>
			<td class="field" colspan="2"><div id="ru_path"></div></td>
		</tr>
		<tr>
			<td class="label_right" >Path Stub:</td>
			<td class="field" nowrap><input type="text" class="content_textbox" name="ru_pathstub" id="ru_pathstub" value="">.htm</td>
		</tr>	
		<tr>
			<td colspan="2"><div class="button_row"><a class="greenbutton updatebutton" href="javascript:resetURL_Save();">Reset URL</a></div></td>
		</tr>
	</table>
</div>

<!-- Layout Options -->
<div id="content_layout" class="content_popupcontent" style="display: none;">
	<input type="hidden" name="layoutid" id="layoutid" value="-1" />
	<input type="hidden" name="layout_after" id="layout_after" value="-1" />
	<table cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td class="field"><?=drawLayoutSettings();?></td>
		</tr>	
		<tr>
			<td class="field"><div class="template_window"><?=drawLayoutList('layout');?></div></td>
		</tr>	
		<tr>
			<td colspan="2" align="right"><div class="button_row"><a class="greenbutton updatebutton" href="javascript:processLayoutChange();">Continue</a></div></td>
		</tr>
	</table>
</div>
<?= drawColumnOptions(); ?>

<script language="javascript">
	<!--
	onload = function(){
		openPage(<?=$pageid?>);
		PopupManager.hideLoading();
	};
	// -->
</script>

<? include('templatebottom.php') ?>