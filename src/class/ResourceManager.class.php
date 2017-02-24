<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');
require_once('Content.lib.php');

/**
 * The ResourceManager class constructs and displays the Resource Manager control, either built into the page of the Resources CMS module, 
 * or within a popup for use anywhere else on the site.
 *
 * The Resource Manager is designed to manage additional files for the site (eg: images, pdf downloads, etc).
 * It works by representing the file system below the /resources folder exactly, in the manner of Windows Explorer.
 * It allows an admin to view and select a single file, or upload a file to use.
 * In manage mode, the admin can also rename, delete, resize, optimise, refresh, move or copy one or more files as needed.
 * 
 * This class can be extended to provide additional functionality. To do this, also ensure that the extendResourcesClass global setting is set to the name of your class.
 * 
 * @package Classes
 * @subpackage Resources
 * @since 2.0
 */

class ResourceManager{
	
	// {{{ Declarations
	/**
	 * The basic root path. All other paths will be appended to this. The path should be relative to the root folder.
	 *
	 * @var string 
	 *
	 */
	var $root;
	
	/**
	 * The path to the icon folder to use for the icons in the interface
	 *
	 * @var string 
	 *
	 */
	var $iconpath;
	
	/**
	 * A reference to the ResourceTreeView instance representing the folder tree
	 *
	 * @var ResourceTreeView 
	 *
	 */
	var $treeview;
	
	/**
	 * The currently selected path
	 *
	 * @var string 
	 *
	 */
	var $selectedpath;
	
	/**
	 * The absolute path to the root folder
	 *
	 * @var string 
	 *
	 */
	var $absolutepath;
	
	/**
	 * The user using the Resource Manager. Used to calculate access rights
	 *
	 * @var array 
	 *
	 */
	var $user;
	
	/**
	 * The display mode for the interface. May be 'details' or 'icons'. This defines the way the files are listed in the file window.
	 *
	 * @var string 
	 *
	 */
	var $display = 'details';
	
	/**
	 * The prefix to use for the JavaScript class. Allows multiple resource managers to be loaded on a page.
	 *
	 * @var string 
	 *
	 */
	var $prefix = 'rm';
	
	/**
	 * The type of media to display in the manager. May be 'all','images','image','media','docs','file'
	 *
	 * @var string 
	 *
	 */
	var $type = 'all';
	
	/**
	 * The current mode of the manager. May be 'select', 'manage' or 'session'.
	 *
	 * @var string 
	 *
	 */
	var $mode = 'select';
	
	/**
	 * A list of all valid file extensions that will be accepted for uploads. This list is usually updated from the site configuration.
	 *
	 * @var array 
	 *
	 */
	var $_extensions = array('jpg','jpeg','gif','png','bmp','doc','xls','ppt','pdf','mpeg','mpg','avi','wmv','swf');
	
	/**
	 * A list of all file extensions that can have thumbnails made.
	 *
	 * @var array 
	 *
	 */
	var $_thumbextensions = array('jpg','jpeg','gif','png');
	
	/**
	 * The link format to use when selecting items. This should be set to work with the JavaScript system with which the manager is working
	 *
	 * @var string 
	 *
	 */
	var $linkformat = 'javascript:fbd_insert(\'%s\')';
	
	/**
	 * The link format to use for a currently selected item. This should be set to work with the JavaScript system with which the manager is working
	 *
	 * @var string 
	 *
	 */
	var $selectedlinkformat = 'javascript:fbd_insert(\'%s\')';
	
	/**
	 * The link format to use for folders in the tree branch.
	 *
	 * @var string 
	 *
	 */
	var $folderlinkformat = '';
	#endregion
	
	// {{{ Constructor
	/**
	 * Constructor
	 *
	 * @param string $type The media type to display
	 * @param string $display the display mode
	 * @return void 
	 *
	 */
	function ResourceManager($type='all',$display='details'){
		$this->_extensions = $GLOBALS['settings']->validfiletypes;
		$this->user = $_SESSION['user'];
		$type = strtolower($type);
		if(in_array($type,array('all','images','image','media','docs','file'))) $this->type = $type;
		$this->display = strtolower($display)=='details'?'details':'icons';
		$this->root = 'resources/';
		$this->iconpath = $GLOBALS['webroot'] . 'images/resources/selector/';
		$this->absolutepath = $GLOBALS['documentroot'];
		$this->folderlinkformat = 'javascript:rs_selectFolder(\'%s\',\'%s\')';
	}
	#endregion
	
