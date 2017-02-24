function openCreateEntry(){
	PopupManager.showDisabled();
	var replacements = {'add_name':''};
	for(key in list.fields){
		replacements['add_'+list.fields[key].name] = '';
	}
	var popup = PopupManager.createOrFetchPopup('create_entry','Create New Entry',0,0,'div','create_entry','disabled');
	popup.Show(replacements);
}

function openEntry(id){
	PopupManager.showLoading();
	var item = agent.call('../ajax/ListManager.ajax.php','AJ_GetEntryDetails','',id);
	if(item.substring(0,1) == '{'){
		currententry = JSON.parse(item);
		document.getElementById('upd_entryid').value = id;
		document.getElementById('upd_name').value = currententry.name;
		for(key in list.fields){
			var elem = document.getElementById('upd_'+list.fields[key].name);
			var value = currententry['values'][key]?currententry['values'][key]['value']:'';
			if(elem){
				switch(list.fields[key].type){
					case 'tinymce':
						if(elem.className.indexOf('mceEditor') >= 0 && tinymce && (editor=tinymce.getInstanceById('upd_'+list.fields[key].name)) && editor.dom){
							editor.setContent(value);
						}else{
							elem.value = value;
						}
						break;
					case 'select':
						elem.selectedIndex = 0;
						for(j=0;j<elem.length;j++){
							if(elem[j].value==value){
								elem.selectedIndex = j;
								break;
							}
						}
						break;
					default:
						elem.value = value;
						break;
				}
			}
		}
		setSelectedEntry(id);
	}else{
		PopupManager.showError('Sorry, there was an error opening the selected entry.'+(item==''?'':'<br />'+item));
	}
	PopupManager.hideLoading();
}

function setSelectedEntry(id){
	for(key in list.items){
		var entry = document.getElementById('entry_'+key);
		if(entry){
			if(key==id){
				entry.className = 'list_entryselected';
			}else{
				entry.className = 'list_entry';
			}
		}
	}
}
function createEntry(){
	var name = document.getElementById('add_name').value;	
	if(name==''){
		msg += ' - Please provide a name for this entry.<br />';
		PopupManager.showError('There were problems creating this entry:<br />' + msg);
		return false;
	}else{
		PopupManager.showLoading();
		return true;
	}
}
function updateEntry(){
	var name = document.getElementById('upd_name').value;	
	if(name==''){
		msg += ' - Please provide a name for this entry.<br />';
		PopupManager.showError('There were problems creating this entry:<br />' + msg);
		return false;
	}else{
		PopupManager.showLoading();
		return true;
	}
}

function deleteEntry(id){
	if(confirm('Are you sure you want to delete the ' + window.list.items[id].name + ' entry?')){
		PopupManager.showLoading();
		document.location.href = 'editlists.php?action=del&type='+window.list.code+'&entryid='+id;
	}
}

// Move a header up or down
function moveEntry(id,ind){
	PopupManager.showLoading();
	var res = agent.call('../ajax/ListManager.ajax.php','AJ_moveListEntry'+(ind<0?'Up':'Down'),'',id);
	if(res=='success'){
		var res = agent.call('','drawList','entry_list',window.list.id,currententry.id);
		PopupManager.hideLoading();
		PopupManager.showCompleted();
	}else{
		PopupManager.showError(res);
		PopupManager.hideLoading();
	}
}