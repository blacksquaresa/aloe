<?php
/**
 * This class represents the state of a page. 
 * 
 * It works by maintaining a set of variables for the page int the session, and updating those from the $_REQUEST variables whenever the page is reloaded. 
 * Any changes are recorded, but if a variable is not listed, it is not changed or removed. 
 * This allows that a page can "remember" a complex set of variables without having to pass them around.
 * The class encapsulates all the state variables for the page, and makes it easy to send the whole state as a single entity.
 * It is usually used in conjunction with a listview control to maintain the state of filters, but is also sometimes used with CSV exports.
 *
 * @package Classes
 * @subpackage Page State
 * @since 2.0
 */
class PageState{
	
	// {{{ Declarations
	/**
	 * An associative array containing all the $_REQUEST variables that should be checked when the state is set
	 *
	 * @var array 
	 *
	 */
	public $columns = array();
	
	/**
	 * An associative array listing the current values of the variables listed in the columns array
	 *
	 * @var array 
	 *
	 */
	public $values = array();
	
	/**
	 * A string to uniquely identify this PageState object in the $_SESSION variable
	 *
	 * @var string 
	 *
	 */
	public $statename = '_pagestate';
	
	/**
	 * The key within the $_REQUEST array that rtepresents the search button
	 *
	 * @var string 
	 *
	 */
	public $searchkey='search';
	
	/**
	 * The default string used by calendar controls to represent an unset date
	 *
	 * @var string 
	 *
	 */
	public $defaultdatestring = '(click to select)';
	
	/**
	 * The method that should be used by a List View or Grid View control to populate themselves. This PageState object will be passed to this method, supplying the required filters 
	 *
	 * @var string 
	 *
	 */
	public $listmethod;
	
	/**
	 * The method that should be used by a List View or Grid View control to count the total results. This PageState object will be passed to this method, supplying the required filters
	 *
	 * @var string 
	 *
	 */
	public $countmethod;
	
	/**
	 * The full path to the library file containing the listmethod and countmethod methods
	 *
	 * @var string 
	 *
	 */
	public $methodfilepath;
	#endregion
	
	// {{{ Constructor
	/**
	 * Constructor
	 *
	 * @param string $statename A unique name for this state object
	 * @param string $listmethod The name of the method for a List View or Grid View to use with this state
	 * @param string $countmethod The name of the method for a List View or Grid View to use to count the total results with this state
	 * @param string $methodfilepath The full path to the library file containing the listmethod and countmethod.
	 * @return void	
	 *
	 */
	public function __construct($statename,$listmethod=null,$countmethod=null,$methodfilepath=null){
		$this->statename = $statename;
		$this->listmethod = $listmethod;
		$this->countmethod = $countmethod;
		$this->methodfilepath = $methodfilepath;
	}
	#endregion
	
	// {{{ Add State Item
	/**
	 * Used in a build method, this method adds a new item to the columns collection
	 *
	 * @param string $type The datatype of the value to be stored
	 * @param string $requestkey The key to identify the value in the $_REQUEST array
	 * @param string $statekey The key to use internally, if different from the requestkey
	 * @return void
	 *
	 */
	public function AddStateItem($type, $requestkey, $statekey=null){
		if(empty($statekey)) $statekey = $requestkey;
		$item = array();
		$item['type'] = $type;
		$item['requestkey'] = $requestkey;
		$item['statekey'] = $statekey;
		$this->columns[$statekey] = $item;
	}
	#endregion
	
	// {{{ Set State
	/**
	 * Populates the values array from the current $_REQUEST array
	 *
	 * @return void 
	 *
	 */
	public function SetState(){
		foreach($this->columns as $item){
			switch($item['type']){
				case 'array':
					if(empty($this->values[$item['statekey']]) || 
							isset($_REQUEST[$item['requestkey']]) || 
							(isset($_REQUEST[$this->searchkey]) && empty($_REQUEST[$item['requestkey']]))){
						$this->values[$item['statekey']] = empty($_REQUEST[$item['requestkey']])?array():$_REQUEST[$item['requestkey']];
					}
					break;
				case 'bool':
					if(empty($this->values[$item['statekey']]) || 
							isset($_REQUEST[$item['requestkey']]) || 
							(isset($_REQUEST[$this->searchkey]) && empty($_REQUEST[$item['requestkey']]))){
						$this->values[$item['statekey']] = empty($_REQUEST[$item['requestkey']])?false:$_REQUEST[$item['requestkey']]?true:false;
					}
					break;
				case 'date':
					if(empty($this->values[$item['statekey']]) || 
							isset($_REQUEST[$item['requestkey']])){
						$this->values[$item['statekey']] = empty($_REQUEST[$item['requestkey']])?$this->defaultdatestring:$_REQUEST[$item['requestkey']];
					}
				default:
					if(empty($this->values[$item['statekey']]) || 
							isset($_REQUEST[$item['requestkey']])){
						$this->values[$item['statekey']] = $_REQUEST[$item['requestkey']];
					}
					break;	
			}
		}
		$this->SaveToSession();
	}
	#endregion
	
