<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('../lib/Global.lib.php');

header('Content-type: text/css');

foreach($GLOBALS['skin']->columns as $column){
	$res .= ".contentcolumn_{$column->id} { width : {$column->adminwidth}px; }";
}
echo $res;

?>