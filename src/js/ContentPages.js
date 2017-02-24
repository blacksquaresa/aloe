var currentpage = null;
var actionitemid = null;
var tab0formcontent = '';

// Loading methods
function drawloading(targetelement){
	var target = document.getElementById(targetelement);
	var loading = '<table width="100%" height="' + target.offsetHeight + 'px" class="lvloading"><tr><td align="center">Loading...</td></tr></table>';
	target.innerHTML = loading;
}

// Call Back Methods
function ContextMenuShowCallback(item){
	var children = item.getChildren();
	var haschildren = children && children.length;
	ContextMenu.disableMenuItem('ctxmenu_addpage',item.objectid==0)
	ContextMenu.disableMenuItem('ctxmenu_deletepage',item.objectid==0||item.icon.substring(0,1)=='d'||item.state=='disabled')
	ContextMenu.disableMenuItem('ctxmenu_openpage',item.objectid==0||item.IsSelected()||item.state=='disabled')
	ContextMenu.disableMenuItem('ctxmenu_moveup',item.objectid==0||item.getPreviousSibling()==null)
	ContextMenu.disableMenuItem('ctxmenu_movedown',item.objectid==0||item.getNextSibling()==null)
	ContextMenu.disableMenuItem('ctxmenu_orderalpha',item.objectid==0 || !haschildren)	
	return true;
}
function ContextMenuHideCallback(){
	return true;
}

// Action Methods
// Open a page
function openPage_CM(item){
	openPage(item.objectid);
	item.owner.Select(item.uniqueid);
}

function openPage(id){
	PopupManager.showLoading();
	showTab(1);
	var tab0 = document.getElementById('tbp0');
	var tab1 = document.getElementById('tbp1');
	if(tab0formcontent=='') tab0formcontent = tab0.innerHTML;
	drawloading('tbp0');
	drawloading('tbp1');
	var page = agent.call('../ajax/Content.ajax.php','AJ_getPage','',id);
	if(page.substring(0,1) == '{'){
		currentpage = JSON.parse(page);
		// draw content tab
		if(currentpage.type == 'link'){
			tab1.innerHTML = '<br /><div class="note"><p><b>Link Page</b></p><p>This is a Link page, and has no content</p></div><br />';
			showTab(0);
		}else if(currentpage.type == 'label'){
			tab1.innerHTML = '<br /><div class="note"><p><b>Menu Label</b></p><p>This is a menu label, and has no content</p></div><br />';
			showTab(0);
		}else if(currentpage.parent==null){
			tab1.innerHTML = '<br /><div class="note"><p><b>Menu</b></p><p>Please select a page from the menu to edit.</p></div><br />';
			showTab(0);
		}else if(currentpage.canedit){
			tab1.innerHTML = currentpage.pagecontent;
			contentBlockManager.Init(currentpage);
		}else{
			tab1.innerHTML = '<br /><div class="note"><p><b>Special Page</b></p><p>This is a special page, and the contents cannot be edited here</p></div><br />';
			showTab(0);
		}
		// draw details tab
		if(currentpage.parent==null){
			tab0.innerHTML = '<br /><div class="note"><p><b>Menu</b></p><p>Please select a page from the menu to edit.</p></div><br />';
		}else{
			tab0.innerHTML = tab0formcontent;
			document.getElementById('cpd_id').value = id;
			document.getElementById('cpd_title').value = currentpage.title;
			document.getElementById('cpd_keywords').value = currentpage.keywords;
			document.getElementById('cpd_description').value = currentpage.description;
			document.getElementById('cpd_menuname').value = currentpage.menuname;
			document.getElementById('cpd_forwardurl').value = currentpage.forwardurl;
			document.getElementById('cpd_friendlyurl').innerHTML = currentpage.friendlyurl;
			document.getElementById('cpd_friendlyurl').title = currentpage.friendlyurl;
			document.getElementById('cpd_reseturlbutton').style.visibility = (currentpage.type=='link'?'hidden':'visible');
			document.getElementById('cpd_specialpage').checked = (currentpage.type=='link'&&currentpage.specialpage=='_blank');
			document.getElementById('cpd_published').checked = (currentpage.published>0);
			document.getElementById('cpd_published').style.visibility = (currentpage.parent==null)?'hidden':'visible';
			document.getElementById('cpd_date').innerHTML = currentpage.displaydate;
			document.getElementById('cpd_updated').innerHTML = currentpage.displayupdated;
			
			document.getElementById('cpd_row_forward').style.display = (currentpage.type=='link'?'':'none');
			document.getElementById('cpd_row_special').style.display = (currentpage.type=='link'?'':'none');
			document.getElementById('cpd_row_friendly').style.display = (currentpage.type!='link'?'':'none');
			document.getElementById('cpd_row_typeselect').style.display = (currentpage.type=='special'?'none':'');
			document.getElementById('cpd_row_typespecial').style.display = (currentpage.type=='special'?'':'none');
			var sel = document.getElementById('cpd_type');
			for(i=0;i<sel.length;i++){
				if(sel[i].value == currentpage.type){
					sel.selectedIndex = i;
					break;
				}
			}
			if(currentpage.custom){
				for(key in currentpage.custom){
					if(key != '_settings'){
						setCustomSetting(currentpage.custom,'cpd_custom',key,false);
					}
				}
			}
		}
		
		pageid = id;
		pagename = currentpage.menuname;
		// save the current selection to the page state 
		var res = agent.call('../../ajax/PageState.ajax.php','SetStateItems','','contentstate',{'currentpage':id});
		PopupManager.hideLoading();
	}else{
		if(page.length > 255) page = page.substring(0,255) + '...';
		PopupManager.showError('Sorry, there was an error opening the selected page.'+(page==''?'':'<br />'+page));
		PopupManager.hideLoading();
		tab0.innerHTML = '';
		tab1.innerHTML = '';
	}
}