	// {{{ Manually Set State Items
	/**
	 * Manually set the value of an item
	 *
	 * @param string $key The key to identify the item being set
	 * @param mixed $value The value of the item being set
	 * @return void 
	 *
	 */
	public function SetStateItem($key,$value){
		$this->values[$key] = $value;
		$this->SaveToSession();
	}
	
	/**
	 * Manually set the value of multiple items
	 *
	 * @param array $items An associative array of key=>value pairs
	 * @return void 
	 *
	 */
	public function SetStateItems($items){
		foreach($items as $key=>$value){
			$this->SetStateItem($key,$value);	
		}		
	}
	
	/**
	 * Save the current state object to the $_SESSION. This is usually done automatically.
	 *
	 * @return void 
	 *
	 */
	public function SaveToSession(){
		$_SESSION[$this->statename] = serialize($this);	
	}
	#endregion
	
	// {{{ Statically Set Page State
	/**
	 * Static method called to update a particular page state
	 *
	 * @param string $statename The unique ID of the pagestate
	 * @return PageState The PageState object, after updating 
	 *
	 */
	public static function SetPageState($statename){
		$state = PageState::GetPageState($statename);
		if($state){
			$state->SetState();
		}else{
			//echo 'Page State could not be found<br />statename: ' . $statename . '<br />';	
		}
		return $state;
	}
	#endregion
	
	// {{{ Static: Get Page State
	/**
	 * Static method to fetch an instance of the PageState class for the given unique ID
	 * 
	 * This method will fetch the PageState object from the $_SESSION, if it exists, otherwise it will attempt to create one
	 * First, it will check for a method named BuildPageState. If this exists, it is called, and it is expected to return the PageState instance. 
	 * This allows each page to build its own PageState, with the keys it requires. If the BuildPageState method is not found, the system will build the object
	 * using the values found in the current $_REQUEST array, ignoring certain common keys.
	 *
	 * @param string $statename The unique ID for this page state
	 * @return PageState The instance of the PAgeState object, either created or fetched from the $_SESSION
	 *
	 */
	public static function GetPageState($statename){
		$IGNORE_REQUEST_KEYS = array('phpsessid','dbgsessid','id','userid','persistent_login__');
		$BOOLEAN_RESULT_VALUES = array('on','true');
		if(isset($_SESSION[$statename])){
			$state = unserialize($_SESSION[$statename]);
		}else{
			if(function_exists('BuildPageState')){
				$state = BuildPageState();	
			}else{
				$state = new PageState($statename);
				foreach($_REQUEST as $key=>$value){
					if(in_array(strtolower($key),$IGNORE_REQUEST_KEYS) == false){
						if(substr($key,-1) == ']'){
							$state->AddStateItem('array',$key);
						}elseif(in_array($value,$BOOLEAN_RESULT_VALUES)){
							$state->AddStateItem('bool',$key);
						}elseif(strpos($key,'date')!==false){
							$state->AddStateItem('date',$key);
						}else{
							$state->AddStateItem('string',$key);
						}	
					}
				}
			}	
		}
		return $state;
	}
	#endregion
	
	// {{{ Static: Clear Page State
	/**
	 * Static method used to completely delete the current version of the object from the $_SESSION variable. 
	 * Usually used in development to clear the state while testing.
	 *
	 * @param string $statename The unique ID for this page state
	 * @return void 
	 *
	 */
	public static function ClearPageState($statename){
		if(isset($_SESSION[$statename])){
			unset($_SESSION[$statename]);	
		}	
	}	
	#endregion
}

?>