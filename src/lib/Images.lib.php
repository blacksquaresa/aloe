<?php
/**
 * The Images library includes functions for resizing and uploading images.
 * 
 * @package Library
 * @subpackage Images
 * @since 2.0
 **/
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');
require_once('Global.lib.php');

// {{{ Resize Images
/**
 * Resize the image to the desired width, maintaining aspect ration
 **/
define('IMAGERESIZETYPE_WIDTH',0); 
/**
 * Resize the image to the desired height, maintaining aspect ration
 **/
define('IMAGERESIZETYPE_HEIGHT',1);
/**
 * Resize the image to the desired width and height. The source image will be inserted so that the entire image is contained in the target space, 
 * and extra space is padded with the supplied colour
 **/
define('IMAGERESIZETYPE_PAD',2);
/**
 * Resize the image to the desired width and height. The source image is inserted so that the entire target space is filled, and extra image is cropped off.
 **/
define('IMAGERESIZETYPE_CROP',3);
/**
 * Resize the image to fit within the desired width and height, maintaining the aspect ratio
 **/
define('IMAGERESIZETYPE_WIDTHHEIGHT',4);

/**
 * Resize an image to the supplied width. Keep the aspect ratio intact.
 *
 * @param string $sourcepath The path to the source image
 * @param string $targetpath The path to the target image. This need not include the file extension, which will be appended according to target file type
 * @param int $width The desired width
 * @param int $targettype One of the standard constants representing one of the valid file types for the target file. May be IMAGETYPE_GIF, IMAGETYPE_PNG or IMAGETYPE_JPEG
 * @return string The full path to the target image, including file extension
 *
 */
function image_resize_width($sourcepath,$targetpath,$width,$targettype=null){
	return image_resize($sourcepath,$targetpath,IMAGERESIZETYPE_WIDTH,$width,0,$targettype);
}

/**
 * Resize an image to the specified height, maintaining aspect ratio 
 *
 * @param string $sourcepath The path to the source image
 * @param string $targetpath The path to the target image. This need not include the file extension, which will be appended according to target file type 
 * @param int $height The desired height
 * @param int $targettype One of the standard constants representing one of the valid file types for the target file. May be IMAGETYPE_GIF, IMAGETYPE_PNG or IMAGETYPE_JPEG
 * @return string The full path to the target image, including file extension
 *
 */
function image_resize_height($sourcepath,$targetpath,$height,$targettype=null){
	return image_resize($sourcepath,$targetpath,IMAGERESIZETYPE_HEIGHT,0,$height,$targettype);
}

/**
 * Resize an image to the specified width and height, padding with solid colour.
 * 
 * The target image will be exactly the width and height supplied, The source image will be scaled down to ensure the entire image fits inside the target space. 
 * The aspect ratio of the source image is maintained, and the extra space is padded with the supplied colour.
 * If the source image is smaller than the target space, it will be centered. It will never be scaled up.
 *
 * @param string $sourcepath The path to the source image
 * @param string $targetpath The path to the target image. This need not include the file extension, which will be appended according to target file type
 * @param int $width The width of the target image
 * @param int $height The height of the target image
 * @param int $targettype One of the standard constants representing one of the valid file types for the target file. May be IMAGETYPE_GIF, IMAGETYPE_PNG or IMAGETYPE_JPEG
 * @param int $canvasred The red portion of the padding colour, between 0 and 255
 * @param int $canvasgreen The green portion of the padding colour, between 0 and 255
 * @param int $canvasblue The blue portion of the padding colour, between 0 and 255
 * @return string The full path to the target image, including file extension
 *
 */
function image_resize_pad($sourcepath,$targetpath,$width,$height,$targettype=null,$canvasred=null,$canvasgreen=null,$canvasblue=null){
	return image_resize($sourcepath,$targetpath,IMAGERESIZETYPE_PAD,$width,$height,$targettype,$canvasred,$canvasgreen,$canvasblue);
}

