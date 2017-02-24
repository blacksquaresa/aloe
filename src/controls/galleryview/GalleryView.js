function GalleryView(prefix,galleryid,storage,webpath,thumbwidth,thumbheight,thumbpadding,thumbspacing,buttonpadding,imagewidth,imageheight,tablename,propertyname,usedisabled,defaultpath,iconpath,videoplayer,audioplayerpath,resizestyle){
	this.prefix = prefix;
	this.galleryid = galleryid;
	this.storage = storage;
	this.webpath = webpath;
	this.imagewidth = imagewidth;
	this.imageheight = imageheight;
	this.thumbwidth = thumbwidth;
	this.thumbheight = thumbheight;
	this.thumbpadding = thumbpadding;
	this.thumbspacing = thumbspacing;
	this.buttonpadding = buttonpadding;
	this.tablename = tablename;
	this.propertyname = propertyname;
	this.usedisabled = usedisabled;
	this.defaultpath = defaultpath;
	this.iconpath = iconpath;
	this.videoplayer = videoplayer;
	this.audioplayerpath = audioplayerpath;
	this.resizestyle = resizestyle;
	this.items = [];
	this.metadata = {};
	eval(prefix+'=this;');
	
	this.containertop = 0;
	this.containerleft = 0;
	
	this.callbacks = {};
	this.canedit = true;
	this.candelete = true;
	this.canmove = true;
	
	this.container = document.getElementById(this.prefix+'_container');
	this.dragobject = null;
}

GalleryView.prototype.CreateItemElement = function(item){
	var container = document.createElement('span');
	container.className = 'GV_editcell';
	container.style.display = 'inline-block';
	container.id = this.prefix+'_'+item.id;
	
	var thumb = document.createElement('div');
	thumb.className = 'GV_thumb';
	thumb.align = 'center';
	thumb.style.width = this.thumbwidth+'px';
	thumb.style.height = this.thumbheight+'px';
	thumb.style.paddingTop = (((this.thumbheight-item.thumbheight)/2)+(this.thumbpadding/2))+'px';
	container.appendChild(thumb);
	
	var imglink = document.createElement('a');
	var imgpath = item.thumbname.length?this.webpath+item.thumbname:this.defaultpath+'GV_default'+item.itemtype+'.png';
	imglink.href = 'javascript:'+this.prefix+'.ShowItem(\''+item.id+'\');';
	imglink.innerHTML = '<img src="'+imgpath+getRandomCode()+'" align="top" style="width:'+item.thumbwidth+'px;height:'+item.thumbheight+'px;" ondragstart="return false;" onmousedown="return false;" />';
	thumb.appendChild(imglink);
	
	if(this.canedit || this.candelete || this.canmove){
		var buttons = document.createElement('div');
		buttons.className = 'GV_buttons';
		buttons.style.width = (this.thumbwidth-this.buttonpadding)+'px';
		if(this.canmove) buttons.innerHTML += '<img src="'+this.iconpath+'moves.gif" class="GV_movebutton" align="top" onmousedown="'+this.prefix+'.BeginDragDrop(event,\''+item.id+'\');" ondragstart="return false;" />';
		if(this.canedit) buttons.innerHTML += '<a href="javascript:'+this.prefix+'.EditItem(\''+item.id+'\');" class="GV_editbutton"><img src="'+this.iconpath+'edits.gif" align="top" /></a>';
		if(this.candelete) buttons.innerHTML += '<a href="javascript:'+this.prefix+'.DeleteItem(\''+item.id+'\');" class="GV_deletebutton"><img src="'+this.iconpath+'deletes.gif" align="top" /></a>';
		container.appendChild(buttons);
	}
	
	return container;	
}

GalleryView.prototype.AddExistingItem = function(id,itemtype,filename,thumbname,position,uploadoption,data){
	var item = new GalleryViewItem(this,id,itemtype,filename,thumbname,position,0,0,uploadoption,data);
	this.items.push(item);
}

GalleryView.prototype.AddItem = function(id,itemtype,filename,thumbname,position,thumbwidth,thumbheight,uploadoption){
	var item = new GalleryViewItem(this,id,itemtype,filename,thumbname,position,thumbwidth,thumbheight,uploadoption);
	this.items.push(item);
	this.container.appendChild(item.element);
	this.container.scrollTop = item.element.offsetTop;
	return item;
}

GalleryView.prototype.RemoveItem = function(item){
	for(var i=0;i<this.items.length;i++){
		if(this.items[i] == item){
			this.items.splice(i,1)
			break;
		}
	}
	this.container.removeChild(item.element);
	return i;
}

GalleryView.prototype.InsertItem = function(item,pos){
	var before = this.items[pos];
	this.items.splice(pos,0,item)
	this.container.insertBefore(item.element,before?before.element:null);
	if(this.storage=='folder'){
		for(var i=0;i<this.items.length;i++){
			this.items[i].ResetFilePaths(i);				
		}
	}
}

GalleryView.prototype.AddMetaDataField = function(name,label,type){
	var item = new GalleryViewField(this,name,label,type);
	this.metadata[name] = item;
}

GalleryView.prototype.GetItemById = function(id){
	for(i=0;i<this.items.length;i++){
		if(this.items[i].id == id){
			return this.items[i];
		}
	}
	return false;
}

GalleryView.prototype.GetNextItem = function(id){
	for(i=0;i<this.items.length;i++){
		if(this.items[i].id == id){
			if(i==this.items.length-1) return null;
			return this.items[i+1];
		}
	}
	return false;
}

/* ==================================================================
					Manage Items (show, move, delete)
   ================================================================== */

GalleryView.prototype.ShowItem = function(id){
	if(this.callbacks.click){
		if(typeof(this.callbacks.click) == 'function'){
			this.callbacks.click(id);
		}else{
			eval(this.callbacks.click+"('"+id+"');");
		}
	}else{
		var item = this.GetItemById(id);
		eval("this.Show"+item.itemtype+"(item);");
	}
}

GalleryView.prototype.Showimage = function(item){
	var generic = top.document.getElementById('content_generic');
	if(!generic){
		generic = top.document.createElement('div');
		generic.id = 'content_generic';
		generic.style.display = 'none';
		top.document.body.appendChild(generic);
	}
	top.PopupManager.showLoading();
	var imgpath = this.webpath + item.filename + '?' + getRandomCode();
	generic.className = 'imagecontainer';
	generic.owner = this;
	generic.innerHTML = '<img src="' + imgpath + '" onclick="this.parentNode.owner.HideItem();" onload="this.setAttribute(\'onload\',\'\');var pop=top.PopupManager.createOrFetchPopup(\'content_generic\',\'\',0,0,\'div\',\'content_generic\',\'loading\');pop.SetSize(0,0);pop.Show();" align="top" />';
	eval("top.document.getElementById('popup__disabled').onclick = function(){"+this.prefix+".HideItem(); };");
}

GalleryView.prototype.Showvideo = function(item){
	if(this.videoplayer.length == 0){
		showError('There is no video player registered.','error',true);
		return false;
	}
	var generic = top.document.getElementById('content_generic');
	if(!generic){
		generic = top.document.createElement('div');
		generic.id = 'content_generic';
		generic.style.display = 'none';
		generic.owner = this;
		top.document.body.appendChild(generic);
	}
	top.PopupManager.showLoading(); 
	generic.innerHTML += '<div style="position: absolute; top: 0px; right: 0px;"><img src="'+this.defaultpath+'close.png" onclick="'+this.prefix+'.HideItem();" align="top" /></div>';
	   
	var container = top.document.createElement('div');
	generic.appendChild(container);
	if(item.uploadoption == 'file' || item.uploadoption == 'resource'){  
		var filename = location.protocol+'//'+location.host+(item.uploadoption == 'file'?'/'+trim(this.webpath,'./')+'/'+item.filename:item.filename);
		FlashVideos[item.id] = new FlashVideo(item.id,filename,"","Title","#000000","400px","300px",this.videoplayer,true);
		FlashVideo_ShowVideo(item.id,container);
	}else if(item.uploadoption == 'embed'){      
		container.innerHTML = item.filename;	       
	}
	top.PopupManager.createOrFetchPopup('content_generic','',0,0,'div','content_generic','loading').Show();
	eval("top.document.getElementById('popup__disabled').onclick = function(){"+this.prefix+".HideItem(); };");
}

