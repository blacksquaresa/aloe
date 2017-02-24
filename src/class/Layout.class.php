<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

/**
 * Class should be extended to represent different layout templates for Pages in the Aloe CMS.
 *
 * @package Classes
 * @subpackage Content
 * @since 2.3
 */
class Layout{
	
	// {{{ Declarations
	/**
	 * The database ID of this layout
	 *
	 * @var int 
	 *
	 */
	public $id;
	/**
	 * The database ID of the page using this template
	 *
	 * @var int 
	 *
	 */
	public $pageid;
	
	/**
	 * Whether the page is being edited in the CMS, or displayed on the website
	 *
	 * @var bool 
	 *
	 */
	public $foredit;
	
	/**
	 * The name of this particular class of layout
	 *
	 * @var string 
	 *
	 */
	public $classname;
	
	/**
	 * The display name of this layout
	 *
	 * @var string 
	 *
	 */
	public $name = 'Default';
	
	/**
	 * The name of the icon to use for this layout. The system will look for a file in the skin folder called "/images/layouts/"
	 *
	 * @var bool 
	 *
	 */
	public $icon;
	
	/**
	 * Defines the order in which the templates are displayed in a list
	 *
	 * @var int 
	 *
	 */
	public $priority;
	
	/**
	 * The position this layout holds in the current page
	 *
	 * @var int 
	 *
	 */
	public $position;
	
	/**
	 * An array of the IDs of all the columns used by the layout
	 *
	 * @var int[] 
	 *
	 */
	public $columnIds = array(1);
	#endregion
	
	// {{{ Constructor
	/**
	 * Constructor
	 *
	 * @param int $pageid The database ID of the page using this template
	 * @param bool $foredit Whether or not this page is being edited
	 * @return void 
	 *
	 */
	public function __construct($pageid,$position,$foredit,$id=null){
		$this->pageid = $pageid;	
		$this->foredit = $foredit;		
		$this->classname = get_class($this);
		$this->position = $position;
		$this->id = $id;
	}
	#endregion
	
	// {{{ Get Structure
	/**
	 * Build the HTML content of the template
	 * 
	 * @return string The HTML content
	 * 
	 */
	public function getContent(){
		if($this->foredit){
			$res = $this->getEditableContent();
		}else{
			$res = $this->getDisplayContent();
		}
		return $res;
	}
	
	protected function getEditableContent(){
		$res .= '<table cellpadding="0" cellspacing="0" class="maincontent" id="maincontenttable">';
		$res .= '<tr>
				<td valign="top" id="contentcolumn_1"></td>
				</tr>';
		$res .= '</table>';	
		return $res;		
	}
	
	protected function getDisplayContent(){
		$main = $this->getColumnContent(1,false);
		$res .= '<div class="contentcolumn_1">'.$main.'</div>';
		return $res;		
	}
	#endregion
	
	// {{{ Get Column Block IDs
	
	/**
	 * For each column, build an array of the IDs of all the content blocks in that column
	 *
	 * @return array A two-dimensional array containing the IDs of each content block in each column
	 *
	 */
	public function getColumnBlockIds(){
		$columnblockids = array();
		foreach($this->columnIds as $columnid){
			$columnblockids[$columnid] = $GLOBALS['db']->selectcolumn("select id from content c where c.layout = " . $this->id . " and c.columnid = $columnid order by position");
		}
		return $columnblockids;
	}
	#endregion
	
