<?php
/**
 * The Resource Manager AJAX library of AJAX functions for the Resource Manager module
 * 
 * @package AJAX
 * @subpackage Resource Manager
 * @since 2.0
 */
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('../lib/Global.lib.php'); 
$usermanager->authenticateUserForAjax();
require_once('Agent.lib.php'); 
$agent->init();

// {{{ TreeView
/**
 * Builds and returns the complete HTML block for the TreeView of the folders
 *
 * @param string $selectedid The ID of the selected item
 * @param string $prefix The prefix used for this instance of the Resource Manager
 * @return string The HTML code for the TreeView
 *
 */
function ResourceManager_ReloadTreeview($selectedid,$prefix){
	$resman = ResourceManager::getResourceManager();
	$resman->prefix = $prefix;
	$root = $resman->_buildFolderTree();
	$tree = new ResourceTreeView($root,$resman);
	$tree->selectedid = trim($selectedid,'/');
	$treecode = $tree->drawTree();
	$treecode = preg_replace('|\<script(.*?)\</script\>|si','',$treecode);
	return $treecode;
}
#endregion

// {{{ Folders

/**
 * Delete a folder
 *
 * @param string $path The path to the folder, relative to the site root
 * @return bool The result of the deletion
 *
 */
function ResourceManager_DeleteFolder($path){
	require_once('Files.lib.php');
	if(empty($path)) return 'Folder not found';
	if(!file_exists($GLOBALS['documentroot'].'/'.$path)) return 'Folder not found';
	if(!ResourceManager::checkPathRight(ResourceManager::getPathRights($path,$_SESSION['user']['id']),'move')) return 'You do not have rights to delete this folder';
	$res = deleteFolder($GLOBALS['documentroot'].'/'.$path);
	return $res;
}

/**
 * Create a new folder
 *
 * @param string $path The path of the parent folder
 * @param string $name The name of the new folder
 * @return string The name of the new folder (which may have been modified), prepended with '0:', or an error message.
 *
 */
function ResourceManager_CreateFolder($path,$name){
	if(empty($name) || empty($path)) return 'Empty folder name';
	$name = getCleanRoot($name,32,'_',true);
	$fullname = trim($path,'\/').'/'.$name.'/';
	if(!ResourceManager::checkPathRight(ResourceManager::getPathRights($path,$_SESSION['user']['id']),'target')) return 'You do not have rights to add subfolders to this folder';
	if(!is_dir($GLOBALS['documentroot'].'/'.$path)) return 'The parent folder could not be found';
	if(is_dir($GLOBALS['documentroot'].'/'.$fullname)) return 'There is already a folder with that name. Please try another name.';
	$res = mkdir($GLOBALS['documentroot'].'/'.$fullname);
	return '0:'.$name;
}

/**
 * Rename an existing folder
 *
 * @param string $path The path to the folder
 * @param string $name The new name for the folder
 * @return string The new name (which may have been modified), prepended with "0:", or an error message
 *
 */
function ResourceManager_RenameFolder($path,$name){
	require_once('Files.lib.php');
	if(empty($name) || empty($path)) return 'Empty folder name';
	$name = getCleanRoot($name,32,'_',true);
	$fullname = dirname(trim($path,'\/')).'/'.$name.'/';
	if($fullname == $path) return true;
	if(!ResourceManager::checkPathRight(ResourceManager::getPathRights($fullname,$_SESSION['user']['id']),'move')) return 'You do not have rights to rename this folder';
	if(!is_dir($GLOBALS['documentroot'].'/'.$path)) return 'The selected folder could not be found';
	if(is_dir($GLOBALS['documentroot'].'/'.$fullname)) return 'There is already a folder with that name. Please try another name.';
	$res = rename($GLOBALS['documentroot'].'/'.$path,$GLOBALS['documentroot'].'/'.$fullname);
	if($res) @updateFileReferences($path,$fullname);
	return '0:'.$name;
}

/**
 * Move a folder
 *
 * @param string $source The path to the folder to be moved
 * @param string $target The path to the target parent folder
 * @return mixed true on success, or an error message
 *
 */
function ResourceManager_MoveFolder($source, $target){
	require_once('Files.lib.php');
	if(empty($target) || !is_dir($GLOBALS['documentroot'].'/'.$target)) return 'Invalid target provided';
	if(!ResourceManager::checkPathRight(ResourceManager::getPathRights($source,$_SESSION['user']['id']),'move')) return 'You do not have rights to move this folder';
	if(!ResourceManager::checkPathRight(ResourceManager::getPathRights($target,$_SESSION['user']['id']),'target')) return 'You do not have rights to move files to this folder';
	if(empty($source) || !file_exists($GLOBALS['documentroot'].'/'.$source)) return 'Source folder not found';
	else{
		$newname = $GLOBALS['documentroot'].'/'.$target.basename($source);
		if(file_exists($newname)) return 'Target folder '.basename($source).' already exists';
		else{
			$res = rename($GLOBALS['documentroot'].'/'.$source,$newname);
			if($res) @updateFileReferences($source,$target.basename($source));
			else return 'An unexpected error occured while moving '.basename($source).'';
		}
	}
	return true;
}

