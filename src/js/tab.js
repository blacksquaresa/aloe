function showTab(index/*,prefix*/){
	var prefix = '';
	if(arguments.length >= 2) prefix = arguments[1];
	hideTabs(index,prefix);	
	var tabElement = document.getElementById(prefix+'tab'+index);
	if(tabElement){
		if(tabElement.className.length < 8 || tabElement.className.substring(tabElement.className.length-8,tabElement.className.length) != 'selected') tabElement.className += 'selected';
	}
	var divElement = document.getElementById(prefix+'tbp'+index);
	if(divElement){
		if(divElement.className.length < 8 || divElement.className.substring(divElement.className.length-8,divElement.className.length) != 'selected') divElement.className += 'selected';
	}
}

function hideTabs(index/*,prefix*/){
	var prefix = '';
	if(arguments.length >= 2) prefix = arguments[1];
	var i=(index==0?1:0);
	var tabElement = document.getElementById(prefix+'tab'+i);
	while(tabElement){
		if(tabElement.className.length > 8 && tabElement.className.substring(tabElement.className.length-8,tabElement.className.length) == 'selected') tabElement.className = tabElement.className.substring(0,tabElement.className.length-8);
		divElement = document.getElementById(prefix+'tbp'+i);
		if(divElement){
			if(divElement.className.length > 8 && divElement.className.substring(divElement.className.length-8,divElement.className.length) == 'selected') divElement.className = divElement.className.substring(0,divElement.className.length-8);
		}
		i++;
		if(i==index) i++;
		tabElement = document.getElementById(prefix+'tab'+i);
	}
}