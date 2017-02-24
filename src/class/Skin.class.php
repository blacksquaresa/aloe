<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

/**
 * This abstract class is used as a base for all skins used by the system
 * 
 * Specific skins must extend this class, and provide an implementation that includes the prepareSkin method, at the very least.
 * 
 * @package Classes
 * @subpackage Skins
 * @since 2.2
 *
 */
abstract class Skin{
	
	// {{{ Declarations
	/**
	 * The class name of this skin. 
	 *
	 * @var string 
	 *
	 */
	public $classname;
	/**
	 * The display name of this skin. 
	 *
	 * @var string 
	 *
	 */
	public $name;
	
	/**
	 * The absolute file path to use to access files within this skin
	 *
	 * @var string 
	 *
	 */
	public $path;
	
	/**
	 * The webroot to be used to reference files within the skin from the current page.
	 *
	 * @var string 
	 *
	 */
	public $webpath;
	
	/**
	 * A list of the names of all Layouts available for this skin.
	 *
	 * @var array 
	 *
	 */
	public $layouts = array();
	
	/**
	 * A collection of Column objects representing all the columns used in the various layouts available to this skin
	 *
	 * @var ContentColumn[] 
	 *
	 */
	public $columns = array();
	
	/**
	 * A multi-dimensional array representing all the settings relevant to this skin, organised by Module
	 *
	 * @var array 
	 *
	 */
	public $settings = array();
	
	/**
	 * The width of the main content area for this skin. For responsive designs, this should be the width in the standard PC (1000px wide) version.
	 *
	 * @var int 
	 *
	 */
	public $contentwidth = 960;
	#endregion
	
	// {{{ Constructor
	
	/**
	 * Constructor method.
	 * 
	 * The constructor will prepare file and web paths and the name of the Skin, then call the prepareSkin method.
	 *
	 * @return void 
	 *
	 */
	public function __construct(){
		$rc = new ReflectionClass($this);
		$this->path = dirname($rc->getFileName());
		$this->webpath = $GLOBALS['webroot'] . str_replace('\\','/',substr($this->path,strlen($GLOBALS['documentroot'])+1));
		$this->classname = get_class($this);
		if(empty($this->name)) $this->name = trim(preg_replace('/([A-Z])/',' $1',$this->classname));
		$this->prepareSkin();
	}
	#endregion
	
	// {{{ Prepare the Skin
	/**
	 * Called from the constructor, and required to prepare the Skin.
	 * 
	 * At minimum, this method should populate the Layout and Column collections, using the prepareLayout and prepareColumn methods.
	 * It may also prepare settings, if required by modules, controls or content blocks used on the site.
	 *
	 * @return void
	 *
	 */
	protected abstract function prepareSkin();
	/**
	 * Add a column to the list of columns available in layouts in this skin. 
	 * 
	 * Column IDs should be incremental, and start at 1.
	 * This method will define a constant for this column ID, as 'CONTENTCOLUMN_name' where 'name' is the name supplied
	 * The admin column width should be calculated from the starting value of width+8. This may be incresed in increments of 5 to 
	 * make sure the column fits nicely in the CMS editor, in the arrangement of columns in all the layouts.
	 *
	 * @param int $id The ID of this column.
	 * @param string $name A name for this column
	 * @param int $width A pixel width for this column
	 * @param int $adminwidth A calculated width for this column, to be used in the CMS editor.
	 * @param array $contentblocks An array of the names of all content blocks available for this column
	 * @return void 
	 *
	 */
	public function prepareColumn($id,$name,$width,$adminwidth,$contentblocks=null){
		define('CONTENTCOLUMN_'.$name,$id);
		$this->columns[$id] = new ContentColumn($id,$width,$adminwidth,$contentblocks);
	}	
	
	/**
	 * Use this method to add a content block to the allowed lists for columns in the skin. 
	 * 
	 * This is an alternative to adding the name of the column to each of the prepareColumn calls.
	 *
	 * @param string $name The class name of the Content Block
	 * @param array $columnids A list of the IDs of all columns that this Content Block should be allowed in.
	 * @return void 
	 *
	 */
	public function prepareContentBlock($name,$columnids){
		if(is_array($columnids)){
			foreach($columnids as $colid){
				if(isset($this->columns[$colid]) && !in_array($name,$this->columns[$colid]->contentblocks)){
					$this->columns[$colid]->contentblocks[] = $name;
				}	
			}	
		}	
	}
	
