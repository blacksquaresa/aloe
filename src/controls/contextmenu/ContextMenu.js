/**
*  Original Context Menu: http://www.webtoolkit.info/
**/

var ContextMenu = {

	// private attributes
	_classes : new Array,
	_menus : new Array,
	_attachedElement : null,
	_menuElement : null,
	_clickEvent : null,
	_preventDefault : true,
	_preventForms : true,
	_showCallback : new Array,
	_hideCallback : new Array,


	// public method. Sets up whole context menu stuff..
	setup : function (conf) {

		if ( document.all && document.getElementById && !window.opera ) {
			ContextMenu.IE = true;
		}

		if ( !document.all && document.getElementById && !window.opera ) {
			ContextMenu.FF = true;
		}

		if ( document.all && document.getElementById && window.opera ) {
			ContextMenu.OP = true;
		}

		if ( ContextMenu.IE || ContextMenu.FF ) {
			document.oncontextmenu = ContextMenu._show;
			document.onclick = ContextMenu._hide;

			if (conf && typeof(conf.preventDefault) != "undefined") {
				ContextMenu._preventDefault = conf.preventDefault;
			}

			if (conf && typeof(conf.preventForms) != "undefined") {
				ContextMenu._preventForms = conf.preventForms;
			}

		}

	},


	// public method. Attaches context menus to specific class names
	attachclass : function (classNames, menuId /*, showcallback, hidecallback*/) {

		if (typeof(classNames) == "string") {
			ContextMenu._classes[classNames] = menuId;
		}

		if (typeof(classNames) == "object") {
			for (x = 0; x < classNames.length; x++) {
				ContextMenu._classes[classNames[x]] = menuId;
			}
		}
		
		if(arguments.length >=2){
			ContextMenu._showCallback[menuId] = arguments[2];
		}
		if(arguments.length >=3){
			ContextMenu._hideCallback[menuId] = arguments[3];
		}

	},


	// public method. Attaches context menus to specific class names
	attachid : function (idNames, menuId /*, showcallback, hidecallback*/) {

		if (typeof(idNames) == "string") {
			ContextMenu._menus[idNames] = menuId;
		}

		if (typeof(idNames) == "object") {
			for (x = 0; x < idNames.length; x++) {
				ContextMenu._menus[idNames[x]] = menuId;
			}
		}
		if(arguments.length >=2){
			ContextMenu._showCallback[menuId] = arguments[2];
		}
		if(arguments.length >=3){
			ContextMenu._hideCallback[menuId] = arguments[3];
		}

	},


	// private method. Get which context menu to show
	_getMenuElementId : function (e) {

		if (ContextMenu.IE) {
			ContextMenu._attachedElement = event.srcElement;
		} else {
			ContextMenu._attachedElement = e.target;
		}

		while(ContextMenu._attachedElement != null) {
			if (ContextMenu._menus[ContextMenu._attachedElement.id]) {
				return ContextMenu._menus[ContextMenu._attachedElement.id];
			}
		
			var className = ContextMenu._attachedElement.className;
			if (typeof(className) != "undefined") {
				className = className.replace(/^\s+/g, "").replace(/\s+$/g, "")
				var classArray = className.split(/[ ]+/g);

				for (i = 0; i < classArray.length; i++) {
					if (ContextMenu._classes[classArray[i]]) {
						return ContextMenu._classes[classArray[i]];
					}
				}
			}

			if (ContextMenu.IE) {
				ContextMenu._attachedElement = ContextMenu._attachedElement.parentElement;
			} else {
				ContextMenu._attachedElement = ContextMenu._attachedElement.parentNode;
			}
		}

		return null;

	},


	// private method. Shows context menu
	_getReturnValue : function (e) {

		var returnValue = true;
		var evt = ContextMenu.IE ? window.event : e;

		if (evt.button != 1) {
			if (evt.target) {
				var el = evt.target;
			} else if (evt.srcElement) {
				var el = evt.srcElement;
			}

			var tname = el.tagName.toLowerCase();

			if ((tname == "input" || tname == "textarea")) {
				if (!ContextMenu._preventForms) {
					returnValue = true;
				} else {
					returnValue = false;
				}
			} else {
				if (!ContextMenu._preventDefault) {
					returnValue = true;
				} else {
					returnValue = false;
				}
			}
		}

		return returnValue;

	},


	// private method. Shows context menu
	_show : function (e) {
		ContextMenu._hide();
		var menuElementId = ContextMenu._getMenuElementId(e);

		if (menuElementId) {
			ContextMenu.ClickEvent = e;
		
			var m = ContextMenu._getMousePosition(e);
			var s = ContextMenu._getScrollPosition(e);
			
			ContextMenu._menuElement = document.getElementById(menuElementId);
			// ensure that the context menu is always a child of the body, and not in a div positioned relative.
			if(ContextMenu._menuElement.parentNode != document.body){
				document.body.appendChild(ContextMenu._menuElement);
			}
			ContextMenu._menuElement.style.left = m.x + s.x + 'px';
			ContextMenu._menuElement.style.top = m.y + s.y + 'px';
			
			if(ContextMenu._showCallback){
				eval(ContextMenu._showCallback[menuElementId]);
			}
			
			ContextMenu._menuElement.style.display = 'block';
			return false;
		}

		return ContextMenu._getReturnValue(e);

	},


	// private method. Hides context menu
	_hide : function () {

		if (ContextMenu._menuElement) {
			if(ContextMenu._hideCallback){
				eval(ContextMenu._hideCallback[ContextMenu._menuElement.id]);
			}
			ContextMenu._menuElement.style.display = 'none';
		}

	},


	// private method. Returns mouse position
	_getMousePosition : function (e) {

		e = e ? e : window.event;
		var position = {
			'x' : e.clientX,
			'y' : e.clientY
		}

		return position;

	},


	// private method. Get document scroll position
	_getScrollPosition : function () {

		var x = 0;
		var y = 0;

		if( typeof( window.pageYOffset ) == 'number' ) {
			x = window.pageXOffset;
			y = window.pageYOffset;
		} else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
			x = document.documentElement.scrollLeft;
			y = document.documentElement.scrollTop;
		} else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
			x = document.body.scrollLeft;
			y = document.body.scrollTop;
		}

		var position = {
			'x' : x,
			'y' : y
		}

		return position;

	},
	
	// public static method. Disable one of the menu items
	disableMenuItem : function(id,disabled){
		var item = document.getElementById(id);
		if(item){
			var links = item.getElementsByTagName('A');
			for(i=0;i<links.length;i++){
				var link = links[i];
				if(disabled){
					var href = link.getAttribute("href");
					if(href && href != "" && href != null){
					   link.setAttribute('href_bak', href);
					}
					link.removeAttribute('href');
					link.className="ctm_link_disabled";
				}else{
					var href = link.attributes['href_bak'];
					if(href && href.nodeValue){
						link.setAttribute('href', href.nodeValue);
						link.className="ctm_link";
					}
				}
			}
		}
	},
	
	// public static method. Rename one of the menu items
	renameMenuItem : function(id,newname){
		var item = document.getElementById(id);
		if(item){
			var intext = item.innerText?item.innerText:item.textContent;
			var reg = new RegExp(intext,'g');
			item.innerHTML = item.innerHTML.replace(reg,newname);
		}
	}

}