// Delete a page
function deletePage_CM(item){
	var par = item.getParent();
	var intree = item.InChildTree(pageid);
	if(confirm('Are you sure you want to delete the ' + item.text + ' page?')){
		item.owner.ShowLoading();
		res = agent.call('../ajax/Content.ajax.php','AJ_deletePage','',item.objectid);
		if(res=='success'){
			if(intree){
				openPage(par.objectid);
			}
			item.deleteItem();
		}else{
			PopupManager.showError(res);
		}		
		item.owner.HideLoading();
	}
}

//Order Pages Alphabetical
function OrderPagesAlphab_CM(item){
	if(confirm('Are you sure you want to order the sub pages of the ' + item.text + ' page alphabetically?')){
		item.owner.ShowLoading();
		res = agent.call('../ajax/Content.ajax.php','AJ_OrderPagesAlphabetically','',item.objectid);
		if(res.substring(0,1)=='{'){
			res = JSON.parse(res);
			var children = item.getChildren();
			for(pos in res){
				var objid = res[pos];
				if(objid > 0){
					for(var i=0;i<children.length;i++){
						if(children[i].objectid == objid){
							children[i].moveTo(item,pos-1);
							children.splice(i,1);
							break;
						}
					}
				}
			}
		}else{
			PopupManager.showError(res);
		}
		item.owner.HideLoading();
	}
}

function MovePageUp_CM(item){
	return movePage(-1,item);
}

function MovePageDown_CM(item){
	return movePage(1,item);
}

// Move a page up or down
function movePage(ind,item){	
	item.owner.ShowLoading();
	res = agent.call('../ajax/Content.ajax.php','AJ_movePage'+(ind<0?'Up':'Down'),'',item.objectid);
	if(res=='success'){
		item.moveTo(item.getParent(),item.getIndex()+ind);
	}else{
		PopupManager.showError(res);
	}
	item.owner.HideLoading();
}

// Publish the current page
function togglePublishPage(){
	var check = document.getElementById('cpd_published');
	if(check){
		if(check.checked) publishPage();
		else hidePage();
	}
}
function publishPage(){
	PopupManager.showLoading();
	res = agent.call('../ajax/Content.ajax.php','AJ_PublishPage','',pageid);
	if(res=='success'){
		var publishcheck = document.getElementById('cpd_published');
		publishcheck.checked = true;
		PopupManager.hideLoading();
	}else{
		PopupManager.showError(res);
		PopupManager.hideLoading();
	}
}
function hidePage(){
	PopupManager.showLoading();
	res = agent.call('../ajax/Content.ajax.php','AJ_HidePage','',pageid);
	if(res=='success'){
		var publishcheck = document.getElementById('cpd_published');
		publishcheck.checked = false;
		PopupManager.hideLoading();
	}else{
		PopupManager.showError(res);
		PopupManager.hideLoading();
	}
}

// Create a new page
function createPage_CM(item){
	createPage_Open(item.objectid,item.uniqueid)
}

function createPage_Open(parentid,activetreeviewid){
	PopupManager.showDisabled();
	actionitemid = activetreeviewid;
	var pop = PopupManager.createOrFetchPopup('createpage','Create Page',480,300,'div','content_createpage','disabled');
	var replacements = { 'crp_title':'','crp_menuname':'','crp_keywords':'','crp_description':'','crp_forwardurl':'','crp_parent' : parentid };
	pop.Show(replacements);
	pop.SetSize();
}

