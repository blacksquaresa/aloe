<?php
/**
 * The Content AJAX library of AJAX functions for the Content module
 * 
 * @package AJAX
 * @subpackage Content
 * @since 2.0
 */
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('../lib/Global.lib.php');
$usermanager->authenticateUserForAjax();
require_once('../lib/Content.lib.php');
require_once('../lib/HTMPaths.lib.php');
require_once('../lib/Agent.lib.php'); 
$agent->init();	

// {{{ Pages

/**
 * Create a new page with the supplied information
 *
 * @param int $parentid The ID of the parent page
 * @param string $title The title of the page
 * @param string $keywords The keywords for the page
 * @param string $description The description of the page
 * @param string $menuname The name of the page, to be used in a menu
 * @param string $type The type of page. May be one of "content", "link", "label" or "special"
 * @param string $forwardurl The URL for the menu link, for pages of "link" type
 * @param string $specialpage The name of the handler for "special" pages, or the link target for "link" pages
 * @param string $template The classname of the layout to use for the page's initial Layout
 * @param bool $published Whether the page should be published or not
 * @param array $custom An array containing the values of custom fields. This may include multiple values for a single setting, depending on how the Settings class defined the control.
 * @return mixed The ID of the created page, or an error message
 *
 */
function AJ_CreatePage($parentid, $title, $keywords, $description, $menuname, $type, $forwardurl, $specialpage, $template, $published, $custom){
	try{
		foreach($custom as &$field) $field = base64_decode($field);
		$res = @Page::CreatePage($parentid, $title, $keywords, $description, $menuname, $type, $forwardurl, $specialpage, $template, $published, $custom, $error);
		if(!$res) return $error;
		return $res->id;
	}catch(Exception $e){
		return $e->getMessage();
	}
}

/**
 * Update the details of an existing page
 *
 * @param int $id The ID of the page to be updated
 * @param string $title The title of the page
 * @param string $keywords The keywords for the page
 * @param string $description The description of the page
 * @param string $menuname The name of the page, to be used in a menu
 * @param string $type The type of page. May be one of "content", "link", "label" or "special"
 * @param string $forwardurl The URL for the menu link, for pages of "link" type
 * @param string $specialpage The name of the handler for "special" pages, or the link target for "link" pages
 * @param array $custom An array containing the values of custom fields. This may include multiple values for a single setting, depending on how the Settings class defined the control.
 * @return string "success" or an error message
 *
 */
function AJ_updatePage($id, $title, $keywords, $description, $menuname, $type, $forwardurl, $specialpage, $custom){
	try{
		$page = Page::GetNewPage($id,true);
		foreach($custom as &$field) $field = base64_decode($field);
		$res = $page->update($title, $keywords, $description, $menuname, $type, $forwardurl, $specialpage, $custom, $error);
		if(!$res) return $error;
		return 'success';
	}catch(Exception $e){
		return $e->getMessage();
	}
}

/**
 * Fetch the details of an existing page
 *
 * @param int $id The ID of the page
 * @return Page The Page object
 *
 */
function AJ_getPage($id){
	try{
		$page = Page::GetNewPage($id,true);
		@$page->PopulateCustom();
		@$page->PopulateContent();
		@$page->PopulateFriendlyUrl();
		return $page;
	}catch(Exception $e){
		return $e->getMessage();
	}
}

/**
 * Fetch an array of the IDs of all content blocks in a particular Layout, on a given page
 *
 * @param int $pageid The ID of the page
 * @param int $layoutid The ID of the layout
 * @return array An array of the IDs of all the Content Blocks in the Layout
 *
 */
function AJ_getPageContentIDs($pageid,$layoutid){
	try{
		$page = Page::GetNewPage($pageid,true);
		$page->PopulateLayouts();
		return $page->layouts[$layoutid]->getColumnBlockIds();
	}catch(Exception $e){
		return $e->getMessage();
	}
}

/**
 * Delete a given page
 *
 * @param int $id The ID of the page to be deleted
 * @return string "success" or an error message
 *
 */
function AJ_deletePage($id){
	try{			
		$page = Page::GetNewPage($id,true);	
		$res = @$page->DeletePage($error);
		if($res == false) return $error;
		return 'success';
	}catch(Exception $e){
		return $e->getMessage();
	}
}

/**
 * Order all child pages of the given page alphabetically, by Title
 *
 * @param int $id The ID of the parent Page
 * @return array An array containing the IDs of all the child pages, in the new order
 *
 */
