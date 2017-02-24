<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

require_once('Text.lib.php');

class ListViewColumn{
	
	var $parent;
	var $name;
	var $itemindex;
	var $class;
	var $headerclass;
	var $displaytype;
	var $displayformat;
	var $headerlink;
	var $width;
	var $onclick;
	var $alt;
	
	function ListViewColumn($parent,$name,$itemindex,$class,$headerclass,$width=null,$displaytype=null,$headerlink=null,$displayformat=null,$onclick=null,$alt=null){
		$this->parent = $parent;
		$this->name = $name;
		$this->itemindex = $itemindex;
		$this->class = $class;
		$this->headerclass = $headerclass;
		$this->width = $width;
		$this->displaytype = $displaytype;
		$this->headerlink = $headerlink;	
		$this->displayformat = $displayformat;
		$this->onclick = $onclick;
		$this->alt = $alt;
	}
	
	function DrawListViewColumnItem($item){
		$ret = '';
		if(!empty($this->displaytype) && function_exists($this->displaytype)){
			$ret .= call_user_func($this->displaytype,$item);
		}else{
			$onclick = preg_replace('|\$(\w+)|e',"\$item['\\1']",$this->onclick);
			$onclick = empty($onclick)?'':' onclick="' . $onclick . '"';
			$title = preg_replace('|\$(\w+)|e',"\$item['\\1']",$this->alt);
			$title = empty($title)?'':' title="' . $title . '"';
			switch($this->displaytype){
				case 'lv_delete':
					if(empty($this->alt)) $this->alt = "Delete this item";
					$ret .= '<a href="' . $this->displayformat . $item[$this->parent->pkindex] . '"' . $onclick . '><img src="' . $this->parent->iconpath . 'delete.gif" border="0" alt="' . $this->alt . '"></a>';
					break;
				case 'lv_textbox':
					$width = $this->width?' style="width:' . $this->width . 'px;"':'';
					$disabled = $item['disabled']?' disabled':'';
					if(empty($this->displayformat)) $this->displayformat = $this->itemindex;
					$ret .= '<input type="text" name="' . $this->displayformat . '[' . $item[$this->parent->pkindex] . ']" id="' . $this->displayformat . '_' . $item[$this->parent->pkindex] . '" value="' . $item[$this->itemindex] . '"' . $width . $disabled . $onclick . $title . '>';
					break;
				case 'lv_checkbox':
					$checked = $item[$this->itemindex]?' checked':'';
					$disabled = $item['disabled']?' disabled':'';
					if(empty($this->displayformat)) $this->displayformat = $this->itemindex;
					$ret .= '<input type="checkbox" name="' . $this->displayformat . '[]" value="' . $item[$this->parent->pkindex] . '" id="' . $this->displayformat . '_' . $item[$this->parent->pkindex] . '"' . $checked . $disabled . $onclick . $title . '>';
					break;
				case 'lv_date':
					if(empty($this->displayformat)) $this->displayformat = 'd M Y';
					if($this->itemindex == $this->parent->selectioncolumn){
						$ret .= '<a id="' . $this->itemindex . '_' . $item[$this->parent->pkindex] . '" href="' . $this->parent->selectionurl . $item[$this->parent->pkindex] . '"' . $onclick . $title . '>';	
					}elseif(!empty($onclick) || !empty($title)){
						$ret .= '<span id="' . $this->name . '_' . $item[$this->parent->pkindex] . '" ' . $onclick . $title . '>';	
					}
					$date = is_numeric($item[$this->itemindex])?$item[$this->itemindex]:strtodate($item[$this->itemindex]);
					$ret .= empty($date)?$item[$this->itemindex]:date($this->displayformat,$date);
					if($this->itemindex == $this->parent->selectioncolumn){
						$ret .= '</a>';
					}elseif(!empty($onclick) || !empty($title)){
						$ret .= '</span>';	
					}	
					break;
				case 'lv_currency':
					if(!empty($onclick) || !empty($title)){
						$ret .= '<span id="' . $this->itemindex . '_' . $item[$this->parent->pkindex] . '" ' . $onclick . $title . '>';	
					}
					$ret .= 'R' . number_format($item[$this->itemindex],2,'.','');
					if(!empty($onclick) || !empty($title)){
						$ret .= '</span>';	
					}
					break;
				case 'lv_float':
					if(empty($this->displayformat)) $this->displayformat = 2;		
					if(!empty($onclick) || !empty($title)){
						$ret .= '<span id="' . $this->itemindex . '_' . $item[$this->parent->pkindex] . '" ' . $onclick . $title . '>';	
					}			
					$ret .= number_format($item[$this->itemindex],$this->displayformat,'.','');
					if(!empty($onclick) || !empty($title)){
						$ret .= '</span>';	
					}
					break;
				case 'lv_icon':		
					if($item[$this->itemindex]){
						if(!empty($onclick) || !empty($title)){
							$ret .= '<span id="' . $this->itemindex . '_' . $item[$this->parent->pkindex] . '" ' . $onclick . $title . '>';	
						}
						$ret .= '<img src="' . $this->parent->iconpath . $this->displayformat . '">';
						if(!empty($onclick) || !empty($title)){
							$ret .= '</span>';	
						}
					}
					break;
				case 'lv_email';
					// apply a link if needed, with a title and/or onclick.
					if(!empty($item[$this->parent->pkindex])){
						$link = 'mailto:' . $item[$this->itemindex];
					}
					$ret .= '<a id="' . $this->itemindex . '_' . $item[$this->parent->pkindex] . '" href="' . $link . '"' . $onclick . $title . '>';
					// set actual text, and apply a displayformat if needed
					$ret .= $item[$this->itemindex];
					// close link or span tag
					$ret .= '</a>';	
					break;
				case 'lv_emailicon';
					// apply a link if needed, with a title and/or onclick.
					if(!empty($item[$this->parent->pkindex])){
						$link = 'mailto:' . $item[$this->itemindex];
					}
					$ret .= '<a id="' . $this->itemindex . '_' . $item[$this->parent->pkindex] . '" href="' . $link . '"' . $onclick . $title . '>';
					// set actual icon, and apply a displayformat if needed
					if(!empty($this->displayformat)) $path = $this->parent->iconpath . $this->displayformat;
					else $path = $this->parent->iconpath . 'email.gif';
					$ret .= '<img src="' . $path . '" border="0" align="absmiddle" alt="Email ' . (empty($item['name'])?'User':$item['name']) . '">';
					// close link or span tag
					$ret .= '</a>';	
					break;
				default;
					// apply a link if needed, with a title and/or onclick.
					if($this->itemindex == $this->parent->selectioncolumn){
						$ret .= '<a id="' . $this->itemindex . '_' . $item[$this->parent->pkindex] . '" href="' . $this->parent->selectionurl . $item[$this->parent->pkindex] . '"' . $onclick . $title . '>';
					// otherwise enclose in a span to hold either a title or onclick method
					}elseif(!empty($onclick) || !empty($title)){
						$ret .= '<span ' . $onclick . $title . '>';	
					}
					// set actual text, and apply a displayformat if needed
					$text = $item[$this->itemindex];
					if(function_exists($this->displayformat)){
						$text = call_user_func($this->displayformat,$text);
					}
					$ret .= $text;
					// close link or span tag
					if($this->itemindex == $this->parent->selectioncolumn){
						$ret .= '</a>';	
					}elseif(!empty($onclick) || !empty($title)){
						$ret .= '</span>';	
					}
					break;
			}
		}
		return $ret;
	}
	
	function DrawListViewColumnHeader(){
		$par = $this->parent;
		$state = $par->pagestate;
		$ret .= '<td class="' . $this->headerclass . '"' . (empty($this->width)?'':' style="width:' . $this->width . 'px;"') . '>';
		if($par->useajax && preg_match('|' . $par->sortstatename . '=([^\&]*)|si',$this->headerlink,$matches)){
			$sortvalue = $matches[1];
			$ret .= '<a href="javascript:_listview_reload_sort(\'' . $state->statename . '\',\'' . $par->sortstatename . '\',\'' . $sortvalue . '\',\'' . $par->drawlistview_callback . '\',\'' . $par->targetelement . '\',\'' . $par->pagestateajaxpath . '\')">';
			$ret .= $this->name;	
			$ret .= '</a>';
		}else{
			if(!empty($this->headerlink)) $ret .= '<a href="' . $this->headerlink . '">';
			$ret .= $this->name;	
			if(!empty($this->headerlink)) $ret .= '</a>';
		}
		return $ret;
		$ret .= '</td>';
	}
}

?>