<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

class ContextMenu{
	
	var $idnames;
	var $classnames;
	var $menuid;
	var $menuoptions = array();
	var $scriptpath;
	var $csspath;
	var $iconpath;
	var $preventDefault = true;
	var $preventForms = true;
	var $showCallback = false;
	var $hideCallback = false;
	
	function ContextMenu($menuid){
		$this->menuid = $menuid;
		$this->csspath = $GLOBALS['webroot'] . 'controls/contextmenu/contextmenu.css';
		$this->scriptpath = $GLOBALS['webroot'] . 'controls/contextmenu/ContextMenu.js';
		$this->iconpath = $GLOBALS['webroot'] . 'images/admin/common/';
	}
	
	function setClassNames($classnames){
		$this->classnames = $classnames;	
	}
	
	function setIDNames($idnames){
		$this->idnames = $idnames;	
	}
	
	function addMenuOption($text,$link=null,$icon=null,$id=null){
		$this->menuoptions[] = new ContextMenuItem($this,$text,$link,$icon,$id);
	}
	
	function drawContextMenu(){		
		if(!$GLOBALS['contextmenu_scriptloaded']){
			$res .= '<script language="javascript" src="' . $this->scriptpath . '"></script>';
			if(!empty($this->csspath)) $res .= '<link rel="stylesheet" href="' . $this->csspath . '" />';
			$GLOBALS['contextmenu_scriptloaded'] = true;
		}
		$res .= '<div class="ctm_menu" id="' . $this->menuid . '">';
		foreach($this->menuoptions as $item){
			$res .= $item->drawMenuItem();
		}
		$res .= '</div>';
		if(!empty($this->idnames)){
			if(is_array($this->idnames)){
				foreach($this->idnames as $name){
					if(!empty($names)) $names .= ',';
					$names .= "'$name'";
				}
				$names = "new Array(" . $names . ")";
			}else{
				$names = "'" . $this->idnames . "'";	
			}
			$res .= '<script language="javascript">ContextMenu.setup({
					\'preventDefault\':' . ($this->preventDefault?'true':'false') . ',
					\'preventForms\':' . ($this->preventForms?'true':'false') . '});
					ContextMenu.attachid(' . $names . ',\'' . $this->menuid . '\',' . ($this->showCallback?'\'' . $this->showCallback . '\'':'null') . ',' . ($this->hideCallback?'\'' . $this->hideCallback . '\'':'false') . ');</script>';
		}else{
			if(is_array($this->classnames)){
				foreach($this->classnames as $name){
					if(!empty($names)) $names .= ',';
					$names .= "'$name'";
				}
				$names = "new Array(" . $names . ")";
			}else{
				$names = "'" . $this->classnames . "'";	
			}
			$res .= '<script language="javascript">ContextMenu.setup({
					\'preventDefault\':' . ($this->preventDefault?'true':'false') . ',
					\'preventForms\':' . ($this->preventForms?'true':'false') . '});
					ContextMenu.attachclass(' . $names . ',\'' . $this->menuid . '\',' . ($this->showCallback?'\'' . $this->showCallback . '\'':'null') . ',' . ($this->hideCallback?'\'' . $this->hideCallback . '\'':'false') . ');</script>';
		}
		return $res;
	}	
}

class ContextMenuItem{
	var $isdivider = false;
	var $text;
	var $link;
	var $icon;
	var $parent;
	var $id;
	
	function ContextMenuItem($parent,$text,$link=null,$icon=null,$id=null){
		$this->text = $text;
		$this->link = $link;
		$this->icon = $icon;
		$this->parent = $parent;
		$this->id = $id;
		$this->isdivider = empty($text) || $text == '_' || $text == '-';
	}	
	
	function drawMenuItem(){
		if($this->isdivider){
			$res .= '<div class="ctm_divider"><img src="' . $this->parent->iconpath . 'spacer.gif" width="1" height="1"></div>';
		}else{
			$res .= '<div class="ctm_item"';
			if(!empty($this->id)) $res .= ' id="' . $this->id . '"';
			$res .= '>';
			$res .= '<a class="ctm_link" href="' . $this->link . '">';
			if(!empty($this->icon)){
				$res .= '<img class="ctm_icon" src="' . $this->parent->iconpath . $this->icon . '" align="absmiddle" border="0">';
			}
			$res .= $this->text;
			$res .= '</a>';
			$res .= '</div>';
		}
		return $res;
	}
}
?>