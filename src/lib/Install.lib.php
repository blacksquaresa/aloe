<?php
/**
 * The Install library provides helper functions for Skins, Modules and Content Blocks to install and uninstall themselves
 * 
 * Skins, Modules and Content Blocks may provide an install and a remove file, and these should be used to install or remove the element respectively.
 * This may include creating global, page or layout settings, adding pages to the menu, creating new lists or any other task that may be required for the 
 * Skin, Module or Content Block to function properly.
 * 
 * All methods in this library accept a $message variable which is intended to be the cumulative set of all success or failure messages for previous tasks. The
 * method will append it's own success or failure message to the variable.
 * 
 * All methods will also roll back the current database transaction if they fail. 
 * 
 * NOTE: some database statements implicitly commit the current transaction, most notably DROP TABLE and CREATE TABLE. You should therefore ensure that these 
 * statements are made before you begin the transaction (or after you commit it). Also be aware that dropped tables cannot be rolled back, and newly created tables will still exist 
 * after a rollback.
 * 
 * @package Library
 * @subpackage Install
 * @since 2.4
 **/
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');
require_once('Text.lib.php');

/**
 * Use this function in an install script to collect custom information from the user. This information can then be used by the install script.
 * 
 * This function should be called before all others in the script. The function will return false if the information has not yet been collected, 
 * and therefore stop any further processing. 
 * 
 * Variables may be accessed in subsequent actions through $GLOBALS['installdata'].
 *
 * @param array $fields An array of fields to collect. Eac item must be an array containing 'name' and 'type' fields
 * @param string $message The current process message. The results of this function will be appended to this string
 * @return bool true if the data has been collected, otherwise false
 *
 */
function collectInstallData($fields,&$message){
	if(isset($_REQUEST['installvariables'])){
		$inputcontrol = new InputControl();
		$GLOBALS['installdata'] = array();
		foreach($fields as $field){
			$GLOBALS['installdata'][$field['name']] = $inputcontrol->processControl($_REQUEST['installvariables'],$field['name'],$field['type']);
		}
		$message .= 'Install data collected<br />';
		return true;
	}else{
		$_SESSION['installdatafields'] = $fields;
		$_SESSION['installparameters'] = array('action'=>$_REQUEST['action'],'type'=>$_REQUEST['type'],'class'=>$_REQUEST['class']);;
		$message .= 'Collecting install data...<br />';
		return false;
	}
}

/**
 * Add a global setting to the system
 *
 * @param string $name The name to use for the setting. This should not include any spaces
 * @param string $type The type of information stored in the setting
 * @param string $data Additional customisation information for this setting. The exact format of this value depends on the type
 * @param string $default The default value
 * @param string $group The group into which this setting should be placed in the Settings module
 * @param string $label The label for this setting, when it is listed in the Settings module
 * @param string $description A short description of this setting, when it is listed in the Settings module
 * @param string $message The current process message. The results of this function will be appended to this string
 * @return bool False on error, otherwise true.
 *
 */
function addGlobalSetting($name, $type, $data, $default, $group, $label, $description, &$message){	
	$res = $GLOBALS['db']->insertupdate('settings',array('name'=>$name,'type'=>$type,'data'=>$data,'value'=>$default,'group'=>$group,'title'=>$label,'description'=>$description),array('name'=>$name));
	if($res===false){
		$message .= 'Error adding global setting: '.$name.':<br />' . mysql_error();
		$GLOBALS['db']->rollbacktransaction();
		return false;
	}
	$message .= 'Added global setting: '.$name.'<br />';	
	return true;
}

/**
 * Remove a global setting from the system
 *
 * @param string $name The name of the setting
 * @param string $message The current process message. The results of this function will be appended to this string
 * @return bool False on error, otherwise true.
 *
 */
function removeGlobalSetting($name, &$message){
	$res = $GLOBALS['db']->delete('settings',$name,'name');
	if($res===false){
		$message .= 'Error deleting global setting: '.$name.':<br />' . mysql_error();
		$GLOBALS['db']->rollbacktransaction();
		return false;
	}
	$message .= 'Deleted global setting: '.$name.'<br />';	
	return true;
}

