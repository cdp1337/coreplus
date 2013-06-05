Core.TinyMCE = {
	FileBrowserCallback: function(field_name, url, type, win) {
		/*
		 self.getEl('inp').id,
		 self.getEl('inp').value,
		 settings.filetype,
		 window
		 */
		var url, title;

		// There are two types of browsers here, file and image.
		// each has a few specific settings.
		if(type == 'file'){
			url = Core.ROOT_URL + 'tinymcenavigator?ajax=1';
			title = 'File Browser';
		}
		else if(type == 'image'){
			url =  Core.ROOT_URL + 'tinymcenavigator/image?ajax=1';
			title = 'Image Browser';
		}
		else{
			return false;
		}

		// Whatever....... :/
		Core.TinyMCE.helper.targetinput = field_name;
		Core.TinyMCE.helper.window = win;

		//console.log(tinymce.PluginManager.get('image'), tinymce.PluginManager.get('image').recalcSize());

		tinyMCE.activeEditor.windowManager.open({
			file : url,
			title : title,
			width : 800,
			height : 600,
			resizable : "yes",
			inline : "yes",  // This parameter only has an effect if you use the inlinepopups plugin!
			close_previous : "no"
		}, {
			window : win,
			input : field_name
		});


		return false;
	},

	helper: {
	     targetinput: null,
	     window: null,
	     image: null
	},

	__last: null
};
