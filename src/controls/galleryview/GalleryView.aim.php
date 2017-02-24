<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('../../lib/Global.lib.php'); 
require_once('Images.lib.php'); 
require_once('Files.lib.php'); 

if(isset($_REQUEST['GV_updateimage'])){
	
	/* ==========================================
						Image
	========================================== */
	
	$galleryid = $_REQUEST['galleryid'];
	if(empty($galleryid) || !is_numeric($galleryid)){
		echo 'Could not identify the gallery';
		exit;	
	}
	$storage = $_REQUEST['storage'];
	if(empty($storage)){
		echo 'Could not identify the storage method';
		exit;	
	}
	switch($storage){
		case 'database':
		case 'serialised':
			$_extensions = array('jpg','jpeg','gif','png');
			break;
		default:
			$_extensions = array('jpg','jpeg');
			break;
	}
	// Check inputs
	if($_FILES['file'] && $_FILES['file']['error']==0 && !in_array(strtolower(pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION)),$_extensions)){
		echo 'illegal file type';
		exit;
	}
	if($_FILES['thumb'] && $_FILES['thumb']['error']==0 && !in_array(strtolower(pathinfo($_FILES['thumb']['name'],PATHINFO_EXTENSION)),$_extensions)){
		echo 'illegal thumbnail type';
		exit;
	}
	$webpath = $_REQUEST['webpath'];
	if(empty($webpath)){
		echo 'Could not identify the file path';
		exit;	
	}	
	$itemid = $_REQUEST['itemid'];
	if(empty($itemid) && (!$_FILES['file'] || $_FILES['file']['error'] == UPLOAD_ERR_NO_FILE)){
		echo 'Cannot create a new item without a file';
		exit;		
	}
	$props = $_REQUEST['prop'];
	$makethumb = $_REQUEST['makethumb'];
	$galleryname = $_REQUEST['galleryname'];
	$sizes = $_REQUEST['sizes'];
	$resizestyle = $_REQUEST['resizestyle'];
	if(empty($resizestyle)) $resizestyle = IMAGERESIZETYPE_WIDTHHEIGHT; 
	if(empty($sizes)) $sizes = '650,450,150,100';
	list($itemwidth,$itemheight,$thumbwidth,$thumbheight) = explode(',',$sizes);
	$tablename = empty($_REQUEST['tablename'])?'galleryitems':$_REQUEST['tablename'];
	$propertyname = empty($_REQUEST['propertyname'])?'galleryid':$_REQUEST['propertyname'];
	$cleanfiles = array();
	
	$path = $GLOBALS['documentroot'].'/'.trim($webpath,'./').'/';
	if(!file_exists($path)){
		mkdir($path,0755);
	}
	
	// Save data
	$GLOBALS['db']->begintransaction();
	switch($storage){
		case 'database':
			if(empty($itemid)){
				$position = $GLOBALS['db']->selectsingle("select max(position) from $tablename where $propertyname = $galleryid") + 1;
			}else{
				$item = $GLOBALS['db']->selectrow("select * from $tablename where id = $itemid");
				$position = $item['position'];
			}
			if($_FILES['file'] && $_FILES['file']['error']!=UPLOAD_ERR_NO_FILE){
				$filename = pathinfo(getCleanFilename($path . $_FILES['file']['name']),PATHINFO_FILENAME);
				$ext = '.'.strtolower(pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION));
			}else{
				$filename = pathinfo($item['filename'],PATHINFO_FILENAME);
				$ext = '.'.strtolower(pathinfo($item['filename'],PATHINFO_EXTENSION));
			}			
			if($_FILES['thumb'] && $_FILES['thumb']['error']!=UPLOAD_ERR_NO_FILE){
				$thumbname = pathinfo(getCleanFilename($path . $_FILES['thumb']['name']),PATHINFO_FILENAME);
				if($thumbname==$filename) $thumbname.='_th';
				$text = '.'.strtolower(pathinfo($_FILES['thumb']['name'],PATHINFO_EXTENSION));
			}else{
				if($makethumb && $_FILES['file'] && $_FILES['file']['error']!=UPLOAD_ERR_NO_FILE){
					$text = $ext;
				}else{
					$text = '.'.strtolower(pathinfo($item['thumbname'],PATHINFO_EXTENSION));
				}
				if(!empty($item['thumbname'])){
					$thumbname = pathinfo($item['thumbname'],PATHINFO_FILENAME);
				}else{
					$thumbname = $filename . '_th';
				}
			}	
			if(empty($itemid)){
				if($text=='.'){
					$makethumb = true;
					$text = $ext;
				}
			}
			
			$filename = checkDatabaseFileName($tablename,$filename,$ext,$itemid);
			if(!empty($item['filename']) && $item['filename'] != $filename.$ext) $cleanfiles[] = $path.$item['filename']; // mark the old file for cleanup
			$thumbname = checkDatabaseFileName($tablename,$thumbname,$text,$itemid);
			if(!empty($item['thumbname']) && $item['thumbname'] != $thumbname.$text) $cleanfiles[] = $path.$item['thumbname']; // mark the old file for cleanup
				
			$values = array();
			$values[$propertyname] = $galleryid;
			$values['itemtype'] = 'image';
			$values['position'] = $position;
			$values['filename'] = $filename.$ext;
			$values['thumbname'] = $thumbname.$text;
			if(is_array($props)){
				foreach($props as $key=>$value){
					$values[$key] = $value;	
				}
			}
			$res = $GLOBALS['db']->insertupdate($tablename,$values,array('id'=>$itemid));
			if($res===false){
				echo $error;
				$GLOBALS['db']->rollbacktransaction();
				exit;	
			}elseif(empty($itemid)){
				$itemid = $res;	
			}
			break;
		case 'serialised':
			try{
				$block = ContentModule::getContentBlock($galleryid);
				$page = Page::GetNewPage($block->pageid,true);
			}catch(Exception $e){
				echo $e->Message;
				$GLOBALS['db']->rollbacktransaction();
				exit;
			}
			$items = $block->items;
			if(!is_array($items)) $items = array();
			$item = null;
			$position = count($items);
			foreach($items as &$checkitem){
				if($checkitem['id'] == $itemid){
					$item = &$checkitem;
					$position = $item['position'];
					break;
				}	
			}
			
			if($_FILES['file'] && $_FILES['file']['error']!=UPLOAD_ERR_NO_FILE){
				$filename = pathinfo(getCleanFilename($path . $_FILES['file']['name']),PATHINFO_FILENAME);
				$ext = '.'.strtolower(pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION));
			}else{
				$filename = pathinfo($item['filename'],PATHINFO_FILENAME);
				$ext = '.'.strtolower(pathinfo($item['filename'],PATHINFO_EXTENSION));
			}			
			if($_FILES['thumb'] && $_FILES['thumb']['error']!=UPLOAD_ERR_NO_FILE){
				$thumbname = pathinfo(getCleanFilename($path . $_FILES['thumb']['name']),PATHINFO_FILENAME);
				if($thumbname==$filename) $thumbname.='_th';
				$text = '.'.strtolower(pathinfo($_FILES['thumb']['name'],PATHINFO_EXTENSION));
			}else{
				if($makethumb && $_FILES['file'] && $_FILES['file']['error']!=UPLOAD_ERR_NO_FILE){
					$text = $ext;
					$thumbname = $filename . '_th';
				}else{
					$text = '.'.strtolower(pathinfo($item['thumbname'],PATHINFO_EXTENSION));
					if(!empty($item['thumbname'])){
						$thumbname = pathinfo($item['thumbname'],PATHINFO_FILENAME);
					}else{
						$thumbname = $filename . '_th';
					}
				}
			}
						
			if(empty($itemid)){
				$itemid = $galleryid.'_'.createRandomCode(8);
				if($text=='.'){
					$makethumb = true;
					$text = $ext;
				}
			}
			$filename = checkSerialisedFileName($items,$filename,$ext,$itemid);
			if(!empty($item['filename']) && $item['filename'] != $filename.$ext) $cleanfiles[] = $path.$item['filename']; // mark the old file for cleanup
			$thumbname = checkSerialisedFileName($items,$thumbname,$text,$itemid);
			if(!empty($item['thumbname']) && $item['thumbname'] != $thumbname.$text) $cleanfiles[] = $path.$item['thumbname']; // mark the old file for cleanup
			if(empty($item)){
				$item = array();
				$items[] = &$item;
			}
			$item['id'] = $itemid;
			$item['filename'] = $filename.$ext;
			$item['thumbname'] = $thumbname.$text;
			$item['position'] = $position;
			$item['itemtype'] = 'image';
			if(is_array($props)){
				foreach($props as $key=>$value){
					$item[$key] = $value;	
				}	
			}
			$res = $page->updateContentBlock($block->id,$block->content,array('items'=>$items),$error);
			if($res===false){
				echo $error;
				$GLOBALS['db']->rollbacktransaction();
				exit;	
			}
			break;
		default:
			if(empty($itemid)){
				$currentfiles = glob($path.'*_th.jpg');
				$position = count($currentfiles);
				$itemid = $galleryid.'_'.$position.'.jpg';
				$filename = $galleryid.'_'.$position;
				$ext = '.jpg';
				$text = '.jpg';
			}else{
				$filename = pathinfo($itemid,PATHINFO_FILENAME);
				$ext = '.jpg';
				$text = '.jpg';
			}
			break;	
	}	
	if($_FILES['file'] && $_FILES['file']['error'] != UPLOAD_ERR_NO_FILE){
		$res = @image_upload_file($_FILES['file'],$path.$filename,$itemwidth,$itemheight,$error,$resizestyle);
		if(!$res){
			echo $error;
			$GLOBALS['db']->rollbacktransaction();
			exit;	
		}
	}else{
		$makethumb = false;
	}
	if(empty($thumbname)) $thumbname = $filename.'_th';
	if($_FILES['thumb'] && $_FILES['thumb']['error'] != UPLOAD_ERR_NO_FILE){
		$res = @image_upload_file($_FILES['thumb'],$path.$thumbname,$thumbwidth,$thumbheight,$error,IMAGERESIZETYPE_CROP);
		if(!$res){
			echo $error;
			if(file_exists($path.$filename.$ext)) @unlink($path.$filename.$ext);
			$GLOBALS['db']->rollbacktransaction();
			exit;	
		}		
	}elseif($makethumb){
		$res = @image_resize_crop($path.$filename.$ext,$path.$thumbname,$thumbwidth,$thumbheight);
		if(!$res){
			echo 'There was an error creating the thumbnail image';
			if(file_exists($path.$filename.$ext)) @unlink($path.$filename.$ext);
			$GLOBALS['db']->rollbacktransaction();
			exit;	
		}
	}
	
	$GLOBALS['db']->committransaction();
	// cleanup - delete old files now that everything has completed successfully
	if(is_array($cleanfiles)){
		foreach($cleanfiles as $cleanfile){
			if(!empty($cleanfile) && file_exists($cleanfile)) @unlink($cleanfile);
		}	
	}
	
	
	echo '{'."'galleryid':$galleryid,'galleryname':'$galleryname','id':'$itemid','filename':'$filename$ext','thumbname':'$thumbname$text','position':'$position','thumbwidth':$thumbwidth,'thumbheight':$thumbheight,'uploadoption':''".'}';
	
}elseif(isset($_REQUEST['GV_updatevideo'])){
	
	/* ==========================================
						Video
	========================================== */
	
	$galleryid = $_REQUEST['galleryid'];
	if(empty($galleryid) || !is_numeric($galleryid)){
		echo 'Could not identify the gallery';
		exit;	
	}
	$storage = $_REQUEST['storage'];
	if(empty($storage)){
		echo 'Could not identify the storage method';
		exit;	
	}
	switch($storage){
		case 'database':
		case 'serialised':
			$_extensions = array('jpg','jpeg','gif','png');
			break;
		default:
			$_extensions = array('jpg','jpeg');
			break;
	}
	// Check inputs
	if($_FILES['file'] && $_FILES['file']['error']==0 && strtolower(pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION))!='flv'){
		echo 'illegal file type';
		exit;
	}
	if($_FILES['thumb'] && $_FILES['thumb']['error']==0 && !in_array(strtolower(pathinfo($_FILES['thumb']['name'],PATHINFO_EXTENSION)),$_extensions)){
		echo 'illegal thumbnail type';
		exit;
	}
	$webpath = $_REQUEST['webpath'];
	if(empty($webpath)){
		echo 'Could not identify the file path';
		exit;	
	}	
	$itemid = $_REQUEST['itemid'];
	if(empty($itemid) && ((!$_FILES['file'] || $_FILES['file']['error'] == UPLOAD_ERR_NO_FILE) && empty($_REQUEST['embed']) && empty($_REQUEST['resource']))){
		echo 'Cannot create a new item without a video';
		exit;		
	}
	$props = $_REQUEST['prop'];
	$sizes = $_REQUEST['sizes'];
	if(empty($sizes)) $sizes = '150,100';
	list($thumbwidth,$thumbheight) = explode(',',$sizes);
	$galleryname = $_REQUEST['galleryname'];
	$tablename = empty($_REQUEST['tablename'])?'galleryitems':$_REQUEST['tablename'];
	$propertyname = empty($_REQUEST['propertyname'])?'galleryid':$_REQUEST['propertyname'];
	$watermark = empty($_REQUEST['watermark'])?null:$GLOBALS['documentroot'].'/'.ltrim($_REQUEST['watermark'],'./');
	
	$path = $GLOBALS['documentroot'].'/'.trim($webpath,'./').'/';
	if(!file_exists($path)){
		mkdir($path);
	}
	
	// Save data
	$GLOBALS['db']->begintransaction();
	switch($storage){
		case 'database':
			if(!empty($itemid)){
				$item = $GLOBALS['db']->selectrow("select * from $tablename where id = $itemid");
				$position = $item['position'];
				$thumbname = pathinfo($item['thumbname'],PATHINFO_FILENAME);
				if($item['uploadoption']=='file') $oldfile = $item['filename'];
				$oldthumb = $item['thumbname'];
			}else{
				$position = $GLOBALS['db']->selectsingle("select max(position) from $tablename where $propertyname = $galleryid") + 1;
			}
		
			if($_FILES['file'] && $_FILES['file']['error'] != UPLOAD_ERR_NO_FILE){
				$root = getCleanRoot(pathinfo($_FILES['file']['name'],PATHINFO_FILENAME),60,'-',true);
				$filename = $root . '.' . pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION);
				$uploadoption = 'file';
			}elseif(!empty($_REQUEST['embed'])){
				$filename = $_REQUEST['embed'];
				$uploadoption = 'embed';
			}elseif(!empty($_REQUEST['resource'])){
				$filename = $_REQUEST['resource'];
				$uploadoption = 'resource';
			}elseif(!empty($item['filename'])){
				$filename = $item['filename'];
				$uploadoption = $item['uploadoption'];
			}
			
			if($_FILES['thumb'] && $_FILES['thumb']['error']!=UPLOAD_ERR_NO_FILE){
				$text = '.'.strtolower(pathinfo($_FILES['thumb']['name'],PATHINFO_EXTENSION));
				if(empty($thumbname)){					
					$thumbname = $galleryid.'_'.createRandomCode(8);
					while(file_exists($path.$thumbname.$text)) $thumbname = $galleryid.'_'.createRandomCode(8);
				}
			}elseif(!empty($item['thumbname'])){
				$thumbname = pathinfo($item['thumbname'],PATHINFO_FILENAME);
				$text = '.'.pathinfo($item['thumbname'],PATHINFO_EXTENSION);
			}
			
			$values = array();
			$values[$propertyname] = $galleryid;
			$values['itemtype'] = 'video';
			$values['position'] = $position;
			$values['filename'] = $filename;
			$values['thumbname'] = $thumbname.$text;
			$values['uploadoption'] = $uploadoption;
			foreach($props as $key=>$value){
				$values[$key] = $value;	
			}
			$res = $GLOBALS['db']->insertupdate($tablename,$values,array('id'=>$itemid));
			if($res===false){
				echo $error;
				$GLOBALS['db']->rollbacktransaction();
				exit;	
			}elseif(empty($itemid)){
				$itemid = $res;	
			}
			break;	
	}	
	if($_FILES['file'] && $_FILES['file']['error'] != UPLOAD_ERR_NO_FILE){
		$res = @uploadfile($_FILES['file'],$path.$filename,'flv');
		if(!$res){
			echo 'Error uploading file';
			$GLOBALS['db']->rollbacktransaction();
			exit;	
		}
	}
	if($_FILES['thumb'] && $_FILES['thumb']['error'] != UPLOAD_ERR_NO_FILE){
		$res = @image_upload_file($_FILES['thumb'],$path.$thumbname,$thumbwidth,$thumbheight,$error,IMAGERESIZETYPE_CROP,null,$watermark);
		if(!$res){
			echo $error;
			$GLOBALS['db']->rollbacktransaction();
			exit;	
		}		
	}
	if($oldfile && strtolower($oldfile) != strtolower($filename)){
		@unlink($path.$oldfile);	
	}
	if($oldthumb && strtolower($oldthumb) != strtolower($thumbname.$text)){
		@unlink($path.$oldthumb);	
	}
	
	$GLOBALS['db']->committransaction();
	
	echo '{'."'galleryid':$galleryid,'galleryname':'$galleryname','id':'$itemid','filename':'$filename','thumbname':'".$thumbname."$text','position':'$position','thumbwidth':$thumbwidth,'thumbheight':$thumbheight,'uploadoption':'$uploadoption'".'}';
	
}elseif(isset($_REQUEST['GV_updateaudio'])){
	
	/* ==========================================
						Audio
	========================================== */
	//require_once('Audio.lib.php'); 
	
	$galleryid = $_REQUEST['galleryid'];
	if(empty($galleryid) || !is_numeric($galleryid)){
		echo 'Could not identify the gallery';
		exit;	
	}
	$storage = $_REQUEST['storage'];
	if(empty($storage)){
		echo 'Could not identify the storage method';
		exit;	
	}
	switch($storage){
		case 'database':
		case 'serialised':
			$_extensions = array('jpg','jpeg','gif','png');
			break;
		default:
			$_extensions = array('jpg','jpeg');
			break;
	}
	// Check inputs
	if($_FILES['file'] && $_FILES['file']['error']==0 && strtolower(pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION))!='mp3'){
		echo 'illegal file type';
		exit;
	}
	if($_FILES['thumb'] && $_FILES['thumb']['error']==0 && !in_array(strtolower(pathinfo($_FILES['thumb']['name'],PATHINFO_EXTENSION)),$_extensions)){
		echo 'illegal thumbnail type';
		exit;
	}
	if((!$_FILES['file'] || $_FILES['file']['error']!=0) && !empty($_REQUEST['url'])){
		if(!isValidURL($_REQUEST['url'])){
			echo 'invalid URL';
			exit;
		}elseif(strtolower(pathinfo($_REQUEST['url'],PATHINFO_EXTENSION))!='mp3'){
			echo 'The URL links to an invalid file type. Please make sure the URL specifically points to a .mp3 file. No other URLs will be accepted, for security reasons.';
			exit;
		}
	}
	$webpath = $_REQUEST['webpath'];
	if(empty($webpath)){
		echo 'Could not identify the file path';
		exit;	
	}	
	$itemid = $_REQUEST['itemid'];
	if(empty($itemid) && ((!$_FILES['file'] || $_FILES['file']['error'] == UPLOAD_ERR_NO_FILE) && empty($_REQUEST['embed']) && empty($_REQUEST['resource']))){
		echo 'Cannot create a new item without an audio file';
		exit;		
	}
	$props = $_REQUEST['prop'];
	$sizes = $_REQUEST['sizes'];
	if(empty($sizes)) $sizes = '150,100';
	list($thumbwidth,$thumbheight) = explode(',',$sizes);
	$galleryname = $_REQUEST['galleryname'];
	$tablename = empty($_REQUEST['tablename'])?'galleryitems':$_REQUEST['tablename'];
	$propertyname = empty($_REQUEST['propertyname'])?'galleryid':$_REQUEST['propertyname'];
	$watermark = empty($_REQUEST['watermark'])?null:$GLOBALS['documentroot'].'/'.ltrim($_REQUEST['watermark'],'./');
	
	$path = $GLOBALS['documentroot'].'/'.trim($webpath,'./').'/';
	if(!file_exists($path)){
		mkdir($path);
	}
	
	// Save data
	$GLOBALS['db']->begintransaction();
	switch($storage){
		case 'database':
			if(!empty($itemid)){
				$item = $GLOBALS['db']->selectrow("select * from $tablename where id = $itemid");
				$position = $item['position'];
				$thumbname = pathinfo($item['thumbname'],PATHINFO_FILENAME);
				if($item['uploadoption']=='file') $oldfile = $item['filename'];
				$oldthumb = $item['thumbname'];
			}else{
				$position = $GLOBALS['db']->selectsingle("select max(position) from $tablename where $propertyname = $galleryid") + 1;
			}
			
			if($_FILES['file'] && $_FILES['file']['error'] != UPLOAD_ERR_NO_FILE){
				$root = getCleanRoot(pathinfo($_FILES['file']['name'],PATHINFO_FILENAME),60,'-',true);
				$filename = $root . '.' . pathinfo($_FILES['file']['name'],PATHINFO_EXTENSION);
				$uploadoption = 'file';
				$info = getAudioFileInfo($_FILES['file']['tmp_name']);
			}elseif(!empty($_REQUEST['url'])){
				$filename = $_REQUEST['url'];
				$uploadoption = 'url';
				$info = getAudioFileInfo($filename);
			}elseif(!empty($_REQUEST['resource'])){
				$filename = $_REQUEST['resource'];
				$uploadoption = 'resource';
				$info = getAudioFileInfo($GLOBALS['documentroot'].$filename);
			}elseif(!empty($item['filename'])){
				$filename = $item['filename'];
				$uploadoption = $item['uploadoption'];
				$info = null;
			}
			
			if($_FILES['thumb'] && $_FILES['thumb']['error']!=UPLOAD_ERR_NO_FILE){
				$text = '.'.strtolower(pathinfo($_FILES['thumb']['name'],PATHINFO_EXTENSION));
				if(empty($thumbname)){					
					$thumbname = $galleryid.'_'.createRandomCode(8);
					while(file_exists($path.$thumbname.$text)) $thumbname = $galleryid.'_'.createRandomCode(8);
				}
			}elseif(!empty($item['thumbname'])){
				$thumbname = pathinfo($item['thumbname'],PATHINFO_FILENAME);
				$text = '.'.pathinfo($item['thumbname'],PATHINFO_EXTENSION);
			}
			
			$values = array();
			$values[$propertyname] = $galleryid;
			$values['itemtype'] = 'audio';
			$values['position'] = $position;
			$values['filename'] = $filename;
			$values['thumbname'] = $thumbname.$text;
			$values['uploadoption'] = $uploadoption;
			foreach($props as $key=>$value){
				$values[$key] = $value;	
			}
			$res = $GLOBALS['db']->insertupdate($tablename,$values,array('id'=>$itemid));
			if($res===false){
				echo $error;
				$GLOBALS['db']->rollbacktransaction();
				exit;	
			}elseif(empty($itemid)){
				$itemid = $res;	
			}
			break;	
	}	
	if($_FILES['file'] && $_FILES['file']['error'] != UPLOAD_ERR_NO_FILE){
		$res = @uploadfile($_FILES['file'],$path.$filename,'mp3');
		if(!$res){
			echo 'Error uploading file';
			$GLOBALS['db']->rollbacktransaction();
			exit;	
		}
	}
	if($_FILES['thumb'] && $_FILES['thumb']['error'] != UPLOAD_ERR_NO_FILE){
		$res = @image_upload_file($_FILES['thumb'],$path.$thumbname,$thumbwidth,$thumbheight,$error,IMAGERESIZETYPE_CROP,null,$watermark);
		if(!$res){
			echo $error;
			$GLOBALS['db']->rollbacktransaction();
			exit;	
		}		
	}
	$res = createAudioXML($path,$uploadoption,$galleryid,$itemid,$filename,$info);
	if(!$res){
		$GLOBALS['db']->rollbacktransaction();
		exit;	
	}	
	if($oldfile && strtolower($oldfile) != strtolower($filename)){
		@unlink($path.$oldfile);	
	}
	if($oldthumb && strtolower($oldthumb) != strtolower($thumbname.$text)){
		@unlink($path.$oldthumb);	
	}
	
	$GLOBALS['db']->committransaction();
	
	echo '{'."'galleryid':$galleryid,'galleryname':'$galleryname','id':'$itemid','filename':'$filename','thumbname':'".$thumbname."$text','position':'$position','thumbwidth':$thumbwidth,'thumbheight':$thumbheight,'uploadoption':'$uploadoption'".'}';
}else{
	echo 'file not found';	
}

function checkDatabaseFileName($tablename,$filename,$ext,$ignoreid=null){
	$ind = 2;
	$rootname = $filename;
	if(!empty($ignoreid)) $itemcheck = " and id <> $ignoreid";
	while($GLOBALS['db']->selectsingle("select id from $tablename where (filename = '$filename$ext' or thumbname = '$filename$ext')$itemcheck")) $filename = $rootname.'_'.$ind++;
	return $filename;
}

function checkSerialisedFileName($itemarray,$filename,$ext,$ignoreid=0){
	$ind = 2;
	$rootname = $filename;
	$continue = true;
	while($continue){
		foreach($itemarray as $item){
			if($item['id'] != $ignoreid && ($item['filename'] == $filename.$ext || $item['thumbname'] == $filename.$ext)){
				$filename = $rootname.'_'.$ind++;
				continue;	
			}	
		}	
		$continue = false;
	}	
	return $filename;
}

?>