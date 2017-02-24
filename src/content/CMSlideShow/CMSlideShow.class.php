<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

require_once('Global.lib.php');
require_once('Files.lib.php');

class CMSlideShow extends ContentModule{
	public $prefix;
	public $width;
	public $height;
	public $padding;
	public $indicator;
	public $transition;
	public $aspect;
	public $duration;
	public $speed;
	public $items;
	public $sizematrix;
	public $speedmatrix;
	
	public function __construct($row,$foredit){
		parent::__construct($row,$foredit);	
		$this->sizematrix = $GLOBALS['skin']->getSetting('CMSlideShow','sizematrix',array(1=>array('width'=>470,'height'=>array('short'=>216,'medium'=>313,'long'=>420))));
		$this->speedmatrix = $GLOBALS['skin']->getSetting('CMSlideShow','speedmatrix',array('fast'=>500,'medium'=>1000,'slow'=>1500));
		$this->padding = $GLOBALS['skin']->getSetting('CMSlideShow','padding',0);
		$this->content = $row['content'];
		$this->indicator = $this->properties['indicator'];
		$this->transition = $this->properties['transition'];
		$this->aspect = $this->properties['aspect'];
		$this->duration = intval($this->properties['duration']);
		if(empty($this->duration)) $this->duration = 6;
		$this->speed = $this->properties['speed'];
		$this->items = unserialize(base64_decode($this->properties['items']));
		if(is_array($this->items)) usort($this->items,array($this,'sortItems'));
		$this->width = empty($this->sizematrix[$this->columnid]['width'])?$this->column->width:$this->sizematrix[$this->columnid]['width'];
		$this->height = empty($this->sizematrix[$this->columnid]['height'][$this->aspect])?round($this->column->width/2):$this->sizematrix[$this->columnid]['height'][$this->aspect];
	}
	
	private function sortItems($a,$b){
		return $a['position'] > $b['position'];
	}
	
	public function drawContentBlock(){
		$res .= '<div id="cbl_' . $this->id . '"';
		if($this->foredit) $res .= ' prop="' . $this->getBlockProperties() . '"';
		$res .= ' class="CMSlideShow_container" style="width:'.($this->width+(2*$this->padding)).'px;height:'.($this->height+(2*$this->padding)).'px;">';
		
		if(!empty($this->items) && count($this->items)){
			if(empty($GLOBALS['CMSlideShowIndex'])){
				$GLOBALS['CMSlideShowIndex'] = 1;
				$this->prefix = 'cmss';
				$res .= '<script src="/content/CMSlideShow/CMSlideShowClient.js"></script>';
				$res .= '<script src="/js/transitions.js"></script>';
			}else{
				$this->prefix = 'cmss'.(++$GLOBALS['CMSlideShowIndex']);
			}
			$firstimage = $this->items[0];
			$path = $GLOBALS['webroot'] . 'content/CMSlideShow/galleries/'.$this->id.'/'.$firstimage['filename'];
			$url = empty($firstimage['url'])?'javascript:;':$firstimage['url'];
			$res .= '<div class="CMSlideShow_imagecontainer" id="CMSlideShow_' . $this->id . '_imagecontainer">';
			$res .= '<a href="'.$url.'" title="'.$firstimage['title'].'" id="CMSlideShowSlide_'.$this->prefix.'_0"><img src="'.$path.'" /></a>';
			switch($this->indicator){
				case 'dot':
					$res .= $this->drawDots();
					break;
				case 'thumb':
					$res .= $this->drawThumbs();
					break;
			}
			if(!$this->foredit){
				$res .= '<script language="javascript">';
				$res .= "var CMSlideShow_{$this->prefix} = new CMSlideShow('{$this->prefix}','{$this->transition}','{$this->indicator}',{$this->speedmatrix[$this->speed]},{$this->duration});\r\n";
				for($i=0;$i<count($this->items);$i++){
					$url = empty($this->items[$i]['url'])?'javascript:;':$this->items[$i]['url'];
					$path = $GLOBALS['webroot'] . 'content/CMSlideShow/galleries/'.$this->id.'/'.$this->items[$i]['filename'];
					$res .= "CMSlideShow_{$this->prefix}.addSlide('{$this->prefix}_{$i}','$path','$url','{$this->items[$i]['title']}');\r\n";
				}
				$res .= "CMSlideShow_{$this->prefix}.start();\r\n";
				$res .= '</script>';
			}
			$res .= '</div>';
		}elseif($this->foredit){
			$res .= '<div style="width: '.$this->width.'px; height: '.$this->height.'px; background-color: #ccc;"></div>';
		}
		$res .= '</div>';
		return $res;
	}
	
	private function drawDots(){
		$res .= '<div class="CMSlideShow_dotcontainer"id="CMSlideShow_'.$this->prefix.'_dotcontainer">';
		foreach($this->items as $index=>$item){
			$class = $index==0?'CMSlideShow_dotselected':'CMSlideShow_dot';
			$res .= '<span id="CMSlideShow_'.$this->prefix.'_dot_'.$index.'" class="'.$class.'" onclick="CMSlideShow_'.$this->prefix.'.click('.$index.')"></span>';	
		}
		$res .= '</div>';
		return $res;
	}
	
	private function drawThumbs(){
		$code = $this->foredit?'?'.createRandomCode(8):'';
		$res .= '<div class="CMSlideShow_thumbcontainer" id="CMSlideShow_'.$this->prefix.'_thumbcontainer">';
		foreach($this->items as $index=>$item){
			$path = $GLOBALS['webroot'] . 'content/CMSlideShow/galleries/'.$this->id.'/'.$item['thumbname'].$code;
			$class = $index==0?'CMSlideShow_thumbselected':'CMSlideShow_thumb';
			$res .= '<img src="'.$path.'" id="CMSlideShow_'.$this->prefix.'_thumb_'.$index.'" class="'.$class.'" onclick="CMSlideShow_'.$this->prefix.'.click('.$index.')" />';	
		}
		$res .= '</div>';
		return $res;
	}
	
	public function CopyBlock($newblockid,&$error){
		$path = $GLOBALS['documentroot'].'/content/CMSlideShow/galleries/'.$this->id.'/';
		$newpath = $GLOBALS['documentroot'].'/content/CMSlideShow/galleries/'.$newblockid.'/';
		if(!file_exists($newpath)) mkdir($newpath);
		foreach($this->items as $item){
			if(file_exists($path.$item['filename'])){
				$res = copy($path.$item['filename'],$newpath.$item['filename']);
				if($res===false){
					$error = 'There was an error copying the image file: ' . $item['filename'];
					@deleteFolder($newpath);
					return false;	
				}
			}	
			if(file_exists($path.$item['thumbname'])){
				$res = copy($path.$item['thumbname'],$newpath.$item['thumbname']);
				if($res===false){
					$error = 'There was an error copying the thumbnail file: ' . $item['thumbname'];
					@deleteFolder($newpath);
					return false;	
				}
			}		
		}
		return true;	
	}
	
	public function DeleteBlock(&$error){
		try{
			$path = $GLOBALS['documentroot'].'/content/CMSlideShow/galleries/'.$this->id.'/';
			if(!deleteFolder($path)){
				$error = 'There was an error deleting the gallery images';
				return false;
			}
		}catch(exception $err){
			$error = 'There was an error deleting the gallery images<br />' . $err->getMessage();
			return false;
		}
		return true;	
	}
}
?>