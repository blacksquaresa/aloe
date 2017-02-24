<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');
require_once('Global.lib.php');
require_once('Text.lib.php');
require_once('Images.lib.php');

class GalleryView{
	public $prefix = 'gv';
	public $galleryid = 0;
	public $width;
	public $height;
	public $thumbwidth;
	public $thumbheight;	
	public $thumbpadding;
	public $thumbspacing;
	public $buttonpadding;
	public $imagewidth = 0;
	public $imageheight = 0;
	public $usedisabled = false;
	public $defaultpath = 'images/';
	public $iconpath = 'controls/galleryview/';
	public $videoplayer = '';
	public $audioplayerpath = '';
	public $resizestyle = IMAGERESIZETYPE_WIDTHHEIGHT;
	
	// relative path to the image folder
	public $imagepath;
	public $tablename = 'galleryitems';
	// For serialised, this is the name of the property that holds the data. For database, this is the name of the galleryid column.
	public $propertyname = 'items';
	public $categoryname = '';
	public $metadata = array();
	
	public $canedit = false;
	public $candelete = true;
	public $canmove = true;
	
	// Commands will be evaluated as functions. If the command should be evaluated as a string, it must be enclosed in single quotes
	public $deletecommand;
	public $editcommand;
	public $movecommand;
	public $clickcommand;
	
	public $loadscripts = false;	
	public $storage = 'folder';
	
	function __construct($galleryid,$imagepath,$width=0,$height=0,$thumbwidth=0,$thumbheight=0,$thumbpadding=0,$thumbspacing=0,$buttonpadding=0){
		$this->galleryid = $galleryid;
		$this->imagepath = trim($imagepath,'/\\') . '/';
		$this->width = $width;
		$this->height = $height;
		$this->itemwidth = $thumbwidth;
		$this->itemheight = $thumbheight;
		$this->itempadding = $thumbpadding;
		$this->itemspacing = $thumbspacing;
		$this->buttonpadding = $buttonpadding;
	}
	
	function prepareEdit($edit,$imagewidth=0,$imageheight=0){		
		$this->canedit = $edit;
		$this->imagewidth = $imagewidth;
		$this->imageheight = $imageheight;
	}
	
	public function addMetaDataField($name,$label,$type){
		$this->metadata[] = array('name'=>$name,'label'=>$label,'type'=>$type);
	}
	
