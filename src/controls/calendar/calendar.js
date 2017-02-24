/* CSS Classes Used:
	datefield: textbox style
	datefield_open : textbox style while the calendar is open
	cldiv: the style of the containing div.
	cltable: the style of the containing table
	cltoday: the style of today's date. applies to the table cell
	clselected: the style of the currently selected date. applied to the table cell
	clpast: the style of dates before mindate. applied to the table cell.
*/

function PositionOffset(top,left,width,height){
	this.top = top;
	this.left = left;
	this.width = width;
	this.height = height;
}

PositionOffset.prototype.setOffset = function(top,left,width,height){
	this.top = top;
	this.left = left;
	this.width = width;
	this.height = height;
}

function Calendar(divid,ownerid,callback){
	this.divid = divid;
	this.ownerid = ownerid;
	this.shownone = true;
	this.callback = callback;
	this.speed = 10;
	this.step = 10;
	this.defaulttext = "(click to select)";
	this.positionOffset = new PositionOffset(-2,2,0,0);
	this.positionOffsetNS = new PositionOffset(0,-2,-6,-8);
	this.leftimage = '';
	this.rightimage = '';
	this.noneimage = '';
	this.mindate = null;
	
	this.divElement = null;
	this.ownerElement = null;
	this.divElementStyle = null;
	this.ownerElementStyle = null;
	this.height = 0;
	this.width = 0;
	this.currentlastday = null;
	this.monthnames = new Array('January','February','March','April','May','June','July','August','September','October','November','December');
	this.today = new Date();
	this.opening = false;
	this.isopen = false;
	this.closing = false;
	this.loaded = false;
	this.obj = "CalendarInstance_" + (++ Calendar.instance);
	eval (this.obj + "=this");
}
Calendar.instance = 0;
	
Calendar.prototype.getElement = function (elementId) {
	var element;
	if(document.all) element = document.all[elementId];
	else if(document.getElementById) element = document.getElementById(elementId);
	return element;
}
Calendar.prototype.getStyle = function (element) {
	if(element) return document.layers ? element : element.style;
	else return null;
}
	
