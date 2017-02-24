/*
Usage: 
<script type="text/javascript">
    function startCallback() {
        // make something useful before submit (onStart)
        return true;
    }
    function completeCallback(response) {
        // make something useful after (onComplete)
        document.getElementById('nr').innerHTML = parseInt(document.getElementById('nr').innerHTML) + 1;
        document.getElementById('r').innerHTML = response;
    }
</script>
<form action="index.php" method="post" onsubmit="return AIM.submit(this, {'onStart' : startCallback, 'onComplete' : completeCallback})">
    <div><label>Name:</label> <input type="text" name="form[name]" /></div>
    <div><label>File:</label> <input type="file" name="form[file]" /></div>
    <div><input type="submit" value="SUBMIT" /></div>
</form>
*/


/**
*
*  AJAX IFRAME METHOD (AIM)
*  http://www.webtoolkit.info/
*
**/

AIM = {

	frame : function(c) {

		var n = 'f' + Math.floor(Math.random() * 99999);
		var d = top.document.createElement('DIV');
		d.innerHTML = '<iframe style="display:none" src="about:blank" id="'+n+'" name="'+n+'" onload="AIM.loaded(\''+n+'\')"></iframe>';
		top.document.body.appendChild(d);

		var i = top.document.getElementById(n);
		if (c && typeof(c.onComplete) == 'function') {
			i.onComplete = c.onComplete;
		}else if(c && typeof(c.onComplete) == 'string'){
			i.onComplete = c.onComplete;
		}

		return n;
	},

	form : function(f, name) {
		f.setAttribute('target', name);
	},

	submit : function(f, c) {
		AIM.form(f, AIM.frame(c));
		if (c && typeof(c.onStart) == 'function') {
			return c.onStart();
		} else {
			return true;
		}
	},

	loaded : function(id) {
		var i = top.document.getElementById(id);
		if (i.contentDocument) {
			var d = i.contentDocument;
		} else if (i.contentWindow) {
			var d = i.contentWindow.document;
		} else {
			var d = window.frames[id].document;
		}
		if (d.location.href == "about:blank") {
			return;
		}
		if (typeof(i.onComplete) == 'function') {
			i.onComplete(d.body.innerHTML);
		}else if(typeof(i.onComplete) == 'string'){
			eval(i.onComplete + '(\'' + escape(d.body.innerHTML) + '\')');
		}
	}

}