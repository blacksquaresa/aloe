var rs_selectedfiles = [];
var rsv_thumbextensions = ['gif','png','jpg','jpeg'];
var currentaction = 'none';
var rsvtreeview;
var temptype;

function rs_init(){
	if(!window.PopupManager){
		var par = parent;
		while(par && !(PopupManager=par.PopupManager)){
			par = par.parent;
		}
	}
	PopupManager.ResourceWindow = window;
	eval('rsvtreeview = '+rsv_prefix+'trv_treeview');
	rs_setFolderIcons();
}

function rs_refreshFiles(callback,type,display,mode,path){
	rs_clearSelection();
	document.getElementById('rs_resourcecontainer').innerHTML = '';
	PopupManager.showLoading();
	var details = rs_getdetails();
	if(path) details.path = path;
	if(callback){
		agent.call('','drawResourceManager',callback,details.sourceid,details.owner,details.path,type,display,mode);
	}else{
		var response = agent.call('','drawResourceManager','',details.sourceid,details.owner,details.path,type,display,mode);
		if(response === '0'){
			PopupManager.showError('Oops, there was an error.');
			PopupManager.hideLoading();
		}else{
			document.getElementById('rs_resourcecontainer').innerHTML = response;
			PopupManager.hideLoading();
		}
	}
}

function rs_refreshFolders(callback,path){
	document.getElementById('rs_treeview').innerHTML = '';
	PopupManager.showLoading();
	if(callback){
		agent.call(rsv_ajaxpath,'ResourceManager_ReloadTreeview',callback,path,rsv_prefix);
	}else{
		var response = agent.call(rsv_ajaxpath,'ResourceManager_ReloadTreeview','',path,rsv_prefix);
		if(response === '0'){
			PopupManager.showError('Oops, there was an error.');
			PopupManager.hideLoading();
		}else{
			document.getElementById('rs_treeview').innerHTML = response;
			rsvtreeview.LoadItems();
			PopupManager.hideLoading();
		}
	}
}

function rs_filetypeChanged(elem){
	var val = elem.value;
	if(val != currenttype){
		rs_refreshFiles('rs_filetypeChangedCallback',val,currentdisplay,currentmode);
		temptype = val;
	}
}

function rs_filetypeChangedCallback(response){
	if(response === '0'){
		PopupManager.showError('Oops, there was an error.');
		PopupManager.hideLoading();
	}else{
		document.getElementById('rs_resourcecontainer').innerHTML = response;		
		currenttype = temptype;
		PopupManager.hideLoading();
	}
}

function rs_changeMode(mode){
	document.getElementById('rsv_mode').value = mode;
	rs_refreshFiles('rs_modeChangedCallback',currenttype,currentdisplay,mode);
}

function rs_modeChangedCallback(response){
	if(response === '0'){
		PopupManager.showError('Oops, there was an error.');
		PopupManager.hideLoading();
	}else{
		document.getElementById('rs_resourcecontainer').innerHTML = response;	
		currentmode = document.getElementById('rsv_mode').value;
		if(currentmode=='select'){
			document.getElementById('rsm_manage').style.display = '';
			document.getElementById('rsm_manageicons').style.display = 'none';	
			document.getElementById('rsm_managefolders').style.display = 'none';
			document.getElementById('rsm_select').style.display = 'none';
		}else{
			document.getElementById('rsm_manage').style.display = 'none';
			document.getElementById('rsm_manageicons').style.display = 'inline-block';
			document.getElementById('rsm_managefolders').style.display = 'inline-block';
			document.getElementById('rsm_select').style.display = '';
		}
		PopupManager.hideLoading();
	}
}

function rs_changeDisplay(display){
	document.getElementById('rsv_display').value = display;
	rs_refreshFiles('rs_displayChangedCallback',currenttype,display,currentmode);
}

