<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

/**
 * Provides a container for information about each of the columns available in the Content Editor.
 * 
 * The class is used by the Skin to contain all information about a column, including width and a full list of available content blocks.
 * 
 * @package Classes
 * @subpackage Content
 * @since 2.0
 */
class ContentColumn{
	
	// {{{ Declarations
	/**
	 * The ID of the current column
	 */ 
	public $id;
	
	/**
	 * The width of the current column, in pixels.
	 */ 
	public $width;
	
	/**
	 * The width of the current column, in pixels, when used in the CMS. This should be the base width plus 8, then incremented by 5s to fit the layouts.
	 */ 
	public $adminwidth;
	
	/**
	 * An array listing all valid content blocks for this column
	 *
	 * @var array 
	 *
	 */
	public $contentblocks = array();
	#endregion	
	
	// {{{ Constructor
	/**
	 * This is method __construct
	 *
	 * @param int $id The ID of the column, as defined by the current Skin
	 * @param int $width The width of the column, in pixels
	 * @param int $adminwidth The width of the column, in pixels, when used in the CMS
	 * @param array $contentblocks A list of the class names of the content blocks allowed in this column.
	 */
	public function __construct($id,$width,$adminwidth,$contentblocks){	
		$this->id = $id;
		$this->width = $width;
		$this->adminwidth = $adminwidth;
		if(is_array($contentblocks)) $this->contentblocks = $contentblocks;
	}
	#endregion
	
	// {{{ Get Column
	/**
	 * Static method returns a specific ContentColumn instance, based on the provided ID
	 *
	 * @param int $id The ID of the required column
	 * @return ContentColumn The column represented by the supplied ID
	 */
	public static function GetColumn($id){
		return $GLOBALS['skin']->columns[$id];	
	}
	
	/**
	 * Returns an array of columns that have the same width as the supplied column. Includes the current column.
	 *
	 * @param int $id The ID of the column against which all the others should be compared
	 * @return ContentColumn[] An array of all columns with the same width.
	 */
	public static function getSimilarColumns($id){
		$current = $GLOBALS['skin']->columns[$id];
		$res = array();
		foreach($GLOBALS['skin']->columns as $col){
			if($col->width = $current->width) $res[] = $col->id;	
		}
		return $res;
	}
	#endregion
}

?>