<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

require_once('News.lib.php');

class News extends Module{
	
	public function getHTMPath($filename,$att1=null,$val1=null,$att2=null,$val2=null,$att3=null,$val3=null){
		require_once('HTMPaths.lib.php');
		try{
			switch($att1){
				case 'id':
					$itm = getNewsItem($val1);
					$htmpath = getHTMPathFromTitle($itm['title'],'news/'.date('Y',$itm['date']).'/'.date('F',$itm['date']).'/',$filename,$att1,$val1,$att2,$val2,$att3,$val3);
					break;
				case 'cat':
					$listmanager = ListManager::getListManager();
					$category = $listmanager->getListItem($val1);
					if(empty($category)) $htmpath = 'news.htm';
					else{
						$htmpath = getHTMPathFromTitle($category['name'],'news/',$filename,$att1,$val1,$att2,$val2,$att3,$val3);
					}
					break;
				default:
					$htmpath = 'news.htm';
					break;
			}
		}catch(exception $err){
			$htmpath = 'news.htm';
		}
		if(!empty($htmpath)) createHTMPath($htmpath,$filename,$att1,$val1,$att2,$val2,$att3,$val3,$error);
		if(substr($htmpath,0,4) != 'http' && substr($htmpath,0,1) != '/') $htmpath = $GLOBALS['webroot'].$htmpath;
		return $htmpath;
	}
}

?>