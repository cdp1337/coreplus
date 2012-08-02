{css src="css/gallery.css"}{/css}

<div class="gallery-listing-top-content">
{insertable name="top-content" title="Top Content"}
	<p></p>
{/insertable}
</div>

{foreach from=$albums item=album}
<div class="gallery-listing-entry">


	{foreach $album->getLink('GalleryImage') as $image}
		{if $image@index < 4}
			<div class="gallery-listing-preview">
				{a href="`$album.rewriteurl`/`$image.id`"}
						{img file=$image->getFile() width="175" height="80" title="`$image.title`"}
					{/a}
			</div>
		{/if}
	{/foreach}

	<div class="gallery-listing-title">
		{a href="`$album.rewriteurl`"}
				{$album->get('title')}
			{/a}
	</div>

	<div class="gallery-listing-viewall">
		{a href="`$album.rewriteurl`"}
			View All Images {img src="assets/gallery-arrow.png"}
		{/a}
	</div>

	<div class="clear"></div>
</div>
{/foreach}