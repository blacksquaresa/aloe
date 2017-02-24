function TreeView(name){
	this.name = name;
	this.prefix = name.substring(0,name.length-9);
	this.selectedIconMap = new Array();
	this.iconroot = '';
	this.itemcount = 0;
	this.useajax = false;
	this.selecteditem = null;
	this.classnames = {
		'loading' : this.prefix+'_loading',
		'selected' : this.prefix+'_selected',
		'item' : this.prefix+'_item',
		'branch' : this.prefix+'_branch',
		'highlight' : this.prefix+'_highlight',
		'disabled' : this.prefix+'_disabled',
		'line' : this.prefix+'_line',
		'icon' : this.prefix+'_icon',
		'disabledicon' : this.prefix+'_disabledicon',
		'shadow' : this.prefix+'_shadow',
		'nextsection' : this.prefix+'_nextsection',
		'searchcontainer' : this.prefix+'_searchcontainer',
		'searchbox' : this.prefix+'_searchbox',
		'searchbutton' : this.prefix+'_searchbutton',
		'prehighlight' : this.prefix+'_loading',
		'searchresultcontainer' : this.prefix+'_searchresultcontainer',
		'section' : this.prefix+'_section'
	};
	this.debug = window.debug?window.debug:parent.debug;
};
TreeView.prototype.ClientSelectionInit = function(iconroot,selectionmap,itemcount,showroot,useselectediconinparents,usedragdrop,usepaging,contextmenushowcallback,contextmenuhidecallback,dragdropstartcallback,dragdropendcallback,lastuniqueid,sectionsize){
	this.iconroot = iconroot;
	this.selectedIconMap = selectionmap;
	this.itemcount = itemcount;
	this.showroot = showroot;
	this.useselectediconinparents = useselectediconinparents;
	this.usedragdrop = usedragdrop;
	this.usepaging = usepaging;
	this.sectionsize = sectionsize;
	this.contextmenushowcallback = contextmenushowcallback;
	this.contextmenuhidecallback = contextmenuhidecallback;
	if(typeof(dragdropstartcallback) == 'function') this.dragdropstartcallback = dragdropstartcallback;
	else if(typeof(dragdropstartcallback) == 'string') eval('this.dragdropstartcallback = ' + dragdropstartcallback);
	if(typeof(dragdropendcallback) == 'function') this.dragdropendcallback = dragdropendcallback;
	else if(typeof(dragdropendcallback) == 'string') eval('this.dragdropendcallback = ' + dragdropendcallback);
	this.lastuniqueid = lastuniqueid;
	
	var container = this.getElement(this.prefix + '_container');
	if(container){
		this.container = container;
		if(this.container.style.position != 'absolute' && this.container.style.position != 'fixed') this.container.style.position = 'relative';
		// prepare drag and drop
		if(this.usedragdrop){
			attachEventHandler(container, 'mousedown', TreeviewMouseDown);
		}
	}
}
TreeView.prototype.AjaxInit = function(classname,ajaxfile,branchmethod,sectionmethod,searchmethod,ajaxpocket){
	this.useajax = true;
	this.ajaxclassname = classname;
	this.ajaxfile = ajaxfile;
	this.ajaxloadbranchmethod = branchmethod,
	this.ajaxloadsectionmethod = sectionmethod;
	this.ajaxperformsearchmethod = searchmethod;
	this.ajaxpocket = eval(ajaxpocket);
	this.searchclosecode = '<div class="trv_searchclosecontainer"><a href="javascript:'+this.name+'.CloseSearchResults();"><img src="'+this.iconroot+'close.png" /></a></div>';
}
TreeView.prototype.LoadItems = function(){
	this.items = [];
	var firstitem = new TreeViewItem(1,this);
	this.iconwidth = firstitem.icontag.offsetWidth;
	this.iconheight = firstitem.icontag.offsetHeight;
	var siblings = firstitem.getSiblings(false);
	var parent = firstitem.divtag.parentNode;
	this.items[1] = firstitem;
	this.selecteditem = firstitem;
	this._populateTreeViewItemChildren(firstitem);
	if(siblings && siblings.length){
		for(uid in siblings){
			if(uid!='indexOf'){ //resourcemanager.js adds the indexOf function to all arrays
				var sib = siblings[uid];
				this.items[uid] = sib;
				this._populateTreeViewItemChildren(sib);
			}
		}
	}
}
TreeView.prototype.getElement = function(id){
	if(document.getElementById) return document.getElementById(id);
	if(document.all) return document.all[id];
	return null;
}
TreeView.prototype.getItemByObjectId = function(objectid){
	for(uid in this.items){	
		if(uid!='indexOf'){ //resourcemanager.js adds the indexOf function to all arrays
			if(this.items[uid].objectid == objectid){
				return uid;
			}
		}
	}
	return null;
}
TreeView.prototype.CloseSearchResults = function(){
	document.getElementById(this.prefix+'_searchresultcontainer').style.display='none';
}
TreeView.prototype.Collapse = function (pre,id){
	var obj = this.getElement(pre + "_" + 'br' + id);
	var lnk = this.getElement(pre + "_" + 'lnk' + id);
	var img = this.getElement(pre + "_" + 'img' + id);
	if(obj && lnk){
		obj.style.display = 'none';
		lnk.href = "javascript:" + this.name + ".Expand('" + pre + "'," + id + ");";
		img.src = img.src.replace('collapse','expand');
	}
}
TreeView.prototype.Expand = function (pre,id){
	var obj = this.getElement(pre + "_" + 'br' + id);
	var lnk = this.getElement(pre + "_" + 'lnk' + id);
	var img = this.getElement(pre + "_" + 'img' + id);
	if(obj && lnk){
		obj.style.display = 'block';
		lnk.href = "javascript:" + this.name + ".Collapse('" + pre + "'," + id + ");";
		img.src = img.src.replace('expand','collapse');
		
		if(obj.innerHTML == '' && this.useajax && agent){
			var item = this.items[id];
			var level = item.getLevel();
			var islasts = item.getIsLasts();
			var expander = '<img src="' + this.iconroot + (islasts[level]?'spacer.gif':'tree_back.gif') + '" width="'+this.iconwidth+'" height="'+this.iconheight+'" align="bottom">';
			obj.innerHTML = item.getTreeLines() + expander + '<img src="' + this.iconroot + 'loader.gif" align="bottom" />';
			agent.call(this.ajaxfile,this.ajaxloadbranchmethod,'TreeViewExpandCallback',this.ajaxclassname,this.ajaxpocket,this.name,this.lastuniqueid,id,item.objectid,item.divtag.getAttribute('props'),level,islasts);
		}else{ 
			if(TreeviewDragObject != null){
				this.PopulateHotspotArray();
			}
		
			// Continue opening a path, if it exists
			if(this.OpenPathStep){
				var nextstep = this.OpenPathStep;
				this.OpenPathStep = null;
				this.items[nextstep.parentid].OpenPath(nextstep.path,nextstep.separator,nextstep.level);
			}
		}
	}
}
// Callback function used by the Expand function when AJAX is on.
function TreeViewExpandCallback(res){
	if(res && !res.error){
		eval('treeview = ' + res.treeviewname + ';');
		var obj = treeview.getElement(treeview.prefix + "_" + 'br' + res.clientitemid);
		var item = treeview.items[res.clientitemid];
		treeview.lastuniqueid = res.lastuniqueid;
		obj.innerHTML = Base64.decode(res.content);
		treeview._populateTreeViewItemChildren(item);
		if(TreeviewDragObject != null){
			if(TreeviewDragObject.shadow){
				if(TreeviewDragObject.shadow.parentNode) TreeviewDragObject.shadow.parentNode.removeChild(TreeviewDragObject.shadow);
				TreeviewDragObject.shadow.style.position = 'static';
				TreeviewDragObject.shadow.style.paddingLeft = (((TreeviewDragObject.overitem.getLevel()-(TreeviewDragObject.overitem.owner.showroot?1:2)) * TreeviewDragObject.overitem.owner.iconwidth) + TreeviewDragObject.overitem.owner.iconwidth) + 'px';
			}
			treeview.PopulateHotspotArray();
			if(TreeviewDragObject.shadow){
				TreeviewDragObject.overitem.childdiv.insertBefore(TreeviewDragObject.shadow,TreeviewDragObject.overitem.childdiv.firstChild);
			}
		}
		
		// Continue opening a path, if it exists
		if(treeview.OpenPathStep){
			var nextstep = treeview.OpenPathStep;
			treeview.OpenPathStep = null;
			treeview.items[nextstep.parentid].OpenPath(nextstep.path,nextstep.separator,nextstep.level);
		}
	}else{
		if(res.treeviewname && res.clientitemid){
			eval('treeview = ' + res.treeviewname + ';');
			var obj = treeview.getElement(treeview.prefix + "_" + 'br' + res.clientitemid);
			obj.innerHTML = '<span class="trv_error" title="' + res.error + '">error</span>';
		}else{
			alert('There was an error expanding the selected item.\r\n'+res.error);
		}
	}	
}
TreeView.prototype.Select = function(uniqueid){
	for(uid in this.items){
		if(uid!='indexOf'){ //resourcemanager.js adds the indexOf function to all arrays
			this.items[uid].SetStandardIcon();
			if(!this.items[uid].IsDisabled()){
				if(this.items[uid].divtag) this.items[uid].divtag.className = this.classnames['item'];
				if(this.items[uid].linktag) this.items[uid].linktag.href = this.items[uid].link==''?'javascript:;':this.items[uid].link;
			}
		}
	}
	this.items[uniqueid].SetSelectedIcon();
	this.items[uniqueid].divtag.className = this.classnames['selected'];
	var newlink = this.items[uniqueid].selectedlink==''?'javascript:;':this.items[uniqueid].selectedlink;
	// set the link to change after it has actually been processed once.
	setTimeout(this.name+'.items[' + uniqueid + '].linktag.href = "' + newlink + '";',1);
	if(this.useselectediconinparents){
		var par = this.items[uniqueid].getParent(true);
		while(par != null){
			par.SetSelectedIcon();
			par = par.getParent(true);
		}
	}
	this.selecteditem = this.items[uniqueid];
}
TreeView.prototype.Highlight = function(uniqueid){
	for(uid in this.items){
		if(uid!='indexOf' && this.items[uid].divtag.className == this.classnames['highlight']){ //resourcemanager.js adds the indexOf function to all arrays
			this.items[uid].divtag.className = this.classnames['prehighlight'];
		}
	}
	if(uniqueid){
		this.classnames['prehighlight'] = this.items[uniqueid].divtag.className;
		this.items[uniqueid].divtag.className = this.classnames['highlight'];
	}
}
TreeView.prototype.OpenSection = function(pre,parentid,pageid,page){
	var container = this.getElement(this.prefix + '_pag' + pageid);
	var obj = this.getElement(this.prefix + '_pab' + pageid);
	var item = this.items[parentid];
	var block = item.pageblocks[pageid];
	var fetch = block.getFirstPage()==page?this.sectionsize-(block.countPreviousItems()%this.sectionsize):this.sectionsize;
	var level = item.getLevel();
	obj.innerHTML = '<img src="' + this.iconroot + 'loader.gif" />';
	obj.className = "";
	agent.call(this.ajaxfile,this.ajaxloadsectionmethod,'TreeViewOpenSectionCallback',this.ajaxclassname,this.ajaxpocket,this.name,this.lastuniqueid,parentid,item.objectid,item.properties,level,pageid,page,container.getAttribute('startpage'),container.getAttribute('endpage'),fetch,item.getIsLasts());
}
// Callback function used by the OpenSection function when AJAX is on.
function TreeViewOpenSectionCallback(res){
	if(res && !res.error){
		eval('treeview = ' + res.treeviewname + ';');
		var obj = treeview.getElement(treeview.prefix + "_" + 'pag' + res.pageid);
		var item = treeview.items[res.clientparentid];
		var container = document.createElement('div');
		treeview.lastuniqueid = res.lastuniqueid;
		container.innerHTML = Base64.decode(res.content);
		var previous = null;
		var lastid = null;
		if(container.childNodes.length){
		
			// identify previous item
			var pageblock = item.pageblocks[res.pageid];
			if(pageblock) previous = pageblock.getPreviousItem(true);
				
			while(container.childNodes.length>0){
				// Identify the item's id, and test to see if it already exists.
				// This will make sure items are not added twice in the case where items
				// are moved, added or deleted, and the paging is out of synch.
				var testnode = container.childNodes[0];
				if(testnode.id && testnode.id.substring(0,treeview.prefix.length+4) == treeview.prefix+'_itm'){
					var uid = testnode.id.substring(testnode.id.length,treeview.prefix.length+4);
					if(treeview.items[uid] != null){
						container.removeChild(testnode);
					}else{
						obj.parentNode.insertBefore(container.childNodes[0],obj);	
						lastid = uid;			
					}
				}else{
					obj.parentNode.insertBefore(container.childNodes[0],obj);
				}
			}
		}
		obj.parentNode.removeChild(obj);
		// Add all the new items to the items collection, and reset the item's pageblock collection	
		treeview._populateTreeViewItemChildren(item);
		var last = treeview.items[lastid];
		// reset previous item's lines
		if(previous){
			previous.SetLines();
			previous.SetExpanderImage();
		}
		// reset last inserted item's lines
		if(last){
			last.SetExpanderImage();
		}
		
		// Continue opening a path, if it exists
		if(treeview.OpenPathStep){
			var nextstep = treeview.OpenPathStep;
			treeview.OpenPathStep = null;
			treeview.items[nextstep.parentid].OpenPath(nextstep.path,nextstep.separator,nextstep.level);
		}
	}else{
		if(res.treeviewname && res.pageid){
			eval('treeview = ' + res.treeviewname + ';');
			var obj = treeview.getElement(treeview.prefix + "_" + 'pab' + res.pageid);
			obj.innerHTML = '<span title="' + res.error + '">error</span>';
			obj.className = "trv_error";
		}else{
			alert('There was an error opening the selected section.\r\n'+res.error);
		}
	}	
}
TreeView.prototype.PerformSearch = function(){
	var textbox = this.getElement(this.prefix + '_search');
	if(textbox){
		var searchstring = trim(textbox.value);
		if(searchstring.length){
			var container = this.getElement(this.prefix + '_searchresultcontainer');
			container.innerHTML = '<img src="' + this.iconroot + 'loader.gif" />' + this.searchclosecode;
			container.style.display = 'block';
			agent.call(this.ajaxfile,this.ajaxperformsearchmethod,'TreeViewPerformSearchCallback',this.ajaxclassname,this.ajaxpocket,this.name,searchstring);
		}
	}
}
// Callback function used by the OpenSection function when AJAX is on.
function TreeViewPerformSearchCallback(res){
	if(res && !res.error){
		eval('treeview = ' + res.treeviewname + ';');
		var container = treeview.getElement(treeview.prefix + '_searchresultcontainer');
		container.innerHTML = treeview.searchclosecode;
		if(res.results.length){
			var i;
			for(i=0;i<res.results.length;i++){
				var result = res.results[i];
				var descriptions = Base64.decode(result.descriptions);
				var parts = descriptions.split(res.separator);
				var j;
				var html = '<div class="trv_searchresultitem" onclick="' + res.treeviewname + '.BeginOpenPath(\'' + result.ids + '\',\''+res.separator+'\');">';
				for(j=0;j<parts.length;j++){
					html += '<ul>' + parts[j];
				}
				for(j=0;j<parts.length;j++){
					html += '</ul>';
				}
				html += '</div>';
				container.innerHTML += html;
			}
		}else{
			container.innerHTML += '<div class="">Sorry, no results found for your search term.</div>';
		}
	}else{
		alert(res.error);
		if(res.treeviewname){
			eval('treeview = ' + res.treeviewname + ';');
			treeview.getElement(treeview.prefix + '_searchresultcontainer').style.display = 'none';
		}
	}	
}
TreeView.prototype.BeginOpenPath = function(path,separator){
	var parts = path.split(separator);
	var parent = this.items[1];
	parent.OpenPath(path,separator,0);
	this.getElement(this.prefix + '_searchresultcontainer').style.display = 'none';
}
TreeView.prototype.ShowLoading = function(){
	this.getElement(this.prefix + '_loading').style.display = 'block';
}
TreeView.prototype.HideLoading = function(){
	this.getElement(this.prefix + '_loading').style.display = 'none';
}
// Expanded to include collecting all paging blocks. 
TreeView.prototype._populateTreeViewItemChildren = function(parent){
	parent.pageblocks = {};
	var children = parent.getChildrenAndPageBlocks(false);
	if(children){
		for(var uid=0;uid<children.length;uid++){
			var item = children[uid];
			if(item.objectid){
				if(item.IsSelected()) this.selecteditem = item;
				this.items[item.uniqueid] = item;
				this._populateTreeViewItemChildren(item);
			}else{
				parent.pageblocks[item.uniqueid] = item;
			}
		}
	}
}
TreeView.prototype.ContextMenuClicked = function(menuitemid){
	var uniqueid = ContextMenu._attachedElement.id.substring(this.prefix.length+4);
	var item = this.items[uniqueid];
	if(this.contextmenulinks && this.contextmenulinks[menuitemid]){
		eval(this.contextmenulinks[menuitemid] + '(item);');
	}
}
TreeView.prototype.ContextMenuShow = function(menuitemid){
	var uniqueid = ContextMenu._attachedElement.id.substring(this.prefix.length+4);
	var item = this.items[uniqueid];
	this.Highlight(uniqueid);
	if(this.contextmenushowcallback){
		eval(this.contextmenushowcallback + '(item);');
	}
}
TreeView.prototype.ContextMenuHide = function(menuitemid){
	if(ContextMenu._attachedElement){
		var uniqueid = ContextMenu._attachedElement.id.substring(this.prefix.length+4);
		var item = this.items[uniqueid];
		this.Highlight(null);
		if(this.contextmenuhidecallback){
			eval(this.contextmenuhidecallback + '(item);');
		}
	}
}

