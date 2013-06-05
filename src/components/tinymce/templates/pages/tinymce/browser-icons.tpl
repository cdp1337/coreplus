<style xmlns="http://www.w3.org/1999/html">
	body {
		background: #eee;
	}
</style>


<div class="directory-browser directory-browser-{$size}" location="{$location}">
    <div class="directory-browser-previewarea" style="display:none;"></div>
    <div class="directory-browser-addressbar">
		<span class="bargraph-inner"></span>
        <a href="?"><i class="icon-home"></i></a>
		{foreach $location_tree as $dir}
	        <a href="?dir={$dir.stack}">/{$dir.name}</a>
		{/foreach}
    </div>

	<div class="directory-browser-files">
		{if !(sizeof($directories) || sizeof($files))}
			There are no files or directories here.
			{if $uploadform}
				Drag some files here to upload some!
			{/if}
		{/if}

		{if $uploadform}
			{$uploadform->render()}
		{/if}

		{foreach $directories as $dir}
			<div class="directory" browsename="{$dir.browsename}">
				<table style="margin:0pt auto;"><tr><td style="vertical-align:middle; height:{$sizepx}px;">{img src="assets/images/mimetypes/directory.png" dimensions="`$sizepx`"}</td></tr></table>
				{$dir.name}
				<div class="preview">
					{img src="assets/images/mimetypes/directory-xl.png"}<br/>
					{$dir.name}<br/>
					Contains {$dir.children} {if $dir.children == 1}child{else}children{/if}<br/>
				</div>
				<ul class="contextmenu">
					<li>
						<a href="#directory-open"><i class="icon-folder-open"></i>Open</a>
					</li>
					<li>
						<a href="#directory-rename"><i class="icon-font"></i>Rename</a>
					</li>
					<li>
						<a href="#directory-delete"><i class="icon-trash"></i>Delete</a>
					</li>
				</ul>
			</div>
		{/foreach}

		{foreach $files as $file}
			<div class="file" selectname="{$file.selectname}">
				<table style="margin:0pt auto;"><tr><td style="vertical-align:middle; height:{$sizepx}px;">{img file="`$file.object`" width="`$sizepx`" height="`$sizepx`"}</td></tr></table>
				{$file.name}
				<div class="preview">
					{img file="`$file.object`" width="180" height="240"}<br/>
					{$file.name}<br/>
					Filetype: {$file.object->getExtension()}<br/>
                    Created: {date date="`$file.object->getMTime()`"}<br/>
                    Filesize: {Core::FormatSize("`$file.object->getFilesize()`")}<br/>
				</div>
				<ul class="contextmenu">
					<li>
						<a href="#file-download"><i class="icon-download"></i>Download</a>
					</li>
					<li>
						<a href="#file-rename"><i class="icon-font"></i>Rename</a>
					</li>
					<li>
						<a href="#file-delete"><i class="icon-trash"></i>Delete</a>
					</li>
				</ul>
			</div>
		{/foreach}

        <div class="clear"></div>



		<ul class="contextmenu">
			<li>
				<a href="#mkdir"><i class="icon-folder-close"></i>Create Directory</a>
			</li>
			<!--<li>
				<a href="#rename"><i class="icon-font"></i>Rename</a>
			</li>
			<li>
				<a href="#delete"><i class="icon-trash"></i>Delete</a>
			</li>-->
		</ul>
    </div>

    <div class="clear"></div>

	<div class="directory-browser-tip">
		<strong>Did you know?</strong> {$tip}
	</div>

</div>

{script library="jquery"}{/script}
{script library="jqueryui.readonly"}{/script}
{script library="jqueryui.contextmenu"}{/script}
{*script src="assets/js/tinymce/plugins/compat3x/tiny_mce_popup.js"}{/script*}

