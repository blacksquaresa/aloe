<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

require_once('Global.lib.php');
require_once('Content.lib.php');


/**
 * The ContentModule class represents one content block within the content management system
 * 
 * This abstract class should be extended to implement any actual content blocks. 
 * 
 * @package Classes
 * @subpackage Content
 * @since 2.0
 */
abstract class ContentModule{
	
	// {{{ Declarations
	/**
	 * The database ID of this content block
	 *
	 * @var integer 
	 */
	public $id;
	
	/**
	 * Whether this content block will be edited by the CMS, or for display on the site
	 *
	 * @var boolean 
	 */
	public $foredit;
	
	/**
	 * The database ID of the page containing this block
	 *
	 * @var integer 
	 */
	public $pageid;
	
	/**
	 * The database ID of the layout containing this block
	 *
	 * @var integer 
	 */
	public $layoutid;
	
	/**
	 * The database ID of the column containing this block
	 *
	 * @var integer 
	 *
	 */
	public $columnid;
	
	/**
	 * The position of this block within the column.
	 *
	 * @var integer 
	 *
	 */
	public $position;
	
	/**
	 * The name of this content block. This will be the name of this class, and the name of the cntaining folder.
	 *
	 * @var string 
	 *
	 */
	public $modulename;
	
	/**
	 * The title of the containing page
	 *
	 * @var string 
	 *
	 */
	public $pagename;
	
	/**
	 * The column object for this column. Includes the width of the column.
	 *
	 * @var ContentColumn 
	 *
	 */
	public $column;
	
	/**
	 * An array containing all the custom properties for this block. 
	 *
	 * @var array 
	 *
	 */
	public $properties = array();
	#endregion
	
	// {{{ Constructor
	/**
	 * Constructor.
	 *
	 * @param array $row an array containing the data from the record representing this block in the database
	 * @param mixed $foredit Whether or not this block will be used by the CMS
	 */
	public function __construct($row,$foredit=false){
		$this->id = $row['id'];
		$this->foredit = $foredit;
		$this->pageid = $row['page'];
		$this->layoutid = $row['layout'];
		$this->pagename = $row['menuname'];
		$this->columnid = $row['columnid'];
		$this->position = $row['position'];
		$this->modulename = $row['module'];
		$this->column = ContentColumn::GetColumn($this->columnid);
		
		// Collect all properties into a single array. Inherited classes can then easily extract these to make them specific fields.
		if(empty($this->id) || !is_numeric($this->id)) $this->id = 0;
		$props = $GLOBALS['db']->select("select * from contentproperties where contentid = " . $this->id);
		foreach($props as $row){
			$this->properties[$row['property']] = $row['value'];
		}
	}
	#endregion
	
	// {{{ Display the Block
	/**
	 * Implement this method to draw the results of the content block.
	 *
	 * @return string The HTML to be inserted where this block needs to be. May be in the CMS, or in the website itself.
	 */
	protected abstract function drawContentBlock();
	
	/**
	 * All implementations should use this method to build the set of properties to assign to the "prop" attribute of the containing DIV tag. This is only required for editing.
	 *
	 * @return string A JSON-encoded string to insert
	 */
	public function getBlockProperties(){
		$res = array();
		$res['BlockClass'] = $this->modulename;
		$res['Columnid'] = $this->columnid;
		$res['Position'] = $this->position;
		$res['ValidColumns'] = $this->getValidColumns();
		return str_replace('"','\'',json_encode($res));
	}
	
	/**
	 * Returns a list of the IDs of all columns that are allowed to contain this block
	 *
	 * @return array The IDs of all columns
	 */
	protected function getValidColumns(){
		$columns = $GLOBALS['skin']->getValidColumnsForEditor($this->modulename);
		return $columns;
	}
	