// this method is used by the drag-and-drop functionality to identify areas of relevance for the draged item
TreeView.prototype.PopulateHotspotArray = function(){
	var hotspots = {};
	for(key in this.items){
		var item = this.items[key];
		if(item.divtag.offsetHeight){
			hotspots[item.getTreeTop()] = item;
		}
	}
	this.hotspots = keysort(hotspots);
}

// this method is set as part of the treeview itself, rather than on the Treeview Item because it will
// also be used for shadow divs that cannot be associated with an item. For this reason, the divtag
// and childtag items must represent the div tags and not the actual items. The Treeview Item has a 
// wrapper method (SetLines).
TreeView.prototype.SetItemLines = function(divtag,childtag,sourceitem,uselink){
	var sourcelines = sourceitem?sourceitem.getTreeLines():'';
	if(sourceitem && sourceitem.getParent() != null){
		var islast = true;
		if(sourceitem.containertag && sourceitem.containertag.nextSibling){
			var nextsib = sourceitem.containertag.nextSibling;
			while(nextsib.nodeType != 1 && nextsib.nextSibling){
				nextsib = nextsib.nextSibling;
			}
			if(nextsib.id && (nextsib.id.indexOf(sourceitem.owner.prefix + '_itm') > -1)){
				islast = false;
			}
		}
		sourcelines += '<span class="'+this.classnames.line+'"><img src="' + this.iconroot + (islast?'spacer.gif':'tree_back.gif') + '" width="'+this.iconwidth+'" height="'+this.iconheight+'" align="bottom"></span>';
	}
	// identify and replace the top row's lines
	var top = divtag.firstChild;
	var foundimg = false;
	if(top){
		var removecount = 0;
		for(i=0;i<top.childNodes.length;i++){
			var node = top.childNodes[i];
			if(node && node.nodeType == 1 && node.tagName.toLowerCase() == 'span' && node.className==this.classnames.line && node.firstChild && node.firstChild.tagName.toLowerCase()=='img'){
				if(node.firstChild.id && node.firstChild.id.indexOf(this.prefix+'_img') > -1){
					if(!sourceitem){
						top.removeChild(node);					
					}
					foundimg = true;
					break;
				}else{
					removecount++;
				}
			}
		}
		for(j=removecount-1;j>=0;j--){
			top.removeChild(top.childNodes[j]);
		}
		top.innerHTML = sourcelines + top.innerHTML;
		// add the sourcelines to any children
		islast = true;
		if(divtag.parentNode && divtag.parentNode.nextSibling){
			var nextsib = divtag.parentNode.nextSibling;
			while(nextsib.nextSibling && (nextsib.nodeType != 1 || (nextsib.id && nextsib.id.indexOf(sourceitem.owner.prefix + '_pag') > -1))){
				nextsib = nextsib.nextSibling;
			}
			if(nextsib.id && (nextsib.id.indexOf(sourceitem.owner.prefix + '_itm') > -1)){
				islast = false;
			}
		}
		sourcelines += '<span class="'+this.classnames.line+'"><img src="' + this.iconroot + (islast?'spacer.gif':'tree_back.gif') + '" width="'+this.iconwidth+'" height="'+this.iconheight+'" align="bottom"></span>';
		removecount ++;
		this.SetItemLinesRecursive(sourcelines, removecount, childtag, uselink);
	}
}