function createPage_Cancel(){
	PopupManager.hidePopup('createpage');
	PopupManager.hideDisabled();
}

function createPage_Save(){
	var parentid = document.getElementById('crp_parent').value;
	var title = document.getElementById('crp_title').value;
	var keywords = document.getElementById('crp_keywords').value;
	var description = document.getElementById('crp_description').value;
	var menuname = document.getElementById('crp_menuname').value;
	var type = document.getElementById('crp_type');
	var forwardurl = document.getElementById('crp_forwardurl').value;
	var specialpage = document.getElementById('crp_specialpage');
	var template = document.getElementById('crp_template').value;
	var published = document.getElementById('crp_published').checked;
	var msg = '';
	if(menuname=='') msg += ' - Please provide a name for this page.\r\n';
	var custom = populateCustomArray('crp_custom');
	
	if(msg == ""){	
		PopupManager.showLoading();
		if(title=='') title = menuname;
	    id = agent.call('../ajax/Content.ajax.php','AJ_createPage','',parentid,title,keywords, description, menuname, type[type.selectedIndex].value, forwardurl, specialpage.checked?specialpage.value:'', template, published?1:0, custom);
	    if(isnumeric(id)){
			var item = trv_treeview.items[actionitemid];
			var newchildid = item.addItem(title,"javascript:openPage(" + id + ")","",'tree_page.gif',id,null);
			if(item.icon.indexOf('tree_page') >= 0){
				var iconname = item.state=='disabled'?'tree_menu.gif':'tree_folder.gif';
				item.SetIcon(iconname);
			}
			PopupManager.hidePopup('createpage');
			PopupManager.hideDisabled();
		    PopupManager.hideLoading();
	    }else{
		    PopupManager.showError(id);
		    PopupManager.hideLoading();
	    }
	}else{
	    PopupManager.showError('There were problems creating this page:\r\n' + msg);
	}
}

// Callback function, which is passed the TreeviewDragObject
function DragDropCallback(tdo){
	if(tdo.overitem){
		PopupManager.showLoading();
		var parentid = tdo.overitem.objectid;
		var index = 0;
		var status = tdo.overitem.getExpandStatus();
		var par = tdo.overitem.getParent();
		if(par == null){
			if(status == 'none'){
				var brdiv = document.createElement('div');
				brdiv.setAttribute('id',tdo.overitem.owner.prefix+'_br'+tdo.overitem.uniqueid);
				tdo.overitem.divtag.parentNode.insertBefore(brdiv,tdo.overitem.divtag.nextSibling);
				tdo.overitem.childdiv = tdo.overitem.owner.getElement(tdo.overitem.owner.prefix+'_br'+tdo.overitem.uniqueid);
			}
			var phantom = document.createElement('div');
			tdo.overitem.childdiv.appendChild(phantom);
			tdo.overitem = {'containertag' : phantom};			
		}else if(status != 'open'){
			parentid = par.objectid;
			index = tdo.overitem.getIndex()+(tdo.dropitembefore?0:1);
			if(parentid==tdo.getParent().objectid && index > tdo.getIndex()) index--;
		}
		res = agent.call('../ajax/Content.ajax.php','AJ_movePageTo','',tdo.objectid,parentid,index+1);
		if(res=='success'){
			PopupManager.showCompleted();
			PopupManager.hideLoading();
			return true;
		}else{
			PopupManager.showError(res);
			PopupManager.hideLoading();
			return false;
		}
	}
}

function updatePage_Save(){
	PopupManager.showLoading();
	var id = document.getElementById('cpd_id').value;
	var title = document.getElementById('cpd_title').value;
	var keywords = document.getElementById('cpd_keywords').value;
	var description = document.getElementById('cpd_description').value;
	var menuname = document.getElementById('cpd_menuname').value;
	var type = document.getElementById('cpd_type');
	var forwardurl = document.getElementById('cpd_forwardurl').value;
	var specialpage = document.getElementById('cpd_specialpage');
	specialpage = specialpage.checked?specialpage.value:currentpage.specialpage;
	var custom = populateCustomArray('cpd_custom');
	
	type = currentpage.type=='special'?'special':type[type.selectedIndex].value;
	
	res = agent.call('../ajax/Content.ajax.php','AJ_updatePage','',id,title,keywords, description, menuname, type, forwardurl, specialpage, custom);
	if(res=='success'){
		if(currentpage.menuname != menuname){
			trv_treeview.selecteditem.SetText(menuname);
		}
		if(currentpage.type != type){
			openPage(id);
			showTab(0);
		}else{
			currentpage.title = document.getElementById('cpd_title').value = title;
			currentpage.keywords = document.getElementById('cpd_keywords').value = keywords;
			currentpage.description = document.getElementById('cpd_description').value = description;
			currentpage.menuname = document.getElementById('cpd_menuname').value = menuname;
			currentpage.forwardurl = document.getElementById('cpd_forwardurl').value = forwardurl;
			currentpage.specialpage = specialpage;
		}
		PopupManager.hideLoading();
		PopupManager.showCompleted();
	}else{
		PopupManager.showError(res);
		PopupManager.hideLoading();
	}
}

