{css src="css/gallery.css"}{/css}

<div class="gallery-widget">

	{foreach $images as $image}
			<div class="gallery-listing-preview">
				<div class="gallery-listing-preview-inner">
					{if $uselightbox}
						<a href="{$image->getFile()->getPreviewURL('1024x768')}" class="lightbox">
							{img file=$image->getFile() dimensions="`$dimensions`" title="`$image.title`"}
						</a>
					{else}
						{a href="`$image.baseurl`"}
							{img file=$image->getFile() dimensions="`$dimensions`" title="`$image.title`"}
						{/a}
					{/if}
				</div>
			</div>
	{/foreach}

	<div class="clear"></div>
	{if $link}
		<div class="gallery-listing-viewall">

			{a href="`$link`"}
				View Full Album
			{/a}
		</div>

		<div class="clear"></div>
	{/if}
</div>


{if $uselightbox}
	{script library="jquery.lightbox"}{/script}
	<script>
		$('.lightbox').lightBox({ fixedNavigation:true });
	</script>
{/if}