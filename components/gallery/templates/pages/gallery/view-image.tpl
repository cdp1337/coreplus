{script library="jqueryui"}{/script}
{script src="js/core.fileupload.js"}{/script}
<div class="gallery-image-details">

{$image.title}<br/>

{* To change the size the "large" version opens at, simply change the resolution here. *}
{a href="`$image->getFile()->getPreviewURL('1020x800')`" class="lightbox"}
	{img file=$image->getFile() width="620" height="800" title="`$image.title`"}
{/a}

{$image.keywords}

{$image.description}
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