Calendar.prototype.drawDate = function (){
	var year = this.getElement(this.obj + '_yearname');
	var month = this.getElement(this.obj + '_monthname');
	var firstdate = new Date(this.currentdate.getFullYear(),this.currentdate.getMonth(),1);
	var firstday = firstdate.getDay();
	var lastday = 32 - (new Date(this.currentdate.getFullYear(),this.currentdate.getMonth(),32)).getDate();
	year.innerHTML = this.currentdate.getFullYear();
	month.innerHTML = this.monthnames[this.currentdate.getMonth()];
	var day = 1;
	for(row=0;row<6;row++){
		for(col=0;col<7;col++){				
			var el = this.getElement(this.obj + '_cell' + row + '_' + col);
			if(row==0&&col<firstday){
				el.innerHTML = '&nbsp;';
				el.className = 'cldate';
			}else if(row==5&&col==6&&this.shownone){
				var nonehtml = '<a href="javascript:' + this.obj + '.selectnone();">';
				if(this.noneimage != ''){
					nonehtml += '<img src="' + this.noneimage + '" border="0" align="absmiddle" alt="No Date">';
				}else{				
					nonehtml += 'X';
				}
				nonehtml += '</a>';
				el.innerHTML = nonehtml;
				el.className = 'cldate';
			}else if(day > lastday){
				el.innerHTML = '&nbsp;';
				if(row!=6) el.className = 'cldate';
			}else{
				if(this.mindate && (firstdate.getTime() + ((day-1)*(24*60*60*1000))) < this.mindate.getTime()){
					el.innerHTML = day;
					el.className = 'clpast';
				}else{
					el.innerHTML = '<a href="javascript:' + this.obj + '.selectdate(' + day + ');">' + day + '</a>';
					if(this.currentdate.getFullYear() == this.selecteddate.getFullYear() && this.currentdate.getMonth() == this.selecteddate.getMonth() && day == this.selecteddate.getDate()){
						el.className = 'clselected';
					}else if(this.currentdate.getFullYear() == this.today.getFullYear() && this.currentdate.getMonth() == this.today.getMonth() && day == this.today.getDate()){
						el.className = 'cltoday';
					}else{
						el.className = 'cldate';
					}
				}
				day ++;
			}
		}
	}
}
Calendar.prototype.yearback = function (){
	var lastday = 32 - (new Date(this.currentdate.getFullYear()-1,this.currentdate.getMonth(),32)).getDate();
	var day = this.currentdate.getDate();
	if(lastday < day){
		this.currentdate.setDate(lastday);
		this.currentlastday = day;
	}
	this.currentdate.setYear(this.currentdate.getFullYear()-1);
	if(this.currentlastday > day && this.currentlastday <= lastday){
		this.currentdate.setDate(this.currentlastday);
		this.currentlastday = null;
	}
	this.drawDate();
}
Calendar.prototype.yearforward = function (){
	var lastday = 32 - (new Date(this.currentdate.getFullYear()+1,this.currentdate.getMonth(),32)).getDate();
	var day = this.currentdate.getDate();
	if(lastday < day){
		this.currentdate.setDate(lastday);
		this.currentlastday = day;
	}
	this.currentdate.setYear(this.currentdate.getFullYear()+1);
	if(this.currentlastday > day && this.currentlastday <= lastday){
		this.currentdate.setDate(this.currentlastday);
		this.currentlastday = null;
	}
	this.drawDate();
}
Calendar.prototype.monthback = function (){
	var lastday = 32 - (new Date(this.currentdate.getFullYear(),this.currentdate.getMonth()-1,32)).getDate();
	var day = this.currentdate.getDate();
	if(lastday < day){
		this.currentdate.setDate(lastday);
		this.currentlastday = day;
	}
	this.currentdate.setMonth(this.currentdate.getMonth()-1);
	if(this.currentlastday > day && this.currentlastday <= lastday){
		this.currentdate.setDate(this.currentlastday);
		this.currentlastday = null;
	}
	this.drawDate();
}
Calendar.prototype.monthforward = function (){
	var lastday = 32 - (new Date(this.currentdate.getFullYear(),this.currentdate.getMonth()+1,32)).getDate();
	var day = this.currentdate.getDate();
	if(lastday < day){
		this.currentdate.setDate(lastday);
		this.currentlastday = day;
	}
	this.currentdate.setMonth(this.currentdate.getMonth()+1);
	if(this.currentlastday > day && this.currentlastday <= lastday){
		this.currentdate.setDate(this.currentlastday);
		this.currentlastday = null;
	}
	this.drawDate();
}
Calendar.prototype.selectdate = function (day){
	if(this.callback){
		eval(this.callback + "(" + this.currentdate.getFullYear() + "," + this.currentdate.getMonth() + "," + day + ")");
	}else{
		this.setvalue(this.currentdate.getFullYear(),this.currentdate.getMonth(),day)
	}
	this.currentlastday = null;
	this.close();
}
Calendar.prototype.selectnone = function (){
	if(this.callback){
		eval(this.callback + "(" + 0 + "," + 0 + "," + 0 + ")");
	}else{
		this.setvalue(0,0,0)
	}
	this.close();
}

Calendar.prototype.getdatefromowner = function(val){
	if(val==null) val = this.ownerElement.value;
	var ticks = Date.parse(val);
	var date = new Date();
	if(ticks) date.setTime(ticks);
	return date;
}

Calendar.prototype.setPosition = function(){
	this.divElementStyle.top = (this.getAbsTop(this.ownerElement) + this.ownerElement.offsetHeight + this.positionOffset.top + (document.all?0:this.positionOffsetNS.top)) + 'px';
	this.divElementStyle.left = (this.getAbsLeft(this.ownerElement) + this.positionOffset.left + (document.all?0:this.positionOffsetNS.left)) + 'px';
}

