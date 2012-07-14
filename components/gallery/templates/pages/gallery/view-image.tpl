
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