GalleryView.prototype.Showaudio = function(item){
	if(this.audioplayerpath.length == 0){
		showError('There is no audio player registered.','error',true);
		return false;
	}
	var generic = top.document.getElementById('content_generic');
	if(!generic){
		generic = top.document.createElement('div');
		generic.id = 'content_generic';
		generic.style.display = 'none';
		generic.owner = this;
		top.document.body.appendChild(generic);
	}
	top.PopupManager.showLoading(); 
	
	var container = document.createElement('div');
	container.style.height = 40+'px'; 
	container.style.overflow = 'hidden'; 
	    
	container.innerHTML = EmbedAudioCode(this.webpath+'xml/'+this.galleryid+'_'+item.id+'.xml',this.audioplayerpath);
	generic.appendChild(container);
		
	top.PopupManager.createOrFetchPopup('content_generic','',0,0,'div','content_generic','loading').Show();
	eval("top.document.getElementById('popup__disabled').onclick = function(){"+this.prefix+".HideItem(); };");
}

GalleryView.prototype.HideItem = function(){
	top.document.getElementById('popup__disabled').onclick = null;
	top.document.getElementById('content_generic').innerHTML = '';
	top.PopupManager.hidePopup('content_generic');
	top.PopupManager.hideLoading();
}

GalleryView.prototype.DeleteItem = function(id){
	if(this.callbacks.del){
		if(typeof(this.callbacks.del) == 'function'){
			this.callbacks.del(id);
		}else{
			eval(this.callbacks.del+"('"+id+"');");
		}
	}else{
		if(confirm('Are you sure you want to delete this item?')){
			top.PopupManager.showDisabled();
			var response = agent.call('/controls/galleryview/GalleryView.ajax.php','GalleryView_DeleteItem','',this.galleryid,id,this.storage,this.webpath,this.tablename,this.propertyname);
			if(!isNaN(response)){
				var item = this.GetItemById(id);
				var pos = this.RemoveItem(item);
				if(this.storage=='folder'){
					for(var i=pos;i<this.items.length;i++){
						this.items[i].ResetFilePaths(i);				
					}
				}
				top.PopupManager.hideDisabled();
			}else{
				showError(response,'error',this.usedisabled);
			}
		}
	}
}

GalleryView.prototype.MoveItem = function(item,targetitem){
	var source = arrayIndexOf(this.items,item);
	var target = targetitem?arrayIndexOf(this.items,targetitem):this.items.length;
	if(source<target) target--;
	if(this.callbacks.move){
		if(typeof(this.callbacks.move) == 'function'){
			this.callbacks.move(item.id,target);
		}else{
			eval(this.callbacks.move+"('"+item.id+"',"+target+");");
		}
	}else{
		top.PopupManager.showDisabled();
		var response = agent.call('/controls/galleryview/GalleryView.ajax.php','GalleryView_MoveItem','',this.galleryid,item.id,target,this.storage,this.webpath,this.tablename,this.propertyname);
		if(!isNaN(response)){
			this.RemoveItem(item);
			this.InsertItem(item,target);
			top.PopupManager.hideDisabled();
		}else{
			showError(response,'error',this.usedisabled);
		}
	}
}

GalleryView.prototype.CreateImage = function(){
	var properties = {};
	for(var key in this.metadata){
		properties['prop['+key+']'] = '';
	}
	return this.Editimage({'id':''},properties);
}

GalleryView.prototype.CreateVideo = function(){
	return this.Editvideo({'id':''},{});
}

GalleryView.prototype.CreateAudio = function(){
	return this.Editaudio({'id':''},{});
}

GalleryView.prototype.EditItem = function(id){
	var item = this.GetItemById(id);
	if(this.callbacks.edit){
		if(typeof(this.callbacks.edit) == 'function'){
			this.callbacks.edit(id);
		}else{
			eval(this.callbacks.edit+"('"+id+"');");
		}
	}else{
		var properties = {};
		for(var key in this.metadata){
			properties['prop['+key+']'] = item.data?item.data[key]:'';
		}
		eval("this.Edit"+item.itemtype+"(item,properties);");
	}
}

/* ==================================================================
							Edit Images
   ================================================================== */

