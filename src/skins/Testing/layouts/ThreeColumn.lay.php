<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

class ThreeColumn extends Layout{
	
	public $name = 'Two Column';
	public $icon = 'ThreeColumn.png';
	public $priority = 4;	
	public $columnIds = array(
		CONTENTCOLUMN_LEFT,
		CONTENTCOLUMN_CENTRE,
		CONTENTCOLUMN_RIGHT
		);	
	
	protected function getEditableContent(){
		$res .= '<div id="contentlayout_'.$this->id.'" class="'.$this->custom['style'].'">';
		$res .= '<table cellpadding="0" cellspacing="10" class="maincontent">';
		$res .= '<tr>
				<td valign="top" class="contentcolumn_'.CONTENTCOLUMN_LEFT.'" id="contentcolumn_'.$this->id.'_'.CONTENTCOLUMN_LEFT.'"></td>
				<td valign="top" class="contentcolumn_'.CONTENTCOLUMN_CENTRE.'" id="contentcolumn_'.$this->id.'_'.CONTENTCOLUMN_CENTRE.'"></td>
				<td valign="top" class="contentcolumn_'.CONTENTCOLUMN_RIGHT.'" id="contentcolumn_'.$this->id.'_'.CONTENTCOLUMN_RIGHT.'"></td>
				</tr>';
		$res .= '</table>';	
		$res .= '</div>';
		return $res;
	}
	
	protected function getDisplayContent(){
		$left = $this->getColumnContent(CONTENTCOLUMN_LEFT,$this->foredit);
		$centre = $this->getColumnContent(CONTENTCOLUMN_CENTRE,$this->foredit);
		$right = $this->getColumnContent(CONTENTCOLUMN_RIGHT,$this->foredit);
		$res .= '<div id="contentlayout_'.$this->id.'" class="'.$this->custom['style'].'"';
		if(!empty($this->custom['background'])) $res .= ' style="background-image: url(\''.$GLOBALS['webroot'].$this->custom['background'].'\'); background-position: bottom center; background-repeat: no-repeat;"';
		$res .= '>';
		$res .= Testing_drawLayoutSettings($this);
		$res .= '<table cellpadding="0" cellspacing="0" class="maincontent">';
		$res .= '<tr>
				<td valign="top" class="contentcolumn_'.CONTENTCOLUMN_LEFT.'">'.$left.'</td>
				<td valign="top" class="contentcolumn_'.CONTENTCOLUMN_CENTRE.'">'.$centre.'</td>
				<td valign="top" class="contentcolumn_'.CONTENTCOLUMN_RIGHT.'">'.$right.'</td>
				</tr>';
		$res .= '</table>';
		$res .= '</div>';
		return $res;
	}
	
}

?>