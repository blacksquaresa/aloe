/*
For non-admin implementations, set the following variables before calling the Popups.js script:
popupcontainerpaddingwidth: the width of the left and right spacing outside the popup content.
popupcontainerpaddingheight: the height of the top and bottom padding, including the popup header
popupborderwidth: the width of the border outside the popup window, in pixels
popupcsspath: The path to the CSS file to use for popup windows
*/
var popupcsspath,popupcontainerpaddingwidth,popupcontainerpaddingheight,popupborderwidth;
var PopupManager = {
	'popups' : {},
	'popupcontainerpaddingwidth' : 18,
	'popupcontainerpaddingheight' : 49,
	'popupborderwidth' : 2,
	'zIndex' : 1000000,
	'init' : function(){
		var link = document.createElement('link');
		link.href = popupcsspath?popupcsspath:'/css/popups.css';
		link.rel = "stylesheet";
		link.type = "text/css";
		document.body.insertBefore(link,document.body.firstChild);
		if(popupcontainerpaddingwidth) this.popupcontainerpaddingwidth = popupcontainerpaddingwidth;
		if(popupcontainerpaddingheight) this.popupcontainerpaddingheight = popupcontainerpaddingheight;
		if(popupborderwidth) this.popupborderwidth = popupborderwidth;
	},
	'showInvisible' : function(zindex){
		if(!this.popups['_invisible']){
			var pop = new popup('_invisible','','100%','100%','div');
			pop.container.className = 'popup_invisible';
			this.popups['_invisible'] = pop;
		}
		if(zindex) this.popups['_invisible'].div.style.zIndex = zindex;
		this.showAsync('_invisible');
	},
	'hideInvisible' : function(){
		this.hideAsync('_invisible');
	},
	'showDisabled' : function(){
		if(!this.popups['_disabled']){
			var pop = new popup('_disabled','','100%','100%','div');
			pop.container.className = 'popup_disabled';
			pop.zIndexes = [];
			this.popups['_disabled'] = pop;
		}
		this.popups['_disabled'].zIndexes.push(this.popups['_disabled'].div.style.zIndex);
		this.showAsync('_disabled');
	},
	'hideDisabled' : function(){
		if(this.popups['_disabled'].zIndexes.length <= 1){
			this.popups['_disabled'].zIndexes = [];
			this.hideAsync('_disabled');
		}else{
			this.popups['_disabled'].div.style.zIndex = this.popups['_disabled'].zIndexes.pop();
		}
	},
	'DisabledIsShowing' : function(){ return this.popups['_disabled'].zIndexes.length > 0; },
	'showLoading' : function(){
		if(!this.popups['_loading']){
			var pop = new popup('_loading','','100%','100%','div');
			pop.container.className = 'popup_loading';
			this.popups['_loading'] = pop;
		}
		this.showDisabled();
		this.showAsync('_loading');
	},
	'hideLoading' : function(){
		if(this.popups['_loading']) this.hideAsync('_loading');
		this.hideDisabled();
	},
	'showCompleted' : function(){
		if(!this.popups['_completed']){
			var pop = new popup('_completed','','100%','100%','div');
			pop.container.className = 'popup_completed';
			this.popups['_completed'] = pop;
		}
		this.showDisabled();
		this.showAsync('_completed');
		setTimeout('PopupManager.hideCompleted()',700);
	},
	'hideCompleted' : function(){
		if(this.popups['_completed']) this.hideAsync('_completed');
		this.hideDisabled();
	},
	'showError' : function(error,title){
		if(!this.popups['_error']){
			var pop = new popup('_error','Error','400','114','div',null,'disabled');
			pop.body.className = 'popup_error';
			pop.container.innerHTML = '<table width="380"><tr><td style="padding: 20px;" valign="top"><img src="/images/popups/error.gif"></td><td><div id="popup__error_text" class="popup_error_text">&nbsp;</div></td></tr><tr><td colspan="2" align="right"><input type="button" class="edt_button" value="Close" onclick="PopupManager.hideError();"></td></tr></table>';
			this.popups['_error'] = pop;
		}
		this.showDisabled();
		var replacements = {'popup__error_text':error};
		if(title) replacements['popup__error_title'] = title;
		this.popups['_error'].Show(replacements);
	},
	'hideError' : function(){
		this.popups['_error'].Hide();
		this.hideDisabled();
	},
	'showMessage' : function(message,title){
		if(!this.popups['_message']){
			var pop = new popup('_message','Message','400','114','div',null,'disabled');
			pop.body.className = 'popup_message';
			pop.container.innerHTML = '<table width="380"><tr><td style="padding: 20px;" valign="top"><img src="/images/popups/message.png"></td><td><div id="popup__message_text" class="popup_message_text">&nbsp;</div></td></tr><tr><td colspan="2" align="right"><input type="button" class="edt_button" value="Close" onclick="PopupManager.hideMessage();"></td></tr></table>';
			this.popups['_message'] = pop;
		}
		this.showDisabled();
		var replacements = {'popup__message_text':message};
		if(title) replacements['popup__message_title'] = title;
		this.popups['_message'].Show(replacements);
	},
	'hideMessage' : function(){
		this.popups['_message'].Hide();
		this.hideDisabled();
	},
	'showImageSelector' : function(selected,sourceid,owner){
		this.showResourceManager(selected,sourceid,owner,'images')
	},
	'hideImageSelector' : function(){
		this.hideResourceManager();
	},
	'showDocSelector' : function(selected,sourceid,owner){
		this.showResourceManager(selected,sourceid,owner,'docs')
	},
	'hideDocSelector' : function(){
		this.hideResourceManager();
	},
	'showResourceManager' : function(selected,sourceid,owner,type){
		if(!this.popups['_resourceselect']){
			this.popups['_resourceselect'] = new popup('_resourceselect','Resource Selector','976','380','iframe','/popups/ResourceManager.pop.php','loading');
		}
		this.showLoading();
		if(!selected){
			if(sourceid.value){
				selected = sourceid.value;
			}else{
				var doc = owner?owner:document;
				if(typeof(doc) == 'string') doc = eval(doc);
				if(typeof(doc) != 'function'){
					var obj = doc.getElementById(sourceid);
					if(obj) selected = obj.value;
				}
			} 
		}
		var replacements = {'selected':selected,'sourceid':sourceid,'owner':owner};
		if(type) replacements.type = type;
		this.ShowRefresh('_resourceselect',replacements);
	},
	'hideResourceManager' : function(type){
		this.hidePopup('_resourceselect');
		this.hideLoading();
	},
	'showLinkSelector' : function(selected,sourceid,owner){
		if(!this.popups['_linkselect']){
			this.popups['_linkselect'] = new popup('_linkselect','Link Selector','500','410','iframe','/popups/LinkSelector.pop.php','loading');
		}
		this.showLoading();
		if(!selected){
			if(sourceid.value){
				selected = sourceid.value;
			}else{
				var doc = owner?owner:document;
				if(typeof(doc) == 'string') doc = eval(doc);
				if(typeof(doc) != 'function'){
					var obj = doc.getElementById(sourceid);
					if(obj) selected = obj.value;
				}
			} 
		}
		var replacements = {'selected':selected,'sourceid':sourceid,'owner':owner};
		this.ShowRefreshWithLoad('_linkselect',replacements);
	},
	'hideLinkSelector' : function(){
		this.popups['_linkselect'].Hide();
		this.hideLoading();
	},
	'showColourSelector' : function(selected,sourceid,owner,pageid){
		if(!this.popups['_colourselect']){
			this.popups['_colourselect'] = new popup('_colourselect','Colour Picker','540','510','iframe','/popups/ColourPicker.pop.php','loading');
		}
		this.showLoading();
		if(!selected){
			if(sourceid.value){
				selected = sourceid.value;
			}else{
				var doc = owner?owner:document;
				if(typeof(doc) == 'string') doc = eval(doc);
				if(typeof(doc) != 'function'){
					var obj = doc.getElementById(sourceid);
					if(obj) selected = obj.value;
				}
			} 
		}
		var replacements = {'selected':selected,'sourceid':sourceid,'owner':owner,'pageid':pageid};
		this.ShowRefresh('_colourselect',replacements);
	},
	'hideColourSelector' : function(){
		this.popups['_colourselect'].Hide();
		this.hideLoading();
	},
	'addPopup' : function(name, title, width, height, type, path, hide){
		this.popups[name] = new popup(name, title, width, height, type, path, hide);
	},
	'createOrFetchPopup' : function(name, title, width, height, type, path, hide){
		if(!this.popups[name]){
			this.popups[name] = new popup(name, title, width, height, type, path, hide);
		}
		return this.popups[name];
	},
	'showPopup' : function(name,replacements){
		if(this.popups[name]) this.popups[name].Show(replacements);
	},
	'ShowRefresh' : function(name,replacements){
		if(this.popups[name]) this.popups[name].ShowRefresh(replacements);
	},
	'ShowRefreshWithLoad' : function(name,replacements){
		if(this.popups[name]) this.popups[name].ShowRefreshWithLoad(replacements);
	},
	'hidePopup' : function(name){
		if(this.popups[name]) this.popups[name].Hide();
	},
	'setSize' : function(name,width,height){
		if(this.popups[name]) this.popups[name].SetSize(width,height);
	},
	'setTitle' : function(name,title){
		if(this.popups[name]) this.popups[name].SetTitle(title);
	},
	'prepare' : function(name,width,height,title){
		if(this.popups[name]) this.popups[name].Prepare(width,height,title);
	},
	'showAsync' : function(name){
		if(isie){
			setTimeout("PopupManager.popups['"+name+"'].Show(null,"+(++this.zIndex)+");",0);
		}else{
			this.popups[name].Show();
		}
	},
	'hideAsync' : function(name){
		if(isie){
			setTimeout("PopupManager.popups['"+name+"'].Hide();",0);
		}else{
			this.popups[name].Hide();
		}
	},
	'isElementInPopup' : function(elem){
		if(elem instanceof String){
			elem = document.getElementById(elem);
		}
		if(!elem) return false;
		var node = elem;
		while(node && node.className != 'popup_popup'){
			node = node.parentNode;
		}
		if(node){
			var name = node.id.substr(6);
			return this.popups[name];
		}else if(elem.ownerDocument){
			if(elem.ownerDocument.defaultView&&elem.ownerDocument.defaultView.frameElement){
				return this.isElementInPopup(elem.ownerDocument.defaultView.frameElement);
			}else if(elem.ownerDocument.parentWindow&&elem.ownerDocument.parentWindow.frameElement){
				return this.isElementInPopup(elem.ownerDocument.parentWindow.frameElement);
			}
		}
		return false;
	}
};
PopupManager.init();