GalleryView.prototype.Editimage = function(item,properties){
	top.PopupManager.showDisabled();
	var edit = top.document.getElementById(this.prefix+'_editimage');
	var creating = item.id&&item.id.length?false:true;
	if(!edit){
		var windowwidth = Math.max(530,430+this.thumbwidth);
		
		var edit = document.createElement('div');
		edit.id = this.prefix+'_editimage';
		edit.style.width = windowwidth+'px';
		
		var form = document.createElement('form');
		form.action = '/controls/galleryview/GalleryView.aim.php';
		form.method = 'post';
		form.id = this.prefix+'_editimage_form';
		form.onsubmit = new function(){ return AIM.submit(form, {'onComplete' : GalleryViewEditImageCallback}) };
		form.enctype = 'multipart/form-data';
		form.encoding = 'multipart/form-data';
		edit.appendChild(form);
		
		var gvid = document.createElement('input');
		gvid.type = 'hidden';
		gvid.name = 'galleryid';
		form.appendChild(gvid);
		
		var gvname = document.createElement('input');
		gvname.type = 'hidden';
		gvname.name = 'galleryname';
		gvname.id = this.prefix+'_editimage_galleryname';
		form.appendChild(gvname);
		
		var itemid = document.createElement('input');
		itemid.type = 'hidden';
		itemid.name = 'itemid';
		form.appendChild(itemid);
		
		var storage = document.createElement('input');
		storage.type = 'hidden';
		storage.name = 'storage';
		form.appendChild(storage);
		
		var webpath = document.createElement('input');
		webpath.type = 'hidden';
		webpath.name = 'webpath';
		form.appendChild(webpath);
		
		var sizes = document.createElement('input');
		sizes.type = 'hidden';
		sizes.name = 'sizes';
		form.appendChild(sizes);
		
		var resize = document.createElement('input');
		resize.type = 'hidden';
		resize.name = 'resizestyle';
		form.appendChild(resize);
		
		if(this.storage == 'database'){		
			var tablename = document.createElement('input');
			tablename.type = 'hidden';
			tablename.name = 'tablename';
			form.appendChild(tablename);
			
			var propertyname = document.createElement('input');
			propertyname.type = 'hidden';
			propertyname.name = 'propertyname';
			form.appendChild(propertyname);
		}
		
		var holdtable = document.createElement('table');
		holdtable.className = 'edt_table';
		form.appendChild(holdtable);
		
		var filerow = holdtable.insertRow(0);
		
		var imagecell = filerow.insertCell(0);
		imagecell.id = this.prefix+'_editimage_thumbcell';
		imagecell.style.borderRight = '1px solid #93928E';
		imagecell.style.paddingRight = '10px';
		imagecell.vAlign = "top";
		imagecell.rowSpan = '3';
		
		var imagediv = document.createElement('img');
		imagediv.id = this.prefix+'_editimage_thumbnail';
		imagediv.style.width = this.thumbwidth + 'px';
		imagediv.style.height = this.thumbheight + 'px';
		imagecell.appendChild(imagediv);
		
		var filelabelcell = filerow.insertCell(1);
		filelabelcell.className = 'label_right';
		filelabelcell.style.whiteSpace = 'nowrap';
		filelabelcell.innerHTML = 'Full Size image:';
		
		var filecell = filerow.insertCell(2);
		filecell.className = 'field';
		filecell.innerHTML = '<input type="file" id="'+this.prefix+'_editimage_file" name="file" class="edt_textbox" />';
		
		var thumbrow = holdtable.insertRow(1);
		
		var thumblabelcell = thumbrow.insertCell(0);
		thumblabelcell.className = 'label_right';
		thumblabelcell.style.whiteSpace = 'nowrap';
		thumblabelcell.innerHTML = 'Thumbnail image:';
		
		var thumbcell = thumbrow.insertCell(1);
		thumbcell.className = 'field';
		thumbcell.innerHTML = '<input type="file" id="'+this.prefix+'_editimage_thumb" name="thumb" class="edt_textbox" />';
		
		var checkrow = holdtable.insertRow(2);
		
		var checkcell = checkrow.insertCell(0);
		checkcell.id = this.prefix+'_editimage_checkcell';
		checkcell.colSpan = '2';
		checkcell.style.paddingLeft = '10px';
		checkcell.className = 'label_left';
		checkcell.innerHTML = '<input type="checkbox" id="'+this.prefix+'_editimage_makethumb" name="makethumb" class="edt_checkbox" /> <label for="'+this.prefix+'_editimage_makethumb">Recreate the thumbnail image from the uploaded file?</label><br /><span class="edt_help">This value will be ignored if a thumbnail image is uploaded. Otherwise, the thumbnail will not be re-created from the new image if this checkbox is not checked. </span>';
		
		var divrow = holdtable.insertRow(3);
		var divcell = divrow.insertCell(0);
		divcell.colSpan = '3';
		divcell.innerHTML = '<div class="edt_divider"></div>';
		
		var saverow = holdtable.insertRow(4);
		var savecell = saverow.insertCell(0);
		savecell.colSpan = '3';
		savecell.align = 'right';
		savecell.innerHTML = '<input type="reset" class="edt_button" value="Reset">&nbsp;<input type="submit" class="edt_button" name="GV_updateimage" value="Save Image">';
		
		if(this.metadata){
			var ind = 3;
			for(var key in this.metadata){
				var proprow = holdtable.insertRow(ind++);
				var propcell = proprow.insertCell(0);
				propcell.className = 'label_right';
				propcell.innerHTML = this.metadata[key].label+':';
				var textcell = proprow.insertCell(1);
				textcell.colSpan = '2';
				textcell.className = 'field';
				switch(this.metadata[key].type){
					case 'link':
						textcell.innerHTML = '<input type="text" id="'+this.prefix+'_editimage_prop_' + key + '" name="prop[' + key + ']" style="width: 327px;" /> <a href="javascript:top.PopupManager.showLinkSelector(null,\''+this.prefix+'_editimage_prop_' + key + '\');"><img src="'+this.iconpath+'select.png" align="absmiddle" /></a>';
						break;
					default:
						textcell.innerHTML = '<input type="text" id="'+this.prefix+'_editimage_prop_' + key + '" name="prop[' + key + ']" style="width: 350px;" />';
						break;
				}
			}
			
			if(ind > 3){
				var pdivrow = holdtable.insertRow(3);
				var pdivcell = pdivrow.insertCell(0);
				pdivcell.colSpan = '3';
				pdivcell.innerHTML = '<div class="edt_divider"></div>';
			}
		}
	}
	properties['galleryid'] = this.galleryid;
	properties['galleryname'] = this.prefix;
	properties['itemid'] = item.id;
	properties['storage'] = this.storage;
	properties['webpath'] = this.webpath;
	properties['sizes'] = this.imagewidth+','+this.imageheight+','+this.thumbwidth+','+this.thumbheight;
	properties['resizestyle'] = this.resizestyle;
	properties['makethumb'] = creating?1:0;
	if(this.storage == 'database'){
		properties['tablename'] = this.tablename;
		properties['propertyname'] = this.propertyname;
	}
	if(!imagecell) imagecell = top.document.getElementById(this.prefix+'_editimage_thumbcell');
	if(!checkcell) checkcell = top.document.getElementById(this.prefix+'_editimage_checkcell');
	if(creating){
		if(checkcell) checkcell.style.display = 'none';
		if(imagecell) imagecell.style.display = 'none';
	}else{
		if(checkcell) checkcell.style.display = '';
		if(imagecell) imagecell.style.display = '';
		if(!imagediv) imagediv = top.document.getElementById(this.prefix+'_editimage_thumbnail');
		if(imagediv) imagediv.src = this.webpath+item.thumbname+getRandomCode();
	}
	top.galleryview_window = window;
	var pop = top.PopupManager.createOrFetchPopup(this.prefix+'_editimage','Gallery Image',0,0,'div',edit,'disabled');
	pop.Show(properties);
	pop.SetSize(0,0);
}

/* ==================================================================
							Edit Video
   ================================================================== */

