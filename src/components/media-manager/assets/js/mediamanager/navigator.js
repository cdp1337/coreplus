/**
 * Created with JetBrains PhpStorm.
 * User: powellc
 * Date: 6/4/13
 * Time: 9:53 AM
 * To change this template use File | Settings | File Templates.
 */

Navigator = {

	DOM: {
		$bargraph: null
	},

	Location: null,
	Mode: null,
	UploadTimer: false,
	UploadProgress: 0,
	UploadTotalProgress: 0,
	FakeBarProgress: false,

	Setup: function(){

		Navigator.Location = $('.mediamanagernavigator').attr('location');
		Navigator.Mode = $('.mediamanagernavigator').attr('mode');
		Navigator.FakeBarProgress = false;

		$('.directory-create').click(function(){
			Navigator.Mkdir();
			return false;
		});

		$('.directory-rename').click(function(){
			Navigator.Rename($(this), 'directory');
			return false;
		});

		$('.directory-delete').click(function(){
			Navigator.Delete($(this), 'directory');
			return false;
		});

		$('.file-rename').click(function(){
			Navigator.Rename($(this), 'file');
			return false;
		});

		$('.file-delete').click(function(){
			Navigator.Delete($(this), 'file');
			return false;
		});

		$('.file-select').click(function(){
			Navigator.SelectFile($(this));
			return false;
		});

		$('.mediamanagernavigator').find('.directory a, .file a').click(function(){
			if($(this).attr('href') != '#'){
				Navigator.FakeBarProgress = true;
				Navigator.Bargraph.Set(40);

				// If it's still on this page in a moment...
				// Yes, this is a blatant dirty hack to make some pointless visual appeal,
				// but it at least gives the user something to look at for a couple seconds!
				setTimeout(function(){ if(Navigator.FakeBarProgress) Navigator.Bargraph.Set(50); }, 200);
				setTimeout(function(){ if(Navigator.FakeBarProgress) Navigator.Bargraph.Set(60); }, 400);
				setTimeout(function(){ if(Navigator.FakeBarProgress) Navigator.Bargraph.Set(70); }, 600);
				setTimeout(function(){ if(Navigator.FakeBarProgress) Navigator.Bargraph.Set(80); }, 1000);
				setTimeout(function(){ if(Navigator.FakeBarProgress) Navigator.Bargraph.Set(0); },  4000);
			}
		});

		Navigator.DOM.$bargraph = $('.mediamanagernavigator-addressbar .bargraph-inner');
		Navigator.DOM.$uploader = $('.multifileinput').closest('form');


		// Change some of the default behaviour of the uploader.
		if(Navigator.DOM.$uploader.length > 0){

			Navigator.DOM.$uploader.fileupload({
				dropzone: $('.mediamanagernavigator'),
				done: function (e, data) {
					if(Navigator.UploadProgress >= 100){
						Core.Reload();
						//console.log('RELOAD IT!');
					}
				},
				fail: function(e, data){
					$('.mediamanagernavigator').readonly(false);
					Navigator.Bargraph.Set(0);
					alert(data.errorThrown);
				}
			});

			Navigator.DOM.$uploader.bind('fileuploadadd', function(){
				if(!Navigator.Bargraph.Get()){
					Navigator.Bargraph.Set(5);
					$('.mediamanagernavigator').readonly(true);
				}
			});

			Navigator.DOM.$uploader.bind('fileuploadprogressall', function(e, data){
				Navigator.UploadProgress = parseInt(data.loaded / data.total * 100, 10);
				console.log('progress called', Navigator.UploadProgress);
				Navigator.Bargraph.Set(Navigator.UploadProgress, false);
			});
		}
	},

	Mkdir: function(){
		newname = prompt('Enter the new directory name');
		if(!newname) return;

		Navigator.Bargraph.Set(35);

		// Post the new name.
		$.ajax({
			url: Core.ROOT_URL + 'mediamanagernavigator/directory/mkdir',
			type: 'post',
			dataType: 'json',
			data: { dir: Navigator.Location, newdir: newname },
			success: function(response){
				if(!response){
					Navigator.Bargraph.Set(0);
					alert('Operation failed, Unknown response');
				}
				else if(response.status){
					Navigator.Bargraph.Set(98);
					Core.Reload();
				}
				else{
					Navigator.Bargraph.Set(0);
					alert(response.message);
				}
			},
			error: function(response){
				Navigator.Bargraph.Set(0);
				alert('Operation failed, Unknown response');
			}
		});
	},

	Rename: function($target, type){
		newname = prompt('Enter the new ' + type + ' name', $target.attr('browsename').replace('/', ''));
		if(!newname) return;

		Navigator.Bargraph.Set(35);

		// Post the new name.
		$.ajax({
			url: Core.ROOT_URL + 'mediamanagernavigator/' + type + '/rename',
			type: 'post',
			dataType: 'json',
			data: { dir: Navigator.Location, olddir: $target.attr('browsename'), newdir: newname },
			success: function(response){
				if(!response){
					Navigator.Bargraph.Set(0);
					alert('Operation failed, Unknown response');
				}
				else if(response.status){
					Navigator.Bargraph.Set(98);
					Core.Reload();
				}
				else{
					Navigator.Bargraph.Set(0);
					alert(response.message);
				}
			},
			error: function(response){
				Navigator.Bargraph.Set(0);
				alert('Operation failed, Unknown response');
			}
		});
	},

	Delete: function($target, type){
		if(confirm('Confirm deletion of ' + type + ' ' + $target.attr('browsename') + '?')){
			Navigator.Bargraph.Set(35);

			$.ajax({
				url: Core.ROOT_URL + 'mediamanagernavigator/' + type + '/delete',
				type: 'post',
				dataType: 'json',
				data: { dir: Navigator.Location, olddir: $target.attr('browsename') },
				success: function(response){
					if(!response){
						Navigator.Bargraph.Set(0);
						alert('Operation failed, Unknown response');
					}
					else if(response.status){
						Navigator.Bargraph.Set(98);
						Core.Reload();
					}
					else{
						Navigator.Bargraph.Set(0);
						alert(response.message);
					}
				},
				error: function(response){
					Navigator.Bargraph.Set(0);
					alert('Operation failed, Unknown response');
				}
			});
		}
	},

	SelectFile: function($target){
		// Default action for selecting a file, (clicking on it).
		var href = $target.closest('.file').find('.defaultaction').first().attr('href'),
			parent = window.parent,
			targetinput, url;


		if(parent != window){
			targetinput = parent.Core.TinyMCE.helper.targetinput;
			url = $target.attr('selectname');

			parent.document.getElementById(targetinput).value = url;
			parent.tinymce.activeEditor.windowManager.close();
		}
		else{
			window.open(href, '_BLANK');
		}
	},

	Bargraph: {
		_value: 0,

		Set: function(width, animate){
			if(typeof animate == 'undefined') animate = true;

			if(width > 0){
				if(!Navigator.DOM.$bargraph.is(':visible')){
					Navigator.DOM.$bargraph.css('width', '0%');
					Navigator.DOM.$bargraph.show();
				}

				if(animate){
					Navigator.DOM.$bargraph.animate({width: (width + '%')}, 'fast');
				}
				else{
					Navigator.DOM.$bargraph.css('width', (width + '%'));
				}

				Navigator.Bargraph._value = width;
			}
			else{
				Navigator.DOM.$bargraph.hide();
				Navigator.Bargraph._value = 0;
			}
		},

		Get: function(){
			return Navigator.Bargraph._value;
		}
	},


	__last: null
};