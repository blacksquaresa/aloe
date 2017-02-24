<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

require_once('Text.lib.php');
require_once('HTMPaths.lib.php');
require_once('Images.lib.php');

function getNewsItem($id){
	if(empty($id) || !is_numeric($id)) $id = 0;
	$itm = $GLOBALS['db']->selectrow("select * from news n where n.id = $id");
	if($itm && count($itm)){
		$itm['categories'] = $GLOBALS['db']->select("select c.* from listitems c inner join newscatlink l on l.catid = c.id where l.newsid = $id");
	}
	return $itm;
}

function getNews($title,$category=null,$sdate=null,$edate=null,$sort=null,$start=0,$lim=0){
	if(strtolower(get_class($title)) == 'pagestate'){
		$category = $title->values['category'];
		$sdate = $title->values['sdate'];
		$edate = $title->values['edate'];
		$sort = $title->values['sort'];
		$start = $title->values['start'];
		$lim = $title->values['lim'];
		$title = $title->values['title'];
	}
	$where = getNewsListWhere($title,$category,$sdate,$edate);
	$order = '';
	$limit = '';
	if(!empty($sort)){
		$sort = mysql_real_escape_string(urldecode($sort));
		$order = ' order by ' . $sort;
	}else{
		$order = ' order by date desc';
	}
	if(!empty($lim) && is_numeric($lim)){
		$limit = ' limit ' . (empty($start) || !is_numeric($start)?'':" $start,") . $lim;
	}
	$sql = "select distinct n.*	
			from news n 
			left join newscatlink l on l.newsid = n.id		
			$where$order$limit";
	$list = $GLOBALS['db']->select($sql);
	return $list;	
}

function countNews($title,$category=null,$sdate=null,$edate=null){
	if(strtolower(get_class($title)) == 'pagestate'){
		$category = $title->values['category'];
		$project = $title->values['project'];
		$sdate = $title->values['sdate'];
		$edate = $title->values['edate'];
		$title = $title->values['title'];
	}
	$where = getNewsListWhere($title,$category,$sdate,$edate);
	$ret = $GLOBALS['db']->selectsingle("select count(distinct n.id) from news n left join newscatlink l on l.newsid = n.id $where");
	return $ret;	
}

function getNewsListWhere($title,$category,$sdate,$edate){	
	$where = $GLOBALS['db']->getSearchStringWhere($title,'title','n','','');
	$where = $GLOBALS['db']->getSearchIntWhere($category,'catid','l',$where);
	$where = $GLOBALS['db']->getSearchDateWhere($sdate,'start','date','n',$where);	
	$where = $GLOBALS['db']->getSearchDateWhere($edate,'end','date','n',$where);
	return $where;
}

function createNewsItem($title, $date, $pubinfo, $description, $keywords, $content, $categories, $image, &$error){
	// check categories array
	if(!is_array($categories)){
		if(empty($categories)) $categories = array();
		else $categories = explode(',',$categories);	
	}
	
	$GLOBALS['db']->begintransaction();
	$values = array();
	$values['title'] = $title;
	$values['keywords'] = $keywords;
	$values['description'] = $description;
	$values['content'] = $content;
	$values['pubinfo'] = $pubinfo;
	if(empty($date)) $values['date'] = time();
	else $values['date'] = strtodate($date);
	$id = $GLOBALS['db']->insert('news',$values);	
	if($id===false){
		$error = mysql_error();
		$GLOBALS['db']->rollbacktransaction();
		return false;
	}

	//create category links
	foreach($categories as $catid){
		$catid = intval($catid);
		if(!empty($catid)){
			$res = $GLOBALS['db']->insertupdate('newscatlink',array('newsid'=>$id,'catid'=>$catid),array('newsid'=>$id,'catid'=>$catid),'id');
			if($res===false){
				$error = mysql_error();
				$GLOBALS['db']->rollbacktransaction();
				return false;
			}
		}	
	}
	
	// create thumbnail image, if found:
	$path = $GLOBALS['documentroot'].'/modules/News/images/'.$id.'_'.pathinfo($image['name'],PATHINFO_FILENAME).'.jpg';
	$thumbsize = $GLOBALS['skin']->getSetting('News','sizematrix',array('width'=>100,'height'=>100));
	$res = image_upload_file($image,$path,$thumbsize['width'],$thumbsize['height'],$error,IMAGERESIZE_CROP,IMAGETYPE_JPEG);
	if($res===false){
		$GLOBALS['db']->rollbacktransaction();
		return false;
	}	
	if(!file_exists($path)){
		$res = createNewsThumbnail($id, $content, 'create', $error);
		if($res===false){
			$GLOBALS['db']->rollbacktransaction();
			return false;
		}
	}
	
	$GLOBALS['db']->committransaction();
	return $id;
}