Calendar.prototype.closeOtherInstances = function(){
	for(var i=1; i <= Calendar.instance; i++){
		var obj = "CalendarInstance_" + i;
		if(obj != this.obj){
			eval(obj + ".close()");
		}
	}
}

Calendar.prototype.open = function (){
	this.ownerElement.blur();
	if(!this.loaded) return;
	if(this.isopen){
		return this.close();
	}
	if(!this.opening && !this.closing){
		this.opening = true;
		this.ownerElement.className = 'datefield_open';
		this.currentdate = this.getdatefromowner();
		this.selecteddate = this.getdatefromowner();
		this.drawDate();
		this.setPosition();
		this.closeOtherInstances();
		this.divElementStyle.visibility = 'hidden';
		this.divElementStyle.display = 'block';
		if(!this.height) this.setSize();
		this.divElementStyle.height = this.step+"px";
		this.divElementStyle.visibility = 'visible';
		this.clear();
		this.interval = setInterval(this.obj + '.openincrement()',this.speed);
	}
}
Calendar.prototype.openincrement = function(){
	var height = parseInt(this.divElementStyle.height);
	if(height < this.height){
		var step = Math.min(this.step,this.height-height);
		this.divElementStyle.height = (height + step) + "px";
	}else{
		this.clear();
		this.opening = false;
		this.isopen = true;
	}
}

Calendar.prototype.close = function (){
	if(this.isopen || this.opening){
		this.clear();
		this.opening = false;
		this.interval = setInterval(this.obj + '.closeincrement()',this.speed);
	}
}
Calendar.prototype.closeincrement = function(){
	var height = parseInt(this.divElementStyle.height);
	if(height <= this.step){
		this.ownerElement.className = 'datefield';
		this.clear();
		this.divElementStyle.display = 'none';
		this.isopen = false;
	}else{
		var step = Math.min(this.step,height);
		this.divElementStyle.height = (height-step) + "px";
	}
}
Calendar.prototype.clear = function () {
	clearInterval (this.interval);
	this.interval = null;
}

Calendar.prototype.setvalue = function (year,month,day){
	if(year==0&&month==0&&day==0) this.ownerElement.value = this.defaulttext;
	else this.ownerElement.value = day + ' ' + this.monthnames[month] + ' ' + year;
}

Calendar.prototype.getAbsTop = function (o) {
	oTop = o.offsetTop
	while(o.offsetParent!=null){
		var pos = this.getAbsPosition(o.offsetParent);
		if(pos==='absolute' || pos=='relative') break;
		oParent = o.offsetParent
		oTop += oParent.offsetTop
		o = oParent
	}
	return oTop
}

Calendar.prototype.getAbsLeft = function (o) {
	oLeft = o.offsetLeft
	while(o.offsetParent!=null){
		var pos = this.getAbsPosition(o.offsetParent);
		if(pos==='absolute' || pos=='relative') break;
		oParent = o.offsetParent
		oLeft += oParent.offsetLeft
		o = oParent
	}
	return oLeft
}

Calendar.prototype.getAbsPosition = function (o){
	if(o.currentStyle){
		return o.currentStyle.position;
	}
	if(document.defaultView && document.defaultView.getComputedStyle){
		return document.defaultView.getComputedStyle(o, "").getPropertyValue('position');
	}
	return '';
}

Calendar.prototype.setSize = function(){
	this.height = this.divElement.offsetHeight + this.positionOffset.height;
	//this.width = this.divElement.offsetWidth + this.positionOffset.width;
	if(!document.all){
		this.height += this.positionOffsetNS.height;
		//this.width += this.positionOffsetNS.width;
	}
	this.divElementStyle.width = this.width;
}

Calendar.prototype.hideElement = function(style){
	if(document.all) style.display = 'none';
	else style.visibility = 'hidden';
}