	// {{{ Draw Resource Manager
	/**
	 * Draw the entire Resource Manager control
	 *
	 * @param bool $delayed Whether the initial file list should be loaded immediately, or wait until the rest of the page has finished loading.
	 * @return string The full HTML for the entire Resource Manager
	 *
	 */
	function drawResourceManager($delayed=false){
		$root = array();
		$root = $this->_buildFolderTree();
		$path = trim(empty($this->selectedpath)?$this->root:$this->selectedpath,' /');
		$this->treeview = new ResourceTreeView($root,$this);
		$this->treeview->selectedid = $path;
		if(!empty($path)) $path .= '/';
		$rights = self::getPathRights($path,$this->user['id']);
		
		foreach($_GET as $key=>$value){
			$res .= '<input type="hidden" name="rsv_'.$key.'" id="rsv_'.$key.'" value="'.$value.'" />';
		}
		if(!isset($_GET['display'])) $res .= '<input type="hidden" name="rsv_display" id="rsv_display" value="'.$this->display.'" />';
		if(!isset($_GET['type'])) $res .= '<input type="hidden" name="rsv_type" id="rsv_type" value="'.$this->type.'" />';
		if(!isset($_GET['mode'])) $res .= '<input type="hidden" name="rsv_mode" id="rsv_mode" value="'.$this->mode.'" />';
		
		$res .= '<table cellpadding="0" cellspacing="0" class="rs_table">';
		$res .= '<tr>';
		$res .= '<td class="rs_treecell"><div style="position: relative;">';
		$res .= '<div class="rs_foldertoolbar">';
		$res .= '<span id="rsm_managefolders" style="display:'.($this->mode == 'select'?'none':'inline-block').';">';
		$res .= '<a href="javascript:void(0);" rev="javascript:rs_addfolder();" class="rsi_addfolder_off" title="Add a new folder" id="rsm_addfolder"></a>&nbsp;';
		$res .= '<a href="javascript:void(0);" rev="javascript:rs_renamefolder();" class="rsi_renamefolder_off" title="Rename current folder" id="rsm_renamefolder"></a>&nbsp;';
		$res .= '<a href="javascript:void(0);" rev="javascript:rs_movefolder();" class="rsi_movefolder_off" title="Move current folder" id="rsm_movefolder"></a>&nbsp;';
		$res .= '<a href="javascript:void(0);" rev="javascript:rs_copyfolder();" class="rsi_copyfolder_off" title="Copy current folder" id="rsm_copyfolder"></a>&nbsp;';
		$res .= '<a href="javascript:void(0);" rev="javascript:rs_deletefolder();" class="rsi_deletefolder_off" title="Delete current folder" id="rsm_deletefolder"></a>&nbsp;';
		$res .= '</span>';
		$res .= '</div>';
		$res .= '<div id="rs_treeview">'.$this->treeview->drawTree().'</div>';
		$res .= '<div class="rs_movefoldercover" id="rs_movefoldercover">&nbsp;</div>';
		$res .= '</div></td>';
		$res .= '<td class="rs_filecell"><div style="position: relative;">';
		$res .= '<div class="rs_toobar">';
		$res .= $this->_drawToolbar($rights);
		// draw display icons
		$res .= '<span style="display: inline-block; vertical-align: top">';
		if($this->display=='details'){
			$res .= '<a href="javascript:rs_changeDisplay(\'icons\');" class="rsi_iconview_off" title="Icon Display" id="rsd_icons"></a>';
			$res .= '<a href="javascript:rs_changeDisplay(\'details\');" class="rsi_detailview" title="Detailed Display" id="rsd_details"></a>';
		}else{
			$res .= '<a href="javascript:rs_changeDisplay(\'icons\');" class="rsi_iconview" title="Icon Display" id="rsd_icons"></a>';
			$res .= '<a href="javascript:rs_changeDisplay(\'details\');" class="rsi_detailview_off" title="Detailed Display" id="rsd_details"></a>';
		}
		$res .= '<img src="'.$this->iconpath.'separator.png" align="top" />';
		$res .= '</span>';
		// draw file-type radio buttons
		$res .= '<span style="display: inline-block; vertical-align: top">Show me: 
					<input type="radio" name="restype" id="restype_all" value="all"'.(!in_array($this->type,array('docs','images'))?' checked':'').' onclick="rs_filetypeChanged(this);" /><label for="restype_all">All Files</label>
					<input type="radio" name="restype" id="restype_images" value="images"'.($this->type=='images'?' checked':'').' onclick="rs_filetypeChanged(this);" /><label for="restype_images">Images only</label>
					<input type="radio" name="restype" id="restype_docs" value="docs"'.($this->type=='docs'?' checked':'').' onclick="rs_filetypeChanged(this);" /><label for="restype_docs">Documents Only</label>
				</span>
			</div>';
		// draw upload block
		$res .= '<div class="rs_resourceupload" id="rsm_resourceupload" style="display:'.(self::checkPathRight($rights,'write')?'block':'none').';">
					<form action="'.$GLOBALS['webroot'].'ajax/ResourceManager.aim.php" method="post" onsubmit="return AIM.submit(this, {\'onStart\' : rs_uploadstart, \'onComplete\' : rs_uploadcallback})" enctype="multipart/form-data" name="uploadresource">
						<input type="hidden" name="newfileroot" id="newfileroot" value="'.$this->root.'" />
						<input type="hidden" name="newfilepath" id="newfilepath" value="'.$path.'" />
						<table>
							<tr>
								<td colspan="3" class="header">Upload a File:</td>
								<td><input type="file" name="newfile" /></td>
								<td><input type="submit" name="submit" value="Upload File" /></td>
							</tr>
						</table>
					</form>
				</div>';
		// draw resource container
		$res .= '<div id="rs_resourcecontainer">';
		if($delayed){
			$res .= "<script>attachEventHandler(window,'load',function(){rs_refreshFiles(null,'{$this->type}','{$this->display}','{$this->mode}','{$path}');});</script>";
		}else{
			$res .= $this->_drawFiles($path,$rights);
		}
		$res .= '</div>';
		$res .= $this->drawMoveCover();
		$res .= '</div></td>';
		$res .= '</tr>';
		$res .= '</table>';
		
		$res .= $this->_drawPopups_RenameFile();
		$res .= $this->_drawPopups_ResizeImage();
		$res .= $this->_drawPopups_AddFolder();
		$res .= $this->_drawPopups_RenameFolder();
		
		return $res;
	}
	#endregion
	