// Reset URL
function resetURL_Open(){
	PopupManager.showLoading();
	var replacements = { 
	'ru_path' : currentpage.path + '/',
	'ru_pathstub' : currentpage.pathstub
	};
	var pop = PopupManager.createOrFetchPopup('content_reseturl','Reset URL',480,200,'div','content_reseturl','loading');
	pop.Show(replacements);
	pop.SetSize(480,0);
}

function resetURL_Cancel(){
	PopupManager.hidePopup('content_reseturl');
	PopupManager.hideLoading();
}

function resetURL_Save(){
	PopupManager.hidePopup('content_reseturl');
	var pathstub = document.getElementById('ru_pathstub').value;
	var msg = '';
	if(msg == ""){	
	    res = agent.call('../ajax/Content.ajax.php','AJ_resetPageURL','',currentpage.id,pathstub);
		if(res.substring(0,1)=='['){
			res = eval(res);
			currentpage.pathstub = res[0];
			document.getElementById('cpd_friendlyurl').innerHTML = res[1];
			PopupManager.showMessage('Thank you - this page URL has been updated, with all the links we could find pointing to it.');
		}else{
			PopupManager.showError(res,'error');
		}
	}else{
	    alert('There were problems creating this page:\r\n' + msg);
	}
	PopupManager.hideLoading();
}

// Templates
function resetTemplates(id){
	var container = document.getElementById(id+'_container');
	if(container){
		var blocks = container.getElementsByTagName('div');
		if(blocks){
			for(var i=0;i<blocks.length;i++){
				blocks[i].className = 'template_block';
			}
		}
	}
	var hidden = document.getElementById(id);
	if(hidden) hidden.value = '';
}

function selectTemplate(id,tempid){
	resetTemplates(id);
	var block = document.getElementById(id+'_'+tempid);
	if(block) block.className = 'template_selected';
	var hidden = document.getElementById(id);
	if(hidden) hidden.value = tempid;
}

// Custom Settings
function setCustomSetting(custom,arrayname,name,usedefault){
	var type = custom._settings[name].type;
	switch(type){
		case 'array':
			var elem = document.getElementById(arrayname+'_'+name);
			if(elem){
				var value = usedefault?custom._settings[name]['default']:custom[name];
				if(!value.join) value = JSON.parse(value);
				if(value instanceof Object){
					var temp = [];
					for(key in value) temp.push(value[key]);
					value = temp;
				}
				elem.value = value.join("\r\n");	
			}
			break;
		default:
			var elem = document.getElementById(arrayname+'_'+name);
			if(elem) elem.value = usedefault?custom._settings[name]['default']:custom[name];
			break;
	}
}

function populateCustomArray(elemid){
	var customcontainer = document.getElementById(elemid+'_container');
	var custom = {};
	if(customcontainer){
		var inputs = customcontainer.getElementsByTagName('input');
		for(var i=0;i<inputs.length;i++){
			if(inputs[i].id.substring(0,elemid.length) == elemid){
				var inputname = inputs[i].id.substring(elemid.length+1);
				custom[inputname] = Base64.encode(inputs[i].value);
			}
		}
		var textareas = customcontainer.getElementsByTagName('textarea');
		for(var i=0;i<textareas.length;i++){
			if(textareas[i].id.substring(0,elemid.length) == elemid){
				var textareaname = textareas[i].id.substring(elemid.length+1);
				custom[textareaname] = Base64.encode(textareas[i].value);
			}
		}
		var selects = customcontainer.getElementsByTagName('select');
		for(var i=0;i<selects.length;i++){
			if(selects[i].id.substring(0,elemid.length) == elemid){
				var selectname = selects[i].id.substring(elemid.length+1);
				custom[selectname] = Base64.encode(selects[i][selects[i].selectedIndex].value);
			}
		}
	}
	return custom;
}

