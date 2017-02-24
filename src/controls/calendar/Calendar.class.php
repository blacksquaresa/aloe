<?php

class Calendar{
	var $index = 1;
	var $name;
	var $value;
	var $callback;
	var $shownone = true;
	var $speed = 10;
	var $step = 10;
	var $defaultText = '(click to select)';
	var $positionOffset;
	var $positionOffsetNS;
	var $leftimage = '';
	var $rightimage = '';
	var $noneimage = '';
	var $mindate = '01 June 2008';
	
	function Calendar($name,$value=null){
		$this->name = $name;
		$this->positionOffset = new PositionOffset(-1,1,-1,0);
		$this->positionOffsetNS = new PositionOffset(0,-1);
		$this->value = $value;
		$this->leftimage = $GLOBALS['webroot'].'images/admin/common/left.png';
		$this->rightimage = $GLOBALS['webroot'].'images/admin/common/right.png';
		$this->noneimage = $GLOBALS['webroot'].'images/admin/common/delete.png';
	}
	
	function drawCalendar(){
		if(!isset($GLOBALS['CalendarScriptLoaded'])){
			$ret .= '<script language="javascript" src="'.$GLOBALS['webroot'].'controls/calendar/calendar.js"></script>';
			$ret .= '<link href="'.$GLOBALS['webroot'].'controls/calendar/calendar.css" rel="stylesheet" type="text/css" />';
			$GLOBALS['CalendarScriptLoaded'] = true;
		}
		if(empty($this->value)) $this->value = $this->defaultText;
		if(is_numeric($this->value)) $this->value = date('d F Y',$this->value);
		$divname = 'cal_' . $this->name;
		$objname = 'cl_' . $this->name;
		$ret .= '<div id="' . $divname . '" class="cldiv" style="position: absolute; display: none; overflow: hidden;"></div>';
		$ret .= '<input type="text" name="' . $this->name . '" id="' . $this->name . '" class="datefield" value="' . $this->value . '" onclick="' . $objname . '.open();" autocomplete="off" />';
		$ret .= '
				<script language="javascript">
				<!--
				var ' . $objname . ';
				' . $objname . ' = new Calendar(\'' . $divname . '\',\'' . $this->name . '\',\'' . $this->callback . '\');
				' . $objname . '.shownone = ' . ($this->shownone?'true':'false') . ';
				' . $objname . '.defaulttext = \'' . $this->defaultText . '\';
				' . $objname . '.leftimage = \'' . $this->leftimage . '\';
				' . $objname . '.rightimage = \'' . $this->rightimage . '\';
				' . $objname . '.noneimage = \'' . $this->noneimage . '\';
				' . $objname . '.mindate = \'' . (empty($this->mindate)?'0':date('d F Y',$this->mindate)) . '\';
				' . $objname . '.positionOffset.setOffset(' . $this->positionOffset->top . ',' . $this->positionOffset->left . ',' . $this->positionOffset->width . ',' . $this->positionOffset->height . ');
				' . $objname . '.positionOffsetNS.setOffset(' . $this->positionOffsetNS->top . ',' . $this->positionOffsetNS->left . ',' . $this->positionOffsetNS->width . ',' . $this->positionOffsetNS->height . ');
				' . $objname . '.build();
				// -->
				</script>';
		return $ret;	
	}
}
	
class PositionOffset{
	var $top = 0;
	var $left = 0;
	var $width = 0;
	var $height = 0;
	
	function PositionOffset($top=0, $left=0, $width=0, $height=0){
		$this->top = $top;
		$this->left = $left;
		$this->width = $width;
		$this->height = $height;	
	}
	
	function setOffset($top, $left, $width, $height){
		$this->top = $top;
		$this->left = $left;
		$this->width = $width;
		$this->height = $height;
	}
}

?>