GalleryView.prototype.Editvideo = function(item,properties){
	top.PopupManager.showDisabled();
	var edit = document.getElementById(this.prefix+'_editvideo');
	var creating = item.id&&item.id.length?false:true;
	if(!edit){
		edit = document.createElement('div');
		edit.id = this.prefix+'_editvideo';
		var width = 720 + (Math.max(0,this.thumbwidth-70));
		edit.style.width = width+'px';
		
		var form = document.createElement('form');
		form.action = '/controls/galleryview/GalleryView.aim.php';
		form.method = 'post';
		form.id = this.prefix+'_editvideo_form';
		form.onsubmit = new function(){ return AIM.submit(form, {'onComplete' : GalleryViewEditVideoCallback}) };
		form.enctype = 'multipart/form-data';
		form.encoding = 'multipart/form-data';
		edit.appendChild(form);
		
		var gvid = document.createElement('input');
		gvid.type = 'hidden';
		gvid.name = 'galleryid';
		form.appendChild(gvid);
		
		var gvname = document.createElement('input');
		gvname.type = 'hidden';
		gvname.name = 'galleryname';
		gvname.id = this.prefix+'_editvideo_galleryname';
		form.appendChild(gvname);
		
		var itemid = document.createElement('input');
		itemid.type = 'hidden';
		itemid.name = 'itemid';
		form.appendChild(itemid);
		
		var storage = document.createElement('input');
		storage.type = 'hidden';
		storage.name = 'storage';
		form.appendChild(storage);
		
		var webpath = document.createElement('input');
		webpath.type = 'hidden';
		webpath.name = 'webpath';
		form.appendChild(webpath);
		
		var sizes = document.createElement('input');
		sizes.type = 'hidden';
		sizes.name = 'sizes';
		form.appendChild(sizes);
		
		var water = document.createElement('input');
		water.type = 'hidden';
		water.name = 'watermark';
		form.appendChild(water);
		
		if(this.storage == 'database' || this.storage == 'photos'){		
			var tablename = document.createElement('input');
			tablename.type = 'hidden';
			tablename.name = 'tablename';
			form.appendChild(tablename);
			
			var propertyname = document.createElement('input');
			propertyname.type = 'hidden';
			propertyname.name = 'propertyname';
			form.appendChild(propertyname);
		}
		
		var holdtable = document.createElement('table');
		form.appendChild(holdtable);
		
		var holdrow = holdtable.insertRow(0);
		
		// Left cell - video file upload options
		var holdfilecell = holdrow.insertCell(0);
		holdfilecell.style.borderRight = '1px solid #dedede';
		holdfilecell.style.paddingRight = '10px';
		holdfilecell.vAlign = 'top';
		
		var filetable = document.createElement('table');
		filetable.style.width = '318px';
		holdfilecell.appendChild(filetable);
		
		var filetitrow = filetable.insertRow(0);
		var filetitcell = filetitrow.insertCell(0);
		filetitcell.className = 'edt_heading2';
		filetitcell.style.paddingBottom = '5px';
		filetitcell.style.background = 'none';
		filetitcell.style.border = 'none';
		filetitcell.innerHTML = 'Upload Video Options';
		
		var fileup1row = filetable.insertRow(1);
		var filetup1cell = fileup1row.insertCell(0);
		filetup1cell.className = 'edt_heading3';
		filetup1cell.style.paddingBottom = '5px';
		filetup1cell.innerHTML = '1. UPLOAD A YOUTUBE VIDEO.';
		
		var filenot1row = filetable.insertRow(2);
		var filenot1cell = filenot1row.insertCell(0);
		filenot1cell.className = 'edt_help';
		filenot1cell.style.paddingBottom = '5px';
		filenot1cell.innerHTML = 'Enter the embed code supplied to you by your video host (eg: YouTube or Vimeo)';
		
		var filefi1row = filetable.insertRow(3);
		var filefi1cell = filefi1row.insertCell(0);
		filefi1cell.style.paddingBottom = '20px';
		filefi1cell.innerHTML = '<textarea id="'+this.prefix+'_editvideo_embed" name="embed" class="edt_textarea" style="width: 300px; height: 100px;"></textarea>';
		
		var fileup2row = filetable.insertRow(4);
		var filetup2cell = fileup2row.insertCell(0);
		filetup2cell.className = 'edt_heading3';
		filetup2cell.style.paddingBottom = '5px';
		filetup2cell.innerHTML = '2. UPLOAD FROM YOUR DESKTOP.';
		
		var filenot2row = filetable.insertRow(5);
		var filenot2cell = filenot2row.insertCell(0);
		filenot2cell.className = 'edt_help';
		filenot2cell.style.paddingBottom = '5px';
		filenot2cell.innerHTML = 'Browse your computer to choose the file you want to upload. Files need to be in .flv format, and must be no larger than 5MB. For larger files, use FTP to upload the file directly into resources, then select it using option 3 below.';
		
		var filein2row = filetable.insertRow(6);
		var filein2cell = filein2row.insertCell(0);
		filein2cell.style.paddingBottom = '5px';
		filein2cell.style.fontWeight = 'bold';
		filein2cell.innerHTML = '<div id="'+this.prefix+'_editvideo_filename" style="width: 300px; overflow: auto; height: 36px; white-space: nowrap;"></div>';
		
		var filefi2row = filetable.insertRow(7);
		var filefi2cell = filefi2row.insertCell(0);
		filefi2cell.style.paddingBottom = '10px';
		filefi2cell.innerHTML = '<input type="file" id="'+this.prefix+'_editvideo_file" name="file" class="edt_textbox" style="width: 300px;" />';
		
		var fileup3row = filetable.insertRow(8);
		var filetup3cell = fileup3row.insertCell(0);
		filetup3cell.className = 'edt_heading3';
		filetup3cell.style.paddingBottom = '5px';
		filetup3cell.innerHTML = '3. LINK FROM RESOURCES.';
		
		var filenot3row = filetable.insertRow(9);
		var filenot3cell = filenot3row.insertCell(0);
		filenot3cell.className = 'edt_help';
		filenot3cell.style.paddingBottom = '5px';
		filenot3cell.innerHTML = 'Link an .flv video file from Resources.';
		
		var filefi3row = filetable.insertRow(10);
		var filefi3cell = filefi3row.insertCell(0);
		filefi3cell.style.paddingBottom = '20px';
		filefi3cell.noWrap = 'true';
		filefi3cell.innerHTML = '<input type="text" id="'+this.prefix+'_editvideo_resource" name="resource" class="edt_textbox" style="width: 280px;" /><a href="javascript:showMediaSelector(\''+this.prefix+'_editvideo_resource\');"><img align="absmiddle" src="../images/admin/common/select.png" /></a>';
		
				
		// Right cell - meta data and thumbnail options
		var holdthumbcell = holdrow.insertCell(1);
		holdthumbcell.vAlign = 'top';
		holdthumbcell.style.paddingLeft = '10px';
		
		var thumbtable = document.createElement('table');
		holdthumbcell.appendChild(thumbtable);
		
		var thumbtitrow = thumbtable.insertRow(0);
		var thumbtitcell = thumbtitrow.insertCell(0);
		thumbtitcell.className = 'edt_heading2';
		thumbtitcell.style.paddingBottom = '5px';
		thumbtitcell.style.background = 'none';
		thumbtitcell.style.border = 'none';
		thumbtitcell.innerHTML = 'Thumbnail';
		thumbtitcell.colSpan = '2';
		
		var thumbfilerow = thumbtable.insertRow(1);
		var thumbfilelabelcell = thumbfilerow.insertCell(0);
		thumbfilelabelcell.className = 'label_right';
		thumbfilelabelcell.innerHTML = 'Thumbnail:';
		var thumbfilefieldcell = thumbfilerow.insertCell(1);
		thumbfilefieldcell.className = 'field';
		thumbfilefieldcell.innerHTML = '<input type="file" id="'+this.prefix+'_editvideo_thumb" name="thumb" class="edt_textbox" />';
		
		var thumbshowrow = thumbtable.insertRow(2);
		var thumbimgcell = thumbshowrow.insertCell(0);
		thumbimgcell.style.paddingRight = '10px';
		thumbimgcell.style.width = this.thumbwidth + 'px';
		thumbimgcell.style.height = this.thumbheight + 'px';
		thumbimgcell.vAlign = "top";
		
		var thumbimage = document.createElement('img');
		thumbimage.id = this.prefix+'_editvideo_thumbnail';
		thumbimgcell.appendChild(thumbimage);
		
		var thumbhelpcell = thumbshowrow.insertCell(1);
		thumbhelpcell.className = 'edt_help';
		thumbhelpcell.vAlign = 'top';
		thumbhelpcell.innerHTML = 'Upload an image to represent your video. This image will be cropped  down to ' + this.thumbwidth + 'px x ' + this.thumbheight + 'px, and it will have a "play"  icon added on top of it to mark it as a video file.';
		
		var divrow = thumbtable.insertRow(3);
		var divcell = divrow.insertCell(0);
		divcell.colSpan = '2';
		divcell.innerHTML = '<div class="edt_divider"></div>';
		
		var saverow = thumbtable.insertRow(4);
		var savecell = saverow.insertCell(0);
		savecell.colSpan = '2';
		savecell.align = 'right';
		savecell.innerHTML = '<input type="reset" class="edt_button" value="Reset">&nbsp;<input type="submit" class="edt_button" name="GV_updatevideo" value="Save Video">';
		
		if(this.metadata){
			var ind = 3;
			for(var key in this.metadata){
				var proprow = thumbtable.insertRow(ind++);
				var propcell = proprow.insertCell(0);
				propcell.className = 'label_right';
				propcell.innerHTML = this.metadata[key].label+':';
				var textcell = proprow.insertCell(1);
				textcell.className = 'field';
				switch(this.metadata[key].type){
					default:
						textcell.innerHTML = '<input type="text" id="'+this.prefix+'_editvideo_prop_' + key + '" name="prop[' + key + ']" class="edt_textbox" />';
						break;
				}
			}
			
			if(ind > 3){
				var pdivrow = thumbtable.insertRow(3);
				var pdivcell = pdivrow.insertCell(0);
				pdivcell.colSpan = '3';
				pdivcell.innerHTML = '<div class="edt_divider"></div>';
		
				var ptitrow = thumbtable.insertRow(4);
				var ptitcell = ptitrow.insertCell(0);
				ptitcell.className = 'edt_heading2';
				ptitcell.colSpan = '2';
				ptitcell.style.paddingBottom = '5px';
				ptitcell.style.background = 'none';
				ptitcell.style.border = 'none';
				ptitcell.innerHTML = 'Supporting Information';
			}
		}
	}
	properties['galleryid'] = this.galleryid;
	properties['galleryname'] = this.prefix;
	properties['itemid'] = item.id;
	properties['storage'] = this.storage;
	properties['webpath'] = this.webpath;
	properties['sizes'] = this.thumbwidth+','+this.thumbheight;
	properties['watermark'] = this.defaultpath+'GV_playmark.png';
	properties['resource'] = item.uploadoption=='resource'?item.filename:'';
	properties['embed'] = item.uploadoption=='embed'?item.filename:'';
	properties[this.prefix+'_editvideo_filename'] = item.uploadoption=='file'?item.filename:'';
	if(this.storage == 'database'){
		properties['tablename'] = this.tablename;
		properties['propertyname'] = this.propertyname;
	}
	var img = top.document.getElementById(this.prefix+'_editvideo_thumbnail');
	if(img){
		if(creating || item.thumbname.length==0) img.src = this.defaultpath+this.prefix+'_defaultvideo.png';
		else img.src = this.webpath+item.thumbname+getRandomCode();
	}
	var fname = top.document.getElementById(this.prefix+'_editvideo_filename');
	if(fname){
		fname.style.display = item.uploadoption=='file'?'block':'none';
	}
	top.galleryview_window = window;
	var pop = top.PopupManager.createOrFetchPopup(this.prefix+'_editvideo','Gallery Video',0,0,'div',edit,'disabled');
	pop.Show(properties);
	pop.SetSize(0,0);
}

