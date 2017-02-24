<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');
require_once('Global.lib.php');
require_once('Text.lib.php');

/**
 * This class provides an easy way to create advanced input controls to collect different types of information.
 * 
 * Use this class to draw the control initially, and also to process the value afterwards. This will clean the input and ensure no invalid information is included 
 * (although it will not check for SQL validity - values that need to be inserted into a database must be cleaned for SQL security separately). Some of the more complex 
 * controls will be made up of several $_REQUEST variables and will need to be compiled into a single value (eg: CSS controls, such as a CSS border control, 
 * which might separately collect width, style and colour, and compile that into a valid CSS string for the value).
 * 
 * The following information types are supported:
 * - hidden: no value is displayed or returned.
 * - readonly: the information is not editable, and is returned as-is.
 * - text: a standard, single-line input box is used. No HTML is allowed.
 * - option: a drop-down list of options is presented. The options are built out of the data value, from a comma-separated list. Each item may be a pipe-separated set of value:label.
 * - float: a floating point number is allowed.
 * - integer: a whole number is allowed.
 * - image: The path to an image in resources is expected, and a popup selector is included.
 * - document: The path to a document in resources is expected, and a popup selector is included.
 * - file: The path to a file in resources is expected, and a popup selector is included.
 * - link: A URL is expected, and a popup selector is included
 * - colour: A hex colour reference (with the #) is expected, and a popup selector is included
 * - multiline: A multiline text input box is used. The number of rows is indicated with the data value. No HTML is allowed.
 * - array: An array of strings is collected using a multiline text box, with one string per line. The resulting value is formatted as an array.
 *
 * @package Controls
 * @subpackage Input Control
 * @since 2.4
 */
class InputControl{
	
	// {{{ Declarations
	/**
	 * The CSS file to use for all controls. By default this uses the CSS files that comes with the control, but you may change that for front-end usage.
	 *
	 * @var string 
	 *
	 */
	public $cssfile = 'controls/input/inputcontrol.css';
	
	/**
	 * The sum of the padding and border values for the left and right sides of a standard textbox. Used to calculate the width of more complex controls
	 *
	 * @var int 
	 *
	 */
	public $textboxpadding = 12;
	
	/**
	 * The path to the icon to use for the file selection controls, including image and document controls
	 *
	 * @var string 
	 *
	 */
	public $fileselecticon = 'images/admin/common/select.png';
	
	/**
	 * The path to the icon to use for link selection controls
	 *
	 * @var string 
	 *
	 */
	public $linkselecticon = 'images/admin/common/select.png';
	
	/**
	 * The width to use for all controls, if not specifically set in the draw method.
	 *
	 * @var int 
	 *
	 */
	public $width = 300;
	#endregion
	
	// {{{ Constructor
	/**
	 * Constructor - set default values if needed
	 *
	 * @param int $width The default width of all controls
	 * @param string $cssfile The CSS file to use for more advanced controls
	 * @param string $fileselecticon The path to the icon to use for file selection controls
	 * @param string $linkselecticon The path to the icon to use for link selection controls
	 * @param int $textboxpadding The sum of the border and padding for the left and right sides of a standard textbox control
	 * @return void 
	 *
	 */
	function __construct($width=null,$cssfile=null,$fileselecticon=null,$linkselecticon=null,$textboxpadding=null){
		if($width !== null) $this->width = $width;
		if($cssfile !== null) $this->cssfile = $cssfile;
		if($fileselecticon !== null) $this->fileselecticon = $fileselecticon;
		if($linkselecticon !== null) $this->linkselecticon = $linkselecticon;
		if($textboxpadding !== null) $this->textboxpadding = $textboxpadding;
	}
	#endregion
	
