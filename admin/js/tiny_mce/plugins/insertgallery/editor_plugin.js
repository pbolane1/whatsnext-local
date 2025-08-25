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
	tinymce.create('tinymce.plugins.InsertGalleryPlugin', {
		init : function(ed, url) {
			// Register commands
			ed.addCommand('mceInsertGallery', function() {
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
			ed.addButton('insertgallery', {
				title : 'Insert Gallery',
				cmd : 'mceInsertGallery',
				image : url + '/images/icon.jpg'
			});

//			ed.onNodeChange.add(function(ed, cm, n) {
//				cm.setActive('InsertGallery', n.nodeName == 'HR');
//			});

			ed.onClick.add(function(ed, e) {
				e = e.target;

//				if (e.nodeName === 'HR')
//					ed.selection.select(e);
			});		
			
		},

		getInfo : function() {
			return {
				longname : 'InsertGallery plugin',
				author : 'PoCo Technolgies LLC',
				authorurl : 'http://www.pocotechnology.com',
				infourl : '#',
				version : tinyMCE.majorVersion + "." + tinyMCE.minorVersion
			};
		},

	});

	// Register plugin
	tinymce.PluginManager.add('insertgallery', tinymce.plugins.InsertGalleryPlugin);
})();