/* ==================================================================
							Edit Audio
   ================================================================== */

GalleryView.prototype.Editaudio = function(item,properties){
	top.PopupManager.showDisabled();
	var edit = document.getElementById(this.prefix+'_editaudio');
	var creating = item.id&&item.id.length?false:true;
	if(!edit){
		edit = document.createElement('div');
		edit.id = this.prefix+'_editaudio';
		var width = 720 + (Math.max(0,this.thumbwidth-70));
		edit.style.width = width+'px';
		
		var form = document.createElement('form');
		form.action = '/controls/galleryview/GalleryView.aim.php';
		form.method = 'post';
		form.id = this.prefix+'_editaudio_form';
		form.onsubmit = new function(){ return AIM.submit(form, {'onComplete' : GalleryViewEditAudioCallback}) };
		form.enctype = 'multipart/form-data';
		form.encoding = 'multipart/form-data';
		edit.appendChild(form);
		
		var gvid = document.createElement('input');
		gvid.type = 'hidden';
		gvid.name = 'galleryid';
		form.appendChild(gvid);
		
		var gvname = document.createElement('input');
		gvname.type = 'hidden';
		gvname.name = 'galleryname';
		gvname.id = this.prefix+'_editaudio_galleryname';
		form.appendChild(gvname);
		
		var itemid = document.createElement('input');
		itemid.type = 'hidden';
		itemid.name = 'itemid';
		form.appendChild(itemid);
		
		var storage = document.createElement('input');
		storage.type = 'hidden';
		storage.name = 'storage';
		form.appendChild(storage);
		
		var webpath = document.createElement('input');
		webpath.type = 'hidden';
		webpath.name = 'webpath';
		form.appendChild(webpath);
		
		var sizes = document.createElement('input');
		sizes.type = 'hidden';
		sizes.name = 'sizes';
		form.appendChild(sizes);
		
		var water = document.createElement('input');
		water.type = 'hidden';
		water.name = 'watermark';
		form.appendChild(water);
		
		if(this.storage == 'database' || this.storage == 'photos'){		
			var tablename = document.createElement('input');
			tablename.type = 'hidden';
			tablename.name = 'tablename';
			form.appendChild(tablename);
			
			var propertyname = document.createElement('input');
			propertyname.type = 'hidden';
			propertyname.name = 'propertyname';
			form.appendChild(propertyname);
		}
		
		var holdtable = document.createElement('table');
		form.appendChild(holdtable);
		
		var holdrow = holdtable.insertRow(0);
		
		// Left cell - audio file upload options
		var holdfilecell = holdrow.insertCell(0);
		holdfilecell.style.borderRight = '1px solid #dedede';
		holdfilecell.style.paddingRight = '10px';
		holdfilecell.vAlign = 'top';
		
		var filetable = document.createElement('table');
		filetable.style.width = '318px';
		holdfilecell.appendChild(filetable);
		
		var filetitrow = filetable.insertRow(0);
		var filetitcell = filetitrow.insertCell(0);
		filetitcell.className = 'edt_heading2';
		filetitcell.style.paddingBottom = '5px';
		filetitcell.style.background = 'none';
		filetitcell.style.border = 'none';
		filetitcell.innerHTML = 'Upload Audio Options';
		
		var fileup1row = filetable.insertRow(1);
		var filetup1cell = fileup1row.insertCell(0);
		filetup1cell.className = 'edt_heading3';
		filetup1cell.style.paddingBottom = '5px';
		filetup1cell.innerHTML = '1. LINK FROM AN EXTERNAL URL.';
		
		var filenot1row = filetable.insertRow(2);
		var filenot1cell = filenot1row.insertCell(0);
		filenot1cell.className = 'edt_help';
		filenot1cell.style.paddingBottom = '5px';
		filenot1cell.innerHTML = 'Enter the full URL of your mp3 file.';
		
		var filefi1row = filetable.insertRow(3);
		var filefi1cell = filefi1row.insertCell(0);
		filefi1cell.style.paddingBottom = '20px';
		filefi1cell.innerHTML = '<input type="text" id="'+this.prefix+'_editaudio_url" name="url" class="edt_textbox" style="width: 300px;" />';
		
		var fileup2row = filetable.insertRow(4);
		var filetup2cell = fileup2row.insertCell(0);
		filetup2cell.className = 'edt_heading3';
		filetup2cell.style.paddingBottom = '5px';
		filetup2cell.innerHTML = '2. UPLOAD FROM YOUR DESKTOP.';
		
		var filenot2row = filetable.insertRow(5);
		var filenot2cell = filenot2row.insertCell(0);
		filenot2cell.className = 'edt_help';
		filenot2cell.style.paddingBottom = '5px';
		filenot2cell.innerHTML = 'Browse your computer to choose the file you want to upload. Files need to be in .mp3 format, and must be no larger than 5MB. For larger files, use FTP to upload the file directly into resources, then select it using option 3 below.';
		
		var filein2row = filetable.insertRow(6);
		var filein2cell = filein2row.insertCell(0);
		filein2cell.style.paddingBottom = '5px';
		filein2cell.style.fontWeight = 'bold';
		filein2cell.innerHTML = '<div id="'+this.prefix+'_editaudio_filename" style="width: 300px; overflow: auto; height: 36px; white-space: nowrap;"></div>';
		
		var filefi2row = filetable.insertRow(7);
		var filefi2cell = filefi2row.insertCell(0);
		filefi2cell.style.paddingBottom = '10px';
		filefi2cell.innerHTML = '<input type="file" id="'+this.prefix+'_editaudio_file" name="file" class="edt_textbox" style="width: 300px;" />';
		
		var fileup3row = filetable.insertRow(8);
		var filetup3cell = fileup3row.insertCell(0);
		filetup3cell.className = 'edt_heading3';
		filetup3cell.style.paddingBottom = '5px';
		filetup3cell.innerHTML = '3. LINK FROM RESOURCES.';
		
		var filenot3row = filetable.insertRow(9);
		var filenot3cell = filenot3row.insertCell(0);
		filenot3cell.className = 'edt_help';
		filenot3cell.style.paddingBottom = '5px';
		filenot3cell.innerHTML = 'Link an .mp3 audio file from Resources.';
		
		var filefi3row = filetable.insertRow(10);
		var filefi3cell = filefi3row.insertCell(0);
		filefi3cell.style.paddingBottom = '20px';
		filefi3cell.noWrap = 'true';
		filefi3cell.innerHTML = '<input type="text" id="'+this.prefix+'_editaudio_resource" name="resource" class="edt_textbox" style="width: 280px;" /><a href="javascript:showMediaSelector(\''+this.prefix+'_editaudio_resource\');"><img align="absmiddle" src="../images/admin/common/select.png" /></a>';
		
				
		// Right cell - meta data and thumbnail options
		var holdthumbcell = holdrow.insertCell(1);
		holdthumbcell.vAlign = 'top';
		holdthumbcell.style.paddingLeft = '10px';
		
		var thumbtable = document.createElement('table');
		holdthumbcell.appendChild(thumbtable);
		
		var thumbtitrow = thumbtable.insertRow(0);
		var thumbtitcell = thumbtitrow.insertCell(0);
		thumbtitcell.className = 'edt_heading2';
		thumbtitcell.style.paddingBottom = '5px';
		thumbtitcell.style.background = 'none';
		thumbtitcell.style.border = 'none';
		thumbtitcell.innerHTML = 'Thumbnail';
		thumbtitcell.colSpan = '2';
		
		var thumbfilerow = thumbtable.insertRow(1);
		var thumbfilelabelcell = thumbfilerow.insertCell(0);
		thumbfilelabelcell.className = 'label_right';
		thumbfilelabelcell.innerHTML = 'Thumbnail:';
		var thumbfilefieldcell = thumbfilerow.insertCell(1);
		thumbfilefieldcell.className = 'field';
		thumbfilefieldcell.innerHTML = '<input type="file" id="'+this.prefix+'_editaudio_thumb" name="thumb" class="edt_textbox" />';
		
		var thumbshowrow = thumbtable.insertRow(2);
		var thumbimgcell = thumbshowrow.insertCell(0);
		thumbimgcell.style.paddingRight = '10px';
		thumbimgcell.style.width = this.thumbwidth + 'px';
		thumbimgcell.style.height = this.thumbheight + 'px';
		thumbimgcell.vAlign = "top";
		
		var thumbimage = document.createElement('img');
		thumbimage.id = this.prefix+'_editaudio_thumbnail';
		thumbimgcell.appendChild(thumbimage);
		
		var thumbhelpcell = thumbshowrow.insertCell(1);
		thumbhelpcell.className = 'edt_help';
		thumbhelpcell.vAlign = 'top';
		thumbhelpcell.innerHTML = 'Upload an image to represent your audio file. This image will be cropped  down to ' + this.thumbwidth + 'px x ' + this.thumbheight + 'px, and it will have a "play"  icon added on top of it to mark it as an audio file.';
		
		var divrow = thumbtable.insertRow(3);
		var divcell = divrow.insertCell(0);
		divcell.colSpan = '2';
		divcell.innerHTML = '<div class="edt_divider"></div>';
		
		var saverow = thumbtable.insertRow(4);
		var savecell = saverow.insertCell(0);
		savecell.colSpan = '2';
		savecell.align = 'right';
		savecell.innerHTML = '<input type="reset" class="edt_button" value="Reset">&nbsp;<input type="submit" class="edt_button" name="GV_updateaudio" value="Save Audio">';
		
		if(this.metadata){
			var ind = 3;
			for(var key in this.metadata){
				var proprow = thumbtable.insertRow(ind++);
				var propcell = proprow.insertCell(0);
				propcell.className = 'label_right';
				propcell.innerHTML = this.metadata[key].label+':';
				var textcell = proprow.insertCell(1);
				textcell.className = 'field';
				switch(this.metadata[key].type){
					default:
						textcell.innerHTML = '<input type="text" id="'+this.prefix+'_editaudio_prop_' + key + '" name="prop[' + key + ']" class="edt_textbox" />';
						break;
				}
			}
			
			if(ind > 3){
				var pdivrow = thumbtable.insertRow(3);
				var pdivcell = pdivrow.insertCell(0);
				pdivcell.colSpan = '3';
				pdivcell.innerHTML = '<div class="edt_divider"></div>';
		
				var ptitrow = thumbtable.insertRow(4);
				var ptitcell = ptitrow.insertCell(0);
				ptitcell.className = 'edt_heading2';
				ptitcell.colSpan = '2';
				ptitcell.style.paddingBottom = '5px';
				ptitcell.style.background = 'none';
				ptitcell.style.border = 'none';
				ptitcell.innerHTML = 'Supporting Information';
			}
		}
	}
	properties['galleryid'] = this.galleryid;
	properties['galleryname'] = this.prefix;
	properties['itemid'] = item.id;
	properties['storage'] = this.storage;
	properties['webpath'] = this.webpath;
	properties['sizes'] = this.thumbwidth+','+this.thumbheight;
	properties['watermark'] = this.defaultpath+this.prefix+'_playmark.png';
	properties['resource'] = item.uploadoption=='resource'?item.filename:'';
	properties['url'] = item.uploadoption=='url'?item.filename:'';
	properties[this.prefix+'_editaudio_filename'] = item.uploadoption=='file'?item.filename:'';
	if(this.storage == 'database'){
		properties['tablename'] = this.tablename;
		properties['propertyname'] = this.propertyname;
	}
	var img = top.document.getElementById(this.prefix+'_editaudio_thumbnail');
	if(img){
		if(creating || item.thumbname.length==0) img.src = this.defaultpath+this.prefix+'_defaultaudio.png';
		else img.src = this.webpath+item.thumbname+getRandomCode();
	}
	var fname = top.document.getElementById(this.prefix+'_editvideo_filename');
	if(fname){
		fname.style.display = item.uploadoption=='file'?'block':'none';
	}
	top.galleryview_window = window;
	var pop = top.PopupManager.createOrFetchPopup(this.prefix+'_editaudio','Gallery Audio',0,0,'div',edit,'disabled');
	pop.Show(properties);
	pop.SetSize(0,0);
}

