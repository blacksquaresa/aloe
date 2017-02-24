<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

require_once('Content.lib.php');
require_once('HTMPaths.lib.php');

/**
 * This class represents a single page within the core content management system for Aloe.
 *
 * @package Classes
 * @subpackage Content
 * @since 2.0
 */
class Page{
	
	// {{{ Declarations
	/**
	 * The database ID of this page
	 *
	 * @var int 
	 *
	 */
	public $id = 0;
	
	/**
	 * Whether this instance will be used by the editor, or for display on the website
	 *
	 * @var bool 
	 *
	 */
	public $foredit;
	/**
	 * The database ID of the parent page, or null for a top level page
	 * 
	 * @var int
	 */
	public $parent = null;
	
	/**
	 * The title of the page
	 *
	 * @var string 
	 *
	 */
	public $title = '';
	
	/**
	 * The keywords to use in the meta-tag
	 *
	 * @var string 
	 *
	 */
	public $keywords = '';
	
	/**
	 * The description of the page. Used in meta-data, and sometimes in page indexes.
	 *
	 * @var string 
	 *
	 */
	public $description = '';
	
	/**
	 * The type of page. May be content, link, label or special
	 * 
	 * content: this page has a layout, and can accept content
	 * link: this page should be included in the menu as a link to another URL
	 * label: this page is included in the menu only as a label. Often used with dropdown menus
	 * special: this page is special, often the entry page for an extension module (eg: News)
	 *
	 * @var string 
	 *
	 */
	public $type = 'link';
	
	/**
	 * The position of this page relative to its siblings, in the menu tree
	 *
	 * @var int 
	 *
	 */
	public $position = 0;
	
	/**
	 * Link pages will redirect the user to this URL
	 *
	 * @var string 
	 *
	 */
	public $forwardurl = ''; 
	
	/**
	 * The name for this page to use in the menu
	 *
	 * @var string 
	 *
	 */
	public $menuname = '';
	
	/**
	 * Whether or not the administrator may delete this page
	 *
	 * @var bool 
	 *
	 */
	public $candelete = true;
	
	/**
	 * Whether or not an admin can edit the content of this page
	 *
	 * @var bool 
	 *
	 */
	public $canedit = true;
	
	/**
	 * Whether or not this page should be included in the front-end menu
	 * 
	 * This value is an integer because of a legacy system allowing edits on a page without publishing. 
	 * To check this value, 0 indicates not published, while any positive value should indicate that the page has been published.
	 *
	 * @var int 
	 *
	 */
	public $published = false;
	
	/**
	 * Whether or not this page has children
	 *
	 * @var bool 
	 *
	 */
	public $haschildren = false;
	
	/**
	 * An array of all child pages
	 * 
	 * This property will not be populated by default. You must call PopulateChildren to populate this property.
	 *
	 * @var Page[] 
	 *
	 */
	public $children = array();
	
	/**
	 * The compiled HTML content of the page.
	 * 
	 * This property will not be populated by default. You must call PopulateContent to populate this property.
	 *
	 * @var string 
	 *
	 */
	public $pagecontent = '';
	
	/**
	 * The path to the PHP page to run for special pages, or the target for link pages
	 *
	 * @var string 
	 *
	 */
	public $specialpage;
	
	/**
	 * The date this page was created, as a unix timestamp
	 *
	 * @var int 
	 *
	 */
	public $date;
	
	/**
	 * The date this page was created, as a display string
	 *
	 * @var string 
	 *
	 */
	public $displaydate;
	
	/**
	 * The date this page was last updated, as a unix timestamp
	 *
	 * @var int 
	 *
	 */
	public $updated;
	
	/**
	 * The date this page was last updated, as a display string
	 *
	 * @var string 
	 *
	 */
	public $displayupdated;
	/**
	 * The pathstub is used, along with the pathstubs of the parent pages, to build the HTM path of the page
	 * 
	 * @var string
	 * 
	 */
	public $pathstub;
	/**
	 * The path created by appending the pathstubs of all parents. The pathstub is appended to this value to create the HTM path for the page
	 * 
	 * @var string
	 * 
	 */
	public $path;
	
	/**
	 * The HTM path for this page
	 *
	 * @var string 
	 *
	 */
	public $friendlyurl;
	
	/**
	 * An array of Layouts that make up this page, in position order, with the database ID as key
	 *
	 * @var Layout[] 
	 *
	 */
	public $layouts;
	
	/**
	 * An array of the IDs of the columns in the page
	 *
	 * @var array 
	 *
	 */
	public $columnarray;
	
	/**
	 * An array containing the names of all the types of content blocks used on this page
	 *
	 * @var array 
	 *
	 */
	public $blocktypes;
	
	/**
	 * An array containing the values of all custom fields defined for this Page
	 *
	 * @var array 
	 *
	 */
	public $custom;
	#endregion
	
	// {{{ Constructor
	
