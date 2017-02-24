function addMultiListItem(sel){
	var option = sel[sel.selectedIndex];
	if(option.value != 0){
		sel.removeChild(option);
		var sourceid = sel.id.substring(0,sel.id.length-7);
		var target = document.getElementById(sourceid+'_list');
		option.selected = false;
		target.appendChild(option);
		if(target.form){
			var hidden = document.createElement('input');
			hidden.type = 'hidden';
			hidden.id = target.id+'_'+option.value;
			hidden.name = sourceid+'[]';
			hidden.value = option.value;
			target.form.appendChild(hidden);
		}
	}
}

function transferMultiListItems(ids,sourceid){
	var source = document.getElementById(sourceid+'_source');
	var target = document.getElementById(sourceid+'_list');
	for(var i=source.length-1;i>=0;i--){
		var option = source[i];
		if(arrayIndexOf(ids,option.value) >= 0){
			option.selected = false;
			source.removeChild(option);
			target.appendChild(option);	
			if(target.form){
				var hidden = document.createElement('input');
				hidden.type = 'hidden';
				hidden.id = target.id+'_'+option.value;
				hidden.name = sourceid+'[]';
				hidden.value = option.value;
				target.form.appendChild(hidden);
			}
		}
	}
}

function removeMultiListItems(sourceid){
	var source = document.getElementById(sourceid+'_list');
	var target = document.getElementById(sourceid+'_source');
	for(var i=source.length-1;i>=0;i--){
		var option = source[i];
		if(option.selected){
			option.selected = false;
			source.removeChild(option);
			target.appendChild(option);	
			var hidden = document.getElementById(sourceid+'_'+option.value);
			if(hidden) hidden.parentNode.removeChild(hidden);
		}
	}
}

function removeAllMultiListItems(sourceid){
	var source = document.getElementById(sourceid+'_list');
	var target = document.getElementById(sourceid+'_source');
	for(var i=source.length-1;i>=0;i--){
		var option = source[i];
		option.selected = false;
		source.removeChild(option);
		target.appendChild(option);	
		var hidden = document.getElementById(sourceid+'_'+option.value);
		if(hidden) hidden.parentNode.removeChild(hidden);
	}
}

function getMultiListSelectedIds(sourceid){
	var ids = [];
	var target = document.getElementById(sourceid+'_list');
	for(var i=target.length-1;i>=0;i--){
		ids.push(target[i].value);
	}
	return ids;
}