/* ==================================================================
					Gallery View Drag-and-Drop
   ================================================================== */
   
GalleryViewDragObject = null;
GalleryView.prototype.BeginDragDrop = function(e,id){
	if (!e) var e = window.event;
	if((isie&&e.button==1) || (e.button==0)){
		this.containertop = this.getAbsTop(this.container);
		this.containerleft = this.getAbsLeft(this.container);
		var item = this.GetItemById(id);
		var pos = GalleryViewMousePosition(e,this);
		GalleryViewDragObject = item;
		
		// create cover:
		GalleryViewDragObject.dragcover = document.createElement('div');
		GalleryViewDragObject.dragcover.style.position = 'fixed';
		GalleryViewDragObject.dragcover.style.zIndex = '9999';
		GalleryViewDragObject.dragcover.style.top = '0px';
		GalleryViewDragObject.dragcover.style.left = '0px';
		GalleryViewDragObject.dragcover.style.width = '100%';
		GalleryViewDragObject.dragcover.style.height = '100%';
		GalleryViewDragObject.dragcover.style.cursor = 'move';
		document.body.appendChild(GalleryViewDragObject.dragcover);
		
		// Fade the selected object
		GalleryViewDragObject.element.style.display = 'none';
		
		// create Hotspot array
		GalleryViewDragObject.hotspotArray = [];
		for(var i=0;i<this.items.length;i++){
			if(this.items[i] != item){
				GalleryViewDragObject.hotspotArray.push(new GalleryViewHotspot(this.items[i]));
			}
		}
		if(GalleryViewDragObject.hotspotArray.length) GalleryViewDragObject.hotspotArray.push(new GalleryViewHotspot(GalleryViewDragObject.hotspotArray[GalleryViewDragObject.hotspotArray.length-1]));
		
		// Create the shadow object
		GalleryViewDragObject.shadow = document.createElement('span');
		GalleryViewDragObject.shadow.style.display = 'inline-block';
		GalleryViewDragObject.shadow.className = 'GV_editcell';
		GalleryViewDragObject.shadow.style.width = (this.thumbwidth+this.thumbpadding)+'px';
		GalleryViewDragObject.shadow.style.zIndex = '9000';
		GalleryViewDragObject.shadow.style.opacity = 0.5;
		GalleryViewDragObject.shadow.style.filter = 'alpha(opacity=50)';
		GalleryViewDragObject.shadow.innerHTML = GalleryViewDragObject.element.innerHTML;
		this.container.insertBefore(GalleryViewDragObject.shadow,GalleryViewDragObject.element.nextSibling);
		
		// Create the top scroll bar object
		GalleryViewDragObject.scrolltop = document.createElement('div');
		GalleryViewDragObject.scrolltop.style.position = 'absolute';
		GalleryViewDragObject.scrolltop.style.display = this.container.scrollTop?'block':'none';
		GalleryViewDragObject.scrolltop.style.width = (this.container.offsetWidth-18)+'px';
		GalleryViewDragObject.scrolltop.style.height = '20px';
		GalleryViewDragObject.scrolltop.style.zIndex = '9000';
		GalleryViewDragObject.scrolltop.style.opacity = 0.25;
		GalleryViewDragObject.scrolltop.style.filter = 'alpha(opacity=25)';
		GalleryViewDragObject.scrolltop.style.top = this.container.scrollTop+'px';
		GalleryViewDragObject.scrolltop.style.left = '0px';
		GalleryViewDragObject.scrolltop.style.background = '#000 url("/images/admin/common/scrollup.png") center center no-repeat';
		this.container.appendChild(GalleryViewDragObject.scrolltop);
		
		// Create the bottom scroll bar object
		GalleryViewDragObject.scrollbot = document.createElement('div');
		GalleryViewDragObject.scrollbot.style.position = 'absolute';
		GalleryViewDragObject.scrollbot.style.display = (this.container.scrollTop+this.container.offsetHeight < this.container.scrollHeight)?'block':'none';
		GalleryViewDragObject.scrollbot.style.width = (this.container.offsetWidth-18)+'px';
		GalleryViewDragObject.scrollbot.style.height = '20px';
		GalleryViewDragObject.scrollbot.style.zIndex = '9000';
		GalleryViewDragObject.scrollbot.style.opacity = 0.25;
		GalleryViewDragObject.scrollbot.style.filter = 'alpha(opacity=25)';
		GalleryViewDragObject.scrollbot.style.background = '#000 url("/images/admin/common/scrolldown.png") center center no-repeat';
		GalleryViewDragObject.scrollbot.style.top = (this.container.scrollTop+this.container.offsetHeight-20)+'px';
		GalleryViewDragObject.scrollbot.style.left = '0px';
		GalleryViewDragObject.scrollbot.style.backgroundColor = '#000';
		this.container.appendChild(GalleryViewDragObject.scrollbot);
		
		// attach mousemove and mouseup events
		attachEventHandler(document,'mousemove',GalleryViewMouseMove);
		attachEventHandler(document,'mouseup',GalleryViewMouseUp);
	}
}

