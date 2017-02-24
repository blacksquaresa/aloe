var isie = document.all && !window.opera;

function trim(stringToTrim,chars) {
	if(!stringToTrim || stringToTrim.length==0) return stringToTrim;
	if(!chars || chars.length == 0) chars = '\\s';
	chars = chars.toString().split('').reverse().join('').replace(new RegExp('([\\/\\(\\)\\.\\?\\[\\]\\*\\-\\+])(?!\\\\)','gi'),'$1\\').split('').reverse().join('');
	var regex = new RegExp('^['+chars+']+|['+chars+']+$','g');
	return stringToTrim.toString().replace(regex,"");
}

function ucwords(str){
	return(str + '').replace(/^([a-z])|\s+([a-z])/g, function($1){
        return $1.toUpperCase();
    });
} 

function getAbsTop(o) {
	oTop = o.offsetTop;
	while(o.offsetParent!=null && getCurrentStyle(o.offsetParent,'position')!='absolute') {
		oParent = o.offsetParent;
		oTop += oParent.offsetTop;
		o = oParent;
	}
	return oTop;
}
function getAbsLeft(o) {
	oLeft = o.offsetLeft;
	while(o.offsetParent!=null && getCurrentStyle(o.offsetParent,'position')!='absolute') {
		oParent = o.offsetParent;
		oLeft += oParent.offsetLeft;
		o = oParent;
	}
	return oLeft;
}

function getCurrentStyle(oElm, strCssRule){
	var strValue = "";
	if(document.defaultView && document.defaultView.getComputedStyle){
		strValue = document.defaultView.getComputedStyle(oElm, "").getPropertyValue(strCssRule);
	}
	else if(oElm.currentStyle){
		strCssRule = strCssRule.replace(/\-(\w)/g, function (strMatch, p1){
			return p1.toUpperCase();
		});
		strValue = oElm.currentStyle[strCssRule];
	}
	return strValue;
}

function setOpacity(opacity, obj) {
	if(obj instanceof String) obj = document.getElementById(obj);
	if(obj){
		var objStyle;
		objStyle = obj.style;
		opacity = Math.max(Math.min(opacity,100),0);
		objStyle.opacity = (opacity / 100);
		objStyle.MozOpacity = (opacity/100);
		objStyle.KhtmlOpacity = (opacity / 100);
		objStyle.filter = "alpha(opacity=" + opacity + ")";
	}
}	

// will return true if parent is child.
function isParentOf(parent,child){
	if(!parent || !child) return false;
	if(parent == child) return true;
	if(child.parentNode){
		while(child = child.parentNode){
			if(parent == child) return true;			
		}
	}
	return false;
}

function arrayIndexOf(arr, elt /*, from*/){
	if(typeof(arr) == 'Array'){
		var len = arr.Length;
		var from = Number(arguments[2]) || 0;
		from = (from < 0)?Math.ceil(from):Math.floor(from);
		if (from < 0) from += len;
		for (; from < len; from++){
			if (from in arr && arr[from] === elt) return from;
		}
    }else if(typeof(arr) == 'object'){
		for(key in arr){
			if(arr[key] == elt) return key;
		}
    }
    return -1;
}

// get the size of an associative array (object)
function arraySizeOf(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
}

// sort an associative array (object) by it's keys
function keysort(arr,dir){
	if(dir!=-1) dir = 1;
	var keys = [];
	for(k in arr) keys.push(k);
	keys.sort( function (a, b){return (a - b)*dir;} );
	var res = {};
	for(i=0;i<keys.length;i++) res[keys[i]] = arr[keys[i]];
	return res;
}

// checks for a positive integer (or 0)
function isnumeric(val){
	var check = /^[\d]+$/;
	return check.test(trim(val));
}

