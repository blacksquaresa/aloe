<?php
/**
 * The PageState AJAX library of AJAX functions for the PageState class
 * 
 * @package AJAX
 * @subpackage PageState
 * @since 2.0
 */
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('../lib/Global.lib.php'); 
require_once('Agent.lib.php'); 
$agent->init();	


/**
 * Update the pagestate with given values
 *
 * @param string $statename The name of the pagestate
 * @param array $items An associative array of key-value pairs of new values
 * @return string "State Set" to indicate success
 *
 */
function SetStateItems($statename,$items){ 
	$state = PageState::SetPageState($statename);
	$state->SetStateItems($items);
	return 'State Set';
}


/**
 * Fetch a value from the gioven pagestate
 *
 * @param string $statename The name of the pagestate
 * @param string $itemname The key representing the desired value
 * @return mixed The value for the given key in the pagestate
 *
 */
function GetStateItem($statename,$itemname){ 
	$state = PageState::GetPageState($statename);
	$val = $state->values[$itemname];
	return $val;
}

?>