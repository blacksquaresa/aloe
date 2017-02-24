function HideSearchOption(){
	var filter_heading = document.getElementById('filter_heading');	
	var filter_options = document.getElementById('filter_options');
	var filter_heading_button = document.getElementById('filter_heading_button');	
	filter_options.style.display="none";	
	filter_heading.className="filter_search_heading_show";			
	filter_heading.onclick = ShowSearchOption;
}

function ShowSearchOption(){	
	var filter_heading = document.getElementById('filter_heading');	
	var filter_options = document.getElementById('filter_options');
	var filter_heading_button = document.getElementById('filter_heading_button');	
	filter_options.style.display="block";		
	filter_heading.className="filter_search_heading_hide";		
	filter_heading.onclick = HideSearchOption;
}