	/**
	 * Add a l\Layout to the list of those allowed for this Skin
	 *
	 * @param string $name The class name of the Layout
	 * @return void 
	 *
	 */
	public function prepareLayout($name){
		$path = $this->path.'/layouts/'.$name.'.lay.php';
		if(file_exists($path)){
			$this->layouts[] = $name;
		}	
	}
	
	/**
	 * Use this method to set a Skin Setting, which may be used by a Content Block, Control or Module.
	 * 
	 * If you wish to make this value a Ste Setting, which can be managed by the site admin, create a Site Setting when this Skin is installed, 
	 * then pass the value of that Site Setting as the value parameter here.
	 *
	 * @param string $module The name of the Module, Control or Content Block. 
	 * @param string $name The name of the setting
	 * @param string $value The value to assign to this setting
	 * @return void 
	 *
	 */
	public function prepareSetting($module,$name,$value){
		if(empty($module)) $module = '__General';
		if(!isset($this->settings[$module])) $this->settings[$module] = array();
		$this->settings[$module][$name] = $value;
	}
	
	/**
	 * Call this method from a Module, Control or Content Block to fetch the value of the Skin Setting required
	 *
	 * @param string $module The name of the Module, Control or Content Block
	 * @param string $name The name of the setting required
	 * @param mixed $default The default value for the setting, in case it has not been set
	 * @return mixed The value of the Skin Setting if set, or the supplied default if not.
	 *
	 */
	public function getSetting($module,$name,$default){
		if(empty($module)) $module = '__General';
		if(isset($this->settings[$module][$name])) return $this->settings[$module][$name];
		else return $default;
	}
	#endregion
	
	// {{{ Meta tags
	
	/**
	 * This method will construct meta tags from the data in the pageobject, and defaulting to values in settings if it can't find relevant data.
	 * 
	 * The method creates and populates a global Object called $metatags, which contains values for:
	 *   -  title
	 *   -  description
	 *   -  keywords
	 *   -  fbsitelogo - to tell Facebook what image to use if the page is shared.
	 *
	 * @return bool Always true.
	 *
	 */
	protected function prepareMetaTags(){
		global $metatags, $pageobject, $settings;
		$metatags = new stdClass();
		$metatags->title = empty($pageobject->title)?$settings->defaulttitle:$pageobject->title;
		$metatags->description = empty($pageobject->description)?$settings->defaultdescription:$pageobject->description;
		$metatags->keywords = empty($pageobject->keywords)?$settings->defaultkeywords:$pageobject->keywords;
		if(!empty($settings->titleprefix)) $metatags->title = $settings->titleprefix . $metatags->title;
		$metatags->fbsitelogo = empty($settings->fbsitelogo)?$this->getFile('images/logo.png',''):$settings->fbsitelogo;
		if(!empty($metatags->fbsitelogo) && substr($metatags->fbsitelogo,0,4) != 'http') $metatags->fbsitelogo = $settings->siteroot.'/'.$metatags->fbsitelogo;
		return true;
	}
	
	/**
	 * This method will construct a meta-tag HTML fragment from the $metatag, $pageobject and $settings variables.
	 * 
	 * This tag will create the standard title, description and keywords meta tags. 
	 * If the site is live, it will also add a set of Open Graph tags and, if the correct Site Settings exist, Google or Bing site verification tags. 
	 * It will also add a robots tag, inclusive if the site is live, but with nofollow and noindex flags if not.
	 *
	 * @return string The HTML fragment containing a set of META tags.
	 *
	 */
	protected function getMetaTags(){
		global $metatags, $pageobject, $settings;
		$tags = '<title>'.$metatags->title.'</title>'.PHP_EOL;
		$tags .= '<meta name="description" content="'.$metatags->description.'" />'.PHP_EOL;
		$tags .= '<meta name="keywords" content="'.$metatags->keywords.'" />'.PHP_EOL;
		$tags .= '<meta property="og:title" content="'.$metatags->title.'" />'.PHP_EOL;
		$tags .= '<meta property="og:type" content="website" />'.PHP_EOL;
		if(!empty($settings->fbsitelogo)) $tags .= '<meta property="og:image" content="'.$metatags->fbsitelogo.'" />'.PHP_EOL;
		$tags .= '<meta property="og:site_name" content="'.$settings->sitename.'" />'.PHP_EOL;
		if(!empty($settings->fbappid)) $tags .= '<meta property="fb:app_id" content="'.$settings->fbappid.'" />'.PHP_EOL;
		if($settings->islivesite){
			$tags = '<meta name="robots" content="index, follow, all" />'.PHP_EOL;
			if(!empty($settings->googleverification)) $tags .= '<meta name="google-site-verification" content="'.$settings->googleverification.'" />'.PHP_EOL;
			if(!empty($settings->bingverification)) $tags .= '<meta name="msvalidate.01" content="'.$settings->bingverification.'" />'.PHP_EOL;
		}else{
			$tags .= '<meta name="robots" content="noindex, nofollow" />'.PHP_EOL;
		}
		return $tags;
	}
	#endregion
	
