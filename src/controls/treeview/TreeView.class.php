<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');
class TreeView{
	
	// {{{ Variable Declarations
	public $rootnode = array();
	// attribute names:
	protected $attributes = array(
		'id'=>'id',
		'text'=>'title',
		'link'=>'link',
		'disabled'=>'disabled',
		'disablelink'=>'disablelink',
		'selectedlink'=>'selectedlink',
		'icon'=>'icon',
		'disabledicon'=>'disabledicon',
		'children'=>'children',
		'properties'=>'properties',
		'childcount'=>'childcount',
		'itemindex'=>'itemindex'
		);
	// class values
	protected $classes = array();
	// callback functions. These are called for each item, and passed the item as a parameter.
	protected $callbacks = array(
		'link'=>null,
		'icon'=>null,
		'include'=>null,
		'disable'=>null
		);	
	// path variables - contain paths to all required files.
	protected $paths = array(
		'javascript'=>'controls/treeview/TreeView.js',
		'base64script'=>'js/base64.js',
		'images'=>'controls/treeview/icons/',
		'css'=>'controls/treeview/treeview.css',
		'ajax'=>'controls/treeview/TreeView.ajax.php'
		);	
	
	public $selectedid;
	public $expandlevel = 2;
	public $showselectedchildren = true;
	public $useselectediconinparents = true;
	public $useclientselection = false;
	public $useajax = false;
	public $usecontextmenu = false;
	public $usedragdrop = true;
	public $usepaging = false;
	public $usesearch = false;
	public $ajaxloadbranchmethod = '';
	public $ajaxloadsectionmethod = '';
	public $ajaxperformsearchmethod = '';
	public $ajaxcountbranchmethod = '';
	public $ajaxpocket;
	public $internalajaxloadbranchmethod = 'drawTreeBranch';
	public $internalajaxloadsectionmethod = 'drawTreeSection';
	public $internalperformsearchmethod = 'performTreeSearch';
	public $showroot = true;
	public $selectediconmap = array();
	public $contextmenushowcallback;
	public $contextmenuhidecallback;
	public $dragdropstartcallback;	
	public $dragdropendcallback;	
	public $sectionsize = 10;
	public $searchpathseparator = '|';
	
	
	protected $islasts;
	public $prefix = 'trv';
	public $treeviewname = 'trv_treeview';
	protected $uniqueid;
	protected $contextmenu;
	protected $contextmenulinks;
	#endregion
	
	// {{{ Constructors and Setup
	public function __construct($rootnode,$prefix='trv'){
		$this->rootnode = $rootnode;
		$this->prefix = $prefix;
		$this->treeviewname = $this->prefix . '_treeview';
		foreach($this->paths as &$value) $value = $GLOBALS['webroot'] . $value;
		
		$this->classes = array(
				'loading' => $prefix.'_loading',
				'selected' => $prefix.'_selected',
				'item' => $prefix.'_item',
				'branch' => $prefix.'_branch',
				'highlight' => $prefix.'_highlight',
				'disabled' => $prefix.'_disabled',
				'line' => $prefix.'_line',
				'icon' => $prefix.'_icon',
				'shadow' => $prefix.'_shadow',
				'nextsection' => $prefix.'_nextsection',
				'searchcontainer' => $prefix.'_searchcontainer',
				'searchbox' => $prefix.'_searchbox',
				'searchbutton' => $prefix.'_searchbutton',
				'searchresultcontainer' => $prefix.'_searchresultcontainer',
				'section' => $prefix.'_section'
				);
		$this->selectediconmap = array(
				'tree_menu.gif'=>'tree_menuopen.gif',
				'tree_folder.gif'=>'tree_folderopen.gif',
				'tree_page.gif'=>'tree_page_active.gif'
				);
	}
	
	public function setAjax($path,$loadbranch,$loadsection=null,$countbranch=null){
		$this->paths['ajax'] = $path;
		$this->ajaxloadbranchmethod = $loadbranch;
		$this->ajaxloadsectionmethod = $loadsection;
		$this->ajaxcountbranchmethod = $countbranch;
		$this->useajax = true;
	}
	
