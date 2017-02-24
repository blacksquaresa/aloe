<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';

/***********************************************************************
downloadcsv.php
Called from any file that has a search function, and a corresponding 
session state, this file creates a CSV file of the search results and
returns it. Each search type must be actively programmed in the 
getCSVData function (CSV.php).
************************************************************************/

require_once('../lib/Global.lib.php');
$usermanager->authenticateUser();
require_once('../lib/CSV.lib.php');

$data = '';
$name = empty($_REQUEST['name'])?'Search_Results':$_REQUEST['name'];
$date = date('Ymd');
if(!empty($_REQUEST['state'])){
	$state = PageState::GetPageState($_REQUEST['state']);
	$data = getCSVContents($state,$type);
}

header('Content-type: text/csv');
header('Content-Disposition: attachment; filename="' . $name . '_' . $date . '.csv"');

echo $data;
?>