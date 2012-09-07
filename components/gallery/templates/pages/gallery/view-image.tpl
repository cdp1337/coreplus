{script library="jqueryui"}{/script}
{script src="js/core.fileupload.js"}{/script}
{css src="css/gallery.css"}{/css}


<div class="gallery-image-details">

	{$image.title}<br/>

	<table><tr>
		<td class="gallery-previous-image">
			{if $prev}
				{a href="`$prev->getRewriteURL()`" title="`$prev.title`"}
					{img file=$prev->getFile() width="50" height="50" title="`$prev.title`"}
				{/a}
			{/if}
		</td>
		<td>
			{* To change the size the "large" version opens at, simply change the resolution here. *}
			{a href="`$image->getFile()->getPreviewURL('1020x800')`" class="lightbox"}
				{img file=$image->getFile() width="620" height="700" title="`$image.title`"}
			{/a}
		</td>
		<td class="gallery-next-image">
			{if $next}
				{a href="`$next->getRewriteURL()`" title="`$next.title`"}
					{img file=$next->getFile() width="50" height="50" title="`$next.title`"}
				{/a}
			{/if}
		</td>
	</tr></table>

	{$image.keywords}

	{$image.description}

	{if $exif}
		Make: {$exif.Make}<br/>
		Model: {$exif.Model}<br/>
		Aperture: {$exif.ApertureFNumber}<br/>
		Original Resolution: {$exif.dimensions}<br/>
		Original Filesize: {$exif.FileSize}<br/>
		Software: {$exif.Software}<br/>
		DateTime: {$exif.DateTime}<br/>
		ExposureTime: {$exif.ExposureTime}<br/>
		ISO: {$exif.ISOSpeedRatings}<br/>
		{if $exif.Flash}Flash Used{else}No Flash Used{/if}<br/>
		Metering: {$exif.MeteringMode}<br/>
	{/if}

	{*
		I also want to display...
		Make
		Model
		Software
		DateTime
		Artist
		HostComputer
		ColorMap
	*}
</div>

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
					$(this).dialog('destroy').remove();
				}
			}).dialog('open');

			$dialog.load(Core.ROOT_WDIR + 'gallery/images/update/{$album.id}?image=' + image);

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
					$(this).dialog('destroy').remove();
				}
			}).dialog('open');

			return false;
		});
	});
</script>

{/if}