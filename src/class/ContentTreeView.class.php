<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');
require_once('Content.lib.php');

/**
 * This class extends the generic TreeView class to provide a treeview specifically for the content module of the CMS
 * 
 * @package Classes
 * @subpackage Content
 * @since 2.0
 */
class ContentTreeView extends TreeView{
	
	// {{{ Constructor
	/**
	 * Constructor
	 *
	 * @param bool $populateroot Whether or not to populate the tree with items. You might not do this when performing AJAX actions.
	 */
	public function __construct($populateroot = true){
		if($populateroot){
			$root = $this->CreateRoot();
			parent::__construct($root);
		}
		$this->showroot = false;
		$this->useclientselection = true;
		$this->SetPath('images',$GLOBALS['webroot'].'images/admin/content/treeview/');
		$this->SetPath('css',$GLOBALS['webroot'].'css/contenttreeview.css');
		$this->SetAttributeName('text','menuname');
		
		// Context Menu
		$this->usecontextmenu = true;		
		$this->addContextMenuOption('Create New Page','createPage_CM','add.png','ctxmenu_addpage');
		$this->addContextMenuOption('Delete Page','deletePage_CM','delete.png','ctxmenu_deletepage');
		$this->addContextMenuOption('Order Alphabetically','OrderPagesAlphab_CM','alphabet.png','ctxmenu_orderalpha');
		$this->addContextMenuOption('Move Up','MovePageUp_CM','up.png','ctxmenu_moveup');
		$this->addContextMenuOption('Move Down','MovePageDown_CM','down.png','ctxmenu_movedown');
		$this->addContextMenuOption('_');
		$this->addContextMenuOption('Edit This Page','openPage_CM','rename.png','ctxmenu_openpage');
		$this->contextmenu->setClassNames(array($this->classes['item'],$this->classes['selected'],$this->classes['disabled']));
		
		$this->contextmenushowcallback = 'ContextMenuShowCallback';
		$this->usedragdrop = true;
		$this->dragdropendcallback = 'DragDropCallback';
		$this->usepaging = false;
	}
	#endregion
	
	// {{{ Build the tree
	/**
	 * Build an array-based structure containing the items to be added to the tree
	 * 
	 * Each item in the tree must have at least a menuname and an id. It may also have an icon, a link, a disabled setting, and a collection of children.
	 *
	 * @return array The tree, as an array
	 */
	private function CreateRoot(){
		$sql = "select * from pages where parent is null order by position asc";
		$root = array();
		$root['menuname'] = 'Menu System';
		$root['id'] = 0;
		$root['icon'] = '';
		$root['children'] = $GLOBALS['db']->select($sql);
		foreach($root['children'] as &$item){
			if(empty($item['menuname'])) $item['menuname'] = '[no name]';
			$this->CreateBranch($item);
			$item['icon'] = 'tree_menu.gif';
			$item['disabled'] = true;
		}
		return $root;
	}
	
	/**
	 * Build an array-based structure containing the items to be added to a particular branch
	 * 
	 * Each item in the branch must have at least a menuname and an id. It may also have an icon, a link, a disabled setting, and a collection of children.
	 * This method is recursive, creating the whole tree.
	 *
	 * @return array The branch, as an array
	 */
	private function CreateBranch(&$item){
		$sql = "select * from pages where parent = " . $item['id'] . " order by position asc";
		$item['children'] = $GLOBALS['db']->select($sql);
		foreach($item['children'] as &$sub){
			$this->CreateBranch($sub);	
			$sub['icon'] = (count($sub['children'])?'tree_folder.gif':'tree_page.gif');
			$sub['link'] = "javascript:openPage(" . $sub['id'] . ");";
		}	
	}
	#endregion
}

?>