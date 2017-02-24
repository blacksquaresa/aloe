<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');
require_once('Global.lib.php');

class CMRSSFeed extends ContentModule{
	public $content;
	public $heading;
	public $linktext;
	public $link;
	public $items;
	public $feed;
	
	public function __construct($row,$foredit=false){
		parent::__construct($row,$foredit);	
		$this->content = $row['content'];
		$this->heading = $this->properties['heading'];
		$this->items = $this->properties['items'];
		if(!is_numeric($this->items)) $this->items = 5;
		$this->link = $this->properties['link'];
		$this->linktext = $this->properties['linktext'];
	}	
	
	public function drawContentBlock(){		
		try{
			$source = $this->content;
			// Check for cached feed
			$files = glob($GLOBALS['documentroot'].'/content/CMRSSFeed/cache/'.$this->pageid.'_'.$this->id.'*.xml');
			if($files && count($files)){
				rsort($files);
				$file = $files[0];
				$time = pathinfo($file,PATHINFO_FILENAME);
				$time = substr($time,strrpos($time,'_')+1);
				if($time > (time() - (1 * 60*60))){
					$source = $file;
					$cachepath = '';
				}else{
					$cachepath = $GLOBALS['documentroot'].'/content/CMRSSFeed/cache/'.$this->pageid.'_'.$this->id.'_'.time().'.xml';
				}
				//clean old files
				for($i=count($files)-1;$i>=1;$i--){
					@unlink($files[$i]);
				}
			}else{
				$cachepath = $GLOBALS['documentroot'].'/content/CMRSSFeed/cache/'.$this->pageid.'_'.$this->id.'_'.time().'.xml';
			}
			$this->feed = new FeedReader($source);
			$this->feed->FetchFeed($cachepath);
			$this->target = getLinkTarget($this->link,false);
			$res = $GLOBALS['skin']->getFragment('/content/CMRSSFeed/CMRSSFeed.tmp.html',$this);
			return $res;
		}catch(exception $err){
			return $err->getMessage();	
		}		
	}
	
	function drawFeedItems(){
		if(count($this->feed->channels)){
			$channel = $this->feed->channels[0];
			if(count($channel->entries)){
				$ind = 1;
				foreach($channel->entries as $entry){
					$entry->date = strtotime($entry->pubdate);
					$entry->ind = $ind;
					$res .= $GLOBALS['skin']->getFragment('/content/CMRSSFeed/CMRSSFeed_item.tmp.html',$entry);
					if($ind >= $this->items) break;
					$ind ++;
				}
			}
		}
		return $res;
	}
	
	public function ClearCache(&$error){
		$files = glob($GLOBALS['documentroot'].'/content/CMRSSFeed/cache/'.$this->pageid.'_'.$this->id.'*.xml');
		if($files&&count($files)){
			foreach($files as $file){
				@unlink($file);	
			}	
		}
		return $res;
	}
}

?>