<script type="text/javascript">
	$(function(){

		var i, inside,
			$directories = $('.directory'),
			$files = $('.file'),
			$both = $directories.add($files),
			$lastclicked,
			$previewpane = $('.directory-browser-previewarea'),
			$filespane = $('.directory-browser-files'),
			$browser = $('.directory-browser'),
			$uploadform = false,
			$bargraphinner = $browser.find('.bargraph-inner'),
			uploadtimer = false;

		if($('.multifileinput').length > 0){
			$uploadform = $('.multifileinput').closest('form');
        }

		/**
		 * Function to update the preview pane based on what is currently selected.
		 */
		function update_preview(){
			var $selectednow = $both.filter('.selected');

			$selectednow = $both.filter('.selected');
			if($selectednow.length == 0){
				$previewpane.html('').hide();
			}
			else if($selectednow.length == 1){
				$previewpane.html($selectednow.find('.preview').html()).show();
				// Reset the height so I can determine the bigger of the two.
				$previewpane.height('auto');
				$previewpane.height(Math.max($previewpane.height(), $browser.height()));
			}
			else{
				$previewpane.html($selectednow.length + ' files/directories selected').show();
				// Reset the height so I can determine the bigger of the two.
				$browser.height()
				$previewpane.height(Math.max($previewpane.height(), $browser.height()));
			}
		}

		/**
		 * Delete a directory
		 *
		 * @param el DOMElement
		 */
		function delete_directory(el){
			var $el;
			if(typeof el.jquery != 'undefined') $el = el;
			else $el = $(el);

			if(confirm('Confirm deletion of directory ' + $el.attr('browsename') + '?')){
				$bargraphinner.width('35%');

				$.ajax({
					url: Core.ROOT_URL + 'tinymce/directory/delete',
					type: 'post',
					dataType: 'json',
					data: { dir: $browser.attr('location'), olddir:$el.attr('browsename') },
					success: function(response){
						if(!response){
							$bargraphinner.width('0pt');
							alert('Operation failed, Unknown response');
						}
						else if(response.status){
							$bargraphinner.width('100%');
							Core.Reload();
						}
						else{
							$bargraphinner.width('0pt');
							alert(response.message);
						}
					}
				});
			}
		}

		/**
		 * Delete a file
		 *
		 * @param el DOMElement
		 */
		function delete_file(el){
			var $el, filename;

			if(typeof el.jquery != 'undefined') $el = el;
			else $el = $(el);

			filename = $el.attr('selectname').replace(/\\/g,'/').replace( /.*\//, '');

			if(confirm('Confirm deletion of file ' + filename + '?')){
				$bargraphinner.width('35%');

				$.ajax({
					url: Core.ROOT_URL + 'tinymce/file/delete',
					type: 'post',
					dataType: 'json',
					data: { dir: $browser.attr('location'), file: filename },
					success: function(response){
						if(!response){
							$bargraphinner.width('0pt');
							alert('Operation failed, Unknown response');
						}
						else if(response.status){
							$bargraphinner.width('100%');
							Core.Reload();
						}
						else{
							$bargraphinner.width('0pt');
							alert(response.message);
						}
					}
				});
			}
		}

		function file_select(el){
			var parent = window.parent,
				targetinput = parent.Core.TinyMCE.helper.targetinput,
				win = parent.Core.TinyMCE.helper.window,
				$el, url;

			if(typeof el.jquery != 'undefined') $el = el;
			else $el = $(el);

			url = $el.attr('selectname');

			//console.log(parent.Core.TinyMCE.targetinput, parent.tinymce.activeEditor); return false;

			parent.document.getElementById(targetinput).value = url;

			parent.tinymce.activeEditor.windowManager.close();

			if(parent.Core.TinyMCE.helper.image){
				parent.Core.TinyMCE.helper.image.recalcSize();
			}

			/*
			 if (typeof(win.ImageDialog) != "undefined") {
			 // we are, so update image dimensions...
			 if (win.ImageDialog.getImageData)
			 win.ImageDialog.getImageData();

			 // ... and preview if necessary
			 if (win.ImageDialog.showPreviewImage)
			 win.ImageDialog.showPreviewImage(url);
			 }
			 */

			// are we an image browser
			//parent.tinymce.PluginManager.lookup.image.instance().recalcSize();

			// close popup window
			this.close();
		}

		/**
		 * Handle mouse clicks.
		 * This supports regular clicking (select/deselect the target element),
		 * CTRL+Clicking (select/deselect target element along with other selected elements),
		 * and SHIFT+Clicking (select everything from last target to this target).
		 */
		$both.click(function(e){
			var $this = $(this),
			hasctrl = e.ctrlKey,
			hasshift = e.shiftKey,
			$selectednow = $both.filter('.selected');

			if(hasctrl){
				// Allow multiple things to be selected.
	            if($this.hasClass('selected')){
		            $this.removeClass('selected');
	            }
	            else{
	                $this.addClass('selected');
	            }
			}
			else if(hasshift){
				// Ignore this if it was the same element
	            if($lastclicked[0] == $this[0]) return;
				// Run through from the last clicked element and select until this one. (or vice versa)
				inside = false;
				$both.each(function(){
					var $me = $(this); // Because $this is already taken.

	                if(inside){
	                    $me.addClass('selected');
	                }

					if($me[0] == $this[0] || $me[0] == $lastclicked[0]){
						// Inverse it.  Basically it will start as not inside the clicked range, when it encounters
						// one, it'll flip, then once it hits the last one, it'll flip back.
						inside = !inside;
	                }

					if(inside){
						$me.addClass('selected');
	                }
				});
	        }
			else{
				if($this.hasClass('selected')){
	                // Just remove all selected classes.
	                $both.filter('.selected').removeClass('selected');

					// If there are other ones selected, unselect them and just select this one.
					if($selectednow.length > 1){
						$this.addClass('selected');
	                }
				}
				else{
					// Unselect anything else first.
	                $both.filter('.selected').removeClass('selected');
					$this.addClass('selected');
				}
			}

			$lastclicked = $this;

			// Now I can update the preview pane!
			update_preview();
		});

		/**
		 * Catch a few key events that will be handled on keyup.
		 * This is intended to prevent browser actions on certain events, such as UP, DOWN, CTRL+S, etc.
		 */
		$('body').keydown(function(e){
			switch(e.keyCode){
				// Prevent event propagation from the D-pad.  These are designed to select elements if there's a $lastelement
				case $.ui.keyCode.UP:
				case $.ui.keyCode.DOWN:
				case $.ui.keyCode.LEFT:
				case $.ui.keyCode.RIGHT:
					if($lastclicked) return false;
					break;
			}

			return true;
		});

		$('body').keyup(function(e){
			var selectnext = false, selectprev = false, panewidth, filewidth, filesperrow, i, filerows, row,
				thiscolumn, thisrow;

			// If there was no last clicked element... just continue on.
			if(!$lastclicked) return true;

			switch(e.keyCode){
				case $.ui.keyCode.ESCAPE:
					$lastclicked = null;
					$both.filter('.selected').removeClass('selected');
					update_preview();
					return false;
				case $.ui.keyCode.UP:
				case $.ui.keyCode.DOWN:
					// Because both up and down require the same logic to calculate the grid of icons,
					// I can lump them both here and wait until the end of the grid generation to pick the next one.

					// I need to calculate which file should be above this one.
					panewidth = $filespane.innerWidth();
					filewidth = $both.first().outerWidth();
					// There are x number of files per row.
					filesperrow = Math.floor(panewidth / filewidth);
					// Now I can run through and put the files into rows logically.
					filerows = [];
					i = 0;
					row = 0;
					$both.each(function(){
						i++;
						if(i > filesperrow){
							i = 1;
							row++;
						}

						if(i == 1){
							filerows[row] = [];
						}

						filerows[row].push(this);

						if(this == $lastclicked[0]){
							thiscolumn = i - 1;
							thisrow = row;
						}
					});

					if(e.keyCode == $.ui.keyCode.UP){
						if(thisrow == 0){
							// Just select the first element.
							selectnext = filerows[0][0];
						}
						else{
							selectnext = filerows[thisrow - 1][thiscolumn];
						}
					}
					else{
						if(typeof filerows[thisrow + 1] == 'undefined'){
							// Already on the last row, just select the last column.
							selectnext = filerows[thisrow][filerows[thisrow].length - 1];
						}
						else if(typeof filerows[thisrow + 1][thiscolumn] == 'undefined'){
							// There is no appropriate column on the next row, select the last one.
							selectnext = filerows[thisrow + 1][filerows[thisrow + 1].length - 1];
						}
						else{
							selectnext = filerows[thisrow + 1][thiscolumn];
						}
					}

					$both.filter('.selected').removeClass('selected');
					$(selectnext).addClass('selected');
					$lastclicked = $(selectnext);
					update_preview();
					return false;
				case $.ui.keyCode.LEFT:
					$both.each(function(){
						if(this == $lastclicked[0] && selectprev){
							$both.filter('.selected').removeClass('selected');
							$(selectprev).addClass('selected');
							$lastclicked = $(selectprev);
							update_preview();
							return false;
						}

						selectprev = this;
					});
					return false;
				case $.ui.keyCode.RIGHT:
					$both.each(function(){
						if(this == $lastclicked[0]){
							selectnext = true;
							return;
						}
						if(selectnext){
							$both.filter('.selected').removeClass('selected');
							$(this).addClass('selected');
							$lastclicked = $(this);
							update_preview();
							return false;
						}
					});
					return false;
				case $.ui.keyCode.DELETE:
					if($lastclicked.hasClass('file')) delete_file($lastclicked);
					else delete_directory($lastclicked);
					return false;
				case $.ui.keyCode.ENTER:
					if($lastclicked.hasClass('file')){
						file_select($lastclicked);
					}
					else{
						window.location.href = '?dir=' + $browser.attr('location') + '/' + $lastclicked.attr('browsename');
					}
					return false;
			}

			//console.log(e);
		});

		// Directories double click action goes to that directory.
		$directories.dblclick(function(){
			window.location.href = '?dir=' + $browser.attr('location') + '/' + $(this).attr('browsename');
		});

		// Files double click action selects the file.
		$files.dblclick(function(){
			file_select(this);
		});


		/**
		 * Enable the context menu on files and directories.
		 */
		$both.each(function(){
			$(this).contextMenu(
				{
					menu: $(this).find('.contextmenu')
				},
				function(action, el, pos){
					var newname;

					switch(action){
						case 'directory-open':
							window.location.href = '?dir=' + $browser.attr('location') + '/' + $(el).attr('browsename');
							break;
						case 'directory-rename':
							newname = prompt('Enter the new directory name', $(el).attr('browsename').replace('/', ''));
							if(!newname) return;

							$bargraphinner.width('35%');

							// Post the new name.
							$.ajax({
								url: Core.ROOT_URL + 'tinymce/directory/rename',
								type: 'post',
								dataType: 'json',
								data: { dir: $browser.attr('location'), olddir: $(el).attr('browsename'), newdir: newname },
								success: function(response){
									if(!response){
										$bargraphinner.width('0pt');
										alert('Operation failed, Unknown response');
									}
									else if(response.status){
										$bargraphinner.width('100%');
										Core.Reload();
									}
									else{
										$bargraphinner.width('0pt');
										alert(response.message);
									}
								}
							});
							break;
						case 'directory-delete':
							delete_directory(el);
							break;
						case 'file-download':
							window.open($(el).attr('selectname'));
							break;
						case 'file-rename':
							newname = $(el).attr('selectname').replace(/\\/g,'/').replace( /.*\//, '').replace(/\.[a-z]*$/i, '');
							newname = prompt('Enter the new file name', newname);
							if(!newname) return;

							$bargraphinner.width('35%');

							// Post the new name.
							$.ajax({
								url: Core.ROOT_URL + 'tinymce/file/rename',
								type: 'post',
								dataType: 'json',
								data: {
									dir: $browser.attr('location'),
									file: $(el).attr('selectname').replace(/\\/g,'/').replace( /.*\//, ''),
									newname: newname
								},
								success: function(response){
									if(!response){
										$bargraphinner.width('0pt');
										alert('Operation failed, Unknown response');
									}
									else if(response.status){
										$bargraphinner.width('100%');
										Core.Reload();
									}
									else{
										$bargraphinner.width('0pt');
										alert(response.message);
									}
								}
							});
							break;
						case 'file-delete':
							delete_file(el);
							break;
					}
				}
			);
		});

		/**
		 * And enable the browser context menu
		 */
		$filespane.contextMenu(
			{
				menu: $filespane.children('.contextmenu')
			},
			function(action, el, pos) {
				var newname;

				switch(action){
					case 'mkdir':
						newname = prompt('Enter the new directory name');
						if(!newname) return;

						$bargraphinner.width('35%');

						// Post the new name.
						$.ajax({
							url: Core.ROOT_URL + 'tinymce/directory/mkdir',
							type: 'post',
							dataType: 'json',
							data: { dir: $browser.attr('location'), newdir: newname },
							success: function(response){
								if(!response){
									$bargraphinner.width('0pt');
									alert('Operation failed, Unknown response');
								}
								else if(response.status){
									$bargraphinner.width('100%');
									Core.Reload();
								}
								else{
									$bargraphinner.width('0pt');
									alert(response.message);
								}
							}
						});
						break;
				}
			}
		);


		// Change some of the default behaviour of the uploader.
		if($uploadform){
            $bargraphinner.width('0px');

			$uploadform.fileupload({
				dropzone: $('.directory-browser-files'),
				singleFileUploads: false,
				add: function(e, data){
					$bargraphinner.width('5%');

					if(!uploadtimer){
						uploadtimer = setTimeout(function(){
							// Data will come from the calling function.
							$('.directory-browser-files').readonly(true);
							data.submit();
						}, 1000);
					}

					return true;
				},
				done: function (e, data) {
					Core.Reload();
				},
				fail: function(e, data){
					$('.directory-browser-files').readonly(false);
					$bargraphinner.width('0px');
					alert(data.errorThrown);
				},
				progress: function (e, data) {
					var progress = parseInt(data.loaded / data.total * 100, 10);
                    $bargraphinner.width(progress + '%');
				}
			});

			// Remember, the default styles are being overwrote; no need to display the uploader.
			//$uploadform.hide();
		}
	});

	// This is meant to be a tinyMCE component, so here are the tinymce components.
	var FileBrowserDialogue = {
		init : function () {
			// Here goes your code for setting your custom things onLoad.
		},
		mySubmit : function () {
			// Here goes your code to insert the retrieved URL value into the original dialogue window.
			// For example code see below.
		}
	}

	//if(typeof tinyMCEPopup.onInit != 'undefined'){
	//	tinyMCEPopup.onInit.add(FileBrowserDialogue.init, FileBrowserDialogue);
	//}
</script>
