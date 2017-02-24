<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

require_once('Global.lib.php');

class CMSpacer extends ContentModule{
	public $content;
	
	public function __construct($row,$foredit){
		parent::__construct($row,$foredit);	
		$this->content = intval($row['content']);
	}	
	
	public function drawContentBlock(){
		if(empty($this->content) && $this->foredit) $this->content = 40;
		if($this->foredit) $colour = 'background-color: #eee;';
		$res .= '<div id="cbl_' . $this->id . '" prop="' . $this->getBlockProperties('CMSpacer') . '" class="CMSpacer_container" style="height:' . $this->content . 'px;'.$colour.'"></div>';
		return $res;
	}
}

?>