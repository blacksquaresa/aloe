<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

require_once('Global.lib.php');

class CMRawHTML extends ContentModule{
	public $content;
	
	public function __construct($row,$foredit=false){
		parent::__construct($row,$foredit);	
		$this->content = $row['content'];
	}	
	
	public function drawContentBlock(){
		if(empty($this->content)) $this->content = '&nbsp;';
		$res .= '<div id="cbl_' . $this->id . '"';
		if($this->foredit) $res .= ' prop="' . $this->getBlockProperties() . '"';
		$res .= ' class="CMRawHTML_container">';
		if($this->foredit){
			$res .= '<div class="CMRawHTML_display">Raw HTML Block</div>';
			if(stripos($this->content,'<iframe')===false && stripos($this->content,'<object')===false){
				$res .= $this->content;
			}else{
				$res .= '<div class="CMRawHTML_display">'.htmlentities($this->content,null,'utf-8').'</div>';
			}
		}else{
			$res .= $this->content;	
		}	
		$res .= '</div>';
		return $res;
	}
}

?>