	// {{{ Editor information
	/**
	 * Creates an array of column IDs for which the specified Content Block is valid
	 * 
	 * This method is used by the CMS Content module to identify which columns a particular Content Block might be moved into.
	 *
	 * @param string $editor The class name of the Content Block
	 * @return array An array of the IDs of all valid columns
	 *
	 */
	function getValidColumnsForEditor($editor){
		$columns = array();
		foreach($this->columns as $column){
			if(in_array($editor,$column->contentblocks)){
				$columns[] = $column->id;
			}	
		}
		return $columns;
	}
	
	/**
	 * Builds an array of the names of all Content Blocks available for a whole page 
	 *
	 * @return array An array of the names of all the available Content Blocks
	 *
	 */
	function getEditorNames(){
		$editors = array();
		foreach($this->columns as $column){
			foreach($column->contentblocks as $editor){
				if(!in_array($editor,$editors)) $editors[] = $editor;
			}
		}
		return $editors;
	}
	#endregion
	
	// {{{ Get File
	
	/**
	 * Return the actual path to a requested file. This method will return the Skin version of the file if it exists, and the global version if it does not.
	 * 
	 * Files in the Skin should be filed under the Skin's particular folder, but should use the same folder structure as the original. 
	 * For example, if the Skin wants to provide it's own CSS file for the CMListItem Content Block, the developer will create a file in:
	 * /skins/MySkin/content/CMListItem/CMListItem.css
	 * When the system needs the CSS file for that Content Block, it will call this method, passing 'content/CMListItem/CMListItem.css' as the path.
	 * This method will first look for that path under the skin root, and finding that, will send that file instead of the original.
	 * 
	 * This method will return the path in one of four types:
	 *   -  site - the path is a fully qualified URL, including the siteroot
	 *   -  doc - the path is an absolute file path
	 *   -  web - the path is a relative URL, relative to the current page
	 *   -  all others - the path is relative to the site root.
	 *
	 * @param string $path The path to the file required, relative to the site root
	 * @param string $default The path to a default file of a different name, if neither version exists
	 * @param string $type The type of path to be returned - may be 'site', 'web', 'doc' or nothing
	 * @return string The path to the file
	 *
	 */
	public function getFile($path,$default='',$type=null){
		$checkpath = $this->path . '/' . $path;
		if(!file_exists($checkpath)){
			$checkpath = $GLOBALS['documentroot'].'/'.$path;
			if(!file_exists($checkpath)){
				if(empty($default)) return '';
				$checkpath = $GLOBALS['documentroot'].'/'.$default;
				if(!file_exists($checkpath)) return '';
			}	
		}
		// convert backslashes into forward slashes for string comparison.
		$checkpath = str_replace('\\','/',$checkpath);
		switch($type){
			case 'site':
				return str_replace($GLOBALS['documentroot'],$GLOBALS['siteroot'],$checkpath);
			case 'doc':
				return $checkpath;
			case 'web':
				return str_replace($GLOBALS['documentroot'].'/',$GLOBALS['webroot'],$checkpath);
			default:
				return str_replace($GLOBALS['documentroot'].'/','',$checkpath);
		}
	}
	#endregion
	
	// {{{ Get Content
	/**
	 * Constructs the entire content of the page.
	 * 
	 * This method should be called by the current processing page (in the case of core content, this is the index.php script).
	 * The PageObject object must be set before this method is called - that global object contains the main content of the page.
	 *
	 * @return string The complete HTML of the page
	 *
	 */
	public function getContent(){
		global $pageobject;
		if(is_array($pageobject)) $pageobject = (object)$pageobject;
		// prepare meta-data variables
		$this->prepareMetaTags();
		
		// load the template contents
		$templatepath = $this->path.'/'.$this->classname.'.tmp.html';
		libxml_use_internal_errors(true);
		$template = new DOMDocument('1.0','UTF-8');
		$templatecontent = file_get_contents($templatepath);
		
		// replace variables		
		$templatecontent = preg_replace_callback('/##(.*?)##/si',array($this,'replaceVariables'),$templatecontent);
		
		// replace entities
		$templatecontent = $this->replaceEntities($templatecontent);
		
		// process aloe: namespace tags
		if($template->loadHTML($templatecontent)){
			$this->processNode($template->documentElement,$this);
		}
		
		$templatecontent = $template->saveHTML();
		
		// restore entities
		$templatecontent = $this->restoreEntities($templatecontent);
		return $templatecontent;
	}
	#endregion
	