/**
 * Copy a folder
 *
 * @param string $source The path to the folder being copied
 * @param string $target The path to the target parent folder
 * @return mixed true on success, or an error message
 *
 */
function ResourceManager_CopyFolder($source, $target){
	require_once('Files.lib.php');
	if(empty($target) || !is_dir($GLOBALS['documentroot'].'/'.$target)) return 'Invalid target provided';
	if(!ResourceManager::checkPathRight(ResourceManager::getPathRights($source,$_SESSION['user']['id']),'move')) return 'You do not have rights to copy this folder';
	if(!ResourceManager::checkPathRight(ResourceManager::getPathRights($target,$_SESSION['user']['id']),'target')) return 'You do not have rights to copy files to this folder';
	if(empty($source) || !file_exists($GLOBALS['documentroot'].'/'.$source)) return 'Source folder not found';
	else{
		$newname = $GLOBALS['documentroot'].'/'.$target.basename($source);
		if(file_exists($newname)) return 'Target folder '.basename($source).' already exists';
		else{
			$res = copyFolder($GLOBALS['documentroot'].'/'.$source,$newname);
			if(!$res){
				return 'An unexpected error occured while moving '.basename($source).'';
			}
		}
	}
	return true;
}
#endregion

// {{{ Files

/**
 * Delete one or more files
 *
 * @param mixed $paths The path to the file to be deleted, or an array of paths
 * @return mixed A boolean result of the deletion, or an error message
 *
 */
function ResourceManager_DeleteFiles($paths){
	require_once('Files.lib.php');
	if(!is_array($paths)) $paths = array($paths);
	if(!ResourceManager::checkPathRight(ResourceManager::getPathRights(dirname($paths[0]),$_SESSION['user']['id']),'write')) return 'You do not have rights to delete these files';
	foreach($paths as $path){
		$path = urldecode($path);
		if(empty($path) || !file_exists($GLOBALS['documentroot'].'/'.$path)) $result = 'File not found';
		else{
			$result = unlink($GLOBALS['documentroot'].'/'.$path);
			if($result){
				if(file_exists($GLOBALS['documentroot'].'/'.dirname($path).'/_thumbs/'.basename($path))){
					unlink($GLOBALS['documentroot'].'/'.dirname($path).'/_thumbs/'.basename($path));	
				}
			}
		}
	}
	return $result;
}

/**
 * Move one or more files to a new folder
 *
 * @param string $path The target folder into which the file(s) should be moved
 * @param mixed $files The path to the file, or an array of paths
 * @return mixed True for success, or an error message
 *
 */
function ResourceManager_MoveFiles($path, $files){
	require_once('Files.lib.php');
	if(empty($path) || !is_dir($GLOBALS['documentroot'].'/'.$path)) return 'Invalid target provided';
	if(!ResourceManager::checkPathRight(ResourceManager::getPathRights($path,$_SESSION['user']['id']),'target')) return 'You do not have rights to move files to this folder';
	if(!is_array($files)) $files = array($files);
	$result = '';
	foreach($files as $file){
		$file = urldecode($file);
		if(empty($file) || !file_exists($GLOBALS['documentroot'].'/'.$file)) $result .= 'File not found<br />';
		else{
			$newname = $GLOBALS['documentroot'].'/'.$path.basename($file);
			if(file_exists($newname)) $result .= 'Target file '.basename($file).' already exists<br />';
			else{
				$res = rename($GLOBALS['documentroot'].'/'.$file,$newname);
				if($res){
					@updateFileReferences($file,$path.basename($file));
					$oldthumb = $GLOBALS['documentroot'].'/'.dirname($file).'/_thumbs/'.basename($file);
					if(file_exists($oldthumb)){
						$thumbpath = $GLOBALS['documentroot'].'/'.$path.'_thumbs/';
						if(!file_exists($thumbpath)) mkdir($thumbpath);
						rename($oldthumb,$thumbpath.basename($file));	
					}
				}else{
					$result .= 'An unexpected error occured while moving '.basename($file).'<br />';
				}
			}
		}
	}
	return empty($result)?true:$result;
}

/**
 * Copy one or more files into a new folder
 *
 * @param string $path The path to the target folder
 * @param mixed $files The path to the file to be copied, or an array of paths
 * @return mixed True on success, or an error message
 *
 */