/**
 * Resize an image to the specified width and height, cropping excess
 * 
 * The target image will be exactly the width and height supplied, The source image will be scaled down to ensure the entire target space is filled with the source image. 
 * The aspect ratio of the source image is maintained, and the extra bits of the image are cropped off.
 * If the source image is smaller than the target space, it will be padded instead (see image_resize_pad)
 *
 * @param string $sourcepath The path to the source image
 * @param string $targetpath The path to the target image. This need not include the file extension, which will be appended according to target file type
 * @param int $width The width of the target image
 * @param int $height The height of the target image
 * @param int $targettype One of the standard constants representing one of the valid file types for the target file. May be IMAGETYPE_GIF, IMAGETYPE_PNG or IMAGETYPE_JPEG
 * @param string $watermark The path to a watermark file to use for this image
 * @param int $canvasred The red portion of the padding colour, between 0 and 255
 * @param int $canvasgreen The green portion of the padding colour, between 0 and 255
 * @param int $canvasblue The blue portion of the padding colour, between 0 and 255
 * @return string The full path to the target image, including file extension
 *
 */
function image_resize_crop($sourcepath,$targetpath,$width,$height,$targettype=null,$watermark=null,$canvasred=null,$canvasgreen=null,$canvasblue=null){
	return image_resize($sourcepath,$targetpath,IMAGERESIZETYPE_CROP,$width,$height,$targettype,$canvasred,$canvasgreen,$canvasblue,$watermark);
}

/**
 * Resize an image to the specified width and height.
 * 
 * The target image will maintain the aspect ration, but will be scaled to fit exactly inside a space of the given width and height.
 * The source image will never be scaled up - if it already fits into the given space, it will simply be copied.
 *
 * @param string $sourcepath The path to the source image
 * @param string $targetpath The path to the target image. This need not include the file extension, which will be appended according to target file type
 * @param int $width The width of the target image
 * @param int $height The height of the target image
 * @param int $targettype One of the standard constants representing one of the valid file types for the target file. May be IMAGETYPE_GIF, IMAGETYPE_PNG or IMAGETYPE_JPEG
 * @return string The full path to the target image, including file extension
 *
 */
function image_resize_widthheight($sourcepath,$targetpath,$width,$height,$targettype=null){
	return image_resize($sourcepath,$targetpath,IMAGERESIZETYPE_WIDTHHEIGHT,$width,$height,$targettype);
}

/**
 * The basic function used to resize images. All other resize functions are wrappers for this one.
 *
 * @param string $sourcepath The path to the source image
 * @param string $targetpath The path to the target image. This need not include the file extension, which will be appended according to target file type
 * @param int $type One of the IMAGERESIZETYPE constants indication how the image should be resized
 * @param int $width The width of the target image
 * @param int $height The height of the target image
 * @param int $targettype One of the standard constants representing one of the valid file types for the target file. May be IMAGETYPE_GIF, IMAGETYPE_PNG or IMAGETYPE_JPEG
 * @param int $canvasred The red portion of the padding colour, between 0 and 255
 * @param int $canvasgreen The green portion of the padding colour, between 0 and 255
 * @param int $canvasblue The blue portion of the padding colour, between 0 and 255
 * @param string $watermark The path to a watermark file to use for this image
 * @return string The full path to the target image, including file extension
 *
 */
