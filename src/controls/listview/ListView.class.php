<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');
require_once('Global.lib.php');
require_once('Text.lib.php');

class ListView{
	var $pagestate;
	var $list;
	
	// paging
	var $usepaging = true;
	var $listcount;
	var $totalcount;
	var $liststart = 0;
	var $itemsperpage = 20;
	var $listiscomplete = false;
	var $paginglink = '?start=';
	var $pagingstatename = 'start';
	
	//sorting
	var $lastsort;
	var $sortlink = '?sort=';
	var $sortstatename = 'sort';
	
	// columns
	var $columns = array();
	var $selectioncolumn;
	var $selectionurl = '?id=';
	var $pkindex = 'id';
	
	// Ajax variables
	var $useajax = false;
	var $csspath;
	var $pagestateajaxpath;
	var $javascriptpath;
	var $drawlistview_callback;
	var $targetelement = '_listview';
	
	// miscellaneous
	var $showheaders = true;
	var $showresults = true;
	var $iconpath;
	var $itemdisabled_callback;
	var $itemsforscrollbar = 7;
	var $css = array();
	
	function ListView(){
		// set defaults;
		$this->iconpath = $GLOBALS['webroot'] . 'images/admin/common/';
		$this->pagestateajaxpath = $GLOBALS['webroot'] . 'ajax/PageState.ajax.php';
		$this->javascriptpath = $GLOBALS['documentroot'] . '/controls/listview/ListView.js';
		$this->csspath = $GLOBALS['webroot'] . 'controls/listview/listview.css';
		$this->SetCSSClasses('lvitem','lvheader','lvtable','lvrow','lvaltrow','lvresults','lvpaging');
	}
	
	function InitFromState($state){
		// Ensure that the state object has the start and sort elements
		if(!in_array('sort',array_keys($state->columns))){
			$state->AddStateItem('string','sort');
			$state->SaveToSession();
		}
		if(!in_array('start',array_keys($state->columns))){
			$state->AddStateItem('string','start');
			$state->SaveToSession();
		}
		
		// Identify the relevant methods
		$listfunction = $state->listmethod;
		$countfuntion = $state->countmethod;
		if(file_exists($state->methodfilepath)) require_once($state->methodfilepath);
		
		// Initialise the ListView object
		$this->pagestate = $state;
		$this->lastsort = $state->values['sort'];
		if(is_callable($listfunction)){
			$this->list = call_user_func($listfunction,$state);	
			if(is_callable($countfuntion)){
				$this->resultcount = call_user_func($countfuntion,$state);	
			}else{
				$this->resultcount = count($this->list);
				$this->listiscomplete = true;
			}
		}else{
			$this->list = array();
			$this->resultcount = 0;
		}
		$this->listcount = count($this->list);
		
		// Normalise the start value in the state object
		while($state->values['start'] >= $this->resultcount){
			$state->SetStateItem('start',$state->values['start'] - 20);
			if($state->values['start'] <= 0){
				$state->SetStateItem('start',0);
				break;	
			}
		}
		$this->liststart = $state->values['start'];
	}
	
	function InitFromList($list,$resultcount,$lastsort=null){
		$this->list = $list;
		$this->resultcount = $resultcount;
		$this->lastsort = $lastsort;
		$this->listcount = count($list);
	}
	
	function SetCSSClasses($defaultclass=null,$defaultheaderclass=null,$tableclass=null,$rowclass=null,$altrowclass=null,$resultsclass=null,$pagingclass=null){
		$this->css['defaultclass'] = $defaultclass;
		$this->css['defaultheaderclass'] = $defaultheaderclass;
		$this->css['tableclass'] = $tableclass;
		$this->css['rowclass'] = $rowclass;
		$this->css['altrowclass'] = $altrowclass;
		$this->css['resultsclass'] = $resultsclass;
		$this->css['pagingclass'] = $pagingclass;
	}
	
	function SetCSSClass($classname,$value){
		$this->css[$classname] = $value;	
	}
	
	function SetLinks($selectionurl=null,$sortlink=null,$paginglink=null){
		$this->selectionurl = $selectionurl;
		$this->sortlink = $sortlink;
		$this->paginglink = $paginglink;
	}
	
	function SetAjax($useajax, $drawlistview_callback, $targetelement, $pagestateajaxpath = null, $javascriptpath = null){
		$this->useajax = $useajax;
		$this->drawlistview_callback = $drawlistview_callback;
		$this->targetelement = $targetelement;
		if($pagestateajaxpath!=null) $this->pagestateajaxpath = $pagestateajaxpath;
		if($javascriptpath!=null) $this->javascriptpath = $javascriptpath;
		if($this->useajax){
			require_once($this->javascriptpath);
		}
	}
	
	function AddColumn($name,$itemindex,$width=null,$displaytype=null,$displayformat=null,$alttext=null,$class=null,$headerclass=null,$headerlink=null,$onclick=null){
		if(empty($class)) $class = $this->css['defaultclass'];
		if(empty($headerclass)) $headerclass = $this->css['defaultheaderclass'];
		if(empty($headerlink))	$headerlink = $this->sortlink . urlencode($itemindex . ($itemindex == $this->lastsort?' desc':''));
		if($headerlink == 'none') $headerlink = null;
		$col = new ListViewColumn($this,$name,$itemindex,$class,$headerclass,$width,$displaytype,$headerlink,$displayformat,$onclick,$alttext);
		$this->columns[] = $col;
	}
	