function rs_displayChangedCallback(response){
	if(response === '0'){
		PopupManager.showError('Oops, there was an error.');
		PopupManager.hideLoading();
	}else{
		document.getElementById('rs_resourcecontainer').innerHTML = response;	
		currentdisplay = document.getElementById('rsv_display').value;
		if(currentdisplay=='details'){
			rs_setIconEx('rsd_details',true);
			rs_setIconEx('rsd_icons',false);
		}else{
			rs_setIconEx('rsd_details',false);
			rs_setIconEx('rsd_icons',true);		
		}
		PopupManager.hideLoading();
	}
}

function rs_selectFolder(path,rights){
	switch(currentaction){
		case 'movefile':
			rs_movefile_save(path);
			break;
		case 'copyfile':
			rs_copyfile_save(path);
			break;
		case 'movefolder':
			rs_movefolder_save(path);
			break;
		case 'copyfolder':
			rs_copyfolder_save(path);
			break;
		default:
			currentpath = path;
			currentuid = rsvtreeview.getItemByObjectId(trim(currentpath,'/'));
			currentrights = rights;
			rs_refreshFiles('rs_folderChangedCallback',currenttype,currentdisplay,currentmode,path);
	}
}

function rs_folderChangedCallback(response){
	if(response === '0'){
		PopupManager.showError('Oops, there was an error.');
		PopupManager.hideLoading();
	}else{
		var show = currentrights.indexOf('w')>=0;
		document.getElementById('rsm_managebar').style.display = show?'inline-block':'none';
		document.getElementById('rsm_resourceupload').style.display = show?'block':'none';
		document.getElementById('rs_resourcecontainer').innerHTML = response;	
		document.getElementById('newfilepath').value = currentpath;
		rs_setFolderIcons();
		PopupManager.hideLoading();
	}
}

function rs_setFolderIcons(){
	rs_setIconEx('rsm_addfolder',currentrights.indexOf('t')>=0,true);
	rs_setIconEx('rsm_renamefolder',currentrights.indexOf('m')>=0,true);
	rs_setIconEx('rsm_movefolder',currentrights.indexOf('m')>=0,true);
	rs_setIconEx('rsm_copyfolder',currentrights.indexOf('m')>=0,true);
	rs_setIconEx('rsm_deletefolder',currentrights.indexOf('m')>=0,true);
}

/**
* @deprecated 2.0 No longer needed
*/
function rs_setFolder(path){
	var folder = document.getElementById('newfilepath');
	folder.value = path;
	currentpath = path;
	currentuid = rsvtreeview.getItemByObjectId(trim(currentpath,'/'));
}

function rs_uploadstart(){
	PopupManager.showLoading();
	return true;
}

function rs_uploadcallback(response){
	if(response != 1){
		PopupManager.showError('Oops, there was an error.<br />'+response);
		PopupManager.hideLoading();
	}else{
		rs_refreshFiles('rs_uploadupdate',currenttype,currentdisplay,currentmode);
		document.uploadresource.reset();
		PopupManager.hideLoading();
	}
}

function rs_uploadupdate(response){
	if(response == 0){
		PopupManager.showError('Oops, there was an error.');
		PopupManager.hideLoading();
	}else{
		document.getElementById('rs_resourcecontainer').innerHTML = response;	
		PopupManager.hideLoading();
	}
}

function rs_getdetails(){
	var selected = document.getElementById('rsv_selected');
	var sourceid = document.getElementById('rsv_sourceid');
	var owner = document.getElementById('rsv_owner');
	var path = document.getElementById('newfilepath');
	var res = {};
	res.selected = selected?selected.value:'';
	res.sourceid = sourceid?sourceid.value:'';
	res.owner = owner?owner.value:'';
	res.path = path?path.value:'';
	return res;
}