function image_resize($sourcepath,$targetpath,$type=null,$width=null,$height=null,$targettype=null,$canvasred=null,$canvasgreen=null,$canvasblue=null,$watermark=null){
	if(@list($width_orig, $height_orig, $type_orig) = check_image($sourcepath)){
		if(!in_array($targettype,array(IMAGETYPE_GIF,IMAGETYPE_JPEG,IMAGETYPE_PNG))){
			$targettype = $type_orig;
		}
		switch($targettype){
			case IMAGETYPE_GIF:
				$ext = '.gif';
				break;
			case IMAGETYPE_PNG:
				$ext = '.png';
				break;
			default:
				$ext = '.jpg';
				break;
		}
		
		// remove the extension from targetpath, if it has one, and add the new one in its place
		$targetext = pathinfo($targetpath,PATHINFO_EXTENSION);
		if(!empty($targetext)) $targetpath = substr($targetpath,0,-(strlen($targetext)+1));	
		$targetpath .= $ext;
		// If the target type is the same as the original, and no resizing is required, then just copy the image
		if(($targettype == $type_orig) && 
				((!in_array($type,array(IMAGERESIZETYPE_WIDTH,IMAGERESIZETYPE_HEIGHT,IMAGERESIZETYPE_PAD,IMAGERESIZETYPE_CROP,IMAGERESIZETYPE_WIDTHHEIGHT))) ||
					($type==IMAGERESIZETYPE_PAD && $width_orig == $width && $height_orig == $height) ||
					($type==IMAGERESIZETYPE_CROP && $width_orig == $width && $height_orig == $height) ||
					($type==IMAGERESIZETYPE_HEIGHT && $height_orig <= $height) ||
					($type==IMAGERESIZETYPE_WIDTH && $width_orig <= $width) ||
					($type==IMAGERESIZETYPE_WIDTHHEIGHT && ($width_orig <= $width && $height_orig <= $height)))){			
			if(is_uploaded_file($sourcepath)){
				return move_uploaded_file($sourcepath,$targetpath);
			}else{
				return copy($sourcepath,$targetpath);
			}
		}
		// Set the width and height of the canvas
		if(empty($width)) $width = $width_orig;
		if(empty($height)) $height = $height_orig;
		switch($type){
			case IMAGERESIZETYPE_WIDTH:
				$picwidth = $width;
				$height = ($width / $width_orig) * $height_orig;
				$picheight = $height;
				break;
			case IMAGERESIZETYPE_HEIGHT:
				$picheight = $height;
				$width = ($height / $height_orig) * $width_orig;
				$picwidth = $width;
				break;
			case IMAGERESIZETYPE_CROP:
				if($width > $width_orig || $height > $height_orig){
					$type = IMAGERESIZETYPE_PAD;
				}else{
					$picwidth = $width;
					$picheight = $height;
					$wrat = $width_orig / $width;
					$hrat = $height_orig / $height;
					if($wrat < 1 && $hrat < 1){
						$width = $width_orig;
						$height = $height_orig;
					}elseif($wrat < $hrat) {
						$height = ($width / $width_orig) * $height_orig;
					} else {
						$width = ($height / $height_orig) * $width_orig;
					}
					break;
				}
			case IMAGERESIZETYPE_PAD:
				$picwidth = $width;
				$picheight = $height;
				$wrat = $width_orig / $width;
				$hrat = $height_orig / $height;
				if($wrat < 1 && $hrat < 1){
					$width = $width_orig;
					$height = $height_orig;
				}elseif($wrat < $hrat) {
					$width = ($height / $height_orig) * $width_orig;
				} else {
					$height = ($width / $width_orig) * $height_orig;
				}
				break;
			case IMAGERESIZETYPE_WIDTHHEIGHT:
				$wrat = $width_orig / $width;
				$hrat = $height_orig / $height;
				if($wrat < 1 && $hrat < 1){
					$width = $width_orig;
					$height = $height_orig;
				}elseif($wrat < $hrat) {
					$width = ($height / $height_orig) * $width_orig;
				} else {
					$height = ($width / $width_orig) * $height_orig;
				}
				$picwidth = $width;
				$picheight = $height;
				break;
			default: 
				$picwidth = $width;
				$picheight = $height;
				break;
		}
		if(!function_exists('imagecreatetruecolor')){
			trigger_error('Creating, Resizing and Saving images required the GD2 library. Ensure that this is enabled in the php.ini file.',E_USER_WARNING);
			return false;
		}
		// create canvas
		$image_p = imagecreatetruecolor($picwidth, $picheight);
		// ensure valid colour values
		if($canvasred < 0 || $canvasred > 255 || $canvasred === null) $canvasred = $GLOBALS['skin']->getSetting(null,'defaultcolour_red',255);
		if($canvasgreen < 0 || $canvasgreen > 255 || $canvasgreen === null) $canvasgreen = $GLOBALS['skin']->getSetting(null,'defaultcolour_green',255);
		if($canvasblue < 0 || $canvasblue > 255 || $canvasblue === null) $canvasblue = $GLOBALS['skin']->getSetting(null,'defaultcolour_blue',255);
		$colour = imagecolorallocate($image_p, $canvasred, $canvasgreen, $canvasblue);
		imagefill($image_p,0,0,$colour);
		// load original image
		switch($type_orig){
			case 1:
				$image = imagecreatefromgif($sourcepath);
				break;
			case 2:
				$image = imagecreatefromjpeg($sourcepath);
				break;
			case 3:
				$image = imagecreatefrompng($sourcepath);
				break;
			default:
				return false;
		}
		// center the image in the canvas
		if($picwidth != $width){
			$destx = round(($picwidth - $width) / 2);
		}else{
			$destx = 0;
		}
		if($picheight != $height){
			$desty = round(($picheight - $height) / 2);
		}else{
			$desty = 0;
		}
		// draw the image onto the canvas
		imagecopyresampled($image_p, $image, $destx, $desty, 0, 0, $width, $height, $width_orig, $height_orig);
		// draw the watermark
		if(!empty($watermark) && file_exists($watermark) && $wsize=check_image($watermark)){
			switch($wsize[2]){
				case IMAGETYPE_GIF:
					$wimage = imagecreatefromgif($watermark);
					break;
				case IMAGETYPE_JPEG:
					$wimage = imagecreatefromjpeg($watermark);
					break;
				case IMAGETYPE_PNG:
					$wimage = imagecreatefrompng($watermark);
					break;					
			}
			if($wimage){
				imagecopyresampled($image_p, $wimage, 0, 0, 0, 0, $wsize[0], $wsize[1], $wsize[0], $wsize[1]);	
			}
		}
		// save the image
		switch($targettype){
			case IMAGETYPE_GIF:
				$res = imagegif($image_p,$targetpath);
				break;
			case IMAGETYPE_JPEG:
				$res = imagejpeg($image_p,$targetpath);
				break;
			case IMAGETYPE_PNG:
				$res = imagepng($image_p,$targetpath);
				break;
		}
		if($res){
			return $targetpath;
		}else{
			return false;	
		}
	}else{
		return false;	
	}
}

