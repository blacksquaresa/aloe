function ImageSlider(prefix,classprefix){
	this.prefix = prefix;
	this.classprefix = classprefix;
	this.classnames  = {
		'before_clicked' : classprefix+'_clicked',
		'after_clicked' : classprefix+'_clicked',
		'before_disabled' : classprefix+'_disabled',
		'after_disabled' : classprefix+'_disabled'
	}
	
	this.speed = 40;
	this.stepping = false;
	this.stepindex = 0;
	this.stepdistanceleft = 0;
	this.stepdirection = 1;
	this.shownpanels = 0;
	
	this.preloaded_file ='';
	this.blending = false;
	this.loading_bg=false;
	this.loading_cancel=false;
	this.millisecs = 1000;
	
	this.window = document.getElementById(this.prefix+'_window'); 
	this.belt = document.getElementById(this.prefix+'_belt');
	this.beforebutton = document.getElementById(this.prefix+'_beforebutton'); 
	this.afterbutton = document.getElementById(this.prefix+'_afterbutton');
	
	this.captionContainer = document.getElementById(this.prefix+'_captioncontainer');
	this.captionText = document.getElementById(this.prefix+'_captiontext');
	this.captionBack = document.getElementById(this.prefix+'_captionback');
	
	this.windowsize = this.window.offsetWidth;
	if(this.windowsize==0){
		// In IE7, the sizing won't be calculated until the page has finished loading, so rebuild this object in the onload event.
		eval('Event.add(window,"load",function(){window.slider_'+this.prefix+' = new ImageSlider("'+this.prefix+'","'+this.classprefix+'");})');
		return;
	}
	
	this.obj = "ImageSliderInstance_" + (++ ImageSlider.instance);
	eval (this.obj + "=this");
	
	this.panelArray = [];
	var totalsize = this._measurePanels();
	if(totalsize > this.windowsize){
		this.currentpanel = 0;	
		this.originalbeltoffset = this.belt.offsetLeft;
		this.belt.style.width = totalsize + 'px';
		eval('this.afterbutton.onclick = function(){ ' + this.obj + '.step(-1); }');
		this.afterbutton.className = this.afterbutton.className.replace(this.classnames.after_disabled,'');
		this.beforebutton.className += ' ' + this.classnames.before_disabled;
	}else{
		this.beforebutton.className += ' ' + this.classnames.before_disabled;
		this.afterbutton.className += ' ' + this.classnames.after_disabled;
	}
}
ImageSlider.instance = 0;

ImageSlider.prototype.step = function(direction){
	if(!this.stepping){
		this.stepping = true;
		this.stepindex = 0;
		this.stepdistanceleft = this.panelArray[this.currentpanel].size * this.shownpanels;
		this.stepdirection = direction;
		if(direction < 0){
			this.afterbutton.className += ' ' + this.classnames.after_clicked;
		}else{
			this.beforebutton.className += ' ' + this.classnames.before_clicked;
		}
		this.interval = setInterval(this.obj + '._stepincrement()',this.speed);
	}
}

ImageSlider.prototype._stepincrement = function(){
	if(this.stepdistanceleft){
		var inc = Math.floor(Math.min(this.stepdistanceleft,Math.max(this.stepdistanceleft/2,2)));
		this.belt.style.left = (this.belt.offsetLeft - this.originalbeltoffset + (inc*this.stepdirection)) + 'px';
		this.stepindex ++;
		this.stepdistanceleft -= inc;
	}else{
		clearInterval (this.interval);
		this.interval = null;
		this.currentpanel -= (this.stepdirection * this.shownpanels);
		if(this.stepdirection < 0){
			this.afterbutton.className = this.afterbutton.className.replace(this.classnames.after_clicked,'');
		}else{
			this.beforebutton.className = this.beforebutton.className.replace(this.classnames.before_clicked,'');
		}
		if(this.currentpanel <= 0){
			this.beforebutton.className += ' ' + this.classnames.before_disabled;
			eval('this.beforebutton.onclick = null');
		}else{
			this.beforebutton.className = 'CMPhotoGallery_beforebutton';
			eval('this.beforebutton.onclick = function(){ ' + this.obj + '.step(1); }');
		}
		if((this.currentpanel) >= this.panelArray.length - this.shownpanels){
			this.afterbutton.className += ' ' + this.classnames.after_disabled;
			eval('this.afterbutton.onclick = null');
		}else{
			this.afterbutton.className = this.afterbutton.className.replace(this.classnames.after_disabled,'');
			eval('this.afterbutton.onclick = function(){ ' + this.obj + '.step(-1); }');
		}
		this.stepping = false;
	}
}