	public function setSearch($searchmethod,$searchpathseparator='|'){
		$this->usesearch = true;
		$this->ajaxperformsearchmethod = $searchmethod;
		$this->searchpathseparator = $searchpathseparator;
	}
	#endregion
	
	// {{{ Draw Tree View
	public function drawTree(){
		if(!$GLOBALS['treeview_scriptincluded']){
			$prescript .= '<script language="javascript" src="' . $this->paths['javascript'] . '"></script>';
			if($this->useajax) $prescript .= '<script language="javascript" src="' . $this->paths['base64script'] . '"></script>';
			if(!empty($this->paths['css'])) $prescript .= '<link rel="stylesheet" href="' . $this->paths['css'] . '" />';
			$GLOBALS['treeview_scriptincluded'] = true;
		}
		$this->islasts = array();
		$this->uniqueid = 0;
		if($this->usesearch) $code .= $this->drawSearch();
		$code .= '<div id="' . $this->prefix . '_container" style="position:relative;">';
		$code .= '<div id="' . $this->prefix . '_loading" class="'.$this->classes['loading'].'" style="width: 100%; height:100%; position: absolute; top:0px; left:0px; display: none;"><table cellpadding="0" cellspacing="0" width="100%" height="100%"><tr><td align="center"><img src="' . $this->paths['images'] . 'loader.gif" /></td></tr></table></div>';
		$code .= $this->drawItemTree($this->rootnode,1,false);
		$code .= '</div>';
		$postscript .= '<script language="javascript">' . $this->treeviewname . ' = new TreeView(\'' . $this->treeviewname . '\');';
		if($this->useclientselection){
			$selmap = '';
			foreach($this->selectediconmap as $key=>$val){
				if(!empty($selmap)) $selmap .= ',';
				$selmap .= "'" . $key . "':'" . $val . "'";	
			}
			$postscript .= '' . $this->treeviewname . '.ClientSelectionInit(\'' . $this->paths['images'] . '\',{' . $selmap . '},' . $this->uniqueid . ',' . ($this->showroot?'true':'false') . ',' . ($this->useselectediconinparents?'true':'false') . ',' . ($this->usedragdrop?'true':'false') . ',' . ($this->usepaging?'true':'false') . ',' . (empty($this->contextmenushowcallback)?'null':'\''.$this->contextmenushowcallback.'\'') . ',' . (empty($this->contextmenuhidecallback)?'null':'\''.$this->contextmenuhidecallback.'\'') . ',' . (empty($this->dragdropstartcallback)?'null':'\''.$this->dragdropstartcallback.'\'') . ',' . (empty($this->dragdropendcallback)?'null':'\''.$this->dragdropendcallback.'\'') . ',' . $this->uniqueid . ',' . $this->sectionsize . ');' . $this->treeviewname . '.LoadItems();';
			if($this->useajax){
				$postscript .= '' . $this->treeviewname . '.AjaxInit(\'' . get_class($this) . '\',\'' . $this->paths['ajax'] . '\',\'' . $this->internalajaxloadbranchmethod . '\',\'' . $this->internalajaxloadsectionmethod . '\',\'' . $this->internalperformsearchmethod . '\',\'' . $this->ajaxpocket . '\');';	
			}
		}
		$postscript .= '</script>';
		$context = $this->drawContextMenu();
		return $prescript.$code.$postscript.$context;
	}
	