	// {{{ Get Fragment
	/**
	 * Use this method to construct an HTML Fragment from a given template file. 
	 * 
	 * Content blocks, Controls, Modules and any other extension that requires a bit of HTML layout can use this method in 
	 * conjunction with a template file to allow the Skin implementation to modify that HTML layout.
	 *
	 * @param string $path The path to the template file
	 * @param object $context The object to use for context. All refernces to $this will be directed at this object
	 * @return string The resulting HTML fragment
	 *
	 */
	public function getFragment($path,$context=null){
		$GLOBALS['context'] = $context;
		libxml_use_internal_errors(true);
		$template = new DOMDocument('1.0','UTF-8');
		$templatepath = $this->path . '/' . ltrim($path,'/');
		if(!file_exists($templatepath)) $templatepath = $GLOBALS['documentroot'] . '/' . ltrim($path,'/');
		if(!file_exists($templatepath)) return "ERROR: Template file $path not found";
		$templatecontent = file_get_contents($templatepath);
		
		// replace variables		
		$templatecontent = preg_replace_callback('/##(.*?)##/si',array($this,'replaceVariables'),$templatecontent);		
		// replace entities
		$templatecontent = $this->replaceEntities($templatecontent);
		
		if($template->loadHTML($templatecontent)){
			$this->processNode($template->documentElement,$context);
		}
		$fragment = $template->saveHTML();
		//strip containing tags
		$fragment = str_replace( array('<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">','<html>', '</html>', '<body>', '</body>'), '', $fragment);		
		// restore entities
		$fragment = $this->restoreEntities($fragment);
		return $fragment;
	}
	#endregion
	
	// {{{ Get Global CSS
	/**
	 * This method provides a list of the paths to all CSS files used in the global Skin. 
	 * 
	 * This method is used by the CMS Content Module to simulate the front-end when displaying content blocks, but could also be used by any other module.
	 *
	 * @return array The list of paths to CSS files
	 *
	 */
	public function getGlobalCSS(){
		global $pageobject;
		if(is_array($pageobject)) $pageobject = (object)$pageobject;
		// prepare meta-data variables
		$this->prepareMetaTags();
		$csspaths = array();
		
		// load the template contents
		$templatepath = $this->path.'/'.$this->classname.'.tmp.html';
		libxml_use_internal_errors(true);
		$template = new DOMDocument('1.0','UTF-8');
		$templatecontent = file_get_contents($templatepath);
		
		// replace variables		
		$templatecontent = preg_replace_callback('/##(.*?)##/si',array($this,'replaceVariables'),$templatecontent);
		
		// replace entities
		$templatecontent = $this->replaceEntities($templatecontent);
		
		// process aloe: namespace tags
		if($template->loadHTML($templatecontent)){
			$this->processNode($template->getElementsByTagName('head')->item(0));
			$links = $template->getElementsByTagName('link');
			if($links instanceof DOMNodeList){
				foreach($links as $link){
					if(strtolower(trim($link->getAttribute('rel'))) == 'stylesheet'){
						$path = trim($link->getAttribute('href'));
						$csspaths[] =  $this->restoreEntities($path);
					}
				}
			}
		}
		return $csspaths;
	}
	#endregion
	