function GalleryViewMouseUp(e){
	if(GalleryViewDragObject){
		if(GalleryViewDragObject.currentHotSpot){
			GalleryViewDragObject.galleryview.MoveItem(GalleryViewDragObject,GalleryViewDragObject.currentHotSpot.beforeobj);
		}
		ReleaseGalleryViewEvents();		
		CloseGalleryViewShadow();
	}
}

function ReleaseGalleryViewEvents(){
	releaseEventHandler(document,'mousemove',GalleryViewMouseMove);
	releaseEventHandler(document,'mouseup',GalleryViewMouseUp);
}

function CloseGalleryViewShadow(){
	GalleryViewDragObject.shadow.parentNode.removeChild(GalleryViewDragObject.shadow);
	GalleryViewDragObject.shadow = null;
	GalleryViewDragObject.scrolltop.parentNode.removeChild(GalleryViewDragObject.scrolltop);
	GalleryViewDragObject.scrolltop = null;
	GalleryViewDragObject.scrollbot.parentNode.removeChild(GalleryViewDragObject.scrollbot);
	GalleryViewDragObject.scrollbot = null;
	document.body.removeChild(GalleryViewDragObject.dragcover);
	GalleryViewDragObject.dragcover = null;
	GalleryViewDragObject.element.style.display = 'inline-block';	
	GalleryViewDragObject.currentHotSpot = null;
	GalleryViewDragObject = null;
}

function GalleryViewMouseMove(e){
	if(GalleryViewDragObject){
		clearTimeout(GalleryViewDragObject.scrollinterval);
		GalleryViewDragObject.pos = GalleryViewMousePosition(e,GalleryViewDragObject.galleryview);
		GalleryViewMouseMoveProcess();
	}
}
		
function GalleryViewMouseMoveProcess(){	
	if(GalleryViewDragObject){
		var pos = GalleryViewDragObject.pos;
		
		if(GalleryViewDragObject.currentHotSpot){
			if(GalleryViewDragObject.currentHotSpot.Contains(pos)){
				GalleryViewMouseScroll();
				return;
			}
			GalleryViewDragObject.currentHotSpot = null;
		}
		
		for(var i=0;i<GalleryViewDragObject.hotspotArray.length;i++){
			if(GalleryViewDragObject.hotspotArray[i].Contains(pos)){
				GalleryViewDragObject.currentHotSpot = GalleryViewDragObject.hotspotArray[i];
				break;
			}
		}
		if(GalleryViewDragObject.currentHotSpot){
			GalleryViewDragObject.shadow.parentNode.removeChild(GalleryViewDragObject.shadow);
			GalleryViewDragObject.shadow.style.position = 'static';
			var beforeobj = GalleryViewDragObject.currentHotSpot.beforeobj?GalleryViewDragObject.currentHotSpot.beforeobj.element:null;		
			GalleryViewDragObject.galleryview.container.insertBefore(GalleryViewDragObject.shadow,beforeobj);
		}else{
			if(GalleryViewDragObject.shadow.parentNode != document.body){
				GalleryViewDragObject.shadow.parentNode.removeChild(GalleryViewDragObject.shadow);
				document.body.appendChild(GalleryViewDragObject.shadow);
				GalleryViewDragObject.shadow.style.position = 'absolute';
			}
			GalleryViewDragObject.shadow.style.top = (pos.posy-(GalleryViewDragObject.galleryview.thumbheight+7))+'px';
			GalleryViewDragObject.shadow.style.left = (pos.posx-(GalleryViewDragObject.galleryview.thumbwidth-12))+'px';
		}
		
		GalleryViewMouseScroll();
	}
}

function GalleryViewMouseScroll(){
	var pos = GalleryViewDragObject.pos;
	var top = GalleryViewDragObject.galleryview.container.scrollTop;
	var height = GalleryViewDragObject.galleryview.container.offsetHeight;
	var bottom = top+height;
	var scrollheight = GalleryViewDragObject.galleryview.container.scrollHeight;
	
	// top
	if(pos.gvy >= top && pos.gvy <= top + 20 && top > 0){
		var scr = Math.min(10,top);
		GalleryViewDragObject.scrolltop.style.top = (top-scr) + 'px';
		GalleryViewDragObject.scrolltop.style.display = (top-scr>0)?'block':'none';
		GalleryViewDragObject.scrollbot.style.top = (bottom-scr-20)+'px';
		GalleryViewDragObject.scrollbot.style.display = ((bottom-scr) < scrollheight)?'block':'none';
		GalleryViewDragObject.galleryview.container.scrollTop -=scr;
		GalleryViewDragObject.pos.gvy -= scr;
		GalleryViewDragObject.scrollinterval = setTimeout("GalleryViewMouseMoveProcess()",50);
	}
	// bottom
	else if(pos.gvy >= (bottom - 20) && pos.gvy <= bottom && bottom < scrollheight){
		var scr = Math.min(10,scrollheight-bottom);
		GalleryViewDragObject.scrolltop.style.top = (top+scr) + 'px';
		GalleryViewDragObject.scrolltop.style.display = (top+scr>0)?'block':'none';
		GalleryViewDragObject.scrollbot.style.top = (bottom-20+scr)+'px';
		GalleryViewDragObject.scrollbot.style.display = ((bottom+scr) < scrollheight)?'block':'none';
		GalleryViewDragObject.galleryview.container.scrollTop +=scr;
		GalleryViewDragObject.pos.gvy += scr;
		GalleryViewDragObject.scrollinterval = setTimeout("GalleryViewMouseMoveProcess()",50);
	}
}

