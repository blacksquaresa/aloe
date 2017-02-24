function CMSlideShow(prefix,type,indicator,speed,duration){
	this.prefix = prefix;
	this.type = type;
	this.indicator = indicator;
	this.speed = speed;
	this.duration = duration<100?duration*1000:duration;
	this.transition = new Transition(this.type,(this.type=='fade'?'linear':'quadratic'),this.slideCompleted);
	this.transition.owner = this;
	this.slides = [];
	this.ready = false;
	this.interval = null;
	this.currentindex = 0;
	this.sliding = false;
	this.indicatorcontainer = document.getElementById('CMSlideShow_'+this.prefix+'_'+this.indicator+'container');
}

CMSlideShow.prototype.addSlide = function(id,imagepath,url,title){
	var slide = new CMSlideShowSlide(id,imagepath,url,title,this);
	this.slides.push(slide);
}

CMSlideShow.prototype.start = function(){
	this.ready = true;
	this.interval = setInterval('CMSlideShow_'+this.prefix+'.slide();',this.duration);
}

CMSlideShow.prototype.slide = function(){
	if(!this.sliding){
		var slide = this.slides[this.currentindex];
		var newindex = this.currentindex + 1;
		if(newindex >= this.slides.length) newindex = 0;
		if(newindex == this.currentindex) return;
		var newslide = this.slides[newindex];
		if(newslide.waiting) clearTimeout(newslide.waiting);
		if(!newslide.isReady()) newslide.waiting = setTimeout('CMSlideShow_'+this.prefix+'.slide();',50);
		else{
			newslide.waiting = null;
			this.sliding = true;
			if(this.indicatorcontainer) this.indicatorcontainer.className = 'CMSlideShow_'+this.indicator+'container_trans';
			this.transition.run(slide.imageobj,newslide.imageobj,this.speed);
			this.currentindex = newindex;
		}
	}
}

CMSlideShow.prototype.slideCompleted = function(transition){
	var slider = transition.owner;
	if(slider.indicatorcontainer) slider.indicatorcontainer.className = 'CMSlideShow_'+slider.indicator+'container';
	for(var i=0;i<slider.slides.length;i++){
		var obj = document.getElementById('CMSlideShow_'+slider.prefix+'_'+slider.indicator+'_'+i);
		if(obj){
			if(i==slider.currentindex) obj.className = 'CMSlideShow_'+slider.indicator+'selected';
			else obj.className = 'CMSlideShow_'+slider.indicator;
		}
	}
	slider.sliding = false;
}

CMSlideShow.prototype.click = function(index){
	if(!this.sliding){
		clearInterval(this.interval);
		var slide = this.slides[this.currentindex];
		if(index == this.currentindex) return;
		var newslide = this.slides[index];
		if(newslide.waiting) clearTimeout(newslide.waiting);
		if(!newslide.isReady()) newslide.waiting = setTimeout('CMSlideShow_'+this.prefix+'.click('+index+');',50);
		else{
			newslide.waiting = null;
			this.sliding = true;
			if(this.indicatorcontainer) this.indicatorcontainer.className = 'CMSlideShow_'+this.indicator+'container_trans';
			this.transition.run(slide.imageobj,newslide.imageobj,this.speed);
			this.currentindex = index;
		}
	}
}

function CMSlideShowSlide(id,imagepath,url,title,owner){
	this.id = id;
	this.imagepath = imagepath;
	this.url = url;
	this.title = title;
	this.owner = owner;
	this.imageobj = document.getElementById('CMSlideShowSlide_'+id);
	this.thumbobj = document.getElementById('CMSlideShowThumb_'+id);
	if(this.imageobj) this.imageobj.setAttribute('ready',true);
	else{	
		this.imageobj = document.createElement('a');
		this.imageobj.href = this.url;
		this.imageobj.title = this.title;
		this.imageobj.id = 'CMSlideShowSlide_'+this.id;
		var img = new Image();
		this.imageobj.appendChild(img);
		img.onload = function(){ 
			this.parentNode.setAttribute('ready',true); 
		}
		img.src = this.imagepath;
	}
	this.waiting = null;
}

CMSlideShowSlide.prototype.isReady = function(){
	if(!this.imageobj) return false;
	var ready = this.imageobj.getAttribute('ready');
	return ready!=null;
}