/**
 * Add a page setting to the system
 *
 * @param string $name The name to use for the setting. This should not include any spaces
 * @param string $type The type of information stored in the setting
 * @param string $data Additional customisation information for this setting. The exact format of this value depends on the type
 * @param string $default The default value
 * @param string $label The label for this setting, when it is listed with the Page details
 * @param string $description A short description of this setting, when it is listed with the Page details. Not currently used
 * @param string $message The current process message. The results of this function will be appended to this string
 * @return bool False on error, otherwise true.
 *
 */
function addPageSetting($name, $type, $data, $default, $label, $description, &$message){	
	$res = $GLOBALS['db']->insertupdate('pagesettings',array('name'=>$name,'type'=>$type,'data'=>$data,'default'=>$default,'label'=>$label,'description'=>$description),array('name'=>$name));
	if($res===false){
		$message .= 'Error adding page setting: '.$name.':<br />' . mysql_error();
		$GLOBALS['db']->rollbacktransaction();
		return false;
	}
	$message .= 'Added page setting: '.$name.'<br />';	
	return true;
}

/**
 * Remove a Page setting from the system
 *
 * @param string $name The name of the setting
 * @param string $message The current process message. The results of this function will be appended to this string
 * @return bool False on error, otherwise true.
 *
 */
function removePageSetting($name, &$message){
	$res = $GLOBALS['db']->delete('pagesettings',$name,'name');
	if($res===false){
		$message .= 'Error deleting page setting: '.$name.':<br />' . mysql_error();
		$GLOBALS['db']->rollbacktransaction();
		return false;
	}
	$message .= 'Deleted page setting: '.$name.'<br />';	
	return true;
}


/**
 * Add a layout setting to the system
 *
 * @param string $name The name to use for the setting. This should not include any spaces
 * @param string $type The type of information stored in the setting
 * @param string $data Additional customisation information for this setting. The exact format of this value depends on the type
 * @param string $default The default value
 * @param string $label The label for this setting, when it is listed with the Layout details
 * @param string $description A short description of this setting, when it is listed with the Layout details. Not currently used
 * @param string $message The current process message. The results of this function will be appended to this string
 * @return bool False on error, otherwise true.
 *
 */
function addLayoutSetting($name, $type, $data, $default, $label, $description, &$message){	
	$res = $GLOBALS['db']->insertupdate('layoutsettings',array('name'=>$name,'type'=>$type,'data'=>$data,'default'=>$default,'label'=>$label,'description'=>$description),array('name'=>$name));
	if($res===false){
		$message .= 'Error adding layout setting: '.$name.':<br />' . mysql_error();
		$GLOBALS['db']->rollbacktransaction();
		return false;
	}
	$message .= 'Added layout setting: '.$name.'<br />';	
	return true;
}


/**
 * Remove a Layout setting from the system
 *
 * @param string $name The name of the setting
 * @param string $message The current process message. The results of this function will be appended to this string
 * @return bool False on error, otherwise true.
 *
 */
function removeLayoutSetting($name, &$message){
	$res = $GLOBALS['db']->delete('layoutsettings',$name,'name');
	if($res===false){
		$message .= 'Error deleting layout setting: '.$name.':<br />' . mysql_error();
		$GLOBALS['db']->rollbacktransaction();
		return false;
	}
	$message .= 'Deleted layout setting: '.$name.'<br />';	
	return true;
}

/**
 * Add a new page to the menu
 *
 * @param int $parent The ID of the containing parent, or null to create a new menu
 * @param string $title The title of the page
 * @param string $keywords The keywords for te page
 * @param string $description The description of the page
 * @param string $menuname The name used in the menu. This should be kept short (max 45 characters)
 * @param string $type The type of page. May be one of: content, link, label, system
 * @param string $forwardurl The URL to use for link pages
 * @param string $specialpage The path to the entry php file for special pages, or the target for links
 * @param bool $candelete Whether or not an admin can delete the page through the Content module
 * @param bool $canedit Whether or not an administrator can edit the page in the Content module
 * @param bool $published Whether or not this page should appear in the menu
 * @param string $pathstub The pathstub is used in conjunction with the pathstubs of all parent pages to construct the fiendly URL for this page
 * @param string $layoutname The classname of the initial layout
 * @param array $custom An array containing the keys and values of any custom settings for this page
 * @param string $message The current process message. The results of this function will be appended to this string
 * @return mixed The ID of the new Page, or false on error
 *
 */
