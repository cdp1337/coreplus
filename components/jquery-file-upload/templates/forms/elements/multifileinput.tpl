{*
<!-- Bootstrap CSS Toolkit styles -->
<link rel="stylesheet" href="http://blueimp.github.com/cdn/css/bootstrap.min.css">
<!-- Generic page styles -->
<link rel="stylesheet" href="css/style.css">
<!-- Bootstrap styles for responsive website layout, supporting different screen sizes -->
<link rel="stylesheet" href="http://blueimp.github.com/cdn/css/bootstrap-responsive.min.css">
<!-- Bootstrap CSS fixes for IE6 -->
<!--[if lt IE 7]><link rel="stylesheet" href="http://blueimp.github.com/cdn/css/bootstrap-ie6.min.css"><![endif]-->
<!-- Bootstrap Image Gallery styles -->
<link rel="stylesheet" href="http://blueimp.github.com/Bootstrap-Image-Gallery/css/bootstrap-image-gallery.min.css">
<!-- CSS to style the file input field as button and adjust the Bootstrap progress bars -->
<link rel="stylesheet" href="css/jquery.fileupload-ui.css">
*}

<div class="container {$element->getClass()} {$element->get('id')}">

{if $element->get('title')}
	<label for="{$element->get('name')}">{$element->get('title')|escape}</label>
{/if}

{if $element->get('description')}
	<p class="formdescription">{$element->get('description')}</p>
{/if}

	<!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
	<div class="row fileupload-buttonbar">
		<div class="span7">
			<!-- The fileinput-button span is used to style the file input field as button -->
			<label style="display:block; float:left; position:relative; overflow:hidden; width:120px; margin-right:10px;">
				<span class="button btn-success fileinput-button" style="width:87px;">
					<i class="icon-plus icon-white"></i>
					<span>Add files...</span>
					<input id="{$element->get('id')}" type="file" name="{$element->get('name')}[]" multiple="multiple" style="position:absolute; left:0pt; top:0pt; opacity:0;">
				</span>
			</label>
			<!--<button type="submit" class="button btn-primary start">
				<i class="icon-upload icon-white"></i>
				<span>Start uploads</span>
			</button>-->
			<button type="reset" class="button btn-warning cancel">
				<i class="icon-ban-circle icon-white"></i>
				<span>Cancel uploads</span>
			</button>
		</div>
		<!-- The global progress information -->
		<div class="progress-container span5 fileupload-progress fade" style="display:none;">
			<!-- The global progress bar -->
			<div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
				<div class="bar" style="width:0%;"></div>
			</div>
			<!-- The extended global progress information -->
			<div class="progress-extended">&nbsp;</div>
		</div>
	</div>

	<div class="multiupload-drag-notice">
		<i class="icon-upload"></i>Drop files here to upload
	</div>

	<!-- The loading indicator is shown during file processing -->
	<div class="fileupload-loading"></div>
	<br>
	<!-- The table listing the files available for upload/download -->
	<table role="presentation" class="table table-striped listing"><tbody class="files" data-toggle="modal-gallery" data-target="#modal-gallery"></tbody></table>
	<br>

</div>


{literal}
<!-- The template to display files available for upload -->
<script id="template-upload" type="text/x-tmpl">
	{% for (var i=0, file; file=o.files[i]; i++) { %}
	<tr class="template-upload fade">
		<td class="preview" width="100">
			{% if (o.files.valid && !i) { %}
			<div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="bar" style="width:0%;"></div></div>
			{% } %}
			<span class="fade"></span>
		</td>
		<td class="name"><span>{%=file.name%}</span></td>
		<td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
		{% if (file.error) { %}
		<td class="error" colspan="2"><span class="label label-important">{%=locale.fileupload.error%}</span> {%=locale.fileupload.errors[file.error] || file.error%}</td>
		{% } else if (o.files.valid && !i) { %}
		{% if (!o.options.autoUpload) { %}
		<td class="start">
			<button class="button btn-primary">
				<i class="icon-upload icon-white"></i>
				<span>{%=locale.fileupload.start%}</span>
			</button>
		</td>
		{% } %}
		{% } else { %}
		<td colspan="2"></td>
		{% } %}
		<td class="cancel">{% if (!i) { %}
			<button class="button btn-warning">
				<i class="icon-ban-circle icon-white"></i>
				<span>{%=locale.fileupload.cancel%}</span>
			</button>
			{% } %}</td>
	</tr>
	{% } %}