	/**
	 * Gets the path of a usable image.
	 * 
	 * For content blocks that collect images from resources, this method will resize images to the correct size, and cache them for faster future use.
	 *
	 * @param string $root The path to the original file
	 * @param string $cachename The name of the image to use in the cache. This will usually be the ID of the content block, but blocks that use multiple images will need to supply a more detailed name.
	 * @param string $idstub The unique prefix for cache images for this instance. All images in this folder that have this stub, but not the full filename will be deleted.
	 * @param int $maxwidth The maximum width of the image
	 * @param int $maxheight The maximum height of the image
	 * @return string The path to the image to be used. This might be the original root file, or it might be a cached, resized version.
	 */
	public function getImagePath($root,$cachename,$idstub=null,$maxwidth=null,$maxheight=null){
		require_once('Images.lib.php');
		$cachepath = 'content/' . $this->modulename . '/images/';
		if(!empty($idstub)){
			$files = glob($GLOBALS['documentroot'].'/'.$cachepath.$idstub.'*.*');
			if(is_array($files)){
				foreach($files as $file){
					if(!is_dir($file) && $file != $GLOBALS['documentroot'].'/'.$cachepath.$cachename){
						@unlink($file);
					}
				}	
			}
		}
		return getImagePath($root,$cachepath,$cachename,$maxwidth,$maxheight);
	}
	#endregion
	
	// {{{ Move the block
	/**
	 * *Move this block to another column, or to another position within this column. Used by the drag-and-drop system
	 *
	 * @param integer $layout The ID of the target layout
	 * @param integer $column The ID of the target column
	 * @param integer $pos The position within the target column
	 * @param string $error container for an error message
	 * @return boolean true on success
	 */
	public function moveTo($layout, $column, $pos, &$error){
		$GLOBALS['db']->begintransaction();
		$layout = (int)$layout;
		$column = (int)$column;
		$pos = (int)$pos;
		if($column == $this->columnid && $layout == $this->layoutid){
			// remove from current position
			$res = $GLOBALS['db']->update('content',array('position'=>999999),array('layout'=>$this->layoutid,'columnid'=>$this->columnid,'position'=>$this->position),true);
			if($res===false){
				$error = 'Could not reset target position.<br />' . mysql_error();
				$GLOBALS['db']->rollbacktransaction();
				return false;
			}
			if($pos > $this->position){
				// move all higher blocks down one
				$res = $GLOBALS['db']->execute("update content set position = position - 1 where position > {$this->position} and position <= $pos and layout = {$this->layoutid} and columnid = {$this->columnid} order by position asc");
				if($res===false){
					$error = 'Could not reset target position.<br />' . mysql_error();
					$GLOBALS['db']->rollbacktransaction();
					return false;
				}
			}elseif($pos < $this->position){
				// move all lower blocks up one
				$res = $GLOBALS['db']->execute("update content set position = position + 1 where position < {$this->position} and position >= $pos and layout = {$this->layoutid} and columnid = {$this->columnid} order by position desc");
				if($res===false){
					$error = 'Could not reset target position.<br />' . mysql_error();
					$GLOBALS['db']->rollbacktransaction();
					return false;
				}
			}		
			// insert block into new position
			$res = $GLOBALS['db']->update('content',array('position'=>$pos),array('layout'=>$this->layoutid,'columnid'=>$this->columnid,'position'=>999999),true);
			if($res===false){
				$error = 'Could not reset target position.<br />' . mysql_error();
				$GLOBALS['db']->rollbacktransaction();
				return false;
			}
		}else{
			// make space for the block
			$res = $GLOBALS['db']->execute("update content set position = position + 1 where position >= $pos and layout = $layout and columnid = $column order by position desc");
			if($res===false){
				$error = 'Could not reset target position.<br />' . mysql_error();
				$GLOBALS['db']->rollbacktransaction();
				return false;
			}
			// move the block
			$res = $GLOBALS['db']->update('content',array('position'=>$pos,'layout'=>$layout,'columnid'=>$column),array('layout'=>$this->layoutid,'columnid'=>$this->columnid,'position'=>$this->position),true);
			if($res===false){
				$error = 'Could not reset target position.<br />' . mysql_error();
				$GLOBALS['db']->rollbacktransaction();
				return false;
			}
			// reset previous blocks
			$res = $GLOBALS['db']->execute("update content set position = position - 1 where position > {$this->position} and layout = {$this->layoutid} and columnid = {$this->columnid}");
			if($res===false){
				$error = 'Could not reset target position.<br />' . mysql_error();
				$GLOBALS['db']->rollbacktransaction();
				return false;
			}
		}		
		$this->columnid = $column;
		$this->position = $pos;
		$GLOBALS['db']->committransaction();
		return true;		
	}
	#endregion
	
