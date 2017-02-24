tinyMCEPopup.requireLangPack();

var MinImageDialog = {
	init : function() {
		var f = document.forms[0], nl = f.elements, ed = tinyMCEPopup.editor, dom = ed.dom, n = ed.selection.getNode(),el,img,cap,div;
		
		tinyMCEPopup.resizeToInnerSize();
		el= dom.getParent(ed.selection.getNode(), 'table');
		if(el){
			if((el.parentNode).nodeName=="DIV")div= el.parentNode;
		}
		if(el){
			img = el.getElementsByTagName('img')[0];
			cap = el.rows[1].innerHTML;
			f.min_src.value = img.src;
			f.insert.value = "Update";
			f.min_caption.value = dom.getAttrib(img, 'alt');
			if(div)	var styles = dom.getAttrib(div, 'style');
			else var styles = dom.getAttrib(el, 'style');
			var floatPattern =/\s*.*float\s*:\s*(left|right)\s*;\s*.*/gi;
			f.min_align.value= styles.replace(floatPattern,"$1");
			var elem = div || el;
			f.min_padding_top.value = parseInt(elem.style.marginTop) || 0;
			f.min_padding_bottom.value = parseInt(elem.style.marginBottom) || 0;
			f.min_padding_left.value = parseInt(elem.style.marginLeft) || 0;
			f.min_padding_right.value = parseInt(elem.style.marginRight) || 0;
			this.showPreviewImage(img.src,1);
		}
				
		//Setting up browse button
		document.getElementById('srcbrowser').innerHTML = getBrowserHTML('srcbrowser','min_src','image','theme_advanced_image');
		if (isVisible('srcbrowser'))
			document.getElementById('min_src').style.width = '260px';
		f.min_src.focus();
	},
	remove: function(){
		var f = document.forms[0];
		f.min_src.value="";
		this.insert();
	},
	
	insert : function() {
		var f = document.forms[0], t= this, ed = tinyMCEPopup.editor,dom=ed.dom,el,div;
		
		if (f.min_src.value === '') {
			el = dom.getParent(ed.selection.getNode(),'table');
			if((el.parentNode).nodeName=="DIV")div = el.parentNode;
			if (el) {
				dom.remove(el);
				ed.execCommand('mceRepaint');
			}
			if(div){
				dom.remove(div);
				ed.execCommand('mceRepaint');
			}

			tinyMCEPopup.close();
			return;
		}
		t.insertAndClose();
	},
	
	insertAndClose : function() {
		var ed = tinyMCEPopup.editor,dom=ed.dom, f = document.forms[0], nl = f.elements, v, args = {}, el,html,marginVals,div,img;

		tinyMCEPopup.restoreSelection();

		// Fixes crash in Safari
		if (tinymce.isWebKit)
			ed.getWin().focus();

			//style : "float:"+nl.min_align.value+";",
		tinymce.extend(args, {
			src : nl.min_src.value,
			alt : nl.min_caption.value,
			title : nl.min_caption.value
		});

		el = dom.getParent(ed.selection.getNode(), 'table');
		if(el){
			if((el.parentNode).nodeName=="DIV")div = el.parentNode;
		}
		img = this.preloadImg;
		nl.min_padding_top.value = nl.min_padding_top.value || 0;
		nl.min_padding_bottom.value = nl.min_padding_bottom.value || 0;
		nl.min_padding_left.value = nl.min_padding_left.value || 0;
		nl.min_padding_right.value = nl.min_padding_right.value || 0;
		marginVals = "margin-top:"+nl.min_padding_top.value+"px;";
		marginVals += "margin-bottom:"+nl.min_padding_bottom.value+"px;";
		marginVals += "margin-left:"+nl.min_padding_left.value+"px;";
		marginVals += "margin-right:"+nl.min_padding_right.value+"px;";
		if(div && el && el.nodeName == 'TABLE') {
			dom.setAttribs(div, {style:"width:"+img.width+"px;float:"+nl.min_align.value+";"+marginVals});
			dom.setAttribs((el.getElementsByTagName('img')[0]),args);
			el.getElementsByTagName('td')[1].innerHTML = args.alt;
		} else {
			if(el && el.nodeName=="TABLE"){
				dom.remove(el);
				ed.execCommand('mceRepaint');
			}
			html =  '<div';
			html += makeAttrib('style',';width:'+img.width+'px; margin-right:'+nl.min_padding_right.value+'px;margin-left:'+nl.min_padding_left.value+'px;margin-bottom:'+nl.min_padding_bottom.value+'px;margin-top:'+nl.min_padding_top.value+'px;float:'+nl.min_align.value);
			html += '><table cellpadding="0" cellspacing="0">'
			html += '<tbody><tr><td><img id="__mce_tmp" /></td></tr>';
			html += '<tr><td class="imagecaption">'+nl.min_caption.value+'</td></tr>';
			html += '</tbody></table></div>';
			ed.execCommand('mceInsertRawHTML', false, html, {skip_undo : 1});
			ed.dom.setAttribs('__mce_tmp', args);
			ed.dom.setAttrib('__mce_tmp', 'id', '');
			ed.undoManager.add();
		}

		tinyMCEPopup.close();
	},

	updateImageData : function(img, st) {
		this.preloadImg = img;
	},
	
	resetImageData : function() {
	},
	
	showPreviewImage : function(u, st) {
		if (!u) {
			tinyMCEPopup.dom.setHTML('min_prev', '');
			return;
		}
		
		u = tinyMCEPopup.editor.documentBaseURI.toAbsolute(u);

		if (!st)
			tinyMCEPopup.dom.setHTML('min_prev', '<img id="minpreviewImg" src="' + u + '" border="0" onload="MinImageDialog.updateImageData(this);" onerror="MinImageDialog.resetImageData();" />');
		else
			tinyMCEPopup.dom.setHTML('min_prev', '<img id="minpreviewImg" src="' + u + '" border="0" onload="MinImageDialog.updateImageData(this, 1);" />');
	}
};

tinyMCEPopup.onInit.add(MinImageDialog.init, MinImageDialog);

function makeAttrib(attrib, value) {
	var formObj = document.forms[0];
	var valueElm = formObj.elements[attrib];

	if (typeof(value) == "undefined" || value == null) {
		value = "";

		if (valueElm)
			value = valueElm.value;
	}

	if (value == "")
		return "";

	// XML encode it
	value = value.replace(/&/g, '&amp;');
	value = value.replace(/\"/g, '&quot;');
	value = value.replace(/</g, '&lt;');
	value = value.replace(/>/g, '&gt;');

	return ' ' + attrib + '="' + value + '"';
}