function ResourceManager_CopyFiles($path, $files){
	if(empty($path) || !is_dir($GLOBALS['documentroot'].'/'.$path)) return 'Invalid target provided';
	if(!ResourceManager::checkPathRight(ResourceManager::getPathRights($path,$_SESSION['user']['id']),'target')) return 'You do not have rights to copy files to this folder';
	if(!is_array($files)) $files = array($files);
	$result = '';
	foreach($files as $file){
		$file = urldecode($file);
		if(empty($file) || !file_exists($GLOBALS['documentroot'].'/'.$file)) $result .= 'File not found<br />';
		else{
			$newname = $GLOBALS['documentroot'].'/'.$path.basename($file);
			if(file_exists($newname)) $result .= 'Target file '.basename($file).' already exists<br />';
			else{
				$res = copy($GLOBALS['documentroot'].'/'.$file,$newname);
				if($res){
					$oldthumb = $GLOBALS['documentroot'].'/'.dirname($file).'/_thumbs/'.basename($file);
					if(file_exists($oldthumb)){
						$thumbpath = $GLOBALS['documentroot'].'/'.$path.'_thumbs/';
						if(!file_exists($thumbpath)) mkdir($thumbpath);
						copy($oldthumb,$thumbpath.basename($file));	
					}
				}else{
					$result .= 'An unexpected error occured while copying '.basename($file).'<br />';
				}
			}
		}
	}
	return empty($result)?true:$result;
}

/**
 * Rename a file
 *
 * @param string $path The path of the file to be renamed
 * @param string $name The new name for the file
 * @return string The new name for the file (which may have been modified) prepended with "0:", or an error message
 *
 */
function ResourceManager_RenameFile($path,$name){
	require_once('Files.lib.php');
	$path = urldecode($path);
	$name = urldecode($name);
	$parts = pathinfo($name);
	$name = getCleanRoot($parts['filename'],64,'_',true) . '.' . strtolower($parts['extension']);
	$fullname = $GLOBALS['documentroot'].'/'.dirname($path).'/'.$name;
	$fullpath = $GLOBALS['documentroot'].'/'.$path;
	if(!ResourceManager::checkPathRight(ResourceManager::getPathRights(dirname($path),$_SESSION['user']['id']),'write')) return 'You do not have rights to rename this file';
	if($fullpath == $fullname) return true;
	if(!file_exists($fullpath)) return 'The selected file could not be found';
	if(!in_array(pathinfo($fullname,PATHINFO_EXTENSION),$GLOBALS['settings']->validfiletypes)) return 'Illegal file type.';
	if(preg_match('|[\\ \/]+|',$name)) return 'illegal characters in filename';
	if(file_exists($fullname)) return 'There is already a file with that name. Please try another name.';
	$res = rename($fullpath,$fullname);
	if($res){
		@updateFileReferences($path,dirname($path).'/'.$name);
		$thumbpath = dirname($fullpath).'/_thumbs/'.basename($fullpath);
		$thumbname = dirname($fullname).'/_thumbs/'.basename($fullname);
		if(file_exists($thumbpath)){
			if(file_exists($thumbname)){
				@unlink($thumbpath);	
			}else{
				@rename($thumbpath,$thumbname);	
			}
		}
	}
	return '0:'.$name;
}
#endregion

// {{{ Images
/**
 * Creates a thumbnail of the supplied image if none exists, or if forced, then returns the HTML code for the image tag to display the thumbnail.
 *
 * @param string $webroot The webroot for the current containing page
 * @param string $path The path to the image file, relative to the resources root folder
 * @param bool $force Whether or not to force the method to re-create the thumbnail even if it already exists
 * @return string The HTML code for an Image tag representing the thumbnail
 *
 */
function ResourceManager_LoadThumbnail($webroot,$path,$force){
	$thumbpath = dirname($path).'/_thumbs/'.basename($path);
	if(!file_exists($GLOBALS['documentroot'].'/'.$thumbpath)||$force){
		require_once('Images.lib.php');
		if(!is_dir($GLOBALS['documentroot'].'/'.dirname($path).'/_thumbs')) mkdir($GLOBALS['documentroot'].'/'.dirname($path).'/_thumbs');
		$res = image_resize_widthheight($GLOBALS['documentroot'].'/'.$path,$GLOBALS['documentroot'].'/'.$thumbpath,100,100);
	}
	if(file_exists($GLOBALS['documentroot'].'/'.$thumbpath)){
		require_once('Text.lib.php');
		$code = '<img src="' . $webroot . $thumbpath . '?' . createRandomCode(8) . '" class="rmf_icon" />';
		return $code;
	}
	return false;
}

/**
 * Checks to see that all thumbnails of a folder have corresponding images, and if not, deletes them
 *
 * @param string $root The resources root, relative to the site root
 * @param string $path The path to the folder whose thumbnails should be checked
 * @return void 
 *
 */
