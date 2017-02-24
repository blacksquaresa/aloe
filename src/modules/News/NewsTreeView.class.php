<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');
require_once('News.lib.php');

class NewsTreeView extends TreeView{
	public $sourceid = '';
	public $owner = '';
	
	public function __construct($sourceid,$owner){
		$this->sourceid = $sourceid;
		$this->owner = $owner;
		$root = $this->CreateRoot();
		parent::__construct($root,'news');
		$this->showroot = true;
		$this->expandlevel = 1;
		$this->useclientselection = true;
		$this->SetAttributeName('text','title');
		$this->selectediconmap = array(
				'tree_menu.gif'=>'tree_menuopen.gif',
				'tree_folder.gif'=>'tree_folderopen.gif',
				'tree_page.gif'=>'tree_page_active.gif'
				);
		
		// Context Menu
		$this->usecontextmenu = false;		
		$this->usedragdrop = false;
		$this->usepaging = false;
		$this->setAjax($GLOBALS['webroot'].'modules/News/NewsTreeView.ajax.php','CreateBranch');
		$this->ajaxpocket = json_encode(array($sourceid,$owner));
		$this->SetPath('css',$GLOBALS['webroot'].'modules/News/newstreeview.css');
	}
	
	private function CreateRoot(){
		$root = array();
		$root['title'] = 'News';
		$root['id'] = 0;
		$root['icon'] = 'tree_menu.gif';
		$root['disabled'] = true;
		$categories = array();
		$categories['title'] = 'Categories';
		$categories['id'] = 'categories';
		$categories['icon'] = 'tree_menu.gif';
		$categories['disabled'] = true;
		$listmanager = ListManager::getListManager();
		$newscategory = $listmanager->getList('news');
		$categories['children'] = array_values($newscategory['items']);
		foreach($categories['children'] as &$cat){
			$link = '/'.trim(getHTMPath('News','news.php','cat',$cat['id']),'./');
			$cat['id'] = 'cat_'.$cat['id'];
			$cat['title'] = $cat['name'];
			$cat['icon'] = 'tree_page.gif';
			$cat['link'] = "javascript:parent.setElementValue('{$this->sourceid}','$link','{$this->owner}');parent.PopupManager.hideLinkSelector();";
		}
		$root['children'][] = $categories;
		$sql = "select distinct year(from_unixtime(date)) as title from news order by title desc";
		$years = $GLOBALS['db']->select($sql);
		foreach($years as $item){
			$item['id'] = $item['title'];
			$this->CreateMonth($item);
			$item['icon'] = 'tree_folder.gif';
			$item['disabled'] = true;
			$root['children'][] = $item;
		}
		return $root;
	}
	
	private function CreateMonth(&$item){
		$sql = "select distinct month(from_unixtime(date)) as id, monthname(from_unixtime(date)) as title from news where date between unix_timestamp('{$item['title']}-01-01') and unix_timestamp('{$item['title']}-12-31 23:59:59') order by date desc";
		$item['children'] = $GLOBALS['db']->select($sql);
		foreach($item['children'] as &$sub){
			$sub['id'] = $item['title'].'-'.$sub['id'];
			$sub['icon'] = 'tree_folder.gif';
			$sub['properties'] = "{sourceid:'{$this->sourceid}',owner:'{$this->owner}'}";
			$sub['childcount'] = 1;
		}	
	}
	
	public function CreateBranch($uniqueid,$itemid,$props,$level,$islasts){
		list($year,$month) = explode('-',$itemid);
		$start = mktime(0,0,0,$month,1,$year);
		$end = mktime(0,0,0,$month+1,1,$year) - 1;
		$sql = "select id, title, date from news where date between $start and $end order by date desc";
		$branch = $GLOBALS['db']->select($sql);
		foreach($branch as &$sub){
			$sub['icon'] = 'tree_page.gif';
			$link = '/'.trim(getHTMPath('News','news.php','id',$sub['id']),'./');
			$sub['link'] = "javascript:parent.setElementValue('{$this->sourceid}','$link','{$this->owner}');parent.PopupManager.hideLinkSelector();";
		}
		return $branch;
	}
}

?>