<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

require_once('Global.lib.php');
require_once('HTMPaths.lib.php');
require_once('News/News.lib.php');

class CMNewsFeed extends ContentModule{
	public $content;
	public $category;
	public $items;
	public $articles;
	
	public function __construct($row,$foredit){
		parent::__construct($row,$foredit);	
		$this->content = $row['content'];
		$this->category = $this->properties['category'];
		$this->items = $this->properties['items'];
		if(!is_numeric($this->items)) $this->items = 5;
	}	
	
	public function drawContentBlock(){
		$this->articles = getNews(null,$this->category,null,null,null,0,$this->items);
		$res = $GLOBALS['skin']->getFragment('/content/CMNewsFeed/CMNewsFeed.tmp.html',$this);
		return $res;
	}
	
	public function drawArticles(){
		$ind = 0;
		foreach($this->articles as $entry){
			$entry = (object)$entry;
			$entry->parent = $this;
			$entry->link = getHTMPath('News','news.php','id',$entry->id);
			$res .= $GLOBALS['skin']->getFragment('/content/CMNewsFeed/CMNewsFeed_item.tmp.html',$entry);
			if($ind >= $this->items) break;
			$ind ++;
		}
		return $res;
	}
}

?>