function popup(name, title, width, height, type, path, hide){
	this.name = name;
	this.title = title;
	this.type = type;
	this.path = path;
	this.doc = document;
	
	this.div = document.createElement('div');
	this.div.id = 'popup_' + name;
	this.div.style.position = 'fixed';
	this.div.style.display = 'none';
	this.div.className = 'popup_popup';
	if(width == '100%' || height == '100%'){
		this.div.style.width = this.width = width;
		this.div.style.height = this.height = height;
		document.body.insertBefore(this.div,document.body.firstChild);
		this.container = this.div;
		this.top = 0;
		this.left = 0;
	}else{
		// create the heading
		this.head = document.createElement('div');
		var closelink = 'javascript:PopupManager.hidePopup(\''+name+'\');';
		switch(hide){
			case 'disabled':
				closelink += 'PopupManager.hideDisabled();';
				break;
			case 'loading':
				closelink += 'PopupManager.hideLoading();';
				break;
			case 'invisible':
				closelink += 'PopupManager.hideInvisible();';
				break;
		}
		this.head.innerHTML = '<div style="float: right"><a href="'+closelink+'"><img src="/images/popups/close.png" alt="Close this window" /></a></div><div id="popup_' + name + '_title">' + title + '</div>';
		this.head.className = 'popup_head';
		this.div.appendChild(this.head);
		
		// create the body of the popup
		this.body = document.createElement('div');
		this.body.className = 'popup_body';
		if(type=='iframe'){
			this.container = document.createElement('iframe');
			this.container.id = 'popup_'+name+'_container';
			this.container.style.overflow = 'auto';
			this.container.style.overflowX = 'hidden';
			this.container.style.border = 0;
			this.container.frameBorder = 0;
			this.container.border = 0;
			this.container.style.display = 'block';
		}else{
			this.container = document.createElement('div');
			this.container.id = 'popup_'+name+'_container';
			this.container.style.overflow = 'auto';
			if(this.path){
				var innerdiv = (this.path.nodeType==1)?this.path:document.getElementById(this.path);
				if(innerdiv){
					var textareas = innerdiv.getElementsByTagName('textarea');
					var editors = [];
					for(i=0;i<textareas.length;i++){
						if(textareas[i].className.indexOf('mceEditor') >= 0 && tinymce && (editor=tinymce.getInstanceById(textareas[i].id)) && editor.dom){
							editors.push(editor);
							editor.remove();
						}
					}
					this.container.appendChild(innerdiv);
					innerdiv.style.display = '';
				}
			}
		}
		this.body.appendChild(this.container);
		this.div.appendChild(this.body);
		document.body.insertBefore(this.div,document.body.firstChild);	
		this.SetSize(width,height);
		if(editors){
			for(var i=0;i<editors.length;i++){
				var ed = tinymce.add(new tinymce.Editor(editors[i].id,editors[i].settings));
				ed.render();
			}
		}
	}
}