function rs_selectFile(div){
	var file = rs_getFile(div);
	var ind = arrayIndexOf(rs_selectedfiles,file);
	var classprefix = currentdisplay=='icons'?'rsf_file':'rsf_detail';
	if(ind >= 0){
		if(currentdisplay=='icons')	div.className = 'rsf_file';
		else div.className = 'rsf_detail' + (div.rowIndex%2==1?'row':'alt');
		rs_selectedfiles.splice(ind,1);
	}else{
		rs_selectedfiles[rs_selectedfiles.length] = file;
		if(currentdisplay=='icons')	div.className = 'rsf_file_selected';
		else div.className = 'rsf_detail_selected'
	}
	rs_setIconEx('rsm_download',rs_selectedfiles.length==1,false);
	rs_setIconEx('rsm_renamefiles',rs_selectedfiles.length==1,true);
	rs_setIconEx('rsm_deletefiles',rs_selectedfiles.length>0,true);
	rs_setIconEx('rsm_movefiles',rs_selectedfiles.length>0,true);
	rs_setIconEx('rsm_copyfiles',rs_selectedfiles.length>0,true);
	rs_setIconEx('rsm_optimise',rs_selectedfiles.length>0&&rs_imageselected(),true);
	rs_setIconEx('rsm_refreshthumb',rs_selectedfiles.length>0&&currentdisplay=='icons'&&rs_imageselected(),true);
	rs_setIconEx('rsm_resize',rs_selectedfiles.length==1&&rs_imageselected(),true);
	document.getElementById('rsm_download').href = rs_selectedfiles.length == 1?rsv_webroot+rs_selectedfiles[0].filepath:'javascript:void(0);';
	document.getElementById('rsm_download').target = rs_selectedfiles.length == 1?'_blank':'';
}

function rs_imageselected(){
	for(var i=0;i<rs_selectedfiles.length;i++){
		if(rs_selectedfiles[i].isimage) return true;
	}
	return false;
}

function rs_clearSelection(){
	for(var i=0;i<rs_selectedfiles.lenngth;i++){		
		if(currentdisplay=='icons')	rs_selectedfiles[i].div.className = 'rsf_file';
		else div.className = 'rsf_detail' + (rs_selectedfiles[i].div.rowIndex%2==1?'row':'alt');
	}
	rs_selectedfiles = [];
	rs_setIconEx('rsm_download',false,false);
	rs_setIconEx('rsm_renamefiles',false,true);
	rs_setIconEx('rsm_deletefiles',false,true);
	rs_setIconEx('rsm_movefiles',false,true);
	rs_setIconEx('rsm_copyfiles',false,true);
	rs_setIconEx('rsm_optimise',false,true);
	rs_setIconEx('rsm_refreshthumb',false,true);
	rs_setIconEx('rsm_resize',false,true);
	document.getElementById('rsm_download').href = 'javascript:void(0);';
	document.getElementById('rsm_download').target = '';
}

function rs_setIconEx(id,on,noclick){
	var obj = document.getElementById(id);
	if(on){
		obj.className = obj.className.replace(/_off$/,'');
		if(obj.rev){
			obj.href = obj.rev;
			obj.rev = '';
		}
	}else{
		obj.className = obj.className.replace(/(_off)?$/,'_off');
		if(noclick && obj.href && obj.href != 'javascript:void(0);'){
			obj.rev = obj.href;
			obj.href = 'javascript:void(0);';
		}
	}
}

function rs_deletefile(){
	if(rs_selectedfiles.length){
		if(confirm('Are you sure you want to delete all the selected files?\r\nThis action is permanent, and cannot be rolled back.')){
			PopupManager.showLoading();
			var paths = new Array();
			for(i=0;i<rs_selectedfiles.length;i++){
				paths[i] = rs_selectedfiles[i].filepath;
			}
			result = agent.call(rsv_ajaxpath,'ResourceManager_DeleteFiles','',paths);
			if(result==1){
				rs_refreshFiles(null,currenttype,currentdisplay,currentmode);
				PopupManager.hideLoading();
			}else{
				var message = 'There were problems deleting the selected files. Please try again';
				if(result.length>1) message += '<br />' + result;
				PopupManager.showError(message);
			}
			PopupManager.hideLoading();
		}
	}
}