	// {{{ Replace Variables
	/**
	 * This method is called with a preg_replace_callback method, and replaces the given variable with it's value
	 * 
	 * All variables must be contained by pairs of hashes (eg: ##variablename##)
	 * If the variable is also contained within braces (eg: ##{date('Y')}##), it will be evaluated as-is, without any further modification
	 * Otherwise, it will be evaluated as a single variable, and be moved into the Global context before being evaluated. In this case, the '$' is optional.
	 * Eg: ##myobject->param## will be translated into '$GLOBALS['myobject']->param' before being evaluated. 
	 * 
	 * All varaibles must eb available in the global scope, or they will not be available to this method.
	 *
	 * @param array $match The match to be replaced
	 * @return string The value of the variable
	 *
	 */
	protected function replaceVariables($match){
		global $documentroot, $webroot, $settings, $pageobject, $context, $metatags;
		$var = $match[1];
		if(substr($var,0,1)=='{'){
			$var = trim($var,'{}');
			$var = str_replace(array('$this-','$this['),array('$context-','$context['),$var);
		}else{
			if(substr($var,0,1)!='$') $var = '$'.$var;
			$var = str_replace(array('$this-','$this['),array('$context-','$context['),$var);
			if(substr($var,0,8) != '$GLOBALS'){
				$var = preg_replace('/\$([\w\d_]+)/si','$GLOBALS'."['$1']",$var);	
			}
		}
		eval('$res = '.$var.';');
		return $res;
	}
	#endregion
	
	// {{{ Process Node
	/**
	 * Process an individual DOMNode
	 * 
	 * If the node is one of the special Aloe tags, it will be handled here. 
	 * If not, the processChildNodes method will be called on it.
	 *
	 * @param DOMNode $node The node to be processed
	 * @return void
	 *
	 */
	protected function processNode($node,$context=null){
		global $documentroot, $webroot, $settings, $pageobject, $metatags;
		if($context===null) $context = $this;
		switch($node->nodeName){
			case 'settings':
				$var = $node->getAttribute('var');
				$text = isset($settings->$var)?$settings->$var:'';
				$text = $this->replaceEntities($text);
				$this->insertHTMLFragment($node,$text);
				break;
			case 'pageobject':
				$var = $node->getAttribute('var');
				$text = isset($pageobject->$var)?$pageobject->$var:'';
				$text = $this->replaceEntities($text);
				$this->insertHTMLFragment($node,$text);
				break;
			case 'seotags':
				$text = $this->getMetaTags();
				$text = $this->replaceEntities($text);
				$this->insertHTMLFragment($node,$text);
				break;
			case 'content':
				$content = isset($pageobject->pagecontent)?$pageobject->pagecontent:'';
				$content = $this->replaceEntities($content);
				$this->insertHTMLFragment($node,$content);
				break;
			case 'if':
				$cond = $node->getAttribute('condition');
				$cond = str_replace('$this->','$context->',$cond);
				$include = false;
				$evalres = @eval('$include = !!('.$cond.');return true;');
				$included = $include;
				$parent = $node->parentNode;
				$addnodes = array();
				$checknodes = $node->childNodes;
				if($checknodes instanceof DOMNodeList){
					foreach($checknodes as $child){
						switch($child->nodeName){
							case 'elseif':
								$cond = $child->getAttribute('condition');
								$cond = str_replace('$this->','$context->',$cond);
								if($include) $include = false;
								elseif(!$included){
									$evalres = @eval('$include = !!('.$cond.');return true;');
									$included = $include;
								}
								if(!$included && $include) $included = true;
								break;
							case 'else':
								if(!$included) $include = $included = true;
								else $include = false;
								break;
							default:
								if($include) $addnodes[] = $child;
								break;
						}	
					}
				}
				foreach($addnodes as $child){			
					$parent->insertBefore($child,$node);	
					$this->processNode($child,$context);	
				}
				$parent->removeChild($node);
				break;
			case 'call':
				$object = ltrim($node->getAttribute('object'),'$');
				$class = $node->getAttribute('class');
				$method = $node->getAttribute('method');
				// identify the parameters for the mmethod
				$pind = 1;
				$params = array();
				while($node->hasAttribute('param'.$pind)){
					$param = $node->getAttribute('param'.$pind);
					if($param == 'false') $param = false;
					if($param == 'true') $param = true;
					$params[] = $param;
					$pind++;
				}
				if($object=='this') $object = 'context';
				if(!empty($object) && isset($GLOBALS[$object])){
					$text = call_user_func_array(array($GLOBALS[$object],$method),$params);
				}elseif(class_exists($class)){
					$text = call_user_func_array(array($class,$method),$params);
				}elseif(!empty($object)){
					$text = call_user_func_array(array($object,$method),$params);
				}else{
					$text = call_user_func_array($method,$params);
				}
				$text = $this->replaceEntities($text);
				$this->insertHTMLFragment($node,$text);
				break;
			case 'contentcontainer':
				$newnode = $node->ownerDocument->createElement('div');
				if($node->hasAttributes()){
					foreach($node->attributes as $attribute){
						$newnode->setAttribute($attribute->nodeName,$attribute->nodeValue);
					}
				}
				$newnode->setAttribute('id','cbl_'.$GLOBALS['context']->id);
				if($context->foredit) $newnode->setAttribute('prop',$context->getBlockProperties());
				$newchildren = array();
				if($node->hasChildNodes()) foreach($node->childNodes as $childnode) $newchildren[] = $childnode;
				foreach($newchildren as $childnode)	$newnode->appendChild($childnode);
				$node->parentNode->replaceChild($newnode,$node);
				$this->processChildNodes($newnode,$context);
				break;
			case 'menu':
				$id = $node->getAttribute('id');
				$orientation = $node->getAttribute('orientation');
				$levels = $node->getAttribute('levels');
				$menu = new Menu($pageobject,$id,$orientation,$levels);
				$menucontent = $menu->drawMenu();
				$menucontent = $this->replaceEntities($menucontent);
				$this->insertHTMLFragment($node,$menucontent);
				break;
			default: 
				$this->processChildNodes($node,$context);
				break;
		}	
	}
	#endregion
	
