<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

require_once('Global.lib.php');

class CMTable extends ContentModule{
	public $header;
	public $headerlead;
	public $rowstyles;
	public $columnstyles;
	public $columntypes;
	public $columnwidths;
	public $data;
	public $rowcount;
	public $columncount;
	public $tableborder;
	public $cellspacing;
	
	public function __construct($row,$foredit){
		parent::__construct($row,$foredit);		
		$this->tableborder = $GLOBALS['skin']->getSetting('CMTable','tableborder',2);
		$this->cellspacing = $GLOBALS['skin']->getSetting('CMTable','cellspacing',10);
		$this->header = $this->properties['header'];
		$this->headerlead = $this->properties['headerlead'];
		$this->hasheaderrow = in_array($this->headerlead,array('header','both'));
		$this->hasheadercolumn = in_array($this->headerlead,array('lead','both'));
		$this->rowstyles = unserialize(base64_decode($this->properties['rowstyles']));
		$this->columnstyles = unserialize(base64_decode($this->properties['columnstyles']));
		$this->columntypes = unserialize(base64_decode($this->properties['columntypes']));
		$this->columnwidths = unserialize(base64_decode($this->properties['columnwidths']));
		$this->data = unserialize(base64_decode($this->properties['data']));
		$this->rowcount = count($this->data);
		$this->columncount = count($this->data[0]);
	}	
	
	public function drawContentBlock(){
		$res .= '<div id="cbl_' . $this->id . '" prop="' . $this->getBlockProperties('CMTable') . '" class="CMTable_container">';
		
		if(!empty($this->header)) $res .= '<h2 class="CMTable_Header">' . $this->header . '</h2>';
		
		$res .= '<table cellpadding="0" cellspacing="0" class="CMTable_Table" '.($this->foredit? 'style="margin-left:0;"':'').'>';
		
		if($this->hasheaderrow){
			for($j=0;$j<$this->columncount;$j++){
				$res .= '<td class="CMTable_ColumnHead" style="text-align: '.$this->columnstyles[$j].';width: '.$this->columnwidths[$j].'px;vertical-align: '.$this->rowstyles[0].';">';
				$res .= $this->data[0][$j];
				$res .= '</td>';
			}
		}
		
		for($i=$this->hasheaderrow?1:0;$i<$this->rowcount;$i++){				
			$res .= '<tr class="' . ($i%2==0?'CMTable_Row':'CMTable_Altrow') . '">';
			if($this->hasheadercolumn){
				$res .= '<td class="CMTable_RowHead" style="text-align: '.$this->columnstyles[0].';width: '.$this->columnwidths[0].'px;vertical-align: '.$this->rowstyles[$i].';">';
				$res .= $this->data[$i][0];
				$res .= '</td>';
			}
			for($j=$this->hasheadercolumn?1:0;$j<$this->columncount;$j++){
				$res .= '<td class="CMTable_Cell" style="text-align: '.$this->columnstyles[$j].';width: '.$this->columnwidths[$j].'px;vertical-align: '.$this->rowstyles[$i].';">';
				switch($this->columntypes[$j]){
					case 'html':
						$res .= '<div style="width: '.$this->columnwidths[$j].'px; overflow: hidden;">'.$this->data[$i][$j].'</div>';
						break;
					case 'icon':
						$res .= '<img src="'.$GLOBALS['webroot'].'content/CMTable/icons/'.$this->data[$i][$j].'" />';
						break;
					default:
						$res .= $this->data[$i][$j];
						break;
				}
				$res .= '</td>';
			}
			$res .= '</tr>';
		}
		$res .= '</table>';	
		$res .= '</div>';
		return $res;
	}
}

?>