function rs_refreshthumb(){
	if(rs_selectedfiles.length){
		for(var i=0;i<rs_selectedfiles.length;i++){
			var file = rs_selectedfiles[i];
			if(arrayIndexOf(rsv_thumbextensions,file.ext.toLowerCase()) >= 0){
				var img = document.getElementById('rsf_image'+file.id);
				img.innerHTML = '<img src="'+rsv_webroot+'images/loading.gif" class="rmf_icon" />';
				agent.call(rsv_ajaxpath,'ResourceManager_LoadThumbnail','rsf_image'+file.id,rsv_webroot,file.filepath,1);
			}
		}
		for(var i=rs_selectedfiles.length-1;i>=0;i--){
			var file = rs_selectedfiles[i];
			rs_selectFile(file.div);
		}
	}
}

function rs_optimise(){
	if(rs_selectedfiles.length){
		for(var i=0;i<rs_selectedfiles.length;i++){
			var file = rs_selectedfiles[i];
			if(arrayIndexOf(rsv_thumbextensions,file.ext.toLowerCase()) >= 0){
				var img = document.getElementById('rsf_image'+file.id);
				img.innerHTML = '<img src="'+rsv_webroot+'images/load'+(currentdisplay=='icons'?'ing':'er')+'.gif" class="rmf_icon" />';
				agent.call(rsv_ajaxpath,'ResourceManager_OptimiseImage',file.div.id,rsv_prefix,file.filepath,file.id,currentdisplay);
			}
		}
		for(var i=rs_selectedfiles.length-1;i>=0;i--){
			var file = rs_selectedfiles[i];
			rs_selectFile(file.div);
		}
	}
}

function rs_renamefile(){
	if(rs_selectedfiles.length==1){
		PopupManager.showDisabled();
		var file = rs_selectedfiles[0];
		var div = document.getElementById('rsp_renamefile');
		var pop = PopupManager.createOrFetchPopup('rsp_rename','Rename File',0,0,'div',div,'disabled');
		var fname = basename(file.filepath);
		pop.Show({'rsp_renamefileparent':fname,'rsp_renamefilefullpath':file.filepath,'rsp_renamefilename':fname});
	}
}
function rs_renamefile_save(){
	PopupManager.showLoading();
	var popup = PopupManager.popups['rsp_rename'];
	var path = popup.doc.getElementById('rsp_renamefilefullpath').value;
	var name = popup.doc.getElementById('rsp_renamefilename').value;
	if(name == ''){
		PopupManager.showError('Please supply a name for the file');
	}else if((/[\/ \\]/gi).test(name)){
		PopupManager.showError('That name contains illegal characters. Please do not use / or \\ in your name.');
	}else{
		var result = agent.call(rsv_ajaxpath,'ResourceManager_RenameFile','',path,escape(name));
		if(result.substr(0,2) == '0:'){
			name = result.substr(2);
			PopupManager.hidePopup('rsp_rename');
			rs_selectedfiles[0].rename(name);
			rs_selectFile(rs_selectedfiles[0].div);
			PopupManager.hideDisabled();
		}else{
			var message = 'There was a problem renaming the file. Please try again';
			if(typeof(result) == 'string') message += '<br />' + result;
			PopupManager.showError(message);
		}
	}
	PopupManager.hideLoading();
}

function rs_resize(){
	if(rs_selectedfiles.length==1){
		PopupManager.showLoading();
		var file = rs_selectedfiles[0];
		var div = document.getElementById('rsp_resizeimage');
		var pop = PopupManager.createOrFetchPopup('rsp_resize','Resize Image',0,0,'div',div,'loading');
		var info = agent.call(rsv_ajaxpath,'ResourceManager_GetImageInfo','',file.filepath);
		info = eval("(" + info + ")");		
		pop.Show({
			'rs_resizefilefullpath':file.filepath,
			'rs_resizefilename':basename(file.filepath),
			'rs_resizewidth':info.width,
			'rs_resizeoriginalwidth':info.width,
			'rs_resizeoriginalwidthtext':'(orignal: ' + info.width + ' px)',
			'rs_resizeheight':info.height,
			'rs_resizeoriginalheight':info.height,
			'rs_resizeoriginalheighttext':'(orignal: ' + info.height + ' px)'
		});
	}
}