TreeView.prototype.SetItemLinesRecursive = function(sourcelines, removecount, container, uselink){
	if(container && container.firstChild){
		var i,j;
		for(i=0;i<container.childNodes.length;i++){
			if(container.childNodes[i].nodeType == 1){
				var node = container.childNodes[i];
				if(node.id && node.id.indexOf('_itm') > -1){
					node = node.firstChild.firstChild;
					var count = 0;
					var nodecount = node.childNodes.length;
					if(removecount){
						for(j=0;j<nodecount;j++){
							var imgnode = node.childNodes[0];
							if(imgnode && imgnode.nodeType == 1 && imgnode.tagName.toLowerCase() == 'span' && imgnode.className==this.classnames.line && imgnode.firstChild && imgnode.firstChild.tagName.toLowerCase()=='img'){
								if(imgnode.firstChild.id && imgnode.firstChild.id.indexOf(this.prefix+'_img') > -1){
									if(!uselink){
										node.removeChild(imgnode);					
									}
									break;
								}else{
									node.removeChild(imgnode);
									count++;
								}
							}
							if(count == removecount) break;
						}
					}
					node.innerHTML = sourcelines + node.innerHTML;
					
					// does this node have children of it's own?
					for(j=1;j<container.childNodes[i].childNodes.length;j++){
						var cnode = container.childNodes[i].childNodes[j];
						if(cnode.nodeType == 1 && cnode.id.indexOf(this.prefix+'_br') > -1){
							this.SetItemLinesRecursive(sourcelines,removecount,cnode,true);
							break;
						}
					}
				}else if(node.id && node.id.indexOf('_pag') > -1){
					node = node.firstChild;
					var count = 0;
					var nodecount = node.childNodes.length;
					if(removecount){
						for(j=0;j<nodecount;j++){
							var imgnode = node.childNodes[0];
							if(imgnode && imgnode.tagName.toLowerCase() == 'img'){
								if(imgnode.id && imgnode.id.indexOf(this.prefix+'_img') > -1){
									if(!uselink){
										node.removeChild(imgnode);					
									}
									break;
								}else{
									node.removeChild(imgnode);
									count++;
								}
							}
							if(count == removecount) break;
						}
					}
					node.innerHTML = sourcelines + node.innerHTML;
				}
			}
		}
	}
}

/*********************************************************
					Treeview Item Class
**********************************************************/

