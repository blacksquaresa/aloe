function Archive_ShowYear(year){
	var label = document.getElementById('Archive_' + year);
	var block = document.getElementById('Archive_' + year + '_List');
	if(block){
		if(block.style.display == 'none'){
			block.style.display = 'block';
			if(label) label.innerHTML = '&#9660;' + label.innerHTML.substring(1,label.innerHTML.length);
		}else{
			block.style.display = 'none';
			if(label) label.innerHTML = '&#9658;' + label.innerHTML.substring(1,label.innerHTML.length);
		}
	}
}


function Archive_ShowMonth(year,month,date){
	var label = document.getElementById('Archive_' + year + '_' + month);
	var block = document.getElementById('Archive_' + year + '_' + month + '_List');

	if(block){
		if(block.style.display == 'none'){
			block.style.display = 'block';	
			
			if(block.firstChild.tagName == 'IMG'){
			    var NewsContent = agent.call('/modules/News/News.ajax.php','AJ_drawNews','',date);
    			
                if(NewsContent.substring(0,1) == '<'){
				    block.innerHTML = NewsContent;	
				    if(label) label.innerHTML = '&#9660;' + label.innerHTML.substring(1,label.innerHTML.length);			  
			    }else{
				    block.innerHTML = 'error: ' + NewsContent;
			    }			
			}else{
			    if(label) label.innerHTML = '&#9660;' + label.innerHTML.substring(1,label.innerHTML.length);			
			}
		}else{
			block.style.display = 'none';
			if(label) label.innerHTML = '&#9658;' + label.innerHTML.substring(1,label.innerHTML.length);
		}
	}
}