	function DrawListView(){
		if(!empty($this->csspath)) $ret .= '<link rel="StyleSheet" href="'.$this->csspath.'" type="text/css" />';
		if($this->useajax){
			$ret .= '<div id="' . $this->targetelement . '">';
		}
		$ret .= '<table cellspacing="0" cellpadding="0" border="0" class="' . $this->css['tableclass'] . '">';
		// Draw header columns
		if($this->showheaders){
			$ret .= '<tr class="' . $this->css['rowclass'] . '">';
			foreach($this->columns as $col){
				$ret .= $col->DrawListViewColumnHeader();
			}	
			$ret .= '</tr>';
		}
		$alt = false;
		// Calculate start and end indexes
		$this->_calculate_indexes($st,$end,$itemsperpage);
		// Draw actual items
		for($i=$st;$i<$end;$i++){
			$item = $this->list[$i];
			if(!empty($this->itemdisabled_callback) && function_exists($this->itemdisabled_callback)){
				$item['_disabled'] = call_user_func($this->itemdisabled_callback,$item);
			}
			$ret .= $item['_disabled']?$this->DrawDisabledListViewItem($item,$alt):$this->DrawListViewItem($item,$alt);		
			$alt = $item['_disabled']?false:!$alt;
		}
		if($this->showresults || $this->usepaging){
			// Draw bottom row
			$ret .= '<tr><td colspan="' . count($this->columns) . '" class="' . $this->css['resultsclass'] . '">';
			$ret .= '<table cellpadding="0" cellspacing="0" width="100%"><tr>';
			// Draw result count
			if($this->showresults){
				$ret .= $this->DrawResults($itemsperpage);
			}
			// Draw paging
			if($this->usepaging){
				$ret .= $this->DrawPaging($itemsperpage);
			}
			$ret .= '</tr></table></td></tr>';
		}
		$ret .= '</table>';
		if($this->useajax) $ret .= '</div>';
		return $ret;
	}
	
	function DrawScrollingListView($width='100%',$height=200){
		if(!empty($this->csspath)) $ret .= '<link rel="StyleSheet" href="'.$this->csspath.'" type="text/css" />';
		// Calculate start and end indexes
		$this->_calculate_indexes($st,$end,$itemsperpage);
		// Enclose the listview in a div tag, if this is not an AJAX callback
		if($this->useajax && $_REQUEST['aa_cfunc'] != $this->targetelement){
			$ret .= '<div id="' . $this->targetelement . '" style="width:' . $width . ';">';
		}
		// Calculate the width of the header row
		$headerwidth = $width;
		if(substr($width,-1) != '%' && ($end-$st) > $this->itemsforscrollbar) $headerwidth = (intval($width) - 20);
		$ret .= '<table cellspacing="0" cellpadding="0" border="0" class="' . $this->css['tableclass'] . '"  style="width: ' . $headerwidth . 'px;">';
		// Draw header columns
		if($this->showheaders){
			$ret .= '<tr class="' . $this->css['rowclass'] . '">';
			foreach($this->columns as $col){
				$ret .= $col->DrawListViewColumnHeader();
			}
			$ret .= '</tr>';
		}
		
		$ret .= '</table>';
		$ret .= '<div style="overflow: auto; height: ' . $height . 'px;">';		
		$ret .= '<table cellspacing="0" cellpadding="0" border="0" class="' . $this->css['tableclass'] . '">';
		$alt = false;
		// Draw actual items
		for($i=$st;$i<$end;$i++){
			$item = $this->list[$i];
			if(!empty($this->itemdisabled_callback) && function_exists($this->itemdisabled_callback)){
				$item['disabled'] = call_user_func($this->itemdisabled_callback,$item);
			}
			$ret .= $item['disabled']?$this->DrawDisabledListViewItem($item,$alt):$this->DrawListViewItem($item,$alt);		
			$alt = $item['disabled']?false:!$alt;
		}
		$ret .= '</table>';
		$ret .= '</div>';
		$ret .= '<table cellspacing="0" cellpadding="0" border="0" class="' . $this->css['tableclass'] . '">';
		if($this->showresults || $this->usepaging){
			// Draw bottom row
			$ret .= '<tr><td colspan="' . count($this->columns) . '" class="' . $this->css['resultsclass'] . '">';
			$ret .= '<table cellpadding="0" cellspacing="0" width="100%"><tr>';
			// Draw result count
			if($this->showresults){
				$ret .= $this->DrawResults($itemsperpage);
			}
			// Draw paging
			if($this->usepaging){
				$ret .= $this->DrawPaging($itemsperpage);
			}
			$ret .= '</tr></table></td></tr>';
		}
		$ret .= '</table>';
		if($this->useajax && $_REQUEST['aa_cfunc'] != $this->targetelement) $ret .= '</div>';
		return $ret;
	}
	
