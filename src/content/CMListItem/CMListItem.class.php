<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

require_once('Global.lib.php');

class CMListItem extends ContentModule{
	public $content;
	public $heading;
	public $subtitle;
	public $image;
	public $imagepath;
	public $orientation;
	public $link;
	public $linktext;
	public $target;
	public $drawline;
	
	public function __construct($row,$foredit=false){
		parent::__construct($row,$foredit);
		$this->sizematrix = $GLOBALS['skin']->getSetting('CMListItem','sizematrix',array(0=>array('portrait'=>170,'landscape'=>230)));
		$this->content = $row['content'];
		$this->heading = $this->properties['heading'];
		$this->subtitle = $this->properties['subtitle'];
		$this->image = $this->properties['image'];
		$this->orientation = $this->properties['orientation'];
		$this->link = $this->properties['link'];
		$this->linktext = $this->properties['linktext'];
		$this->drawline = $this->properties['drawline'];
	}	
	
	public function drawContentBlock(){		
		$width = $this->sizematrix[$this->columnid][$this->orientation];
		if(empty($width)) $width = $this->sizematrix[0][$this->orientation];
		if(empty($width)) $width = $this->orientation=='portrait'?100:140;
		$this->target = getLinkTarget($this->link,false);
		if(!empty($this->image)){
			$this->imagepath = $this->getImagePath($this->image,$this->id.'_'.basename($this->image),$this->id,$width);
			$docpath = str_replace($GLOBALS['webroot'],$GLOBALS['documentroot'].'/',$this->imagepath);
			if(file_exists($docpath)) $this->size = getimagesize($docpath);
		}
		$res = $GLOBALS['skin']->getFragment('/content/CMListItem/CMListItem.tmp.html',$this);
		return $res;
	}
	
	public function ClearCache(&$error){
		$image = $GLOBALS['documentroot'].'/content/CMListItem/images/'.$this->id.'_'.basename($this->image);
		if(file_exists($image)){
			$res = @unlink($image);
			if($res===false){
				$error = 'There was an error deleting the cached image file';
				return false;	
			}
		}
		return true;	
	}
}

?>