function GalleryViewMouseLeave(e){
	var pos = new MousePosition(e);
	var top = window.pageYOffset?window.pageYOffset:document.documentElement.scrollTop;
	var left = window.pageXOffset?window.pageXOffset:document.documentElement.scrollLeft;
	var bottom = top + document.body.clientHeight;
	var right = left + document.body.clientWidth;	
	if(pos.posy < top || pos.posy > bottom || pos.posx < left || pos.posx > right){
		ReleaseGalleryViewEvents();
		CloseGalleryViewShadow();
	}
}

function GalleryViewMousePosition(e,gv){
	var pos = new MousePosition(e);
	pos.gvx = pos.posx - gv.containerleft + gv.container.scrollLeft;
	pos.gvy = pos.posy - gv.containertop + gv.container.scrollTop;
	return pos;
}

GalleryView.prototype.getAbsTop = function(o) {
	oTop = o.offsetTop;
	while(o.offsetParent!=null) {
		oParent = o.offsetParent;
		oTop += oParent.offsetTop;
		o = oParent;
	}
	return oTop;
}
GalleryView.prototype.getAbsLeft = function(o) {
	oLeft = o.offsetLeft;
	while(o.offsetParent!=null) {
		oParent = o.offsetParent;
		oLeft += oParent.offsetLeft;
		o = oParent;
	}
	return oLeft;
}

/* ==================================================================
					Gallery View Item Class
   ================================================================== */

function GalleryViewItem(gv,id,itemtype,filename,thumbname,position,thumbwidth,thumbheight,uploadoption,data){
	this.galleryview = gv;
	this.id = id;
	this.itemtype = itemtype;
	this.filename = filename;
	this.thumbname = thumbname;
	this.position = position;
	this.thumbwidth = thumbwidth;
	this.thumbheight = thumbheight;
	this.uploadoption = uploadoption;
	this.element = document.getElementById(this.galleryview.prefix+'_'+id);
	if(!this.element) this.element = gv.CreateItemElement(this);
	this.data = data?data:{};
	this.image = null;
	
	var imgdiv = this.element.firstChild;
	while(imgdiv && imgdiv.nodeType != 1) imgdiv = imgdiv.nextSibling;
	if(imgdiv){
		var imga = imgdiv.firstChild;
		while(imga && imga.nodeType != 1) imga = imga.nextSibling;
		if(imga){
			this.image = imga.firstChild;
			while(this.image && this.image.nodeType != 1) this.image = this.image.nextSibling;
		}
	}
}

GalleryViewItem.prototype.ResetFilePaths = function(pos){
	var regex = new RegExp(this.id);
	this.id = this.id.replace(/(\d+_)(\d+)(\.jpg)/,'$1'+pos+'$3');
	this.filename = this.filename.replace(/(\d+_)(\d+)(\.jpg)/,'$1'+pos+'$3');
	this.thumbname = this.thumbname.replace(/(\d+_)(\d+)(_th\.jpg)/,'$1'+pos+'$3');
	this.element.id = this.id;
	var links = this.element.getElementsByTagName('A');
	for(var i=0;i<links.length;i++){
		links[i].href = links[i].href.replace(regex,this.id);
	}
	var imgs = this.element.getElementsByTagName('IMG');
	for(var i=0;i<imgs.length;i++){
		if(imgs[i].className == 'GV_movebutton'){
			eval("imgs[i].onmousedown = function(event){ "+this.galleryview.prefix+".BeginDragDrop(event,'"+this.id+"'); }");
		}
	}	
}

GalleryViewItem.prototype.ReloadThumbnail = function(){
	if(this.image){
		var imgpath = this.thumbname.length?this.galleryview.webpath+this.thumbname:this.galleryview.defaultpath+'GV_default'+this.itemtype+'.png';
		this.image.src = imgpath + getRandomCode();
	}
}

/* ==================================================================
					Gallery View Meta Data Field Class
   ================================================================== */

function GalleryViewField(gv,name,label,type){
	this.galleryview = gv;
	this.name = name;
	this.label = label;
	this.type = type;
}

/* ==================================================================
					Gallery View Hotspot
   ================================================================== */
function GalleryViewHotspot(item){
	if(item instanceof GalleryViewHotspot){
		this.item = null;
		this.galleryview = item.galleryview;
		var pad = item.galleryview.thumbspacing;
		this.width = item.item.element.offsetWidth + pad;
		this.height = item.item.element.offsetHeight + pad;
		this.top = item.top
		this.left = item.left + this.width;
		if(this.left + this.width > this.galleryview.width){
			this.left = 0;
			this.top += this.height;
		}
		this.beforeobj = null;
		this.bottom = this.top + this.height;
		this.right = this.left + this.width;
	}else{
		this.item = item;
		this.galleryview = item.galleryview;
		var pad = item.galleryview.thumbspacing;
		this.width = item.element.offsetWidth + pad;
		this.height = item.element.offsetHeight + pad;
		this.top = item.element.offsetTop;
		this.left = item.element.offsetLeft;
		this.beforeobj = item;
		this.bottom = this.top + this.height;
		this.right = this.left + this.width;
	}
}

GalleryViewHotspot.prototype.Contains = function(pos){
	if(pos.gvx < this.galleryview.container.scrollLeft || pos.gvy < this.galleryview.container.scrollTop) return false;
	if(pos.gvy > this.galleryview.container.scrollHeight + this.galleryview.container.scrollTop || pos.gvx > this.galleryview.container.scrollWidth + this.galleryview.container.scrollLeft) return false;
	return pos.gvx >= this.left && pos.gvx < this.right && pos.gvy >= this.top && pos.gvy < this.bottom;
}

/* ==================================================================
					Gallery View Edit Callbacks
   ================================================================== */

function GalleryViewEditCallback(response,type){
	if(response.substring(0,1) == '{'){
		eval("response = " + response + ";");
		var form = top.document.getElementById(response.galleryname+'_edit'+type+'_form');
		eval("var galleryview = top.galleryview_window." + response.galleryname + ";");
		var itemid = response.id;
		var item = galleryview.GetItemById(itemid);
		if(item){
			item.filename = response.filename;
			item.thumbname = response.thumbname;
			item.position = response.position;
			item.uploadoption = response.uploadoption;
			item.ReloadThumbnail();
		}else{
			item = galleryview.AddItem(response.id,type,response.filename,response.thumbname,response.position,response.thumbwidth,response.thumbheight,response.uploadoption);
		}
		var len = 11 + response.galleryname.length + type.length;
		for(var i=0;i<form.elements.length;i++){
			var elem = form.elements[i];
			if(elem.id.substring(0,len) == response.galleryname+'_edit'+type+'_prop_'){
				var key = elem.id.substring(len);
				var value = elem.value;
				item.data[key] = value;
			}
		}
		form.reset();
		var img = top.galleryview_window.document.getElementById(response.galleryname+'_edit'+type+'_thumbnail');
		if(img) img.src = '';
		top.PopupManager.hidePopup(response.galleryname+'_edit'+type);
		top.PopupManager.hideDisabled();
	}else{
		top.PopupManager.showError(response);
	}
}

function GalleryViewEditImageCallback(response){
	top.galleryview_window.GalleryViewEditCallback(response,'image');
}
function GalleryViewEditVideoCallback(response){
	top.galleryview_window.GalleryViewEditCallback(response,'video');
}
function GalleryViewEditAudioCallback(response){
	top.galleryview_window.GalleryViewEditCallback(response,'audio');
}