	function DrawListViewItem($item,$alt){
		$ret = '';
		$ret .= '<tr class="' . ($alt?$this->css['altrowclass']:$this->css['rowclass']) . '">';
		foreach($this->columns as $col){
			$ret .= '<td class="' . $col->class . '"' . (empty($col->width)?'':' style="width:' . $col->width . 'px;"') . '>';
			$ret .= $col->DrawListViewColumnItem($item);
			$ret .= '</td>';
		}
		$ret .= '</tr>';
		return $ret;
	}
	
	function DrawDisabledListViewItem($item,$alt){
		$item['disabled'] = true;
		$ret = '';
		$ret .= '<tr class="disabled" title="This item has been disabled">';
		foreach($this->columns as $col){
			$ret .= '<td class="' . $col->class . ' disabled"' . (empty($col->width)?'':' style="width:' . $col->width . 'px;"') . '>';
			$ret .= $col->DrawListViewColumnItem($item);
			$ret .= '</td>';
		}
		$ret .= '</tr>';
		return $ret;
	}
	
	function _calculate_indexes(&$start,&$end,&$itemsperpage){
		if(empty($this->resultcount)) $this->resultcount = count($this->list);
		if(empty($this->liststart)) $this->liststart = 0;
		$itemsperpage = $this->itemsperpage;
		if($this->usepaging){			
			if(empty($itemsperpage)) $itemsperpage = $this->listcount;
		}else{
			$itemsperpage = $this->listcount;
			$this->listiscomplete = true;
		}
		if($itemsperpage==0){
			$this->liststart = 0;
		}else{
			while($this->liststart >= $this->resultcount && $this->liststart - $itemsperpage >= 0) $this->liststart -= $itemsperpage;
		}
		if($this->listiscomplete){
			$start = $this->liststart;
			$end = $start + $itemsperpage > $this->resultcount?$this->resultcount:$start + $itemsperpage;
		}else{
			$start = 0;
			$end = $this->liststart + $itemsperpage > $this->resultcount?$this->resultcount - $this->liststart:$itemsperpage;
		}
	}
	
	function DrawPaging($itemsperpage){
		if($this->resultcount > $itemsperpage){
			$ret .= '<td align="right" class="' . $this->css['pagingclass'] . '">';
			if($this->liststart > 0){
				$ret .= '<a href="' . $this->GetPagingLink(0) . '"><img src="' . $this->iconpath . 'first.png" border="0" align="absmiddle"></a>';
				$ret .= '<a href="' . $this->GetPagingLink($this->liststart - $itemsperpage) . '"><img src="' . $this->iconpath . 'left.png" border="0" align="absmiddle"></a> ';
			}else{
				$ret .= '<img src="' . $this->iconpath . 'first_ex.png" border="0" align="absmiddle"><img src="' . $this->iconpath . 'left_ex.png" border="0" align="absmiddle"> ';
			}
			
			$page = $this->liststart / $itemsperpage;
			$startpage = $page > 5?$page-5:0;
			for($i=$startpage;$i<$startpage+11;$i++){
				if($i*$this->itemsperpage >= $this->resultcount) break;
				if($i==$page){
					$ret .= '<b>' . ($page+1) . '</b> ';	
				}else{
					$ret .= '<a href="' . $this->GetPagingLink($i * $itemsperpage) . '">' . ($i+1) . '</a> ';
				}
			}
			
			
			if($this->liststart + $itemsperpage < $this->resultcount){
				$ret .= '<a href="' . $this->GetPagingLink($this->liststart + $itemsperpage) . '"><img src="' . $this->iconpath . 'right.png" border="0" align="absmiddle"></a>';
				$md = $this->resultcount % $itemsperpage;
				if($md == 0) $md = $itemsperpage;
				$ret .= '<a href="' . $this->GetPagingLink($this->resultcount - $md) . '"><img src="' . $this->iconpath . 'last.png" border="0" align="absmiddle"></a>';
			}else{
				$ret .= '<img src="' . $this->iconpath . 'right_ex.png" border="0" align="absmiddle"><img src="' . $this->iconpath . 'last_ex.png" border="0" align="absmiddle">';
			}
			$ret .= '</td>';	
		}
		return $ret;
	}
	
	function GetPagingLink($start){
		if($this->useajax){
			return 'javascript:_listview_reload_paging(\'' . $this->pagestate->statename . '\',\'' . $this->pagingstatename . '\',\'' . $start . '\',\'' . $this->drawlistview_callback . '\',\'' . $this->targetelement . '\',\'' . $this->pagestateajaxpath . '\')';
		}else{
			return $this->paginglink . $start;	
		}
	}
	
	function DrawResults($itemsperpage){
		$ret .= '<td>';
		if($this->listcount){
			$ret .= 'Returned: ' . $this->resultcount . ' results, showing ' . ($this->liststart+1) . ' to ' . ($this->liststart + $itemsperpage > $this->resultcount?$this->resultcount:$this->liststart + $itemsperpage);
		}else{
			$ret .= 'No results were returned';
		}
		$ret .= '</td>';
		return $ret;
	}
	
}

?>