<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');
require_once('Global.lib.php');

class CMLinkList extends ContentModule{
	public $header;
	public $texts;
	public $urls;
	
	public function __construct($row,$foredit){
		parent::__construct($row,$foredit);	
		$this->header = $this->properties['header'];
		$this->texts = unserialize(base64_decode($this->properties['texts']));
		$this->urls = unserialize(base64_decode($this->properties['urls']));
	}	
	
	public function drawContentBlock(){
		$res = $GLOBALS['skin']->getFragment('/content/CMLinkList/CMLinkList.tmp.html',$this);
		return $res;
	}
	
	public function drawLinks(){
		if(is_array($this->urls) && count($this->urls)){
			for($i=0;$i<count($this->urls);$i++){
				$text = $this->texts[$i];
				$url = $this->urls[$i];
				if(!empty($text) && !empty($url)){
					$target = getLinkTarget($this->link);
					$res .= '<a href="' . $url . '" '.$target.' class="CMLinkList_Link arrowlink">' . $text . '</a>';	
				}	
			}
		}
		return $res;
	}
}

?>