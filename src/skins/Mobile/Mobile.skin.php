<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');
require_once('Mobile.lib.php');
require_once('HTMPaths.lib.php');

class Mobile extends Skin{
	
	public $name = 'Mobile';
	
	public function prepareSkin(){
		$this->contentwidth = 300;
		$this->prepareLayout('OneColumn');
		$this->prepareLayout('TwoColumnEqual');
		$this->prepareLayout('ThreeColumn');
		$this->prepareColumn(1,'FULL',300,313,array('CMStandard','CMRawHTML','CMSpacer','CMListItem','CMLinkList','CMIndex','CMTable','CMLightboxGallery','CMSlideShow'));	
		$this->prepareColumn(2,'SINISTER',145,153,array('CMStandard','CMRawHTML','CMSpacer','CMLinkList','CMIndex','CMTable','CMFeature','CMLightboxGallery'));
		$this->prepareColumn(3,'DEXTER',145,153,array('CMStandard','CMRawHTML','CMSpacer','CMLinkList','CMIndex','CMTable','CMFeature','CMLightboxGallery'));
		$this->prepareColumn(4,'LEFT',90,98,array('CMStandard','CMRawHTML','CMFeature'));
		$this->prepareColumn(5,'CENTRE',90,98,array('CMStandard','CMRawHTML','CMFeature'));
		$this->prepareColumn(6,'RIGHT',90,98,array('CMStandard','CMRawHTML','CMFeature'));
		
		$this->prepareSetting('CMLightboxGallery','sizematrix',array(
					CONTENTCOLUMN_FULL=>array('width'=>40,'height'=>40),
					CONTENTCOLUMN_SINISTER=>array('width'=>40,'height'=>40),
					CONTENTCOLUMN_DEXTER=>array('width'=>40,'height'=>40),
					CONTENTCOLUMN_LEFT=>array('width'=>40,'height'=>40),
					CONTENTCOLUMN_CENTRE=>array('width'=>40,'height'=>40),
					CONTENTCOLUMN_RIGHT=>array('width'=>40,'height'=>40),
					));
		
		$this->prepareSetting('CMSlideShow','sizematrix',array(
					CONTENTCOLUMN_FULL=>array('width'=>600,'height'=>array('short'=>200,'medium'=>400,'long'=>600)),
					CONTENTCOLUMN_MAIN=>array('width'=>600,'height'=>array('short'=>200,'medium'=>400,'long'=>600)),
					));
	}
	
}

?>