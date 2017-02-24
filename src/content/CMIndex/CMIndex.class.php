<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

require_once('Global.lib.php');
require_once('HTMPaths.lib.php');

class CMIndex extends ContentModule{
	public $content;
	public $heading;
	public $relationship;
	public $filter;
	public $display;
	public $pages;
	
	public function __construct($row,$foredit){
		parent::__construct($row,$foredit);
		$this->heading = $this->properties['heading'];
		$this->relationship = $this->properties['relationship'];
		$this->filter = $this->properties['filter'];
		$this->display = $this->properties['display'];
	}	
	
	public function drawContentBlock(){
		$where = empty($this->filter)?'':" and c.module = '{$this->filter}'";
		$parent = $this->pageid;
		if($this->relationship=='sibling'){
			$page = Page::getNewPage($this->pageid);
			$parent = $page->parent;
		}
		$this->pages = $GLOBALS['db']->select("select distinct p.id, p.parent, p.title, p.description from pages p
					inner join layouts l on l.pageid = p.id
					inner join content c on l.id = c.layout
					where p.parent = $parent
					$where
					and p.published > 0
					order by p.position");
		$res = $GLOBALS['skin']->getFragment('/content/CMIndex/CMIndex.tmp.html',$this);
		return $res;
	}
	
	public function drawItems(){
		if(is_array($this->pages) && count($this->pages)){
			foreach($this->pages as $page){
				$page = (object)$page;
				$page->parent = $this;
				$page->link = getHTMPath(null,'index.php','id',$page->id);
				$res .= $GLOBALS['skin']->getFragment('/content/CMIndex/CMIndex_item.tmp.html',$page);
			}				
		}
		return $res;
	}
}

?>