<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

require_once('Global.lib.php');

class CMStandard extends ContentModule{
	public $content;
	
	public function __construct($row,$foredit=false){
		parent::__construct($row,$foredit);	
		$this->content = $row['content'];
	}	
	
	public function drawContentBlock(){
		if(empty($this->content)) $this->content = '&nbsp;';
		$res .= '<div id="cbl_' . $this->id . '"';
		if($this->foredit) $res .= ' prop="' . $this->getBlockProperties() . '"';
		$res .= ' class="CMStandard_container">' . $this->content . '</div>';
		return $res;
	}
}
?>