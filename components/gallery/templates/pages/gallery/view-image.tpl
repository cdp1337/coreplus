{script library="jqueryui"}{/script}
{script src="js/core.fileupload.js"}{/script}

{$image.title}<br/>

{* To change the size the "large" version opens at, simply change the resolution here. *}
{a href="`$image->getFile()->getPreviewURL('1024x768')`" class="lightbox"}
	{img src="public/galleryalbum/`$image.file`" width="700" height="800" title="`$image.title`"}
{/a}

{$image.keywords}

{$image.description}

{if $lightbox_available}
	{script library="jquery.lightbox"}{/script}
	<script>
		$('.lightbox').lightBox({ fixedNavigation:true });
	</script>
{/if}


{if $editor}

<script>
	$(function(){

		$('.update-link').click(function(){
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
				close: function(){ $(this).dialog('destroy').remove(); }
			}).dialog('open');

			$dialog.load(Core.ROOT_WDIR + 'gallery/images/update/{$album.id}?image=' + image);

			return false;
		});
	});
</script>

{/if}