	// {{{ Draw Toolbar
	/**
	 * Draw the toolbar part of the interface
	 *
	 * @param string $rights The rights of the current folder
	 * @return string The HTML code for the toolbar
	 *
	 */
	private function _drawToolbar($rights){
		$res .= '<div style="float: right;">';
		$res .= '<span id="rsm_managebar" style="display:'.(self::checkPathRight($rights,'write')?'inline-block':'none').';">';
		$res .= '<span id="rsm_manageicons" style="display:'.($this->mode == 'select'?'none':'inline-block').';">';
		$res .= '<a href="javascript:void(0);" class="rsi_download_off" title="Download File" id="rsm_download"></a>&nbsp;';
		$res .= '<a href="javascript:void(0);" rev="javascript:rs_renamefile();" class="rsi_renamefile_off" title="Rename Selected Files" id="rsm_renamefiles"></a>&nbsp;';
		$res .= '<a href="javascript:void(0);" rev="javascript:rs_deletefile();" class="rsi_deletefile_off" title="Delete Selected Files" id="rsm_deletefiles"></a>&nbsp;';
		$res .= '<a href="javascript:void(0);" rev="javascript:rs_movefile();" class="rsi_movefile_off" title="Move Selected Files" id="rsm_movefiles"></a>&nbsp;';
		$res .= '<a href="javascript:void(0);" rev="javascript:rs_copyfile();" class="rsi_copyfile_off" title="Copy Selected Files" id="rsm_copyfiles"></a>&nbsp;';
		$res .= '<a href="javascript:void(0);" rev="javascript:rs_optimise();" class="rsi_optimise_off" title="Optimise Selected Images" id="rsm_optimise"></a>&nbsp;';
		$res .= '<a href="javascript:void(0);" rev="javascript:rs_refreshthumb();" class="rsi_refreshthumb_off" title="Refresh Selected Thumbnails" id="rsm_refreshthumb"></a>&nbsp;';
		$res .= '<a href="javascript:void(0);" rev="javascript:rs_resize();" class="rsi_resize_off" title="Resize Image" id="rsm_resize"></a>&nbsp;';
		$res .= '</span>';
		switch($this->mode){
			case 'section':
				$res .= '<a href="javascript:rs_changeMode(\'manage\');" class="rsi_manage" title="Manage Mode" id="rsm_manage" style="display:none;"></a>&nbsp;&nbsp;';
				$res .= '<a href="javascript:rs_changeMode(\'select\');" class="rsi_select" title="Select Mode" id="rsm_select" style="display:none;"></a>&nbsp;';
				break;
			case 'manage':
				$res .= '<a href="javascript:rs_changeMode(\'manage\');" class="rsi_manage" title="Manage Mode" id="rsm_manage" style="display:none;"></a>&nbsp;&nbsp;';
				$res .= '<a href="javascript:rs_changeMode(\'select\');" class="rsi_select" title="Select Mode" id="rsm_select"></a>&nbsp;';
				break;
			case 'select':
				$res .= '<a href="javascript:rs_changeMode(\'manage\');" class="rsi_manage" title="Manage Mode" id="rsm_manage"></a>&nbsp;&nbsp;';
				$res .= '<a href="javascript:rs_changeMode(\'select\');" class="rsi_select" title="Select Mode" id="rsm_select" style="display:none;"></a>&nbsp;';
				break;				
		}
		$res .= '</span>';
		$res .= '</div>';
		return $res;
	}
	#endregion
	
