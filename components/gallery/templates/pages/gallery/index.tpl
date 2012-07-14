{css src="css/gallery.css"}{/css}

{foreach from=$albums item=album}
	<div class="gallery-listing-entry">
		<div class="gallery-listng-title">
			{a href="`$album.rewriteurl`"}
				{$album->get('title')}
			{/a}
		</div>

		{foreach $album->getLink('GalleryImage') as $image}
			{if $image@index < 3}
				<div class="gallery-listing-preview">
					{a href="`$album.rewriteurl`/`$image.id`"}
						{img src="public/galleryalbum/`$image.file`" width="80" height="100" title="`$image.title`"}
					{/a}
				</div>
			{/if}
		{/foreach}

		<div class="gallery-listing-viewall">
			{a href="`$album.rewriteurl`"}
				View All Images
			{/a}
		</div>

		<div class="clear"></div>
	</div>
{/foreach}