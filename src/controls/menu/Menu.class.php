<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');
require_once('Content.lib.php');

class Menu{
	public $id;
	public $orientation = 'left';
	public $levels = 2;
	public $items = array();
	public $pageobject;
	
	public function __construct($pageobject,$id=null,$orientation='left',$levels=2){
		$this->pageobject = (object)$pageobject;
		if(!empty($id)) $this->id = $id;
		if(!empty($orientation)) $this->orientation = $orientation;
		if(intval($levels) > 0) $this->levels = intval($levels);
	}
	
	public function drawMenu(){
		if(empty($this->items)){
			$this->populateItems();
		}
		switch($this->orientation){
			case 'top':
				return $this->drawTopMenu();
			default:
				return $this->drawLeftMenu();	
		}
	}
	
	private function populateItems(){		
		$this->items = getSubMenu(PAGE_MAINMENU,$this->pageobject->id);
	}
	
	private function drawLeftMenu(){
		$top = Page::getNewPage($this->pageobject->id);
		$second = null;
		while($top->parent!=PAGE_MAINMENU && $top->parent != null){
			$second = $top;
			$top = $top->getParent();
		}
		$res = '<menu'.(empty($this->id)?'':' id="'.$this->id.'"').'>';
		$res .= $this->drawLeftMenuRecursive($this->items,1,$top,$second);
		$res .= '</menu>';
		return $res;
	}
	
	private function drawLeftMenuRecursive($root,$level,$top,$second){
		if(isset($root['children'])) $root = $root['children'];
		if($root && count($root)){
			$res .= '<span class="menulevel'.$level.'">';
			foreach($root as $item){
				if($item['published']){
					$target=($item['type']=='link'&&!empty($item['specialpage'])?' target="'.$item['specialpage'].'"':'');
					$class = 'linklevel' . $level;
					if($item['id']==$this->pageobject->id||($top && $item['id']==$top->id)||($second&&$item['id']==$second->id)) $class .= ' linklevel'.$level.'selected';
					$res .= '<a href="' . getHTMPath(null,'index.php','id',$item['id']) . '" class="' . $class. '"'.$target.'>' . $item['menuname'] . '</a>';
					if($level < $this->levels && $top && $top->id==$item['id']) $res .= $this->drawLeftMenuRecursive($item['children'],$level+1,$top,$second);
				}
			}	
			$res .= '</span>';	
		}
		return $res;
	}
	
	private function drawTopMenu(){
		$top = Page::getNewPage($this->pageobject->id);
		$second = null;
		while($top->parent!=PAGE_MAINMENU && $top->parent != null){
			$second = $top;
			$top = $top->getParent();
		}
		$res = '<menu'.(empty($this->id)?'':' id="'.$this->id.'"').'>';
		$res .= $this->drawTopMenuRecursive($this->items,1,$top,$second);
		$res .= '</menu>';
		return $res;
	}
	
	private function drawTopMenuRecursive($root,$level,$top,$second){
		if(isset($root['children'])) $root = $root['children'];
		if($root && count($root)){
			$res .= '<span class="menulevel'.$level.'">';
			foreach($root as $item){
				if($item['published']){
					$target=($item['type']=='link'&&!empty($item['specialpage'])?' target="'.$item['specialpage'].'"':'');
					$class = 'linklevel' . $level;
					if($item['id']==$this->pageobject->id||($top && $item['id']==$top->id)||($second&&$item['id']==$second->id)) $class .= ' linklevel'.$level.'selected';
					$res .= '<span class="' . $class. '"><a href="' . getHTMPath(null,'index.php','id',$item['id']) . '"'.$target.'>' . $item['menuname'] . '</a>';
					if($level < $this->levels) $res .= $this->drawTopMenuRecursive($item['children'],$level+1,$top,$second);
					$res .= '</span>';
				}
			}	
			$res .= '</span>';	
		}
		return $res;
	}
}
?>