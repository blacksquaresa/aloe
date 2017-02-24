function setColour(colour){
	currentcolour = colour;
	var obj = document.getElementById('newcolour');
	var robj = document.getElementById('red');
	var gobj = document.getElementById('green');
	var bobj = document.getElementById('blue');
	var trans = document.getElementById('transparent');
	var hex = document.getElementById('hex');
	var pos = document.getElementById('colourpos');
	var spot = document.getElementById('colourspot');
	var selector = document.getElementById('selector');
	obj.style.backgroundColor = colour;
	if(colour == 'transparent'){
		obj.style.border = 'dotted 1px #ccc';
		obj.style.width = '98px';
		obj.style.height = '78px';
		robj.disabled = true;
		gobj.disabled = true;
		bobj.disabled = true;
		hex.disabled = true;
		trans.checked = true;
		pos.style.display = 'none';
		spot.style.display = 'none';
		selector.style.backgroundColor = 'transparent';
	}else{
		obj.style.border = '0';
		obj.style.width = '';
		obj.style.height = '';
		var r = parseInt(colour.substring(1,3),16);
		var g = parseInt(colour.substring(3,5),16);
		var b = parseInt(colour.substring(5),16);
		robj.disabled = false;
		gobj.disabled = false;
		bobj.disabled = false;
		trans.checked = false;
		hex.disabled = false;
		setElementValue(robj,r);
		setElementValue(gobj,g);
		setElementValue(bobj,b);
		hex.value = colour.substring(1);
		var hsb = rgb_hsb(r,g,b);
		pos.style.bottom = (Math.round(hsb['h']/360*256)-3)+'px';
		spot.style.bottom = (Math.round(hsb['b']/100*256)-7)+'px';
		spot.style.left = (Math.round(hsb['s']/100*256)-7)+'px';
		pos.style.display = '';
		spot.style.display = '';
		var rgb = hsb_rgb(hsb['h'],100,100);
		selector.style.backgroundColor = rgb_hex(rgb.r,rgb.g,rgb.b);
	}
}

function toggleTransparent(){
	var trans = document.getElementById('transparent');
	if(trans.checked){
		setColour('transparent');
	}else{		
		setColour(getColourFromValues());
	}
}

function selectColour(src){
	var obj = document.getElementById(src);
	var val = parseInt(obj.value.replace(/[^\d]+/g,''));
	if(val > 255) val = 255;
	if(val < 0) val = 0;
	obj.value = val;	
	setColour(getColourFromValues());
}

function selectHex(){
	var obj = document.getElementById('hex');
	var val = obj.value.replace(/[^\da-fA-F]/g,'0');
	if(val.length > 6) val = val.substring(0,6);
	if(val.length == 3) val = val.substring(0,1) + val.substring(0,1) + val.substring(1,2) + val.substring(1,2) + val.substring(2,3) + val.substring(2,3);
	while(val.length < 6) val = '0' + val;
	obj.value = val;	
	setColour('#'+val);
}

function textHex(){
	var obj = document.getElementById('hex');
	var val = obj.value.replace(/[^\da-fA-F]/g,'');
	if(val.length == 6){
		obj.value = val;	
		setColour('#'+val);
	}
}

function getColourFromValues(){
	var robj = document.getElementById('red');
	var gobj = document.getElementById('green');
	var bobj = document.getElementById('blue');
	return rgb_hex(robj.value,gobj.value,bobj.value);
}

function returnColour(){
	if(currentcolour.toLowerCase() != 'transparent') currentcolour = currentcolour.toUpperCase();
	else currentcolour = currentcolour.toLowerCase();
	parent.setElementValue(sourceid,currentcolour,owner);
	parent.PopupManager.hideColourSelector();
}

function hex_rgb(hex){	
	var r = parseInt(hex.substring(1,3),16);
	var g = parseInt(hex.substring(3,5),16);
	var b = parseInt(hex.substring(5),16);
	return {'r':r,'g':g,'b':b};
}

function rgb_hex(r,g,b){
	r = parseInt(r).toString(16);
	g = parseInt(g).toString(16);
	b = parseInt(b).toString(16);
	if(r.length==1) r = '0'+r;
	if(g.length==1) g = '0'+g;
	if(b.length==1) b = '0'+b;
	return '#'+r+g+b;
}