function rs_resizeimage_check(obj){
	var num = 0;
	var popup = PopupManager.popups['rsp_resize'];
	var width = popup.doc.getElementById('rs_resizeoriginalwidth').value;
	var height = popup.doc.getElementById('rs_resizeoriginalheight').value;
	var sel = popup.doc.getElementById('rs_resizeimageaction');
	try{num = parseInt(obj.value)}catch(e){}
	if(num <= 0 || isNaN(num)){
		if(sel[sel.selectedIndex].value == 'maintain'){
			var hobj = (obj.id == 'rs_resizewidth')?document.getElementById('rs_resizeheight'):document.getElementById('rs_resizewidth');
			hobj.value = '###';
		}
		return true;
	}
	obj.value = num;
	if(sel[sel.selectedIndex].value == 'maintain'){
		var ratio = width / height;
		if(obj.id == 'rs_resizewidth'){
			var hobj = popup.doc.getElementById('rs_resizeheight');
			hobj.value = Math.max(Math.round(num/ratio),1);
		}else{
			var hobj = popup.doc.getElementById('rs_resizewidth');
			hobj.value = Math.max(Math.round(num*ratio),1);
		}
	}
	return true;
}

function rs_resizeimage_actionchange(obj){
	var popup = PopupManager.popups['rsp_resize'];
	var colours = popup.doc.getElementById('rs_resizeimagecolours');
	if(colours) colours.style.display = (obj[obj.selectedIndex].value=='pad'?'inline':'none');
	if(obj[obj.selectedIndex].value=='maintain'){
		rs_resizeimage_check(popup.doc.getElementById('rs_resizewidth'));
		rs_resizeimage_check(popup.doc.getElementById('rs_resizeheight'));
	}
}

function rs_imageresize_setcolour(sourceid,value){
	var popup = PopupManager.popups['rsp_resize'];
	popup.doc.getElementById('rs_imageresizepadcolour').value = value;
	popup.doc.getElementById('rs_resizeimagecolourdisplay').style.backgroundColor = value;
}

function rs_resizeimage_save(){
	var popup = PopupManager.popups['rsp_resize'];
	var originalwidth = popup.doc.getElementById('rs_resizeoriginalwidth').value;
	var originalheight = popup.doc.getElementById('rs_resizeoriginalheight').value;
	var width = popup.doc.getElementById('rs_resizewidth').value;
	var height = popup.doc.getElementById('rs_resizeheight').value;
	var actionsel = popup.doc.getElementById('rs_resizeimageaction');
	var action = actionsel[actionsel.selectedIndex].value;
	var colour = popup.doc.getElementById('rs_imageresizepadcolour').value
	var error = '';
	try{width = parseInt(width)}catch(e){}
	try{height = parseInt(height)}catch(e){}
	if(width <= 0 || isNaN(width) || height <= 0 || isNaN(height)){
		PopupManager.showError('Please enter positive whole numbers for the width and height values');
	}else{	
		PopupManager.showLoading();
		var file = rs_selectedfiles[0];
		var result = agent.call(rsv_ajaxpath,'ResourceManager_ResizeImage','',rsv_webroot,rsv_prefix,file.filepath,file.id,currentdisplay,width,height,action,colour);
		if(result.substring(0,2)=='0:'){
			PopupManager.hidePopup('rsp_resize');
			PopupManager.hideLoading();
			file.div.innerHTML = result.substring(2);
		}else{
			var message = 'There was a problem resizing the file.';
			if(typeof(result) == 'string') message += '<br />' + result;
			PopupManager.showError(message);
		}
		PopupManager.hideLoading();
	}
}