	// {{{ Draw Move Cover
	/**
	 * Draw the cover that gets shown when files or folders are moved or copied
	 *
	 * @return string The HTML for the cover
	 *
	 */
	private function drawMoveCover(){
		$res .= '<div id="rs_movefilecover" class="rs_movefilecover">';
		$res .= '<div class="rs_resourcecover">&nbsp;</div>';
		$res .= '<table id="rs_movefiledetails" class="rs_movefiledetails" cellpadding="0" cellspacing="0"><tr><td colspan="3">';
		$res .= '<div class="rs_movefilehead"><div style="float: right"><a href="javascript:rs_movefile_cancel();"><img alt="Cancel" src="/images/popups/close.png"></a></div><div id="rs_movefiletitle">Move Files</div></div>';
		$res .= '</td></tr><tr><td class="rs_movefilearrow" valign="top">';
		$res .= '<img src="/images/resources/selector/movearrow.png" /></td><td class="rs_movefiledescription" valign="top">';
		$res .= '<div id="rs_movefileheading">Click on a folder to move the files into</div>';
		$res .= '<div id="rs_movefiletext">Now select the folder on the left into which the selected files should be moved. Note that any files in the target folder with the same filename as one of the moved files will not be overwritten. You will need to delete the old file first.</div><br />';
		$res .= '<input type="button" value="Cancel Action" class="rs_movefilecancel" onclick="rs_movefile_cancel();" />';
		$res .= '</td><td valign="top">';
		$res .= '<div id="rs_movefilelistcontainer"></div>';
		$res .= '</td></tr></table>';		
		$res .= '</div>';
		return $res;	
	}
	#endregion
	
	// {{{ Build Folder Tree
	/**
	 * Build and return a structure representing the folders in the navigation tree.
	 *
	 * @return array A multi-dimensional array representing the whole folder structure.
	 *
	 */
	function _buildFolderTree(){
		$tree = $this->_buildFolderTreeBranch($this->root);
		$tree['icon'] = 'dtree_menu.gif';
		$tree['properties'] = "{rights:'rwt'}";		
		return $tree;
	}
	
	/**
	 * Worker function to build a branch of the folder tree. Used recursively to build the whole tree
	 *
	 * @param string $path The path of the root folder for this branch
	 * @return array a multi-dimensional array representing this branch of the tree, with all it's children
	 *
	 */
	private function _buildFolderTreeBranch($path){
		$testpath = rtrim($path,'/');
		$rights = 'rtw'.(count(explode('/',$testpath))>1?'m':'');
		$tree = array();
		$tree['name'] = basename($path);
		$tree['id'] = trim($path,'/');
		$tree['icon'] = 'tree_folder.gif';
		$tree['disabledicon'] = 'tree_disabled.gif';
		$tree['link'] = sprintf($this->folderlinkformat,urlencode($path),$rights);
		$tree['selectedlink'] = $tree['link'];
		$tree['properties'] = "{rights:'$rights'}";
		
		$dirs = glob($this->absolutepath.'/'.$path.'*',GLOB_ONLYDIR);
		foreach($dirs as $dir){
			if(basename($dir)!='_thumbs'){
				$dir = substr($dir,strlen($this->absolutepath.'/')) . '/';
				$children = $this->_buildFolderTreeBranch($dir);
				if($children) $tree['children'][] = $children;
			}
		}
		
		return $tree;
	}
	#endregion
	