popup.prototype.GetOwnerDocument = function(){
	if(this.type=='iframe'){
		this.doc = this.container.contentDocument?this.container.contentDocument:this.container.contentWindow.document;
	}
	return this.doc;
}

popup.prototype.ShowRefresh = function(replacements){
	var path = this.path;
	if(typeof(replacements) == 'object'){
		for(key in replacements){
			var link = path.indexOf('?') > -1?'&':'?';
			path += link + key + '=' + escape(replacements[key]);
		}
	}
	eval("function PopupManagerRefreshLoaded(){releaseEventHandler('"+this.container.id+"','load',PopupManagerRefreshLoaded);PopupManager.popups['"+this.name+"'].Show();}");
	attachEventHandler(this.container,'load',PopupManagerRefreshLoaded);
	this.container.src = path;
}

popup.prototype.ShowRefreshWithLoad = function(replacements){
	var path = this.path;
	if(typeof(replacements) == 'object'){
		for(key in replacements){
			var link = path.indexOf('?') > -1?'&':'?';
			path += link + key + '=' + escape(replacements[key]);
		}
	}
	this.GetOwnerDocument().body.innerHTML = '';
	this.Show();
	this.container.src = path;
}

popup.prototype.Show = function(replacements,zindex){
	if(typeof(replacements) == 'object'){
		for(name in replacements){
			var spans = this.container.getElementsByTagName('span');
			for(i=0;i<spans.length;i++){
				if(spans[i].id == name) spans[i].innerHTML = replacements[name];
			}
			var divs = this.div.getElementsByTagName('div');
			for(i=0;i<divs.length;i++){
				if(divs[i].id == name) divs[i].innerHTML = replacements[name];
			}
			var inputs = this.container.getElementsByTagName('input');
			for(i=0;i<inputs.length;i++){
				if(inputs[i].name == name || inputs[i].id == name){
					if(inputs[i].type == "checkbox"){
						inputs[i].checked = replacements[name];
					}else{
						inputs[i].value = replacements[name];
					}
				}
			}
			var selects = this.container.getElementsByTagName('select');
			for(i=0;i<selects.length;i++){
				if(selects[i].name == name || selects[i].id == name){
					selects[i].selectedIndex = 0;
					for(j=0;j<selects[i].length;j++){
						if(selects[i][j].value==replacements[name]){
							selects[i].selectedIndex = j;
							break;
						}
					}
				}
			}
			var textareas = this.container.getElementsByTagName('textarea');
			for(i=0;i<textareas.length;i++){
				if(textareas[i].name == name || textareas[i].id == name){
					if(textareas[i].className.indexOf('mceEditor') >= 0 && tinymce && (editor=tinymce.getInstanceById(name)) && editor.dom){
						editor.setContent(replacements[name]);
					}else{
						textareas[i].value = replacements[name];
					}
				}
			}
		}
	}
	
	this.div.style.display = 'block';
	if(zindex) this.div.style.zIndex = zindex;
	else this.div.style.zIndex = ++PopupManager.zIndex;
	// Set focus to the first text field
	if(inputs && inputs.length){
		for(i=0;i<inputs.length;i++){
			if(inputs[i].type=='text'){
				inputs[i].focus();
				break;
			}
		}
	}
}

