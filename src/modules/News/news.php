<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('lib/Global.lib.php');
require_once('lib/Content.lib.php');
require_once('modules/News/News.lib.php');
require_once('class/Page.class.php');
$newsid = empty($_REQUEST['id'])?(empty($GLOBALS['id'])?-1:$GLOBALS['id']):$_REQUEST['id'];
$newspageid = $GLOBALS['db']->selectsingle("select id from pages where specialpage = 'news.php'");
if(empty($newspageid)) $newspageid = PAGE_HOME;
$newspage = Page::GetNewPage($newspageid);

if($newsid==-1){
	$catid = empty($_REQUEST['cat'])?(empty($GLOBALS['cat'])?null:$GLOBALS['cat']):$_REQUEST['cat'];
	if(!empty($catid)){
		$category = ListManager::getListItem($catid);
		$pageobject = $category['fields'];
		if(stripos($pageobject['title'],'news')===false) $pageobject['title'] .= ' News';
	}
}else{
	$item = getNewsItem($newsid);
	$pageobject = $item;
}

if(empty($pageobject['name']) && empty($pageobject['title'])){
	$pageobject = $newspage->toArray();
}
$pageobject['id'] = $newspageid;


function drawNewsLandingPage(){
	global $newsid,$catid,$projid,$pageobject;
	$res =	'<h1>'.$pageobject['title'].'</h1>';
	$ind=1;		
	$newslist = getNews(null,$catid,$projid);
	if($newslist && count($newslist)){
		foreach($newslist as $entry){
			$context = (object)$entry;
			$context->drawline = ($ind > 1);
			$context->link = getHTMPath('news','news.php','id',$entry['id']);
			$thumb = getNewsThumbnail($entry['id']);
			if(file_exists($thumb)){
				$size = getimagesize($thumb);
				$context->size = $size[3];
				$context->webthumb = getNewsThumbnail($entry['id'],true);
			}
			$res .= $GLOBALS['skin']->getFragment('modules/News/newsitem.tmp.html',$context);
			$ind++;	
		}
	}else{
		$res .= 'There are no current news stories available.';	
	}
	return $res;
}

function drawNewsItem(){
	global $item;
	$context = (object)$item;
	if(is_array($item['categories'])){
		foreach($item['categories'] as $category){
			$cats .= '<a href="'.getHTMPath('news','news.php','cat',$category['id']).'" class="list_categoryname">'.$category['name'].'</a>';	
		}
	}
	$context->categorylist = $cats;
	$res = $GLOBALS['skin']->getFragment('modules/News/newsarticle.tmp.html',$context);
	return $res;	
}

function drawNewsCategoryList(){
	$categories = getActiveNewsCategories();
	$res .= '<div class="news_categorylist">';
	foreach($categories as $category){
		$res .= '<a href="'.getHTMPath('news','news.php','cat',$category['id']).'" class="arrowlink">' . $category['name'] . '</a>';
	}	
	$res .= '</div>';
	return $res;
}

$context = new stdClass();
$context->content = ($newsid=='-1')?drawNewsLandingPage():drawNewsItem();
$context->categories = drawNewsCategoryList();
$context->archive = drawNewsArchive($newsid,$item['date']);
$pageobject['pagecontent'] = $GLOBALS['skin']->getFragment('/modules/News/news.tmp.html',$context);
echo $GLOBALS['skin']->getContent(); 
?>