</script>
<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
	{% for (var i=0, file; file=o.files[i]; i++) { %}
<tr class="template-download fade">
	{% if (file.error) { %}
	<td></td>
	<td class="name"><span>{%=file.name%}</span></td>
	<td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
	<td class="error" colspan="2"><span class="label label-important">{%=locale.fileupload.error%}</span> {%=locale.fileupload.errors[file.error] || file.error%}</td>
	{% } else { %}
	<td class="preview">{% if (file.thumbnail_url) { %}
		<a href="{%=file.url%}" title="{%=file.name%}" rel="gallery" download="{%=file.name%}"><img src="{%=file.thumbnail_url%}"></a>
		{% } %}</td>
<td class="name">
{/literal}{* Remember, this form doesn't actually change the database... that's still up to the form submission! *}{literal}
	<input type="hidden" name="{/literal}{$element->get('name')}[]{literal}" value="{%=file.name%}"/>
	<a href="{%=file.url%}" title="{%=file.name%}" rel="{%=file.thumbnail_url&&'gallery'%}" download="{%=file.name%}" target="_BLANK">{%=file.name%}</a>
</td>
	<td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
	{% } %}
	<td class="delete">
		<button class="button btn-danger" data-type="{%=file.delete_type%}" data-url="{%=file.delete_url%}">
			<i class="icon-trash icon-white"></i>
			<span>{%=locale.fileupload.destroy%}</span>
		</button>
	</td>
</tr>
	{% } %}
</script>
{/literal}

{css src="css/jquery-file-upload.css"}{/css}

{* This system requires jquery and jqueryui. *}
{script library="jqueryui"}{/script}
{* The Templates plugin is included to render the upload/download listings *}
{script library="jquery.tmpl"}{/script}

<!-- The Load Image plugin is included for the preview images and image resizing functionality -->
{*<script src="http://blueimp.github.com/JavaScript-Load-Image/load-image.min.js"></script>*}
<!-- The Canvas to Blob plugin is included for image resizing functionality -->
{*<script src="http://blueimp.github.com/JavaScript-Canvas-to-Blob/canvas-to-blob.min.js"></script>*}

{* The Iframe Transport is required for browsers without support for XHR file uploads *}
{script src="js/jquery.iframe-transport.js"}{/script}
{* The basic File Upload plugin *}
{script src="js/jquery.fileupload.js"}{/script}
{* The File Upload file processing plugin *}
{script src="js/jquery.fileupload-fp.js"}{/script}
{* The File Upload user interface plugin *}
{script src="js/jquery.fileupload-ui.js"}{/script}
{* The localization script *}
{script src="js/locale.js"}{/script}

