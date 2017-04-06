
<div class="container {$element->getClass()} {$element->get('id')}">

	{if $element->get('title')}
		<label for="{$element->get('name')}">{$element->get('title')|escape}</label>
	{/if}

	{if $element->get('description')}
		<p class="formdescription">{$element->get('description')}</p>
	{/if}

	<!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
	<div class="fileupload-buttonbar">
		<!-- The fileinput-button span is used to style the file input field as button -->
		<label style="display:block; float:left; position:relative; overflow:hidden;">
			<span class="button btn-success fileinput-button">
				<i class="icon icon-plus"></i>
				<span>Add files...</span>
				<input id="{$element->get('id')}" type="file" name="{$element->get('name')}[]" multiple="multiple" style="position:absolute; left:0pt; top:0pt; opacity:0;">
			</span>
		</label>
		<!--<button type="submit" class="button btn-primary start">
			<i class="icon icon-upload icon-white"></i>
			<span>Start uploads</span>
		</button>-->
		<button type="reset" class="button btn-warning cancel">
			<i class="icon icon-times-circle"></i>
			<span>Cancel uploads</span>
		</button>
	</div>
	
	<!-- The global progress information -->
	<progress id="{$element->get('id')}-progressbar" class="fileupload-progress" style="display:none;"></progress>
	<br/>
	<div id="{$element->get('id')}-progress-extended" class="progress-extended">&nbsp;</div>

	<div class="multiupload-drag-notice">
		<i class="icon icon-upload"></i>Drop files here to upload
	</div>
	<br/>
	
	<!-- The table listing the files available for upload/download -->
	<table class="listing">
		<tbody class="files" data-toggle="modal-gallery" data-target="#modal-gallery"></tbody>
	</table>
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
			<td class="error" colspan="2"><p class="message message-error">{%=file.error%}</p></td>
		{% } else if (o.files.valid && !i) { %}
			{% if (!o.options.autoUpload) { %}
			<td class="start">
				<button class="button btn-primary">
					<i class="icon icon-upload"></i>
					<span>Start</span>
				</button>
			</td>
			{% } %}
		{% } else { %}
			<td colspan="2"></td>
		{% } %}
		<td class="cancel">{% if (!i) { %}
			<button class="button btn-warning">
				<i class="icon icon-ban-circle"></i>
				<span>Cancel</span>
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
				<td class="preview"></td>
				<td class="name"><span>{%=file.name%}</span></td>
				<td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
				<td class="error" colspan="1"><p class="message message-error"> {%=file.error%}</p></td>
			{% } else { %}
				<td class="preview">
					{% if (file.thumbnail_url) { %}
						<a href="{%=file.url%}" title="{%=file.name%}" rel="gallery" download="{%=file.name%}" target="_BLANK"><img src="{%=file.thumbnail_url%}"></a>
					{% } %}
				</td>
				<td class="name">
					<!-- Remember, this form doesn't actually change the database... that's still up to the form submission! -->
					<input type="hidden" name="{/literal}{$element->get('name')}[]{literal}" value="{%=file.name%}"/>
					<a href="{%=file.url%}" title="{%=file.name%}" rel="{%=file.thumbnail_url&&'gallery'%}" download="{%=file.name%}" target="_BLANK">{%=file.name%}</a>
				</td>
				<td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
				<td class="error"></td>
			{% } %}
			<td class="delete">
				<button class="button remove-uploaded-file-link" data-type="{%=file.delete_type%}" data-url="{%=file.delete_url%}">
					<i class="icon icon-trash"></i>
					<span>Remove File</span>
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
{script src="libs/jquery-file-upload/js/jquery.iframe-transport.js"}{/script}
{* The basic File Upload plugin *}
{script src="libs/jquery-file-upload/js/jquery.fileupload.js"}{/script}
{* The File Upload file processing plugin *}
{script src="libs/jquery-file-upload/js/jquery.fileupload-process.js"}{/script}
{*{script src="libs/jquery-file-upload/js/jquery.fileupload-resize.js"}{/script}*}
{script src="libs/jquery-file-upload/js/jquery.fileupload-validate.js"}{/script}
{* The File Upload user interface plugin *}
{script src="libs/jquery-file-upload/js/jquery.fileupload-ui.js"}{/script}