function addPage($parent,$title,$keywords,$description,$menuname,$type,$forwardurl,$specialpage,$candelete,$canedit,$published,$pathstub,$layoutname,$custom,&$message){
	require_once('Content.lib.php');
	// prepare the path stub
	if(!empty($pathstub)) $pathstub = getCleanRoot($title);
	else $pathstub = getCleanRoot($pathstub);
	$ind = 1;
	$root = $pathstub;
	$filename = mysql_real_escape_string($pathstub);
	$parentwhere = empty($parent)||!is_numeric($parent)?'parent is null':'parent = '.$parent;
	while($GLOBALS['db']->selectsingle("select count(*) from pages where $parentwhere and pathstub = '$filename'")){
		$filename = mysql_real_escape_string($root.'_'.++$ind);
		$pathstub = $root.'_'.$ind;
	}
	// insert the values into the pages table
	$id = $GLOBALS['db']->insert('pages',array(
				'parent'=>$parent,
				'title'=>$title,
				'keywords'=>$keywords,
				'description'=>$description,
				'menuname'=>$menuname,
				'type'=>$type,
				'candelete'=>$candelete?1:0,
				'forwardurl'=>$forwardurl,
				'specialpage'=>$specialpage,
				'canedit'=>$canedit?1:0,
				'published'=>$published?1:0,
				'pathstub'=>$pathstub,
				'position'=>getMaxPagePosition($parent),
				'date'=>time(),
				'updated'=>time()
				),true);
	if($id===false){
		$message .= 'Error creating module table entry:<br />' . mysql_error();
		$GLOBALS['db']->rollbacktransaction();
		return false;
	}
	// insert the initial layout
	if(!empty($layoutname) || $type=='content'){
		if(empty($layoutname)) $layoutname = $GLOBALS['skin']->layouts[0];
		$res = $GLOBALS['db']->insert('layouts',array('pageid'=>$id,'position'=>1,'classname'=>$layoutname));
		if($res === false){
			$message .= 'Error creating layout for page:<br />' . mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return null;	
		}	
	}
	// apply custom settings
	if(is_array($custom)){
		$customsettings = Page::getCustomSettings();
		foreach($customsettings as $setting){
			$value = $custom[$setting['name']];
			$res = $GLOBALS['db']->insertupdate('pagevalues',array('pageid'=>$id,'settingid'=>$setting['id'],'value'=>$value),array('pageid'=>$id,'settingid'=>$setting['id']));
			if($res === false){
				$message .= 'Error applying page setting: '.$setting['name'].':<br />' . mysql_error();
				$GLOBALS['db']->rollbacktransaction();
				return false;	
			}
		}
	}
	$message .= 'Page '.$id.' added successfully<br />';	
	return $id;	
}

/**
 * Remove a page from the menu system
 *
 * @param int $id The ID of the page to remove
 * @param string $message The current process message. The results of this function will be appended to this string
 * @return bool False on error, otherwise true.
 *
 */
function removePage($id,&$message){
	$page = Page::GetNewPage($id);
	$res = $page->delete($error,false);
	if($res===false){
		$message .= 'Error deleting page '.$id.':<br />' . $error;
		$GLOBALS['db']->rollbacktransaction();
		return false;
	}
	$message .= 'Deleted page: '.$id.'<br />';	
	return true;
}

/**
 * Remove all pages that are processed using a specified specialpage
 *
 * @param string $specialpage The filename of the special page (eg: news.php)
 * @param string $message The current process message. The results of this function will be appended to this string
 * @return bool False on error, otherwise true.
 *
 */
function removePagesBySpecial($specialpage,&$message){
	$specialpage = mysql_real_escape_string($specialpage);
	$ids = $GLOBALS['db']->selectcolumn("select id from pages where specialpage = '$specialpage'",'id');
	if(is_array($ids)){
		foreach($ids as $id){
			$page = Page::GetNewPage($id);
			$res = $page->DeletePage($error,false);
			if($res===false){
				$message .= 'Error deleting page '.$id.':<br />' . $error;
				$GLOBALS['db']->rollbacktransaction();
				return false;
			}
		}
	}
	$message .= 'Deleted pages using '.$specialpage.'<br />';	
	return true;
}

