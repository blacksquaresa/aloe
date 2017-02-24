<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

/**
 * Extends the basic TreeView to provide a tree view for the Resource Manager
 *
 * @package Classes
 * @subpackage Resources
 * @since 2.0
 */
class ResourceTreeView extends TreeView{
	
	// {{{ Declarations
	/**
	 * Contains a reference to the parent Resource Manager
	 *
	 * @var ResourceManager 
	 */
	var $resourcemanager = null;
	#endregion
	
	// {{{ Constructor
	/**
	 * Constructor
	 *
	 * @param bool $populateroot Whether or not to populate the tree. 
	 * @param ResourceManager $owner The ResourceManager instance containing this treeview
	 */
	public function __construct($populateroot = false, $owner = null){
		$this->resourcemanager = $owner;
		parent::__construct($populateroot,$this->resourcemanager->prefix.'trv');
		$this->showroot = true;
		$this->useclientselection = true;
		$this->classname = 'ResourceTreeView';
		$this->SetPath('images',$GLOBALS['webroot'].'images/resources/treeview/');
		$this->SetPath('css',null);
		$this->SetAttributeName('text','name');
		$this->selectediconmap = array(
				'tree_folder.gif'=>'tree_folderopen.gif',
				'dtree_menu.gif'=>'dtree_menuopen.gif',
				'tree_page.gif'=>'tree_page_active.gif',
				'tree_disabled.gif'=>'tree_disabledopen.gif'
				);
		
		$this->usedragdrop = false;
		$this->usepaging = false;
	}
	#endregion
}

?>