<script>
	$(function () {
		var $form = $('#{$element->get('id')}').closest('form'),
			$progressbar = $('.' + '{$element->get('id')}').find('.fileupload-progress'),
			$bargraphinner = $progressbar.find('.bar'),
			$barextendedinfo = $progressbar.find('.progress-extended'),
			failnotice = false,
			bitrates = []; // keep track of the last few bit rates for averaging purposes.

		// Initialize the jQuery File Upload widget:
		$form.fileupload({
			url: Core.ROOT_URL + 'jqueryfileupload',
			formData: { key: '{$element->get('id')}' },
			previewSourceFileTypes: /^.*$/, // Core+ handles previews of all files ;)
			autoUpload: true, // By default, files added to the UI widget are uploaded as soon as the user clicks on the start buttons. To enable automatic uploads, set this option to true.
			start: function(e, data){
				$progressbar.show();
				$bargraphinner.width('1%');
				bitrates = [];
			},
			fail: function(e, data){
				if(!failnotice){
					failnotice = true;
					$bargraphinner.width('0px');
					$progressbar.hide();
					// If the user clicked abort.... they probably don't care.
					if(data.errorThrown != 'abort') alert(data.errorThrown);
					setTimeout(function(){ failnotice = false; }, 2000);
				}
			},
			progressall: function (e, data) {
				var progress = parseFloat(data.loaded / data.total * 100, 10), i, sum,
					avgbitrate, bitratestr, timeremainingstr, totalsizestr,
					timeremaining = { raw: 0, h: null, m: null, s: null };

				//console.log(data);
				if(progress >= 99){
					$bargraphinner.width('0px');
					$progressbar.hide();
				}
				else{
					if(bitrates.length > 100){
						bitrates.shift();
					}
					bitrates.push(data.bitrate);
					sum = 0.00;
					for(i=0; i<bitrates.length; i++){
						sum += parseFloat(bitrates[i]);
					}
					// Since javascript doesn't support rounding to a certain number of decimal places... simply boost each number by a power of 100.
					avgbitrate = Math.round((sum / bitrates.length) * 1000) / 1000;

					// Now that I have the average bitrate for recent connections... convert that into a human readable string.
					if(avgbitrate > (1024*1024)){
						bitratestr = ((Math.round(avgbitrate / (1024*1024) * 10 )) / 10) + ' MB/s';
					}
					else if(avgbitrate > 1024){
						bitratestr = ((Math.round(avgbitrate / (1024) * 10)) / 10) + ' kB/s';
					}
					else {
						bitratestr = (Math.round(avgbitrate)) + ' B/s';
					}

					// Make the total size readable.
					if(data.total > (1024*1024)){
						totalsizestr = (Math.round(data.total / (1024*1024) * 10) / 10) + 'MB';
					}
					else if(data.total > 1024){
						totalsizestr = (Math.round(data.total / (1024) * 10) / 10) + 'kB';
					}
					else{
						totalsizestr = data.total + ' bytes';
					}

					// Figure out how much longer based on the data left and the average speed.
					timeremaining.raw = (data.total - data.loaded) / (avgbitrate * .1) + 1;

					if(timeremaining.raw > (60*60)){
						timeremaining.h = Math.round(timeremaining.raw / 3600);
						timeremaining.m = Math.round(timeremaining.raw % 3600);

						// :p
						timeremainingstr = (timeremaining.h * 2) + ' cups of coffee, ' + timeremaining.m + ' minute' + (timeremaining.m == 1 ? '' : 's');
						//timeremainingstr = Math.round(timeremaining / 3600) + 'h ' + Math.round(timeremaining % 3600) + 'm';
					}
					else if(timeremaining.raw > 60){
						timeremaining.m = Math.round(timeremaining.raw / 60);
						timeremaining.s = Math.round(timeremaining.raw % 60);

						timeremainingstr = timeremaining.m + ' minute' + (timeremaining.m == 1 ? '' : 's');// + ', ' +
						//timeremaining.s + ' second' + (timeremaining.s == 1 ? '' : 's')
					}
					else {
						timeremaining.s = Math.round(timeremaining.raw);

						timeremainingstr = timeremaining.s + ' second' + (timeremaining.s == 1 ? '' : 's')
					}

					// Now I can write all this to the extended info bar!
					$barextendedinfo.html(Math.round(progress) + '% of ' + totalsizestr + ' uploaded.  Estimated time remaining: ' + timeremainingstr + ' @ ' + bitratestr);

					$bargraphinner.width( (Math.round(progress * 100) / 100) + '%');
				}
			},
			_last: null
		});




		// Hijack the submit function too, just to provide some user tips.
		$form.submit(function(){
			if($form.find('.template-upload').length > 0){
				if(!confirm('You still have files waiting to upload, press cancel to go back and upload them.')) return false;
			}
			return true;
		});
	});
</script>
{* The main application script *}
{*script src="js/main.js"}{/script*}
<!-- The XDomainRequest Transport is included for cross-domain file deletion for IE8+ -->
<!--[if gte IE 8]><script src="{asset file='js/cors/jquery.xdr-transport.js'}"></script><![endif]-->