/**
 * Add a new layout to a given page. Note that this should be done to add additional layouts to an existing page - new pages are created with an initial layout already.
 *
 * @param int $pageid The ID of the page to which this layout should be added
 * @param string $classname The classname of the layout to be added
 * @param int $position The intended position of the layout in the stack on this page
 * @param array $custom An array of custom setting values for this layout
 * @param string $message The current process message. The results of this function will be appended to this string
 * @return mixed The ID of the new Layout, or false on error.
 *
 */
function addLayout($pageid,$classname,$position,$custom,&$message){
	$page = Page::GetNewPage($pageid);
	$layoutid = $page->createLayout($position,$classname,null,$error);
	if($layoutid===false){
		$message .= 'Error creating layout: '.$classname.':<br />' . $error;
		$GLOBALS['db']->rollbacktransaction();
		return false;
	}
	
	if(is_array($custom)){
		$customsettings = Layout::getCustomSettings();
		foreach($customsettings as $setting){
			$value = $custom[$setting['name']];
			$res = $GLOBALS['db']->insertupdate('layoutvalues',array('layoutid'=>$layoutid,'settingid'=>$setting['id'],'value'=>$value),array('layoutid'=>$layoutid,'settingid'=>$setting['id']));
			if($res === false){
				$message .= 'Error applying layout setting: '.$setting['name'].':<br />' . mysql_error();
				$GLOBALS['db']->rollbacktransaction();
				return false;	
			}
		}
	}
	$message .= 'Layout '.$classname.' created<br />';	
	return $layoutid;
}

/**
 * Remove a layout
 *
 * @param int $id The ID of the layout to be deleted
 * @param string $message The current process message. The results of this function will be appended to this string
 * @return bool False on error, otherwise true.
 *
 */
function removeLayout($id,&$message){
	$page = Page::GetNewPage($id);
	$res = $page->deleteLayout($id,$error,false);
	if($res===false){
		$message .= 'Error deleting layout '.$id.':<br />' . $error;
		$GLOBALS['db']->rollbacktransaction();
		return false;
	}
	$message .= 'Deleted layout '.$id.'<br />';	
	return true;
}

/**
 * Copy a file into a new location. Most often used to move a processing file into the root folder.
 *
 * @param string $source The full, absolute path to the source file
 * @param string $target The full, absolute path to the target, including the file name.
 * @param string $message The current process message. The results of this function will be appended to this string
 * @return bool False on error, otherwise true.
 *
 */
function copyFile($source, $target, &$message){	
	$res = @copy($source,$target);
	if($res===false){
		$message .= 'Error copying file: '.basename($source). '<br />';
		$GLOBALS['db']->rollbacktransaction();
		return false;
	}
	$message .= basename($source) . ' copied successfully<br />';	
	return true;
}

/**
 * Delete a file. Most often used to remove a processing file from the root.
 *
 * @param string $target The file to be deleted
 * @param string $message The current process message. The results of this function will be appended to this string
 * @return bool False on error, otherwise true.
 *
 */
function removeFile($target, &$message){
	if(file_exists($target)){
		$res = @unlink($target);
		if($res===false){
			$message .= 'Error deleting file: '.basename($target). '<br />';
			$GLOBALS['db']->rollbacktransaction();
			return false;
		}
		$message .= basename($target) . ' deleted successfully<br />';	
	}else{
		$message .= basename($target) . ' not found<br />';		
	}
	return true;
}

/**
 * Delete a folder
 *
 * @param string $target The path to the folder to be deleted
 * @param string $message The current process message. The results of this function will be appended to this string
 * @return bool False on error, otherwise true.
 *
 */
function removeFolder($target, &$message){
	require_once('Files.lib.php');
	$res = @deleteFolder($target);
	if($res===false){
		$message .= 'Error deleting folder: '.basename($target). '<br />';
		$GLOBALS['db']->rollbacktransaction();
		return false;
	}
	$message .= basename($target) . ' deleted successfully<br />';	
	return true;
}