	// {{{ Create the block
	/**
	 * Override this method to perform actions when the block is created.
	 * 
	 * When a content block is created, this method is called on the implementing class to allow that class to, for example, create new folders, database entries or download external resources. This method is called before the creation is finalised, so that a false return value can cancel the creation.
	 *
	 * @param ineteger $id The ID of the content block being created
	 * @param string $content The content that will be saved with this block
	 * @param array $properties The collection of properties that will be saved for this block
	 * @param string $error container for an error message
	 * @return boolean true for success, fale to abort the create action.
	 */
	public static function CreateBlock($id, $content, $properties, &$error){
		return true;	
	}
	#endregion
	
	// {{{ Update the block
	/**
	 * Override this method to perform actions when the content block is updated 
	 *
	 * @param string $content The content that will be saved with this block
	 * @param array $properties The collection of properties that will be saved for this block
	 * @param string $error container for an error message
	 * @return boolean true for success, fale to abort the update action.
	 */
	public function UpdateBlock($content, $properties, &$error){
		return true;	
	}
	#endregion
	
	// {{{ Delete the block
	/**
	 * Override this method to perform actions when the content block is deleted 
	 * 
	 * When a content block is deleted, this method is called. Use this method to remove folders, delete files or database entries, or make external API calls as needed.
	 *
	 * @param string $error container for an error message
	 * @return boolean true for success, fale to abort the delete action.
	 */
	public function DeleteBlock(&$error){
		return true;	
	}
	#endregion
	
	// {{{ Copy the block
	/**
	 * Override this method to perform actions when the content block is copied 
	 * 
	 * When a content block is copied, this method is called. Use this method to copy folders, files or database entries, or make external API calls as needed.
	 * This method is called on the original Content Block, after thenew block has been created.
	 * This method is called within a transaction, so it should not begin its own transaction.
	 *
	 * @param int $newblockid The ID for the new Content Block
	 * @param string $error container for an error message
	 * @return boolean true for success, false to abort the copy action.
	 */
	public function CopyBlock($newblockid,&$error){
		return true;	
	}
	#endregion
	
	// {{{ Clear the Cache
	/**
	 * Override this method to clear any caches used by this Content Block 
	 * 
	 * This method will be called whenever a Content Block is updated or deleted, but may also be called at other times 
	 * (eg: when a resource that might have been cached has been changed)
	 *
	 * @return boolean true for success, false on failure.
	 */
	public function ClearCache(&$error){
		return true;	
	}
	#endregion
	
	// {{{ Fetch an instance
	/**
	 * Use this method to build a new instance of a content block. 
	 *
	 * @param array $mod An array containing all the fields in the database record for this block
	 * @param bool $foredit Whether the content block will be used in the CMS or not
	 * @return ContentModule An instance of the correct extension of this class
	 *
	 */
	public static function getContentBlock($mod, $foredit=false){
		if(!is_array($mod)){
			if(empty($mod) || !is_numeric($mod)) $mod = 0;
			$mod = $GLOBALS['db']->selectrow("select c.*, p.id as page, p.menuname 
						from content c 
						inner join layouts l on l.id = c.layout 
						inner join pages p on p.id = l.pageid 
						where c.id = $mod");
		}
		if(empty($mod)){
			throw new Exception('Content Block not found');
		}
		if(!file_exists($GLOBALS['settings']->contentpath.$mod['module'].'/'.$mod['module'].'.class.php')){
			throw new Exception('Content Module not found');
		}
		require_once($GLOBALS['settings']->contentpath.$mod['module'].'/'.$mod['module'].'.class.php');
		$module = eval('return new ' . $mod['module'] . '($mod,$foredit);');
		return $module;
	}
	#endregion
}

?>