function updateNewsItem($id, $title, $date, $pubinfo, $description, $keywords, $content, $categories, $image, &$error){
	if(empty($id) || !is_numeric($id)) $id = 0;
	// check categories array
	if(!is_array($categories)){
		if(empty($categories)) $categories = array();
		else $categories = explode(',',$categories);	
	}
	
	$GLOBALS['db']->begintransaction();
	$values = array();
	$values['title'] = $title;
	$values['keywords'] = $keywords;
	$values['description'] = $description;
	$values['content'] = $content;
	$values['pubinfo'] = $pubinfo;
	if(!empty($date)) $values['date'] = strtodate($date);
	$pks = array();
	$pks['id'] = $id;
	$res = $GLOBALS['db']->update('news',$values,$pks);	
	if($res===false){
		$error = mysql_error();
		$GLOBALS['db']->rollbacktransaction();
		return false;
	}
	
	//create category links
	$res = $GLOBALS['db']->delete('newscatlink',$id,'newsid');
	if($res===false){
		$error = mysql_error();
		$GLOBALS['db']->rollbacktransaction();
		return false;
	}
	foreach($categories as $catid){
		$catid = intval($catid);
		if(!empty($catid)){
			$res = $GLOBALS['db']->insertupdate('newscatlink',array('newsid'=>$id,'catid'=>$catid),array('newsid'=>$id,'catid'=>$catid),'id');
			if($res===false){
				$error = mysql_error();
				$GLOBALS['db']->rollbacktransaction();
				return false;
			}
		}	
	}
	// create thumbnail image, if found:
	$path = $GLOBALS['documentroot'].'/modules/News/images/'.$id.'_'.pathinfo($image['name'],PATHINFO_FILENAME).'.jpg';
	$thumbsize = $GLOBALS['skin']->getSetting('News','sizematrix',array('width'=>100,'height'=>100));
	$res = image_upload_file($image,$path,$thumbsize['width'],$thumbsize['height'],$error,IMAGERESIZE_CROP,IMAGETYPE_JPEG);
	if($res===false){
		$GLOBALS['db']->rollbacktransaction();
		return false;
	}	
	$thumb = getNewsThumbnail($id);
	if(!file_exists($thumb)){
		$res = createNewsThumbnail($id, $content, 'create', $error);
		if($res===false){
			$GLOBALS['db']->rollbacktransaction();
			return false;
		}
	}
	
	$GLOBALS['db']->committransaction();
	return $id;	
}

function deleteNewsItem($id, &$error){
	if(empty($id) || !is_numeric($id)) $id = 0;
	$res = $GLOBALS['db']->delete('news',$id);
	if($res === false){
		$error = mysql_error();
		return false;
	}
	
	// delete HTMPaths
	@deleteHTMPath('news.php','id',$id,'','','','',$noerror);
	$thumbpath = getNewsThumbnail($id);
	if(file_exists($thumbpath)) @unlink($thumbpath);
	return $res;
}