/**
 * Create a list
 *
 * @param string $name The name of this list
 * @param string $code A short code identifying this list. 
 * @param string $itemname A descriptive noun for an item in this list (this is a list of ____s). The noun should be singular.
 * @param int $width The width required for field controls. If the custom fields for the list will be displayed on the front end, it may be useful to collect the content in fields of the same width as the display area for them
 * @param array $fields An array of the details of all custom fields for this list. Each field should include: label, type, position, data, name
 * @param string $message The current process message. The results of this function will be appended to this string
 * @return mixed The ID of the new list, of false on error
 *
 */
function createList($name, $code, $itemname, $width, $fields, &$message){
	$listid = $GLOBALS['db']->insertupdate('lists',array('name'=>$name,'code'=>$code,'itemname'=>$itemname,'width'=>$width),array('code'=>$code));
	if($listid===false){
		$message .= 'Error creating new list: '.$name.'<br />' . mysql_error();
		$GLOBALS['db']->rollbacktransaction();
		return false;
	}
	if(is_array($fields)){
		foreach($fields as $field){
			$fieldid = $GLOBALS['db']->insert('listfields',array('listid'=>$listid,'label'=>$field['label'],'type'=>$field['type'],'position'=>$field['position'],'data'=>$field['data'],'name'=>$field['name']));
			if($fieldid===false){
				$message .= 'Error creating list field '.$field['name'].':<br />' . mysql_error();
				$GLOBALS['db']->rollbacktransaction();
				return false;
			}
		}	
	}
	$message .= 'Created new list: ID: '.$listid.'<br />';
	return $listid;
}

/**
 * Delete a list from the Lists module
 *
 * @param mixed $code The ID or code of the list to be removed
 * @param string $message The current process message. The results of this function will be appended to this string
 * @return bool False on error, otherwise true.
 *
 */
function removeList($code,&$message){
	$code = mysql_real_escape_string($code);
	$res = $GLOBALS['db']->execute("delete from lists where id = '$code' or code = '$code'");
	if($res===false){
		$message .= 'Error deleting list '.$code.':<br />' . mysql_error();
		$GLOBALS['db']->rollbacktransaction();
		return false;
	}
	$message .= 'Deleted list: '.$code .'<br />';	
	return true;
}

/**
 * Add an item to an existing list
 *
 * @param int $listid The ID of the list
 * @param string $name The name of the item
 * @param array $fields An array of the values of the custom fields
 * @param string $message The current process message. The results of this function will be appended to this string
 * @return mixed The ID of the new list item, of false on error
 *
 */
function addListItem($listid,$name,$fields,&$message){
	$listmanager = ListManager::getListManager();
	$itemid = $GLOBALS['db']->insert('listitems',array('listid'=>$listid,'name'=>$name,'position'=>$listmanager->getMaxListPosition($listid) + 1));
	if($itemid===false){
		$message .= 'Error creating list item: '.$name.'<br />' . mysql_error();
		$GLOBALS['db']->rollbacktransaction();
		return false;
	}
	if(is_array($fields)){
		foreach($fields as $id=>$value){
			if(!is_numeric($id)){
				$id = mysql_real_escape_string($id);
				$id = $GLOBALS['db']->selectsingle("select id from listfields where name = '$id'");
			}
			$fieldid = $GLOBALS['db']->insert('listitemfields',array('itemid'=>$itemid,'fieldid'=>$id,'value'=>$value));
			if($fieldid===false){
				$message .= 'Error creating list field: '.$field['name'].':<br />' . mysql_error();
				$GLOBALS['db']->rollbacktransaction();
				return false;
			}
		}	
	}
	$message .= 'Created list item: '.$name.'<br />';	
	return $itemid;
}

/**
 * Remove an item from a list
 *
 * @param int $id The ID of the list item
 * @param string $message The current process message. The results of this function will be appended to this string
 * @return bool False on error, otherwise true.
 *
 */
function removeListItem($id, &$message){
	$listid = $GLOBALS['db']->delete('listitems',$id,'id');
	if($listid===false){
		$message .= 'Error deleting list item '.$id.':<br />' . mysql_error();
		$GLOBALS['db']->rollbacktransaction();
		return false;
	}
	$message .= 'Deleted list item '.$id .'<br />';	
	return true;
}