function basename(path) {
    return trim(path.replace(/\\/g,'/'),'/').replace( /.*\//, '' );
}
 
function dirname(path) {
    return trim(path.replace(/\\/g,'/'),'/').replace(/\/[^\/]*$/, '');
}

function MousePosition(e){
	this.posX = 0;
	this.posy = 0;
	if (!e) var e = window.event;
	if (e.pageX || e.pageY){
		this.posx = e.pageX;
		this.posy = e.pageY;
	}
	else if (e.clientX || e.clientY){
		this.posx = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft - document.documentElement.clientLeft;
		this.posy = e.clientY + document.body.scrollTop + document.documentElement.scrollTop - document.documentElement.clientTop;
	}
}

// Designed to be used to restrict a field to any number. 
// Usage: <input type="text" onkeyup="return NumbersOnly(this,event,true,true)">
function NumbersOnly(elem,e,whole,positive){
	var num,text = elem.value;
	if(text=='-'||text=='.') return;
	try{num=parseFloat(text);}catch(err){elem.value = '';return;}
	if(whole&&num!=Math.round(num)) num = Math.round(num);
	if(positive&&num<0) num = '';
	if(num!=text) elem.value = isNaN(num)?'':num;
}

// use this function to set the value of a form element.
function setElementValue(target, value, owner){
	if(owner == 'undefined' || owner == null) owner = document;
	if(typeof(owner) == 'string') owner = eval(owner);
	if(typeof(owner) == 'function') return owner(target,value);
	if(typeof(target) == 'string') target = owner.getElementById(target);
	if(target){
		if(target.tagName.toLowerCase() == 'input'){
			if(target.type == 'checkbox'){
				target.checked = value==null?false:value?true:false;
			}else{
				target.value = value==null?'':value;
			}
			return true;
		}else if(target.tagName.toLowerCase() == 'textarea'){
			if(target.className.indexOf('mceEditor') >= 0 && tinymce && (editor=tinymce.getInstanceById(target.id)) && editor.dom){
				owner.window.tinyMCE.getInstanceById(target.id).setContent(value);
			}else{
				target.value = value;
			}
			return true;
		}else if(target.tagName.toLowerCase() == 'select'){
			for(j=0;j<target.length;j++){
				if(target[j].value==value){
					target.selectedIndex = j;
					return true;
				}
			}
			target.selectedIndex = 0;	
		}else{
			target.innerHTML = value==null?'':value;
			return true;
		}
	}
	return false;
}

function getRandomCode(){
	var string = Math.round(Math.random() * 100000000);
	return '?' + string;
}

function attachEventHandler(elem,type,listener,capture){
	if(typeof(elem) == 'string') elem = document.getElementById(elem);
	if(elem.attachEvent){
		elem.attachEvent("on" + type, listener);
	}else if(elem.addEventListener){
		elem.addEventListener(type, listener, capture?true:false);
	}
}

function releaseEventHandler(elem,type,listener,capture){
	if(typeof(elem) == 'string') elem = document.getElementById(elem);
	if(elem.attachEvent){
		elem.detachEvent("on" + type, listener);
	}else if(elem.addEventListener){
		elem.removeEventListener(type, listener, capture?true:false);
	}
}

function getPageSize(){	
	var xScroll, yScroll;	
	if (window.innerHeight && window.scrollMaxY) {	
		xScroll = document.body.scrollWidth;
		yScroll = window.innerHeight + window.scrollMaxY;
	} else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
		xScroll = document.body.scrollWidth;
		yScroll = document.body.scrollHeight;
	} else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
		xScroll = document.body.offsetWidth;
		yScroll = document.body.offsetHeight;
	}
	
	var windowWidth, windowHeight;
	if (self.innerHeight) {	// all except Explorer
		windowWidth = self.innerWidth;
		windowHeight = self.innerHeight;
	} else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
		windowWidth = document.documentElement.clientWidth;
		windowHeight = document.documentElement.clientHeight;
	} else if (document.body) { // other Explorers
		windowWidth = document.body.clientWidth;
		windowHeight = document.body.clientHeight;
	}	
	
	var pageHeight = Math.max(yScroll,windowHeight);
	var pageWidth = Math.max(xScroll,windowWidth);

	arrayPageSize = {'pagewidth':pageWidth,'pageheight':pageHeight,'windowwidth':windowWidth,'windowheight':windowHeight,'contentwidth':xScroll,'contentheight':yScroll};
	return arrayPageSize;
}

/*
Useful Event Manager, stolen from http://v3.thewatchmakerproject.com
Usage: to add an event, simply use Event.add(object,eventtype(without the on),function(name or code));
	   ro remove an event, use Event.remove(object,eventtype(without the on),function(name or code));
*/
var Event = {
	add: function(obj,type,fn) {
		if(typeof(obj) == 'string') obj = document.getElementById(obj);
		if (obj.attachEvent) {
			obj['e'+type+fn] = fn;
			obj[type+fn] = function() { obj['e'+type+fn](window.event); }
			obj.attachEvent('on'+type,obj[type+fn]);
		} else
		obj.addEventListener(type,fn,false);
	},
	remove: function(obj,type,fn) {
		if(typeof(obj) == 'string') obj = document.getElementById(obj);
		if (obj.detachEvent) {
			obj.detachEvent('on'+type,obj[type+fn]);
			obj[type+fn] = null;
		} else
		obj.removeEventListener(type,fn,false);
	}
}

function createCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

function sleep(milliseconds){
	var sleeping = true, target = new Date().getTime()+milliseconds;
	while(sleeping){
		if(new Date().getTime() > target) sleeping = false;
	}
}