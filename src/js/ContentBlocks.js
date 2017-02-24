/* =================================================
				ContentBlockManager
================================================= */

var contentBlockManager = {
	layouts : {},
	columns : {},
	blocks : {},
	pageid : 0,
	container : null,
	validpastecolumns: [],
	
	Init : function(page){
		this.layouts = {};
		this.blocks = {};
		this.pageid = page.id;
		this.container = document.getElementById('tbp1');
		// create layouts
		for(var layoutid in page.layouts){
			this.layouts[page.layouts[layoutid].id] = new ContentLayout(page.layouts[layoutid]);
		}
		this.ResetLayoutOptions();
		
		// Calculate which columns are valid paste targets.
		var validpastecolumns = agent.call('../ajax/Content.ajax.php','AJ_getValidPasteColumnIDs','');
		if(validpastecolumns.substring(0,1) == '['){
			this.validpastecolumns = JSON.parse(validpastecolumns);
			if(this.validpastecolumns.length > 0) document.getElementById('clipboard_previewbutton').style.display = '';
			for(var layoutid in this.layouts){
				for(var columnid in this.layouts[layoutid].columns){
					this.layouts[layoutid].columns[columnid].SetPaste(arrayIndexOf(this.validpastecolumns,columnid) >= 0);
				}
			}
		}
	},
	
	PopulateHotspotArray : function(){
		for(var layoutid in this.layouts){
			for(var columnid in this.layouts[layoutid].columns){
				this.layouts[layoutid].columns[columnid].PopulateHotspotArray();
			}
		}
	},
	
	PopulateLayoutHotspotArray : function(){
		var hotspots = {};
		for(var layoutid in this.layouts){
			var layout = this.layouts[layoutid];
			if(layout && layout.obj.offsetTop){
				layout.top = getAbsTop(layout.obj);
				hotspots[layout.top + Math.round(layout.obj.offsetHeight/2)] = layout;
			}
		}
		this.hotspots = keysort(hotspots,-1);
	},
	
	ResetLayoutOptions : function(){
		var size = arraySizeOf(this.layouts);
		for(key in this.layouts){
			var layout = this.layouts[key];
			layout.buttons.innerHTML = '';
			if(size > 1){	
				// edit icon
				var editicon = document.createElement('a');
				editicon.href = 'javascript:' + layout.id + '.EditLayout();';
				editicon.innerHTML = '<img src="../images/admin/content/content/cb-edit.png" />';
				editicon.title = 'Edit Layout Options';
				layout.editicon = editicon;
				layout.buttons.appendChild(editicon);
				
				// move icon					
				var moveicon = document.createElement('img');
				moveicon.id = 'clmv_' + layout.layoutid;
				moveicon.src = "../images/admin/content/content/cb-move.png";
				moveicon.title = 'Drag this block';
				moveicon.style.cursor = 'move';
				moveicon.onmousedown = ContentLayoutMouseDown;
				moveicon.ondragstart = function(){return false;};
				layout.moveicon = moveicon;
				layout.buttons.appendChild(moveicon);
				
				// delete icon
				var deleteicon = document.createElement('a');
				deleteicon.href = 'javascript:' + layout.id + '.DeleteLayout();';
				deleteicon.innerHTML = '<img src="../images/admin/content/content/cb-delete.png" />';
				deleteicon.title = 'Delete Layout';
				layout.deleteicon = deleteicon;
				layout.buttons.appendChild(deleteicon);
			}else{
				// edit icon
				var editicon = document.createElement('a');
				editicon.href = 'javascript:' + layout.id + '.EditLayout();';
				editicon.innerHTML = '<img src="../images/admin/content/content/cb-editonly.png" />';
				editicon.title = 'Edit Layout Options';
				layout.editicon = editicon;
				layout.buttons.appendChild(editicon);	
			}
		}
	}
}

/* =================================================
				ContentBlock
================================================= */

function ContentBlock(blockid,column,after){
	this.blockid = blockid;
	this.column = column;
	this.container = column.obj;
	this.width = column.width;
	this.height = 40;
	this.name = 'Content Block';
	this.id = 'ContentBlock_' + this.blockid;
	eval(this.id + ' = this');
	
	// create containing iframe
	this.iframe = document.createElement('iframe');
	this.iframe.id = blockid + '_frame';
	this.iframe.style.width = this.width + 'px';
	this.iframe.style.height = this.height + 'px';
	this.iframe.style.border = 0;
	this.iframe.frameBorder = 0;
	this.iframe.align = 'top';
	this.iframe.scrolling = "no";
	this.iframe.className = "iframe";
	this.iframe.src = '../popups/ContentBlock.pop.php?id=' + blockid + '&w=' + (this.width-coverwidthpadding) + '&h=' + (this.height-coverheightpadding);
	if(after==-1){
		this.container.appendChild(this.iframe);
	}else if(after && this.column.blocks[after]){
		this.container.insertBefore(this.iframe,this.column.blocks[after].iframe.nextSibling);
	}else{
		this.container.insertBefore(this.iframe,this.container.firstChild.nextSibling);
	}
}

