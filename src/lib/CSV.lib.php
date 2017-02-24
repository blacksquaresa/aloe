<?php
/**
 * The CSV library provides functions to create CSV files from data constructed with a PageState class. 
 * 
 * @package Library
 * @subpackage CSV
 * @since 2.0
 **/
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');


/**
 * Build the contents of a CSV file from a PageState object
 *
 * @param PageState $state The PageState instance which contains all filters and paths to the functions required to colect the data
 * @return string The constructed CSV content
 *
 */
function getCSVContents($state){
	$csv = '';
	$data = getCSVData($state,$type);
	if($data && count($data)){
		$csv .= getCSVHeadings($data);
		foreach($data as $row){
			$csv .= getCSVRow($row);
		}
	}
	return $csv;
}

/**
 * Fetches the data to be converted into CSV. Uses the supplied PageState instance to call the appropriate methods, and pass the correct filters
 *
 * @param PageState $state the PageState instance containing all the required information
 * @return array A multi-dimensional array containing the data to be processed
 *
 */
function getCSVData($state){
	// Set the start and limit values to 0, so the whole list is returned. 
	// Don't use the SetValue method, so as not to save the result to the Session.
	$state->values['lim'] = 0;
	$state->values['start'] = 0;
	if(file_exists($state->methodfilepath)) require_once($state->methodfilepath);
	if(function_exists($state->listmethod)){
		$data = call_user_func($state->listmethod,$state);
		return $data;
	}
}

/**
 * Construct a set of headings to be used for the CSV file
 *
 * @param array $data The data being processed
 * @return string The first line of the CSV file, including the line-endings, containing the column headings.
 *
 */
function getCSVHeadings($data){
	$headings = '';
	foreach(array_keys($data[0]) as $head){
		if(!empty($headings)) $headings .= ',';
		$headings .= '"' . ucwords($head) . '"';	
	}
	$headings .= "\r\n";
	return $headings;
}

/**
 * Formats a single row of data for the CSV, with a line-ending. 
 * Prices are formatted as numbers
 * Dates are expected as unix timedstanmps, and are displayed as simp[le dates
 * Passwords are hidden
 *
 * @param array $row The data for this row
 * @return string The formatted line to be included in the CSV
 *
 */
function getCSVRow($row){
	$pricenames = array('amount','price','paid','earned','owed','commission','price_excl','price_incl','total_excl','total_incl');
	$datenames = array('date','datevalid','datecreated','confirmdate','shipdate','lastearned','lastpaid','lastlogin','departure','arrivalport','arrivalwarehouse','notification','windowstart','windowend','finaldate','startdate','enddate');
	$hidenames = array('password');
	$rowdata = '';
	foreach($row as $head=>$item){
		$item = str_replace("\r","",$item);
		if(!empty($rowdata)) $rowdata .= ',';
		if(in_array($head,$hidenames)) $rowdata .= '"****"';
		elseif(in_array($head,$pricenames)) $rowdata .= number_format($item,2,'.','');
		elseif(in_array($head,$datenames)){
			if($item) $rowdata .= '"' . date('Y/m/d',$item) . '"';
			else $rowdate .= '-';
		}
		elseif(is_numeric($item)) $rowdata .= $item;
		else $rowdata .= '"' . str_replace("\n","|",$item) . '"';	
	}
	$rowdata .= "\r\n";
	return $rowdata;	
}

?>