<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

class OneColumn extends Layout{
	
	public $name = 'One Column';
	public $icon = 'OneColumn.png';
	public $priority = 1;
	public $columnIds = array(
		CONTENTCOLUMN_FULL
		);	
	
	protected function getEditableContent(){
		$res .= '<div id="contentlayout_'.$this->id.'">';
		$res .= '<table cellpadding="0" cellspacing="10" class="maincontent">';
		$res .= '<tr>
				<td valign="top" class="contentcolumn_'.CONTENTCOLUMN_FULL.'" id="contentcolumn_'.$this->id.'_'.CONTENTCOLUMN_FULL.'"></td>
				</tr>';
		$res .= '</table>';	
		$res .= '</div>';
		return $res;
	}
	
	protected function getDisplayContent(){
		$main = $this->getColumnContent(CONTENTCOLUMN_FULL,$this->foredit);
		$res .= '<div id="contentlayout_'.$this->id.'">';
		$res .= '<table cellpadding="0" cellspacing="0" class="maincontent">';
		$res .= '<tr>
				<td valign="top" class="contentcolumn_'.CONTENTCOLUMN_FULL.'">
				'.$main.'
				</td>
				</tr>';
		$res .= '</table>';
		$res .= '</div>';
		return $res;
	}
	
}

?>