	protected function drawItemTree($item,$level,$islast){
		$res = '';
		if(!function_exists($this->callbacks['include']) || call_user_func($this->callbacks['include'],$item)){
			$disable = $item[$this->attributes['disabled']]?true:false;
			if(function_exists($this->callbacks['disable'])) $disable =  call_user_func($this->callbacks['disable'],$item);
			$childblock = false;
			if($level > 1 && (count($item[$this->attributes['children']]) > 0 || $item[$this->attributes['childcount']])){
				$childblock = true;
			}
			if($this->showroot || $item != $this->rootnode){
				$item['clientid'] = ++$this->uniqueid;
				$res = '<div id="' . $this->prefix . '_itm' . $item['clientid'] . '">';
				$res .= $this->drawItem($item,$level,$islast,$childblock,$disable);
			}else $level --;
			$this->islasts[$level] = $islast;	
			$ch = $this->attributes['children'];
			$cc = $this->attributes['childcount'];
			$ii = $this->attributes['itemindex'];
			if(!empty($item[$ch]) || $item[$cc]){
				$display = $level >= $this->expandlevel && !$this->itemContainsSelectedId($item)  && !($this->showselectedchildren && $item['id']==$this->selectedid)?'none':'block';
				$res .= '<div id="' . $this->prefix . '_br' . $item['clientid'] . '" class="'.$this->classes['branch'].'" style="display:' . $display . ';">';
				$i=0;
				if(!empty($item[$ch])){
					if($this->usepaging && $item[$ch][0][$ii] > 1){
						$res .= $this->drawLoadSection($item['clientid'],$level + 1,$item['childcount'],0,$item[$ch][0][$ii]);
					}
					$lastindex = $item[$ch][0][$ii] - 1;
					for(;$i<count($item[$ch]);$i++){
						$subitem = $item[$ch][$i];
						if($this->usepaging && $subitem[$ii] != $lastindex+1){
							$res .= $this->drawLoadSection($item['clientid'],$level + 1,$item['childcount'],$lastindex + 1,$subitem[$ii]);
						}
						$islast = $item[$ch][count($item[$ch])-1] == $subitem;
						$res .= $this->drawItemTree($subitem,$level + 1,$islast);	
						$lastindex = $subitem[$ii];
					}
					if($this->usepaging && $i<$item[$cc]){
						$res .= $this->drawLoadSection($item['clientid'],$level + 1,$item['childcount'],$subitem[$ii] + 1,$item[$cc]);	
					}
				}
				$res .= '</div>';
			}
		}
		if($this->showroot || $item != $this->rootnode)	$res .= "</div>\r\n";
		return $res;
	}
	
