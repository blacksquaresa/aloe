<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');
require_once('AloeBlue.lib.php');
require_once('HTMPaths.lib.php');

class AloeBlue extends Skin{
	
	public $name = 'Aloe Blue';
	
	public function prepareSkin(){
		$this->contentwidth = 960;
		$this->prepareLayout('OneColumn');
		$this->prepareLayout('TwoColumnLeft');
		$this->prepareLayout('TwoColumnRight');
		$this->prepareLayout('TwoColumnEqual');
		$this->prepareLayout('ThreeColumn');
		$this->prepareLayout('CommonRight');
		$this->prepareColumn(1,'FULL',960,968,array('CMStandard','CMRawHTML','CMTable','CMLightboxGallery','CMPhotoGallery','CMSlideShow'));	
		$this->prepareColumn(2,'MAIN',630,643,array('CMStandard','CMEnquiry','CMRawHTML','CMListItem','CMLinkList','CMIndex','CMTable','CMLightboxGallery','CMPhotoGallery','CMSlideShow'));
		$this->prepareColumn(3,'LEFT',300,318,array('CMStandard','CMEnquiry','CMRawHTML','CMListItem','CMLinkList','CMIndex','CMTable','CMFeature','CMLightboxGallery'));
		$this->prepareColumn(4,'CENTRE',300,313,array('CMStandard','CMEnquiry','CMRawHTML','CMListItem','CMLinkList','CMIndex','CMTable','CMFeature','CMLightboxGallery'));
		$this->prepareColumn(5,'RIGHT',300,318,array('CMStandard','CMEnquiry','CMRawHTML','CMListItem','CMLinkList','CMIndex','CMTable','CMFeature','CMLightboxGallery'));
		$this->prepareColumn(6,'SINISTER',460,483,array('CMStandard','CMEnquiry','CMRawHTML','CMListItem','CMLinkList','CMIndex','CMTable','CMFeature','CMLightboxGallery'));
		$this->prepareColumn(7,'DEXTER',460,478,array('CMStandard','CMEnquiry','CMRawHTML','CMListItem','CMLinkList','CMIndex','CMTable','CMFeature','CMLightboxGallery'));
		
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
					CONTENTCOLUMN_FULL=>array('width'=>960,'height'=>640,'thumbwidth'=>80,'thumbheight'=>53),
					CONTENTCOLUMN_MAIN=>array('width'=>630,'height'=>420,'thumbwidth'=>80,'thumbheight'=>53),
					));
		
		$this->prepareSetting('CMSlideShow','sizematrix',array(
					CONTENTCOLUMN_FULL=>array('width'=>960,'height'=>array('short'=>320,'medium'=>480,'long'=>640)),
					CONTENTCOLUMN_MAIN=>array('width'=>630,'height'=>array('short'=>210,'medium'=>315,'long'=>420)),
					));
		
		$this->prepareSetting('CMListItem','sizematrix',array(
					CONTENTCOLUMN_MAIN=>array('portrait'=>170,'landscape'=>230),
					CONTENTCOLUMN_LEFT=>array('portrait'=>120,'landscape'=>300),
					CONTENTCOLUMN_CENTRE=>array('portrait'=>120,'landscape'=>300),
					CONTENTCOLUMN_RIGHT=>array('portrait'=>120,'landscape'=>300),
					CONTENTCOLUMN_SINISTER=>array('portrait'=>120,'landscape'=>150),
					CONTENTCOLUMN_DEXTER=>array('portrait'=>120,'landscape'=>150),
					));
		
		$this->prepareSetting('News','sizematrix',array('width'=>200,'height'=>130));
	}
	
}

?>