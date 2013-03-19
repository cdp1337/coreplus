{css src="css/gallery.css"}{/css}

<div class="gallery-listing-top-content">
{insertable name="top-content" title="Top Content"}
	<p></p>
{/insertable}
</div>

{foreach from=$albums item=album}
	<div class="gallery-listing-entry">

		<h3>
			{a href="`$album.rewriteurl`"}
				{$album->get('title')}
			{/a}
		</h3>

		{foreach $album->getLink('GalleryImage') as $image}
			{if $image@index < 4}
				<div class="gallery-listing-preview">
					<div class="gallery-listing-preview-inner">
						{a href="`$album.rewriteurl`/`$image.id`"}
							{img file=$image->getFile() dimensions="^150x150" title="`$image.title`"}
						{/a}
					</div>
				</div>
			{/if}
		{/foreach}

		<div class="clear"></div>
		<div class="gallery-listing-viewall">
			{a href="`$album.rewriteurl`"}
				View All
			{/a}
			({sizeof($album->getLink('GalleryImage'))}) total
		</div>
		<div class="gallery-listing-creationdate">
			Created {date date="`$album.created`"}
		</div>

		<div class="clear"></div>
	</div>
{/foreach}