	/**
	 * The constructor for this class. Fetches page details and populates the instance
	 * 
	 * The constructor will populate most of the properties of the Page, but some of the more complicated, or recursive, properties are left. 
	 * You will need to explicitly populate these properties by calling the relevant Populate methods.
	 *
	 * @param int $id The database ID of the page
	 * @param bool $foredit True if the page is to be edited in the CMS, false if it is to be displayed on the site
	 *
	 */
	function __construct($id,$foredit=false){
		$this->foredit = $foredit;
		if(empty($id) || !is_numeric($id)) $id = 0;
		$sql = "select * from pages where id = $id";
		$pg = $GLOBALS['db']->selectrow($sql);
		if(empty($pg)){
			throw new Exception("Page $id not found");
		}
		$this->id = $pg['id'];
		$this->parent = $pg['parent'];
		$this->title = $pg['title'];
		$this->keywords = $pg['keywords'];
		$this->description = $pg['description'];
		$this->type = $pg['type'];
		$this->position = $pg['position'];
		$this->forwardurl = $pg['forwardurl'];
		$this->menuname = $pg['menuname'];
		$this->candelete = $pg['candelete']?true:false;
		$this->canedit = $pg['canedit']?true:false;
		$this->specialpage = $pg['specialpage'];
		$this->published = $pg['published'];
		$this->pathstub = empty($pg['pathstub'])?getCleanRoot($this->title):$pg['pathstub'];
		$this->date = $pg['date'];
		$this->displaydate = empty($this->date)?'unknown':date('d F Y',$this->date);
		$this->updated = $pg['updated'];
		$this->displayupdated = empty($this->updated)?'unknown':date('d F Y',$this->updated);
		$this->image_header = $pg['image_header'];
		
		$this->haschildren = $GLOBALS['db']->selectsingle("select count(*) from pages where parent = $id")?true:false;
	}
	#endregion
	
	// {{{ Populate Properties
	/**
	 * Populate the Friendly URL field. If the HTM path has previously been populated, it will be fetched from the cache. Otherwise, it will be built from the path and pathstub properties.
	 *
	 */
	public function PopulateFriendlyUrl(){
		require_once('HTMPaths.lib.php');
		$this->friendlyurl = getHTMPath(null,'index.php','id',$this->id);
		if(substr($this->friendlyurl,0,4) != 'http' && substr($this->friendlyurl,0,1) != '/'){
			$this->friendlyurl = $GLOBALS['settings']->siteroot . '/' . substr($this->friendlyurl,strlen($GLOBALS['webroot']));
		}elseif(substr($this->friendlyurl,0,1) == '/'){
			$this->friendlyurl = $GLOBALS['settings']->siteroot . $this->friendlyurl;
		}
		$this->path = getPagePath($this->parent);
	}
	/**
	 * Populate the layouts array with populated layouts, and build the column array
	 */
	public function PopulateLayouts(){
		$layoutdata = $GLOBALS['db']->select("select * from layouts where pageid = {$this->id} order by position");
		$columnarray = array();
		$this->layouts = array();
		foreach($layoutdata as $data){
			$this->layouts[$data['id']] = Layout::getLayout($data['classname'],$this->id,$data['position'],$this->foredit,$data['id']);
			$this->layouts[$data['id']]->PopulateCustom();
			$columnarray = array_merge($columnarray,$this->layouts[$data['id']]->columnIds);
		}
		$this->columnarray = array_unique($columnarray);
	}
	/**
	 * Populate the Block Types property
	 */
	public function PopulateBlockTypes(){
		if(empty($this->blocktypes)){
			$ids = implode(',',array_keys($this->layouts));
			if(!empty($ids)) $this->blocktypes = $GLOBALS['db']->selectcolumn("select distinct c.module from content c where c.layout in ($ids) order by c.module",'module');
		}
	}
	
	/**
	 * Populate the Children array with Page instances of their own. 
	 *
	 * @param bool $recursive Whether or not to make the process recursive
	 */
	public function PopulateChildren($recursive=false){
		$this->children = array();
		$sql = "select * from pages where parent = {$this->id}";
		$children = $GLOBALS['db']->select($sql);
		foreach($children as $child){
			$page = Page::GetNewPage($child['id'],$this->foredit);
			if($recursive) $page->PopulateChildren($recursive);
			$this->children[$child['id']] = $page;
		}
	}
	
	/**
	 * Build the content of the page
	 */
	public function PopulateContent(){	
		$this->PopulateCustom();
		$this->PopulateLayouts();
		if(!$foredit){ // include custom css files
			$this->PopulateBlockTypes();
			$this->pagecontent .= $this->getCSS();
		}
		foreach($this->layouts as $layout) $this->pagecontent .= $layout->getContent();
	}
	
	/**
	 * Make a list of link tags calling the CSS for each of the content blocks used in the page. 
	 *
	 * @return string The HTML code containing the link tags
	 *
	 */
	private function getCSS(){
		foreach($this->blocktypes as $module){
			if(file_exists($GLOBALS['settings']->contentpath.$module.'/'.$module.'.css')){
				$res .= '<link href="'.$GLOBALS['settings']->contentpathweb.$module.'/'.$module.'.css" rel="stylesheet" type="text/css" />'."\r\n";	
			}
			if(file_exists($GLOBALS['skin']->path.'/content/'.$module.'/'.$module.'.css')){
				$res .= '<link href="'.$GLOBALS['skin']->webpath.'/content/'.$module.'/'.$module.'.css" rel="stylesheet" type="text/css" />'."\r\n";	
			}
		}
		return $res;
	}
	
	/**
	 * Populate the custom fields for this Page
	 */
	public function PopulateCustom(){	
		$this->custom = array();
		$this->custom['_settings'] = Page::getCustomSettings();
		$values = $GLOBALS['db']->selectindex("select * from pagevalues where pageid = {$this->id}",'settingid');
		foreach($this->custom['_settings'] as $setting){
			if($setting['type']=='array'){
				$val = isset($values[$setting['id']])?$values[$setting['id']]['value']:$setting['default'];
				$val = json_decode($val);
				$this->custom[$setting['name']] = $val;
			}else{
				$this->custom[$setting['name']] = isset($values[$setting['id']])?$values[$setting['id']]['value']:$setting['default'];
			}
		}
	}
	#endregion
	