/**
 * Returns the size of the image, or false if the file is not an image. Used to check the validity of the image file.
 *
 * @param string $sourcepath The path to the image to check
 * @return mixed The result of the getimagesize call, or false if the check failed
 *
 */
function check_image($sourcepath){
	if(@$size = getimagesize($sourcepath)){
		if(!in_array($size[2],array(IMAGETYPE_GIF,IMAGETYPE_JPEG,IMAGETYPE_PNG))){
			return false;
		}
	}else{
		return false;
	}
	return $size;
}
#endregion

// {{{ Handle File Uploads

/**
 * Handle a file uploaded using a file input box. 
 *
 * @param array $image The image array, as suuplied in the $_FILES variable
 * @param string $targetpath The target path. This does not need the file extension.
 * @param int $width The width of the target image
 * @param int $height The height of the target image
 * @param string $error A container variable for any errors that might occur during the process
 * @param int $process One of the IMAGERESIZETYPE constants identifying the process to use when resizing
 * @param int $targettype One of the IMAGETYPE constants identifying the target type of image
 * @param string $watermark The path to the watermark file to use, if any
 * @param int $canvasred The red portion of the padding colour, 0 to 255
 * @param int $canvasgreen The green portion of the padding colour, 0 to 255
 * @param int $canvasblue The blue portion of the padding colour, 0 to 255
 * @return bool True if successful, otherwise false
 *
 */
