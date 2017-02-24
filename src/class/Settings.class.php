<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

/**
 * This class represents all settings for the system
 * 
 * Settings are identified from a config.php file and extracted from the database. 
 * config.php settings are set by the developers, while database settings can be edited by administrators in the Settings module
 *
 * @package Classes
 * @subpackage System
 * @since 2.0
 */
class Settings {
	
	// {{{ Declarations
	/**
	 * The root URL for the site. Should be set in the config. Value should not include a trailing slash.
	 *
	 * @var string 
	 *
	 */
	public $siteroot;
	
	/**
	 * The error level to set for all pages. Should be left to default for testing, but set to E_NONE for live implementations
	 *
	 * @var mixed 
	 *
	 */
	public $errorlevel = "E_ALL ^ E_NOTICE";
	
	/**
	 * The timezone to set for the site. Values should be valid for the date_default_timezone_set (http://www.php.net/manual/en/timezones.php)
	 *
	 * @var string 
	 *
	 */
	public $timezone = 'Africa/Johannesburg';
	
	/**
	 * The default value to use for the title
	 *
	 * @var string 
	 *
	 */
	public $defaulttitle = '';
	
	/**
	 * The database server. Must be set in the config
	 *
	 * @var string 
	 *
	 */
	public $dbserver;
	
	/**
	 * The database schema name. Must be set in the config
	 *
	 * @var string 
	 *
	 */
	public $dbdatabase;
	
	/**
	 * The user name to use when connecting to the database. Must be set in config
	 *
	 * @var string 
	 *
	 */
	public $dbusername;
	
	/**
	 * The password to use when connecting to the database. Must be set in config
	 *
	 * @var string 
	 *
	 */
	public $dbpassword;
	
	/**
	 * The email address from which all email from the system will be sent. Should be on the same domain as the site.
	 *
	 * @var string 
	 *
	 */
	public $sourceemail = 'gareth@blacksquare.co.za';
	
	/**
	 * The email address of the administrator. 
	 *
	 * @var string 
	 *
	 */
	public $adminemail = 'gareth@blacksquare.co.za';
	
	/**
	 * The name of the active skin.
	 * 
	 * This skin will be loaded in the Global process, just after the Settings object is created. 
	 * The name should be the exact name of the skin's class file. This should be found at skins/SkinName/SkinName.skin.php.
	 *
	 * @var string 
	 *
	 */
	public $skin = 'Standard';
	#endregion
	
	// {{{ Constructor
	/**
	 * Constructor
	 * 
	 * First fetches the config file, and loads all settings from that.
	 * Then connects to the database, and loads all settings from that.
	 *
	 * @return void 
	 *
	 */
	public function __construct()
	{
		@$this->siteroot = $_SERVER['SERVER_NAME'];

		// Collect settings from config.php
		include($GLOBALS['documentroot'] . '/config.php');
		foreach($config as $key=>$val){
			$key = strtolower(trim($key));
			if(substr($key,0,1) == '@'){
				$key = substr($key,1);
				$val = trim($val);
				$webname = ($key.'web');
				$this->$key = $GLOBALS['documentroot'] . $val;
				$this->$webname = $val;
			}elseif(is_array($val)){
				$this->$key = $val;
			}else{
				$val = trim($val);
				$this->$key = $val;
			}
		}

		$GLOBALS['db'] = new DataAccess($this->dbserver,$this->dbdatabase,$this->dbusername,$this->dbpassword);	
		if(!@$GLOBALS['db']->dbConnect($this->dbserver,$this->dbdatabase,$this->dbusername,$this->dbpassword)){
			include($GLOBALS['documentroot'].'/dberror.php');
			exit;
		}	
	
		// Collect settings from the database. These will overwrite any settings set in config.
		$sets = $GLOBALS['db']->select("select name, value, type from settings");	
		foreach ($sets as $set) {
			if($set['type'] == 'array'){
				$val = json_decode($set['value']);
				$this->$set['name'] = $val;
			}else{
				$this->$set['name'] = $set['value'];	
			}
		}					
	}
	#endregion
	
	// {{{ Manage Settings
	/**
	 * Get all editable settings, for the Settings CMS module
	 *
	 * @return array The set of all editable settings
	 *
	 */
	public function getEditableSettings(){			
		$res = $GLOBALS['db']->select("select * from settings where type not in ('hidden') order by `group`");
		return $res;
	}
	
	/**
	 * Update the editable settings
	 *
	 * @param array $values An associative array of new values
	 * @param string $error refrerence string to hold the message on error
	 * @return bool True for success, otherwise false
	 *
	 */
	public function updateSettings($values,&$error){
		$GLOBALS['db']->begintransaction();
		foreach($values as $name=>$value){
			$res = $GLOBALS['db']->update('settings',array('value'=>$value),array('name'=>$name));
			if($res === false){
				$error = mysql_error();
				$GLOBALS['db']->rollbacktransaction();
				return false;
			}
		}
		$GLOBALS['db']->committransaction();
		return true;
	}
	#endregion
	
}

?>