	// {{{ Update Method
	/**
	 * Update this page
	 *
	 * @param string $title The title of the page
	 * @param string $keywords The keywords to use in the meta-tag
	 * @param string $description The description, for meta-tags and listings
	 * @param string $menuname The name to use in the menu
	 * @param string $type The type of page
	 * @param string $forwardurl The URL for link pages
	 * @param string $specialpage The page to the entry page for special pages, or the target for link pages
	 * @param array $custom An associative array containing the names and values of custom fields.
	 * @param string $error refrerence string to hold the message on error
	 * @return bool True on success, otherwise false
	 *
	 */
	public function update($title, $keywords, $description, $menuname, $type, $forwardurl, $specialpage, $custom, &$error){
		if(!$this->foredit){
			$error = 'Live pages cannot be updated. Please edit the editable version, then publish the changes.';
			return false;	
		}
		if($type=='link' && empty($forwardurl)){
			$error = 'A forwarding URL is required for a link.';
			return false;	
		}
		$oldtype = $this->type;
		$GLOBALS['db']->begintransaction();
		$values = array();
		$values['title'] = $title;
		$values['keywords'] = $keywords;
		$values['description'] = $description;
		$values['menuname'] = $menuname;
		$values['type'] = $type;
		$values['forwardurl'] = $forwardurl;
		$values['specialpage'] = $specialpage;
		$values['updated'] = time();
		$res = $GLOBALS['db']->update('pages',$values,array('id'=>$this->id));
		if($res === false){
			$error = mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return false;	
		}
		if(!empty($forwardurl)){
			$res = $GLOBALS['db']->execute("update htmpath set htmpath=\"$forwardurl\" where filename='index.php' and att1='id' and val1=".$this->id."");	
			if($res === false){
				$error = mysql_error();
				$GLOBALS['db']->rollbacktransaction();
				return false;	
			}
		}
		
		// Handle Custom Fields
		$customsettings = Page::getCustomSettings();
		if(is_array($customsettings) && count($customsettings)){
			$inputcontrol = new InputControl();
			foreach($customsettings as &$setting){
				$value = $inputcontrol->processControl($custom,$setting['name'],$setting['type']);
				$setting['value'] = $value;
				$res = $GLOBALS['db']->insertupdate('pagevalues',array('pageid'=>$this->id,'settingid'=>$setting['id'],'value'=>$value),array('pageid'=>$this->id,'settingid'=>$setting['id']));
				if($res === false){
					$error = mysql_error();
					$GLOBALS['db']->rollbacktransaction();
					return false;	
				}
			}
		}
		
		$this->title = $title;
		$this->keywords = $keywords;
		$this->description = $description;
		$this->type = $type;
		$this->forwardurl = $forwardurl;
		$this->menuname = $menuname;
		$this->specialpage = $specialpage;
		$this->templateclass = $template;
		$this->published = $published;
		$this->updated = $values['updated'];
		$this->displayupdated = date('d F Y',$this->updated);	
		if(!empty($this->custom)){	
			foreach($customsettings as $setting){
				$this->custom[$setting['name']] = $setting['value'];
			}
		}
		if($type != $oldtype){
			@$this->resetURL($error);
		}
		$GLOBALS['db']->committransaction();
		$this->Save();
		return true;
	}
	#endregion
	
	// {{{ Delete Methods
	/**
	 * Delete this page
	 *
	 * @param string $error refrerence string to hold the message on error
	 * @param bool $usetransaction Whether or not to use a transaction for this action. Use false only if this action is part of a larger action that does use a transaction.
	 * @return bool True on success, otherwise false
	 *
	 */
	public function DeletePage(&$error,$usetransaction=true){
		if($usetransaction) $GLOBALS['db']->begintransaction();
		
		$this->PopulateChildren();
		
		foreach($this->children as $child){
			$res = $child->DeletePage($error,false);		
			if($res == false){
				if($usetransaction) $GLOBALS['db']->rollbacktransaction();
				return false;
			}
		}
		
		// Collect the blocks now, before their details are deleted. 
		// Only process them at the end because they are likely to manage files, which cannot be rolled back.
		$blocks = array();
		$blockids = $GLOBALS['db']->selectcolumn("select c.id from content c inner join layouts l on l.id = c.layout where l.pageid = {$this->id}");
		if(is_array($blockids)){
			foreach($blockids as $blockid){
				$blocks[] = ContentModule::getContentBlock($blockid,true);	
			}
		}
		
		$res = $GLOBALS['db']->delete('pages',$this->id);		
		if($res == false){			
			$error = 'Could not delete the live page.<br />' . mysql_error();
			if($usetransaction) $GLOBALS['db']->rollbacktransaction();
			return false;
		}
		
		$position = $GLOBALS['db']->execute("update pages set position = position - 1 where parent = " . $this->parent . " and position > " . $this->position);
		if($position === false){
			$error = 'Could not reset sibling positions.<br />' . mysql_error();
			if($usetransaction) $GLOBALS['db']->rollbacktransaction();
			return false;
		}
		
		deleteChildHTMPath('index.php',$this->id,true);
		deleteHTMPath('index.php','id',$this->id);	
		
		foreach($blocks as $block){
			$res = $block->DeleteBlock($error);	
			if($res == false){			
				if($usetransaction) $GLOBALS['db']->rollbacktransaction();
				return false;
			}	
		}
			
		if($usetransaction) $GLOBALS['db']->committransaction();	
		return $res;
	}
	

	#endregion
	