	// {{{ Draw Files
	/**
	 * Draw the files within the file view window. This will either display icons or details depending on the display property
	 *
	 * @param string $path The path to the folder containing the files, relative to the root
	 * @param string $rights The rights for the current folder
	 * @return string The HTML displaying the current file view
	 *
	 */
	function _drawFiles($path,$rights=null){
		if($rights===null) $rights = self::getPathRights($path,$this->user['id']);
		return $this->display=='details'?$this->_drawDetails($path,$rights):$this->_drawIcons($path,$rights);
	}
	#endregion
	
	// {{{ Draw Files in Details View
	/**
	 * Draw the files in a details view. This lists the files by name in a table, with some added information. No thumbnails are shown.
	 *
	 * @param string $path The path to the folder containing the files, relative to the root
	 * @param string $rights The rights for the current folder
	 * @return string The HTML displaying the current file view, as a detailed list
	 *
	 */
	private function _drawDetails($path,$rights){
		if(self::checkPathRight($rights,'read')){
			$typelims = $this->_getTypeLimits();
			$files = glob($this->absolutepath.'/'.$path.'*'.$typelims,GLOB_BRACE);
			$index = 0;
			$res .= '<table class="rsf_table" cellspacing="0" cellpadding="2" width="100%">';
			$res .= '<tr>';
			$res .= '<td class="rsf_detailhead"></td>';
			$res .= '<td class="rsf_detailhead">Filename</td>';
			$res .= '<td class="rsf_detailhead rsf_detail">Width</td>';
			$res .= '<td class="rsf_detailhead rsf_detail">Height</td>';
			$res .= '<td class="rsf_detailhead rsf_detail">Size</td>';
			$res .= '</tr>';
			foreach($files as $file){
				$parts = pathinfo($file);
				$title = basename($file);
				if(in_array($parts['extension'],$this->_extensions)){
					$width = $height = '';
					$filepath = substr($file,strlen($this->absolutepath.'/'));
					if($this->mode=='select'){
						$link = sprintf($this->linkformat,$filepath);
						if(substr($link,0,10) != 'javascript') $link = "document.location.href='$link'";
					}elseif(self::checkPathRight($rights,'write')){
						$link = 'rs_selectFile(this);';
					}
					$rowclass=$index%2==0?'rsf_detailrow':'rsf_detailalt';
					$res .= '<tr id="rs_file' . $index . '" class="'.$rowclass.'" title="'.$title.'" onclick="'.$link.'" filepath="'.$filepath.'">';
					$res .= $this->_drawDetail($file,$index);
					$res .= '</tr>';
					$index ++;
				}
			}
			$res .= '</table>';
		}elseif(!empty($path)){
			$res .= '<table class="rsf_table" cellspacing="0" cellpadding="2" width="100%" height="100%">';
			$res .= '<tr><td align="center">Sorry, you do not have rights to view this folder</td></tr>';	
			$res .= '</table>';
		}
		
		return $res;
	}
	
	/**
	 * Construct the HTML for an individual file in the Details view
	 *
	 * @param string $file The full path to the file
	 * @param int $index The number of this file in the list. Used to uniquely identify the HTML tags
	 * @param string $webroot The relative path to the root of the website, used for image paths
	 * @return string The HTML for the current item
	 *
	 */
	private function _drawDetail($file,$index,$webroot=null){
		if(is_null($webroot)) $webroot = $GLOBALS['webroot'];
		$parts = pathinfo($file);
		$title = basename($file);
		if(in_array($parts['extension'],$this->_thumbextensions) && file_exists($file)){
			list($width,$height) = getimagesize($file);
		}
		$res .= '<td class="rsf_iconfile"><img src="'.$webroot.'images/resources/filetypes/' . $parts['extension'] . '16.gif" /></td>';
		$res .= '<td><div id="rsf_image' . $index . '" class="rsf_detailfile">'.$title.'</div></td>';
		$res .= '<td class="rsf_detail">'.$width.(empty($width)?'':'px').'</td>';
		$res .= '<td class="rsf_detail">'.$height.(empty($height)?'':'px').'</td>';
		$res .= '<td class="rsf_detail">' . (ceil(filesize($file)/1000)) . 'Kb</td>';
		return $res;
	}
	#endregion
	
