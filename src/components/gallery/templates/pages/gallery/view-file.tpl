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

<div class="clear"></div>


<div class="gallery-{$image->getFileType()}-details">

	{*
	Yeah I know this is the file view, but the variable is called image... deal with it
	*}
	{img file=$image->getFile() width="48" height="48" title="`$image.title`"}
	<a href="{$image->getFile()->getURL()}">
		{$image.title}
	</a><br/>
	Filesize: {Core::FormatSize($image->getFile()->getFilesize())}<br/>
	Filetype: {$image->getFile()->getMimetype()}


</div>

<div class="gallery-image-description">
	{$image.description}
</div>

<div class="gallery-image-keywords">
	{$image.keywords}
</div>



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

	});
</script>

{/if}
