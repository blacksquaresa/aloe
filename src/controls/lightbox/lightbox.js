var LB_settings,LB_collections,LB_cover,LB_container;
function LB_Init(){
	releaseEventHandler(window,'load',LB_Init);
	// check settings, and provide defaults
	if(LB_settings == undefined) LB_settings = {};
	if(LB_settings.imagepath == undefined) LB_settings.imagepath = '/images/lightbox/';
	if(LB_settings.padding == undefined) LB_settings.padding = 10;
	if(LB_settings.captionspacer == undefined) LB_settings.captionspacer = LB_settings.padding;
	if(LB_settings.videoplayer == undefined) LB_settings.videoplayer = '/swf/videoplayer.swf';
	if(LB_settings.audioplayer == undefined) LB_settings.audioplayer = '/swf/pi.swf';
	if(LB_settings.audioxml == undefined) LB_settings.audioxml = '/swf/pixml.php';
	if(LB_settings.videowidth == undefined) LB_settings.videowidth = 400;
	if(LB_settings.videoheight == undefined) LB_settings.videoheight = 300;
	if(LB_settings.audiowidth == undefined) LB_settings.audiowidth = 450;
	if(LB_settings.audioheight == undefined) LB_settings.audioheight = 290;
	if(LB_settings.audiodisplaywidth == undefined) LB_settings.audiodisplaywidth = 400;
	if(LB_settings.audiodisplayheight == undefined) LB_settings.audiodisplayheight = 36;
	if(LB_settings.usefade == undefined) LB_settings.usefade = true;
	if(LB_settings.slideduration == undefined) LB_settings.slideduration = 500;
	if(LB_settings.slidestep == undefined) LB_settings.slidestep = 50;
	if(LB_settings.stepminsize == undefined) LB_settings.stepminsize = 2;
	
	// include built-in default styles, above all others, so they get overridden
	var style = document.createElement('style'); 
	style.type = 'text/css'; 
	var stylecontent = ".LB_container { position: absolute;z-index: 99999;display:none;width:400px;height:300px;padding:"+LB_settings.padding+"px;background-color:#ffffff;overflow:hidden; }\r\n"; 
	stylecontent += ".LB_display { background:#ffffff url('"+LB_settings.imagepath+"loading.gif') center center no-repeat; overflow: hidden; }\r\n"; 
	stylecontent += ".LB_caption { margin-top: "+LB_settings.captionspacer+"px;background-color:#ffffff;color: #797979; }\r\n"; 
	stylecontent += ".LB_previous { position: absolute;top:0px;left:0px;height:100%;background:url('"+LB_settings.imagepath+"previous.png') center left no-repeat;cursor:pointer; }\r\n"; 
	stylecontent += ".LB_next { position:absolute;top:0px;right:0px;height:100%;background:url('"+LB_settings.imagepath+"next.png') center right no-repeat;cursor:pointer; }\r\n"; 
	stylecontent += ".LB_close { position:absolute;top:10px;right:10px; }\r\n"; 
	stylecontent += ".LB_error { color:#f99;font-weight: bold; }"; 
	if(style.styleSheet) style.styleSheet.cssText = stylecontent; // for IE
	else style.innerHTML = stylecontent; // for everyone else
	var head = document.getElementsByTagName('head')[0];
	head.insertBefore(style,head.firstChild); 
	
	// identify all lightbox links, and process them.
	LB_collections = {};
	var links = document.getElementsByTagName('a');
	for(var i=0;i<links.length;i++){
		var link = links[i];
		var rel = link.getAttribute('rel');
		if(rel && rel.length>= 8 && rel.substring(0,8) == 'lightbox'){
			var item = new LBItem(link);
			if(!LB_collections[item.collection]) LB_collections[item.collection] = [];
			LB_collections[item.collection].push(item);
			item.index = LB_collections[item.collection].length - 1;
			link.href = "javascript: LB_container.show('"+item.collection+"','"+item.index+"');";
		}
	}
	// Add a specific link to the specified collection
	LB_collections._additem = function(link,collection){
		collection = trim(collection,'_');
		if(link instanceof String){
			link = document.getElementById(link);
		}
		if(link){
			link.rel = 'lightbox_' + collection;
			var item = new LBItem(link);
			if(!this[item.collection]) this[item.collection] = [];
			this[item.collection].push(item);
			item.index = this[item.collection].length - 1;
			link.href = "javascript: LB_container.show('"+item.collection+"','"+item.index+"');";
		}
	}
	// Add all links within a container to the lightbox. Used mainly for ajax-generated content.
	LB_collections._additems = function(container){
		if(container instanceof String) container = document.getElementById(container);
		if(container){
			var links = container.getElementsByTagName('a');
			for(var i=0;i<links.length;i++){
				var rel = links[i].getAttribute('rel');
				if(rel && rel.length>= 8 && rel.substring(0,8) == 'lightbox'){
					var item = new LBItem(link);
					if(!this[item.collection]) this[item.collection] = [];
					this[item.collection].push(item);
					item.index = this[item.collection].length - 1;
					link.href = "javascript: LB_container.show('"+item.collection+"','"+item.index+"');";
				}
			}
		}
	}
	// create container
	LB_container = {
		elem : null,
		display : null,
		fadingdisplay : null,
		fadeouttimer : null,
		fadeintimer : null,
		slidetimer : null,
		slidesteps : null,
		slidestep : 0,
		sizedetails : null,
		currentsize : {width:400,height:300,captionheight:0},
		close : null,
		previous : null,
		next : null,
		caption : null,
		captionsizer : null,
		collection : null,
		index : 0,
		loadimage : null,
		init : function(){
			var pagesize = getPageSize();
			
			// the containing element
			this.elem = document.createElement('div');
			this.elem.style.top = Math.max((pagesize.windowheight/2) - (150+LB_settings.padding),0) + 'px';
			this.elem.style.left = Math.max((pagesize.windowwidth/2) - (200+LB_settings.padding),0) + 'px';
			this.currentsize.top = Math.max((pagesize.windowheight/2) - (150+LB_settings.padding),0);
			this.currentsize.left = Math.max((pagesize.windowwidth/2) - (200+LB_settings.padding),0);
			this.elem.className = 'LB_container';
			document.body.insertBefore(this.elem,document.body.firstChild);
			
			// the container for the actual image, video, audio or embedded widget
			this.display = document.createElement('div');
			this.display.className = 'LB_display';
			this.elem.appendChild(this.display);
			
			// the container for the caption
			this.caption = document.createElement('div');
			this.caption.className = 'LB_caption';
			this.elem.appendChild(this.caption);
			
			// a hidden div off-screen used to calculate the height of the caption.
			this.captionsizer = document.createElement('div');
			this.captionsizer.style.position = 'absolute';
			this.captionsizer.style.left = '-2000px';
			this.captionsizer.style.top = '-2000px';
			this.captionsizer.className = 'LB_caption';
			document.body.insertBefore(this.captionsizer,document.body.firstChild);
			
			// previous button
			this.previous = document.createElement('div');
			this.previous.style.width = LB_settings.padding+'px';
			this.previous.innerHTML = '&nbsp;';
			this.previous.className = 'LB_previous';
			this.previous.onclick = function(){LB_container.showprevious();};
			this.elem.appendChild(this.previous);
			
			// next button
			this.next = document.createElement('div');
			this.next.style.width = LB_settings.padding+'px';
			this.next.onclick = function(){LB_container.shownext();};
			this.next.className = 'LB_next';
			this.next.innerHTML = '&nbsp;';
			this.elem.appendChild(this.next);
			
			// close button
			this.close = document.createElement('div');
			this.close.className = 'LB_close';
			this.close.innerHTML = '<a href="javascript:LB_container.hide();"><img src="'+LB_settings.imagepath+'close.png" /></a>';
			this.elem.appendChild(this.close);
			
		},
		show : function(collection,index){
			LB_cover.show();
			if(!LB_collections || !LB_collections[collection]){
				this.showerror('ERROR: Collection not found');
				return;
			}
			if(!LB_collections[collection][index]){
				this.showerror('ERROR: item not found');
				return;
			}
			var item = LB_collections[collection][index];
			this.collection = collection;
			this.index = index;
			// setup the existing image for fade, or clear the previous item
			if(LB_settings.usefade && this.display.firstChild && this.display.firstChild.tagName == 'IMG'){
				if(this.fadingdisplay) this.elem.removeChild(this.fadingdisplay);
				this.fadingdisplay = this.display;
				this.fadingdisplay.style.position = 'absolute';
				this.fadingdisplay.style.top = LB_settings.padding+'px';
				this.fadingdisplay.style.left = LB_settings.padding+'px';
				this.display = document.createElement('div');
				this.display.className = 'LB_display';
				this.display.style.width = this.currentsize.width+'px';
				this.display.style.height = this.currentsize.height+'px';
				this.elem.insertBefore(this.display,this.fadingdisplay);
				this.fadeouttimer = setTimeout("LB_container.fadeout(100);",LB_settings.slidestep);
			}else{
				this.display.innerHTML = '';
			}
			this.caption.innerHTML = '';
			this.caption.style.display = 'none';
			this.elem.style.display = 'block';
			switch(item.type){
				case 'video':
					this.previous.style.width = LB_settings.padding+'px';
					this.next.style.width = LB_settings.padding+'px';
					this.caption.innerHTML = item.caption;
					this.sizeto(item.width?item.width:LB_settings.videowidth,item.height?item.height:LB_settings.videoheight);
					break;
				case 'embed':
					this.previous.style.width = LB_settings.padding+'px';
					this.next.style.width = LB_settings.padding+'px';
					this.caption.innerHTML = item.caption;
					this.sizeto(item.width?item.width:LB_settings.videowidth,item.height?item.height:LB_settings.videoheight);
					break;
				case 'audio':
					this.previous.style.width = LB_settings.padding+'px';
					this.next.style.width = LB_settings.padding+'px';
					this.caption.innerHTML = item.caption;
					this.sizeto(LB_settings.audiodisplaywidth,LB_settings.audiodisplayheight);
					break;
				default:
					this.loadimage = new Image();
					this.loadimage.item = item;
					this.loadimage.onload = function(){
						var item = this.item;
						LB_container.caption.innerHTML = item.caption;
						LB_container.sizeto(this.width,this.height);
					}
					this.previous.style.width = '50%';
					this.next.style.width = '50%';
					this.loadimage.src = item.href;
					break;
			}
		},
		showprevious : function(collection,index){
			if(this.index > 0) this.show(this.collection,this.index-1);
			else this.show(this.collection,LB_collections[this.collection].length - 1);
		},
		shownext : function(){
			if(this.index < LB_collections[this.collection].length - 1) this.show(this.collection,parseInt(this.index)+1);
			else this.show(this.collection,0);
		},
		showerror : function(text){
			this.display.style.display = 'none';
			this.caption.innerHTML = '<div class="LB_error">' + text + '</div>';
			this.sizeto(400,0);
		},
		hide : function(){
			if(this.slidetimer) clearInterval(this.slidetimer);
			if(this.fadeouttimer) clearTimeout(this.fadeouttimer);
			if(this.fadeintimer) clearTimeout(this.fadeintimer);
			this.elem.style.display = 'none';
			this.close.style.display = '';
			this.next.style.display = '';
			this.previous.style.display = '';
			this.display.innerHTML = '';
			this.caption.innerHTML = '';
			LB_cover.hide();
			this.collection = null;
			this.index = 0;
			if(this.slidetimer) clearInterval(this.slidetimer);
		},
		// prepare to resize, and calculate the caption size, if needed.
		sizeto : function(width,height){
			if(this.caption.innerHTML != ''){
				this.captionsizer.style.width = width+'px';
				this.captionsizer.innerHTML = this.caption.innerHTML;
				setTimeout("LB_container.sizecontainer("+width+","+height+");",1);
			}else{
				this.sizecontainer(width,height);
			}
		},
		// once we have the caption size, resize the container
		sizecontainer : function(width,height){
			var pagesize = getPageSize();
			this.sizedetails = {};
			this.sizedetails.width = width;
			this.sizedetails.height = height;
			this.sizedetails.captionheight = this.captionsizer.offsetHeight;
			this.sizedetails.fullheight = height + (this.sizedetails.captionheight?this.sizedetails.captionheight+LB_settings.captionspacer:0);
			this.sizedetails.top = Math.max((pagesize.windowheight/2) - ((this.sizedetails.fullheight+2*LB_settings.padding)/2) + (document.documentElement.scrollTop | document.body.scrollTop),0);
			this.sizedetails.left = Math.max((pagesize.windowwidth/2) - ((width+2*LB_settings.padding)/2) + (document.documentElement.scrollLeft | document.body.scrollLeft),0);
			if(LB_collections[this.collection][this.index].type != 'image') this.display.style.display = 'none';
			this.displayitem();
			if(!LB_settings.usefade && LB_collections[this.collection][this.index].type == 'image' && this.display.firstChild) this.display.firstChild.style.display = 'none';
			this.close.style.display = 'none';
			this.next.style.display = 'none';
			this.previous.style.display = 'none';
			this.beginslide();
			this.captionsizer.innerHTML = '';
		},
		// once we have resized the container, show the image, video, audio or embedded widget
		displayitem : function(){
			var item = LB_collections[this.collection][this.index];
			switch(item.type){
				case 'video':
					this.display.onclick = '';
					var vidObj = new SWFObject(LB_settings.videoplayer,item.title,LB_settings.videowidth,LB_settings.videoheight,'9','#000;');
					vidObj.addParam("allowFullScreen", "true");
					vidObj.addParam("scale", "noscale");
					vidObj.addParam("menu", "false");
					vidObj.addParam("wmode", "transparent");					
					vidObj.addVariable("inFlash", "false");
					vidObj.addVariable("autoPlayVid", "true");
					vidObj.addVariable("usemWheel", "true");
					vidObj.addVariable("useStatusBox", "true");
					vidObj.addVariable("usePlayAgain", "true");
					vidObj.addVariable("hideControls", "true");
					vidObj.addVariable("useAspectRatio", "true");
					vidObj.addVariable("useMetaVideoSize", "false");
					vidObj.addVariable("initVolume", "60");
					vidObj.addVariable("mSens", "2");
					vidObj.addVariable("bufBGAlpha", "75");
					vidObj.addVariable("controlAlpha", "75");
					vidObj.addVariable("statusAlpha", "75");
					vidObj.addVariable("bufLength", "5");
					vidObj.addVariable("dcSpeed", "250");
					vidObj.addVariable("vidWidth", LB_settings.videowidth);
					vidObj.addVariable("vidHeight", LB_settings.videoheight);
					vidObj.addVariable("baseColour", "111111");
					vidObj.addVariable("highColour", "C80D45");
					vidObj.addVariable("iconColour", "FFFFFF");
					vidObj.addVariable("miscColour", "58051F");
					vidObj.addVariable("videoFile", item.href);
					vidObj.addVariable("useImage", "false");
					vidObj.write(this.display);
					break;
				case 'audio':
					this.display.onclick = '';
					var base = item.href.substring(0,item.href.length-basename(item.href).length);
					var xml = LB_settings.audioxml+'?file=' + escape(item.href);
					if(item.title) xml += escape('&title=' + item.title);
					if(item.artist) xml += escape('&artist=' + item.artist);
					var vidObj = new SWFObject(LB_settings.audioplayer,item.title,LB_settings.audiowidth,LB_settings.audioheight,'9','#000;');
					vidObj.addParam("scale", "noscale");
					vidObj.addParam("base", base);
					vidObj.addParam("wmode", "transparent");	
					vidObj.addVariable("playlist", xml);
					vidObj.write(this.display);
					break;
				case 'embed':
					this.display.onclick = '';
					this.display.innerHTML = item.vcode;
					break;
				default:
					this.display.appendChild(this.loadimage);
					setOpacity(0,this.display.firstChild);
					this.display.onclick = function(){LB_container.hide();};
					break;
			}
		},
		beginslide : function(){
			this.slidesteps = [];
			var laststep = null;
			for(var i=0; i<=LB_settings.slideduration; i+=LB_settings.slidestep){
				var step = new LBSlideStep(i,this.currentsize,this.sizedetails);
				if(!laststep || step.checkChange(laststep)){
					this.slidesteps.push(step);
					laststep = step;
				}
			}
			this.slidestepindex = 0;
			this.slidesteptotal = Math.floor(LB_settings.slideduration/LB_settings.slidestep);
			this.slidetimer = setInterval("LB_container.slide();",LB_settings.slidestep);
		},
		slide : function(){	
			if(this.slidestepindex >= this.slidesteptotal){
				if(this.slidetimer) clearInterval(this.slidetimer);
				this.display.style.display = 'block';
				if(this.display.firstChild && this.display.firstChild.style.display == 'none') this.display.firstChild.style.display = '';
				this.caption.style.display = this.caption.innerHTML != ''?'block':'none';
				
				this.caption.style.width = this.sizedetails.width+'px';
				this.display.style.width = this.sizedetails.width+'px';
				this.display.style.height = this.sizedetails.height+'px';
				if(this.display.firstChild && this.display.firstChild.tagName == 'IMG'){
					this.display.firstChild.style.width = this.sizedetails.width+'px';
					this.display.firstChild.style.height = this.sizedetails.height+'px';
				}
				this.elem.style.width = this.sizedetails.width+'px';
				this.elem.style.height = this.sizedetails.fullheight+'px';
				this.elem.style.top = this.sizedetails.top+'px';
				this.elem.style.left = this.sizedetails.left+'px';
				if(LB_settings.usefade) this.fadein(0);
				else setOpacity(100,this.display.firstChild);
				
				this.currentsize = this.sizedetails;
				this.close.style.display = '';
				if(LB_collections[this.collection].length>1){
					this.previous.style.display = 'block';
					this.next.style.display = 'block';
				}
				LB_cover.show();
			}else{
				try{
					if(this.slidestepindex < this.slidesteps.length){
						var step = this.slidesteps[this.slidestepindex];
						this.caption.style.width = step.width+'px';
						this.display.style.width = step.width+'px';
						this.display.style.height = step.height+'px';
						if(this.fadingdisplay){
							this.fadingdisplay.style.top = step.fadetop+'px';
							this.fadingdisplay.style.left = step.fadeleft+'px';
						}
						this.elem.style.width = Math.max(step.width,0)+'px';
						this.elem.style.height = Math.max(step.fullheight,0)+'px';
						this.elem.style.top = Math.max(step.top,0)+'px';
						this.elem.style.left = Math.max(step.left,0)+'px';
					}
				}finally{
					this.slidestepindex++;
				}
			}
		},
		fadein : function(opacity){
			if(this.fadeintimer){
				clearTimeout(this.fadeintimer);
			}
			if(opacity<=100){
				setOpacity(opacity,this.display.firstChild);
				var step = Math.max(1,Math.round(LB_settings.slideduration / LB_settings.slidestep));
				this.fadeintimer = setTimeout("LB_container.fadein("+(opacity+step)+");",LB_settings.slidestep);
			}
		},
		fadeout : function(opacity){
			if(this.fadeouttimer){
				clearTimeout(this.fadeouttimer);
			}
			if(opacity<=0){
				this.elem.removeChild(this.fadingdisplay);
				this.fadingdisplay = null;
			}else{
				setOpacity(opacity,this.fadingdisplay);
				var step = Math.max(1,Math.round(LB_settings.slideduration / LB_settings.slidestep));
				this.fadeouttimer = setTimeout("LB_container.fadeout("+(opacity-step)+");",LB_settings.slidestep);
			}
		}
	};
	LB_container.init();
	// create cover
	LB_cover = {
		elem : null,
		init : function(){
			this.elem = document.createElement('div');
			this.elem.style.position = 'absolute';
			this.elem.style.top = '0px';
			this.elem.style.left = '0px';
			this.elem.style.width = '100%';
			this.elem.style.height = '100%';
			this.elem.style.backgroundColor = "#000";
			this.elem.style.opacity = '0.7';
			this.elem.style.filter = 'alpha(opacity=70)';
			this.elem.style.zIndex = 99998;
			this.elem.style.display = 'none';
			this.elem.className = 'LB_cover';
			this.elem.onclick = function(){LB_container.hide();};
			document.body.insertBefore(this.elem,document.body.firstChild);
		},
		show : function(){
			this.elem.style.display = 'block';
			var pagesize = getPageSize();
			this.elem.style.height = pagesize.pageheight+'px';
		},
		hide : function(){
			this.elem.style.display = 'none';
		}
	};
	LB_cover.init();
}

