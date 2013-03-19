Core.TinyMCE = {
	FileBrowserCallback: function(field_name, url, type, win) {
		var url, title;

		// Do custom browser logic
		//win.document.forms[0].elements[field_name].value = 'my browser value';
		//console.log(field_name, url, type, win);

		// There are two types of browsers here, file and image.
		// each has a few specific settings.
		if(type == 'file'){
			url = Core.ROOT_URL + 'tinymce/file';
			title = 'File Browser';
		}
		else if(type == 'image'){
			url =  Core.ROOT_URL + 'tinymce/image';
			title = 'Image Browser';
		}
		else{
			return false;
		}

		tinyMCE.activeEditor.windowManager.open({
			file : url,
			title : title,
			width : 760,
			height : 600,
			resizable : "yes",
			inline : "yes",  // This parameter only has an effect if you use the inlinepopups plugin!
			close_previous : "no"
		}, {
			window : win,
			input : field_name
		});

		return false;
	}
};