ImageSlider.prototype._measurePanels = function(){
	var totalsize = 0;
	for(i=0;i<this.belt.childNodes.length;i++){
		var panel = this.belt.childNodes[i];
		if(panel.id && panel.id.substring(0,this.prefix.length+6) == this.prefix + '_panel'){
			var sliderpanel = new ImageSliderPanel(this,panel);
			this.panelArray[this.panelArray.length] = sliderpanel;
			totalsize += sliderpanel.size;
			if(totalsize <= this.windowsize) this.shownpanels++;
		}
	}
	return totalsize;
}

ImageSlider.prototype.show = function(newimage,caption){
	var oldimage = document.getElementById(this.prefix+'_fullview');
	var oldimage_src = oldimage.src;
	if(newimage.length<=0 || this.blending==true){
		oldimage.src=newimage;
		var captioncontainer = document.getElementById(this.prefix+'_captioncontainer');	
		var captext = document.getElementById(this.prefix+'_captiontext');
		captext.innerHTML = caption;	
		captioncontainer.style.display = caption.length?'block':'none';
		return;
	 }
	this.blending = true;
	this.showLoading();
	preloaded_file = new Image();
	preloaded_file.slider = this;
	preloaded_file.onload = ImageSliderBlendStart;
	preloaded_file.title = caption;
	preloaded_file.src = newimage; 
	return;	
}

ImageSlider.prototype.showLoading = function(){
	if(this.loading_bg==false){
		this.loading_bg=true;
		this.loading_bg = setTimeout("document.getElementById('"+this.prefix+"_overlay').style.display = 'block';",200)
	}else{
		clearTimeout(this.loading_bg);
		this.loading_bg = false;
		var overlay = document.getElementById(this.prefix+'_overlay');
		overlay.style.display = "none";	
	}		
}

ImageSlider.prototype.blendimage =  function(newimage) {
	var timer = 0;
	var div_holder = document.getElementById(this.prefix+'_Image');
	var oldimage = document.getElementById(this.prefix+'_fullview');	
	var captioncontainer = document.getElementById(this.prefix+'_captioncontainer');	
	var captext = document.getElementById(this.prefix+'_captiontext');
	captext.innerHTML = newimage.title;	
	captioncontainer.style.display = newimage.title.length?'block':'none';
	div_holder.style.backgroundImage = "url(" + oldimage.src + ")";
	changeOpac(0, oldimage);
	changeOpac(0, captioncontainer);
	oldimage.src = newimage.src;
	this.blend();
}

ImageSlider.prototype.blend = function(){
	var speed = Math.round(this.millisecs / 100);
	var img = document.getElementById(this.prefix+'_fullview'); 
	var captioncontainer = document.getElementById(this.prefix+'_captioncontainer');
	var opacity = img.style.opacity * 100;
	if(opacity < 100){
		changeOpac(opacity+1,img);
		changeOpac(opacity+1,captioncontainer);
		if(opacity>85) this.blending=false;
		setTimeout(this.obj+".blend()",speed);
	}
}

function ImageSliderBlendStart(){
	this.slider.showLoading();
	this.slider.blendimage(this);
}

function ImageSliderPanel(owner,obj){
	this.owner = owner;
	this.obj = obj;
	this.index = obj.id.substring(owner.prefix.length+7);
	this.size = obj.offsetWidth;
}
	
//change the opacity for different browsers
function changeOpac(opacity, obj) {
	var style = obj.style;
	style.opacity = (opacity / 100);
	style.MozOpacity = (opacity/100);
	style.KhtmlOpacity = (opacity / 100);
	style.filter = "alpha(opacity=" + opacity + ")";
}


function findPosX(obj) {
    var curleft = 0;
    if (obj.offsetParent) {
        while (1) {
            curleft+=obj.offsetLeft;
            if (!obj.offsetParent) {
                break;
            }
            obj=obj.offsetParent;
        }
    } else if (obj.x) {
        curleft+=obj.x;
    }
    return curleft;
}
function findPosY(obj) {
    var curtop = 0;
    if (obj.offsetParent) {
        while (1) {
            curtop+=obj.offsetTop;
            if (!obj.offsetParent) {
                break;
            }
            obj=obj.offsetParent;
        }
    } else if (obj.y) {
        curtop+=obj.y;
    }
    return curtop;
}