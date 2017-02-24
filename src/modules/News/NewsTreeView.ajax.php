<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('../../lib/Global.lib.php');
require_once('../../lib/Agent.lib.php');
$agent->init();	

function drawTreeBranch($classname,$pocket,$name,$lastuniqueid,$uniqueid,$itemid,$properties,$level,$islasts){
	$tree = new NewsTreeView($pocket[0],$pocket[1]);
	return $tree->drawTreeBranch($name,$pocket,$lastuniqueid,$itemid,$uniqueid,$properties,$level,$islasts);
}

?>