function rs_renamefolder(){
	if(currentrights.indexOf('m')>=0){
		PopupManager.showDisabled();
		var div = document.getElementById('rsp_renamefolder');
		var pop = PopupManager.createOrFetchPopup('rsp_renamefolder','Rename Folder',0,0,'div',div,'disabled');
		var fname = basename(currentpath);
		pop.Show({'rsp_renamefolderparent':fname,'rsp_renamefolderfullpath':currentpath,'rsp_renamefoldername':fname});
	}
}
function rs_renamefolder_save(){
	PopupManager.showLoading();
	var popup = PopupManager.popups['rsp_renamefolder'];
	var path = popup.doc.getElementById('rsp_renamefolderfullpath').value;
	var name = popup.doc.getElementById('rsp_renamefoldername').value;
	if(name == ''){
		PopupManager.showError('Please supply a name for the folder');
	}else if((/[\/ \\]/gi).test(name)){
		PopupManager.showError('That name contains illegal characters. Please do not use / or \\ in your name.');
	}else{
		var result = agent.call(rsv_ajaxpath,'ResourceManager_RenameFolder','',path,escape(name));
		if(result.substr(0,2) == '0:'){
			name = result.substr(2);
			var newpath = dirname(path)+'/'+name+'/';
			PopupManager.hidePopup('rsp_renamefolder');
			
			rs_refreshFolders(null,newpath);
			rs_selectFolder(newpath,'rwtm');
			PopupManager.hideDisabled();
		}else{
			var message = 'There was a problem renaming the folder. Please try again';
			if(typeof(result) == 'string') message += '<br />' + result;
			PopupManager.showError(message);
		}
	}
	PopupManager.hideLoading();
}

function rs_addfolder(){
	if(currentrights.indexOf('t')>=0){
		PopupManager.showDisabled();
		var div = document.getElementById('rsp_addfolder');
		var pop = PopupManager.createOrFetchPopup('rsp_addfolder','Add Folder',0,0,'div',div,'disabled');
		pop.Show({'rsp_addfolderfullpath':currentpath,'rsp_addfoldername':''});
	}
}
function rs_addfolder_save(){
	PopupManager.showLoading();
	var popup = PopupManager.popups['rsp_addfolder'];
	var path = popup.doc.getElementById('rsp_addfolderfullpath').value;
	var name = popup.doc.getElementById('rsp_addfoldername').value;
	if(name == ''){
		PopupManager.showError('Please supply a name for the folder');
	}else if((/[\/ \\]/gi).test(name)){
		PopupManager.showError('That name contains illegal characters. Please do not use / or \\ in your name.');
	}else{
		var result = agent.call(rsv_ajaxpath,'ResourceManager_CreateFolder','',path,escape(name));
		if(result.substr(0,2) == '0:'){
			name = result.substr(2);
			var newpath = trim(path,'/')+'/'+name+'/';
			PopupManager.hidePopup('rsp_addfolder');
			
			rs_refreshFolders(null,newpath);
			rs_selectFolder(newpath,'rwtm');
			PopupManager.hideDisabled();
		}else{
			var message = 'There was a problem adding the new folder. Please try again';
			if(typeof(result) == 'string') message += '<br />' + result;
			PopupManager.showError(message);
		}
	}
	PopupManager.hideLoading();
}

function rs_deletefolder(){
	if(currentrights.indexOf('m')>=0){
		if(confirm('Are you sure you want to delete the selected folder?\r\nThis will delete the folder, all subfolders, and all files in them.\r\nThis action is permanent, and cannot be rolled back.')){
			PopupManager.showLoading();
			result = agent.call(rsv_ajaxpath,'ResourceManager_DeleteFolder','',currentpath);
			if(result==1){
				var newpath = dirname(currentpath)+'/';				
				rs_refreshFolders(null,newpath);
				rs_selectFolder(newpath,'rwtm');
			}else{
				var message = 'There were problems deleting the selected folder. Please try again';
				if(result.length>1) message += '<br />' + result;
				PopupManager.showError(message);
			}
			PopupManager.hideLoading();
		}
	}
}

