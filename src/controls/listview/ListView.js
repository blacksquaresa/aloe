<script language="javascript">
<!--
function _listview_reload_sort(statename,sortname,sortvalue,callback,targetelement,pagestateajaxpath){
	_listview_targetelement = targetelement;
	var target = document.getElementById(targetelement);
	_listview_drawloading(targetelement);
	var obj = new Object;
	obj[sortname] = sortvalue;
	agent.call(pagestateajaxpath,'SetStateItems','',statename,obj);
	agent.call('',callback,targetelement);
}
function _listview_reload_paging(statename,startname,startvalue,callback,targetelement,pagestateajaxpath){
	_listview_targetelement = targetelement;
	var target = document.getElementById(targetelement);
	_listview_drawloading(targetelement);
	var obj = new Object;
	obj[startname] = startvalue;
	agent.call(pagestateajaxpath,'SetStateItems','',statename,obj);
	agent.call('',callback,targetelement);
}
function _listview_drawloading(targetelement){
	var target = document.getElementById(targetelement);
	var loading = '<table width="100%" height="' + target.offsetHeight + 'px" class="lvloading"><tr><td align="center">Loading...</td></tr></table>';
	target.innerHTML = loading;
}
// -->
</script>