function createNewsThumbnail($id, $content, $action, &$error){
	$regex = '|\<img[^\>]*src="([^"]+)"[^\>]*\>|i';
	if(preg_match($regex,stripslashes($content),$matches)){
		$imagepath = $GLOBALS['documentroot'] . $matches[1];
		if(file_exists($imagepath)){
			require_once('Images.lib.php');
			$targetpath = $GLOBALS['documentroot'].'/modules/News/images/' . $id.'_'.pathinfo($imagepath,PATHINFO_FILENAME);
			$thumbsize = $GLOBALS['skin']->getSetting('News','sizematrix',array('width'=>100,'height'=>100));
			$res = image_resize_crop($imagepath,$targetpath,$thumbsize['width'],$thumbsize['height'],IMAGETYPE_JPEG);			
			if($res === false){
				$error = 'There was an error saving the thumbnail image';	
			}
			return $res;
		}else{
			$error = 'image file not found';
			return false;	
		}
	}
	return true;
}

function getNewsThumbnail($id,$forweb=false){
	$files = glob($GLOBALS['documentroot'].'/modules/News/images/' . $id . '_*.*');
	$res = null;
	if(is_array($files)){
		// clean up old files
		if(count($files)>1){
			require_once('Files.lib.php');
			usort($files,'sortFilesByLastUpdateDate');
			for($i=1;$i<count($files);$i++){
				@unlink($files[$i]);	
			}
		}
		$res = $files[0];
	}
	if($forweb && !empty($res)){
		$res = $GLOBALS['webroot'] . substr($res,strlen($GLOBALS['documentroot']));	
	}
	return $res;
}

/* =============================================================
							Dispay News
   ============================================================= */	

function drawNewsArchive($newsid ,$article_month=0){
	$year = 0;
	$month = '';
	$thisyear = ($article_month==0)? date('Y',time()):date('Y',$article_month);
	$thismonth = ($article_month==0)? date('F',time()):date('F',$article_month);
	$yeartext = '';
	$monthtext = '';
	$items = getNews(null,null,'year(from_unixtime(date)) desc, month(from_unixtime(date)) desc');
	foreach($items as $item){
		$itemyear = date('Y',$item['date']);
		$itemmonth = date('F',$item['date']);
		if($itemyear!=$year){
			$year = $itemyear;
			$month = '';
			if(!empty($yeartext)) $res .= $yeartext . $monthtext . '</div></div>'."\r\n";
			$monthtext = '';
			if($year == $thisyear){
				$yeartext = '<div id="Archive_' . $itemyear . '" class="Archive_year" onclick="Archive_ShowYear(\'' . $itemyear . '\');">&#9660; ' . $itemyear . '</div>'."\r\n";
				$yeartext .= '<div id="Archive_' . $itemyear . '_List" class="Archive_yearlist">';
			}else{
				$yeartext = '<div id="Archive_' . $itemyear . '" class="Archive_year" onclick="Archive_ShowYear(\'' . $itemyear . '\');">&#9658; ' . $itemyear . '</div>'."\r\n";
				$yeartext .= '<div id="Archive_' . $itemyear . '_List" class="Archive_yearlist" style="display: none;">';
			}
		}
		if($itemmonth!=$month || $itemyear!=$year){
			$month = $itemmonth;
			if(!empty($monthtext)) $yeartext .= $monthtext . '</div>';
			if($year == $thisyear && $month == $thismonth){
				$monthtext = '<div id="Archive_' . $itemyear . '_' . $itemmonth . '" class="Archive_month" onclick="Archive_ShowMonth(\'' . $itemyear . '\',\'' . $itemmonth . '\',\''.$item['date'].'\');">&#9660; ' . $itemmonth . '</div>'."\r\n";
				$monthtext .= '<div id="Archive_' . $itemyear . '_' . $itemmonth . '_List" class="Archive_monthlist">';
				
			}else{
				$monthtext = '<div id="Archive_' . $itemyear . '_' . $itemmonth . '" class="Archive_month" onclick="Archive_ShowMonth(\'' . $itemyear . '\',\'' . $itemmonth . '\',\''.$item['date'].'\');">&#9658; ' . $itemmonth . '</div>'."\r\n";
				$monthtext .= '<div id="Archive_' . $itemyear . '_' . $itemmonth . '_List" class="Archive_monthlist" style="display: none;">';
				$monthtext .= '<img src="/images/loader.gif"/>';				
			}
		}
		if($month == $thismonth && $year ==$thisyear){
			$monthtext .= '<div class="'.($newsid==$item['id']?' Archive_Item_selected ': ' Archive_Item ').'"><a href="' . getHTMPath('News','news.php','id',$item['id']) . '">' . $item['title'] . '</a></div>'."\r\n";
		}	
	}
	$res .= $yeartext . $monthtext . '</div></div>'."\r\n";
	$res .= '<script language="javascript" src="' . $GLOBALS['webroot'] . 'modules/News/NewsArchive.js"></script>'."\r\n";
	$res .= '<link rel="stylesheet" href="' . $GLOBALS['skin']->getFile('modules/News/archives.css','','web') . '" />'."\r\n";
	return $res;
}