/**
 * Add a link to the admin menu
 *
 * @param string $code The code to use for the module. This should be short, and without spaces. All pages within this admin module should set $cmslinkpageid to this code.
 * @param string $name The name of the module. This will be used as a display label
 * @param string $path The path to the entry point for the module in the admin, from the site root, including bounding slashes (eg: /modules/News/allnews.admin.php)
 * @param string $description The description of the link. Not currently used.
 * @param int $position The relative position of this item within the list. By convention, each item is given a number between 1 and 100, default 50.
 * @param int $rights The rights required to access the section. The link will not be displayed if the user does not have these rights. Ignored if the useadminrights setting is off.
 * @param string $message The current process message. The results of this function will be appended to this string
 * @return bool False on error, otherwise true.
 *
 */
function addAdminLink($code, $name, $path, $description, $position, $rights, &$message){
	$res = $GLOBALS['db']->insertupdate('adminlinks',array('code'=>$code,'name'=>$name,'path'=>$path,'description'=>$description,'position'=>(int)$position,'rights'=>(int)$rights),array('code'=>$code));
	if($res===false){
		$message .= 'Error creating admin link '.$code.':<br />' . mysql_error();
		$GLOBALS['db']->rollbacktransaction();
		return false;
	}
	$message .= 'Created admin link: '.$code.'<br />';	
	return true;
}

/**
 * Remove a link from the admin menu
 *
 * @param string $code The code for the module
 * @param string $message The current process message. The results of this function will be appended to this string
 * @return bool False on error, otherwise true.
 *
 */
function removeAdminLink($code, &$message){
	$res = $GLOBALS['db']->delete('adminlinks',$code,'code');
	if($res===false){
		$message .= 'Error deleting admin link '.$code.':<br />' . mysql_error();
		$GLOBALS['db']->rollbacktransaction();
		return false;
	}
	$message .= 'Deleted admin link: '.$code.'<br />';	
	return true;
}

/**
 * Add a new Admin Right
 *
 * @param string $code The short code for this right. A constant will be created by prepending "RIGHT_" to an uppercased version of this.
 * @param string $description The description to appear in the user management section
 * @param string $message The current process message. The results of this function will be appended to this string
 * @return int The ID of this right - use as a bitwise value for checking this right.
 *
 */
function addUserRight($code, $description, &$message){
	$code = mysql_real_escape_string(getCleanRoot($code));
	$id = $GLOBALS['db']->selectsingle("select id from user_rights where const = '$code'");
	if(empty($id)){
		$used = $GLOBALS['db']->selectcolumn("select id from user_rights");
		$id = 1;
		while(in_array($id,$used)) $id *= 2;
		$res = $GLOBALS['db']->insert('user_rights',array('id'=>$id,'const'=>$code,'description'=>$description),array('const'=>$code));
		if($res===false){
			$message .= 'Error adding user right: '.$code.':<br />' . mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return false;
		}
		$message .= 'Added user right: '.$code.'<br />';	
	}
	return $id;
}

function removeUserRight($code, &$message){
	$res = $GLOBALS['db']->delete('user_rights',$code,'const');
	if($res===false){
		$message .= 'Error deleting user right '.$code.':<br />' . mysql_error();
		$GLOBALS['db']->rollbacktransaction();
		return false;
	}
	$message .= 'Deleted user right: '.$code.'<br />';	
	return true;
}

/**
 * Execute an SQL command. NOTE: This method makes no attempt to prevent any security issues - it simply executes the code provided. All security issues must be handled before this step.
 *
 * @param string $sql The SQL to be executed
 * @param string $message The current process message. The results of this function will be appended to this string
 * @return bool False on error, otherwise true.
 *
 */
function executeSQL($sql, &$message){
	$res = $GLOBALS['db']->execute($sql);
	if($res===false){
		$message .= 'Error executing SQL:<br />' . mysql_error();
		$GLOBALS['db']->rollbacktransaction();
		return false;
	}
	$message .= 'SQL executed<br />';	
	return true;
}

/**
 * Drop a table from the database, if it exists
 *
 * @param string $tablename The name of the table to drop
 * @param string $message The current process message. The results of this function will be appended to this string
 * @return bool False on error, otherwise true.
 *
 */
