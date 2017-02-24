<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
$path = dirname(dirname(dirname(__FILE__)));
require_once($path.'/lib/Global.lib.php');
$usermanager->authenticateUser();
require_once($path.'/lib/Install.lib.php');
global $message;

addGlobalSetting('sitename','text','','Testing Site','Testing','Site Name','Enter the name of the site',$message) or die($message);
addGlobalSetting('menualignment','option','left|Left,right|Right','left','Testing','Menu Alignmnent','Choose which side of the content to place the menu',$message) or die($message);
addGlobalSetting('floatvalue','float','','0.5','Testing','Float Value','Enter a number between 0 and 1',$message) or die($message);
addGlobalSetting('intvalue','integer','','50','Testing','Integer Value','Enter a number between 1 and 100',$message) or die($message);
addGlobalSetting('logo','image','','skins/Testing/images/logo.png','Testing','Logo','Select the logo image',$message) or die($message);
addGlobalSetting('document','document','','','Testing','More Details','Select a document to download for more details',$message) or die($message);
addGlobalSetting('file','file','','','Testing','Random Download','Select a file to download',$message) or die($message);
addGlobalSetting('link','link','','','Testing','Further Reading','Select a website to visit for more details',$message) or die($message);
addGlobalSetting('headerback','colour','','#eeeeee','Testing','Header Background Colour','Select the colour for the background of the header row',$message) or die($message);
addGlobalSetting('description','multiline','','This is a default description string. Change me now.','Testing','Description','Select a description for the whole site',$message) or die($message);
addGlobalSetting('authors','array','','["Gareth","Tom","Sam","Michele"]','Testing','Authors','Enter the list of names of all those who helped develop this site',$message) or die($message);

addPageSetting('pagename','text','','Testing Page','Page Name','Enter the name of the page',$message) or die($message);
addPageSetting('menualignment','option','top|Top,bottom|Bottom','top','Menu Alignmnent','Choose whether to place the menu top or bottom',$message) or die($message);
addPageSetting('floatvalue','float','','0.5','Float Value','Enter a number between 0 and 1',$message) or die($message);
addPageSetting('intvalue','integer','','50','Integer Value','Enter a number between 1 and 100',$message) or die($message);
addPageSetting('logo','image','','skins/Testing/images/pagelogo.png','Logo','Select the logo image for the page',$message) or die($message);
addPageSetting('document','document','','','More Details','Select a document to download for more details',$message) or die($message);
addPageSetting('file','file','','','Random Download','Select a file to download',$message) or die($message);
addPageSetting('link','link','','','Further Reading','Select a website to visit for more details',$message) or die($message);
addPageSetting('headertext','colour','','#222222','Header Text Colour','Select the colour for the text in the header row',$message) or die($message);
addPageSetting('description','multiline','','This is a default description string. Change me now.','Description','Select a description for the page',$message) or die($message);
addPageSetting('authors','array','','["Gareth"]','Authors','Enter the list of names of all those who helped develop this page',$message) or die($message);

addLayoutSetting('layoutname','text','','Testing Layout','Layout Name','Enter the name of the layout',$message) or die($message);
addLayoutSetting('style','option','contemporary,desert|Desert Classic,midnight|Midnight Inversion,','contemporary','Style','Select the visual style to use for this layout',$message) or die($message);
addLayoutSetting('floatvalue','float','','0.5','Float Value','Enter a number between 0 and 1',$message) or die($message);
addLayoutSetting('intvalue','integer','','50','Integer Value','Enter a number between 1 and 100',$message) or die($message);
addLayoutSetting('background','image','','','Background Image','Select the image to use for the background',$message) or die($message);
addLayoutSetting('document','document','','','More Details','Select a document to download for more details',$message) or die($message);
addLayoutSetting('file','file','','','Random Download','Select a file to download',$message) or die($message);
addLayoutSetting('link','link','','','Further Reading','Select a website to visit for more details',$message) or die($message);
addLayoutSetting('headerback','colour','','#eeeeee','Header Background Colour','Select the colour for the background of the header row for this layer',$message) or die($message);
addLayoutSetting('description','multiline','','This is a default description string. Change me now.','Description','Select a description for the page',$message) or die($message);
addLayoutSetting('authors','array','','["Gareth"]','Authors','Enter the list of names of all those who helped develop this page',$message) or die($message);

?>