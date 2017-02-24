function linkselector_submiturl(){
	var sourceid = document.getElementById('sourceid').value;
	var owner = document.getElementById('owner').value;
	var url = parent.trim(document.getElementById('url').value);
	if(url.substring(0,4) != 'http' && url.substring(0,3) != 'ftp') url = 'http://' + url;
	parent.setElementValue(sourceid,url,owner);
	parent.PopupManager.hideLinkSelector();
}

function linkselector_submitemail(){
	var sourceid = document.getElementById('sourceid').value;
	var owner = document.getElementById('owner').value;
	var email = parent.trim(document.getElementById('email').value);
	var subject = parent.trim(document.getElementById('subject').value);
	var body = parent.trim(document.getElementById('body').value);
	var url = '';
	
	if(email.length > 0){
		url = email;
		if(url.substring(0,7) != 'mailto:') url = 'mailto:' + url;
		if(subject.length > 0) url += '?subject='+escape(subject);
		if(body.length > 0) url += (subject.length>0?'&':'?') + 'body=' + escape(body);
	}
	
	parent.setElementValue(sourceid,url,owner);
	parent.PopupManager.hideLinkSelector();
}