popup.prototype.Hide = function(){
	this.div.style.display = 'none';
}

popup.prototype.SetSize = function(width,height){
	this.width = Math.max(parseInt(width|0),0);
	this.height = Math.max(parseInt(height|0),0);
	
	if(this.width==0 || this.height==0) this.MeasureSize();
	
	this.div.style.width = (this.width + PopupManager.popupcontainerpaddingwidth) + 'px';
	this.div.style.height = (this.height + PopupManager.popupcontainerpaddingheight) + 'px';
	
	this.container.style.width = this.width + 'px';
	this.container.style.height = this.height + 'px';
	
	this.left = Math.max((document.documentElement.clientWidth/2) - ((this.width+PopupManager.popupcontainerpaddingwidth)/2) - PopupManager.popupborderwidth,0);
	this.top = Math.max((document.documentElement.clientHeight/2) - ((this.height+PopupManager.popupcontainerpaddingheight)/2) - PopupManager.popupborderwidth,0);
	this.div.style.top = this.top + 'px';
	this.div.style.left = this.left + 'px';
}

popup.prototype.SetTitle = function(title){
	this.title = title;
	document.getElementById('popup_' + this.name + '_title').innerHTML = title;
}

popup.prototype.Prepare = function(width,height,title){
	this.SetSize(width,height);
	if(title) this.SetTitle(title);
}

popup.prototype.MeasureSize = function(){
	var clone;
	if(this.type=='iframe'){
		clone = this.GetOwnerDocument().body.cloneNode(true);
	}else{
		clone = this.container.cloneNode(true);
	}
	(function resetids(elem){
		if(elem.id && elem.id != '') elem.id += '_clone';
		for(var i=0;i<elem.childNodes.length;i++){resetids(elem.childNodes[i]);}
	})(clone);
	clone.style.width = 'auto';
	clone.style.height = 'auto';
	clone.style.position = 'absolute';
	clone.style.top = '-2000px';
	clone.style.left = '-2000px';
	clone.style.display = 'block';
	document.body.appendChild(clone);
	var pagesize = getPageSize();
	var pageheight = pagesize.windowheight - PopupManager.popupcontainerpaddingheight - PopupManager.popupborderwidth;
	var cloneheight = isie?clone.scrollHeight:clone.offsetHeight;
	this.width = isie?clone.scrollWidth:clone.offsetWidth;
	if(cloneheight > pageheight){
		this.height = pageheight;
		this.width += 20;
	}else{
		this.height = cloneheight;
	}
	document.body.removeChild(clone);
}
