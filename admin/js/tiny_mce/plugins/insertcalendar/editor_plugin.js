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
	tinymce.create('tinymce.plugins.InsertCalendarPlugin', {
		init : function(ed, url) {
			// Register commands
			ed.addCommand('mceInsertCalendar', function() {
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
			ed.addButton('insertcalendar', {
				title : 'Insert Calendar',
				cmd : 'mceInsertCalendar',
				image : url + '/images/icon.png'
			});

//			ed.onNodeChange.add(function(ed, cm, n) {
//				cm.setActive('InsertCalendar', n.nodeName == 'HR');
//			});

			ed.onClick.add(function(ed, e) {
				e = e.target;

//				if (e.nodeName === 'HR')
//					ed.selection.select(e);
			});		
			
		},

		getInfo : function() {
			return {
				longname : 'InsertCalendar plugin',
				author : 'PoCo Technolgies LLC',
				authorurl : 'http://www.pocotechnology.com',
				infourl : '#',
				version : tinyMCE.majorVersion + "." + tinyMCE.minorVersion
			};
		},

	});

	// Register plugin
	tinymce.PluginManager.add('insertcalendar', tinymce.plugins.InsertCalendarPlugin);
})();