	// {{{ Process Node support functions
	/**
	 * Loop through the child nodes of the supplied DOMNode, and process each of them.
	 *
	 * @param DOMNode $node The node to process
	 * @return void 
	 *
	 */
	private function processChildNodes($node,$context=null){
		if($node->hasChildNodes()){
			$childnodes = array();
			foreach($node->childNodes as $child) $childnodes[] = $child;
			foreach($childnodes as $child){
				$this->processNode($child,$context);	
			}
		}
	}
	
	/**
	 * Replace all entities found in the content with a long string.
	 * 
	 * The DOMDocument parser often has issues with entities, so we replace all entities with comlex strings before processing, 
	 * then restore them with restoreEntities afterwards
	 *
	 * @param string $content The content to be parsed
	 * @return string The content with all entities replaced
	 *
	 */
	private function replaceEntities($content){
		return preg_replace('/\&([^;\s]{1,6});/','--ENTITY**$1**ENTITY--',$content);
	}
	
	/**
	 * Restore all entities previously replaced with replaceEntities
	 *
	 * @param string $content The content containing the replaced entities
	 * @return string The content with the entities restored
	 *
	 */
	private function restoreEntities($content){
		return preg_replace('/\-\-ENTITY\*\*([^;\s]{1,6})\*\*ENTITY\-\-/','&$1;',$content);
	}
	
	/**
	 * Replaces the supplied node with the HTML fragment.
	 * 
	 * The supplied DOMNode is remove from the DOMDocument, and the supplied fragment is parsed and added into the DomDocument in it's place.
	 *
	 * @param DOMNode $node The node to be replaced
	 * @param string $fragment The HTML fragment that should replace the DOMNode
	 * @return void
	 *
	 */
	private function insertHTMLFragment($node,$fragment){
		if(substr(trim($fragment),0,1)!='<'){
			$newnode = $node->ownerDocument->createDocumentFragment();
			$newnode->appendXML($fragment);
			$node->parentNode->replaceChild($newnode,$node);			
		}else{
			$tempdoc = new DOMDocument('1.0','UTF-8');
			$fragment = '<div id="__TEMPCONTAINER">'.$fragment.'</div>';
			if($tempdoc->loadHTML($fragment)){
				$tempcontainer = $tempdoc->getElementsByTagName('body')->item(0)->childNodes->item(0);
				$addnodes = array();
				if($tempcontainer->hasChildNodes()){
					foreach($tempcontainer->childNodes as $child) $addnodes[] = $node->ownerDocument->importNode($child,true);
					foreach($addnodes as $child) $node->parentNode->insertBefore($child,$node);
				}
				$node->parentNode->removeChild($node);
			}
		}
	}
	#endregion
	
	// {{{ Static Fetch Skin functions
	/**
	 * Fetch a Skin object, given it's class name
	 *
	 * @param string $name The class name of the Skin
	 * @return Skin An instance of the relevant Skin class.
	 *
	 */
	public static function getSkin($name){
		$path = $GLOBALS['documentroot'].'/skins/'.$name.'/'.$name.'.skin.php';
		if(!file_exists($path)){
			throw new Exception('Skin not found');
		}
		require_once($path);
		$skin = eval('return new ' . $name . '();');
		return $skin;
	}
	
	/**
	 * Returns the current Skin for this request
	 *
	 * @return Skin The current Skin for this request
	 *
	 */
	public static function getCurrentSkin(){
		return $GLOBALS['skin'];
	}
	#endregion
}
?>