function rgb_hsb(red,green,blue){
	red = red / 255;
	green = green / 255;
	blue = blue / 255;
	
	var min = Math.min(red, green, blue);
	var max = Math.max(red, green, blue);
	var diff = max - min;
	
	var brightness = max;
	
	if(diff == 0){
		var hue = 0;
		var saturation = 0;
	}else{
		var saturation = diff / max;
		var hue;
		
		var dr = (((max - red) / 6) + (diff / 2)) / diff;
		var dg = (((max - green) / 6) + (diff / 2)) / diff;
		var db = (((max - blue) / 6) + (diff / 2)) / diff;
		
		if(max == red) hue = db - dg;
		else if(max == green) hue = (1/3) + dr - db;
		else hue = (2/3) + dg - dr;
		
		if(hue < 0) hue += 1;
		if(hue > 1) hue -= 1;
	}
	
	brightness = brightness * 100;
	hue = hue * 360;
	saturation = saturation * 100;
	return {'h':hue,'s':saturation,'b':brightness};
}

function hsb_rgb(hue,saturation,brightness){
	hue = hue%360;
	if(hue < 0) hue = 360 - hue;
	hue = hue/360;
	brightness = brightness / 100;
	saturation = saturation / 100;
	if(saturation==0) return {'r':brightness*255,'g':brightness*255,'b':brightness*255};
	
	var h = hue * 6;
	var i = Math.floor(h);
	
	var val1 = brightness * (1 - saturation);
	var val2 = brightness * (1 - (saturation * (h - i)));
	var val3 = brightness * (1 - (saturation * (1- (h - i))));
	
	var red, green, blue;
	switch(i){
		case 0:
			red = brightness;
			green = val3;
			blue = val1;
			break;	
		case 1:
			red = val2;
			green = brightness;
			blue = val1;
			break;		
		case 2:
			red = val1;
			green = brightness;
			blue = val3;
			break;		
		case 3:
			red = val1;
			green = val2;
			blue = brightness;
			break;		
		case 4:
			red = val3;
			green = val1;
			blue = brightness;	
			break;	
		default:
			red = brightness;
			green = val1;
			blue = val2;
			break;		
	}
	red = red * 255;
	green = green * 255;
	blue = blue * 255;
	return {'r':red,'g':green,'b':blue};
}



/* =================================================
				Hue Selection
================================================= */

var HueDragObject = null;

function HueMouseDown(e){
	e = e || this.document.parentWindow.event;
	if(e.button==2) return false;
	// identify the item
	HueDragObject = document.getElementById('colourpos');
		
	HueDragObject.coverdiv = document.createElement('div');
	HueDragObject.coverdiv.style.height = '100%';
	HueDragObject.coverdiv.style.width = '100%';
	HueDragObject.coverdiv.style.top = '0px';
	HueDragObject.coverdiv.style.left = '0px';
	HueDragObject.coverdiv.style.position = 'fixed';
	HueDragObject.coverdiv.style.zIndex = 9999;
	HueDragObject.coverdiv.style.cursor = 'move';
	document.body.appendChild(HueDragObject.coverdiv);
	
	// set the iframe offset position for Opera
	HueDragObject.absOffsetTop = getAbsTop(HueDragObject);
	
	var rgb = hex_rgb(currentcolour);
	var hsb = rgb_hsb(rgb.r,rgb.g,rgb.b);
	HueDragObject.saturation = hsb.s;
	HueDragObject.brightness = hsb.b;
	
	// attach mousemove and mouseup events
	attachEventHandler(document,'mousemove',HueMouseMove);
	attachEventHandler(document,'mouseup',HueMouseUp);
	if(!isie) attachEventHandler(document,'mouseout',HueMouseLeave);
	
	HueMouseMove(e);
	
	return false;
}

function HueMouseUp(e){
	if(HueDragObject){
		HueMouseMove(e)
		ReleaseHueEvents();
	}
}

function ReleaseHueEvents(){
	releaseEventHandler(document,'mousemove',HueMouseMove);
	releaseEventHandler(document,'mouseup',HueMouseUp);
	if(!isie) releaseEventHandler(document,'mouseout',HueMouseLeave);
	document.body.removeChild(HueDragObject.coverdiv);
	HueDragObject.coverdiv = null;
	HueDragObject = null;
}