function rs_movefile(){
	if(rs_selectedfiles.length>0){
		currentaction = 'movefile';
		document.getElementById('rs_movefilecover').style.display = 'block';
		document.getElementById('rs_movefoldercover').style.display = 'block';
		var filenames = '';
		for(var i=0;i<rs_selectedfiles.length;i++){
			filenames += basename(rs_selectedfiles[i].filepath) + '<br />';
		}
		document.getElementById('rs_movefilelistcontainer').style.display = 'block';
		document.getElementById('rs_movefilelistcontainer').innerHTML = '<div class="rs_movefilelisthead">Files to Move</div><div class="rs_movefilelist">'+filenames+'</div>';
		document.getElementById('rs_movefiletitle').innerHTML = 'Move Files';
		document.getElementById('rs_movefileheading').innerHTML = 'Click on a folder to move the files into';
		document.getElementById('rs_movefiletext').innerHTML = 'Note that if a file already exists, it will not be overwritten. You will need to delete the old file first.';
	}
}

function rs_movefile_cancel(){
	document.getElementById('rs_movefilecover').style.display = 'none';
	document.getElementById('rs_movefoldercover').style.display = 'none';
	if(currentaction.indexOf('folder') >= 0){
		if(currentuid){
			var item = rsvtreeview.items[currentuid];
			item.enableItem(true);
			var parent = item.getParent();
			parent.enableItem(false);
		}
	}
	currentaction = 'none';
}

function rs_movefile_save(path){
	PopupManager.showLoading();
	var filenames = [];
	for(var i=0;i<rs_selectedfiles.length;i++){
		filenames.push(rs_selectedfiles[i].filepath);
	}
	var result = agent.call(rsv_ajaxpath,'ResourceManager_MoveFiles','',path,filenames);
	if(result!=1){
		var message = 'There was a problem moving the selected files.';
		if(typeof(result) == 'string') message += '<br />' + result;
		PopupManager.showError(message);
	}
	rs_refreshFiles(null,currenttype,currentdisplay,currentmode,currentpath);
	rs_movefile_cancel();
	rsvtreeview.Select(currentuid);
	PopupManager.hideLoading();
}

function rs_copyfile(){
	if(rs_selectedfiles.length>0){
		currentaction = 'copyfile';
		document.getElementById('rs_movefilecover').style.display = 'block';
		document.getElementById('rs_movefoldercover').style.display = 'block';
		var filenames = '';
		for(var i=0;i<rs_selectedfiles.length;i++){
			filenames += basename(rs_selectedfiles[i].filepath) + '<br />';
		}
		document.getElementById('rs_movefilelistcontainer').style.display = 'block';
		document.getElementById('rs_movefilelistcontainer').innerHTML = '<div class="rs_movefilelisthead">Files to Move</div><div class="rs_movefilelist">'+filenames+'</div>';
		document.getElementById('rs_movefiletitle').innerHTML = 'Copy Files';
		document.getElementById('rs_movefileheading').innerHTML = 'Click on a folder to copy the files into';
		document.getElementById('rs_movefiletext').innerHTML = 'Note that if a file already exists, it will not be overwritten. You will need to delete the old file first.';
	}
}

function rs_copyfile_save(path){
	PopupManager.showLoading();
	var filenames = [];
	for(var i=0;i<rs_selectedfiles.length;i++){
		filenames.push(rs_selectedfiles[i].filepath);
	}
	var result = agent.call(rsv_ajaxpath,'ResourceManager_CopyFiles','',path,filenames);
	if(result!=1){
		var message = 'There was a problem copying the selected files.';
		if(typeof(result) == 'string') message += '<br />' + result;
		PopupManager.showError(message);
	}
	rs_refreshFiles(null,currenttype,currentdisplay,currentmode,currentpath);
	rs_movefile_cancel();
	rsvtreeview.Select(currentuid);
	PopupManager.hideLoading();
}

function rs_movefolder(){
	if(currentrights.indexOf('m')>=0){
		currentaction = 'movefolder';
		document.getElementById('rs_movefilecover').style.display = 'block';
		document.getElementById('rs_movefoldercover').style.display = 'block';
		document.getElementById('rs_movefilelistcontainer').style.display = 'none';
		document.getElementById('rs_movefiletitle').innerHTML = 'Move Folder';
		document.getElementById('rs_movefileheading').innerHTML = 'Click on a folder to move the current folder into';
		document.getElementById('rs_movefiletext').innerHTML = 'Note that if a folder already exists with the same name, it will not be overwritten. You will need to delete the old folder first.';
		var item = rsvtreeview.items[currentuid];
		item.disableItem(true);
		var parent = item.getParent();
		parent.disableItem(false);
	}
}