function dropTable($tablename, &$message){
	$tablename = mysql_real_escape_string($tablename);
	$res = $GLOBALS['db']->execute("DROP TABLE IF EXISTS `$tablename`;");
	if($res===false){
		$message .= 'Error dropping table: '.$tablename.':<br />' . mysql_error();
		$GLOBALS['db']->rollbacktransaction();
		return false;
	}
	$message .= 'Dropped table: '.$tablename.', if found<br />';	
	return true;
}

/**
 * Register an event handler for this module
 *
 * @param string $event The name of the event
 * @param string $type The type of handler. May be function (for a library file), class (for a static class method) or instance (for a method of an existing object). 
 * @param string $classname The name of the class, for "class" or "instance" types
 * @param string $function The name of the functio or method
 * @param string $path The path to the library file containing the method, relative to the root of the site, for "function" type
 * @param string $message The current process message. The results of this function will be appended to this string
 * @return bool False on error, otherwise true
 *
 */
function addEventHandler($event, $type, $classname, $function, $path, &$message){
	$values = array('event'=>$event,'type'=>$type,'classname'=>$classname,'function'=>$function,'path'=>$path);
	$res = $GLOBALS['db']->insertifnotexists('handlers',$values,$values);
	if($res===false){
		$message .= 'Error creating event handler '.$event.':<br />' . mysql_error();
		$GLOBALS['db']->rollbacktransaction();
		return false;
	}
	$message .= 'Created event handler: '.$event.'<br />';	
	return true;
}

/**
 * Deregister an event handler. Exact details of the handler must be supplied because the ID of the handler will not be known in the remove script.
 *
 * @param string $event The name of the event
 * @param string $type The type of handler. May be function (for a library file), class (for a static class method) or instance (for a method of an existing object). 
 * @param string $classname The name of the class, for "class" or "instance" types
 * @param string $function The name of the functio or method
 * @param string $path The path to the library file containing the method, relative to the root of the site, for "function" type
 * @param string $message The current process message. The results of this function will be appended to this string
 * @return bool False on error, otherwise true
 *
 */
function removeEventHandler($event, $type, $classname, $function, $path, &$message){
	$event = mysql_real_escape_string($event);
	$type = mysql_real_escape_string($type);
	$classname = mysql_real_escape_string($classname);
	$function = mysql_real_escape_string($function);
	$path = mysql_real_escape_string($path);
	$res = $GLOBALS['db']->execute("delete from handlers where event = '$event' and type = '$type' and classname = '$classname' and function = '$function' and path = '$path'");
	if($res===false){
		$message .= 'Error deleting event handler '.$event.':<br />' . mysql_error();
		$GLOBALS['db']->rollbacktransaction();
		return false;
	}
	$message .= 'Deleted event handler: '.$event.'<br />';	
	return true;
}

/**
 * Flush the HTMPaths cache, limiting the flush to only records with the supplied filename and attribute values.
 *
 * @param string $filename The name of the processing file
 * @param string $att1 The name of the first attribute. Ignored if empty or null.
 * @param string $att2 The name of the second attribute. Ignored if empty or null.
 * @param string $att3 The name of the third attribute. Ignored if empty or null.
 * @param string $message The current process message. The results of this function will be appended to this string
 * @return bool False on error, otherwise true.
 *
 */
function flushHTMCache($filename, $att1, $att2, $att3, &$message){
	$filename = mysql_real_escape_string($filename);
	$att1 = mysql_real_escape_string($att1);
	$att2 = mysql_real_escape_string($att2);
	$att3 = mysql_real_escape_string($att3);
	$sql = "delete from htmpath";
	if(!empty($filename) || !empty($att1) || !empty($att2) || !empty($att3)){
		if(!empty($filename)) $where .= "filename = '$filename'";
		if(!empty($att1)) $where .= (empty($where)?'':" and ") . "att1 = '$att1'";
		if(!empty($att2)) $where .= (empty($where)?'':" and ") . "att2 = '$att2'";
		if(!empty($att3)) $where .= (empty($where)?'':" and ") . "att3 = '$att3'";
		$sql .= " where $where";
	}
	$res = $GLOBALS['db']->execute($sql);
	if($res===false){
		$message .= 'Error flushing HTMPaths for file: '.$filename.':<br />' . mysql_error();
		$GLOBALS['db']->rollbacktransaction();
		return false;
	}
	$message .= 'Flushed HTMPaths for: '.$filename.'<br />';	
	return true;
}

?>