function ContentBlockFrameLoaded(blockid){
	// Google Chrome incorrectly reports the clientHeight of a newly loaded frame.
	// A 20ms delay fixes the problem.
	if(arguments.length==1 && window.chrome){
		setTimeout("ContentBlockFrameLoaded("+blockid+",true);",20);
		return;
	}
	var block = contentBlockManager.blocks[blockid];
	block.iframe.doc = block.iframe.contentDocument?block.iframe.contentDocument:block.iframe.contentWindow.document;
	block.iframe.body = block.iframe.doc.getElementById('site');
	block.iframe.ContentBlock = block;
	block.iframe.doc.getElementById(blockid+'_loading').style.display = 'none';
	
	block.cover = block.iframe.doc.getElementById(blockid+'_cover');
	block.cover.ContentBlock = block;
	block.name = block.iframe.doc.getElementById(blockid+'_name').innerHTML;
	
	block.content = block.iframe.doc.getElementById('cbl_'+block.blockid);
	
	block.cover.style.width = Math.max((block.column.width - coverwidthpadding),0) + 'px';
	
	if(isie){
		block.iframe.onmouseenter = function(){
			var e = window.event;
			if(e.srcElement.ContentBlock && (typeof(ContentBlockDragObject) == 'undefined' || ContentBlockDragObject == null)){
				e.srcElement.ContentBlock.ShowCover();
			}
		}
		block.iframe.onmouseleave = function(){
			var e = window.event
			if(e.srcElement.ContentBlock && (typeof(ContentBlockDragObject) == 'undefined' || ContentBlockDragObject == null)){
				e.srcElement.ContentBlock.HideCover();
			}
		}
	}else{
		block.cover.onmouseover = function(e){
			if(e.target.ContentBlock && (typeof(ContentBlockDragObject) == 'undefined' || ContentBlockDragObject == null)){
				e.target.ContentBlock.ShowCover();
			}
		}
		block.cover.onmouseout = function(e){
			if((typeof(ContentBlockDragObject) == 'undefined' || ContentBlockDragObject == null) && typeof(isParentOf) == 'function'){
				if(e.target.ContentBlock){
					if(!isParentOf(e.target,e.relatedTarget)){
						e.target.ContentBlock.HideCover();
					}
				}else{
					if(e.target.parentNode){
						var block = e.target;
						while(block.parentNode && !block.ContentBlock){
							block = block.parentNode;
						}
						if(!isParentOf(block,e.relatedTarget)){
							block.ContentBlock.HideCover();
						}
					}
				}
			}
		}
	}
	
	block.height = block.iframe.body.clientHeight;
	// IE will report the height as 0 if the element is hidden.
	// Luckily, IE will also not reload the iframe if it is moved,
	// so we move it off the visible screen, and make it visible, get the height, then move it back.
	if(isie && block.height == 0){
		var tab1 = document.getElementById('tbp1');
		var parent = tab1.parentNode;
		var div = document.createElement('div');
		var sibling = tab1.nextSibling;
		var olddisplay = tab1.style.display;
		div.style.left = '-3000px';
		div.style.position = 'absolute';
		div.appendChild(tab1);
		document.body.appendChild(div);
		tab1.style.display = 'block';		
		block.height = block.iframe.body.clientHeight;
		block.column.width = block.column.obj.offsetWidth;
		block.cover.style.width = Math.max((block.column.width - coverwidthpadding),0) + 'px';
		tab1.style.display = olddisplay;
		parent.insertBefore(tab1,sibling);
		document.body.removeChild(div);
	}		
	block.iframe.style.height = (block.height) + 'px';
	block.cover.style.height = Math.max((block.height-coverheightpadding),0) + 'px';
	
	// add icons	
	block.gears = block.iframe.doc.createElement('img');
	block.gears.className = 'cbl_gears';
	block.gears.src = '../images/admin/content/content/gears.png';
	block.cover.appendChild(block.gears);
	
	block.buttons = block.iframe.doc.createElement('div');
	block.buttons.className = block.height>58?'cbl_buttons':'cbl_buttons_small';
	block.cover.insertBefore(block.buttons,block.cover.firstChild);
		
	block.editcon = block.iframe.doc.createElement('a');
	block.editcon.href = 'javascript:parent.' + block.id + '.Edit();';
	block.editcon.innerHTML = '<img src="../images/admin/content/content/cb-edit.png" />';
	block.editcon.title = 'Edit';
	block.buttons.appendChild(block.editcon);
	
	block.copyicon = block.iframe.doc.createElement('a');
	block.copyicon.href = 'javascript:parent.' + block.id + '.Copy();';
	block.copyicon.innerHTML = '<img src="../images/admin/content/content/cb-copy.png" />';
	block.copyicon.title = 'Copy';
	block.buttons.appendChild(block.copyicon);
	
	block.mvicon = block.iframe.doc.createElement('img');
	block.mvicon.id = 'cbmv_' + block.blockid;
	block.mvicon.src = "../images/admin/content/content/cb-move.png";
	block.mvicon.title = 'Drag this block';
	block.mvicon.style.cursor = 'move';
	block.mvicon.onmousedown = ContentBlockMouseDown;
	block.mvicon.ondragstart = function(){return false;};
	block.buttons.appendChild(block.mvicon);
		
	block.delicon = block.iframe.doc.createElement('a');
	block.delicon.href = 'javascript:parent.' + block.id + '.Delete();';
	block.delicon.innerHTML = '<img src="../images/admin/content/content/cb-delete.png" />';
	block.delicon.title = 'Delete';
	block.buttons.appendChild(block.delicon);
	
	// add icons	
	block.colbuttons = block.iframe.doc.createElement('div');
	block.colbuttons.className = block.height>58?'cbl_colbuttons':'cbl_colbuttons_small';
	block.cover.insertBefore(block.colbuttons,block.cover.firstChild);
		
	block.pasteicon = block.iframe.doc.createElement('a');
	block.pasteicon.href = 'javascript:parent.' + block.column.id + '.Paste('+block.blockid+');';
	block.pasteicon.innerHTML = '<img src="../images/admin/content/content/cb-paste.png" />';
	block.pasteicon.title = 'Paste block below';
	block.pasteicon.style.display = block.column.pasteactive?'inline':'none';
	block.colbuttons.appendChild(block.pasteicon);
		
	block.addicon = block.iframe.doc.createElement('a');
	block.addicon.href = 'javascript:parent.' + block.column.id + '.AddColumn('+block.blockid+');';
	block.addicon.innerHTML = '<img src="../images/admin/content/content/cb-add'+(block.column.pasteactive?'':'only')+'.png" />';
	block.addicon.title = 'Insert block below';
	block.colbuttons.appendChild(block.addicon);	
	
	// append object properties
	var comment = block.content.getAttribute('prop');
	block.properties = JSON.parse(comment.replace(/\'/g,'"'));
	block.col = block.properties.Columnid;
	block.pos = block.properties.Position;
	block.loaded = true;
}

ContentBlock.prototype.HideCover = function(){
	this.cover.className = 'cbl_invisible';
}

ContentBlock.prototype.ShowCover = function(){
	this.cover.className = 'cbl_disabled';
}

ContentBlock.prototype.SetPaste = function(active){
	if(active){
		this.pasteicon.style.display = 'inline';
		this.addicon.innerHTML = '<img src="../images/admin/content/content/cb-add.png" />';
	}else{
		this.pasteicon.style.display = 'none';
		this.addicon.innerHTML = '<img src="../images/admin/content/content/cb-addonly.png" />';
	}
}

ContentBlock.prototype.Delete = function(){
	this.HideCover();
	PopupManager.showDisabled();
	if(confirm('Are you sure you want to delete this content block?')){
		PopupManager.showLoading();
		PopupManager.hideDisabled();
		var res = agent.call('../ajax/Content.ajax.php','AJ_Delete_Block','',pageid, this.blockid);
		if(res=='success'){
			var par = this.iframe.parentNode;
			par.removeChild(this.iframe);
			delete contentBlockManager.blocks[this.blockid];
			PopupManager.hideLoading();
			PopupManager.showCompleted();
		}else{
			PopupManager.showError(res);
			PopupManager.hideLoading();
		}
	}else{
		PopupManager.hideDisabled();	
	}
}

ContentBlock.prototype.Edit = function(){
	this.HideCover();
	PopupManager.showLoading();
	var pop = PopupManager.createOrFetchPopup(this.properties.BlockClass,'Content Block Editor',600,400,'iframe','/content/'+this.properties.BlockClass+'/'+this.properties.BlockClass+'.edit.php','loading');
	var replacements = {
		'pageid' : pageid,
		'blockid' : this.blockid,
		'layout' : this.column.layout.layoutid,
		'col' : this.col,
		'pos' : this.pos
	}
	pop.ShowRefresh(replacements);
}

ContentBlock.prototype.UpdateBlock = function(){
	this.iframe.src = '../popups/ContentBlock.pop.php?id=' + this.blockid + '&w=' + (this.width-coverwidthpadding) + '&h=' + (this.height-coverheightpadding);
}

ContentBlock.prototype.Copy = function(){
	try{
		PopupManager.showLoading();
		var res = agent.call('../../ajax/Content.ajax.php','AJ_AddContentBlockToClipboard','',this.blockid, this.column.width);
		if(res == 'success'){
			document.getElementById('clipboard_previewbutton').style.display = '';
			this.validpastecolumns = this.properties.ValidColumns;
			for(var layoutid in contentBlockManager.layouts){
				for(columnid in contentBlockManager.layouts[layoutid].columns){
					contentBlockManager.layouts[layoutid].columns[columnid].SetPaste(arrayIndexOf(this.properties.ValidColumns,columnid) >= 0);
				}
			}
			PopupManager.hideLoading();
			PopupManager.showCompleted();
		}else{
			PopupManager.showError('There was an error adding this item to the clipboard');
			PopupManager.hideLoading();
		}
	}catch(e){
		PopupManager.showError(e.message);
		PopupManager.hideLoading();
	}
};

function ContentBlockCompleteEdit(res, blockid, col, layout, module, after, error){
	if(res == 'false'){
		PopupManager.showError(error);
	}else{
		if(blockid > 0 && contentBlockManager.blocks[blockid]){
			contentBlockManager.blocks[blockid].UpdateBlock();
			PopupManager.showCompleted();
		}else{
			contentBlockManager.layouts[layout].columns[col].AppendContentBlock(res,after);
			PopupManager.showCompleted();
		}
	}
	PopupManager.hidePopup(module);
	PopupManager.hideLoading();
}

/* =================================================
				ContentColumn
================================================= */

function ContentColumn(id,layout){
	this.layout = layout;
	this.columnid = id;
	this.obj = document.getElementById('contentcolumn_' + layout.layoutid + '_' + id);
	this.obj.className += ' contentcolumn';
	this.top = getAbsTop(this.obj);
	this.left = getAbsLeft(this.obj);
	this.border = parseInt(getCurrentStyle(this.obj,'border-left-width'));
	this.width = this.obj.offsetWidth - (2*this.border);
	this.height = 25;
	this.blocks = {};
	this.pasteactive = false;
	this.id = 'ContentColumn_' + layout.layoutid + '_' + this.columnid;
	eval(this.id + ' = this');
	
	this.obj.style.minHeight = '60px';
	
	// create add block button
	this.button = document.createElement('div');
	this.button.className ='ContentColumnButtons';
	this.button.id = this.obj.id + '_button';
	this.button.style.width = this.width+'px';
	this.button.style.height = this.height + 'px';
	this.button.ContentColumn = this;
	
	// add icon
	var addicon = document.createElement('a');
	addicon.href = 'javascript:' + this.id + '.AddColumn();';
	addicon.innerHTML = '<img src="../images/admin/content/content/col-add.png" />';
	addicon.title = 'Add Content Block';
	this.addicon = addicon;
	this.button.appendChild(addicon);
	// paste icon
	var pasteicon = document.createElement('a');
	pasteicon.href = '';
	pasteicon.innerHTML = '';
	pasteicon.title = 'Paste';
	this.pasteicon = pasteicon;
	this.button.appendChild(pasteicon);
	
	this.obj.appendChild(this.button);
}

ContentColumn.prototype.IsLoaded = function(){
	for(key in this.blocks){
		if(!this.blocks[key].loaded) return false;
	}
	return true;
}

ContentColumn.prototype.SetPaste = function(active){
	if(!this.IsLoaded()){
		setTimeout(this.id+'.SetPaste('+active+')',500);
	}else{
		this.pasteactive = active;
		if(active){
			this.pasteicon.href = 'javascript: ' + this.id + '.Paste();';
			this.pasteicon.innerHTML = '<img src="../images/admin/content/content/col-paste.png" />';
		}else{
			this.pasteicon.href = '';
			this.pasteicon.innerHTML = '';
		}
		for(key in this.blocks){
			this.blocks[key].SetPaste(active);
		}
	}
}

ContentColumn.prototype.AddColumn = function(after){
	PopupManager.showDisabled();
	var pop = PopupManager.createOrFetchPopup('CreateBlock' + this.columnid,'Create New Content Block',500,0,'div','createblock_' + this.columnid,'disabled');
	var replacements = {};
	replacements['createblock_' + this.columnid + '_after'] = (after?after:'');
	replacements['layoutid_' + this.columnid] = this.layout.layoutid;
	pop.Show(replacements);
}

ContentColumn.prototype.CreateBlock = function(classname){
	PopupManager.showLoading();
	PopupManager.hidePopup('CreateBlock' + this.columnid);
	PopupManager.hideDisabled();
	var pop = PopupManager.createOrFetchPopup(classname,'Content Block Editor',600,400,'iframe','/content/'+classname+'/'+classname+'.edit.php','loading');
	var replacements = {
		'pageid' : pageid,
		'col' : this.columnid,
		'layout' : this.layout.layoutid,
		'pos' : document.getElementById('createblock_' + this.columnid + '_after').value
	}
	pop.ShowRefresh(replacements);
}

ContentColumn.prototype.AppendContentBlock = function(blockid,after){
	var block = new ContentBlock(blockid,this,after);
	contentBlockManager.blocks[blockid] = block;
	this.blocks[blockid] = block;
}

ContentColumn.prototype.Paste = function(after){
	PopupManager.showLoading();
	var blockid = agent.call('../ajax/Content.ajax.php','AJ_CopyContentblock','',pageid, this.layout.layoutid,this.columnid);
	if(isnumeric(blockid)){
		var block = new ContentBlock(blockid,this,after);
		contentBlockManager.blocks[blockid] = block;
		this.blocks[blockid] = block;
		PopupManager.hideLoading();
	}else{
		PopupManager.showError(blockid);
		PopupManager.hideLoading();
	}
}

ContentColumn.prototype.PopulateHotspotArray = function(){
	// set for use later.
	this.top = getAbsTop(this.obj);
	var hotspots = {};
	for(blockid in this.blocks){
		var block = this.blocks[blockid];
		if(block && block.iframe.offsetTop){
			hotspots[block.iframe.offsetTop] = block;
		}
	}
	this.hotspots = keysort(hotspots);
}

ContentColumn.prototype.ResetBlockPositions = function(){
	// order blocks
	var order = {};
	for(blockid in this.blocks){
		var block = this.blocks[blockid];
		if(block){
			order[block.iframe.offsetTop] = block;
		}
	}
	order = keysort(order);
	var ind = 1;
	for(key in order){
		order[key].pos = ind;
		ind++;
	}
}

/* =================================================
				ContentLayout
================================================= */

function ContentLayout(layout){
	this.layoutid = layout.id;
	this.position = parseInt(layout.position);
	this.layoutClass = layout.classname;
	this.custom = layout.custom;
	this.obj = document.getElementById('contentlayout_' + this.layoutid);
	this.obj.className = 'contentlayout';
	this.width = this.obj.firstChild.offsetWidth;
	this.obj.style.width = this.width+'px';
	this.obj.layout = this;
	this.height = 25;
	this.columns = {};
	this.pasteactive = false;
	this.optionsopen = false;
	this.id = 'ContentLayout_' + this.layoutid;
	eval(this.id + ' = this');
	
	this.obj.style.minHeight = '50px';
	
	// populate columns array
	var columns = agent.call('../ajax/Content.ajax.php','AJ_getPageContentIDs','',contentBlockManager.pageid,this.layoutid);
	if(columns.substring(0,1) == '{'){
		columns = JSON.parse(columns);
		for(columnid in columns){
			this.columns[columnid] = new ContentColumn(columnid,this);
			var blockids = columns[columnid];
			this.columns[columnid].blockcount = blockids.length;
			for(i=0;i<blockids.length;i++){
				var blockid = blockids[i];
				contentBlockManager.blocks[blockid] = new ContentBlock(blockid,this.columns[columnid],-1);
				this.columns[columnid].blocks[blockid] = contentBlockManager.blocks[blockid];
			}			
		}
	}
	
	// create show options tab
	this.tab = document.createElement('div');
	this.tab.className ='ContentLayoutTab';
	this.tab.id = this.obj.id + '_tab';
	this.tab.ContentLayout = this;	
	this.obj.appendChild(this.tab);
	
	// options icon
	var optionsicon = document.createElement('a');
	optionsicon.href = 'javascript:' + this.id + '.ShowLayoutOptions();';
	optionsicon.innerHTML = '<img src="../images/admin/content/content/layout.png" width="20" height="20" />';
	optionsicon.title = 'Show Layout Options';
	this.optionsicon = optionsicon;
	this.tab.appendChild(optionsicon);
	
	// create shadow
	this.cover = document.createElement('div');
	this.cover.className ='ContentLayoutShadow';
	this.cover.id = this.obj.id + '_shadow';
	this.cover.ContentLayout = this;	
	eval('this.cover.onclick = function(){contentBlockManager.layouts["'+this.layoutid+'"].ShowLayoutOptions();}');
	this.obj.appendChild(this.cover);
	
	// create button container
	this.buttons = document.createElement('div');
	this.buttons.className ='ContentLayoutButtons';
	this.buttons.id = this.obj.id + '_buttons';
	this.buttons.ContentLayout = this;	
	this.obj.appendChild(this.buttons);
	
	// add icon
	var addicon = document.createElement('a');
	addicon.href = 'javascript:' + this.id + '.AddLayout();';
	addicon.innerHTML = '<img src="../images/admin/content/content/cb-addonly.png" />';
	addicon.title = 'Insert a new layout below this one';
	addicon.className ='ContentLayoutAddButton';
	this.addicon = addicon;
	this.obj.appendChild(addicon);
}

ContentLayout.prototype.ShowLayoutOptions = function(){
	if(this.optionsopen){
		this.cover.style.display = 'none';
		this.optionsicon.style.opacity = '1';
		this.optionsopen = false;
		this.buttons.style.display = 'none';
		this.addicon.style.display = 'none';
	}else{
		this.cover.style.display = 'block';
		this.optionsicon.style.opacity = '0.5';
		this.optionsopen = true;
		this.buttons.style.display = 'block';
		this.addicon.style.display = 'block';
	}
}

ContentLayout.prototype.IsLoaded = function(){
	for(id in this.columns){
		for(key in this.columns[id].blocks){
			if(!this.blocks[key].loaded) return false;
		}
	}
	return true;
}

ContentLayout.prototype.AddLayout = function(){
	PopupManager.showDisabled();
	var pop = PopupManager.createOrFetchPopup('CreateLayout','Create New Layout',0,0,'div','content_layout','disabled');
	var replacements = {};
	replacements['layout'] = 'OneColumn';
	replacements['layoutid'] = -1;
	replacements['layout_after'] = this.layoutid;
	pop.SetTitle('Create New Layout');
	pop.Show(replacements);
	selectTemplate('layout','OneColumn');
	if(this.custom){
		for(key in this.custom){
			if(key != '_settings'){
				setCustomSetting(this.custom,'layout_custom',key,true);
			}
		}
	}
}

ContentLayout.prototype.EditLayout = function(){
	PopupManager.showDisabled();
	var pop = PopupManager.createOrFetchPopup('CreateLayout','Edit Layout',0,0,'div','content_layout','disabled');
	var replacements = {};
	replacements['layout'] = this.layoutClass;
	replacements['layoutid'] = this.layoutid;
	replacements['layout_after'] = '';
	pop.SetTitle('Edit Layout');
	pop.Show(replacements);
	selectTemplate('layout',this.layoutClass);
	if(this.custom){
		for(key in this.custom){
			if(key != '_settings'){
				setCustomSetting(this.custom,'layout_custom',key,false);
			}
		}
	}
}

ContentLayout.prototype.DeleteLayout = function(){
	PopupManager.showLoading();
	if(confirm('Are you sure you want to delete this entire layout? All content blocks in all columns will be deleted as well.')){
		var res = agent.call('../ajax/Content.ajax.php','AJ_DeleteLayout','',pageid,this.layoutid);
		if(res=='success'){
			contentBlockManager.container.removeChild(this.obj);
			delete contentBlockManager.layouts[this.layoutid];
			contentBlockManager.ResetLayoutOptions();
		}else{
			PopupManager.showError(res);
		}
	}
	PopupManager.hideLoading();
}

ContentLayout.prototype.ResetBlockPositions = function(){
	for(var i=0;i<this.columns.length;i++){
		this.columns[i].ResetBlockPositions();
	}
}

function processLayoutChange(){
	PopupManager.showLoading();
	var layoutid = document.getElementById('layoutid').value;
	var selected = document.getElementById('layout').value;
	var custom = populateCustomArray('layout_custom');
	if(layoutid==-1){
		var after = document.getElementById('layout_after').value;
		var insertpos = contentBlockManager.layouts[after].position + 1;
		var layoutdata = agent.call('../ajax/Content.ajax.php','AJ_CreateLayout','',pageid,insertpos,selected,custom);
		if(layoutdata.substring(0,1) == '{'){
			var layout = JSON.parse(layoutdata);
			var before = contentBlockManager.layouts[after].obj.nextSibling;
			var holder = document.createElement('div');
			holder.innerHTML = layout.content;
			contentBlockManager.container.insertBefore(holder.firstChild,before);
			contentBlockManager.layouts[layout.id] = new ContentLayout(layout);
			contentBlockManager.layouts[after].ShowLayoutOptions();
			contentBlockManager.ResetLayoutOptions();
			for(var columnid in contentBlockManager.layouts[layout.id].columns){
				contentBlockManager.layouts[layout.id].columns[columnid].SetPaste(arrayIndexOf(contentBlockManager.validpastecolumns,columnid) >= 0);
			}
		}else{
			PopupManager.showError(layoutdata);
		}
	}else{
		var current = contentBlockManager.layouts[layoutid];
		var layoutdata = agent.call('../ajax/Content.ajax.php','AJ_UpdateLayout','',pageid,layoutid,selected,custom);
		if(layoutdata.substring(0,1) == '{'){
			var layout = JSON.parse(layoutdata);
			for(columnid in current.columns){
				for(blockid in current.columns[columnid].blocks){
					delete contentBlockManager.blocks[blockid];
					delete current.columns[columnid].blocks[blockid];
				}
				delete current.columns[columnid];
			}
			var before = current.obj.nextSibling;
			current.obj.parentNode.removeChild(current.obj);
			delete contentBlockManager.layouts[current.layoutid];
			var holder = document.createElement('div');
			holder.innerHTML = layout.content;
			contentBlockManager.container.insertBefore(holder.firstChild,before);
			var newlayout = new ContentLayout(layout);
			contentBlockManager.layouts[newlayout.layoutid] = newlayout;
			contentBlockManager.ResetLayoutOptions();
			for(var columnid in newlayout.columns){
				newlayout.columns[columnid].SetPaste(arrayIndexOf(contentBlockManager.validpastecolumns,columnid) >= 0);
			}
		}else{
			PopupManager.showError(layoutdata);
		}
	}
	PopupManager.hideLoading();
	PopupManager.hideDisabled();
	PopupManager.hidePopup('CreateLayout');
}

/* =================================================
				Drag and Drop
================================================= */

var ContentBlockDragObject = null;

function ContentBlockMouseDown(e){
	e = e || this.document.parentWindow.event;
	var target = e.target != null ? e.target : e.srcElement;
	if(target.id){
		// identify the item
		var blockid = target.id.substring(5);
		var block = contentBlockManager.blocks[blockid];
		ContentBlockDragObject = block;
		
		ContentBlockDragObject.coverdiv = document.createElement('div');
		ContentBlockDragObject.coverdiv.style.height = '100%';
		ContentBlockDragObject.coverdiv.style.width = '100%';
		ContentBlockDragObject.coverdiv.style.top = '0px';
		ContentBlockDragObject.coverdiv.style.left = '0px';
		ContentBlockDragObject.coverdiv.style.position = 'fixed';
		ContentBlockDragObject.coverdiv.style.zIndex = 9999;
		ContentBlockDragObject.coverdiv.style.cursor = 'move';
		document.body.appendChild(ContentBlockDragObject.coverdiv);
		
		// set the iframe offset position for Opera
		ContentBlockDragObject.iframeOffsetTop = getAbsTop(ContentBlockDragObject.iframe);
		ContentBlockDragObject.iframeOffsetLeft = getAbsLeft(ContentBlockDragObject.iframe);
		
		// create the shadow branch to follow the mouse
		ContentBlockDragObject.HideCover();
		ContentBlockDragObject.iframe.style.display = 'none';
		
		// set the Hotspots. Do this after the moving item has been hidden.
		contentBlockManager.PopulateHotspotArray();
		
		// create the shadow object
		var div = document.createElement('div');
		div.style.position = 'absolute';
		var pos = new MousePosition(e);
		div.style.left = pos.posx+'px';
		div.style.top = pos.posy+'px';
		var shadow = document.createElement('div');
		shadow.innerHTML = ContentBlockDragObject.name;
		div.appendChild(shadow);
		div.className = 'ContentBlockShadow';
		div.style.cursor = 'move';
		ContentBlockDragObject.shadow = div;
		ContentBlockDragObject.shadow.style.position = 'static';
		ContentBlockDragObject.shadow.style.width = Math.max((ContentBlockDragObject.width - coverwidthpadding),0) + 'px';
		ContentBlockDragObject.shadow.style.height = Math.max((ContentBlockDragObject.height - coverheightpadding),0) + 'px';
		ContentBlockDragObject.column.obj.insertBefore(ContentBlockDragObject.shadow,ContentBlockDragObject.iframe);
		
		// attach mousemove and mouseup events
		if(!window.opera){
			attachEventHandler(document,'mousemove',ContentBlockMouseMove);
			attachEventHandler(document,'mouseup',ContentBlockMouseUp);
		}else{
			attachEventHandler(ContentBlockDragObject.iframe.doc,'mousemove',ContentBlockMouseMoveFromChild);
			attachEventHandler(ContentBlockDragObject.iframe.doc,'mouseup',ContentBlockMouseUp);		
		}
		if(!isie) attachEventHandler(document,'mouseout',ContentBlockMouseLeave);
		
		return false;
	}
}

function ContentBlockMouseUp(e){
	// Opera loads this in the context of the iframe, not the containing window,
	// So we rerun the function asynchronously in the main window context.
	if(window.opera && window.parent != this){
		return setTimeout("window.parent.ContentBlockMouseUp()",0);
	}
	if(ContentBlockDragObject){
		ReleaseContentBlockEvents();
		
		// handle drop
		if(ContentBlockDragObject.overcolumn){
			var position = ContentBlockDragObject.overitem?ContentBlockDragObject.overitem.pos - (ContentBlockDragObject.overcolumn==ContentBlockDragObject.column&&ContentBlockDragObject.pos<ContentBlockDragObject.overitem.pos?1:0):ContentBlockDragObject.overcolumn.blockcount + (ContentBlockDragObject.overcolumn==ContentBlockDragObject.column?0:1);
			if(ContentBlockDragObject.overcolumn!=ContentBlockDragObject.column || ContentBlockDragObject.pos != position){
				PopupManager.showLoading();
				var res = agent.call('../ajax/Content.ajax.php','AJ_MoveBlockTo','',ContentBlockDragObject.blockid,ContentBlockDragObject.overcolumn.layout.layoutid,ContentBlockDragObject.overcolumn.columnid,position);
				if(res=='success'){
					ContentBlockDragObject.iframe.style.background = "url('../images/loader.gif') top no-repeat";
					if(ContentBlockDragObject.overitem){
						ContentBlockDragObject.overcolumn.obj.insertBefore(ContentBlockDragObject.iframe, ContentBlockDragObject.overitem.iframe);
					}else{
						ContentBlockDragObject.overcolumn.obj.appendChild(ContentBlockDragObject.iframe);
					}
					ContentBlockDragObject.width = ContentBlockDragObject.overcolumn.width;
					ContentBlockDragObject.iframe.style.width = ContentBlockDragObject.width + 'px';
					ContentBlockDragObject.iframe.style.height = '80px';
					ContentBlockDragObject.cover.style.height = '80px';
					
					ContentBlockDragObject.column.blockcount -= 1;
					
					if(isie){
						ContentBlockDragObject.cover.style.width = ContentBlockDragObject.width + 'px';
						setTimeout('ContentBlockReSize(contentBlockManager.blocks[' + ContentBlockDragObject.blockid + ']);',1);
					}
					
					// re-align column block arrays
					if(ContentBlockDragObject.column != ContentBlockDragObject.overcolumn){
						var index = arrayIndexOf(ContentBlockDragObject.column.blocks,ContentBlockDragObject);
						delete ContentBlockDragObject.column.blocks[index];
						ContentBlockDragObject.column.ResetBlockPositions();
						ContentBlockDragObject.overcolumn.blocks[ContentBlockDragObject.blockid] = ContentBlockDragObject;
						ContentBlockDragObject.column = ContentBlockDragObject.overcolumn;
					}				
					
					ContentBlockDragObject.column.blockcount += 1;	
					PopupManager.hideLoading();
				}else{
					PopupManager.showError(res);			
					PopupManager.hideLoading();
				}
			}
		}	
		
		CloseContentBlockShadow();
	}
}

function ReleaseContentBlockEvents(){
	if(!window.opera){
		releaseEventHandler(ContentBlockDragObject.iframe.doc,'mousemove',ContentBlockMouseMoveFromChild);
		releaseEventHandler(ContentBlockDragObject.iframe.doc,'mouseup',ContentBlockMouseUp);
	}else{
		releaseEventHandler(document,'mousemove',ContentBlockMouseMove);
		releaseEventHandler(document,'mouseup',ContentBlockMouseUp);
	}
	if(!isie) releaseEventHandler(document,'mouseout',ContentBlockMouseLeave);
}

function CloseContentBlockShadow(){
	ContentBlockDragObject.shadow.parentNode.removeChild(ContentBlockDragObject.shadow);
	ContentBlockDragObject.shadow = null;
	document.body.removeChild(ContentBlockDragObject.coverdiv);
	ContentBlockDragObject.coverdiv = null;
	ContentBlockDragObject.iframe.style.display = 'block';	
	ContentBlockDragObject.HideCover();
	ContentBlockDragObject.column.ResetBlockPositions();
	ContentBlockDragObject.overcolumn = null;
	ContentBlockDragObject.overitem = null;
	ContentBlockDragObject = null;
}

// Used by IE to re-align frame sizes after moving. Must be done asynchronously, 
// because IE won't re-calculate display dimensions until after the whole method has been processed.
function ContentBlockReSize(block){
	block.height = block.content.offsetHeight;
	block.iframe.style.height = block.height + 'px';
	block.cover.style.height = Math.max((block.height-coverheightpadding),0) + 'px';
}

function ContentBlockMouseMove(e){
	if(ContentBlockDragObject){
		var pos = new MousePosition(e);
		if(isie && e.button == 0){
			ReleaseContentBlockEvents();
			return CloseContentBlockShadow();
		}
		ContentBlockMouseMoveProcess(pos);
	}
}

// Used for move event fired from within the iframe. Used for Opera.
function ContentBlockMouseMoveFromChild(e){
	if(ContentBlockDragObject){
		var pos = new MousePosition(e);
		//TODO: Work out why Opera moves the mouse X position when the button is released. 
		pos.posx += ContentBlockDragObject.iframeOffsetLeft;
		pos.posy += ContentBlockDragObject.iframeOffsetTop;
		ContentBlockMouseMoveProcess(pos);
	}
}
		
function ContentBlockMouseMoveProcess(pos){
	if(ContentBlockDragObject.scroller) clearTimeout(ContentBlockDragObject.scroller);
	var column = null;
	
	for(var layoutid in contentBlockManager.layouts){
		for(var columnid in contentBlockManager.layouts[layoutid].columns){
			var col = contentBlockManager.layouts[layoutid].columns[columnid];
			if(arrayIndexOf(ContentBlockDragObject.properties.ValidColumns,col.columnid) >= 0){
				if(pos.posx >= col.left && pos.posx <= col.left + col.obj.offsetWidth && pos.posy >= col.top && pos.posy <= col.top + col.obj.offsetHeight){
					column = col;
					break;
				}
			}
		}
	}
	if(!column){
		// Not in the tree
		if(ContentBlockDragObject.shadow.parentNode != document.body){
			ContentBlockDragObject.shadow.parentNode.removeChild(ContentBlockDragObject.shadow);
			document.body.appendChild(ContentBlockDragObject.shadow);
			ContentBlockDragObject.shadow.style.position = 'absolute';
			ContentBlockDragObject.shadow.style.width = ContentBlockDragObject.width+'px';
		}
		ContentBlockDragObject.shadow.style.left = pos.posx+'px';
		ContentBlockDragObject.shadow.style.top = pos.posy+'px';
		ContentBlockDragObject.overcolumn = null;
		ContentBlockDragObject.overitem = null;
	}else{
		// In a column
		pos.treey = pos.posy - column.top;
		
		// identify which item is being hovered over
		var overitem = null;
		var h = 0;
		for(h in column.hotspots){
			h = parseInt(h);
			if(h < pos.treey){
				overitem = column.hotspots[h];
			}else{
				break;
			}
		}
		if(overitem && h + overitem.iframe.offsetHeight < pos.treey){
			overitem = null;
		}
		
		if(overitem){
			if(ContentBlockDragObject.overitem != overitem){
				ContentBlockDragObject.shadow.parentNode.removeChild(ContentBlockDragObject.shadow);
				ContentBlockDragObject.shadow.style.position = 'static';
				ContentBlockDragObject.shadow.style.width = Math.max((column.width - coverwidthpadding),0) + 'px';
				column.obj.insertBefore(ContentBlockDragObject.shadow,overitem.iframe);
			}
		}else{
			if(ContentBlockDragObject.overcolumn != column || ContentBlockDragObject.overitem != null){
				ContentBlockDragObject.shadow.parentNode.removeChild(ContentBlockDragObject.shadow);
				ContentBlockDragObject.shadow.style.position = 'static';
				ContentBlockDragObject.shadow.style.width = Math.max((column.width - coverwidthpadding),0) + 'px';
				column.obj.insertBefore(ContentBlockDragObject.shadow,null);
			}
		}
		
		ContentBlockDragObject.overitem = overitem;
		ContentBlockDragObject.overcolumn = column;
	}
	
	// Handle Bottom or Top Scroll
	var pagetop = window.pageYOffset?window.pageYOffset:document.documentElement.scrollTop;
	var pageleft = window.pageXOffset?window.pageXOffset:document.documentElement.scrollLeft;
	var pageheight = document.body.clientHeight;
	var container = document.getElementById('tbp1');
	if(pos.posy > (pageheight + pagetop - 10) && pageheight + pagetop + 10 < container.scrollHeight + getAbsTop(container)){
		window.scrollBy(0,10);
		pos.posy += 10;
		ContentBlockDragObject.scroller = setTimeout(function(){ContentBlockMouseMoveProcess(pos)},50);
	}
	if(pos.posy < pagetop + 10 && pagetop - 10 > 0){
		window.scrollBy(0,-10);
		pos.posy -= 10;
		ContentBlockDragObject.scroller = setTimeout(function(){ContentBlockMouseMoveProcess(pos)},50);
	}
}

function ContentBlockMouseLeave(e){
	var pos = new MousePosition(e);
	var top = window.pageYOffset?window.pageYOffset:document.documentElement.scrollTop;
	var left = window.pageXOffset?window.pageXOffset:document.documentElement.scrollLeft;
	var bottom = top + document.body.clientHeight;
	var right = left + document.body.clientWidth;	
	if(pos.posy < top || pos.posy > bottom || pos.posx < left || pos.posx > right){
		ReleaseContentBlockEvents();
		CloseContentBlockShadow();
	}
}

/* =================================================
				Layer Drag and Drop
================================================= */

var ContentLayoutDragObject = null;

function ContentLayoutMouseDown(e){
	e = e || this.document.parentWindow.event;
	var target = e.target != null ? e.target : e.srcElement;
	if(target.id){
		// identify the item
		var layoutid = target.id.substring(5);
		var layout = contentBlockManager.layouts[layoutid];
		var width = layout.obj.offsetWidth;
		var height = layout.obj.offsetHeight;
		ContentLayoutDragObject = layout;
		
		ContentLayoutDragObject.coverdiv = document.createElement('div');
		ContentLayoutDragObject.coverdiv.style.height = '100%';
		ContentLayoutDragObject.coverdiv.style.width = '100%';
		ContentLayoutDragObject.coverdiv.style.top = '0px';
		ContentLayoutDragObject.coverdiv.style.left = '0px';
		ContentLayoutDragObject.coverdiv.style.position = 'fixed';
		ContentLayoutDragObject.coverdiv.style.zIndex = 9999;
		ContentLayoutDragObject.coverdiv.style.cursor = 'move';
		document.body.appendChild(ContentLayoutDragObject.coverdiv);
		
		// create the shadow branch to follow the mouse
		ContentLayoutDragObject.ShowLayoutOptions();
		ContentLayoutDragObject.obj.style.display = 'none';
		
		// set the Hotspots. Do this after the moving item has been hidden.
		contentBlockManager.PopulateLayoutHotspotArray();
		
		// create the shadow object
		var div = document.createElement('div');
		div.style.position = 'absolute';
		var pos = new MousePosition(e);
		div.style.left = pos.posx+'px';
		div.style.top = pos.posy+'px';
		var shadow = document.createElement('div');
		div.appendChild(shadow);
		div.className = 'ContentLayoutDragShadow';
		div.style.cursor = 'move';
		ContentLayoutDragObject.shadow = div;
		ContentLayoutDragObject.shadow.style.position = 'static';
		ContentLayoutDragObject.shadow.style.width = width + 'px';
		ContentLayoutDragObject.shadow.style.height = height + 'px';
		ContentLayoutDragObject.obj.parentNode.insertBefore(ContentLayoutDragObject.shadow,ContentLayoutDragObject.obj);
		
		// attach mousemove and mouseup events
		attachEventHandler(document,'mousemove',ContentLayoutMouseMove);
		attachEventHandler(document,'mouseup',ContentLayoutMouseUp);
		if(!isie) attachEventHandler(document,'mouseout',ContentLayoutMouseLeave);
		
		return false;
	}
}

function ContentLayoutMouseUp(e){
	if(ContentLayoutDragObject){
		ReleaseContentLayoutEvents();
		var position = ContentLayoutDragObject.overlayout?(ContentLayoutDragObject.overlayout.position-(ContentLayoutDragObject.overlayout.position>ContentLayoutDragObject.position?1:0)):arraySizeOf(contentBlockManager.layouts);
		if(ContentLayoutDragObject.position != position){
			PopupManager.showLoading();
			var res = agent.call('../ajax/Content.ajax.php','AJ_MoveLayoutTo','',pageid,ContentLayoutDragObject.layoutid,position);
			if(res=='success'){
				var container = document.getElementById('tbp1');
				if(ContentLayoutDragObject.overlayout){
					container.insertBefore(ContentLayoutDragObject.obj, ContentLayoutDragObject.overlayout.obj);
				}else{
					container.appendChild(ContentLayoutDragObject.obj);
				}
				// reset layer positions:
				var pos = 1;
				for(var i=0;i<container.childNodes.length;i++){
					if(container.childNodes[i].layout){
						container.childNodes[i].layout.position = pos++;
					}
				}
				PopupManager.hideLoading();
			}else{
				PopupManager.showError(res);			
				PopupManager.hideLoading();
			}
		}		
		CloseContentLayoutShadow();
	}
}

function ReleaseContentLayoutEvents(){
	releaseEventHandler(document,'mousemove',ContentLayoutMouseMove);
	releaseEventHandler(document,'mouseup',ContentLayoutMouseUp);
	if(!isie) releaseEventHandler(document,'mouseout',ContentLayoutMouseLeave);
}

function CloseContentLayoutShadow(){
	ContentLayoutDragObject.shadow.parentNode.removeChild(ContentLayoutDragObject.shadow);
	ContentLayoutDragObject.shadow = null;
	document.body.removeChild(ContentLayoutDragObject.coverdiv);
	ContentLayoutDragObject.coverdiv = null;
	ContentLayoutDragObject.obj.style.display = 'block';	
	ContentLayoutDragObject.overlayout = null;
	ContentLayoutDragObject = null;
}

function ContentLayoutMouseMove(e){
	if(ContentLayoutDragObject){
		var pos = new MousePosition(e);
		if(isie && e.button == 0){
			ReleaseContentBlockEvents();
			return CloseContentLayoutShadow();
		}
		ContentLayoutMouseMoveProcess(pos);
	}
}
		
function ContentLayoutMouseMoveProcess(pos){
	if(ContentLayoutDragObject.scroller) clearTimeout(ContentLayoutDragObject.scroller);
	
	var container = document.getElementById('tbp1');
	var overlayout = null;
	for(var h in contentBlockManager.hotspots){
		h = parseInt(h);
		if(h > pos.posy){
			overlayout = contentBlockManager.hotspots[h];
		}else{
			break;
		}
	}
		
	if(overlayout){
		if(ContentLayoutDragObject.overlayout != overlayout){
			container.insertBefore(ContentLayoutDragObject.shadow,overlayout.obj);
		}
	}else{
		container.insertBefore(ContentLayoutDragObject.shadow,null);
	}
	
	ContentLayoutDragObject.overlayout = overlayout;
	
	// Handle Bottom or Top Scroll
	var pagetop = window.pageYOffset?window.pageYOffset:document.documentElement.scrollTop;
	var pageleft = window.pageXOffset?window.pageXOffset:document.documentElement.scrollLeft;
	var pageheight = document.body.clientHeight;
	if(pos.posy > (pageheight + pagetop - 10) && pageheight + pagetop + 10 < container.scrollHeight + getAbsTop(container)){
		window.scrollBy(0,10);
		pos.posy += 10;
		ContentLayoutDragObject.scroller = setTimeout(function(){ContentLayoutMouseMoveProcess(pos)},50);
	}
	if(pos.posy < pagetop + 10 && pagetop - 10 > 0){
		window.scrollBy(0,-10);
		pos.posy -= 10;
		ContentLayoutDragObject.scroller = setTimeout(function(){ContentLayoutMouseMoveProcess(pos)},50);
	}
}

function ContentLayoutMouseLeave(e){
	var pos = new MousePosition(e);
	var top = window.pageYOffset?window.pageYOffset:document.documentElement.scrollTop;
	var left = window.pageXOffset?window.pageXOffset:document.documentElement.scrollLeft;
	var bottom = top + document.body.clientHeight;
	var right = left + document.body.clientWidth;	
	if(pos.posy < top || pos.posy > bottom || pos.posx < left || pos.posx > right){
		ReleaseContentLayoutEvents();
		CloseContentLayoutShadow();
	}
}

/* =================================================
					Clipboard
================================================= */

function ClipboardPreview(){
	PopupManager.showLoading();
	var pop = PopupManager.createOrFetchPopup('clipboard','ClipBoard Preview',600,400,'iframe','../popups/Clipboard.pop.php','loading');
	PopupManager.ShowRefresh('clipboard');
}