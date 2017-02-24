var STEPDURATION = 30;

function Transition(type,method,callback,stepcallback){
	this.type = type.toLowerCase();
	this.method = method.toLowerCase();
	this.callback = callback;
	this.stepcallback = stepcallback;
	this.id = Math.round(Math.random() * 100000000);
	eval('window.transit'+this.id+' = this;');
	this.running = false;
	this.interval = null;
}

Transition.prototype.run = function(source,target,duration){
	if(this.running) return false;
	try{
		this.source = source instanceof String?document.getElementById(source):source;
		this.target = target instanceof String?document.getElementById(target):target;
	}catch(e){}
	if(!this.source || !this.target) return false;
	this.duration = duration<100?duration*1000:duration;
	this.steps = Math.floor(this.duration/STEPDURATION);
	if(this.type=='fade' && this.steps > 100) this.steps = 100;
	this.points = [];
	this.currentstep = 0;
	this.stepduration = Math.round(this.duration/this.steps);
	
	this.running = true;
	
	for(var i=0;i<this.steps;i++){
		var ratio = (i / this.steps) ;
		switch(this.method){
			case 'quadratic':
				ratio = (2*ratio) - ((Math.pow(ratio,2)));
				break;
			case 'bezier':
				ratio = (2*(1-ratio)*ratio) + (Math.pow(ratio,2));
				break;
		}
		var step;
		switch(this.type){
			case 'slidetop':
				step = Math.round(this.source.offsetHeight * ratio)-this.source.offsetHeight;
				break;
			case 'slideright':
				step = -Math.round(this.source.offsetWidth * ratio);
				break;
			case 'slidebottom':
				step = -Math.round(this.source.offsetHeight * ratio);
				break;
			case 'slideleft':
				step = Math.round(this.source.offsetWidth * ratio)-this.source.offsetWidth;
				break;
			default:
				step = Math.round(100*ratio);
				break;
		}
		this.points[i] = step;
	}
	
	this.originalstyle = {'position':this.source.style.position,'top':this.source.style.top,'left':this.source.style.left,'bottom':this.source.style.bottom,'right':this.source.style.right}
	eval('this.prepare'+this.type+'();');
	this.interval = setInterval('window.transit'+this.id+'.step()',this.stepduration);
}

Transition.prototype.preparefade = function(){
	this.container = document.createElement('div');
	this.container.style.position = 'relative';
	this.container.style.width = this.source.offsetWidth+'px';
	this.container.style.height = this.source.offsetHeight+'px';
	this.container.style.overflow = 'hidden';
	this.source.parentNode.insertBefore(this.container,this.source);
	this.source.style.position = 'absolute';
	this.source.style.top = '0px';
	this.source.style.left = '0px';
	this.container.appendChild(this.source);
	this.target.style.position = 'absolute';
	this.target.style.top = '0px';
	this.target.style.left = '0px';
	setOpacity(0,this.target);
	this.container.appendChild(this.target);
}

Transition.prototype.prepareslidetop = function(){
	var parent = this.source.parentNode;
	var height = this.source.offsetHeight;
	var width = this.source.offsetWidth;
	this.container = document.createElement('div');
	this.container.style.position = 'relative';
	this.container.style.width = width+'px';
	this.container.style.height = height+'px';
	this.container.style.overflow = 'hidden';
	this.scroller = document.createElement('div');
	this.scroller.style.width = width+'px';
	this.scroller.style.height = (2*height)+'px';
	this.scroller.style.position = 'absolute';
	this.scroller.style.top = -height+'px';
	this.scroller.style.left = '0px';
	this.target.style.position = 'absolute';
	this.target.style.top = '0px';
	this.target.style.left = '0px';
	this.clone = this.source.cloneNode(true);
	this.clone.style.position = 'absolute';
	this.clone.style.top = height+'px';
	this.clone.style.left = '0px';
	this.container.appendChild(this.scroller);
	this.scroller.appendChild(this.target);
	this.scroller.appendChild(this.clone);
	parent.insertBefore(this.container,this.source);
	parent.removeChild(this.source);
}

