{script library="jqueryui"}{/script}
{script src="js/core.fileupload.js"}{/script}
{script library="jqueryui.timepicker"}{/script}
{css src="css/gallery.css"}{/css}
{script library="core.ajaxlinks"}{/script}


{if $prev}
	<div class="gallery-previous-image">
		{a href="`$prev->getRewriteURL()`" title="`$prev.title`"}
			{img file=$prev->getFile() width="75" height="75" title="`$prev.title`"}
			<i class="icon icon-chevron-left"></i>
		{/a}
	</div>
{/if}

{if $next}
	<div class="gallery-next-image">
		{a href="`$next->getRewriteURL()`" title="`$next.title`"}
			{img file=$next->getFile() width="75" height="75" title="`$next.title`"}
			<i class="icon icon-chevron-right"></i>
		{/a}
	</div>
{/if}


<div class="gallery-{$image->getFileType()}-details">
	{* To change the size the "large" version opens at, simply change the resolution here. *}
	{a href="`$image->getFile()->getPreviewURL('1020x800')`" data-lightbox="image-1" data-title="{$image.title}"}
		{img file=$image->getFile() width="760" height="760" title="`$image.title`"}
	{/a}

	<div class="gallery-image-detailspaneouter">
		<div class="gallery-image-detailspane">
			<div class="gallery-image-detailspane-title">
				{$image.title}
			</div>

			{if !$exif && ($image.location || $image.datetaken)}
				<div class="gallery-image-detailspane-datetakenlocation">
					{if $image.location}
						<span class="gallery-image-detailspane-location">{$image.location}</span>
					{/if}
					{if $image.location && $image.datetaken}
						<span class="gallery-image-detailspane-location-date-separator">-</span>
					{/if}
					{if $image.datetaken}
						{date date="`$image.datetaken`"}
					{/if}
				</div>
			{/if}

			{if $exif}
				<div class="gallery-image-detailspane-datetakenlocation">
					<span class="gallery-image-detailspane-location" style="display:none;"></span>
					<span class="gallery-image-detailspane-location-date-separator" style="display:none;">-</span>
					{if $image.datetaken}
						{date date="`$image.datetaken`"}
					{else}
						{date date="`$exif.DateTime`"}
					{/if}
				</div>
				<div class="gallery-image-detailspane-showhideextra">
					<span class="show">more info [+]</span>
					<span class="hide" style="display:none;">less info [-]</span>
				</div>
				<div class="gallery-image-detailspane-exposureaperture">
					{if is_numeric($exif.ExposureTime) && $exif.ExposureTime >= 1}
						<span title="{$exif.ExposureTime} second exposure time">
							{$exif.ExposureTime} second{if $exif.ExposureTime > 1}s{/if}
						</span>
					{elseif $exif.ExposureTime}
						<span title="{$exif.ExposureTime}th of a second exposure time">
							{$exif.ExposureTime}th
						</span>
					{/if}

					{if $exif.ExposureTime && $exif.FNumber}
						@
					{/if}

					{if $exif.FNumber}
						<span title="Aperture of f/{$exif.FNumber}">
							f/{$exif.FNumber}
						</span>
					{/if}
				</div>

				{if $exif.FocalLength}
					<div class="gallery-image-detailspane-focallength" title="Focal Length of {$exif.FocalLength}mm">
						{$exif.FocalLength}mm
					</div>
				{/if}

				{if $exif.ISOSpeedRatings}
					<div class="gallery-image-detailspane-iso" title="ISO of {$exif.ISOSpeedRatings}">
						ISO {$exif.ISOSpeedRatings}
					</div>
				{/if}

				{if $exif.Make || $exif.Model}
					<div class="gallery-image-detailspane-makemodel">
						{$exif.Make} - {$exif.Model}
					</div>
				{/if}

				<div class="gallery-image-detailspane-extrainformation">
					<span class="extralabel">Original Dimensions:</span>
					{$exif.Width}px X {$exif.Height}px<br/>

					<span class="extralabel">Original Filesize:</span>
					{$exif.FileSizeFormatted}<br/>

					<span class="extralabel">Original Resolution:</span>
					{$exif.XResolution} X {$exif.YResolution}<br/>

					{if $exif.Software}<span class="extralabel">Software:</span> {$exif.Software}<br/>{/if}

					<span class="extralabel">Flash:</span>
					{$exif.FlashDesc}<br/>

					<span class="extralabel">Metering Mode:</span>
					{$exif.MeteringModeDesc}<br/>

					<span class="extralabel">Exposure Program:</span>
					{$exif.ExposureProgramDesc}<br/>

					{if $exif.ShutterSpeedValue}
						<span class="extralabel">Shutter Speed:</span>
						{$exif.ShutterSpeedValue} EV<br/>
					{/if}

					{if $exif.ApertureValue}
						<span class="extralabel">Aperture Value:</span>
						{$exif.ApertureValue}<br/>
					{/if}

					{if $exif.MaxApertureValue}
						<span class="extralabel">Max Aperture:</span>
						{$exif.MaxApertureValue}<br/>
					{/if}

					{if $exif.ExposureBiasValue}
						<span class="extralabel">Exposure Bias Value:</span>
						{$exif.ExposureBiasValue}<br/>
					{/if}

					<span class="extralabel">Light Source:</span>
					{$exif.LightSourceDesc}<br/>

					{if $exif.Artist && $exif.Artist != 'unknown'}
						<span class="extralabel">Artist:</span>
						{$exif.Artist}<br/>
					{/if}

					{if $exif.Copyright}
						<span class="extralabel">Copyright:</span>
						{$exif.Copyright}<br/>
					{/if}
				</div>
			{/if}
		</div>
	</div>
	<div></div>
