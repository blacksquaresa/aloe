<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('../../lib/Global.lib.php'); 
require_once('Files.lib.php'); 
require_once('Agent.lib.php'); 
$agent->init();

function GalleryView_DeleteItem($galleryid,$itemid,$storage,$path='',$tablename='',$propertyname=''){
	if(empty($galleryid)) return 'Invalid Gallery ID';
	if(empty($itemid)) return 'Invalid Item ID';
	switch($storage){
		case 'database':
			if(empty($tablename)) return 'Invalid tablename';
			if(empty($propertyname)) return 'Invalid gallery id column name';
			$path = $GLOBALS['documentroot'] . '/' . trim($path,'./') . '/';
			$item = $GLOBALS['db']->selectrow("select * from $tablename where id = $itemid");
			$tablename = mysql_real_escape_string($tablename);
			$propertyname = mysql_real_escape_string($propertyname);
			$GLOBALS['db']->begintransaction();
			$res = $GLOBALS['db']->delete($tablename,$itemid);
			if($res===false){
				$error = mysql_error();	
				$GLOBALS['db']->rollbacktransaction();
				return $error;
			}
			$res = $GLOBALS['db']->execute("update $tablename set position = position - 1 where position > {$item['position']} and $propertyname = $galleryid");
			if($res===false){
				$error = mysql_error();	
				$GLOBALS['db']->rollbacktransaction();
				return $error;
			}
			if(is_dir($path)){
				$imagefile = $path.$item['filename'];
				$thumbfile = $path.$item['thumbname'];
				@unlink($imagefile);
				@unlink($thumbfile);
			}
			$GLOBALS['db']->committransaction();
			return 1;
		case 'serialised':
			$path = $GLOBALS['documentroot'] . '/' . trim($path,'./') . '/';
			if(is_dir($path)){
				try{
					$block = ContentModule::getContentBlock($galleryid);
					$page = Page::GetNewPage($block->pageid,true);
				}catch(Exception $e){
					return $e->Message;
				}
				
				$items = array();
				foreach($block->items as $itm){
					if($itm['id'] == $itemid) $delitem = $itm;	
					else $items[] = $itm;
				}
				foreach($items as &$itm){
					if($itm['position'] > $delitem['position']) $itm['position'] -= 1;	
				}
				$res = $page->updateContentBlock($block->id,$block->content,array('items'=>$items),$error);
				if($res===false){
					return $error;
				}
				
				$imagefile = $path.$delitem['filename'];
				$thumbfile = $path.$delitem['thumbname'];
				@unlink($imagefile);
				@unlink($thumbfile);
				return 1;
			}else{
				return 'Folder not found';
			}
		case 'folder':
			$path = $GLOBALS['documentroot'] . '/' . trim($path,'./') . '/';
			if(is_dir($path)){
				list($gid,$pos) = split('_',pathinfo($itemid,PATHINFO_FILENAME));
				$imagefile = $path . $itemid;
				$thumbfile = str_replace('.jpg','_th.jpg',$imagefile);
				unlink($imagefile);
				unlink($thumbfile);
				$files = glob($path.'*_th.jpg');
				natsort($files);
				foreach($files as $thumbpath){
					$imagepath = str_replace('_th.jpg','.jpg',$thumbpath);					
					list($fid,$fpos) = split('_',pathinfo($imagepath,PATHINFO_FILENAME));
					if($fpos > $pos){
						$newimagepath = $path.$fid.'_'.($fpos-1).'.jpg';
						$newthumbpath = $path.$fid.'_'.($fpos-1).'_th.jpg';
						rename($imagepath,$newimagepath);
						rename($thumbpath,$newthumbpath);
					}
				}
				return 1;
			}else{
				return 'Folder not found';
			}
	}	
}