	protected function drawItem($item, $level, $islast, $childblock, $disable = false){
		$res = '';
		$item['_treeview'] = $this;
		$class = $disable?$this->classes['disabled']:($this->selectedid==$item[$this->attributes['id']]?$this->classes['selected']:$this->classes['item']);
		// create the containing div tag
		$res .= '<div id="' . $this->prefix . '_div' . $item['clientid'] . '" class="' . $class . '"';
		// add properties and other info if needed
		if($this->useclientselection){
			$res .= ' objid="' . $item[$this->attributes['id']] . '" props="'.$item[$this->attributes['properties']].'"';
			$res .= ' link="' . (empty($item[$this->attributes['link']])?'javascript:;':$item[$this->attributes['link']]) . '"';
			$res .= ' slink="'.(empty($item[$this->attributes['selectedlink']])?'javascript:;':$item[$this->attributes['selectedlink']]).'"';
			$res .= ' dlink="'.(empty($item[$this->attributes['disabledlink']])?'javascript:;':$item[$this->attributes['disabledlink']]).'"';
			$res .= ' icon="' . $item[$this->attributes['icon']] . '" dicon="'.$item[$this->attributes['disabledicon']].'"';
		}
		$res .= '><nobr>';
		for($i=2;$i<$level;$i++){
			if($this->islasts[$i]){
				$res .= '<span class="' . $this->classes['line'] . '"><img src="' . $this->paths['images'] . 'spacer.gif" width="16" height="16" align="bottom"></span>';			
			}else{
				$res .= '<span class="' . $this->classes['line'] . '"><img src="' . $this->paths['images'] . 'tree_back.gif" align="bottom"></span>';
			}
		}
		$itemContainsSelectedId = $this->itemContainsSelectedId($item);
		if($level > 1){
			$imgname = ($islast?'bot':'mid');
			if($childblock) {
				$exp = ($level >= $this->expandlevel && !$itemContainsSelectedId && !($this->showselectedchildren && $item[$this->attributes['id']]==$this->selectedid));
				$imgname .= ($exp?'_expand':'_collapse');
				$res .= '<a href="javascript:' . ($exp?$this->treeviewname . '.Expand':$this->treeviewname . '.Collapse') . '(\'' . $this->prefix . '\',\'' . $item['clientid'] . '\');" id="' . $this->prefix . '_lnk' . $item['clientid'] . '" class="' . $this->classes['line'] . '">';
				$res .= '<img src="' . $this->paths['images'] . 'tree_' . $imgname . '.gif" align="bottom" id="' . $this->prefix . '_img' . $item['clientid'] . '" border="0">';
				$res .= '</a>';
			}else{
				$res .= '<span class="' . $this->classes['line'] . '">';	
				$res .= '<img src="' . $this->paths['images'] . 'tree_' . $imgname . '.gif" align="bottom" id="' . $this->prefix . '_img' . $item['clientid'] . '" border="0">';
				$res .= '</span>';
			}
		}
		// Insert Item Icon
		if(function_exists($this->callbacks['icon'])){
			$iconfile = call_user_func($this->callbacks['icon'],$item);
			$res .= '<span id="' . $this->prefix . '_ico' . $item['clientid'] . '" style="background-image: url(\'' . $this->paths['images'] . $iconfile . '\');" class="' . $this->classes['icon'] . '"></span>';
		}elseif(!empty($item['icon'])){
			if($disable && !empty($item[$this->attributes['disableicon']])){
				$iconfile =  $item[$this->attributes['disableicon']];
			}elseif(($this->selectedid==$item[$this->attributes['id']] || ($this->useselectediconinparents && $itemContainsSelectedId)) && isset($this->selectediconmap[$item[$this->attributes['icon']]])){
				$iconfile =  $this->selectediconmap[$item[$this->attributes['icon']]];
			}else{
				$iconfile = $item[$this->attributes['icon']];
			}
			$res .= '<span id="' . $this->prefix . '_ico' . $item['clientid'] . '" style="background-image: url(\'' . $this->paths['images'] . $iconfile . '\');" class="' . $this->classes['icon'] . '"></span>';
		}else{
			$res .= '<span id="' . $this->prefix . '_ico' . $item['clientid'] . '" class="' . $this->classes['icon'] . '"></span>';
		}
		// Insert item link
		$addlink = !($disable && empty($this->attributes['disablelink'])) && (!empty($item[$this->attributes['link']]) || function_exists($this->callbacks['link']));
		if($addlink){
			if(function_exists($this->callbacks['link'])){
				$link = call_user_func($this->callbacks['link'],$item);
			}elseif(!empty($this->attributes['link'])){
				if($disable){
					$link =  $item[$this->attributes['disablelink']];
				}elseif($this->selectedid==$item[$this->attributes['id']] && isset($this->attributes['selectedlink'])){
					$link = $item[$this->attributes['selectedlink']];
				}else{
					$link = $item[$this->attributes['link']];
				}
			}			
			if(empty($link) && $this->useclientselection) $link = 'javascript:;';
			if(!empty($link)){
				$res .= '<a id="' . $this->prefix . '_act' . $item['clientid'] . '" href="' . $link . '"';
				if($this->useclientselection && !$disable){
					$res .= ' onclick="' . $this->treeviewname . '.Select(\'' . $item['clientid'] . '\')"';	
				}
				$res .= '>';
				$linkcloser = '</a>';
			}
		}
		$res .= ' &nbsp;' . $item[$this->attributes['text']];
		if($linkcloser){
			$res .= $linkcloser;
		}
		$res .= "</nobr></div>\r\n";
		return $res;
	}
	
	protected function drawSearch(){
		$res .= '<div class="'.$this->classes['searchcontainer'].'">';
		$res .= '<input type="text" name="' . $this->prefix . '_search" id="' . $this->prefix . '_search" class="'.$this->classes['searchbox'].'" /> <input type="button" name="' . $this->prefix . '_searchbutton" value="Search" class="'.$this->classes['searchbutton'].'" onclick="' . $this->treeviewname . '.PerformSearch()" />';
		$res .= '<div id="'.$this->prefix.'_searchresultcontainer" class="'.$this->classes['searchresultcontainer'].'" style="display: none"></div>';
		$res .= '</div>';
		return $res;
	}
	#endregion
	