function LBItem(link){
	this.collection = (link.rel.length > 9)?trim(link.rel.substring(9),'_'):'lightbox';
	if(this.collection=='') this.collection = 'lightbox';
	this.type = link.rev?link.rev.toLowerCase():'image';
	if(this.type != 'video' && this.type != 'audio' && this.type != 'embed') this.type = 'image';
	this.href = link.href;
	this.caption = link.getAttribute('caption');
	this.title = link.title;
	this.vcode = link.getAttribute('vcode');
	this.index = 0;
	try{ this.width = parseInt(link.getAttribute('width')); } catch(e){}
	try{ this.height = parseInt(link.getAttribute('height')); } catch(e){}
	this.link = link;
	if(this.title.length > 1 && this.title.substring(0,1) == '{'){
		try{
			var props = eval('('+this.title+')');
			this.title = '';
			var caption = '';
			for(key in props){
				this[key] = props[key];
				if(key != 'vcode' && key != 'width' && key != 'height'){
					caption += '<div>'+ucwords(key)+': '+props[key]+'</div>';
				}
			}
			if(!this.caption) this.caption = caption;
		}catch(e){
			this.caption = '<div class="LB_error">Error: invalid title</div>';
		}
		link.title = this.title;
	}
	if(!this.caption) this.caption = this.title;
}

