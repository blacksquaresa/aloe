<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('../../lib/Global.lib.php'); 
require_once('../../lib/Agent.lib.php');
$agent->init();	

function AJ_drawNews($date){
	require_once('News.lib.php');
	$items = getNews(null,$section,null,null,'year(from_unixtime(date)) desc, month(from_unixtime(date)) desc, date desc');
	$yearMonth = date('F Y',$date);
	$res="";
	foreach($items as $item){
		if($yearMonth == date('F Y',$item['date'])){
			$res .= '<div class="Archive_Item"><a href="'.$GLOBALS['settings']->siteroot.'/'.trim(getHTMPath('News','news.php','id',$item['id']),'./') . '">' . $item['title'] . '</a></div>'."\r\n";
		}
	}
	return $res;
}
?>