	// {{{ Load Section Drawing Methods	
	protected function drawLoadSection($clientid, $level, $total, $start, $end){
		$startpage = floor($start / $this->sectionsize) + 1;
		$endpage = ceil($end / $this->sectionsize);
		$divid = $this->prefix . '_pag' . $clientid . '_' . $start;
		$res .= '<div id="' . $divid . '" class="'.$this->classes['section'].'" startpage="'.$startpage.'" endpage="'.$endpage.'"><nobr>';
		for($i=2;$i<$level;$i++){
			if($this->islasts[$i]){
				$res .= '<span class="' . $this->classes['line'] . '"><img src="' . $this->paths['images'] . 'spacer.gif" width="16" height="16" align="top"></span>';			
			}else{
				$res .= '<span class="' . $this->classes['line'] . '"><img src="' . $this->paths['images'] . 'tree_back.gif" align="top"></span>';
			}
		}
		if($end >= $total){
			$res .= '<span class="' . $this->classes['line'] . '"><img src="' . $this->paths['images'] . 'spacer.gif" width="16" height="16" align="top"></span>';
		}else{
			$res .= '<span class="' . $this->classes['line'] . '"><img src="' . $this->paths['images'] . 'tree_back.gif" align="top"></span>';
		}
		$res .= '<span id="' . $this->prefix . '_pab' . $clientid . '_' . $start . '" class="'.$this->classes['nextsection'].'">';
		// draw paging
		for($page = $startpage;$page<=$endpage;$page++){
			$title = 'View Records: ' . (($page-1) * $this->sectionsize) . ' to ' . ($page * $this->sectionsize - 1);
			$res .= '<a href="javascript:'.$this->treeviewname.'.OpenSection(\'' . $this->prefix . '\',\''.$clientid.'\',\''.$clientid . '_' . $start.'\','.$page.');" title="'.$title.'">&nbsp;'.$page.'&nbsp;</a>';
			if($page != $endpage) $res .= '|';
		}
		$res .= '</span></nobr></div>';
		return $res;
	}
	#endregion
	
	// {{{ AJAX Methods for drawing branches and sections	
	public function drawTreeBranch($treeviewname,$pocket,$uniqueid,$itemid,$clientitemid,$props,$level,$islasts){
		try{
			$this->ajaxpocket = $pocket;
			$children = call_user_func(array($this,$this->ajaxloadbranchmethod),$uniqueid,$itemid,$props,$level,$islasts);
			$this->treeviewname = $this->prefix . '_treeview';
			$this->uniqueid = $uniqueid;
			$content = '';
			$this->islasts = $islasts;
			if($children){
				foreach($children as $child){
					$islast = $children[count($children)-1] == $child;
					$content .= $this->drawItemTree($child,$level + 1,$islast);
				}
			}
			$res['lastuniqueid'] = $this->uniqueid;
			$res['content'] = base64_encode($content);
			$res['error'] = 0;
			$res['itemid'] = $itemid;
			$res['treeviewname'] = $treeviewname;
			$res['clientitemid'] = $clientitemid;
			return $res;
		}catch(exception $err){
			$res['error'] = $err.getMessage();
			return $res;
		}
	}
	
	public function drawTreeSection($treeviewname,$pocket,$uniqueid,$clientparentid,$parentid,$props,$level,$pageid,$page,$startpage,$endpage,$fetchcount,$islasts){
		try{			
			$this->ajaxpocket = $pocket;
			$children = call_user_func($this->ajaxloadsectionmethod,$uniqueid,$clientparentid,$parentid,$props,$level,$pageid,$page,$startpage,$endpage,$fetchcount,$islasts,$this->sectionsize);
			$this->treeviewname = $this->prefix . '_treeview';
			$this->uniqueid = $uniqueid;
			$content = '';
			$this->islasts = $islasts;
			$total = call_user_func($this->ajaxcountbranchmethod,$pocket,$parentid);
			if($page > $startpage){
				$start = ($startpage-1) * $this->sectionsize;
				$end = ($page-1) * $this->sectionsize - 1;
				$content .= $this->drawLoadSection($clientparentid,$level+1,$total,$start,$end);
			}
			if($children){
				foreach($children as $child){
					// it does not matter whether this item is last or not - its expander image will be reset on the client side
					$content .= $this->drawItemTree($child,$level + 1,false);
				}
			}
			if($page < $endpage){
				$start = $page * $this->sectionsize;
				$end = $endpage * $this->sectionsize;
				$content .= $this->drawLoadSection($clientparentid,$level+1,$total,$start,$end);
			}
			$res['lastuniqueid'] = $this->uniqueid;
			$res['content'] = base64_encode($content);
			$res['error'] = 0;
			$res['itemid'] = $itemid;
			$res['treeviewname'] = $treeviewname;
			$res['clientparentid'] = $clientparentid;
			$res['pageid'] = $pageid;
			return $res;
		}catch(exception $err){
			$res['error'] = $err.getMessage();
			return $res;	
		}
	}
	
