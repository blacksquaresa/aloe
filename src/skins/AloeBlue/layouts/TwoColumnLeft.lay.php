<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

class TwoColumnLeft extends Layout{
	
	public $name = 'Two Column';
	public $icon = 'TwoColumnLeft.png';
	public $priority = 2;	
	public $columnIds = array(
				CONTENTCOLUMN_MAIN,
				CONTENTCOLUMN_RIGHT
				);	
	
	protected function getEditableContent(){
		$res .= '<div id="contentlayout_'.$this->id.'">';
		$res .= '<table cellpadding="0" cellspacing="10" class="maincontent">';
		$res .= '<tr>
				<td valign="top" class="contentcolumn_'.CONTENTCOLUMN_MAIN.'" id="contentcolumn_'.$this->id.'_'.CONTENTCOLUMN_MAIN.'"></td>
				<td valign="top" class="contentcolumn_'.CONTENTCOLUMN_RIGHT.'" id="contentcolumn_'.$this->id.'_'.CONTENTCOLUMN_RIGHT.'"></td>
				</tr>';
		$res .= '</table>';	
		$res .= '</div>';
		return $res;
	}
	
	protected function getDisplayContent(){
		$main = $this->getColumnContent(CONTENTCOLUMN_MAIN,$this->foredit);
		$right = $this->getColumnContent(CONTENTCOLUMN_RIGHT,$this->foredit);
		$res .= '<div id="contentlayout_'.$this->id.'">';
		$res .= '<table cellpadding="0" cellspacing="0" class="maincontent">';
		$res .= '<tr>
				<td valign="top" class="contentcolumn_'.CONTENTCOLUMN_MAIN.'">'.$main.'</td>
				<td valign="top" class="contentcolumn_'.CONTENTCOLUMN_RIGHT.'">'.$right.'</td>
				</tr>';
		$res .= '</table>';
		$res .= '</div>';
		return $res;
	}
	
}

?>