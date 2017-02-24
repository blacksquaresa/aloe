function CMPhotoGallery(id){
	this.id = id;
	this.slides = [];
	this.ready = false;
	this.interval = null;
	this.currentindex = 0;
	this.working = false;
	this.frame = document.getElementById('CMPhotoGallery_frame_'+this.id);
	this.name = 'CMPhotoGallery'+this.id;
	eval(this.name+' = this');
}

CMPhotoGallery.prototype.addSlide = function(id,imagepath,caption){
	var slide = new CMPhotoGallerySlide(id,imagepath,caption,this);
	this.slides.push(slide);
}

CMPhotoGallery.prototype.start = function(){
	if(this.slides.length){
		var slide = this.slides[0];
		slide.show();
	}
}

CMPhotoGallery.prototype.click = function(index){
	if(!this.working){
		var slide = this.slides[this.currentindex];
		if(index == this.currentindex) return;
		this.working = true;
		slide.hide();
		var newslide = this.slides[index];
		newslide.show();
	}
}

function CMPhotoGallerySlide(id,imagepath,caption,owner){
	this.id = id;
	this.imagepath = imagepath;
	this.caption = caption;
	this.owner = owner;
	this.loaded = false;
	this.currentopacity = 0;
	
	this.container = document.createElement('div');
	this.container.className = 'CMPhotoGallery_imagecontainer';
	setOpacity(0,this.container);
	this.owner.frame.appendChild(this.container);
	
	this.image = document.createElement('img');
	this.image.slide = this;
	this.container.appendChild(this.image);
	
	if(this.caption){
		this.captioncontainer = document.createElement('div');
		this.captioncontainer.className = 'CMPhotoGallery_caption';
		this.captioncontainer.innerHTML = '<div class="CMPhotoGallery_captionback"></div><div class="CMPhotoGallery_captiontext">' +this.caption+ '</div>';
		this.container.appendChild(this.captioncontainer);
	}
}

CMPhotoGallerySlide.prototype.hide = function(){
	if(this.currentopacity > 0){
		this.currentopacity -= 2;
		setOpacity(this.currentopacity,this.container);
		setTimeout(this.owner.name+'.slides['+this.id+'].hide();',20);
	}else{
		this.container.style.visibility = 'hidden';
	}
}

CMPhotoGallerySlide.prototype.show = function(){
	if(this.loaded){
		this.container.style.visibility = 'visible';
		if(this.currentopacity < 100){
			this.currentopacity += 2;
			setOpacity(this.currentopacity,this.container);
			setTimeout(this.owner.name+'.slides['+this.id+'].show();',20);
		}else{
			this.owner.working = false;
			this.owner.currentindex = this.id;
		}
	}else{
		this.image.onload = function(){
			this.slide.image.onload = null;
			this.slide.loaded = true;
			this.slide.show();
		}
		this.image.src = this.imagepath;
	}
}