function AJ_OrderPagesAlphabetically($id){
	try{			
		$page = Page::GetNewPage($id,true);	
		$res = @$page->OrderPagesAlphabetically($error);
		if($res == false) return $error;
		return $res;
	}catch(Exception $e){
		return $e->getMessage();
	}
}

/**
 * Publish a page
 *
 * @param int $pageid The ID of the Page to be published
 * @return string "success" or an error message
 *
 */
function AJ_PublishPage($pageid){
	try{
		$page = Page::GetNewPage($pageid,true);
		$res = @$page->PublishPage($error);
		if(!$res) return $error;
		return 'success';
	}catch(Exception $e){
		return $e->getMessage();
	}
}

/**
 * Unpublish a given page
 *
 * @param int $pageid The ID of the Page to be hidden
 * @return string "success" or an error message
 *
 */
function AJ_HidePage($pageid){
	try{
		$page = Page::GetNewPage($pageid,true);
		$res = @$page->UnPublishPage($error);
		if(!$res) return $error;
		return 'success';
	}catch(Exception $e){
		return $e->getMessage();
	}
}

/**
 * Move a page up one position within it's parent
 *
 * @param int $id The ID of the Page to move
 * @return string "success" or an error message
 *
 */
function AJ_MovePageUp($id){
	try{
		$page = Page::GetNewPage($id,true);
		$res = @$page->movePageUp($error);
		if(!$res) return $error;
		return 'success';
	}catch(Exception $e){
		return $e->getMessage();
	}
}

/**
 * Move a page down one position within it's parent
 *
 * @param int $id The ID of the Page to move
 * @return string "success" or an error message
 *
 */
function AJ_MovePageDown($id){
	try{
		$page = Page::GetNewPage($id,true);
		$res = @$page->movePageDown($error);
		if(!$res) return $error;
		return 'success';
	}catch(Exception $e){
		return $e->getMessage();
	}
}

/**
 * Move a page to a new position in the menu tree. Usually used with drag-and-drop
 *
 * @param int $id The ID of the page to be moved
 * @param int $target The ID of the new parent page
 * @param int $position The position the page should take up within the children of the target
 * @return string "success" or an error message
 *
 */
function AJ_movePageTo($id,$target,$position){
	$target = preg_replace('/:/', '', $target);
	try{
		$page = Page::GetNewPage($id,true);
		$res = @$page->movePageTo($target,$position, $error);
		if(!$res) return $error;
		return 'success';
	}catch(Exception $e){
		return $e->getMessage();
	}
}
#endregion

// {{{ Layouts

/**
 * Create a new layout on a given page
 *
 * @param int $pageid The ID of the page
 * @param int $position The intended position of the new layout
 * @param string $classname The classname of the new Layout
 * @param array $custom An array containing the values of custom fields. This may include multiple values for a single setting, depending on how the Settings class defined the control.
 * @return Layout The new Layout object
 *
 */
function AJ_CreateLayout($pageid,$position,$classname,$custom){
	try{
		$page = Page::GetNewPage($pageid,true);
		foreach($custom as &$field) $field = base64_decode($field);
		$res = @$page->createLayout($classname, $position, $custom, $error);
		if(!$res) return $error;
		$page->PopulateLayouts();
		$layout = $page->layouts[$res];
		$layout->content = $layout->getContent();
		return $layout;
	}catch(Exception $e){
		return $e->getMessage();
	}
}

/**
 * Update an existing Layout
 *
 * @param int $pageid The ID of the page in which to find the Layout
 * @param int $layoutid The ID of the Layout
 * @param string $classname The name of the new class for this Layout
 * @param array $custom An array containing the values of custom fields. This may include multiple values for a single setting, depending on how the Settings class defined the control.
 * @return Layout The new Layout object
 *
 */
function AJ_UpdateLayout($pageid,$layoutid,$classname,$custom){
	try{
		$page = Page::GetNewPage($pageid,true);
		foreach($custom as &$field) $field = base64_decode($field);
		$res = @$page->updateLayout($layoutid, $classname, $custom, $error);
		if(!$res) return $error;
		$page->PopulateLayouts();
		$layout = $page->layouts[$res];
		$layout->content = $layout->getContent();
		return $layout;
	}catch(Exception $e){
		return $e->getMessage();
	}
}

/**
 * Delete an existing Layout
 *
 * @param int $pageid The ID of the page containing the Layout
 * @param int $layoutid The ID of the Layout
 * @return string "success" or an error message
 *
 */
function AJ_DeleteLayout($pageid,$layoutid){
	try{
		$page = Page::GetNewPage($pageid,true);
		$res = @$page->deleteLayout($layoutid, $error);
		if(!$res) return $error;
		return 'success';		
	}catch(Exception $e){
		return $e->getMessage();
	}
}

