<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

class CommonRight extends Layout{
	
	public $name = 'Two Column';
	public $icon = 'CommonRight.png';
	public $priority = 6;	
	public $columnIds = array(
		CONTENTCOLUMN_MAIN,
		CONTENTCOLUMN_RIGHT,
		CONTENTCOLUMN_LEFT,
		CONTENTCOLUMN_CENTRE
		);	
	
	protected function getEditableContent(){
		$res .= '<div id="contentlayout_'.$this->id.'">';
		$res .= '<table cellpadding="0" cellspacing="10" class="maincontent">';
		$res .= '<tr>
				<td valign="top" class="contentcolumn_'.CONTENTCOLUMN_MAIN.'" id="contentcolumn_'.$this->id.'_'.CONTENTCOLUMN_MAIN.'" colspan="2"></td>
				<td valign="top" class="contentcolumn_'.CONTENTCOLUMN_RIGHT.'" id="contentcolumn_'.$this->id.'_'.CONTENTCOLUMN_RIGHT.'" rowspan="2"></td>
				</tr>';
		$res .= '<tr>
				<td valign="top" class="contentcolumn_'.CONTENTCOLUMN_LEFT.'" id="contentcolumn_'.$this->id.'_'.CONTENTCOLUMN_LEFT.'"></td>
				<td valign="top" class="contentcolumn_'.CONTENTCOLUMN_CENTRE.'" id="contentcolumn_'.$this->id.'_'.CONTENTCOLUMN_CENTRE.'"></td>
				</tr>';
		$res .= '</table>';	
		$res .= '</div>';
		return $res;
	}
	
	protected function getDisplayContent(){
		$main = $this->getColumnContent(CONTENTCOLUMN_MAIN,$this->foredit);
		$right = $this->getColumnContent(CONTENTCOLUMN_RIGHT,$this->foredit);
		$left = $this->getColumnContent(CONTENTCOLUMN_LEFT,$this->foredit);
		$centre = $this->getColumnContent(CONTENTCOLUMN_CENTRE,$this->foredit);
		$res .= '<div id="contentlayout_'.$this->id.'">';
		$res .= '<table cellpadding="0" cellspacing="0" class="maincontent">';
		$res .= '<tr>
				<td valign="top" class="contentcolumn_'.CONTENTCOLUMN_MAIN.'" colspan="2">'.$main.'</td>
				<td valign="top" class="contentcolumn_'.CONTENTCOLUMN_RIGHT.'" rowspan="2">'.$right.'</td>
				</tr>';
		$res .= '<tr>
				<td valign="top" class="contentcolumn_'.CONTENTCOLUMN_LEFT.'">'.$left.'</td>
				<td valign="top" class="contentcolumn_'.CONTENTCOLUMN_CENTRE.'">'.$centre.'</td>
				</tr>';
		$res .= '</table>';
		$res .= '</div>';
		return $res;
	}
	
}

?>