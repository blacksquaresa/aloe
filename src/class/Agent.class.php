<?php
/**
 * Class to handle all AJAX calls from a single page
 *
 */
class Agent {
	/**
	 * This method handles a single AJAX call
	 *
	 * @param mixed $aa_sfunc The URL of the Library file containing the method being called, or empty for the current PHP file
	 * @param mixed $aa_cfunc The name of the method being called
	 * @param mixed $aa_sfunc_args The name of a JavaScript callback function to which to send the result, or the ID of an object on the page whose innerHTML should be set to the result. 
	 * If no value is provided, the method returns the result. Further arguements are passed on to the called method.
	 * @return mixed The result of the method called
	 *
	 */
	function call ($aa_sfunc, $aa_cfunc, $aa_sfunc_args) {
		$aa_sfunc_args_dc=array();
		if($aa_sfunc_args && sizeof($aa_sfunc_args)>=1) {
			foreach ($aa_sfunc_args as $aa_arg) {
				if ((strpos($aa_arg, "[")!==false) || (strpos($aa_arg, "{")!==false)) {
					if ((strpos($aa_arg, "[")===0) || (strpos($aa_arg, "{")===0)) {
						$aa_arg = stripslashes($aa_arg);
						$aa_arg_dc = json_decode($aa_arg);
						array_push($aa_sfunc_args_dc,$aa_arg_dc);
					} else {
						array_push($aa_sfunc_args_dc,$aa_arg);
					}
				} else {
					array_push($aa_sfunc_args_dc,$aa_arg);
				}
			}
		}
		
		// sfix # sf001
		$arr = get_defined_functions();
		if (!in_array(strtolower($aa_sfunc), $arr["user"]) && !in_array($aa_sfunc, $arr["user"])) exit();
		
		$ret = call_user_func_array($aa_sfunc, $aa_sfunc_args_dc);
		if(is_array($ret) || is_object($ret)) {
			$ret = json_encode($ret);
			echo $ret; 
		} else {
			echo $ret;
		}
		exit();
	}
	
	/**
	 * Initiate the AJAX agent. Essentially just adds the JavaScript calls to the page.
	 *
	 * @param bool $echo Whether the method should return the script, or echo it directly. This is included for backward compatibility, so the default is to echo.
	 * @return mixed The script or nothing
	 *
	 */
	function init($echo=true) {
		global $aa_url;
		if($_SERVER['REQUEST_URI']==null||$_SERVER['REQUEST_URI']=="") {
			$aa_url = $_SERVER['PHP_SELF'];
		} else {
			$aa_url = $_SERVER['REQUEST_URI'];
		}
		$res .= '<script type="text/javascript">var this_url = \''. $aa_url .'\';</script><script type="text/javascript" src="/js/Agent.js"></script>';
		if($echo) echo $res;
		else return $res;
	}
}
?>