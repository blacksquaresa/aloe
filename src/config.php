<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

#General
$config['siteroot'] = 'http://localhost:12947';
$config['sitename'] = 'Aloe CMS '.ALOE_VERSION;
$config['usefriendlyurls'] = true;
$config['islivesite'] = false;
$config['fbsitelogo'] = ''; //Enter the path to the logo to be used when sharing on Facebook
$config['fbappid'] = ''; //Enter the Facebook App ID to be used by this site
$config['skin'] = 'AloeBlue';
#Database
$config['dbserver'] = 'terror';
$config['dbdatabase'] = 'blacksq_aloe';
$config['dbusername'] = 'blacksq_blacksq';
$config['dbpassword'] = '530z@1nt3rn3t';
#Email
$config['sourceemail'] = 'gareth@blacksquare.co.za';
#Valid file types for resource manager
$config['validfiletypes'] = array('jpg','jpeg','gif','png','bmp','doc','docx','xls','xlsx','ppt','pptx','pdf','mpeg','mpg','mpg4','avi','wmv','swf','mp3','zip');
$config['validimagetypes'] = array('jpg','jpeg','gif','png');
$config['validpagetypes'] = array('htm','html','php');
$config['validvideotypes'] = array('flv');
$config['validaudiotypes'] = array('mp3');
$config['displayimagetypes'] = array('jpg','jpeg','gif','png','bmp');
$config['displaydoctypes'] = array('doc','docx','xls','xlsx','ppt','pptx','pdf','css');
$config['displaymediatypes'] = array('mpeg','mpg','avi','wmv','swf','mp3');
#Content
$config['@contentpath'] = '/content/';

// User Type Constants
define("ADM_USERID",1);

// Content Page Constants
define("PAGE_MAINMENU",1);
define("PAGE_ORPHANMENU",2);
define("PAGE_HOME",3);
define("PAGE_DISCLAIMER",4);

?>