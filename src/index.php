<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('lib/Global.lib.php');
require_once('lib/Content.lib.php');
require_once('lib/HTMPaths.lib.php');
$pageid = (!is_numeric($_REQUEST['id']))?((!is_numeric($GLOBALS['id']))?PAGE_HOME:$GLOBALS['id']):$_REQUEST['id'];
$pageobject = Page::GetNewPage($pageid);
// Handle special pages. Allows HTMPath system to treat special pages just like any other page, and use the 'index.php' page name.
if($pageobject->type=='special' && file_exists($GLOBALS['documentroot'].'/'.$pageobject->specialpage)){
	include($GLOBALS['documentroot'].'/'.$pageobject->specialpage);
	exit;	
}
$pageobject->populateContent();
?>
<?= $GLOBALS['skin']->getContent(); ?>