/**
 * Move the Layout to a new position within the page. Usually used with Drag-and-Drop
 *
 * @param int $pageid The ID of the page
 * @param int $id The ID of the Layout to be moved
 * @param int $pos The desired position for the Layout within the page
 * @return string "success" or an error message
 *
 */
function AJ_MoveLayoutTo($pageid, $id, $pos){
	try{
		$page = Page::GetNewPage($pageid,true);
		$page->PopulateLayouts();
		$res = $page->layouts[$id]->moveTo($pos, $error);
		if(!$res) return $error;
		return 'success';
	}catch(Exception $e){
		return $e->getMessage();
	}
}
#endregion

// {{{ Content Blocks

/**
 * Delete a Content Block
 *
 * @param int $pageid The ID of the Page containing the Content Block
 * @param int $blockid The ID of the Content Block to be deleted
 * @return string "success" or an error message
 *
 */
function AJ_Delete_Block($pageid, $blockid){
	try{
		$page = Page::GetNewPage($pageid,true);
		$res = @$page->deleteContentBlock($blockid, $error);
		if(!$res) return $error;
		return 'success';		
	}catch(Exception $e){
		return $e->getMessage();
	}
}

/**
 * Copy the Content Block that is currently on the Clipboard into a new location. Called when a Block is pasted.
 *
 * @param int $pageid The ID of the target Page
 * @param int $layoutid The ID of the target Layout
 * @param int $col The ID of the target Column
 * @return int The ID of the newly created Content Block
 *
 */
function AJ_CopyContentblock($pageid, $layoutid, $col){
	try{
		if(!empty($_SESSION['ContentBlockClipboard'])){
			$page = Page::GetNewPage($pageid,true);
			$res = @$page->copyContentBlock($layoutid, $col, $_SESSION['ContentBlockClipboard']['id'], $error);
			if(!$res) return $error;
			return $res;
		}else{
			return 'The clipboard is empty';
		}
	}catch(Exception $e){
		return $e->getMessage();
	}
}

/**
 * Move a Content Block to a new location within the Page
 *
 * @param int $id The ID of the Content Block
 * @param int $layout The ID of the target Layout
 * @param int $column The ID of the target Column
 * @param int $pos The desired position with the Column
 * @return string "success" or an error message
 *
 */
function AJ_MoveBlockTo($id, $layout, $column, $pos){
	try{
		$block = ContentModule::getContentBlock($id,true);
		$res = @$block->moveTo($layout, $column, $pos, $error);
		if(!$res) return $error;
		return 'success';
	}catch(Exception $e){
		return $e->getMessage();
	}
}

/**
 * Adds the Content Block to the Clipboard. This saves the information in the session, so that Content Block may be copied to any other Page
 * 
 * Only one Content Block can be on the Clipboard at any one time. Adding a new Content Block replaces the old.
 *
 * @param int $id The ID of the Content Block to be added to the Clipboard
 * @param int $width The width of the Column which contains the current block. This is used to help determine which Columns might be suitable paste targets
 * @return string "success" or an error message
 *
 */
function AJ_AddContentBlockToClipboard($id, $width){
	try{
		$_SESSION['ContentBlockClipboard'] = array('id'=>$id,'width'=>$width);
		return 'success';
	}catch(Exception $e){
		return $e->getMessage();
	}
}

/**
 * Fetch an array of the IDs of all Columns into which the Content Block on the Clipboard may be pasted. 
 *
 * @return int[] An array of the IDs of all Columns into which the Content Block on the Clipboard may be pasted
 *
 */
function AJ_getValidPasteColumnIDs(){
	try{
		$res = array();
		if(!empty($_SESSION['ContentBlockClipboard'])){
			$block = ContentModule::getContentBlock($_SESSION['ContentBlockClipboard']['id'],true);
			$res = $GLOBALS['skin']->getValidColumnsForEditor($block->modulename);
		}
		return $res;
	}catch(Exception $e){
		return $e->getMessage();
	}
}

/**
 * Reset the URL of the current Page
 *
 * @param int $id The ID of the Page
 * @param string $pathstub The new pathstub. This will be used with the pathstubs of the parent Pages to build the new path.
 * @return array An array containing the pathstub (which may have been altered) and the resulting friendly URL
 *
 */
function AJ_resetPageURL($id,$pathstub){
	try{
		$page = Page::GetNewPage($id);
		$res = @$page->resetURL($pathstub,$error);
		if(!$res) return $error;
		return array($page->pathstub,$page->friendlyurl);
	}catch(Exception $e){
		return $e->getMessage();
	}
}
#endregion


?>