	// {{{ Get Column Content 
	/**
	 * Fetch the HTML content for the given column
	 *
	 * @param int $column The ID of the required column
	 * @param bool $foredit Whether or not this page is being edited.
	 * @return string The HTML content built from the content blocks in the column
	 *
	 */
	public function getColumnContent($column, $foredit=false){
		if(empty($column) || !is_numeric($column)) $column = 0;
		$rows = $GLOBALS['db']->select("select c.*, p.id as page, p.menuname 
		from content c inner join layouts l on l.id = c.layout 
		inner join pages p on p.id = l.pageid 
		where c.layout = " . $this->id . " and c.columnid = $column 
		order by position");
		if($rows && !empty($rows)){
			foreach($rows as $row){
				$block = ContentModule::getContentBlock($row,$foredit);
				$res .= $block->drawContentBlock();
			}
		}
		return $res;
	}
	#endregion
	
	// {{{ Get Icon Path 	
	/**
	 * Fetches the web path to the icon for this Layout. Looks first in the skin files, then in the core images/layouts/ folder. 
	 *
	 * @return string The web path to the icon
	 *
	 */
	public function getIconPath(){
		return $GLOBALS['skin']->getFile('images/layouts/'.$this->icon,'images/layouts/Layout.png','web');
	}
	#endregion
	
	// {{{ Move the layout
	/**
	 * *Move this layout to another position within the page. Used by the drag-and-drop system
	 *
	 * @param integer $layout The ID of the target layout
	 * @param integer $column The ID of the target column
	 * @param integer $pos The position within the target column
	 * @param string $error container for an error message
	 * @return boolean true on success
	 */
	public function moveTo($pos, &$error){
		$GLOBALS['db']->begintransaction();
		$pos = (int)$pos;
		// remove from current position
		$res = $GLOBALS['db']->update('layouts',array('position'=>999999),array('id'=>$this->id),true);
		if($res===false){
			$error = 'Could not reset target position.<br />' . mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return false;
		}
		if($pos > $this->position){
			// move all higher layout down one
			$res = $GLOBALS['db']->execute("update layouts set position = position - 1 where position > {$this->position} and position <= $pos and pageid = {$this->pageid} order by position asc");
			if($res===false){
				$error = 'Could not reset target position.<br />' . mysql_error();
				$GLOBALS['db']->rollbacktransaction();
				return false;
			}
		}elseif($pos < $this->position){
			// move all lower layout up one
			$res = $GLOBALS['db']->execute("update layouts set position = position + 1 where position < {$this->position} and position >= $pos and pageid = {$this->pageid} order by position desc");
			if($res===false){
				$error = 'Could not reset target position.<br />' . mysql_error();
				$GLOBALS['db']->rollbacktransaction();
				return false;
			}
		}		
		// insert layout into new position
		$res = $GLOBALS['db']->update('layouts',array('position'=>$pos),array('id'=>$this->id),true);
		if($res===false){
			$error = 'Could not reset target position.<br />' . mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return false;
		}
		$GLOBALS['db']->committransaction();
		return true;		
	}
	#endregion
	
	// {{{ Fetch Layout
	/**
	 * Static method to fetch an instance of the relevant extending class
	 *
	 * @param string $classname The classname of the relevant Layout extension
	 * @param int $pageid the database ID of the containing page
	 * @param bool $foredit Whether or not this page is being edited
	 * @return Template An instance of the relevant Template extension
	 *
	 */
	public static function getLayout($classname, $pageid, $position, $foredit, $id=null){
		$path = $GLOBALS['skin']->path.'/layouts/'.$classname.'.lay.php';
		if(!file_exists($path)){
			return new Layout($pageid, $position, $foredit, $id);
		}
		require_once($path);
		$module = eval('return new ' . $classname . '($pageid, $position, $foredit, $id);');
		return $module;
	}
	#endregion
	
	// {{{ List all Available Layouts
	/**
	 * Fetch a list of all available layout templates.
	 * 
	 * The list is constructed in the prepareSkin method of the current skin
	 *
	 * @return array An array containing instances of all the available layout templates for this page
	 *
	 */
	public static function getLayoutList($pageid){
		$templates = $GLOBALS['skin']->layouts;
		$res = array();
		foreach($templates as $classname) $res[] = Layout::getLayout($classname,$pageid,0,true);
		usort($res,array('Layout','sortLayoutList'));
		return $res;
	}
	
	/**
	 * Used to sort the templates by priority
	 *
	 * @param Template $a First comparitor
	 * @param Template $b Second comparitor
	 * @return int Result of the comparison
	 *
	 */
	public static function sortLayoutList($a,$b){
		return $a->priority>$b->priority?1:0;	
	}
	#endregion
	
	// {{{ Custom Settings
	/**
	 * Populate the custom fields for this Layout
	 */
	public function PopulateCustom(){	
		$this->custom = array();
		$this->custom['_settings'] = Layout::getCustomSettings();
		$values = $GLOBALS['db']->selectindex("select * from layoutvalues where layoutid = {$this->id}",'settingid');
		foreach($this->custom['_settings'] as $setting){
			$this->custom[$setting['name']] = isset($values[$setting['id']])?$values[$setting['id']]['value']:$setting['default'];
		}
	}
	
	/**
	 * Fetch the list of custom settings defined for all layouts
	 *
	 * @return array A multi-dimensional array containing the details of all custom settings
	 *
	 */
	public static function getCustomSettings(){
		$settings = $GLOBALS['db']->selectindex("select * from layoutsettings",'name');
		return $settings;
	}
	#endregion
}
?>