<?php
/**
 * The Resource Manager AIM file handles file uploads for the Resource Manager
 * 
 * @package AJAX
 * @subpackage Resource Manager
 * @since 2.0
 */
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('../lib/Global.lib.php'); 
$usermanager->authenticateUserForAjax();
require_once('Text.lib.php'); 
require_once('Files.lib.php'); 

$_extensions = $GLOBALS['settings']->validfiletypes;
if(isset($_FILES['newfile'])){
	// check file
	if($_FILES['newfile']['size'] == 0){
		echo 'Please select a file to upload.';
		exit;
	}
	// check file
	if($_FILES['newfile']['error'] != 0){
		echo 'File upload failed.';
		exit;
	}
	// check file name
	if(preg_match('|[\\ \/]+|',$_FILES['newfile']['name'])){
		echo 'Illegal characters in filename';
		exit;
	}
	$nameinfo = pathinfo($_FILES['newfile']['name']);
	if(!in_array(strtolower($nameinfo['extension']),$_extensions)){
		echo 'Illegal file type';
		exit;
	}
	// check that the path exists
	if(empty($_REQUEST['newfilepath']) || !file_exists($GLOBALS['documentroot'].'/'.$_REQUEST['newfilepath'])){
		echo 'Path not found';
		exit;	
	}
	if(!ResourceManager::checkPathRight(ResourceManager::getPathRights($_REQUEST['newfilepath'],$_SESSION['user']['id']),'write')) return 'You do not have rights to upload files to this folder';
	$ext = strtolower($nameinfo['extension']);
	if($ext=='jpeg') $ext = 'jpg'; //fix an issue where jpeg extensions are not handled by the Image library correctly.
	$path = $GLOBALS['documentroot'].'/'.trim($_REQUEST['newfilepath'],' /').'/'.getCleanRoot($nameinfo['filename'],64,'_',true).'.'.$ext;
	$res = loadfile($_FILES['newfile']['tmp_name'],$path,$_extensions);
	
	if(in_array($ext,$GLOBALS['settings']->validimagetypes)){
		require_once('Images.lib.php');
		$thumbpath = dirname($path).'/_thumbs/'.basename($path);
		if(!is_dir(dirname($thumbpath))) @mkdir(dirname($thumbpath));
		$tres = @image_resize_widthheight($path,$thumbpath,100,100);
	}
	
	echo $res?1:0;
}else{
	echo 'File not found';	
}

?>