	// {{{ Draw Files in Icon View
	/**
	 * Draw files in the Icon view. Lays out all files in a grid, with an icon or thumbnail the most prominent detail.
	 *
	 * @param string $path The path to the folder containing the files, relative to the root
	 * @param string $rights The rights of the current folder
	 * @return string displaying the current file view, as grid of icons or thumbnails
	 *
	 */
	function _drawIcons($path,$rights){		
		if(self::checkPathRight($rights,'read')){
			$typelims = $this->_getTypeLimits();
			$files = glob($this->absolutepath.'/'.$path.'*'.$typelims,GLOB_BRACE);
			$index = 0;
			foreach($files as $file){
				$parts = pathinfo($file);
				if(in_array($parts['extension'],$this->_extensions)){
					$title = basename($file);
					if(in_array($parts['extension'],$this->_thumbextensions) && file_exists($file)){
						$size = getimagesize($file);
						if($size){
							$title .= " (" . $size[0] . " x " . $size[1] . "px)";	
						}
					}
					$filepath = substr($file,strlen($this->absolutepath.'/'));
					if($this->mode=='select'){
						$link = sprintf($this->linkformat,$filepath);
						if(substr($link,0,10) != 'javascript') $link = "document.location.href='$link'";
					}elseif(self::checkPathRight($rights,'write')){
						$link = 'rs_selectFile(this);';
					}
					$res .= '<div class="rsf_file" id="rs_file' . $index . '" onclick="'.$link.'" title="' . $title . '" filepath="'.$filepath.'">';
					$res .= $this->_drawIcon($file,$index);
					$res .= '</div>';	
					$index ++;
				}
			}
		}elseif(!empty($path)){
			$res .= '<table class="rsf_table" cellspacing="0" cellpadding="2" width="100%" height="100%">';
			$res .= '<tr><td align="center">Sorry, you do not have rights to view this folder</td></tr>';	
			$res .= '</table>';
		}
		
		return $res;
	}
	
	/**
	 * Construct the HTML for an individual file in the Icon view
	 *
	 * @param string $file The full path to the file
	 * @param int $index The number of this file in the list. Used to uniquely identify the HTML tags
	 * @param string $webroot The relative path to the root of the website, used for image paths
	 * @return string The HTML for the current item
	 *
	 */
	function _drawIcon($file,$index,$webroot=null){
		if(is_null($webroot)) $webroot = $GLOBALS['webroot'];
		$ext = pathinfo($file,PATHINFO_EXTENSION);
		$res .= '<div class="rsf_image"><table class="rsf_imagetable"><tr><td id="rsf_image' . $index . '"><img src="';
		$root = dirname($file);
		$webpath = $webroot . trim(substr($root,strlen($this->absolutepath)),'/.');
		if(file_exists($root.'/_thumbs/'.basename($file))){
			$code = '?'.createRandomCode(8);
			$res .= $webpath . '/_thumbs/' . basename($file) . $code;
		}else{
			$res .= $webroot . 'images/resources/filetypes/' . $ext . '.gif';
		}
		$res .= '" class="rsf_icon" /></td></tr></table></div>';
		$res .= '<div class="rsf_filename">' . basename($file) . '</div>';
		return $res;
	}
	#endregion
	
	// {{{ Draw Popups
	/**
	 * Draw the HTML for the Rename File popup
	 *
	 * @return string The HTML for the Rename File popup
	 *
	 */
	function _drawPopups_RenameFile(){
		$res .= '<div id="rsp_renamefile" style="display: none;">';
		$res .= '<table width="100%" cellspacing="0" cellpadding="0">';
		$res .= '<tr><td class="edt_label_left">Renaming: <span class="rsp_filename" style="width: 200px;" id="rsp_renamefileparent"></span></td></tr>';
		$res .= '<tr><td class="edt_label_left">Enter the new name for this file</td></tr>';
		$res .= '<tr><td><input type="hidden" id="rsp_renamefilefullpath" value=""><input type="text" id="rsp_renamefilename" value="" class="edt_textbox" onkeypress="if(event.keyCode==13)PopupManager.ResourceWindow.rs_renamefile_save();"></td></tr>';
		$res .= '</table>';
		$res .= '<div class="edt_label_right" style="margin-top: 7px;"><a class="edt_button" href="javascript:PopupManager.ResourceWindow.rs_renamefile_save();" />Save</a></div>';
		$res .= '</div>';
		return $res;		
	}
	