</div>

<div class="gallery-image-description">
	{$metas.description}
</div>

{if $metas.keywords}
	<div class="gallery-image-keywords">
		{$metas.keywords|implode:", "}
	</div>
{/if}

{$metas->getAsHTML()}


{if $exif && $exif.GPS}
	{script src="https://maps.googleapis.com/maps/api/js?sensor=false"}{/script}
	{script location="foot"}<script type="text/javascript">
		(function(){

			{if $image.location}
				$('.gallery-image-detailspane-location').html("{$image.location}").show();
				$('.gallery-image-detailspane-location-date-separator').show();
			{else}
				var geo = new google.maps.Geocoder(),
					loc = new google.maps.LatLng({$exif.GPS.lat}, {$exif.GPS.lng});

				//console.log(loc);

				geo.geocode(
					{
						location: loc
						//address: address,
						//region: $formels.country.val()
						//region: 'us'
					}, function(result, status){
						//console.log(result[0]);
						//console.log(result[0].address_components[3].long_name + ', ' + result[0].address_components[5].long_name);

						if(status == 'OK'){
							{if $uploader}
								$('.gallery-image-detailspane-location').html(result[0].formatted_address).show();
							{else}
								$('.gallery-image-detailspane-location').html(
									result[0].address_components[3].long_name + ', ' + result[0].address_components[5].long_name
								).show();

							{/if}

							$('.gallery-image-detailspane-location-date-separator').show();
						}
					}
				);
			{/if}
		})();

	</script>{/script}
{/if}

{if $exif}
{script location="foot"}<script type="text/javascript">
	$('.gallery-image-detailspane-showhideextra').click(function(){
		if($(this).find('.show').is(':visible')){
			// So show it!
			$(this).find('.show').hide();
			$(this).find('.hide').show();
			$('.gallery-image-detailspane-extrainformation').show();
		}
		else{
			// So hide it!
			$(this).find('.show').show();
			$(this).find('.hide').hide();
			$('.gallery-image-detailspane-extrainformation').hide();
		}
	});
</script>{/script}

{/if}


{if $lightbox_available}
	{script library="jquery.lightbox"}{/script}
	<script>
		$('.lightbox').lightBox({ fixedNavigation:true });
	</script>
{/if}


{if $editor}

<script>
	$(function () {

		$('.update-link').click(function () {
			var $dialog = $('<div>Loading...</div>'),
					$this = $(this),
					image = $this.attr('image'),
					windowtitle = $this.attr('title');

			$('body').append($dialog);

			$dialog.show().dialog({
				modal:   true,
				autoOpen:false,
				title:   windowtitle,
				width:   '500px',
				close:   function () {
					$(this).remove();
				}
			}).dialog('open');

			$dialog.load(
				Core.ROOT_WDIR + 'gallery/images/update/{$album.id}?image=' + image,
				function(){
					$dialog.dialog('option', 'position', 'center');
				}
			);

			return false;
		});

		$('.rotate-link').click(function () {
			var $dialog = $('<div>Rotating...</div>'),
					$this = $(this),
					image = $this.attr('image'),
					windowtitle = 'Rotating',
					$xhr;

			$('body').append($dialog);

			$xhr = $.ajax({
				url:     Core.ROOT_WDIR + 'gallery/images/rotate/{$album.id}.json',
				data:    {
					image: image,
					rotate:$this.attr('rotate')
				},
				type:    'GET',
				dataType:'json',
				error:   function () {
					$dialog.dialog('destroy').remove();
					alert('There was an error while rotating the image.');
				},
				success: function () {
					window.location.reload();
				}
			});

			$dialog.show().dialog({
				modal:   true,
				autoOpen:false,
				title:   windowtitle,
				width:   '500px',
				close:   function () {
					$xhr.abort();
					$(this).remove();
				}
			}).dialog('open');

			return false;
		});
	});
</script>

{/if}

<script type="text/javascript">
	$('.gallery-image-detailspane')
		.mouseover(function(){
			$(this).addClass('hover');
		})
		.mouseout(function(){
			$(this).removeClass('hover');
		});
</script>