	// {{{ Publish Methods
	/**
	 * Flag this page to appear in the front-end menu
	 *
	 * @param string $error refrerence string to hold the message on error
	 * @return bool True for success, otherwise false
	 *
	 */
	public function PublishPage(&$error){
		$res = $GLOBALS['db']->update('pages',array('published'=>1),array('id'=>$this->id));		
		if($res == false){			
			$error = 'Could not publish the live page.<br />' . mysql_error();
			return false;
		}	
		return $res;
	}
	
	/**
	 * Flag this page as hidden in the front-end menu
	 *
	 * @param string $error refrerence string to hold the message on error
	 * @return bool True for success, otherwise false
	 *
	 */
	public function UnPublishPage(&$error){
		$res = $GLOBALS['db']->update('pages',array('published'=>0),array('id'=>$this->id));		
		if($res == false){			
			$error = 'Could not update page ' . $this->id . '.<br />' . mysql_error();
			return false;		
		}		
		return true;
	}	
	#endregion
	
	// {{{ Move Page
	/**Move the page up or down relative to its siblings. 
	 * 
	 * @param int $to The new position for this page
	 * @param string $error refrerence string to hold the message on error
	 * @return bool True for success, otherwise false
	 * 
	 */
	private function movePage($to, &$error){
		if(!$this->foredit){
			$error = 'Live pages cannot be updated. Please edit the editable version, then publish the changes.';
			return false;	
		}
		$GLOBALS['db']->begintransaction();
		$res = $GLOBALS['db']->update('pages',array('position'=>999999),array('parent'=>$this->parent,'position'=>$to));
		if(!$res){
			$error = 'Could not reset target position.<br />' . mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return false;
		}	
		$res = $GLOBALS['db']->update('pages',array('position'=>$to),array('parent'=>$this->parent,'position'=>$this->position));
		if(!$res){
			$error = 'Could not reset source position.<br />' . mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return false;
		}	
		$res = $GLOBALS['db']->update('pages',array('position'=>$this->position),array('parent'=>$this->parent,'position'=>999999));
		if(!$res){
			$error = 'Could not reset target to source.<br />' . mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return false;
		}	
		$this->position = $to;
		$GLOBALS['db']->committransaction();
		return true;
	}
	
	/**
	 * Move this page up one spot
	 *
	 * @param string $error refrerence string to hold the message on error
	 * @return bool True on success, otherwise false
	 *
	 */
	public function movePageUp(&$error){
		if($this->position <= 1){
			$error = 'Cannot move the first item up any more.';
			return false;	
		}
		return $this->movePage($this->position-1,$error);
	}
	
	/**
	 * Move this page one place down
	 *
	 * @param string $error refrerence string to hold the message on error
	 * @return bool True on success, otherwise false
	 *
	 */
	public function movePageDown(&$error){
		if($this->position >= getMaxPagePosition($this->parent)){
			$error = 'Cannot move the last item down any more.';
			return false;	
		}
		return $this->movePage($this->position + 1,$error);
	}
	
	/**
	 * Move this page to another spot within the whole menu tree. Usually used with drag-and-drop.
	 *
	 * @param int $target The database ID of the new parent
	 * @param int $position The position within the children of the target
	 * @param string $error refrerence string to hold the message on error
	 * @return bool True on success, otherwise false
	 *
	 */
	public function movePageTo($target, $position, &$error){
		$GLOBALS['db']->begintransaction();
		$position = max(min($position,getMaxPagePosition($target) + 1),1);
		$res = $GLOBALS['db']->execute("update pages set position = position - 1 where parent = " . $this->parent . " and position > " . $this->position);
		if($res === false){
			$error = mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return false;	
		}
		$values = array();
		$values['parent'] = $target;
		$values['position'] = $position;
		$res = $GLOBALS['db']->update('pages',$values,array('id'=>$this->id));
		if($res === false){
			$error = mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return false;	
		}
		$res = $GLOBALS['db']->execute("update pages set position = position + 1 where parent = $target and position >= $position and id <> " . $this->id . " order by position desc");
		if($res === false){
			$error = mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return false;	
		}
		$GLOBALS['db']->committransaction();
		return true;
	}
	/**
	 * Order the pages within a branch alphabetically
	 * 
	 * @param string $error refrerence string to hold the message on error
	 * @return array An associative array containing the IDs of all the relevant pages, in the new order.
	 */
	public function OrderPagesAlphabetically(&$error){
		if(!$this->foredit){
			$error = 'Live pages cannot be updated. Please edit the editable version, then publish the changes.';
			return false;	
		}
		$sql = "select * from pages where parent = " . $this->id." order by title";
		$order = $GLOBALS['db']->select($sql);	
		$newposition = 1;	
		$GLOBALS['db']->begintransaction;
		$posarray = array();
		foreach($order as $item){
			$res = $GLOBALS['db']->execute("update pages set position = ".$newposition." where parent = " . $this->id. " and id = ".$item['id']);
			if($res === false){
				$error = 'Could not reset sibling positions.<br />' . mysql_error();
				$GLOBALS['db']->rollbacktransaction;
				return false;
			}		
			$posarray[$newposition] = $item['id'];
			$newposition++;
		}
		$GLOBALS['db']->committransaction;
		return $posarray;
	}	
	#endregion
	
	// {{{ Miscellaneous Methods
	/**
	 * Get the Page instance representing the parent of this page
	 *
	 * @return Page The parent Page
	 *
	 */
	public function getParent(){
		if($this->parent == null) return null;
		return Page::GetNewPage($this->parent,$this->foredit);	
	}
	
	/**
	 * Convert this object into an array
	 *
	 * @return array An array representing this Page, in its current state
	 *
	 */
	public function toArray(){
		$arr = array();
		foreach($this as $key=>$value){
			$arr[$key] = $value;	
		}	
		return $arr;
	}
	