function ResourceManager_CheckThumbnails($root,$path){
	$path = rtrim($path,'\/');
	$thumbpath = $GLOBALS['documentroot'].'/'.$root.$path.'/_thumbs';
	$thumbfiles = glob($thumbpath.'/*');
	foreach($thumbfiles as $thumbfile){
		$test = preg_replace('|_thumbs/|si','',$thumbfile);
		if(!file_exists($test)){
			unlink($thumbfile);	
		}
	}
}

/**
 * Resaves an image using the default saving settings. Used primarily to reduce the file size of JPEG images.
 *
 * @param string $prefix The prefix used by the current instance of the Resource Manager
 * @param string $path The path to the image to be optimised
 * @param int $index The index of this image within the current file list
 * @param string $display 'icons' or 'details' to define the code returned
 * @return string The HTML code to replace that currently being used to display the image in the Resource Manager.
 *
 */
function ResourceManager_OptimiseImage($prefix,$path,$index,$display){
	require_once('Images.lib.php');
	if(!ResourceManager::checkPathRight(ResourceManager::getPathRights(dirname($path),$_SESSION['user']['id']),'write')) return 'You do not have rights to optimise this image';
	$res = image_resize($GLOBALS['documentroot'].'/'.$path,$GLOBALS['documentroot'].'/'.$path,null,null,null,null,null,null,null,null,true);
	if($res){
		$resman = ResourceManager::getResourceManager('all',$display);
		$resman->prefix = $prefix;
		$resman->mode = 'manage';
		if($display=='icons'){
			$code = $resman->_drawIcon($GLOBALS['documentroot'].'/'.$path,$index);
		}else{
			$code = $resman->_drawDetail($GLOBALS['documentroot'].'/'.$path,$index);
		}
		return $code;
	}
	return false;
}

/**
 * Returns the width and height of the image
 *
 * @param string $path the path to the image
 * @return array An associative array containing keys for the width and height of the image
 *
 */
function ResourceManager_GetImageInfo($path){
	$path = $GLOBALS['documentroot'].'/'.ltrim($path,'\/');
	if(file_exists($path)){
		$info = getimagesize($path);
		$ret = array();
		$ret['width'] = $info[0];
		$ret['height'] = $info[1];
		return $ret;
	}
	return false;
}

/**
 * Resize an image
 *
 * @param string $webroot The webroot for the current page
 * @param string $prefix The prefix used by the current instance of the Resource Manager
 * @param string $path The path to the image to be resized
 * @param int $index The position the image holds in the current view
 * @param string $display The type of display used by the current view. Either 'icons' or 'details'
 * @param int $width The desired width
 * @param int $height The desired height
 * @param string $action The action to be taken on the image ('maintain' to maintain aspect ratio, 'crop' or 'pad')
 * @param string $colour The colour to use as a background when padding
 * @return string The HTML code to replace that currently being used to display the image in the Resource Manager, prepended with "0:", or an error message.
 *
 */
function ResourceManager_ResizeImage($webroot,$prefix,$path,$index,$display,$width,$height,$action,$colour){
	require_once('Colours.lib.php');
	require_once('Images.lib.php');
	if(!ResourceManager::checkPathRight(ResourceManager::getPathRights(dirname($path),$_SESSION['user']['id']),'write')) return 'You do not have rights to resize this image';
	$source = $GLOBALS['documentroot'].'/'.$path;
	if(!file_exists($source)) return 'File not found';
	$currentsize = getimagesize($source);
	if(empty($currentsize)) return 'Failed to identify the current size.';
	if($currentsize[0]==$width && $currentsize[1]==$height) return 'The image is not being resized.';
	$resman = ResourceManager::getResourceManager('all',$display);
	$resman->prefix = $prefix;
	$resman->mode = 'manage';
	$rgb = hex_rgb($colour);
	switch($action){
		case 'maintain':
			$action = IMAGERESIZETYPE_WIDTHHEIGHT;
			break;	
		case 'crop':
			$action = IMAGERESIZETYPE_CROP;
			break;	
		case 'pad':
			$action = IMAGERESIZETYPE_PAD;
			break;	
	}
	$res = image_resize($GLOBALS['documentroot'].'/'.$path,$GLOBALS['documentroot'].'/'.$path,$action,$width,$height,null,$rgb['r'],$rgb['g'],$rgb['b']);
	if($res){
		@ResourceManager_LoadThumbnail($webroot,$path,true);
		if($display=='icons'){
			$code = $resman->_drawIcon($GLOBALS['documentroot'].'/'.$path,$index,$webroot);
		}else{
			$code = $resman->_drawDetail($GLOBALS['documentroot'].'/'.$path,$index);
		}
		return '0:'.$code;
	}
	return false;
}
#endregion


?>