	public function performTreeSearch($treeviewname,$pocket,$searchstring){
		try{
			$this->ajaxpocket = $pocket;
			$results = call_user_func($this->ajaxperformsearchmethod,$searchstring,$this->searchpathseparator);
			if($results === false){
				$res['error'] = 'There was an error performing the search';
				$res['treeviewname'] = $treeviewname;
				return $res;	
			}
			$content = '';
			foreach($results as &$result){
				$result['descriptions'] = base64_encode($result['descriptions']);
			}
			$res['results'] = $results;
			$res['separator'] = $this->searchpathseparator;
			$res['error'] = 0;
			$res['treeviewname'] = $treeviewname;
			return $res;
		}catch(exception $err){
			$res['error'] = $err.getMessage();
			$res['treeviewname'] = $treeviewname;
			return $res;	
		}
	}
	#endregion
	
	// {{{ Internal Check Methods
	protected function itemContainsSelectedId($item){
		if(is_array($item[$this->attributes['children']]) && count($item[$this->attributes['children']])){
			foreach($item[$this->attributes['children']] as $child){
				if($child[$this->attributes['id']] == $this->selectedid || $this->itemContainsSelectedId($child)) return true;
			}
		}
		return false;
	}
	#endregion
	
	// {{{ Public methods to set protected array values. 
	// These methods protect the internal arrays, and insure that the user cannot delete required information. 
	public function SetAttributeName($attribute,$value){
		$this->attributes[$attribute] = $value;
	}
	
	public function SetClass($class,$value){
		$this->classes[$class] = $value;
	}
	
	public function SetCallback($callback,$value){
		$this->callbacks[$callback] = $value;
	}
	
	public function SetPath($path,$value){
		$this->paths[$path] = $value;
	}
	#endregion
	
	// {{{ Context Menu Methods
	public function addContextMenuOption($text,$onclick=null,$icon=null,$id=null){
		if(empty($this->contextmenu)){
			$this->contextmenu = new ContextMenu($this->prefix.'ctx');	
			$this->contextmenu->preventDefault = false;
			$this->contextmenu->iconpath = $this->paths['images'];
			$this->contextmenu->setClassNames(array($this->classes['item'],$this->classes['selected']));
			$this->contextmenu->showCallback = $this->treeviewname.'.ContextMenuShow();';
			$this->contextmenu->hideCallback = $this->treeviewname.'.ContextMenuHide();';
			$this->contextmenulinks = array();
		}
		$this->contextmenu->addMenuOption($text,'javascript:'.$this->treeviewname.'.ContextMenuClicked(\'' . $id . '\');',$icon,$id);
		$this->contextmenulinks[$id] = $onclick;
	}
	
	protected function drawContextMenu(){
		$res = '';
		if($this->usecontextmenu && !empty($this->contextmenu)){
			$res = $this->contextmenu->drawContextMenu();
			$res .= '<script language="javascript">';
			$res .= $this->treeviewname . '.contextmenulinks = {';
			foreach($this->contextmenulinks as $id=>$link){
				$res .= '\'' . $id . '\':\'' . $link . '\',';
			}
			$res = trim($res,',');
			$res .= '};';
			$res .= '</script>';
			
		}
		return $res;	
	}
	#endregion
	
}

?>