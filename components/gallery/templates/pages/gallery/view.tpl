{script library="jqueryui"}{/script}
{script src="js/core.fileupload.js"}{/script}

{if $editor}
	<a class="button update-link" title="Upload New Image">
		<i class="icon-upload"></i>
		<span>Upload New Image</span>
	</a>
	<br/><br/>
{/if}


<div class="gallery-description">
	{insertable name="description" title="Description"}
		<p>Description for this gallery!</p>
	{/insertable}
</div>

{foreach from=$images item=i}
	<div class="gallery-image-wrapper">
		<div class="gallery-image">
			{a href="`$i.link`"}
				{img src="public/galleryalbum/`$i.file`" width="300" height="300" title="`$i.title`"}
			{/a}
		</div>
		<div class="gallery-image-title">
			{$i.title}
		</div>

		{if $editor}
			<ul class="gallery-admin-image-utils controls">
			{*
				  <li class="control-move">
					  <a href="#" title="Drag to rearrange image">
						  <i class="icon-move"></i>
						  <span>Rearrange Image</span>
					  </a>
				  </li>
				  *}

				<li class="control-edit">
					<a href="#" title="Edit Image" class="update-link" image="{$i.id}">
						<i class="icon-edit"></i>
						<span>Edit Image</span>
					</a>
				</li>
			{*
					   <li class="control-rotate-ccw">
						   <a href="" title="Rotate Image CCW">
							   <i class="icon-undo"></i>
							   <span>Rotate Image CCW</span>
						   </a>
					   </li>

					   <li class="control-rotate-cw">
						   <a href="" title="Rotate Image CW">
							   <i class="icon-repeat"></i>
							   <span>Rotate Image CW</span>
						   </a>
					   </li>
   *}
				<li class="control-remove">
					{a href="gallery/images/delete/`$album.id`?image=`$i.id`" title="Remove Image" confirm="Confirm deleting image?"}
						<i class="icon-remove"></i>
						<span>Remove Image</span>
					{/a}
				</li>
			</ul>
		{/if}
	</div>
{/foreach}


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