function LBSlideStep(time,currentsize,targetsize){
	time = time / LB_settings.slideduration;
	//var ratio = time; // linear
	var ratio = (2*time) - ((Math.pow(time,2))); // quadratic
	//var ratio = (2*(1-time)*time*1) + ((Math.pow(time,2)); // bezier
	//debug(time + ' : ' + ratio);
	this.width = Math.max(Math.round(currentsize.width + ((targetsize.width-currentsize.width)*ratio)),0);	
	this.height = Math.max(Math.round(currentsize.height + ((targetsize.height-currentsize.height)*ratio)),0);	
	this.fullheight = (targetsize.captionheight?targetsize.captionheight+LB_settings.captionspacer:0) + this.height;
	this.top = Math.max(Math.round(currentsize.top + ((targetsize.top-currentsize.top)*ratio)),0);	
	this.left = Math.max(Math.round(currentsize.left + ((targetsize.left-currentsize.left)*ratio)),0);
	if(LB_settings.usefade && LB_container.fadingdisplay){
		this.fadetop = LB_settings.padding+Math.round(((targetsize.fullheight - currentsize.fullheight) * ratio)/2);
		this.fadeleft = LB_settings.padding+Math.round(((targetsize.width - currentsize.width) * ratio)/2);
	}
}

LBSlideStep.prototype.checkChange = function(previous){
	if(Math.abs(this.width-previous.width) > LB_settings.stepminsize) return true;
	if(Math.abs(this.height-previous.height) > LB_settings.stepminsize) return true;
	if(Math.abs(this.fullheight-previous.fullheight) > LB_settings.stepminsize) return true;
	if(Math.abs(this.top-previous.top) > LB_settings.stepminsize) return true;
	if(Math.abs(this.left-previous.left) > LB_settings.stepminsize) return true;
	return false;
}

// load the lightbox
//if(window.OnDomLoaded){
//	OnDomLoaded(LB_Init);
//}
//attachEventHandler(window,'load',LB_Init);