	/**
	 * Change the HTM Path for this page. Attempts to find all references to the page in the content of the site, and update them
	 *
	 * @param string $pathstub The new pathstub to use
	 * @param string $error refrerence string to hold the message on error
	 * @return bool True on success, otherwise false
	 *
	 */
	public function resetURL($pathstub,&$error){
		require_once('HTMPaths.lib.php');
		$ext = pathinfo($pathstub,PATHINFO_EXTENSION);
		if(!empty($ext) && strlen($ext) <= 4) $pathstub = substr($pathstub,-(strlen($ext)+1));
		$pathstub = getCleanRoot($pathstub);
		if(empty($pathstub)) $pathstub = getCleanRoot($this->title);
		if($GLOBALS['db']->selectsingle("select id from pages where id <> {$this->id} and parent = {$this->parent} and pathstub = '$pathstub'")){
			$error = 'There is already another page with that path stub.';
			return false;	
		}		
		$updated = time();
		$res = $GLOBALS['db']->update('pages',array('pathstub'=>$pathstub,'updated'=>$updated),array('id'=>$this->id));
		if($res === false){
			$error = mysql_error();
			return false;	
		}
		$this->pathstub = $pathstub;
		$this->updated = $updated;
		$this->displayupdated = date('d F Y',$updated);
		$this->Save();
		$res = deleteChildHTMPath('index.php',$this->id,true);
		$res = resetHTMPath(null,'index.php','id',$this->id);
		$this->PopulateFriendlyUrl();
		return $res;
	}
	
	/**
	 * Save changes to this Page to the Global page cache.
	 */
	public function Save(){
		$GLOBALS['pages'][$this->id] = $this;	
	}
	#endregion
	
	// {{{ Layouts
	/**
	 * Create a new layout, and position it in the correct place within the page 
	 *
	 * @param int $position The intended position of the new layout
	 * @param string $classname The class name of the layout
	 * @param array $custom An associative array containing the names and values of custom fields.
	 * @param string $error refrerence string to hold the message on error
	 * @return int The ID of the layout on success, otherwise false
	 *
	 */
	public function createLayout($classname, $position, $custom, &$error){
		if(!$this->foredit){
			$error = 'Live pages cannot create layouts. Please add the layout to an editable page, then publish the changes.';
			return false;	
		}
		$GLOBALS['db']->begintransaction();
		if(empty($position) || !is_numeric($position)){
			$pos = getMaxLayoutPosition($this->id) + 1;
		}else{
			$pos = min($position,getMaxLayoutPosition($this->id) + 1);
		}
		$res = $GLOBALS['db']->execute("update layouts set position = position + 1 where pageid = {$this->id} and position >= $pos order by position desc");
		if($res===false){
			$error = 'Could not reset sibling layout positions.<br />' . mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return false;
		}
		$values = array();
		$values['pageid'] = $this->id;
		$values['classname'] = $classname;
		$values['position'] = $pos;
		$id = $GLOBALS['db']->insert('layouts',$values);
		if(!$id){
			$error = 'Could not insert new layout record.<br />' . mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return false;
		}
		
		// Handle Custom Fields
		if(is_array($custom)){
			$customsettings = Layout::getCustomSettings();
			$inputcontrol = new InputControl();
			foreach($customsettings as &$setting){
				$value = $inputcontrol->processControl($custom,$setting['name'],$setting['type']);
				$setting['value'] = $value;
				$res = $GLOBALS['db']->insertupdate('layoutvalues',array('layoutid'=>$id,'settingid'=>$setting['id'],'value'=>$value),array('layoutid'=>$id,'settingid'=>$setting['id']));
				if($res === false){
					$error = mysql_error();
					$GLOBALS['db']->rollbacktransaction();
					return false;	
				}
			}
		}
		
		// update the Page last modified date
		$res = $GLOBALS['db']->update('pages',array('updated'=>time()),array('id'=>$this->id));
		if($res===false){
			$error = 'Could not update page modified time.<br />' . mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return false;
		}
		$GLOBALS['db']->committransaction();
		return $id;
	}
	
	/**
	 * Update an existing layout
	 *
	 * @param int $layoutid The database ID of the layout to edit
	 * @param string $classname The classname of the new layout to use
	 * @param array $custom An associative array containing the names and values of custom fields.
	 * @param string $error refrerence string to hold the message on error
	 * @return int The ID of the layout on success, otherwise false
	 *
	 */
	public function updateLayout($layoutid, $classname, $custom, &$error){
		if(!$this->foredit){
			$error = 'Live pages cannot update layouts. Please update the layout on an editable page, then publish the changes.';
			return false;	
		}
		$GLOBALS['db']->begintransaction();
		$res = $GLOBALS['db']->update('layouts',array('classname'=>$classname),array('id'=>$layoutid));
		if(!$res){
			$error = 'Could not update the layout record.<br />' . mysql_error();
			return false;	
		}
		
		// Handle Custom Fields
		$customsettings = Layout::getCustomSettings();
		if(is_array($customsettings) && count($customsettings)){
			$inputcontrol = new InputControl();
			foreach($customsettings as &$setting){
				$value = $inputcontrol->processControl($custom,$setting['name'],$setting['type']);
				$setting['value'] = $value;
				$res = $GLOBALS['db']->insertupdate('layoutvalues',array('layoutid'=>$layoutid,'settingid'=>$setting['id'],'value'=>$value),array('layoutid'=>$layoutid,'settingid'=>$setting['id']));
				if($res === false){
					$error = mysql_error();
					$GLOBALS['db']->rollbacktransaction();
					return false;	
				}
			}
		}
		
		// update the Page last modified date
		$res = $GLOBALS['db']->update('pages',array('updated'=>time()),array('id'=>$this->id));
		if($res===false){
			$error = 'Could not update page modified time.<br />' . mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return false;
		}
		$GLOBALS['db']->committransaction();
		return $layoutid;
	}
	