	/**
	 * Draw the HTML for the Resize Image popup
	 *
	 * @return string The HTML for the Resize Image popup
	 *
	 */
	function _drawPopups_ResizeImage(){
		$res .= '<div id="rsp_resizeimage" style="display: none;">';
		$res .= '<input type="hidden" id="rs_resizefilefullpath" value="">';
		$res .= '<table width="100%" class="rm_popup_table">';
		$res .= '<tr><td colspan="3">Resizing: <span class="rsp_filename" style="width: 200px;" id="rs_resizefilename"></span></td></tr>';
		// Width
		$res .= '<tr><td class="label_left">Width</td>';
		$res .= '<td><input type="text" id="rs_resizewidth" value="" style="width: 40px;" onkeyup="return PopupManager.ResourceWindow.rs_resizeimage_check(this);"> px</td>';
		$res .= '<td class="label_left"><input type="hidden" id="rs_resizeoriginalwidth" value=""><span id="rs_resizeoriginalwidthtext"></span></td></tr>';
		// Height
		$res .= '<tr><td class="label_left">Height</td>';
		$res .= '<td><input type="text" id="rs_resizeheight" value="" style="width: 40px;" onkeyup="return PopupManager.ResourceWindow.rs_resizeimage_check(this);"> px</td>';
		$res .= '<td class="label_left"><input type="hidden" id="rs_resizeoriginalheight" value=""><span id="rs_resizeoriginalheighttext"></span></td></tr>';
		$res .= '<tr><td colspan="3" class="label_left">';
		// colours
		$res .= '<span style="float: right; display: none;" id="rs_resizeimagecolours" title="select a colour for padding.">';
		$res .= 'colour: <span id="rs_resizeimagecolourdisplay" class="rsp_colourblock" style="background-color: #fff;"></span>';
		$res .= ' <a href="javascript:PopupManager.showColourSelector(document.getElementById(\'rs_imageresizepadcolour\').value,\'rs_imageresizepadcolour\',\'PopupManager.ResourceWindow.rs_imageresize_setcolour\','.(empty($GLOBALS['page']->id)?'0':$GLOBALS['page']->id).');">change</a><input type="hidden" name="rs_imageresizepadcolour" id="rs_imageresizepadcolour" value="#ffffff" />';
		$res .= '</span>';
		// Action
		$res .= '<select id="rs_resizeimageaction" onchange="PopupManager.ResourceWindow.rs_resizeimage_actionchange(this);">';
		$res .= '<option value="maintain">Maintain Aspect Ratio</option>';
		$res .= '<option value="stretch">Stretch</option>';
		$res .= '<option value="crop">Crop To Fit</option>';
		$res .= '<option value="pad">Pad To Fill</option>';
		$res .= '</select>';
		$res .= '</td></tr>';
		// Buttons
		$res .= '</table>';
		$res .= '<div class="edt_label_right" style="margin-top: 7px;"><a class="edt_button" href="javascript:PopupManager.ResourceWindow.rs_resizeimage_save();" />Resize</a></div>';
		$res .= '</div>';
		return $res;		
	}
	
	/**
	 * Draw the HTML for the Add Folder popup
	 *
	 * @return string The HTML for the Add Folder popup
	 *
	 */
	function _drawPopups_AddFolder(){
		$res .= '<div id="rsp_addfolder" style="display: none;">';
		$res .= '<table width="100%" cellspacing="0" cellpadding="0">';
		$res .= '<tr><td class="edt_label_left">Enter the name for this new folder</td></tr>';
		$res .= '<tr><td><input type="hidden" id="rsp_addfolderfullpath" value=""><input type="text" id="rsp_addfoldername" value="" class="edt_textbox" onkeypress="if(event.keyCode==13)PopupManager.ResourceWindow.rs_addfolder_save();"></td></tr>';
		$res .= '</table>';
		$res .= '<div class="edt_label_right" style="margin-top: 7px;"><a class="edt_button" href="javascript:PopupManager.ResourceWindow.rs_addfolder_save();" />Save</a></div>';
		$res .= '</div>';
		return $res;		
	}
	
