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
	tinymce.create('tinymce.plugins.AdvancedLinkPlugin', {
		init : function(ed, url) {
			this.editor = ed;

			// Register commands
			ed.addCommand('mceAdvLink', function() {
				var se = ed.selection;

				// No selection and not in link
				if (se.isCollapsed() && !ed.dom.getParent(se.getNode(), 'A'))
					return;

				// Since I'll be using a controller, I'll override the url.
				// file : url + '/link.htm',
				// to
				// file: Core.ROOT_URL + 'tinymce/link',

				ed.windowManager.open({
					file : Core.ROOT_URL + 'tinymce/link',
					width : 480 + parseInt(ed.getLang('advlink.delta_width', 0)),
					height : 400 + parseInt(ed.getLang('advlink.delta_height', 0)),
					inline : 1
				}, {
					plugin_url : url
				});
			});

			// Register buttons
			ed.addButton('link', {
				title : 'advlink.link_desc',
				cmd : 'mceAdvLink'
			});

			ed.addShortcut('ctrl+k', 'advlink.advlink_desc', 'mceAdvLink');

			ed.onNodeChange.add(function(ed, cm, n, co) {
				cm.setDisabled('link', co && n.nodeName != 'A');
				cm.setActive('link', n.nodeName == 'A' && !n.name);
			});
		},

		getInfo : function() {
			return {
				longname : 'Advanced link (Core Plus Deviation)',
				author : 'eVAL, Ltd.',
				authorurl : 'http://corepl.us',
				infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/advlink',
				version : tinymce.majorVersion + "." + tinymce.minorVersion + "~core1"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('advlinkcoreplus', tinymce.plugins.AdvancedLinkPlugin);
})();