	/**
	 * Delete a layout from this page, including all content blocks within in
	 *
	 * @param int $layoutid The database ID of the layout to be deleted
	 * @param string $error reference string to hold the message on error
	 * @return bool True on success, otherwise false
	 *
	 */
	public function deleteLayout($layoutid, &$error, $usetransaction=true){
		if(!$this->foredit){
			$error = 'Live pages cannot delete layouts. Please delete the layout on an editable page, then publish the changes.';
			return false;	
		}
		if($usetransaction) $GLOBALS['db']->begintransaction();
		if(empty($layoutid) || !is_numeric($layoutid)) $layoutid = 0;
		$this->PopulateLayouts();
		$oldlayout = $this->layouts[$layoutid];
		$columnblockids = $oldlayout->getColumnBlockIds();
		foreach($columnblockids as $blockids){
			foreach($blockids as $blockid){
				$res = $this->deleteContentBlock($blockid,$error,false);
				if($res === false){
					$error = "Could not delete a content block (ID: $blockid).<br />$error";
					if($usetransaction) $GLOBALS['db']->rollbacktransaction();
					return false;
				}	
			}
		}
		$res = $GLOBALS['db']->delete('layouts',$layoutid);
		if(!$res){
			$error = 'Could not delete layout record.<br />' . mysql_error();
			if($usetransaction) $GLOBALS['db']->rollbacktransaction();
			return false;
		}
		$res = $GLOBALS['db']->execute("update layouts set position = position - 1 where pageid = " . $this->id . " and position > " . $oldlayout->position);
		if($res === false){
			$error = 'Could not reset sibling positions.<br />' . mysql_error();
			if($usetransaction) $GLOBALS['db']->rollbacktransaction();
			return false;
		}
		// update the Page last modified date
		$res = $GLOBALS['db']->update('pages',array('updated'=>time()),array('id'=>$this->id));
		if($res===false){
			$error = 'Could not update page modified time.<br />' . mysql_error();
			if($usetransaction) $GLOBALS['db']->rollbacktransaction();
			return false;
		}
		if($usetransaction) $GLOBALS['db']->committransaction();
		return true;		
	}
	#endregion	
	
	// {{{ Content Blocks
	/**
	 * Create a new content block, and allocate it to the correct column within this page 
	 *
	 * @param int $col The column into which the content block should be placed
	 * @param string $module The class name of the module
	 * @param string $content The HTML content of the block
	 * @param array $properties An array of the properties for the block
	 * @param int $after the database ID of the block after which this one should be inserted, or null to insert at the top
	 * @param string $error refrerence string to hold the message on error
	 * @return int The ID of the block on success, otherwise false
	 *
	 */
	public function createContentBlock($col, $layout, $module, $content, $properties, $after, &$error){
		if(!$this->foredit){
			$error = 'Live pages cannot create content blocks. Please add the block to an editable page, then publish the changes.';
			return false;	
		}
		$GLOBALS['db']->begintransaction();
		if(empty($after) || !is_numeric($after)){
			$pos = 1;
		}else{
			$pos = $GLOBALS['db']->selectsingle("select position from content where id = $after") + 1;
			if(empty($pos)) $pos = 1;
		}
		$res = $GLOBALS['db']->execute("update content set position = position + 1 where columnid = $col and position >= $pos order by position desc");
		if($res===false){
			$error = 'Could not reset sibling content block positions.<br />' . mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return false;
		}
		$values = array();
		$values['layout'] = $layout;
		$values['columnid'] = $col;
		$values['module'] = $module;
		$values['content'] = $content;
		$values['position'] = $pos;
		$id = $GLOBALS['db']->insert('content',$values);
		if(!$id){
			$error = 'Could not insert new content record.<br />' . mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return false;
		}
		foreach($properties as $name=>$prop){
			if(is_array($prop)) $prop = base64_encode(serialize($prop));
			$res = $GLOBALS['db']->insert('contentproperties',array('contentid'=>$id,'property'=>$name,'value'=>$prop));
			if(!$res){
				$error = 'Could not insert new content record property ' . $name . '.<br />' . mysql_error();
				$GLOBALS['db']->rollbacktransaction();
				return false;
			}
		}
		// Create call the static CreateBlock method.
		// This allows the specific content module implementation to do extra stuff
		try{
			if(file_exists($GLOBALS['settings']->contentpath.$module.'/'.$module.'.class.php')){
				require_once($GLOBALS['settings']->contentpath.$module.'/'.$module.'.class.php');
				eval('$indres = ' . $module . '::CreateBlock($id, $content, $properties, $error);');
				if($indres === false){
					$error = $module.' implementation failed' . (empty($error)?'':'<br />'.$error);
					$GLOBALS['db']->rollbacktransaction();
					return false;
				}
			}else{
				$error = $module.' implementation failed<br />'.$GLOBALS['settings']->contentpath.$module.'/'.$module.'.class.php not found';
				$GLOBALS['db']->rollbacktransaction();
				return false;
			}
		}catch(exception $error){
			$error = $module.' implementation failed<br />'.$error->getMessage();
			$GLOBALS['db']->rollbacktransaction();
			return false;			
		}
		// update the Page last modified date
		$res = $GLOBALS['db']->update('pages',array('updated'=>time()),array('id'=>$this->id));
		if($res===false){
			$error = 'Could not update page modified time.<br />' . mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return false;
		}
		$GLOBALS['db']->committransaction();
		return $id;
	}
	
