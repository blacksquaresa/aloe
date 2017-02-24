<?php
/**
 * The Content library provides methods to collect and manage the menu and pages for the Content Module
 * 
 * @package Library
 * @subpackage Content
 * @since 2.0
 */
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');
require_once('Global.lib.php');
require_once('Text.lib.php');

/**
 * Builds a tree representation of a branch of the content tree. Used primarily to build menus on the front-end of the site.
 *
 * @param mixed $parentid The ID or an array representing the top level item for the tree
 * @return array A multi-dimensional array containing all the pages. Child arrays are stored under the 'children' key.
 *
 */
function getSubMenu($parentid){
	if(!is_numeric($parentid)) $parentid = 0;
	$sql = "select * from pages where parent = $parentid and published > 0 order by position asc";
	$res = $GLOBALS['db']->select($sql);
	if($res && count($res)){
		foreach($res as &$item){
			$item['children'] = getSubMenu($item['id']);
		}
	}
	return $res;
}

/**
 * Identifies the highest existing position value for children of the provided parent page within the content tree
 *
 * @param mixed $parent The ID of the parent page, or null for new top-level pages
 * @return int The highest existing position, or 0 if there are no children
 *
 */
function getMaxPagePosition($parent){
	if(empty($parent) || !is_numeric($parent)) $sql = "select max(position) from pages where parent is null";
	else $sql = "select max(position) from pages where parent = $parent";
	$res = $GLOBALS['db']->selectsingle($sql);
	if(empty($res) || !is_numeric($res)) return 0;
	return $res;
}

/**
 * Identifies the highest existing position value for a layout within the given page
 *
 * @param mixed $pageid The ID of the parent page
 * @return int The highest existing position, or 0 if there are no current layouts
 *
 */
function getMaxLayoutPosition($pageid){
	$pageid = (int)$pageid;
	$sql = "select max(position) from layouts where pageid = $pageid";
	$res = $GLOBALS['db']->selectsingle($sql);
	if(empty($res) || !is_numeric($res)) return 0;
	return $res;
}

/**
 * Identifies the highest existing position value for content blocks within a column on a page
 *
 * @param int $pageid The ID of the page
 * @param int $columnid The ID of the column
 * @return int The highest existing position, or 0 if there are no blocks in the column
 *
 */
function getMaxBlockPosition($layoutid, $columnid){
	if(empty($layoutid) || !is_numeric($layoutid)) $layoutid = 0;
	if(empty($columnid) || !is_numeric($columnid)) $columnid = 0;
	else $sql = "select max(position) from content where layout = $layoutid and columnid = $columnid";
	$res = $GLOBALS['db']->selectsingle($sql);
	if(empty($res) || !is_numeric($res)) return 0;
	return $res;
}

/**
 * Returns a set of HTML LINK tags calling all the CSS stylesheets used in the global skin.
 * 
 * This method will cache the request, so that it can be used multiple times in a session. 
 * The cache variable will be purged every time the editcontent.php page is refreshed.
 *
 * @return string The HTML fragment containing all the LINK tags
 *
 */
function getGlobalSkinCSS(){
	if(empty($_SESSION['globalcss'])){
		$paths = $GLOBALS['skin']->getGlobalCSS();
		foreach($paths as $path){
			$path = '{webroot}' . ltrim($path,'./');
			$html .= '<link rel="stylesheet" href="'.$path.'" type="text/css" />';	
		}
		$_SESSION['globalcss'] = $html;
	}
	return str_replace('{webroot}',$GLOBALS['webroot'],$_SESSION['globalcss']);
}