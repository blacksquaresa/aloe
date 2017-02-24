<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('lib/Global.lib.php');
require_once('lib/HTMPaths.lib.php');

function listSitemapMenu($parentid=null,$priority=11){
	$priority = max(1,$priority);
	if(!is_numeric($parentid)) $sql = "select * from pages where parent is null order by position asc";
	else $sql = "select * from pages where parent = $parentid order by position asc";
	$res = $GLOBALS['db']->select($sql);
	if($res && count($res)){
		foreach($res as &$item){
			if($item['type'] != 'link' && $item['id'] != PAGE_ORPHANMENU){
				if($parentid != null){
					$url = $GLOBALS['settings']->siteroot.'/'.getHTMPath(null,'index.php','id',$item['id']);
					drawURL($url,$item['updated'],null,$priority);
				}		
				listSitemapMenu($item['id'],$priority-2);
			}
		}
	}
	return $res;
}

function listModuleURLs(){
	global $xml;
	$links = array();
	$data = array('links'=>&$links);
	fireEvent('sitemapLoading',$data);
	if(is_array($links)){
		foreach($links as $link){
			if(is_array($link)){
				drawURL($link['url'],$link['lastmod'],$link['changefreq'],$link['priority']);
			}else{
				drawURL($link);
			}
		}
	}
}

function drawURL($url,$lastmod=null,$changefreq=null,$priority=null){
	global $xml;
	$xml->startElement('url');
	$xml->writeElement('loc',$url);
	if(!empty($lastmod)){
		$xml->writeElement('lastmod',date('Y-m-d',$lastmod));	
	}
	if(!empty($changefreq) && ($changefreq=strtolower($changefreq)) && in_array($changefreq,array('always','hourly','daily','weekly','monthly','yearly','never'))){
		$xml->writeElement('changefreq',$changefreq);	
	}
	if(is_numeric($priority) && $priority >= 0){
		if($priority>1) $priority /= (10^abs(log($priority,10)));
		$xml->writeElement('priority',number_format($priority,1,'.',''));	
	}
	$xml->endElement();
}

$xml = new XMLWriter();
$xml->openMemory();
$xml->setIndent(true);
$xml->setIndentString(' ');
$xml->startDocument('1.0', 'UTF-8');
$xml->startElement('urlset');
$xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

// Insert contents here
listSitemapMenu();
listModuleURLs();

$xml->endElement();
header('Content-type: text/xml');
echo $xml->outputMemory();
?>