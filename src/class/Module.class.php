<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

/**
 * Abstract class provides the basis for all modules. 
 * 
 * All modules in Aloe that need to integrate with the core must include an extension of this class. 
 * This provides the core system with information about the module, and implements all hooks provided by the core.
 *
 * @package Classes
 * @subpackage Content
 * @since 2.0
 */
abstract class Module{
	
	// {{{ Declarations
	/**
	 * The database ID of the module
	 *
	 * @var int 
	 */
	var $id;
	
	/**
	 * The name of the class that extends the Module class
	 *
	 * @var string 
	 */
	var $classname;
	
	/**
	 * The name of the module
	 *
	 * @var string 
	 */
	var $name;
	
	/**
	 * The identifying code for this module
	 *
	 * @var string 
	 */
	var $code;
	
	/**
	 * The path to the files pertaining to this module
	 *
	 * @var string 
	 */
	var $path;
	
	/**
	 * The url of the file the CMS should link to from its menu
	 *
	 * @var string 
	 *
	 */
	var $adminurl = 'index.php';
	
	/**
	 * The description of this module
	 *
	 * @var string 
	 *
	 */
	var $description;
	#endregion
	
	// {{{ Constructor
	/**
	 * Constructor
	 *
	 * @param int $id The dtabase ID of this module
	 * @param string $classname The name of the extending class for this module
	 * @param string $name The name of this module
	 * @param string $code The identifying code for this module
	 * @param string $path The path to the files pertaining to this module
	 */
	public function __construct(){
		$rc = new ReflectionClass($this);
		$this->classname = $rc->getName();
		$this->name = trim(preg_replace('/([A-Z])/',' $1',$this->classname));
		$this->code = getCleanRoot($this->name);
		$this->path = dirname($rc->getFileName());
	}
	#endregion
	
	// {{{ Hook implementations	
	/**
	 * Override this method to create an HTM path for a page managed by this module
	 *
	 * @param string $filename The name of the target php file
	 * @param string $att1 The name of the first attribute to check
	 * @param string $val1 The value of the first attribute to check
	 * @param string $att2 The name of the second attribute to check
	 * @param string $val2 The value of the second attribute to check
	 * @param string $att3 The name of the third attribute to check
	 * @param string $val3 The value of the third attribute to check
	 * @return string The HTM path to use
	 *
	 */
	public function getHTMPath($filename,$att1=null,$val1=null,$att2=null,$val2=null,$att3=null,$val3=null){
		return 'index.htm';
	}
	
	/**
	 * Override this method to provide a different link for the CMS top menu.
	 *
	 * @return string The code of the link, which includes the anchor tag and the link text
	 */
	public function getAdminMenuItem(){
		return drawMenuItem($this->code,$GLOBALS['webroot'].ltrim($this->path,'/').$this->adminurl,$this->name);
	}
	#endregion
	
	// {{{ Get Modules
	/**
	 * Fetch an instance of the correct extending class for the supplied module details
	 *
	 * @param int $id The database ID for this module
	 * @param string $classname The name of the class of the module
	 * @param string $title A title for this module
	 * @param string $code The code for this module
	 * @param string $path The path to the files for this module
	 * @return Module The new instance
	 */
	public static function getModule($classname){
		$modfile = $GLOBALS['documentroot'].'/modules/'.$classname.'/'.$classname.'.mod.php';
		if(!file_exists($modfile)){
			throw new Exception('Module not found');
		}
		require_once($modfile);
		$module = eval("return new $classname();");
		return $module;
	}
	
	/**
	 * Get a collection of all modules in the system
	 *
	 * @return Module[] An array containing instances of each module in the system
	 */
	public static function getModules(){
		$modules = $GLOBALS['db']->select("select * from modules");
		if($modules===false) return false;
		foreach($modules as $mod){
			$res[$mod['classname']] = Module::getModule($mod['classname']);
		}
		return $res;
	}
	#endregion
}
?>