Calendar.prototype.build = function(){
	this.divElement = this.getElement(this.divid);
	this.ownerElement = this.getElement(this.ownerid);
	this.divElementStyle = this.getStyle(this.divElement);
	this.ownerElementStyle = this.getStyle(this.ownerElement);
	
	this.mindate = this.getdatefromowner(this.mindate);
	
	var html = '<table class="cltable" cellspacing="0" cellpadding="0" align="center">';
	html += '<tr><td colspan="7"><div class="date_row1"></div></td></tr>';
	html += '<tr class="date_row1"><td class="clyeartitle" valign="top"><a style="font-size: 0px;" href="javascript:' + this.obj + '.yearback();">';	
	if(this.leftimage != ''){
		html += '<img src="' + this.leftimage + '" border="0" style="font-size: 0px;" align="abstop">';	
	}else{
		html += '&lt;&lt;';
	}
	html += '</a></td>';
	html += '<td class="clyeartitle" valign="top" colspan="5"> <span class="clyearname" id="' + this.obj + '_yearname"></span> </td>';	
	html += '<td class="clyeartitle" valign="top" ><a style="font-size: 0px;" href="javascript:' + this.obj + '.yearforward();">';	
	if(this.rightimage != ''){
		html += '<img src="' + this.rightimage + '" border="0" style="font-size: 0px;" align="abstop">';	
	}else{
		html += '&gt;&gt;';	
	}
	html += '</a></td></tr>';
	html += '<tr><td colspan="7"><div class="date_rowspacer"></div></td></tr>';
	html += '<tr><td class="clyeartitle" valign="top"><a style="font-size: 0px;" href="javascript:' + this.obj + '.monthback();">';
	if(this.leftimage != ''){
		html += '<img src="' + this.leftimage + '" style="font-size: 0px;" border="0">';	
	}else{
		html += '&lt;';
	}
	html += '</a></td>';
	html += '<td class="clyeartitle" valign="top" colspan="5"> <span class="clmonthname" id="' + this.obj + '_monthname"></span> </td>';	
	html += '<td class="clyeartitle" valign="top"><a style="font-size: 0px;" href="javascript:' + this.obj + '.monthforward();">';		
	if(this.rightimage != ''){
		html += '<img src="' + this.rightimage + '" style="font-size: 0px;" border="0">';	
	}else{
		html += '&gt;';	
	}	
	html += '</a></td></tr>';
	html += '<tr><td width="14%" class="cldayname cldate">su</td><td width="14%" class="cldayname cldate">mo</td><td width="14%" class="cldayname cldate">tu</td><td width="14%" class="cldayname cldate">we</td><td width="14%" class="cldayname cldate">th</td><td width="14%" class="cldayname cldate">fr</td><td width="14%" class="cldayname cldate">sa</td></tr>';	
	for(var row=0; row<6; row++){
		html += '<tr id="' + this.obj + '_row' + row + '">';
		for(var col=0; col<7; col++){
			html += '<td id="' + this.obj + '_cell' + row + '_' + col + '">&nbsp;</td>';
		}
		html += '</tr>';
	}
	html += '</table>';
	this.divElement.innerHTML = html;
	
	this.divElementStyle.display = 'none';
	if(this.ownerElement.value == 'Loading...' || this.ownerElement.value == '') this.ownerElement.value = this.defaulttext;
	this.loaded = true;
}

// if the Common functions are installed, prevent conflict with other onresize events.
if(Event.add){
	Event.add(window,'resize',function(){
		for(var i=1; i <= Calendar.instance; i++){
			var obj = "CalendarInstance_" + i;
			eval(obj + ".setPosition()");
		}
	});
}else{
	onresize = function(){
		for(var i=1; i <= Calendar.instance; i++){
			var obj = "CalendarInstance_" + i;
			eval(obj + ".setPosition()");
		}
	}
}