<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

require_once('Global.lib.php');
require_once('Text.lib.php');

class CMEnquiry extends ContentModule{
	public $heading;
	public $target;
	
	public function __construct($row,$foredit=false){
		parent::__construct($row,$foredit);	
		$this->heading = $this->properties['heading'];
		$this->target = $this->properties['target'];
	}
	
	public function drawContentBlock(){
		$res = $GLOBALS['skin']->getFragment('/content/CMEnquiry/CMEnquiry.tmp.html',$this);
		#submit button event handlers
		if(!$this->foredit && empty($GLOBALS['CMEnquiry_Scriptloaded'])){
			$res .= '<script>var ErrorMsg="";</script>
					<script src="/js/validation.js"></script>
					<script src="/content/CMEnquiry/CMEnquiry_Client.js"></script>';
			$GLOBALS['CMEnquiry_Scriptloaded'] = true;
		}
		
		return $res;
	}	
}

?>