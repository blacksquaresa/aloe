<?php
$GLOBALS['authcode'] = 'Acojc5ttj 24t0qtqv#';
require_once('lib/Global.lib.php');
header("HTTP/1.0 404 Not Found");

$pageobject = array();
$pageobject['id'] = PAGE_HOME;
$pageobject['pagecontent'] = <<<HTML
<table cellpadding="0" cellspacing="0" class="maincontent" id="maincontenttable" style="margin-top: 18px;">
	<tr>
		<td valign="top" colspan="2" id="contentcolumn_1">
			<h1>Oops - Page Not Found</h1>
			<div class="error">The page you are looking for cannot be found. Please try again.</div>
			<a href="/index.htm" class="arrowlink">return to the home page</a>
		</td>
	</tr>
</table>
HTML;
?>
<?= $GLOBALS['skin']->getContent(); ?>