	/**
	 * Draw the HTML for the Rename Folder popup
	 *
	 * @return string The HTML for the Rename Folder popup
	 *
	 */
	function _drawPopups_RenameFolder(){
		$res .= '<div id="rsp_renamefolder" style="display: none;">';
		$res .= '<table width="100%" cellspacing="0" cellpadding="0">';
		$res .= '<tr><td class="edt_label_left">Renaming: <span class="rsp_filename" style="width: 200px;" id="rsp_renamefolderparent"></span></td></tr>';
		$res .= '<tr><td class="edt_label_left">Enter the new name for this folder</td></tr>';
		$res .= '<tr><td><input type="hidden" id="rsp_renamefolderfullpath" value=""><input type="text" id="rsp_renamefoldername" value="" class="edt_textbox" onkeypress="if(event.keyCode==13)PopupManager.ResourceWindow.rs_renamefolder_save();"></td></tr>';
		$res .= '</table>';
		$res .= '<div class="edt_label_right" style="margin-top: 7px;"><a class="edt_button" href="javascript:PopupManager.ResourceWindow.rs_renamefolder_save();" />Save</a></div>';
		$res .= '</div>';
		return $res;		
	}
	#endregion
	
	// {{{ Get Type Limits
	/**
	 * Fetch a JSONified string containing an array of allowed display file types. This list is used when limiting the display to a specific type of file (eg: docs or images)
	 *
	 * @return string a JSON string of an array of file extensions.
	 *
	 */
	function _getTypeLimits(){	
		switch($this->type){
			case 'file':
			case 'docs':
				$res = '.{'.implode(',',$GLOBALS['settings']->displaydoctypes).'}';
				break;
			case 'image':
			case 'images':
				$res = '.{'.implode(',',$GLOBALS['settings']->displayimagetypes).'}';
				break;
			case 'media':
				$res = '.{'.implode(',',$GLOBALS['settings']->displaymediatypes).'}';
				break;
			default:
				$res = '';
				break;	
		}
		return $res;
	}
	#endregion
	
	// {{{ Path Rights
	/**
	 * Build a string identifying the rights of a folder for the current user
	 * 
	 * This method currently gives everyone all rights to all folders (except move rights to the root). 
	 * However, it is designed to allow future implementations to extend this to allow certain users read-only access to some folders, 
	 * while others might have only write, or only move (and copy) rights.
	 * 
	 * The string is made up of up to four characters, each of which asigns a right. 
	 * If the character is not in the string, the user does not have the associated right.
	 * r - read and select files
	 * w - write, rename, modify and upload files
	 * t - target; files and folders can be moved or copied into the folder
	 * m - move or copy this folder
	 *
	 * @param string $path The path being considered
	 * @param int $userid The ID of the user to be considered. Currently not used.
	 * @return string The user rights snippet
	 *
	 */
	public static function getPathRights($path,$userid){
		$path = rtrim($path,'/');
		$parts = explode('/',$path);
		$res = 'rwt';
		if(count($parts) > 1) $res .= 'm';
		return $res;
	}
	
	/**
	 * Checks to see whether a right snippet contains the required right
	 *
	 * @param string $rights The rights snippet
	 * @param string $right The right being checked
	 * @return bool True if the right exists, false otherwise
	 *
	 */
	public static function checkPathRight($rights,$right){
		switch($right){
			case 'read':
				return strpos($rights,'r') !== false;
			case 'write':
				return strpos($rights,'w') !== false;
			case 'move':
				return strpos($rights,'m') !== false;
			case 'target':
				return strpos($rights,'t') !== false;					
		}
		return false;
	}
	#endregion
	
	// {{{ Extendability
	/**
	 * Creates an instance of the ResourceManager class, or a predefined subclass of it. Set the "extendResourcesClass" global variable with the name of the subclass.
	 *
	 * @return ResourceManager The instance. Also added to the $GLOBALS set.
	 *
	 */
	final public static function getResourceManager($type='all',$display='details'){
		if(!empty($GLOBALS['resourcemanager'])) return $GLOBALS['resourcemanager'];
		if(!empty($GLOBALS['settings']->extendResourcesClass) && class_exists($GLOBALS['settings']->extendResourcesClass) && is_subclass_of($GLOBALS['settings']->extendResourcesClass,'ResourceManager')){
			$GLOBALS['resourcemanager'] = new $GLOBALS['settings']->extendResourcesClass($type,$display);
		}else{
			$GLOBALS['resourcemanager'] = new ResourceManager($type,$display);
		}
		return $GLOBALS['resourcemanager'];
	}
	#endregion
}
?>