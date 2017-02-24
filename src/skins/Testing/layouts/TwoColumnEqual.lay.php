<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

class TwoColumnEqual extends Layout{
	
	public $name = 'Two Column';
	public $icon = 'TwoColumnEqual.png';
	public $priority = 2;	
	public $columnIds = array(
		CONTENTCOLUMN_SINISTER,
		CONTENTCOLUMN_DEXTER
		);	
	
	protected function getEditableContent(){
		$res .= '<div id="contentlayout_'.$this->id.'" class="'.$this->custom['style'].'">';
		$res .= '<table cellpadding="0" cellspacing="10" class="maincontent">';
		$res .= '<tr>
				<td valign="top" class="contentcolumn_'.CONTENTCOLUMN_SINISTER.'" id="contentcolumn_'.$this->id.'_'.CONTENTCOLUMN_SINISTER.'"></td>
				<td valign="top" class="contentcolumn_'.CONTENTCOLUMN_DEXTER.'" id="contentcolumn_'.$this->id.'_'.CONTENTCOLUMN_DEXTER.'"></td>
				</tr>';
		$res .= '</table>';	
		$res .= '</div>';
		return $res;
	}
	
	protected function getDisplayContent(){
		$left = $this->getColumnContent(CONTENTCOLUMN_SINISTER,$this->foredit);
		$right = $this->getColumnContent(CONTENTCOLUMN_DEXTER,$this->foredit);
		$res .= '<div id="contentlayout_'.$this->id.'" class="'.$this->custom['style'].'"';
		if(!empty($this->custom['background'])) $res .= ' style="background-image: url(\''.$GLOBALS['webroot'].$this->custom['background'].'\'); background-position: bottom center; background-repeat: no-repeat;"';
		$res .= '>';
		$res .= Testing_drawLayoutSettings($this);
		$res .= '<table cellpadding="0" cellspacing="0" class="maincontent">';
		$res .= '<tr>
				<td valign="top" class="contentcolumn_'.CONTENTCOLUMN_SINISTER.'">'.$left.'</td>
				<td valign="top" class="contentcolumn_'.CONTENTCOLUMN_DEXTER.'">'.$right.'</td>
				</tr>';
		$res .= '</table>';
		$res .= '</div>';
		return $res;
	}
	
}

?>