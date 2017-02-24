<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');
require_once('Testing.lib.php');
require_once('HTMPaths.lib.php');

class Testing extends Skin{
	
	public $name = 'Testing';
	
	public function prepareSkin(){
		$this->contentwidth = 720;
		$this->prepareLayout('OneColumn');
		$this->prepareLayout('TwoColumnLeft');
		$this->prepareLayout('TwoColumnRight');
		$this->prepareLayout('TwoColumnEqual');
		$this->prepareLayout('ThreeColumn');
		$this->prepareLayout('CommonRight');
		$this->prepareColumn(1,'FULL',720,728,array('CMStandard','CMContact','CMEnquiry','CMRawHTML','CMSpacer','CMListItem','CMLinkList','CMIndex','CMTable','CMPhotoGallery','CMLightboxGallery','CMSlideShow'));	
		$this->prepareColumn(2,'MAIN',470,483,array('CMStandard','CMContact','CMEnquiry','CMRawHTML','CMSpacer','CMListItem','CMLinkList','CMIndex','CMTable','CMLightboxGallery','CMPhotoGallery'));
		$this->prepareColumn(3,'LEFT',220,233,array('CMStandard','CMEnquiry','CMRawHTML','CMSpacer','CMLinkList','CMIndex','CMTable','CMFeature','CMLightboxGallery'));
		$this->prepareColumn(4,'CENTRE',220,238,array('CMStandard','CMEnquiry','CMRawHTML','CMSpacer','CMLinkList','CMIndex','CMTable','CMFeature','CMLightboxGallery'));
		$this->prepareColumn(5,'RIGHT',220,233,array('CMStandard','CMEnquiry','CMRawHTML','CMSpacer','CMLinkList','CMIndex','CMTable','CMFeature','CMLightboxGallery'));
		$this->prepareColumn(6,'SINISTER',345,358,array('CMStandard','CMEnquiry','CMRawHTML','CMSpacer','CMLinkList','CMIndex','CMTable','CMFeature','CMLightboxGallery'));
		$this->prepareColumn(7,'DEXTER',345,358,array('CMStandard','CMEnquiry','CMRawHTML','CMSpacer','CMLinkList','CMIndex','CMTable','CMFeature','CMLightboxGallery'));
		
		$this->prepareContentBlock('CMNewsFeed',array(CONTENTCOLUMN_LEFT,CONTENTCOLUMN_CENTRE,CONTENTCOLUMN_RIGHT));
		$this->prepareContentBlock('CMRSSFeed',array(CONTENTCOLUMN_LEFT,CONTENTCOLUMN_CENTRE,CONTENTCOLUMN_RIGHT));
		
		$this->prepareSetting('CMLightboxGallery','sizematrix',array(
					CONTENTCOLUMN_FULL=>array('width'=>80,'height'=>53),
					CONTENTCOLUMN_MAIN=>array('width'=>80,'height'=>53),
					CONTENTCOLUMN_LEFT=>array('width'=>50,'height'=>33),
					CONTENTCOLUMN_CENTRE=>array('width'=>50,'height'=>33),
					CONTENTCOLUMN_RIGHT=>array('width'=>50,'height'=>33),
					CONTENTCOLUMN_SINISTER=>array('width'=>75,'height'=>50),
					CONTENTCOLUMN_DEXTER=>array('width'=>75,'height'=>50),
					));
		
		$this->prepareSetting('CMPhotoGallery','sizematrix',array(
					CONTENTCOLUMN_FULL=>array('width'=>720,'height'=>480,'thumbwidth'=>80,'thumbheight'=>53),
					CONTENTCOLUMN_MAIN=>array('width'=>470,'height'=>313,'thumbwidth'=>80,'thumbheight'=>53),
					));
		
		$this->prepareSetting('CMSlideShow','sizematrix',array(
					CONTENTCOLUMN_FULL=>array('width'=>720,'height'=>array('short'=>330,'medium'=>480,'long'=>640)),
					CONTENTCOLUMN_MAIN=>array('width'=>470,'height'=>array('short'=>216,'medium'=>313,'long'=>420)),
					));
	}
	
}

?>