function image_upload_file($image,$targetpath,$width,$height,&$error,$process=IMAGERESIZETYPE_CROP,$targettype=null,$watermark=null,$canvasred=255,$canvasgreen=255,$canvasblue=255){
	if(!empty($image)){
		switch($image['error']){
			case UPLOAD_ERR_OK:
				if(empty($width) && empty($height)) $res = image_resize($image['tmp_name'],$targetpath,null,null,null,$targettype,$canvasred,$canvasgreen,$canvasblue,$watermark);
				elseif(empty($width)) $res = image_resize_height($image['tmp_name'],$targetpath,$height,$targettype,$canvasred,$canvasgreen,$canvasblue,$watermark);
				elseif(empty($height)) $res = image_resize_width($image['tmp_name'],$targetpath,$width,$targettype,$canvasred,$canvasgreen,$canvasblue,$watermark);
				else $res = image_resize($image['tmp_name'],$targetpath,$process,$width,$height,$targettype,$canvasred,$canvasgreen,$canvasblue,$watermark);		
				if(!$res){
					$error .= "The image file was not in a recognisable image format or the dimensions were too large.<br />";
					return false;
				}
				break;
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				$error .= "The image file size was too large. Please ensure that the file is within the allowed limits<br />";
				return false;
			case UPLOAD_ERR_NO_FILE:
				break;
			default:
				$error .= "There was an error uploading the image file. Please try again<br />";
				return false;
		}
	}
	return true;
}
#endregion

// {{{ Create and cache correctly sized images

/**
 * Create and cache correctly sized images
 * 
 * Content Blocks, Controls or Modules may require an image to be different sizes depending on where that image is displayed. For example, 
 * a CMFeature Content Block may want to display the supplied image at the full width of its column, whatever that it. So, the Content Block will collect the image
 * as a file in resources, then create a cached version of that image of the correct size for use on the site. If that Content Block is moved to a new column, with a 
 * different width, a new cached version of the image will be created, without any input from the admin required, and without the source image being modified in any way.
 * 
 * This function will check the source image against the requirements given, and if it fulfills them, it is simply returned.
 * If it does not, the function will check to see whether there already exists a cached image, and whether that cached image fulfills the requirements. 
 * If so, that image is returned. If not, a new image is created, saved in the correct cache, and returned.
 *
 * @param string $root The path to the root file
 * @param string $cachepath The path to the folder to use for caching
 * @param string $cachename The name of the cache file
 * @param int $maxwidth The maximum width of the resulting image
 * @param int $maxheight The maximum height of the resulting image
 * @param int $process One of the IMAGERESIZETYPE constants identifying which process should be used to resize the image
 * @return string The path to the file that should be used.
 *
 */
function getImagePath($root,$cachepath,$cachename,$maxwidth=null,$maxheight=null,$process=IMAGERESIZETYPE_WIDTHHEIGHT){
	if(empty($root)) return '';
	if(empty($cachepath)) return '';
	$cachepath = trim($cachepath,'/.').'/';
	$ext = pathinfo($cachename,PATHINFO_EXTENSION);
	if(empty($ext)) $cachename .= '.'.pathinfo($root,PATHINFO_EXTENSION);
	if(!empty($maxwidth) || !empty($maxheight)){
		$abspath = substr($root,0,4)=='http'?$root:$GLOBALS['documentroot'] . '/' . ltrim($root,'/');
		if(file_exists($abspath)){
			$size = getimagesize($abspath);
			if($size && ((!empty($maxwidth) && $size[0] > $maxwidth) || (!empty($maxheight) && $size[1] > $maxheight))){
				if(!is_dir($GLOBALS['documentroot'] . '/'.$cachepath)) mkdir($GLOBALS['documentroot'] . '/'.$cachepath,0755,true);
				$cachefile = $GLOBALS['documentroot'] . '/'.$cachepath . $cachename;
				if(file_exists($cachefile)){
					$cachesize = getimagesize($cachefile);
					if(empty($cachesize) || (!empty($maxwidth) && $cachesize[0] != $maxwidth) || (!empty($maxheight) && $cachesize[1] != $maxheight)){
						unlink($cachefile);
					}
				}
				if(!file_exists($cachefile)){
					require_once('Images.lib.php');
					if(empty($maxwidth)) image_resize_height($abspath,$cachefile,$maxheight);
					elseif(empty($maxheight)) image_resize_width($abspath,$cachefile,$maxwidth);
					else $res = image_resize($abspath,$cachefile,$process,$maxwidth,$maxheight);		
				}
				$root = $cachepath . $cachename;
			}
		}
	}
	$rootpath = substr($root,0,4)!='http' && substr($root,0,1) != '/'?$GLOBALS['webroot'] . $root:$root;
	return $rootpath;
}
#endregion

?>