	// {{{ Draw Standard Controls
	/**
	 * Produce the HTML to display and edit a global, page or layer setting.
	 *
	 * @param string $arrayname The name of the containing variable. All settings will be grouped into the same containing array.
	 * @param string $name The name of this particular setting
	 * @param string $type The type of setting. This defines the way the setting control is constructed
	 * @param string $data Data to use to customise the construction of the control. This might provide options, a number of rows or range limits, depending on the type
	 * @param string $value The current value of the variable
	 * @param int $width The width of the space containing the control. If no width is supplied, the class default is used.
	 * @return string The HTML to use to display the control.
	 *
	 */
	public function drawStandardControl($arrayname, $name, $type, $data, $value, $width=null){
		if($width===null) $width = $this->width;
		switch($type){
			case 'hidden':
				break;
			case 'readonly':
				$res .= '<div>' . $value . '</div>';
				break;
			case 'option':
				$options = explode(',',$data);
				$res .= '<select name="'.$arrayname.'['.$name.']" id="'.$arrayname.'_'.$name.'" style="width: '.$width.'px">';
				foreach($options as $option){
					list($val,$key)	= explode('|',$option,2);
					if(empty($key)) $key = $val;
					$res .= '<option value="'.$val.'"'.($val==$value?' selected':'').'>'.$key.'</option>';
				}
				$res .= '</select>';
				break;
			case 'float':
				$boxwidth = min($width-$this->textboxpadding,100);
				$res .= '<input type="text" name="'.$arrayname.'['.$name.']" id="'.$arrayname.'_'.$name.'" style="width: '.($boxwidth).'px;" value="'.clean($value).'" onkeyup="return NumbersOnly(this,event,false,false)" />';
				break;
			case 'integer':
				$boxwidth = min($width-$this->textboxpadding,100);
				$res .= '<input type="text" name="'.$arrayname.'['.$name.']" id="'.$arrayname.'_'.$name.'" style="width: '.($boxwidth).'px;" value="'.clean($value).'" onkeyup="return NumbersOnly(this,event,true,false)" />';
				break;
			case 'image':
				$boxwidth = $width - $this->textboxpadding - 20;
				$res .= '<input type="text" name="'.$arrayname.'['.$name.']" id="'.$arrayname.'_'.$name.'" style="width: '.($boxwidth).'px;" value="'.clean($value).'" />';
				$res .= '<a href="javascript:PopupManager.showImageSelector(null,\''.$arrayname.'_'.$name.'\');"><img align="top" src="../../images/admin/common/select.png"></a>';
				break;
			case 'document':
				$boxwidth = $width - $this->textboxpadding - 20;
				$res .= '<input type="text" name="'.$arrayname.'['.$name.']" id="'.$arrayname.'_'.$name.'" style="width: '.($boxwidth).'px;" value="'.clean($value).'" />';
				$res .= '<a href="javascript:PopupManager.showDocSelector(null,\''.$arrayname.'_'.$name.'\');"><img align="top" src="../../images/admin/common/select.png"></a>';
				break;
			case 'file':
				$boxwidth = $width - $this->textboxpadding - 20;
				$res .= '<input type="text" name="'.$arrayname.'['.$name.']" id="'.$arrayname.'_'.$name.'" style="width: '.($boxwidth).'px;" value="'.clean($value).'" />';
				$res .= '<a href="javascript:PopupManager.showResourceManager(null,\''.$arrayname.'_'.$name.'\');"><img align="top" src="../../images/admin/common/select.png"></a>';
				break;
			case 'link':
				$boxwidth = $width - $this->textboxpadding - 20;
				$res .= '<input type="text" name="'.$arrayname.'['.$name.']" id="'.$arrayname.'_'.$name.'" style="width: '.($boxwidth).'px;" value="'.clean($value).'" />';
				$res .= '<a href="javascript:PopupManager.showLinkSelector(null,\''.$arrayname.'_'.$name.'\');"><img align="top" src="../../images/admin/common/select.png"></a>';
				break;
			case 'colour':
				$boxwidth = min($width-$this->textboxpadding-20,80);
				$res .= '<input type="text" name="'.$arrayname.'['.$name.']" id="'.$arrayname.'_'.$name.'" style="width: '.($boxwidth).'px;" value="'.clean($value).'" />';
				$res .= '<a href="javascript:PopupManager.showColourSelector(null,\''.$arrayname.'_'.$name.'\');"><img align="top" src="../../images/admin/common/select.png"></a>';
				break;
			case 'multiline':
				$data = (int)$data;
				$rows = empty($data)?4:$data;
				$res .= '<textarea rows="'.$rows.'" name="'.$arrayname.'['.$name.']" id="'.$arrayname.'_'.$name.'" style="width: '.($width-$this->textboxpadding).'px;">'.$value.'</textarea>';
				break;	
			case 'array':
				if(!is_array($value)) $value = json_decode($value);
				if(is_array($value))$value = implode("\r\n",$value);
				$data = (int)$data;
				$rows = empty($data)?4:$data;
				$res .= '<textarea rows="'.$rows.'" name="'.$arrayname.'['.$name.']" id="'.$arrayname.'_'.$name.'" style="width: '.($width-$this->textboxpadding).'px;">'.$value.'</textarea>';
				break;	
			default:
				$res .= '<input type="text" name="'.$arrayname.'['.$name.']" id="'.$arrayname.'_'.$name.'" style="width: '.($width-$this->textboxpadding).'px;" value="'.clean($value).'" />';
				break;	
		}
		return $res;
	}
	#endregion
	
	// {{{ Process Controls	
	/**
	 * Processes the result from a control created with this class, and returns the result. 
	 * 
	 * In most cases this is simply the value in the $_REQUEST variable, but in some cases this must be translated or compiled.
	 *
	 * @param array $array The array containing the values for all the settings being managed in this batch
	 * @param string $name The name of this setting
	 * @param string $type The type of setting
	 * @return string The value of the setting, translated and compiled.
	 *
	 */
	
	public static function processControl($array, $name, $type){
		if(is_array($array)) $array = (object)$array;
		switch($type){
			case 'array':
				$value = $array->$name;
				cleanSingleUserInput($value);
				$value = str_replace("\r","",$value);
				$value = explode("\n",$value);
				$value = array_values(array_filter($value,trim));
				$value = json_encode($value);
				break;
			default:
				$value = $array->$name;
				cleanSingleUserInput($value);
				break;	
		}
		return $value;
	}
	#endregion
}

?>