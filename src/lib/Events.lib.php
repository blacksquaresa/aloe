<?php
/**
 * The Events library provides methods to manage events in the CMS, and on the site
 * 
 * @package Library
 * @subpackage Events
 * @since 2.0
 */
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');
require_once('Global.lib.php');
require_once('Text.lib.php');

function loadEventHandlers(){
	$handlers = $GLOBALS['db']->select("select * from handlers order by event");	
	$GLOBALS['eventhandlers'] = array();
	foreach($handlers as $handler){
		$GLOBALS['eventhandlers'][$handler['event']][] = $handler;	
	}
}

function fireEvent($event,&$data){
	if(isset($GLOBALS['eventhandlers'][$event])){
		foreach($GLOBALS['eventhandlers'][$event] as $handler){
			switch($handler['type']){
				case 'class':
					if(class_exists($handler['classname'])){
						if(method_exists($handler['classname'],$handler['function'])){
							call_user_func(array($handler['classname'],$handler['function']),$event,$data);
						}	
					}
					break;	
				case 'instance':
					$objectname = $handler['classname'];
					if(isset($GLOBALS[$objectname])){
						$methodname = $handler['function'];
						if(method_exists($GLOBALS[$objectname],$methodname)){
							$GLOBALS[$objectname]->$methodname($event,$data);
						}	
					}
					break;	
				default:
					if(!empty($handler['path'])){
						$path = $GLOBALS['documentroot'].'/'.ltrim($handler['path'],'/');
						if(file_exists($path)) require_once($path);
					}
					if(function_exists($handler['function'])){
						call_user_func($handler['function'],$event,$data);
					}
					break;	
			}	
		}
	}	
}

?>