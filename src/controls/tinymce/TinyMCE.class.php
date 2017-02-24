<?php
if($GLOBALS['authcode']!='Acojc5ttj 24t0qtqv#') die('Hacking Attempt');

class TinyMCE{
	
	var $tinymcepath;
	var $cssfilepath;	
	var $jsfilepath;	
	var $loadonload = false;
	
	function TinyMCE(){
		
		// Set default filepaths.
		if(isset($GLOBALS['webroot'])){
			$this->webroot = $GLOBALS['webroot'];
			$this->tinymcepath = $this->webroot.'controls/tinymce/jscripts/tiny_mce/tiny_mce.js';
			$this->cssfilepath = $this->webroot.'controls/tinymce/editor.css.php';
			$this->jsfilepath = $this->webroot.'controls/tinymce/filebrowser.js';
		}
	}
	
	function Init($style = 'full',$width=null){
		switch($style){
			case 'mini':				
				$buttons = '
						plugins : "advlink,autolink,inlinepopups,paste,typekit",
						theme_advanced_buttons1 : "bold,italic,link,unlink,removeformat,code",
						theme_advanced_buttons2 : "",
						theme_advanced_buttons3 : "",
						theme_advanced_buttons4 : "",';
				break;
			case 'reduced':				
				$buttons = '
						plugins : "minimage,autolink,advlink,advimage,advlist,inlinepopups,paste,visualchars,nonbreaking,typekit",
						theme_advanced_buttons1 : "bold,italic,justifyleft,justifycenter,justifyright,justifyfull,formatselect,styleselect,link,unlink,anchor,image",
						theme_advanced_buttons2 : "hr,sub,sup,|,charmap,nonbreaking,minimage,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,removeformat,code",
						theme_advanced_buttons3 : "",
						theme_advanced_buttons4 : "",';
				break;
			case 'compact':				
				$buttons = '
						plugins : "minimage,autolink,advlink,advimage,advlist,inlinepopups,paste,visualchars,nonbreaking,typekit",
						theme_advanced_buttons1 : "bold,italic,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist",
						theme_advanced_buttons2 : "formatselect,styleselect",
						theme_advanced_buttons3 : "link,unlink,anchor,|,image,minimage,|,undo,redo,removeformat,code",
						theme_advanced_buttons4 : "charmap,nonbreaking,|,hr,sub,sup,|,outdent,indent,blockquote",';
				break;
			case 'short':				
				$buttons = '
						plugins : "minimage,autolink,advlink,advimage,advlist,inlinepopups,paste,visualchars,nonbreaking,typekit",
						theme_advanced_buttons1 : "bold,italic,justifyleft,justifycenter,justifyright,justifyfull,|,link,unlink,anchor,image,minimage",
						theme_advanced_buttons2 : "formatselect,styleselect,hr,sub,sup",
						theme_advanced_buttons3 : "bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,removeformat,code,|,charmap,nonbreaking",
						theme_advanced_buttons4 : "",';
				break;
			case 'long':				
				$buttons = '
						plugins : "minimage,autolink,advlink,advimage,advlist,inlinepopups,paste,visualchars,nonbreaking,typekit",
						theme_advanced_buttons1 : "bold,italic,justifyleft,justifycenter,justifyright,justifyfull,formatselect,styleselect,bullist,numlist,|,outdent,indent,blockquote,|,hr,sub,sup,|,link,unlink,anchor,image,minimage,|,charmap,nonbreaking,|,undo,redo,removeformat,code",
						theme_advanced_buttons2 : "",
						theme_advanced_buttons3 : "",
						theme_advanced_buttons4 : "",';
				break;
			default:
				$buttons = '
						plugins : "minimage,autolink,advlink,advimage,advlist,inlinepopups,paste,visualchars,nonbreaking,typekit",
						theme_advanced_buttons1 : "bold,italic,justifyleft,justifycenter,justifyright,justifyfull,formatselect,styleselect,link,unlink,anchor,image",
						theme_advanced_buttons2 : "hr,sub,sup,|,charmap,nonbreaking,minimage,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,removeformat,code",
						theme_advanced_buttons3 : "",
						theme_advanced_buttons4 : "",';
				break;
		}
		$script = '
<!-- TinyMCE -->
<script type="text/javascript" src="' . $this->tinymcepath . '"></script>
<script type="text/javascript" src="' . $this->jsfilepath . '"></script>
<script language="javascript" type="text/javascript">
	popwins = new Array();
	popelems = new Array();
	' . ($this->loadonload?'onload = function(){':'') . '
		tinyMCE.init({
			mode : "textareas",
			theme : "advanced",
			editor_selector : "mceEditor",' . $buttons . '
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_statusbar_location : "none",
			theme_advanced_blockformats : "p,h1,h2,h3,h4,h5,h6,blockquote",
			content_css : "' . $this->cssfilepath . '",
			relative_urls : false,
			document_base_url : "/",
			file_browser_callback : "fileBrowserCallBack",
			theme_advanced_source_editor_height : 400,
			theme_advanced_source_editor_width : '.(max(500,$width)).',
			accessibility_warnings : false,
			nonbreaking_force_tab : true,
			apply_source_formatting : true,
			paste_text_sticky : true,
			paste_convert_middot_lists: true,
			paste_text_linebreaktype : "p",
			typekit_kitid : "' . $GLOBALS['settings']->typekit . '",
			setup : function(ed) {
				ed.onInit.add(function(ed) {
					ed.pasteAsPlainText = true;
				});
			}';
		if(!empty($width)) $script .= ',
			oninit : TinyMCE_SetEditorBodyWidth';
			$script .= '
		});
	';
	if($this->loadonload) $script .= '};';
	if(!empty($width)) $script .= '
	function TinyMCE_SetEditorBodyWidth(){
		var editor = tinyMCE.get(\'content\');
		if(editor) editor.dom.setStyle(\'tinymce\', \'width\', '.$width.'+\'px\');
	}';
	$script .= '
</script>
<!-- /TinyMCE -->
';
		echo $script;
	}
	
}

?>