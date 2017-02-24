<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

require_once('Global.lib.php');
require_once('Files.lib.php');

class CMLightboxGallery extends ContentModule{
	public $prefix;
	public $style;
	public $width;
	public $height;
	public $items;
	public $sizematrix;
	
	public function __construct($row,$foredit){
		parent::__construct($row,$foredit);	
		$this->sizematrix = $GLOBALS['skin']->getSetting('CMLightboxGallery','sizematrix',array(1=>array('width'=>80,'height'=>53)));
		$this->content = $row['content'];
		$this->style = $this->properties['style'];
		$this->items = unserialize(base64_decode($this->properties['items']));
		if(is_array($this->items)) usort($this->items,array($this,'sortItems'));
		$this->width = empty($this->sizematrix[$this->columnid]['width'])?80:$this->sizematrix[$this->columnid]['width'];
		$this->height = empty($this->sizematrix[$this->columnid]['height'])?53:$this->sizematrix[$this->columnid]['height'];
	}
	
	private function sortItems($a,$b){
		return $a['position'] > $b['position'];
	}
	
	public function drawContentBlock(){
		$res .= '<div id="cbl_' . $this->id . '"';
		if($this->foredit) $res .= ' prop="' . $this->getBlockProperties() . '"';
		$res .= ' class="CMLightboxGallery_container CMLightboxGallery_column'.$this->columnid.'">';
		
		if(empty($this->items) && $this->foredit){
			$res .= '<div class="CMLightboxGallery_empty">[Empty Lightbox Gallery]</div>';
		}else{			
			if($this->style == 'grid') $res .= $this->drawGrid();
			else $res .= $this->drawScroller();
		}
		
		$res .= '</div>';
		return $res;
	}
	
	function drawGrid(){
		$path = $GLOBALS['webroot'].'content/CMLightboxGallery/galleries/'.$this->id.'/';
		$res .= '<div class="CMLightboxGallery_gridcontainer">';
		if(is_array($this->items)){
			foreach($this->items as $item){
				$title = preg_replace('/[\r\n\t\v]+/',' ',htmlentities($photo['caption']));
				$res .= '<span class="CMLightboxGallery_griditem">';
				$res .= '<a href="'.$path.$item['filename'].'" rel="lightbox" rev="CMLightboxGallery_'.$this->id.'" caption="'.$title.'" class="CMLightboxGallery_gridlink">';
				$res .= '<img src="'.$path.$item['thumbname'].'" class="CMLightboxGallery_gridimage" />';
				$res .= '</a>';
				$res .= '</span>';
			}	
		}
		$res .= '</div>';
		return $res;
	}
	
	function drawScroller(){
		if(empty($GLOBALS['Scripts']['scroller.js'])){
			$GLOBALS['Scripts']['scroller.js'] = 1;
			$res .= '<script src="'.$GLOBALS['webroot'].'js/scroller.js"></script>';
			$this->prefix = 'CMLightboxGallery';
		}else{
			$this->prefix = 'CMLightboxGallery'.(++$GLOBALS['Scripts']['scroller.js']);
		}
		$classprefix = 'CMLightboxGallery';
		$res .= '<div id="'.$this->prefix.'_slider" class="CMLightboxGallery_slider">
				<div id="'.$this->prefix.'_beforebutton" class="CMLightboxGallery_beforebutton"></div>
				<div id="'.$this->prefix.'_window" class="CMLightboxGallery_window">
				<div id="'.$this->prefix.'_belt" class="CMLightboxGallery_belt">';
		$webpath = $GLOBALS['webroot'].'content/CMLightboxGallery/galleries/'.$this->id.'/';
		foreach($this->items as $ind=>$photo){
			$title = preg_replace('/[\r\n\t\v]+/',' ',htmlentities($photo['caption']));
			$res .= '<div id="'.$this->prefix.'_panel_'.$ind.'" class="CMLightboxGallery_panel">';
			$res .= '<a href="'.$webpath.$photo['filename'].'" caption="'.$title.'" rel="lightbox" rev="'.$this->prefix.'_'.$this->id.'"><img src="' . $webpath.$photo['thumbname'].'" class="CMLightboxGallery_border" /></a>';
			$res .= '</div>';
		}
		$res .= '</div></div>
				<div id="'.$this->prefix.'_afterbutton" class="CMLightboxGallery_afterbutton"></div>
				</div>';
		$res .= '<script>var scroller_'.$this->prefix.' = new Scroller(\''.$this->prefix.'\',\'CMLightboxGallery\');</script>';		
		return $res;
	}
	
	public function CopyBlock($newblockid,&$error){
		$path = $GLOBALS['documentroot'].'/content/CMLightboxGallery/galleries/'.$this->id.'/';
		$newpath = $GLOBALS['documentroot'].'/content/CMLightboxGallery/galleries/'.$newblockid.'/';
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
			$path = $GLOBALS['documentroot'].'/content/CMLightboxGallery/galleries/'.$this->id.'/';
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