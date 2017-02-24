function Scroller(prefix,classprefix){
	// set the prefix
	this.prefix = prefix;
	this.classprefix = classprefix?classprefix:'scroller';
	this.classnames  = {
		'before_clicked' : this.classprefix+'_beforeclicked',
		'after_clicked' : this.classprefix+'_afterclicked',
		'before_disabled' : this.classprefix+'_beforedisabled',
		'after_disabled' : this.classprefix+'_afterdisabled'
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
	
	this.windowsize = this.window.offsetWidth;
	if(this.windowsize==0){
		// In IE7, the sizing won't be calculated until the page has finished loading, so rebuild this object in the onload event.
		eval('Event.add(window,"load",function(){window.scroller_'+this.prefix+' = new Scroller("'+this.prefix+'");})');
		return;
	}
	
	this.obj = "ScrollerInstance_" + (++ Scroller.instance);
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
	
	this.currentStepArray = [];
}
Scroller.instance = 0;

Scroller.prototype.step = function(direction){
	if(!this.stepping){
		this.stepping = true;
		this.stepdirection = direction;
		
		var stepdistanceleft = 0;
		var panelindex = this.currentpanel;
		var panel = this.panelArray[this.currentpanel];
		for(var i=0;i<this.shownpanels;i++){
			stepdistanceleft += this.panelArray[panelindex].size;
			panelindex = this._getNextPanelIndex(panelindex);
		}
		var totaldistance = stepdistanceleft;
		while(stepdistanceleft){
			var inc = Math.floor(Math.min(stepdistanceleft,Math.max(stepdistanceleft/2,2)));
			stepdistanceleft -= inc;
			this.currentStepArray.push(((totaldistance-stepdistanceleft)*this.stepdirection) - panel.position - this.originalbeltoffset);
		}
		
		if(direction < 0){
			this.afterbutton.className += ' ' + this.classnames.after_clicked;
		}else{
			this.beforebutton.className += ' ' + this.classnames.before_clicked;
		}
		this.interval = setInterval(this.obj + '._stepincrement()',this.speed);
	}
}

Scroller.prototype._stepincrement = function(){
	if(this.currentStepArray.length){
		var left = this.currentStepArray.shift();
		this.belt.style.left = left + 'px';
	}else{
		clearInterval (this.interval);
		this.interval = null;
		this.currentpanel -= (this.stepdirection * this.shownpanels);
		//this.belt.style.left = -this.panelArray[this.currentpanel].position + 'px';
		if(this.stepdirection < 0){
			this.afterbutton.className = this.afterbutton.className.replace(this.classnames.after_clicked,'');
		}else{
			this.beforebutton.className = this.beforebutton.className.replace(this.classnames.before_clicked,'');
		}
		if(this.currentpanel <= 0){
			this.beforebutton.className += ' ' + this.classnames.before_disabled;
			eval('this.beforebutton.onclick = null');
		}else{
			this.beforebutton.className = this.classprefix+'_beforebutton';
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

Scroller.prototype._measurePanels = function(){
	var totalsize = 0;
	for(i=0;i<this.belt.childNodes.length;i++){
		var panel = this.belt.childNodes[i];
		if(panel.id && panel.id.substring(0,this.prefix.length+6) == this.prefix + '_panel'){
			var sliderpanel = new ScrollerPanel(this,panel,totalsize);
			this.panelArray[this.panelArray.length] = sliderpanel;
			totalsize += sliderpanel.size;
			if(totalsize <= this.windowsize) this.shownpanels++;
		}
	}
	return totalsize;
}

Scroller.prototype._getNextPanelIndex = function(index){
	return (index == this.panelArray.length-1)?0:index+1;
}

Scroller.prototype._getPreviousPanelIndex = function(index){
	return (index == 0)?this.panelArray.length-1:index-1;
}

function ScrollerPanel(owner,obj,position){
	this.owner = owner;
	this.obj = obj;
	this.index = obj.id.substring(owner.prefix.length+7);
	this.size = obj.offsetWidth;
	this.position = position;
}