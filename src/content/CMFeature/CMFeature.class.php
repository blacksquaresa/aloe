<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

require_once('Global.lib.php');

class CMFeature extends ContentModule{
	public $content;
	public $heading;
	public $subtitle;
	public $image;
	public $imagepath;
	public $width;
	public $link;
	public $linktext;
	public $drawline;
	
	public function __construct($row,$foredit=false){
		parent::__construct($row,$foredit);
		$this->content = $row['content'];
		$this->heading = $this->properties['heading'];
		$this->subtitle = $this->properties['subtitle'];
		$this->image = $this->properties['image'];
		$this->link = $this->properties['link'];
		$this->linktext = $this->properties['linktext'];
		$this->drawline = $this->properties['drawline'];
		$column = ContentColumn::GetColumn($this->columnid);
		$this->width = $column->width;
	}	
	
	public function drawContentBlock(){		
		$this->target = getLinkTarget($this->link,false);
		if(!empty($this->image)){
			$this->imagepath = $this->getImagePath($this->image,$this->id.'_'.basename($this->image),$this->id,$this->width);
			$docpath = str_replace($GLOBALS['webroot'],$GLOBALS['documentroot'].'/',$this->imagepath);
			if(file_exists($docpath)) $this->size = getimagesize($docpath);
		}
		$res = $GLOBALS['skin']->getFragment('/content/CMFeature/CMFeature.tmp.html',$this);
		return $res;
	}
	
	public function ClearCache(&$error){
		$image = $GLOBALS['documentroot'].'/content/CMFeature/images/'.$this->id.'_'.basename($this->image);
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