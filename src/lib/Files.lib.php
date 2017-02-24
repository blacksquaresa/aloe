<?php
/**
 * The Files library contains methods for managing files and folders
 * 
 * @package Library
 * @subpackage Files
 * @since 2.0
 */
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');
require_once('Global.lib.php');

/**
 * Get the contents of a file. Used to counteract a problem where file_get_contents returned a ? at the beginning of a file. 
 *
 * @param string $filepath The full path to the file
 * @return string The contents of the file
 * @deprecated This fix is probably unnecessary in PHP5.
 * 
 */
function openfile($filepath){
	$content = file_get_contents($filepath);
	$content = ltrim($content,"?");
	return $content;
}

/**
 * Writes the contents to the given file
 *
 * @param string $target The full path to the target file
 * @param string $contents The contents of the file
 * @return bool Whether or not the file saved successfully
 * @deprecated Use file_put_contents instead
 *
 */
function writefile($target,$contents){
	if(strlen($contents) > 0 && $handle = fopen($target,'wb')){
		fwrite($handle,$contents);
		fclose($handle);		
		return true;
	}
	else return false;
}

/**
 * Loads a file onto the server. Handles normal files as a simple copy, or uploaded files.
 *
 * @param string $sourcepath The full path to the source file
 * @param string $targetpath The full path to the target file
 * @param array $type An array of accepted file extensions
 * @return bool Whether or not the operation was successful
 *
 */
function loadfile($sourcepath,$targetpath,$type){
	$ext = pathinfo(is_uploaded_file($sourcepath)?$targetpath:$sourcepath,PATHINFO_EXTENSION);
	$types = is_array($type)?$type:explode(',',$type);
	if(in_array(strtolower($ext),$types)){	
		if(is_uploaded_file($sourcepath)){
			return move_uploaded_file($sourcepath,$targetpath);
		}else{
			return copy($sourcepath,$targetpath);
		}
	}
	else return false;
}

/**
 * Deletes a folder recursively.
 * 
 * Note that there is no rollback facility should anything fail.
 *
 * @param string $path The full path to the folder
 * @return bool Whether or not the operation was successful
 *
 */
function deleteFolder($path){
	$files = glob($path . '/*');
	foreach($files as $file){
		if(is_dir($file)){
			if(!(deleteFolder($file))) return false;
		}else{
			if(!(unlink($file))) return false;	
		}
	}
	if(is_dir($path)) rmdir($path);
	return true;
}

/**
 * Copies an entire folder, including all sub-folders
 *
 * @param string $source The full path to the source folder
 * @param string $dest The full path to the destination folder
 * @param int $mode The octol mode to use for new folders
 * @return bool Whether or not the operation was successful
 *
 */
function copyFolder($source, $dest, $mode=0755){ 
	$source = rtrim($source,'/\\ ');
	$dest = rtrim($dest,'/\\ ');
	if(empty($source) || empty($dest)) return false;
	if(!is_dir($source)) return false;
	$handle = opendir($source);	
	if(!is_dir($dest)) mkdir($dest,$mode,true);
	while($res = readdir($handle)){ 
		if($res == '.' || $res == '..') continue;		
		if(is_dir($source . '/' . $res)){ 
			if(!copyFolder($source . '/' . $res, $dest . '/' . $res)) return false; 
		} else { 
			if(!copy($source . '/' . $res, $dest . '/' . $res)) return false;			
		} 
	} 
	closedir($handle);
	return true;
} 

/**
 * Identifies whether or not a given folder contains any files (not folders). This method is not recursive.
 *
 * @param string $dirname The full path to the folder
 * @return bool Whether or not the given folder contains any files
 *
 */
function directoryContainsFile($dirname){
	if(!is_dir($dirname)) return false;
	$ret = false;
	$d = opendir($dirname);	
	while (false !== ($file = readdir($d))) { 
		if(!is_dir($file)){
			$ret = true;
			break;	
		}
	}
	return $ret;
}

/**
 * Use this function with the usort command to sort a list of files by last update date (most recent first)
 *
 * @param string $a The full path to the first file to compare
 * @param string $b The full path to the second file to compare
 * @return int An integer indicating which file should take precendence.
 *
 */
function sortFilesByLastUpdateDate($a,$b){
	$atime = filemtime($a);
	$btime = filemtime($b);
	if(!$atime && !$btime) return 0;
	if(!$atime) return 1;
	if(!$btime) return -1;
	if($atime==$btime) return $a > $b?1:-1;
	return $atime > $btime?-1:1;
}

/**
 * Identifies any referneces to the old file in content (for example, in links or images), and updates them to the new path.
 * 
 * If a directory s passed, all files and directories in that directory will be updated recursively. 
 * This method may be calledbefore or after the actual renaming of the files.
 *
 * @param string $oldpath The original refernce path, relative to the root directory
 * @param mixed $newpath The new reference path, relative to the root directory
 * @return void
 *
 */
function updateFileReferences($oldpath,$newpath){
	require_once('HTMPaths.lib.php');
	$oldroot = $GLOBALS['documentroot'].'/'.trim($oldpath,'/');
	$newroot = $GLOBALS['documentroot'].'/'.trim($newpath,'/');
	if(file_exists($oldroot) && is_dir($oldroot)){
		$childroots = glob($oldroot.'/*');
		foreach($childroots as $childroot){
			if(basename($childroot)!='_thumbs'){
				$oldchild = rtrim($oldpath,'/').'/'.basename($childroot);
				$newchild = rtrim($newpath,'/').'/'.basename($childroot);
				updateFileReferences($oldchild,$newchild);
			}
		}
	}elseif(file_exists($newroot) && is_dir($newroot)){
		$childroots = glob($newroot.'/*');
		foreach($childroots as $childroot){
			if(basename($childroot)!='_thumbs'){
				$oldchild = rtrim($oldpath,'/').'/'.basename($childroot);
				$newchild = rtrim($newpath,'/').'/'.basename($childroot);
				updateFileReferences($oldchild,$newchild);
			}
		}
	}else{
		resetHTMPathsInTable('content','content',$oldpath,$newpath);
		resetHTMPathsInTable('contentproperties','value',$oldpath,$newpath);
		$data = array('oldpath'=>$oldpath,'newpath'=>$newpath);
		fireEvent('htmpathResetting',$data);
	}
}

?>