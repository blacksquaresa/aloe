<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

require_once('Global.lib.php');
require_once('Files.lib.php');

class CMPhotoGallery extends ContentModule{
	public $prefix;
	public $width;
	public $height;
	public $thumbwidth;
	public $thumbheight;
	public $items;
	public $sizematrix;
	
	public function __construct($row,$foredit){
		parent::__construct($row,$foredit);	
		$this->sizematrix = $GLOBALS['skin']->getSetting('CMPhotoGallery','sizematrix',array(1=>array('width'=>470,'height'=>313,'thumbwidth'=>80,'thumbheight'=>53)));
		$this->content = $row['content'];
		$this->style = $this->properties['style'];
		$this->items = unserialize(base64_decode($this->properties['items']));
		if(is_array($this->items)) usort($this->items,array($this,'sortItems'));
		$this->width = empty($this->sizematrix[$this->columnid]['width'])?$this->column->width:$this->sizematrix[$this->columnid]['width'];
		$this->height = empty($this->sizematrix[$this->columnid]['height'])?round($this->column->width/1.5):$this->sizematrix[$this->columnid]['height'];
		$this->thumbwidth = empty($this->sizematrix[$this->columnid]['thumbwidth'])?80:$this->sizematrix[$this->columnid]['thumbwidth'];
		$this->thumbheight = empty($this->sizematrix[$this->columnid]['thumbheight'])?53:$this->sizematrix[$this->columnid]['thumbheight'];
	}
	
	private function sortItems($a,$b){
		return $a['position'] > $b['position'];
	}
	
	public function drawContentBlock(){
		$res .= '<div id="cbl_' . $this->id . '"';
		if($this->foredit) $res .= ' prop="' . $this->getBlockProperties() . '"';
		$res .= ' class="CMPhotoGallery_container CMPhotoGallery_column'.$this->columnid.'">';
		$res .= $this->drawPicture();		
		$res .= $this->drawScroller();		
		$res .= '</div>';
		return $res;
	}
	
	private function drawPicture(){
		$photo = is_array($this->items)?current($this->items):null;
		$webpath = $GLOBALS['webroot'].'content/CMPhotoGallery/galleries/'.$this->id.'/';
		$res .= '<div class="CMPhotoGallery_frame" id="CMPhotoGallery_frame_'.$this->id.'">';
		$res .= '<img src="'.$GLOBALS['webroot'].'images/loading.gif" class="CMPhotoGallery_loading" />';
		if($this->foredit && !empty($photo)){
			$res .= '<div class="CMPhotoGallery_imagecontainer" style="visibility:visible;"><img src="'.$webpath.$photo['filename'].'">';
			if(!empty($photo['caption'])){
				$res .= '<div class="CMPhotoGallery_caption"><div class="CMPhotoGallery_captionback"></div><div class="CMPhotoGallery_captiontext">'.$photo['caption'].'</div></div>';
			}
			$res .= '</div>';	
		}
		$res .= '</div>';
		return $res;	
	}
	
	private function drawScroller(){
		if(!$this->foredit){
			if(empty($GLOBALS['Scripts']['scroller.js'])){
				$GLOBALS['Scripts']['scroller.js'] = 1;
				$res .= '<script src="'.$GLOBALS['webroot'].'js/scroller.js"></script>';
			}
			if(empty($GLOBALS['CMPhotoGalleryIndex'])){
				$GLOBALS['CMPhotoGalleryIndex'] = 1;
				$this->prefix = 'cmss';
				$res .= '<script src="'.$GLOBALS['webroot'].'content/CMPhotoGallery/CMPhotoGalleryClient.js"></script>';
			}else{
				$this->prefix = 'cmss'.(++$GLOBALS['CMPhotoGalleryIndex']);
			}
		}
		$classprefix = 'CMPhotoGallery';
		$res .= '<div id="CMPhotoGallery'.$this->id.'_slider" class="CMPhotoGallery_slider">
				<div id="CMPhotoGallery'.$this->id.'_beforebutton" class="CMPhotoGallery_beforebutton"></div>
				<div id="CMPhotoGallery'.$this->id.'_window" class="CMPhotoGallery_window">
				<div id="CMPhotoGallery'.$this->id.'_belt" class="CMPhotoGallery_belt">';
		$webpath = $GLOBALS['webroot'].'content/CMPhotoGallery/galleries/'.$this->id.'/';
		if(is_array($this->items)){
			foreach($this->items as $ind=>$photo){
				$title = preg_replace('/[\r\n\t\v]+/',' ',htmlentities($photo['caption'],ENT_QUOTES));
				$res .= '<div id="CMPhotoGallery'.$this->id.'_panel_'.$ind.'" class="CMPhotoGallery_panel">';
				$res .= '<a href="javascript:CMPhotoGallery'.$this->id.'.click('.$ind.')"><img src="' . $webpath.$photo['thumbname'].'" class="CMPhotoGallery_border" alt="'.$title.'" title="'.$title.'" /></a>';
				$res .= '</div>';
				$slides .= 'gallery.addSlide('.$ind.',\''.$webpath.$photo['filename'].'\',\''.$title.'\');';
			}
		}
		$res .= '</div></div>
				<div id="CMPhotoGallery'.$this->id.'_afterbutton" class="CMPhotoGallery_afterbutton"></div>
				</div>';
		if(!$this->foredit){
			$res .= '<script>var scroller = new Scroller(\'CMPhotoGallery'.$this->id.'\',\'CMPhotoGallery\');';
			$res .= 'var gallery = new CMPhotoGallery('.$this->id.');';		
			$res .= $slides;
			$res .= 'gallery.start();</script>';
		}
		return $res;
	}
	
	public function CopyBlock($newblockid,&$error){
		$path = $GLOBALS['documentroot'].'/content/CMPhotoGallery/galleries/'.$this->id.'/';
		$newpath = $GLOBALS['documentroot'].'/content/CMPhotoGallery/galleries/'.$newblockid.'/';
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
			$path = $GLOBALS['documentroot'].'/content/CMPhotoGallery/galleries/'.$this->id.'/';
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