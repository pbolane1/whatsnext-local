/**
 * editor_plugin_src.js
 *
 * Copyright 2009, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://tinymce.moxiecode.com/license
 * Contributing: http://tinymce.moxiecode.com/contributing
 */

(function() {
	tinymce.create('tinymce.plugins.InsertListPlugin', {
		init : function(ed, url) {
			// Register commands
			ed.addCommand('mceInsertList', function() {
				ed.windowManager.open({
					file : url + '/insert.php',
					width : 300,
					height : 200,
					inline : 1
				}, {
					plugin_url : url
				});
			});

			// Register buttons
			ed.addButton('insertlist', {
				title : 'Insert Detail List',
				cmd : 'mceInsertList',
				image : url + '/images/icon.png'
			});

//			ed.onNodeChange.add(function(ed, cm, n) {
//				cm.setActive('insertlist', n.nodeName == 'HR');
//			});

			ed.onClick.add(function(ed, e) {
				e = e.target;

//				if (e.nodeName === 'HR')
//					ed.selection.select(e);
			});		
			
		},

		getInfo : function() {
			return {
				longname : 'insertlist plugin',
				author : 'PoCo Technolgies LLC',
				authorurl : 'http://www.pocotechnology.com',
				infourl : '#',
				version : tinyMCE.majorVersion + "." + tinyMCE.minorVersion
			};
		},

	});

	// Register plugin
	tinymce.PluginManager.add('insertlist', tinymce.plugins.InsertListPlugin);
})();