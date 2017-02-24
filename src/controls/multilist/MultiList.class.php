<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');
require_once('Global.lib.php');
require_once('Text.lib.php');

class MultiList{
	
	public $id;
	public $list;
	public $selected;
	public $singlename;
	public $pluralname;
	public $width;
	public $labelwidth;
	public $idkey;
	public $namekey;
	
	public function __construct($id, $list, $selected, $singlename, $pluralname=null, $width=250, $labelwidth=100, $idkey='id', $namekey='name'){
		$this->id = $id;
		$this->list = $list;
		$this->selected = $selected;
		$this->singlename = $singlename;
		$this->pluralname = empty($pluralname)?$singlename.'s':$pluralname;
		$this->width = $width;
		$this->labelwidth = $labelwidth;
		$this->idkey = $idkey;
		$this->namekey = $namekey;
	}
	
	public function drawMultiList($includelabel=true){
		if(!isset($GLOBALS['MultiListScriptLoaded'])){
			$res .= '<script language="javascript" src="'.$GLOBALS['webroot'].'controls/multilist/MultiList.js"></script>';
			$GLOBALS['MultiListScriptLoaded'] = true;
		}
		
		if(is_array($this->selected) && count($this->selected) && is_array($this->selected[0]) && isset($this->selected[0][$this->idkey])){
			$arr = array();
			foreach($this->selected as $sel){
				$arr[] = $sel[$this->idkey];
			}
			$this->selected = $arr;
		}
		
		$res .= '<table cellpadding="0" cellspacing="0" width="100%">';
		$res .= '<tr>';
		if($includelabel) $res .= '<td class="label_right" style="width: '.$this->labelwidth.'px;">'.ucwords($this->pluralname).':</td>';
		$res .= '<td class="field"><select name="'.$this->id.'_source" id="'.$this->id.'_source" style="width: '.$this->width.'px;" onchange="addMultiListItem(this,\''.$id.'\')">';
		$connector = in_array(substr($this->singlename,0,1),array('a','e','i','o','u'))?'an':'a';
		$res .= '<option value="-1">select '.$connector.' '.$this->singlename.'</option>';	
		if(is_array($this->list)){
			foreach($this->list as $item){
				if(!is_array($this->selected) || !in_array($item[$this->idkey],$this->selected)){
					$res .= '<option value="' . $item[$this->idkey] . '">' . $item[$this->namekey] . '</option>';	
				}
			}
		}
		$res .= '</select></td></tr>';
		$res .= '<tr>';
		if($includelabel) $res .= '<td class="note">Select '.$this->pluralname.' from the drop down to add them to this list.</td>';
		$res .= '<td class="field" valign="top"><select name="'.$this->id.'_list" id="'.$this->id.'_list" style="width: '.$this->width.'px;height: 100px;" multiple>';
		if(is_array($this->list)){
			foreach($this->list as $item){
				if(is_array($this->selected) && in_array($item[$this->idkey],$this->selected)){
					$res .= '<option value="' . $item[$this->idkey] . '">' . $item[$this->namekey] . '</option>';	
					$suffix .= '<input type="hidden" name="'.$this->id.'[]" id="'.$this->id.'_'.$item[$this->idkey].'" value="'.$item[$this->idkey].'" />';
				}
			}
		}
		$res .= '</select>';
		$res .= '<div style="font-size: 0.8em"><a href="javascript:removeMultiListItems(\''.$this->id.'\')">remove selected</a></div>';
		$res .= '</td></tr></table>';
		$res .= $suffix;
		return $res;	
	}
	
	public function drawSingleList($shownone=false){
		$res .= '<table cellpadding="0" cellspacing="0" width="100%">';
		$res .= '<tr><td class="label_right" style="width: '.$this->labelwidth.'px;">'.ucwords($this->singlename).':</td>';
		$res .= '<td class="field"><select name="'.$this->id.'" id="'.$this->id.'" style="width: '.$this->width.'px;">';
		if($shownone){
			$connector = in_array(substr($this->singlename,0,1),array('a','e','i','o','u'))?'an':'a';
			$res .= '<option value="-1">select '.$connector.' '.$this->singlename.'</option>';	
		}
		$selected = is_array($this->selected)?$this->selected[0]:$this->selected;
		foreach($this->list as $item){
			$sel = $item[$this->idkey]==$selected?' selected':'';
			$res .= '<option value="' . $item[$this->idkey] . '"'.$sel.'>' . $item[$this->namekey] . '</option>';	
		}
		$res .= '</select>';
		$res .= '</td></tr></table>';
		return $res;	
	}
	
}

?>