<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

require_once('Global.lib.php');
require_once('Text.lib.php');

class CMContact extends ContentModule{
	public $content;
	public $title;
	public $email;
	public $phone;
	public $fax;
	public $physical;
	public $postal;
	public $map;
	
	public function __construct($row,$foredit=false){
		parent::__construct($row,$foredit);	
		$this->content = $row['content'];
		$this->email = $this->properties['email'];
		$this->phone = $this->properties['phone'];
		$this->fax = $this->properties['fax'];
		$this->cell = $this->properties['cell'];
		$this->physical = $this->properties['physical'];
		$this->postal = $this->properties['postal'];
		$this->map = $this->properties['map'];
	}
	
	public function drawContentBlock(){		
		$this->heading = $this->content;
		$this->obfuscatedemail = obfuscateEmail($this->email);
		$res = $GLOBALS['skin']->getFragment('/content/CMContact/CMContact.tmp.html',$this);
		return $res;
	}
	
	function drawMap(){
		if(substr($this->map,0,1) == '<'){
			$width = $this->columnid==CONTENTCOLUMN_MAIN?470:($this->columnid==CONTENTCOLUMN_FULL?720:230);
			$map = preg_replace('|(width[=:"]+)\d+|si','${1}'.$width,$this->map);
			$res .= '<div class="CMContact_map">'.$map.'</div>';
		}elseif(substr($this->map,0,4) == 'http') $res .= '<a href="'.$this->map.'" target="_blank" class="arrowlink">Find us on Google.</a>';
		elseif(in_array(pathinfo($this->map,PATHINFO_EXTENSION),$GLOBALS['settings']->validimagetypes)) $res .= '<img src="'.$GLOBALS['webroot'].$this->map.'" />';
		else $res .= '<a href="http://mapof.it/' . rawurlencode(preg_replace('|[^\w\d,-]+|',' ',$this->map)).'" target="_blank" class="arrowlink">Find us on Google.</a>';
		return $res;
	}
	
}

?>