Transition.prototype.prepareslideright = function(){
	var parent = this.source.parentNode;
	var width = this.source.offsetWidth;
	var height = this.source.offsetHeight;
	this.container = document.createElement('div');
	this.container.style.position = 'relative';
	this.container.style.width = width+'px';
	this.container.style.height = height+'px';
	this.container.style.overflow = 'hidden';
	this.scroller = document.createElement('div');
	this.scroller.style.width = (2*width)+'px';
	this.scroller.style.height = height+'px';
	this.scroller.style.position = 'absolute';
	this.scroller.style.top = '0px';
	this.scroller.style.left = '0px';
	this.clone = this.source.cloneNode(true);
	this.clone.style.position = 'absolute';
	this.clone.style.top = '0px';
	this.clone.style.left = '0px';
	this.target.style.position = 'absolute';
	this.target.style.top = '0px';
	this.target.style.left = width+'px';
	this.container.appendChild(this.scroller);
	this.scroller.appendChild(this.clone);
	this.scroller.appendChild(this.target);
	parent.insertBefore(this.container,this.source);
	parent.removeChild(this.source);
}

Transition.prototype.prepareslidebottom = function(){
	var parent = this.source.parentNode;
	var height = this.source.offsetHeight;
	var width = this.source.offsetWidth;
	this.container = document.createElement('div');
	this.container.style.position = 'relative';
	this.container.style.width = width+'px';
	this.container.style.height = height+'px';
	this.container.style.overflow = 'hidden';
	this.scroller = document.createElement('div');
	this.scroller.style.width = width+'px';
	this.scroller.style.height = (2*height)+'px';
	this.scroller.style.position = 'absolute';
	this.scroller.style.top = '0px';
	this.scroller.style.left = '0px';
	this.clone = this.source.cloneNode(true);
	this.clone.style.position = 'absolute';
	this.clone.style.top = '0px';
	this.clone.style.left = '0px';
	this.target.style.position = 'absolute';
	this.target.style.top = height+'px';
	this.target.style.left = '0px';
	this.container.appendChild(this.scroller);
	this.scroller.appendChild(this.clone);
	this.scroller.appendChild(this.target);
	parent.insertBefore(this.container,this.source);
	parent.removeChild(this.source);
}

Transition.prototype.prepareslideleft = function(){
	var parent = this.source.parentNode;
	var width = this.source.offsetWidth;
	var height = this.source.offsetHeight;
	this.container = document.createElement('div');
	this.container.style.position = 'relative';
	this.container.style.width = width+'px';
	this.container.style.height = height+'px';
	this.container.style.overflow = 'hidden';
	this.scroller = document.createElement('div');
	this.scroller.style.width = (2*width)+'px';
	this.scroller.style.height = height+'px';
	this.scroller.style.position = 'absolute';
	this.scroller.style.top = '0px';
	this.scroller.style.left = -width+'px';
	this.target.style.position = 'absolute';
	this.target.style.top = '0px';
	this.target.style.left = '0px';
	this.clone = this.source.cloneNode(true);
	this.clone.style.position = 'absolute';
	this.clone.style.top = '0px';
	this.clone.style.left = width+'px';
	this.container.appendChild(this.scroller);
	this.scroller.appendChild(this.target);
	this.scroller.appendChild(this.clone);
	parent.insertBefore(this.container,this.source);
	parent.removeChild(this.source);
}

Transition.prototype.step = function(){
	var point = this.points[this.currentstep];
	
	switch(this.type){
		case 'slidetop':
			this.scroller.style.top = point+'px';
			break;
		case 'slideright':
			this.scroller.style.left = point+'px';
			break;
		case 'slidebottom':
			this.scroller.style.top = point+'px';
			break;
		case 'slideleft':
			this.scroller.style.left = point+'px';
			break;
		default:
			setOpacity(point,this.target);
			break;
	}
	this.call(this.stepcallback);
	this.currentstep++;
	
	if(this.currentstep >= this.points.length){
		this.finish();
	}
}

Transition.prototype.finish = function(){
	clearInterval(this.interval);
	this.target.style.position = this.originalstyle.position;
	this.target.style.left = this.originalstyle.left;
	this.target.style.top = this.originalstyle.top;
	this.target.style.right = this.originalstyle.right;
	this.target.style.bottom = this.originalstyle.bottom;
	if(this.type=='fade') setOpacity(100,this.target);
	this.container.parentNode.insertBefore(this.target,this.container);
	this.container.parentNode.removeChild(this.container);
	this.running = false;
	this.call(this.callback);
}

Transition.prototype.call = function(callback){
	if(callback){
		try{
			if(callback instanceof String){
				eval(callback+'(this);');
			}else{
				callback(this);
			}
		}catch(e){}
	}
}