	/**
	 * Update an existing content block
	 *
	 * @param int $blockid The database ID of the block to edit
	 * @param string $content The HTML content of the block
	 * @param array $properties An associative array of the properties for the block
	 * @param string $error refrerence string to hold the message on error
	 * @return int The ID of the block on success, otherwise false
	 *
	 */
	public function updateContentBlock($blockid, $content, $properties, &$error){
		if(!$this->foredit){
			$error = 'Live pages cannot update content blocks. Please update the block on an editable page, then publish the changes.';
			return false;	
		}
		// Create an instance of the content module, then call the updateBlock method.
		$module = ContentModule::getContentBlock($blockid,true);
		if(!$module){
			$error = 'Could not create Content Module object ';
			return false;
		}
		$GLOBALS['db']->begintransaction();
		$values = array();
		$values['content'] = $content;
		$res = $GLOBALS['db']->update('content',$values,array('id'=>$module->id));
		if(!$res){
			$error = 'Could not update content record.<br />' . mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return false;
		}
		foreach($properties as $name=>$prop){
			if(is_array($prop)) $prop = base64_encode(serialize($prop));
			$res = $GLOBALS['db']->insertupdate('contentproperties',array('contentid'=>$module->id,'property'=>$name,'value'=>$prop),array('contentid'=>$module->id,'property'=>$name),'id');
			if(!$res){
				$error = 'Could not update content record property ' . $name . '.<br />' . mysql_error();
				$GLOBALS['db']->rollbacktransaction();
				return false;
			}
		}
		// Call the Update method of the Content Block
		$res = $module->UpdateBlock($content, $properties, $error);
		if($res===false){
			$GLOBALS['db']->rollbacktransaction();	
			return false;
		}
		// Clear out any cache files that may have been used by the Content Block, in case the sources have been changed or updated.
		$res = $module->ClearCache($error);
		if($res===false){
			$GLOBALS['db']->rollbacktransaction();	
			return false;
		}
		// update the Page last modified date
		$res = $GLOBALS['db']->update('pages',array('updated'=>time()),array('id'=>$this->id));
		if($res===false){
			$error = 'Could not update page modified time.<br />' . mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return false;
		}
		// update the properties of this instance
		$module->content = $content;
		foreach($properties as $name=>$prop){
			$module->$name = $prop;
		}
		$GLOBALS['db']->committransaction();
		return $blockid;
	}
	
	/**
	 * Create a new content block based on the supplied block, and add it to the bottom of the selected column
	 *
	 * @param int $col The ID of the target column
	 * @param int $blockid The database ID of the source block
	 * @param string $error refrerence string to hold the message on error
	 * @return int The ID of the new block on success, otherwise false
	 *
	 */
	public function copyContentBlock($layoutid, $col, $blockid, &$error){
		if(!$this->foredit){
			$error = 'Live pages cannot copy content blocks. Please paste the block on an editable page, then publish the changes.';
			return false;	
		}
		$block = ContentModule::getContentBlock($blockid,true);
		$GLOBALS['db']->begintransaction();
		$values = array();
		$values['layout'] = $layoutid;
		$values['columnid'] = $col;
		$values['module'] = $block->modulename;
		$values['content'] = $block->content;
		$values['position'] = getMaxBlockPosition($layoutid,$col) + 1;
		$id = $GLOBALS['db']->insert('content',$values);
		if(!$id){
			$error = 'Could not insert new content record.<br />' . mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return false;
		}
		foreach($block->properties as $name=>$prop){
			$res = $GLOBALS['db']->insert('contentproperties',array('contentid'=>$id,'property'=>$name,'value'=>$prop));
			if(!$res){
				$error = 'Could not insert new content record property ' . $name . '.<br />' . mysql_error();
				$GLOBALS['db']->rollbacktransaction();
				return false;
			}
		}
		// Call the Copy method of the Content Block
		$res = $block->CopyBlock($id, $error);
		if($res===false){
			$GLOBALS['db']->rollbacktransaction();	
			return false;
		}
		// update the Page last modified date
		$res = $GLOBALS['db']->update('pages',array('updated'=>time()),array('id'=>$this->id));
		if($res===false){
			$error = 'Could not update page modified time.<br />' . mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return false;
		}
		$GLOBALS['db']->committransaction();
		return $id;
	}
	
	/**
	 * Delete a content block from this page, and call any cleanup code the block has defined
	 *
	 * @param int $blockid The databasse ID of the block to be deleted
	 * @param string $error refrerence string to hold the message on error
	 * @return bool True on success, otherwise false
	 *
	 */
	public function deleteContentBlock($blockid, &$error, $usetransacion=true){
		if(!$this->foredit){
			$error = 'Live pages cannot delete content blocks. Please delete the block on an editable page, then publish the changes.';
			return false;	
		}
		if($usetransacion) $GLOBALS['db']->begintransaction();
		if(empty($blockid) || !is_numeric($blockid)) $blockid = 0;
		$oldblock = ContentModule::getContentBlock($blockid,true);
		$res = $GLOBALS['db']->delete('content',$blockid);
		if(!$res){
			$error = 'Could not delete content record.<br />' . mysql_error();
			if($usetransacion) $GLOBALS['db']->rollbacktransaction();
			return false;
		}
		$res = $GLOBALS['db']->execute("update content set position = position - 1 where layout = " . $oldblock->layoutid . " and columnid = " . $oldblock->columnid . " and position > " . $oldblock->position);
		if($res === false){
			$error = 'Could not reset sibling positions.<br />' . mysql_error();
			if($usetransacion) $GLOBALS['db']->rollbacktransaction();
			return false;
		}
		// Clear out any cache files that may have been used by the Content Block.
		@$oldblock->ClearCache($error);
		// Call the deleteBlock method on the old Content Block.
		// This allows the specific content module implementation to do extra stuff
		if(!$oldblock->DeleteBlock($error)){
			if($usetransacion) $GLOBALS['db']->rollbacktransaction();
			return false;
		}
		// update the Page last modified date
		$res = $GLOBALS['db']->update('pages',array('updated'=>time()),array('id'=>$this->id));
		if($res===false){
			$error = 'Could not update page modified time.<br />' . mysql_error();
			if($usetransacion) $GLOBALS['db']->rollbacktransaction();
			return false;
		}
		if($usetransacion) $GLOBALS['db']->committransaction();
		return true;		
	}
	#endregion	
	
