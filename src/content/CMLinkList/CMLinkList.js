var objCMLinkList = {	
	AddNewLinkRow: function(blockid, index){
		var container = document.getElementById('Linklist');
		var table = container.firstChild.firstChild;
		
		// set this link to delete
		var lnk = table.lastChild.lastChild.firstChild;
		var img = lnk.firstChild;
		img.src = '../../images/admin/common/delete.png';
		lnk.href = 'javascript:objCMLinkList.DeleteLinkRow('+(index-1)+')';
		
		// create new row
		var row = document.createElement('tr');
		row.id = 'linkrow_' + index;
		var cell1 = document.createElement('td');
		cell1.innerHTML = '<input type="text" name="text['+index+']" id="text_'+index+'" class="edt_textbox" />';
		var cell2 = document.createElement('td');
		cell2.innerHTML = '<input type="text" name="url['+index+']" id="url_'+index+'" class="edt_textbox" />';
		cell2.innerHTML += '<a href="javascript:parent.PopupManager.showLinkSelector(null,\'url_'+index+'\',\'PopupManager.popups.CMLinkList.GetOwnerDocument()\');"><img src="../../images/admin/common/select.png" align="top" /></a>';
		var cell3 = document.createElement('td');
		cell3.innerHTML = '<a href="javascript:objCMLinkList.AddNewLinkRow('+blockid+','+(index+1)+');"><img src="../../images/admin/common/add.png" /></a>';
		row.appendChild(cell1);
		row.appendChild(cell2);
		row.appendChild(cell3);
		table.appendChild(row);
	},
	
	DeleteLinkRow: function(index){
		var row = document.getElementById('linkrow_'+index);
		var text = row.firstChild.firstChild;
		text.value = '';
		var url = row.firstChild.nextSibling.firstChild;
		url.value = '';
		row.style.display = 'none';
	}
}