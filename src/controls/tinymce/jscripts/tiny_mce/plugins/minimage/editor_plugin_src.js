(function() {
	tinymce.PluginManager.requireLangPack('minimage');

	tinymce.create('tinymce.plugins.MinImagePlugin', {
		init : function(ed, url) {
			ed.addCommand('mceMinImage', function() {
				ed.windowManager.open({
					file : url + '/minimage.htm',
					width : 420 + parseInt(ed.getLang('minimage.delta_width', 0)),
					height : 300 + parseInt(ed.getLang('minimage.delta_height', 0)),
					inline : 1
				}, {
					plugin_url : url
				});
			});

			ed.addButton('minimage', {
				title : 'minimage.desc',
				cmd : 'mceMinImage',
				image : url + '/img/minimage.gif'
			});
			ed.onNodeChange.add(function(ed, cm, n) {
				var p = ed.dom.getParent(n, 'div,td,th,caption');
				cm.setActive('minimage', n.nodeName == 'IMG' || n.nodeName == 'DIV' || n.nodeName=="TABLE" || !!p);
			});
		},
		createControl : function(n, cm) {
			return null;
		},
		getInfo : function() {
			return {
				longname : 'Minimum Image Plugin',
				author : 'Vincent',
				authorurl : 'http://blacksquare.co.za',
				infourl : 'http://blacksquare.co.za',
				version : "1.0"
			};
		}
	});
	tinymce.PluginManager.add('minimage', tinymce.plugins.MinImagePlugin);
})();