	// {{{ Static Methods
	/**
	 * Fetch an instance of the PAge class for the supplied ID
	 * 
	 * This method uses a Request caching system, so that any piece of code in the system can get direct access to the same instance of the Page class, and it only needs to be instanciated once.
	 * 
	 * @param int $id The database ID of the required page
	 * @param bool $foredit True if the Page will be used in the CMS, false if it is used for front-end display
	 * @return Page The resulting Page instance
	 * 
	 */
	public static function GetNewPage($id,$foredit=false){
		if(!isset($GLOBALS['pages'][$id])){
			$GLOBALS['pages'][$id] = new Page($id,$foredit);
		}		
		return $GLOBALS['pages'][$id];		
	}
	
	/**
	 * Creates a new page
	 *
	 * @param int $parent The database ID of the parent page
	 * @param string $title The title of the page
	 * @param string $keywords The keywords for the meta-tag
	 * @param string $description The description, for meta-tag and lists
	 * @param string $menuname The name to use in the menu
	 * @param string $type The type of page to create
	 * @param string $forwardurl The URL to use for link pages
	 * @param string $specialpage The path to the entry php file for special pages, or the target for links
	 * @param string $layout The classname of the initial layout
	 * @param int $published Whether or not this page should be included in the menu
	 * @param array $custom An associative array containing the names and values of custom fields.
	 * @param string $error refrerence string to hold the message on error
	 * @return Page An instance of the Page class representing the new page.
	 *
	 */
	public static function CreatePage($parent, $title, $keywords, $description, $menuname, $type, $forwardurl, $specialpage, $layout, $published, $custom, &$error){
		if(strlen($menuname) > 45){
			$error = 'The menu name is too long - please choose a more concise menu name.';
			return false;	
		}
		$GLOBALS['db']->begintransaction();
		$title = empty($title)?$menuname:$title;
		$pathstub = getCleanRoot($title);
		$ind = 1;
		$root = $pathstub;
		$filename = mysql_real_escape_string($pathstub);
		$parentwhere = empty($parent)||!is_numeric($parent)?'parent is null':'parent = '.$parent;
		while($GLOBALS['db']->selectsingle("select count(*) from pages where $parentwhere and pathstub = '$filename'")){
			$filename = mysql_real_escape_string($root.'_'.++$ind);
			$pathstub = $root.'_'.$ind;
		}
		$values = array();
		$values['parent'] = $parent;
		$values['title'] = $title;
		$values['keywords'] = $keywords;
		$values['description'] = $description;
		$values['menuname'] = $menuname;
		$values['type'] = $type;
		$values['forwardurl'] = $forwardurl;
		$values['specialpage'] = $specialpage;
		$values['candelete'] = 1;
		$values['canedit'] = 1;
		$values['published'] = in_array($type,array('link','label'))?1:$published;
		$values['date'] = time();
		$values['updated'] = $values['date'];
		$values['pathstub'] = $pathstub;
		$values['position'] = getMaxPagePosition($parent) + 1;
		$id = $GLOBALS['db']->insert('pages',$values);
		if($id == false){
			$error = mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return null;	
		}
		
		// Handle Layout template
		if(empty($layout)) $layout = $GLOBALS['skin']->layouts[0];
		$res = $GLOBALS['db']->insert('layouts',array('pageid'=>$id,'position'=>1,'classname'=>$layout));
		if($res === false){
			$error = mysql_error();
			$GLOBALS['db']->rollbacktransaction();
			return null;	
		}
		
		// Handle Custom Fields
		$customsettings = Page::getCustomSettings();
		if(is_array($customsettings) && count($customsettings)){
			$inputcontrol = new InputControl();
			foreach($customsettings as &$setting){
				$value = $inputcontrol->processControl($custom,$setting['name'],$setting['type']);
				$setting['value'] = $value;
				$res = $GLOBALS['db']->insertupdate('pagevalues',array('pageid'=>$id,'settingid'=>$setting['id'],'value'=>$value),array('pageid'=>$id,'settingid'=>$setting['id']));
				if($res === false){
					$error = mysql_error();
					$GLOBALS['db']->rollbacktransaction();
					return false;	
				}
			}
		}
		
		$GLOBALS['db']->committransaction();
		$page = Page::GetNewPage($id,true);
		return $page;
	}
	
	/**
	 * Fetch the list of custom settings defined for all pages
	 *
	 * @return array A multi-dimensional array containing the details of all custom settings
	 *
	 */
	public static function getCustomSettings(){
		$settings = $GLOBALS['db']->selectindex("select * from pagesettings",'name');
		return $settings;
	}
	#endregion
}

?>