/* =============================================================
							News Categories
   ============================================================= */	

function getActiveNewsCategories(){
	$res = $GLOBALS['db']->select("select distinct c.* from listitems c inner join newscatlink l on l.catid = c.id order by c.position");
	return $res;
}

function getNewsCategories(){
	$res = $GLOBALS['db']->select("select i.* from listitems i inner join lists l on l.id = i.listid where l.code = 'news' order by name");
	return $res;
}

function getNewsCategoriesForItem($itemid){
	if(empty($itemid) || !is_numeric($itemid)) $itemid = 0;
	$res = $GLOBALS['db']->select("select c.* from listitems c inner join newscatlink l on l.catid = c.id where l.newsid = $itemid order by c.name");
	return $res;
}

function drawNewsCategories($id,$selected=null){
	$categories = getNewsCategories();
	$res .= '<select name="'.$id.'" id="'.$id.'" class="edt_select">';
	$res .= '<option value="">All Categories</option>';
	foreach($categories as $category){
		if(!empty($category['name'])){
			$sel = $selected==$category['id']?' selected':'';
			$res .= '<option value="'. $category['id'] . '"'.$sel.'>'. $category['name'] . '</option>';
		}
	}
	$res .= '</select>';
	return $res;
}

/* =============================================================
						Event Handlers
   ============================================================= */
	
function linkSelectorLoadingHandler($eventname,&$data){
	$mtree = new NewsTreeView($data['sourceid'],$data['owner']);
	$mtree->selectedid = $data['selected'];
	$html .= '<link rel="StyleSheet" href="'.$GLOBALS['webroot'].'modules/News/newstreeview.css" type="text/css" />';
	$html .= $mtree->drawTree();
	$tab = array('description'=>'Link to a news category or article.');
	$tab['html'] = $html;
	$data['tabs'][] = $tab;
	return true;
}
	
function sitemapLoadingHandler($eventname,&$data){
	$listmanager = ListManager::getListManager();
	$categories = $listmanager->getList('news');
	if($categories && is_array($categories['items'])){
		foreach($categories['items'] as &$item){
			$data['links'][] = $GLOBALS['settings']->siteroot.'/'.getHTMPath('News','news.php','cat',$item['id']);
		}
	}
	
	$sql = "select * from news order by date desc";
	$res = $GLOBALS['db']->select($sql);
	if($res && count($res)){
		$today = mktime(0,0,0,date('m'),date('d'),date('Y'));
		$week = $today - (7*24*60*60);
		$month = $today - (30*24*60*60);
		$halfyear = $today - (180*24*60*60);
		foreach($res as &$item){
			$url = array();
			$url['url'] = $GLOBALS['settings']->siteroot.'/'.getHTMPath('News','news.php','id',$item['id']);
			$url['lastmod'] = $item['date'];
			$url['priority'] = 1;
			if($item['date'] >= $halfyear) $url['priority'] = 3;
			if($item['date'] >= $month) $url['priority'] = 5;
			if($item['date'] >= $week) $url['priority'] = 7;
			if($item['date'] >= $today) $url['priority'] = 9;
			$data['links'][] = $url;
		}
	}
	return true;
}
	
function resetHTMPathHandler($event,&$data){
	resetHTMPathsInTable('news','content',$data['oldpath'],$data['newpath']);
	return true;
}

?>