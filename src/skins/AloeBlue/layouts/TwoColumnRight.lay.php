<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

class TwoColumnRight extends Layout{
	
	public $name = 'Two Column';
	public $icon = 'TwoColumnRight.png';
	public $priority = 3;	
	public $columnIds = array(
		CONTENTCOLUMN_MAIN,
		CONTENTCOLUMN_LEFT
		);	
	
	protected function getEditableContent(){
		$res .= '<div id="contentlayout_'.$this->id.'">';
		$res .= '<table cellpadding="0" cellspacing="10" class="maincontent">';
		$res .= '<tr>
				<td valign="top" class="contentcolumn_'.CONTENTCOLUMN_LEFT.'" id="contentcolumn_'.$this->id.'_'.CONTENTCOLUMN_LEFT.'"></td>
				<td valign="top" class="contentcolumn_'.CONTENTCOLUMN_MAIN.'" id="contentcolumn_'.$this->id.'_'.CONTENTCOLUMN_MAIN.'"></td>
				</tr>';
		$res .= '</table>';	
		$res .= '</div>';
		return $res;
	}
	
	protected function getDisplayContent(){
		$left = $this->getColumnContent(CONTENTCOLUMN_LEFT,$this->foredit);
		$main = $this->getColumnContent(CONTENTCOLUMN_MAIN,$this->foredit);
		$res .= '<div id="contentlayout_'.$this->id.'">';
		$res .= '<table cellpadding="0" cellspacing="0" class="maincontent">';
		$res .= '<tr>
				<td valign="top" class="contentcolumn_'.CONTENTCOLUMN_LEFT.'">'.$left.'</td>
				<td valign="top" class="contentcolumn_'.CONTENTCOLUMN_MAIN.'">'.$main.'</td>
				</tr>';
		$res .= '</table>';
		$res .= '</div>';
		return $res;
	}
	
}

?>