function rs_movefolder_save(path){
	PopupManager.showLoading();
	var result = agent.call(rsv_ajaxpath,'ResourceManager_MoveFolder','',currentpath,path);
	if(result==1){
		rs_movefile_cancel();
		var newpath = path + basename(currentpath) + '/';
		rs_refreshFolders(null,newpath);
		rs_openpath(dirname(currentpath));
		rs_selectFolder(newpath,'rwtm');
	}else{
		var message = 'There was a problem moving the selected folder.';
		if(typeof(result) == 'string') message += '<br />' + result;
		rsvtreeview.Select(currentuid);
		PopupManager.showError(message);
	}
	PopupManager.hideLoading();
}

function rs_copyfolder(){
	if(currentrights.indexOf('m')>=0){
		currentaction = 'copyfolder';
		document.getElementById('rs_movefilecover').style.display = 'block';
		document.getElementById('rs_movefoldercover').style.display = 'block';
		document.getElementById('rs_movefilelistcontainer').style.display = 'none';
		document.getElementById('rs_movefiletitle').innerHTML = 'Copy Folder';
		document.getElementById('rs_movefileheading').innerHTML = 'Click on a folder to copy the current folder into';
		document.getElementById('rs_movefiletext').innerHTML = 'Note that if a folder already exists with the same name, it will not be overwritten. You will need to delete the old folder first.';
		var item = rsvtreeview.items[currentuid];
		item.disableItem(true);
		var parent = item.getParent();
		parent.disableItem(false);
	}
}

function rs_copyfolder_save(path){
	PopupManager.showLoading();
	var result = agent.call(rsv_ajaxpath,'ResourceManager_CopyFolder','',currentpath,path);
	if(result==1){
		rs_movefile_cancel();
		var newpath = path + basename(currentpath) + '/';
		rs_refreshFolders(null,newpath);
		rs_openpath(currentpath);
		rs_selectFolder(newpath,'rwtm');
	}else{
		var message = 'There was a problem copying the selected folder.';
		if(typeof(result) == 'string') message += '<br />' + result;
		rsvtreeview.Select(currentuid);
		PopupManager.showError(message);
	}
	PopupManager.hideLoading();
}

function rs_openpath(path){
	var uid = rsvtreeview.getItemByObjectId(trim(path,'/'));
	var item = rsvtreeview.items[uid];
	while(item && item.uniqueid!=1){
		rsvtreeview.Expand(rsvtreeview.prefix,item.uniqueid);
		item = item.getParent();
	}
}


/* ============================================================
						File Object
   ============================================================ */
function rs_getFile(div){
	for(var i=0;i<rs_selectedfiles.length;i++){
		if(rs_selectedfiles[i].div == div) return rs_selectedfiles[i];
	}
	return new RS_File(div);
}
function RS_File(div){
	this.div = div;
	this.filepath = div.getAttribute('filepath');
	this.id = div.id.substring(7);
	this.ext = this.filepath.substring(this.filepath.lastIndexOf('.')+1);
	this.isimage = arrayIndexOf(rsv_thumbextensions,this.ext.toLowerCase()) >= 0;
	var divs = this.div.getElementsByTagName('DIV');
	for(var i=0;i<divs.length;i++){
		if(divs[i].className == 'rsf_image') this.imagecontainer = divs[i];
		if(divs[i].className == 'rsf_filename') this.label = divs[i];
		if(divs[i].className == 'rsf_detailfile') this.label = divs[i];
	}
}
RS_File.prototype.rename = function(name){
	var filepath = dirname(this.filepath) + '/' + name;
	this.filepath = filepath;
	this.div.setAttribute('filepath',filepath);
	this.label.innerHTML = name;
	if(this.div.title.indexOf('(') >= 0){
		this.div.title = name + ' ' + this.div.title.substring(this.div.title.indexOf('('));
	}else{
		this.div.title = name;
	}
}
