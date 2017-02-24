<?php
/**
 * The Lists AJAX library of AJAX functions for the Lists module
 * 
 * @package AJAX
 * @subpackage Lists
 * @since 2.0
 */
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('../lib/Global.lib.php'); 
$usermanager->authenticateUserForAjax(RIGHT_EDITLISTS);
require_once('../lib/Agent.lib.php'); 
$agent->init();	


/**
 * Returns the details of a single entry
 *
 * @param int $id The database ID of the list entry
 * @return array The details of the entry
 *
 */
function AJ_GetEntryDetails($id){
	try{
		$listmanager = ListManager::getListManager();
		$entry = $listmanager->getListItem($id);
		return $entry;
	}catch(Exception $e){
		return $e->getMessage();
	}
}

/**
 * Move a list entry one place up within the list
 *
 * @param int $id The ID of the entry
 * @return string "success" or an error message
 *
 */
function AJ_moveListEntryUp($id){
	try{
		$listmanager = ListManager::getListManager();
		$res = $listmanager->moveListEntryUp($id,$error);
		if(!$res) return $error;
		return 'success';
	}catch(Exception $e){
		return $e->getMessage();
	}
}

/**
 * Move a list entry one place down within the list
 *
 * @param int $id The ID of the entry
 * @return string "success" or an error message
 *
 */
function AJ_moveListEntryDown($id){
	try{
		$listmanager = ListManager::getListManager();
		$res = $listmanager->moveListEntryDown($id,$error);
		if(!$res) return $error;
		return 'success';
	}catch(Exception $e){
		return $e->getMessage();
	}
}

?>