	public function drawGallery(){		
		$code = '?'.createRandomCode(8);
		$path = $GLOBALS['documentroot'].'/'.$this->imagepath;
		$webpath = $GLOBALS['webroot'].$this->imagepath;
		$defaultpath = $GLOBALS['webroot'].trim($this->defaultpath,'/\\') . '/';
		$iconpath = $GLOBALS['webroot'].trim($this->iconpath,'/\\') . '/';
		$videoplayer = empty($this->videoplayer)?'':$GLOBALS['webroot'].trim($this->videoplayer,'/\\');
		$audioplayerpath= empty($this->audioplayerpath)?'':$GLOBALS['webroot'].trim($this->audioplayerpath,'/\\');
		if($this->loadscripts && !$GLOBALS['GalleryViewScriptLoaded']){
			$res .= '<script language="javascript" src="'.$GLOBALS['webroot'].'controls/galleryview/GalleryView.js"></script>';
			$res .= '<link rel="stylesheet" href="'.$GLOBALS['webroot'].'controls/galleryview/galleryview.css" />';
			$GLOBALS['GalleryViewScriptLoaded'] = true;
		}
		
		$items = array();
		switch($this->storage){
			case 'database':
				if(empty($this->galleryid) || !is_numeric($this->galleryid)) $this->galleryid = 0;
				$items = array();
				$itemset = $GLOBALS['db']->select("select * from {$this->tablename} where {$this->propertyname} = {$this->galleryid} order by position");
				if(is_array($itemset)){
					foreach($itemset as $itm){
						$data = '{';
						foreach($this->metadata as $md){
							$name = $md['name'];
							$value = preg_replace('/[\r\n\t\v]+/',' ',addslashes($itm[$name]));
							$data .= "'$name':'$value',";
						}
						$itm['data'] = rtrim($data,',') . '}';
						$items[$itm['id']] = $itm;	
					}
				}
				break;
			case 'serialised':
				$block = ContentModule::getContentBlock($this->galleryid);
				$items = array();
				if(is_array($block->{$this->propertyname})){
					foreach($block->{$this->propertyname} as $itm){
						$data = '{';
						foreach($this->metadata as $md){
							$name = $md['name'];
							$value = preg_replace('/[\r\n\t\v]+/',' ',addslashes($itm[$name]));
							$data .= "'$name':'$value',";
						}
						$itm['data'] = rtrim($data,',') . '}';
						$items[$itm['id']] = $itm;	
					}
				}
				break;
			default:
				$files = glob($path.'*_th.jpg');
				natsort($files);
				$ind = 1;
				foreach($files as $file){
					$item = array();
					$item['thumbname'] = basename($file);
					$item['filename'] = str_replace('_th','',$item['thumbname']);
					$item['id'] = $item['filename'];
					$item['position'] = $ind++;
					$item['data'] = '{}';
					$item['itemtype'] = 'image';
					$items[$item['id']] = $item;
				}
				break;
		}
		
		$divstyle = ' style="position: relative;';
		if($this->width) $divstyle .= 'width: ' . $this->width . 'px;';
		if($this->height) $divstyle .= 'height: ' . $this->height . 'px; overflow: auto;';
		$divstyle .= '"';
		$res .= '<div class="GV_container"'.$divstyle.' id="'.$this->prefix.'_container">';
		foreach($items as $id=>$item){
			$res .= '<span class="GV_editcell" style="display: inline-block;" id="'.$this->prefix.'_'.$id.'">';
			$imgstyle = 'width: ' . $this->itemwidth . 'px; height: ' . $this->itemheight . 'px;';
			if(!empty($item['thumbname']) && file_exists($path.$item['thumbname'])){
				list($width,$height,$type,$str) = getimagesize($path.$item['thumbname']);	
				if($height < $this->itemheight) $imgstyle .= 'padding-top: ' . (($this->itemheight-$height)/2) . 'px;';
				$thumbsrc = $webpath.$item['thumbname'];
			}else{	
				$thumbsrc = $defaultpath.'GV_default'.$item['itemtype'].'.png';
			}
			$res .= '<div class="GV_thumb" align="center" style="'.$imgstyle.'">';
			$res .= '<a href="javascript:'.$this->prefix.'.ShowItem(\'' . $id . '\');">';
			$res .= '<img src="'.$thumbsrc.$code.'" '.$str.' align="top" ondragstart="return false;" onmousedown="return false;" />';
			$res .= '</a>';
			$res .= '</div>';
			$res .= '<div class="GV_buttons" style="width: ' . ($this->itemwidth-$this->buttonpadding) . 'px;">';
			if($this->canmove) $res .= '<img src="'.$iconpath.'moves.gif" class="GV_movebutton" align="top" onmousedown="'.$this->prefix.'.BeginDragDrop(event,\''.$id.'\');" ondragstart="return false;" />';
			if($this->canedit) $res .= '<a href="javascript:'.$this->prefix.'.EditItem(\''.$id.'\');" class="GV_editbutton"><img src="'.$iconpath.'edits.gif" align="top" /></a>';
			if($this->candelete) $res .= '<a href="javascript:' . $this->prefix . '.DeleteItem(\''.$id.'\');" class="GV_deletebutton"><img src="'.$iconpath.'deletes.gif" align="top" /></a>';
			$res .= '</div>';
			$res .= '</span>';
		}
		$res .= '</div>';
		
		$res .= '<script language="javascript">';
		$res .= "var {$this->prefix} = new GalleryView('{$this->prefix}','{$this->galleryid}','{$this->storage}','{$webpath}',{$this->itemwidth},{$this->itemheight},{$this->itempadding},{$this->itemspacing},{$this->buttonpadding},{$this->imagewidth},{$this->imageheight},'{$this->tablename}','{$this->propertyname}','{$this->usedisabled}','{$defaultpath}','{$iconpath}','{$videoplayer}','{$audioplayerpath}',{$this->resizestyle});";
		if(!empty($this->clickcommand)) $res .= "{$this->prefix}.callbacks.click = {$this->clickcommand};";
		if(!$this->canedit) $res .= "{$this->prefix}.canedit = false;";
		elseif(!empty($this->editcommand)) $res .= "{$this->prefix}.callbacks.edit = {$this->editcommand};";
		if(!$this->candelete) $res .= "{$this->prefix}.candelete = false;";
		elseif(!empty($this->deletecommand)) $res .= "{$this->prefix}.callbacks.del = {$this->deletecommand};";
		if(!$this->canmove) $res .= "{$this->prefix}.canmove = false;";
		elseif(!empty($this->movecommand)) $res .= "{$this->prefix}.callbacks.move = {$this->movecommand};";
		foreach($items as $item){
			$res .= "{$this->prefix}.AddExistingItem('{$item['id']}','{$item['itemtype']}','".addslashes($item['filename'])."','{$item['thumbname']}','{$item['position']}','{$item['uploadoption']}',{$item['data']});";	
		}
		foreach($this->metadata as $md){
			$res .= "{$this->prefix}.AddMetaDataField('{$md['name']}','".addslashes($md['label'])."','{$md['type']}');";	
		}
		$res .= '</script>';
		return $res;
	}
}

?>