<script>
	$(function () {
		var $form = $('#{$element->get('id')}').closest('form'),
			$formelement = $('#{$element->get('id')}').closest('.multifileinput'),
			$progressbar = $('#{$element->get('id')}-progressbar'),
			$barextendedinfo = $('#{$element->get('id')}-progress-extended'),
			failnotice = false,
			bitrates = [], // keep track of the last few bit rates for averaging purposes.
			d = new Date(),
			dateStarted, formatSize, formatDuration;
			
		formatSize = function(raw, isSpeed){
			var onegb = 1073741824,
				onemb = 1048576,
				onekb = 1024,
				suffixes;
		
			if(isSpeed){
				suffixes = ['bps', 'kbps', 'mbps', 'gbps'];
			}
			else{
				suffixes = [' bytes', 'kB', 'MB', 'GB'];
			}
			
			if(raw >= onegb){
				return (Math.round(raw / onegb * 10) / 10) + suffixes[3];
			}
			else if(raw >= onemb){
				return (Math.round(raw / onemb * 10) / 10) + suffixes[2];
			}
			else if(raw >= onekb){
				return (Math.round(raw / onekb * 10) / 10) + suffixes[1];
			}
			else{
				return raw + suffixes[0];
			}
		};
		
		formatDuration = function(raw){
			var h, m, s;
			h = raw > 3600 ? Math.floor(raw / 3600) : 0;
			m = Math.floor((raw - (h * 3600)) / 60);
			s = Math.floor(raw - (h * 3600) - (m * 60));

			if(s === 0) s = '00';
			else if(s < 10) s = '0' + s;

			if(h > 0){
				return h + ' hr ' + m + ' min';
			}
			else{
				return m + ':' + s;
			}
		};

		// Initialize the jQuery File Upload widget:
		$formelement.fileupload({
			url: Core.ROOT_URL + 'jqueryfileupload',
			formData: { key: '{$element->get('uploadkey')}' },
			previewSourceFileTypes: /^.*$/, // Core+ handles previews of all files ;)
			autoUpload: true, // By default, files added to the UI widget are uploaded as soon as the user clicks on the start buttons. To enable automatic uploads, set this option to true.
			multipart: false,
			maxChunkSize: {$element->get('maxsize')},
			headers: {
				'X-Key': '{$element->get('uploadkey')}',
				'X-Upload-Time': Math.round(d.getTime() / 100) // Used to prevent multiple page loads from appending to the same file if there's an error.
			},
			start: function(e, data){
				$progressbar.show();
				bitrates = [];
				dateStarted = new Date();
			},
			finished: function(e, data){
				var dateFinished = new Date(),
					duration = (dateFinished - dateStarted) / 1000,
					strOut;
				
				// Upload is done, hide the progress bar.
				$progressbar.hide();
				
				// Update the extended info.
				strOut = 'Uploaded ' + formatSize(data.total, false) + 
					' in ' + formatDuration(duration) +
					' ( ' + formatSize(data.total * 8 / duration, true) + ' )';
					
				$barextendedinfo.html(strOut);
				
				// I need to bind the remove button event on the record!
				data.context.find('.remove-uploaded-file-link').click(function(){
					$(this).closest('tr').remove();
					return false;
				});
			},
			fail: function(e, data){
				if(!failnotice){
					failnotice = true;
					$progressbar.hide();
					// If the user clicked abort.... they probably don't care.
					if(data.errorThrown != 'abort') alert(data.errorThrown);
					setTimeout(function(){ failnotice = false; }, 2000);
				}
			},
			progressall: function (e, data) {
				$progressbar.attr('max', data.total);
				$progressbar.attr('value', data.loaded);
				var progress = parseFloat(data.loaded / data.total * 100, 10), i, sum,
					avgbitrate, bitratestr, timeremainingstr, totalsizestr,
					speeds = { raw: 0, h: null, m: null, s: null };

				if(bitrates.length > 10){
					bitrates.shift();
				}
				bitrates.push(data.bitrate);
				sum = 0.00;
				for(i=0; i<bitrates.length; i++){
					sum += parseFloat(bitrates[i]);
				}
				// Since javascript doesn't support rounding to a certain number of decimal places... simply boost each number by a power of 100.
				avgbitrate = Math.round((sum / bitrates.length) * 1000) / 1000;

				// Now that I have the average bitrate for recent connections...
				// convert that into a human readable string.
				bitratestr = formatSize(avgbitrate, true);

				// Make the total size readable.
				totalsizestr = formatSize(data.total, false);

				// Figure out how much longer based on the data left and the average speed.
				speeds.raw = (data.total - data.loaded) / (avgbitrate / 8);
				timeremainingstr = formatDuration(speeds.raw);

				// Now I can write all this to the extended info bar!
				$barextendedinfo.html(Math.round(progress) + '% of ' + totalsizestr + ' uploaded.  Estimated time remaining: ' + timeremainingstr + ' @ ' + bitratestr);
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
<!-- The XDomainRequest Transport is included for cross-domain file deletion for IE8+ -->
<!--[if gte IE 8]><script src="{asset file='js/cors/jquery.xdr-transport.js'}"></script><![endif]-->