function HueMouseMove(e){
	if(HueDragObject){
		if(isie && e.button == 0){
			return ReleaseHueEvents();
		}
		var pos = new MousePosition(e);
		var hue;
		if(pos.posy < 0) hue = 360;
		else if(pos.posy > 256) hue = 0;
		else hue = ((256 - pos.posy)/256*360);
		
		var rgb = hsb_rgb(hue,HueDragObject.saturation,HueDragObject.brightness);
		setColour(rgb_hex(rgb.r,rgb.g,rgb.b));
	}
}

function HueMouseLeave(e){
	var pos = new MousePosition(e);
	var top = window.pageYOffset?window.pageYOffset:document.documentElement.scrollTop;
	var left = window.pageXOffset?window.pageXOffset:document.documentElement.scrollLeft;
	var bottom = top + document.body.clientHeight;
	var right = left + document.body.clientWidth;	
	if(pos.posy < top || pos.posy > bottom || pos.posx < left || pos.posx > right){
		HueMouseMove(e)
		ReleaseHueEvents();
	}
}



/* =================================================
				Spot Selection
================================================= */

var SpotDragObject = null;

function SpotMouseDown(e){
	e = e || this.document.parentWindow.event;
	if(e.button==2) return false;
	// identify the item
	SpotDragObject = document.getElementById('selector');
		
	SpotDragObject.coverdiv = document.createElement('div');
	SpotDragObject.coverdiv.style.height = '100%';
	SpotDragObject.coverdiv.style.width = '100%';
	SpotDragObject.coverdiv.style.top = '0px';
	SpotDragObject.coverdiv.style.left = '0px';
	SpotDragObject.coverdiv.style.position = 'fixed';
	SpotDragObject.coverdiv.style.zIndex = 9999;
	SpotDragObject.coverdiv.style.cursor = 'move';
	document.body.appendChild(SpotDragObject.coverdiv);
	
	document.getElementById('colourspot').style.visibility = 'hidden';
	
	var rgb = hex_rgb(currentcolour);
	var hsb = rgb_hsb(rgb.r,rgb.g,rgb.b);
	SpotDragObject.hue = hsb.h;
	
	SpotDragObject.xoffset = getAbsLeft(SpotDragObject);
	
	// attach mousemove and mouseup events
	attachEventHandler(document,'mousemove',SpotMouseMove);
	attachEventHandler(document,'mouseup',SpotMouseUp);
	if(!isie) attachEventHandler(document,'mouseout',SpotMouseLeave);
	
	SpotMouseMove(e);
	
	return false;
}

function SpotMouseUp(e){
	if(SpotDragObject){
		SpotMouseMove(e)
		ReleaseSpotEvents();
	}
}

function ReleaseSpotEvents(){
	releaseEventHandler(document,'mousemove',SpotMouseMove);
	releaseEventHandler(document,'mouseup',SpotMouseUp);
	if(!isie) releaseEventHandler(document,'mouseout',SpotMouseLeave);
	document.body.removeChild(SpotDragObject.coverdiv);
	document.getElementById('colourspot').style.visibility = 'visible';
	SpotDragObject.coverdiv = null;
	SpotDragObject = null;
}

function SpotMouseMove(e){
	if(SpotDragObject){
		if(isie && e.button == 0){
			return ReleaseSpotEvents();
		}
		var pos = new MousePosition(e);
		var saturation,brightness;
		if(pos.posx < SpotDragObject.xoffset) saturation = 0;
		else if(pos.posx > 256+SpotDragObject.xoffset) saturation = 100;
		else saturation = ((pos.posx - SpotDragObject.xoffset)/256*100);
		if(pos.posy < 0) brightness = 100;
		else if(pos.posy > 256) brightness = 0;
		else brightness = ((256 - pos.posy)/256*100);
		
		var rgb = hsb_rgb(SpotDragObject.hue,saturation,brightness);
		setColour(rgb_hex(rgb.r,rgb.g,rgb.b));
	}
}

function SpotMouseLeave(e){
	var pos = new MousePosition(e);
	var top = window.pageYOffset?window.pageYOffset:document.documentElement.scrollTop;
	var left = window.pageXOffset?window.pageXOffset:document.documentElement.scrollLeft;
	var bottom = top + document.body.clientHeight;
	var right = left + document.body.clientWidth;	
	if(pos.posy < top || pos.posy > bottom || pos.posx < left || pos.posx > right){
		SpotMouseMove(e)
		ReleaseSpotEvents();
	}
}