function TreeViewItem(uniqueid,owner){
	this.uniqueid = uniqueid;
	this.owner = owner; // parent treeview
	this.RefreshElementReferences();
	// Properties
	this.objectid = this.divtag.getAttribute('objid');
	this.link = this.divtag.getAttribute('link');
	this.selectedlink = this.divtag.getAttribute('slink');
	this.disabledlink = this.divtag.getAttribute('dlink');
	var props = this.divtag.getAttribute('props');
	eval('this.properties = ' + (props&&props.length?props:"''"));
	this.oicon = this.divtag.getAttribute('icon');
	this.dicon = this.divtag.getAttribute('dicon');
	this.text = trim(this.divtag.innerText?this.divtag.innerText:this.divtag.textContent);
	this.state = this.originalstate = this.laststate = this.IsDisabled()?'disabled':(this.IsSelected()?'selected':'item');
	// Prepare drag-drop
	if(this.owner.usedragdrop && this.state != 'disabled'){
		this.icontag.style.cursor = 'move';
	}
	this.pageblocks = {};
}
TreeViewItem.prototype.RefreshElementReferences = function(){
	this.containertag = this.owner.getElement(this.owner.prefix+'_itm'+this.uniqueid);
	this.divtag = this.owner.getElement(this.owner.prefix+'_div'+this.uniqueid);
	this.expandertag = this.owner.getElement(this.owner.prefix+'_img'+this.uniqueid);
	this.icontag = this.owner.getElement(this.owner.prefix+'_ico'+this.uniqueid);
	this.exlinktag = this.owner.getElement(this.owner.prefix+'_lnk'+this.uniqueid);
	// the link tag if there is a link, otherwise the span containing the text.
	this.linktag = this.owner.getElement(this.owner.prefix+'_act'+this.uniqueid);
	this.childdiv = this.owner.getElement(this.owner.prefix+'_br'+this.uniqueid);
	this.icon = this.icontag.style.backgroundImage.replace(/^.*[\/\\]([^'"\)]*)['"]?\)/g, '$1');
}
TreeViewItem.prototype.IsSelected = function(){
	return this.divtag.className == this.owner.classnames['selected'] && this.divtag.className != this.owner.classnames['item'];
}
TreeViewItem.prototype.IsDisabled = function(){
	return this.divtag.className == this.owner.classnames['disabled'] && this.divtag.className != this.owner.classnames['item'];
}
// Checks to see if an item with the supplied objectid is an ancestor of this item.
TreeViewItem.prototype.InParentTree = function(check){
	if(this.objectid == check) return true;
	while(itm = this.getParent()){
		if(itm.objectid == check) return true;
	}
	return false;
}
// Checks to see if an item with the supplied objectid is a descendant of this item.
TreeViewItem.prototype.InChildTree = function(check){	
	if(this.objectid == check) return true;
	var children = this.getChildren();
	if(!children || children.length == 0) return false;
	var i=0;
	for(;i<children.length;i++){
		var child = children[i];
		if(child.objectid == check) return true;
		else if(child.InChildTree(check)) return true;
	}
	return false;
}
TreeViewItem.prototype.SetSelectedIcon = function(){
	if(this.owner.selectedIconMap[this.icon] && this.owner.selectedIconMap[this.icon].length > 0){
		this.icon = this.owner.selectedIconMap[this.icon];
		this.icontag.style.backgroundImage = 'url(' + this.owner.iconroot + this.icon + ')';
	}
}
TreeViewItem.prototype.SetStandardIcon = function(){
	for(key in this.owner.selectedIconMap){
		if(this.owner.selectedIconMap[key] == this.icon){
			this.icon = key;
			this.icontag.style.backgroundImage = 'url(' + this.owner.iconroot + key + ')';
			break;
		}
	}
}
TreeViewItem.prototype.SetIcon = function(imagename){
	var selected = this.owner.useselectediconinparents?this.InChildTree(this.owner.selecteditem.objectid):this.IsSelected();
	imagename = selected && this.owner.selectedIconMap[imagename] && this.owner.selectedIconMap[imagename].length > 0?this.owner.selectedIconMap[imagename]:imagename;
	this.icontag.style.backgroundImage = 'url(' + this.owner.iconroot + imagename + ')';
	this.icon = imagename;
}
TreeViewItem.prototype.SetText = function(title){
	this.linktag.innerHTML = " &nbsp;" + title;
	this.text = trim(title);
}
TreeViewItem.prototype.SetLines = function(){
	this.owner.SetItemLines(this.divtag,this.childdiv,this.getParent(),true);
}
TreeViewItem.prototype.SetExpanderImage = function(){
	this.RefreshElementReferences();
	var islast = this.getNextSibling(true) == null;
	var hasopen = this.childdiv != null;
	var isopen = hasopen && this.childdiv.style.display == 'block';
	
	if(this.exlinktag){
		if(hasopen){
			if(isopen){
				this.exlinktag.href = "javascript:" + this.owner.name + ".Collapse('" + this.owner.prefix + "'," + this.uniqueid + ");";
				this.expandertag.src = this.owner.iconroot + (islast?'tree_bot_collapse.gif':'tree_mid_collapse.gif');
			}else{
				this.exlinktag.href = "javascript:" + this.owner.name + ".Expand('" + this.owner.prefix + "'," + this.uniqueid + ");";
				this.expandertag.src = this.owner.iconroot + (islast?'tree_bot_expand.gif':'tree_mid_expand.gif');
			}
		}else{
			this.exlinktag.parentNode.insertBefore(this.expandertag,this.exlinktag);
			this.exlinktag.parentNode.removeChild(this.exlinktag);
			this.exlinktag = null;
			this.expandertag.src = this.owner.iconroot + (islast?'tree_bot.gif':'tree_mid.gif');
		}
	}else if(this.expandertag){
		if(hasopen){
			var lnk = document.createElement('a');
			lnk.id = this.owner.prefix + '_lnk' + this.uniqueid;
			if(isopen){
				lnk.href = "javascript:" + this.owner.name + ".Collapse('" + this.owner.prefix + "'," + this.uniqueid + ");";
				this.expandertag.src = this.owner.iconroot + (islast?'tree_bot_collapse.gif':'tree_mid_collapse.gif');
			}else{
				lnk.href = "javascript:" + this.owner.name + ".Expand('" + this.owner.prefix + "'," + this.uniqueid + ");";
				this.expandertag.src = this.owner.iconroot + (islast?'tree_bot_expand.gif':'tree_mid_expand.gif');
			}
			this.expandertag.parentNode.insertBefore(lnk,this.expandertag);
			lnk.appendChild(this.expandertag);
			this.exlinktag = lnk;
		}else{
			this.expandertag.src = this.owner.iconroot + (islast?'tree_bot.gif':'tree_mid.gif');
		}
	}else{
		if((this.owner.showroot && this.getLevel() > 1) || (!this.owner.showroot && this.getLevel() > 2)){
			var img = document.createElement('img');
			img.id = this.owner.prefix + '_img' + this.uniqueid;
			img.src = this.owner.iconroot + (islast?'tree_bot.gif':'tree_mid.gif');
			this.divtag.firstChild.insertBefore(img,this.divtag.firstChild.firstChild);
			this.expandertag = img;
			if(hasopen){
				var lnk = document.createElement('a');
				lnk.id = this.owner.prefix + '_lnk' + this.uniqueid;
				if(isopen){
					lnk.href = "javascript:" + this.owner.name + ".Collapse('" + this.owner.prefix + "'," + this.uniqueid + ");";
					this.expandertag.src = this.owner.iconroot + (islast?'tree_bot_collapse.gif':'tree_mid_collapse.gif');
				}else{
					lnk.href = "javascript:" + this.owner.name + ".Expand('" + this.owner.prefix + "'," + this.uniqueid + ");";
					this.expandertag.src = this.owner.iconroot + (islast?'tree_bot_expand.gif':'tree_mid_expand.gif');
				}
				this.expandertag.parentNode.insertBefore(lnk,this.expandertag);
				lnk.appendChild(this.expandertag);
				this.exlinktag = lnk;
			}
		}
	}
	
	if(islast && this.expandertag){
		var prev = this.getPreviousSibling();
		if(prev){
			prev.SetLines();
			prev.SetExpanderImage();
		}
	}
}
TreeViewItem.prototype.OpenPath = function(path,separator,level){
	var parts = path.split(separator);
	var targetindex = parts[level];
	var children = this.getChildrenAndPageBlocks();
	var hasnextstep = level+1<parts.length;
	var index = 0;
	for(i=0;i<children.length;i++){
		var child = children[i];
		if(child.objectid){
			if(targetindex==index){
				if(hasnextstep){
					this.owner.OpenPathStep = {'path':path,'separator':separator,'level':level+1,'parentid':child.uniqueid};
					this.owner.Expand(this.owner.prefix,child.uniqueid);
				}else{
					this.owner.Select(child.uniqueid);
					// ensure selected item is visible
					var scrolltop = child.divtag.offsetTop;
					this.owner.container.scrollTop = scrolltop - (this.owner.container.offsetHeight / 2);
					// perform click
					if(child.linktag && child.linktag.href){
						var url = child.linktag.href;
						if(url.substring(0,11) == 'javascript:'){
							eval(url.substring(11,url.length));
						}else{
							document.location.href = url;
						}
					}
				}
				break;
			}else{
				index++;
			}
		}else{
			var first = child.getFirstPage();
			var firstpageindex = first * this.owner.sectionsize;
			if(firstpageindex >= targetindex){
				this.owner.OpenPathStep = {'path':path,'separator':separator,'level':level,'parentid':this.uniqueid};
				this.owner.OpenSection(this.owner.prefix,this.uniqueid,child.uniqueid,first);
				break;
			}else{
				var last = child.getLastPage();
				var lastpageindex = last * this.owner.sectionsize;
				if(lastpageindex >= targetindex){
					var page = Math.floor(targetindex / this.owner.sectionsize) + 1;
					this.owner.OpenPathStep = {'path':path,'separator':separator,'level':level,'parentid':this.uniqueid};
					this.owner.OpenSection(this.owner.prefix,this.uniqueid,child.uniqueid,page);
					break;
				}else{
					index = lastpageindex;
				}
			}
		}
	}
}
TreeViewItem.prototype.getIndex = function(){
	var children = this.getParent().getChildrenAndPageBlocks();
	var index = 0;
	for(i=0;i<children.length;i++){
		var child = children[i];
		if(child == this) return index;
		else if(child.objectid){
			index++;
		}else{
			var last = child.getLastPage();
			index = last * this.owner.sectionsize;
		}
	}
}
TreeViewItem.prototype.getParent = function(useitems){
	if(useitems!==false) useitems = true;
	var parent = this.containertag.parentNode;
	while(parent && parent.id && parent.id.substring(0,this.owner.prefix.length+3) != this.owner.prefix+'_br'){
		parent = parent.parentNode;
	}
	if(!parent) return null;
	var parid = parent.id.substring(this.owner.prefix.length+3,parent.id.length);
	if(parid.length) return useitems?this.owner.items[parid]:new TreeViewItem(parid,this.owner);
	else return null;
}
TreeViewItem.prototype.getChildren = function(useitems){
	if(useitems!==false) useitems = true;
	if(!this.childdiv) return null;
	var res = [];
	for(i=0;i<this.childdiv.childNodes.length;i++){
		var testnode = this.childdiv.childNodes[i];
		if(testnode.id && testnode.id.substring(0,this.owner.prefix.length+4) == this.owner.prefix+'_itm'){
			var uid = testnode.id.substring(testnode.id.length,this.owner.prefix.length+4);
			var thisitem = useitems?this.owner.items[uid]:new TreeViewItem(uid,this.owner);
			res[res.length] = thisitem;
		}
	}
	return res;
}
TreeViewItem.prototype.getChildrenAndPageBlocks = function(useitems){
	if(useitems!==false) useitems = true;
	if(!this.childdiv) return null;
	var res = [];
	for(i=0;i<this.childdiv.childNodes.length;i++){
		var testnode = this.childdiv.childNodes[i];
		if(testnode.id && testnode.id.substring(0,this.owner.prefix.length+4) == this.owner.prefix+'_itm'){
			var uid = testnode.id.substring(testnode.id.length,this.owner.prefix.length+4);
			var thisitem = useitems?this.owner.items[uid]:new TreeViewItem(uid,this.owner);
			res[res.length] = thisitem;
		}else if(testnode.id && testnode.id.substring(0,this.owner.prefix.length+4) == this.owner.prefix+'_pag'){
			var uid = testnode.id.substring(testnode.id.length,this.owner.prefix.length+4);
			var thisitem = useitems?this.pageblocks[uid]:new TreeViewPageBlock(uid,this);
			res[res.length] = thisitem;
		}
	}
	return res;
}
TreeViewItem.prototype.getSiblings = function(useitems){
	if(useitems!==false) useitems = true;
	if(!this.containertag) return null;
	var res = [];
	var sib = this.containertag.previousSibling;
	while(sib != null){
		if(sib.tagName == 'DIV' && sib.id.substring(0,this.owner.prefix.length+4) == this.owner.prefix+'_itm'){
			var uid = sib.id.substring(sib.id.length,this.owner.prefix.length+4);
			var thisitem = useitems?this.owner.items[uid]:new TreeViewItem(uid,this.owner);
			res[uid] = thisitem;
		}
		sib = sib.previousSibling;
	}
	sib = this.containertag.nextSibling;
	while(sib != null){
		if(sib.tagName == 'DIV' && sib.id.substring(0,this.owner.prefix.length+4) == this.owner.prefix+'_itm'){
			var uid = sib.id.substring(sib.id.length,this.owner.prefix.length+4);
			var thisitem = useitems?this.owner.items[uid]:new TreeViewItem(uid,this.owner);
			res[uid] = thisitem;
		}
		sib = sib.nextSibling;
	}
	return res;
}
TreeViewItem.prototype.getPreviousSibling = function(useitems){
	if(useitems!==false) useitems = true;
	if(!this.containertag) return null;
	var res = null;
	var sib = this.containertag.previousSibling;
	while(sib != null){
		if(sib.tagName == 'DIV' && sib.id.substring(0,this.owner.prefix.length+4) == this.owner.prefix+'_itm'){
			var uid = sib.id.substring(sib.id.length,this.owner.prefix.length+4);
			var thisitem = useitems?this.owner.items[uid]:new TreeViewItem(uid,this.owner);
			res = thisitem;
			break;
		}
		sib = sib.previousSibling;
	}
	return res;
}
TreeViewItem.prototype.getNextSibling = function(useitems){
	if(useitems!==false) useitems = true;
	if(!this.containertag) return null;
	var res = null;
	var sib = this.containertag.nextSibling;
	while(sib != null){
		if(sib.tagName == 'DIV' && sib.id.substring(0,this.owner.prefix.length+4) == this.owner.prefix+'_itm'){
			var uid = sib.id.substring(sib.id.length,this.owner.prefix.length+4);
			var thisitem = useitems?this.owner.items[uid]:new TreeViewItem(uid,this.owner);
			res = thisitem;
			break;
		}
		sib = sib.nextSibling;
	}
	return res;
}
TreeViewItem.prototype.getPreviousPageBlock = function(useitems){
	if(useitems!==false) useitems = true;
	if(!this.containertag) return null;
	var res = null;
	var sib = this.containertag.previousSibling;
	while(sib != null){
		if(sib.tagName == 'DIV' && sib.id.substring(0,this.owner.prefix.length+4) == this.owner.prefix+'_pag'){
			var uid = sib.id.substring(sib.id.length,this.owner.prefix.length+4);
			var thisitem = useitems?this.getParent(true).pageblocks[uid]:new TreeViewPageBlock(uid,this.owner);
			res = thisitem;
			break;
		}
		sib = sib.previousSibling;
	}
	return res;
}
TreeViewItem.prototype.getNextPageBlock = function(useitems){
	if(useitems!==false) useitems = true;
	if(!this.containertag) return null;
	var res = null;
	var sib = this.containertag.nextSibling;
	while(sib != null){
		if(sib.tagName == 'DIV' && sib.id.substring(0,this.owner.prefix.length+4) == this.owner.prefix+'_pag'){
			var uid = sib.id.substring(sib.id.length,this.owner.prefix.length+4);
			var thisitem = useitems?this.getParent(true).pageblocks[uid]:new TreeViewPageBlock(uid,this.owner);
			res = thisitem;
			break;
		}
		sib = sib.nextSibling;
	}
	return res;
}
TreeViewItem.prototype.getLastChildPageBlock = function(){
	var res = null;
	var lastkey = 0;
	for(key in this.pageblocks){
		var block = this.pageblocks[key];
		if(block.startPage > lastkey){
			res = block;
			lastkey = block.startPage;
		}
	}
	return res;
}
TreeViewItem.prototype.getLevel = function(){
	var parent = this.getParent(true);
	if(parent == null){
		return 1;
	}else{
		return parent.getLevel() + 1;
	}
}
TreeViewItem.prototype.getIsLasts = function(){
	var parent = this.getParent(true);
	if(parent == null){
		var islasts = [true];
	}else{
		var islasts = parent.getIsLasts();
	}
	islasts[this.getLevel()] = this.expandertag?this.expandertag.src.indexOf('tree_bot') > -1:true;
	return islasts;
}
TreeViewItem.prototype.getTreeLines = function(){
	var cont = this.divtag?this.divtag.firstChild:null;
	var treelines = '';
	if(cont){
		// identify the lines to use
		for(i=0;i<cont.childNodes.length;i++){
			var node = cont.childNodes[i];
			if(node && node.nodeType == 1 && node.tagName.toLowerCase() == 'img'){
				if(node.id && node.id.indexOf(this.prefix+'_img') > -1){
					break;
				}else{
					treelines += '<img src="' + node.src + '" width="' + node.width + '" height="' + node.height + '" align="' + node.align + '">';
				}
			}
		}
	}
	return treelines;
}
TreeViewItem.prototype.getTreeTop = function(){
	var obj = this.containertag;
	var objTop = obj.offsetTop;
	while(obj.offsetParent!=null && obj.offsetParent!=this.owner.container){
		objParent = obj.offsetParent;
		objTop += objParent.offsetTop;
		obj = objParent;
	}
	// if currently expanding during a drag operation, discount the drag shadow.
	if(TreeviewDragObject && TreeviewDragObject.shadow.style.position == 'static' && getAbsTop(TreeviewDragObject.shadow) < objTop){
		objTop -= TreeviewDragObject.shadow.offsetHeight;
	}
	return objTop;
}
TreeViewItem.prototype.getExpandStatus = function(){
	var status = 'none';
	if(this.childdiv){
		status = this.childdiv.style.display == 'block'?'open':'closed';
	}
	return status;
}

TreeViewItem.prototype.addItem = function(text,link,slink,icon,objid,props){
	var id = ++this.owner.lastuniqueid;
	var parentislast = this.getNextSibling()==null;
	// if there is no child container, create one.
	if(!this.childdiv){
		var brdiv = document.createElement('div');
		brdiv.setAttribute('id',this.owner.prefix+'_br'+this.uniqueid);
		this.divtag.parentNode.insertBefore(brdiv,this.divtag.nextSibling);
		this.childdiv = this.owner.getElement(this.owner.prefix+'_br'+this.uniqueid);
		if(this.expandertag){
			// change the expander to a 'collapse' icon
			this.expandertag.src = this.owner.iconroot + (parentislast?'tree_bot_collapse.gif':'tree_mid_collapse.gif');
			// create or set the expander link
			if(this.exlinktag){
				this.exlinktag.href = "javascript:" + this.owner.name + ".Collapse('" + this.owner.prefix + "'," + this.uniqueid + ");";
			}else{
				var lnk = document.createElement('a');
				lnk.id = this.owner.prefix + '_lnk' + this.uniqueid;
				lnk.href = "javascript:" + this.owner.name + ".Collapse('" + this.owner.prefix + "'," + this.uniqueid + ");";
				this.expandertag.parentNode.insertBefore(lnk,this.expandertag);
				lnk.appendChild(this.expandertag);
				this.exlinktag = lnk;
			}
		}
	}else{
		this.owner.Expand(this.owner.prefix,this.uniqueid);
	}
	// create the new item
	var cont = document.createElement('div');
	cont.setAttribute('id',this.owner.prefix+'_itm'+id);
	var div = document.createElement('div');
	div.setAttribute('id',this.owner.prefix+'_div'+id);
	div.className = this.owner.classnames['item'];
	div.setAttribute('objid',objid);
	div.setAttribute('props',props);
	div.setAttribute('link',link);
	div.setAttribute('slink',slink);
	var nobr = document.createElement('nobr');
	div.appendChild(nobr);
	var level = this.getLevel();
	// check to see whether this is in the last of the top level items
	// If so, do not show vertical lines.
	var check = this;
	var lines = '';
	for(i=level;i>1;i--){
		lines = '<span class="'+this.owner.classnames.line+'"><img src="' + this.owner.iconroot + ((check.getNextSibling() == null)?'spacer.gif':'tree_back.gif') + '" width="'+this.owner.iconwidth+'" height="'+this.owner.iconheight+'" align="bottom"></span>' + lines;
		check = check.getParent();
	}
	nobr.innerHTML += lines;
	nobr.innerHTML += '<span class="'+this.owner.classnames.line+'"><img src="' + this.owner.iconroot + 'tree_bot.gif" align="bottom" id="' + this.owner.prefix + '_img' + id + '" border="0"></span>';
	nobr.innerHTML += '<span id="' + this.owner.prefix + '_ico' + id + '" style="background-image: url(\'' + this.owner.iconroot + icon + '\');" class="' + this.owner.classnames['icon'] + '">';
	nobr.innerHTML += '<a id="' + this.owner.prefix + '_act' + id + '" href="' + link + '" onclick="' + this.owner.prefix + '_treeview.Select(\'' + id + '\')"> &nbsp;' + text + '</a>';
	cont.appendChild(div);
	this.childdiv.appendChild(cont);
	var child = new TreeViewItem(id,this.owner);
	this.owner.items[id] = child;
	// adjust the previous sibling's expander
	var sib = child.getPreviousSibling();
	if(sib) sib.expandertag.src = sib.expandertag.src.replace('_bot','_mid');
	var lastpage = this.getLastChildPageBlock();
	if(lastpage != null) lastpage.SetExpanderImage();
	return id;
}

TreeViewItem.prototype.deleteItem = function(){
	var parent = this.getParent();
	var previous = this.getPreviousSibling();
	var next = this.getNextSibling();
	var previousblock = this.getPreviousPageBlock();
	var nextblock = this.getNextPageBlock();
	// remove this item from the tree
	this.containertag.parentNode.removeChild(this.containertag);
	// adjust the parent item's line images, and expander if needed.
	if(parent){
		if(next == null && previous != null){
			previous.expandertag.src = previous.expandertag.src.replace('_mid','_bot');
		}else if(next == null && previous == null){
			if(parent.expandertag){
				parent.expandertag.src = parent.expandertag.src.replace('_expand','').replace('_collapse','');
				parent.exlinktag.parentNode.insertBefore(parent.expandertag,parent.exlinktag);
				parent.exlinktag.parentNode.removeChild(parent.exlinktag);
				this.owner.items[parent.uniqueid].exlinktag = null;
			}
			parent.childdiv.parentNode.removeChild(parent.childdiv);
			this.owner.items[parent.uniqueid].childdiv = null;
		}
	}
	// recursively remove children from the treeview items list
	this.deleteItemFromListRecursive(this);
	
	// check pageblocks
	if(nextblock != null){
		nextblock.checkNumbers();
	}else if(previousblock){
		previousblock.SetExpanderImage();
	}
}

TreeViewItem.prototype.deleteItemFromListRecursive = function(item){ 
	var children = item.getChildren();
	if(children){
		for(key in children){
			if(key!='indexOf'){ //resourcemanager.js adds the indexOf function to all arrays
				this.deleteItemFromListRecursive(children[key]);
			}
		}
	}
	delete this.owner.items[item.uniqueid];
}

TreeViewItem.prototype.moveTo = function(parent,index){
	var oldparent = this.containertag.parentNode;
	var oldblock = this.getNextPageBlock();
	var oldprevious = this.getPreviousSibling();
	oldparent.removeChild(this.containertag);
	if(oldblock!=null) oldblock.checkNumbers();
	if(oldprevious) oldprevious.SetExpanderImage();
	
	var cont;
	if(parent) cont = parent.childdiv;
	else if(this.owner.showroot) cont = this.owner.items[1].childdiv; 
	else cont = this.owner.container;
	
	var ind = 0;
	for(i=0;i<cont.childNodes.length;i++){
		if(ind == index) break;
		if(cont.childNodes[i].nodeType == 1){
			ind++;
		}
	}
	cont.insertBefore(this.containertag,cont.childNodes[i]?cont.childNodes[i]:null);
	this.SetLines();
	this.SetExpanderImage();
	
	var newblock = this.getNextPageBlock();
	if(newblock!=null) newblock.checkNumbers();
}

TreeViewItem.prototype.disableItem = function(recursive){
	if(this.dicon.length) this.SetIcon(this.dicon);
	this.divtag.className = this.owner.classnames['disabled'];
	this.linktag.href = this.disabledlink;
	this.linktag.onclick = '';
	if(recursive){
		var children = this.getChildren(true);
		for(ind in children){
			children[ind].disableItem(recursive);
		}
	}
}

TreeViewItem.prototype.enableItem = function(recursive){
	this.divtag.className = this.owner.selecteditem==this?this.owner.classnames['selected']:this.owner.classnames['item'];
	if(this.oicon.length) this.SetIcon(this.oicon);
	this.linktag.href = this.link;
	eval("this.linktag.onclick = function(){" + this.owner.name + ".Select('" + this.uniqueid + "');}");
	if(recursive){
		var children = this.getChildren(true);
		for(ind in children){
			children[ind].enableItem(recursive);
		}
	}
}

/* ===================================================
			TreeView Paging Block Class
=================================================== */

function TreeViewPageBlock(uniqueid,parent){
	this.uniqueid = uniqueid;
	this.parent = parent; // containing treeview item
	this.owner = parent.owner; // parent treeview
	this.RefreshElementReferences();
}
TreeViewPageBlock.prototype.RefreshElementReferences = function(){
	this.containertag = this.owner.getElement(this.owner.prefix+'_pag'+this.uniqueid);
	this.divtag = this.owner.getElement(this.owner.prefix+'_pab'+this.uniqueid);
	this.startPage =  parseInt(this.containertag.getAttribute('startpage'));
	this.endPage =  parseInt(this.containertag.getAttribute('endpage'));
}
TreeViewPageBlock.prototype.SetExpanderImage = function(useitems){
	var image = this.divtag.previousSibling;
	while(image != null && image.tagName != 'IMG'){
		image = image.previousSibling;
	}
	var next = this.getNextItem(useitems);
	if(next){
		image.src = this.owner.iconroot + 'tree_back.gif';
	}else{
		image.src = this.owner.iconroot + 'spacer.gif';
		image.width = this.owner.iconwidth;
		image.height = this,owner.iconheight;
	}
}
TreeViewPageBlock.prototype.getPreviousItem = function(useitems){
	if(useitems!==false) useitems = true;
	if(!this.containertag) return null;
	var res = null;
	var sib = this.containertag.previousSibling;
	while(sib != null){
		if(sib.tagName == 'DIV' && sib.id.substring(0,this.owner.prefix.length+4) == this.owner.prefix+'_itm'){
			var uid = sib.id.substring(sib.id.length,this.owner.prefix.length+4);
			res = useitems?this.owner.items[uid]:new TreeViewItem(uid,this.owner);
			break;
		}
		sib = sib.previousSibling;
	}
	return res;
}
TreeViewPageBlock.prototype.getNextItem = function(useitems){
	if(useitems!==false) useitems = true;
	if(!this.containertag) return null;
	var res = null;
	var sib = this.containertag.nextSibling;
	while(sib != null){
		if(sib.tagName == 'DIV' && sib.id.substring(0,this.owner.prefix.length+4) == this.owner.prefix+'_itm'){
			var uid = sib.id.substring(sib.id.length,this.owner.prefix.length+4);
			res = useitems?this.owner.items[uid]:new TreeViewItem(uid,this.owner);
			break;
		}
		sib = sib.nextSibling;
	}
	return res;
}
TreeViewPageBlock.prototype.getPreviousPageBlock = function(){
	var previous = null;
	for(key in this.parent.pageblocks){
		var block = this.parent.pageblocks[key];
		if(block == this) break;
		else if(block != null) previous = block;
	}
	return previous;
}
TreeViewPageBlock.prototype.countPreviousItems = function(){
	if(!this.containertag) return 0;
	var res = 0;
	var sib = this.containertag.previousSibling;
	while(sib != null){
		if(sib.tagName == 'DIV' && sib.id.substring(0,this.owner.prefix.length+4) == this.owner.prefix+'_itm'){
			res++;
		}else if(sib.tagName == 'DIV' && sib.id.substring(0,this.owner.prefix.length+4) == this.owner.prefix+'_pag'){
			break;
		}
		sib = sib.previousSibling;
	}
	return res;
}
TreeViewPageBlock.prototype.getFirstPage = function(){
	if(!this.divtag) return null;
	var link = this.divtag.firstChild;
	while(link != null && link.tagName != 'A'){
		link = link.nextSibling;
	}
	return link!=null&&link.nodeType==1?trim(link.innerHTML,true):null;
}
TreeViewPageBlock.prototype.getLastPage = function(){
	if(!this.divtag) return null;
	var link = this.divtag.childNodes[this.divtag.childNodes.length-1];
	while(link != null && link.tagName != 'A'){
		link = link.previousSibling;
	}
	return link.nodeType==1?trim(link.innerHTML,true):null;
}
TreeViewPageBlock.prototype.removeFirstPage = function(){
	if(!this.divtag) return null;
	var link = this.divtag.firstChild;
	while(link != null && link.tagName != 'A'){
		link = link.nextSibling;
	}
	if(link != null){
		if(link.previousSibling != null && link.previousSibling.nodeType == 3) this.divtag.removeChild(link.previousSibling);
		this.divtag.removeChild(link);
	}
	this.containertag.setAttribute('startpage',this.startPage + 1);
}
TreeViewPageBlock.prototype.prependPage = function(page){
	if(!this.divtag) return null;
	var link = document.createElement('a');
	link.href = 'javascript:' + this.owner.name + '.OpenSection(\'' + this.owner.prefix + '\',\'' + this.parent.uniqueid + '\',\'' + this.uniqueid + '\',' + page + ');';
	link.innerHTML = page;
	this.divtag.insertBefore(document.createTextNode(' '),this.divtag.firstChild);
	this.divtag.insertBefore(link,this.divtag.firstChild);
	this.containertag.setAttribute('startpage', page);
}
TreeViewPageBlock.prototype.mergePrevious = function(page){
	var previousblock = this.getPreviousPageBlock();
	previousblock.containertag.setAttribute('endpage', this.endPage);
	var regex = new RegExp("'"+this.uniqueid+"'","g");
	previousblock.divtag.innerHTML += this.divtag.innerHTML.replace(regex,"'"+previousblock.uniqueid+"'");
	this.parent.pageblocks[this.uniqueid] = null;
	this.containertag.parentNode.removeChild(this.containertag);
	previousblock.SetExpanderImage(true);
}

TreeViewPageBlock.prototype.checkNumbers = function(){
	var previousblock = this.getPreviousPageBlock();
	var previouspage = previousblock==null?0:previousblock.getLastPage();
	var previousitems = this.countPreviousItems();
	var firstpage = this.getFirstPage();
	var space = (firstpage - previouspage - 1) * this.owner.sectionsize;
	if(previousitems < space){
		// not enough items - add a new one
		this.prependPage(firstpage-1);
	}else if(previousitems >= (space + this.owner.sectionsize)){
		// too many items - remove one paging number
		this.removeFirstPage();
	}
	if(previousitems==0 && previousblock!=null){
		this.mergePrevious();
	}else{	
		// check that there are still numbers
		firstpage = this.getFirstPage();
		if(firstpage==null){
			// remove this pageblock entirely.
			this.containertag.parentNode.removeChild(this.containertag);
			this.parent.pageblocks[this.uniqueid] = null;
		}
	}
}

/* ===================================================
					Drag and Drop Events
=================================================== */
var TreeviewDragObject = null;

function TreeviewMouseDown(e){
	e = e || window.event;
	var target = e.target != null ? e.target : e.srcElement;
	if(target.id){
		var pos = target.id.indexOf('_ico');
		if(pos > -1){
			// identify the tree and the item
			var prefix = target.id.substring(0,pos);
			var uniqueid = target.id.substring(pos+4);
			eval('var treeview = ' + prefix + '_treeview');
			TreeviewDragObject = treeview.items[uniqueid];
			
			if(!TreeviewDragObject.IsDisabled() && (!TreeviewDragObject.owner.dragdropstartcallback || TreeviewDragObject.owner.dragdropstartcallback(TreeviewDragObject))){
				// create the shadow branch to follow the mouse
				// TODO: make the shadow appear where the treeviewdragobject would have been, rather than floating.
				var div = document.createElement('div');
				div.style.position = 'absolute';
				var pos = new MousePosition(e);
				div.style.left = pos.posx+'px';
				div.style.top = pos.posy+'px';
				var shadow = TreeviewDragObject.containertag.cloneNode(true);
				TreeviewDragObject.containertag.style.display = 'none';
				div.appendChild(shadow);
				div.className = treeview.classnames['shadow'];
				TreeviewDragObject.shadow = div;
				document.body.appendChild(div);
				// hide text selections
				if (typeof treeview.container.onselectstart!="undefined") treeview.container.onselectstart=function(){return false};
				else if (typeof treeview.container.style.MozUserSelect!="undefined") treeview.container.style.MozUserSelect="-moz-none";
				
				// remove extra lines from the shadow div
				var shadowdivs = shadow.getElementsByTagName('div');
				var level = TreeviewDragObject.getLevel();
				for(key in shadowdivs){
					var sdiv = shadowdivs[key];
					if(sdiv.id && sdiv.id.indexOf(prefix+'_div') >= 0){
						for(i=0;i<(level-(treeview.showroot?1:2));i++){
							if(!sdiv.firstChild || !sdiv.firstChild.firstChild) break;
							sdiv.firstChild.removeChild(sdiv.firstChild.firstChild);
						}
					}
				}
				
				// set the top and left positions of the container
				var cont = TreeviewDragObject.owner.container;
				cont.left = getAbsLeft(cont);
				cont.top = getAbsTop(cont);
				
				// set the Hotspots. Do this after the moving item has been hidden.
				treeview.PopulateHotspotArray();
				
				// attach mousemove and mouseup events
				attachEventHandler(document,'mousemove',TreeviewMouseMove);
				attachEventHandler(document,'mouseup',TreeviewMouseUp);
			}
		}
	}
	return false;
}

function TreeviewMouseUp(e){
	if(TreeviewDragObject){
		e = e || window.event;
		clearTimeout(window.TreeviewScrollAction);
		clearTimeout(window.TreeviewExpandAction);
		window.TreeviewScrollAction = null;
		window.TreeviewExpandAction = null;
		releaseEventHandler(document,'mousemove',TreeviewMouseMove);
		releaseEventHandler(document,'mouseup',TreeviewMouseUp);
		TreeviewDragObject.shadow.parentNode.removeChild(TreeviewDragObject.shadow);
		
		// handle drop
		if(TreeviewDragObject.overitem){
			// set the overitem to the first child of the overitem, if it is open
			if(TreeviewDragObject.overitem.getExpandStatus() == 'open'){
				var childobj = TreeviewDragObject.overitem.getChildren(true)[0];
				if(childobj){
					TreeviewDragObject.overitem = childobj;
					TreeviewDragObject.dropitembefore = true;
				}else{
					TreeviewDragObject.dropitembefore = false;
				}
			}else{
				TreeviewDragObject.dropitembefore = false;
			}
			if(TreeviewDragObject.overitem.uniqueid != TreeviewDragObject.uniqueid && (!TreeviewDragObject.owner.dragdropendcallback || TreeviewDragObject.owner.dragdropendcallback(TreeviewDragObject))){
				var oldprevious = TreeviewDragObject.getPreviousSibling();
				var oldblock = TreeviewDragObject.getNextPageBlock();
				TreeviewDragObject.containertag.parentNode.removeChild(TreeviewDragObject.containertag);
				if(!TreeviewDragObject.overitem.containertag.parentNode){
					var check = 1;
				}
				TreeviewDragObject.overitem.containertag.parentNode.insertBefore(TreeviewDragObject.containertag, (TreeviewDragObject.dropitembefore?TreeviewDragObject.overitem.containertag:TreeviewDragObject.overitem.containertag.nextSibling));
				TreeviewDragObject.containertag.style.display = 'block';
				TreeviewDragObject.SetLines();
				TreeviewDragObject.SetExpanderImage();
				if(oldprevious){
					oldprevious.SetExpanderImage();
				}
				if(TreeviewDragObject.IsSelected()) TreeviewDragObject.owner.Select(TreeviewDragObject.uniqueid);
				var newblock = TreeviewDragObject.getNextPageBlock();
				if(oldblock!=null) oldblock.checkNumbers();
				if(newblock!=null) newblock.checkNumbers();
			}else{		
				TreeviewDragObject.containertag.style.display = 'block';		
			}
		}else{		
			TreeviewDragObject.containertag.style.display = 'block';		
		}
		
		// clean up and release all drag and drop settings
		if (typeof TreeviewDragObject.owner.container.onselectstart!="undefined") TreeviewDragObject.owner.container.onselectstart=null;
		else if (typeof TreeviewDragObject.owner.container.style.MozUserSelect!="undefined") TreeviewDragObject.owner.container.style.MozUserSelect="text";
		TreeviewDragObject = null;
	}
}

function TreeviewMouseMove(e){
	if(TreeviewDragObject){
		var pos = new MousePosition(e);
		var cont = TreeviewDragObject.owner.container;
		if(pos.posx < cont.left || pos.posx > cont.left + cont.offsetWidth || pos.posy < cont.top || pos.posy > cont.top + cont.offsetHeight){
			// Not in the tree
			if(TreeviewDragObject.shadow.parentNode != document.body){
				TreeviewDragObject.shadow.parentNode.removeChild(TreeviewDragObject.shadow);
				document.body.appendChild(TreeviewDragObject.shadow);
				TreeviewDragObject.shadow.style.position = 'absolute';
				TreeviewDragObject.shadow.style.paddingLeft = '0px';
			}
			TreeviewDragObject.shadow.style.left = pos.posx+'px';
			TreeviewDragObject.shadow.style.top = pos.posy+'px';
			TreeviewDragObject.cancelscroll = true;
			TreeviewDragObject.cancelexpand = true;
			TreeviewDragObject.overitem = null;
		}else{
			// In the tree
			pos.treex = pos.posx + cont.scrollLeft - cont.left;
			pos.treey = pos.posy + cont.scrollTop - cont.top;
			pos.scrolly = pos.treey - cont.scrollTop;
			
			// identify which item is being hovered over
			var overitem = null;
			for(h in TreeviewDragObject.owner.hotspots){
				if(h < pos.treey){
					overitem = TreeviewDragObject.owner.hotspots[h];
				}else{
					break;
				}
			}
			if(overitem && overitem.getTreeTop() + overitem.containertag.offsetHeight < pos.treey){
				overitem = null;
			}
			
			if(overitem){
				if(TreeviewDragObject.overitem != overitem){
					TreeviewDragObject.shadow.parentNode.removeChild(TreeviewDragObject.shadow);
					TreeviewDragObject.shadow.style.position = 'static';
					TreeviewDragObject.shadow.style.paddingLeft = ((overitem.getLevel()-(overitem.owner.showroot?1:2)) * overitem.owner.iconwidth) + (overitem.getExpandStatus()=='open'?overitem.owner.iconwidth:0) + 'px';
					if(overitem.childdiv && overitem.childdiv.style.display == 'block'){
						overitem.childdiv.insertBefore(TreeviewDragObject.shadow,overitem.childdiv.firstChild);
					}else{
						overitem.containertag.parentNode.insertBefore(TreeviewDragObject.shadow,overitem.containertag.nextSibling);
					}
				}
				// Decide whether or not to scroll up or down
				if(pos.scrolly < overitem.owner.iconheight || pos.scrolly > cont.offsetHeight - overitem.owner.iconheight){
					TreeviewDragObject.cancelscroll = false;
					if(!window.TreeviewScrollAction){
						window.TreeviewScrollAction = setTimeout('TreeviewScroll(' + (pos.scrolly < overitem.owner.iconheight?-1:1) + ');',500);
					}
				}else{
					TreeviewDragObject.cancelscroll = true;				
				}
				// Decide whether or not to expand the currently hovered item.
				if(TreeviewDragObject.ExpandActionItem != overitem){
					TreeviewDragObject.cancelexpand = true;	
					clearTimeout(window.TreeviewExpandAction);
					window.TreeviewExpandAction = null;
					TreeviewDragObject.ExpandActionItem = null;
				}
				if(overitem.getExpandStatus() == 'closed'){
					TreeviewDragObject.cancelexpand = false;
					if(!window.TreeviewExpandAction){
						TreeviewDragObject.ExpandActionItem = overitem;
						window.TreeviewExpandAction = setTimeout('TreeviewExpand();',1000);
					}
				}
			}else{
				if(TreeviewDragObject.shadow.parentNode != document.body){
					TreeviewDragObject.shadow.parentNode.removeChild(TreeviewDragObject.shadow);
					document.body.appendChild(TreeviewDragObject.shadow);
					TreeviewDragObject.shadow.style.position = 'absolute';
					TreeviewDragObject.shadow.style.paddingLeft = '0px';
				}
				TreeviewDragObject.shadow.style.left = pos.posx+'px';
				TreeviewDragObject.shadow.style.top = pos.posy+'px';
				TreeviewDragObject.cancelscroll = true;
				TreeviewDragObject.cancelexpand = true;
			}
			
			TreeviewDragObject.overitem = overitem;
		}
	}
	return false;
}

function TreeviewScroll(direction){
	if(TreeviewDragObject && !TreeviewDragObject.cancelscroll){
		TreeviewDragObject.owner.container.scrollTop += (TreeviewDragObject.owner.iconheight*direction);
		window.TreeviewScrollAction = setTimeout('TreeviewScroll(' + direction + ');',200);
	}else{
		clearTimeout(window.TreeviewScrollAction);
		window.TreeviewScrollAction = null;
	}
}

function TreeviewExpand(){
	if(TreeviewDragObject && !TreeviewDragObject.cancelexpand){
		// Remove the shadow from the tree before expanding, so the re-population of the hotspot array doesn't count it.
		TreeviewDragObject.shadow.parentNode.removeChild(TreeviewDragObject.shadow);
		TreeviewDragObject.shadow.style.position = 'static';
		TreeviewDragObject.shadow.style.paddingLeft = ((TreeviewDragObject.overitem.getLevel()-(TreeviewDragObject.overitem.owner.showroot?1:2)) * TreeviewDragObject.overitem.owner.iconwidth) + TreeviewDragObject.overitem.owner.iconwidth + 'px';
		
		TreeviewDragObject.owner.Expand(TreeviewDragObject.owner.prefix,TreeviewDragObject.overitem.uniqueid);
		
		// reposition the shadow to the top of the opened branch, if the branch is not being opened asynchronously by AJAX
		if(TreeviewDragObject.overitem.getChildren().length || !TreeviewDragObject.owner.useajax || !agent){
			TreeviewDragObject.overitem.childdiv.insertBefore(TreeviewDragObject.shadow,TreeviewDragObject.overitem.childdiv.firstChild);
		}
	}else{
		clearTimeout(window.TreeviewExpandAction);
	}
	window.TreeviewExpandAction = null;
}