function GalleryView_MoveItem($galleryid,$itemid,$targetposition,$storage,$path='',$tablename='',$propertyname=''){
	try{
		if(empty($galleryid)) return 'Invalid Gallery ID';
		if(empty($itemid)) return 'Invalid Item ID';
		switch($storage){
			case 'database':
				if(empty($tablename)) return 'Invalid tablename';
				if(empty($propertyname)) return 'Invalid gallery id column name';
				// database positions are 1-based, while the others are 0-based, so we must compensate.
				$targetposition ++;
				$item = $GLOBALS['db']->selectrow("select * from $tablename where id = $itemid");
				$sourceposition = $item['position'];
				$tablename = mysql_real_escape_string($tablename);
				$propertyname = mysql_real_escape_string($propertyname);
				$GLOBALS['db']->begintransaction();
				if($targetposition > $sourceposition){
					$res = $GLOBALS['db']->execute("update $tablename set position = position - 1 where position > $sourceposition and position <= $targetposition and $propertyname = $galleryid");
				}else{
					$res = $GLOBALS['db']->execute("update $tablename set position = position + 1 where position >= $targetposition and position < $sourceposition and $propertyname = $galleryid");
				}
				if($res===false){
					$error = mysql_error();	
					$GLOBALS['db']->rollbacktransaction();
					return $error;
				}
				$res = $GLOBALS['db']->update($tablename,array('position'=>$targetposition),array('id'=>$itemid));
				if($res===false){
					$error = mysql_error();	
					$GLOBALS['db']->rollbacktransaction();
					return $error;
				}
				$GLOBALS['db']->committransaction();
				break;
			case 'serialised':
				$path = $GLOBALS['documentroot'] . '/' . trim($path,'./') . '/';
				if(is_dir($path)){
					try{
						$block = ContentModule::getContentBlock($galleryid);
						$page = Page::GetNewPage($block->pageid,true);
					}catch(Exception $e){
						return $e->Message;
					}
					$sourceposition = 0;
					foreach($block->items as $itm){
						if($itm['id'] == $itemid){
							$sourceposition = $itm['position'];
							break;	
						}
					}
					foreach($block->items as &$itm){
						if($itm['id'] == $itemid) $itm['position'] = $targetposition;	
						else{
							if($sourceposition > $targetposition){
								if($itm['position'] < $sourceposition && $itm['position'] >= $targetposition){
									$itm['position']++;	
								}	
							}else{
								if($itm['position'] > $sourceposition && $itm['position'] <= $targetposition){
									$itm['position']--;	
								}	
							}	
						}
					}
					$res = $page->updateContentBlock($block->id,$block->content,array('items'=>$block->items),$error);
					if($res===false){
						return $error;
					}
				}
				break;
			case 'folder':
				$path = $GLOBALS['documentroot'] . '/' . trim($path,'./') . '/';	
				if(is_dir($path)){
					list($gid,$pos) = split('_',pathinfo($itemid,PATHINFO_FILENAME));
					$imagefile = $path . $itemid;
					$thumbfile = str_replace('.jpg','_th.jpg',$imagefile);
					$files = glob($path.'*_th.jpg');
					natsort($files);
					$files = array_values($files);
					
					rename($imagefile,$path.'tmp.jpg');
					rename($thumbfile,$path.'tmp_th.jpg');
					
					if($pos > $targetposition){
						for($i=$pos-1;$i>=$targetposition;$i--){
							$thumbfile = $files[$i];
							$imagefile = str_replace('_th.jpg','.jpg',$thumbfile);
							$newthumbfile = str_replace('_'.$i,'_'.($i+1),$thumbfile);		
							$newimagefile = str_replace('_'.$i,'_'.($i+1),$imagefile);
							rename($imagefile,$newimagefile);
							rename($thumbfile,$newthumbfile);				
						}
					}else{
						for($i=$pos+1;$i<=$targetposition;$i++){
							$thumbfile = $files[$i];
							$imagefile = str_replace('_th.jpg','.jpg',$thumbfile);
							$newthumbfile = str_replace('_'.$i,'_'.($i-1),$thumbfile);		
							$newimagefile = str_replace('_'.$i,'_'.($i-1),$imagefile);
							rename($imagefile,$newimagefile);
							rename($thumbfile,$newthumbfile);				
						}
					}
					
					rename($path.'tmp.jpg',$path.$galleryid.'_'.$targetposition.'.jpg');
					rename($path.'tmp_th.jpg',$path.$galleryid.'_'.$targetposition.'_th.jpg');
					
					return 1;
				}else{
					return 'Folder not found';
				}
		}
	}catch(exception $err){
		return $err->getMessage();
	}
}

?>