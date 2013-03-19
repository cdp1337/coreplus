{script library="jqueryui"}{/script}
{script library="jquery.masonry"}{/script}
{script src="js/core.fileupload.js"}{/script}
{script library="jqueryui.timepicker"}{/script}
{css src="css/gallery.css"}{/css}

<h1>{$album.title}</h1>

{if $uploader}
	{if Core::IsComponentAvailable('jQuery-File-Upload')}
		{$uploadform->render()}
	{else}
		<a class="button update-link" title="Upload New Image">
			<i class="icon-upload"></i>
			<span>Upload New Image</span>
		</a>
	{/if}
	<br/><br/>
{/if}

<div class="gallery-description">
	{insertable name="description" title="Description"}
		<p>Description for this gallery!</p>
	{/insertable}
</div>

<div id="gallery-images">
	{foreach from=$images item=i}

		{* Calculate the image size based on sm/med/lg *}
		{if $i.previewsize == 'sm'}
			{assign var="dimensions" value="200x200"}
		{elseif $i.previewsize == 'med'}
			{assign var="dimensions" value="400x400"}
		{elseif $i.previewsize == 'lg'}
			{assign var="dimensions" value="800x800"}
		{else}
			{assign var="dimensions" value="200x200"}
		{/if}

		<div class="gallery-image-wrapper gallery-image-wrapper-{$i.previewsize}">
			<div class="gallery-image">
				{a href="`$i.link`"}
					{img file=$i->getFile() dimensions="`$dimensions`" title="`$i.title`"}
				{/a}
			</div>
			<div class="gallery-image-title">
				{$i.title}
			</div>

			{if $editor || $userid == $i.uploaderid}
				<ul class="gallery-admin-image-utils controls">
					<li class="control-edit">
						<a href="#" title="Edit {$i->getFileType()}" class="update-link" image="{$i.id}">
							<i class="icon-edit"></i>
							<span>Edit {$i->getFileType()}</span>
						</a>
					</li>
					{if ($i->getFileType() == 'image')}
						<li class="control-rotate-ccw">
							<a href="#" title="Rotate Image CCW" class="rotate-link" image="{$i.id}" rotate="ccw">
								<i class="icon-undo"></i>
								<span>Rotate Image CCW</span>
							</a>
						</li>

						<li class="control-rotate-cw">
							<a href="#" title="Rotate Image CW" class="rotate-link" image="{$i.id}" rotate="cw">
								<i class="icon-repeat"></i>
								<span>Rotate Image CW</span>
							</a>
						</li>
					{/if}
					<li class="control-remove">
						{a href="gallery/images/delete/`$album.id`?image=`$i.id`" title="Remove `$i->getFileType()`" confirm="Confirm deleting `$i->getFileType()`?"}
							<i class="icon-remove"></i>
							<span>Remove {$i->getFileType()}</span>
						{/a}
					</li>
				</ul>
			{/if}
		</div>
	{/foreach}
</div>

<div class="clear"></div>



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
					close: function(){ $(this).remove(); }
				}).dialog('open');

				$dialog.load(
					Core.ROOT_WDIR + 'gallery/images/update/{$album.id}?image=' + image,
					function(){
						$dialog.dialog('option', 'position', 'center');
					}
				);

				return false;
			});

			$('.rotate-link').click(function(){
				var $dialog = $('<div>Rotating...</div>'),
						$this = $(this),
						image = $this.attr('image'),
						windowtitle = 'Rotating',
						$xhr;

				$('body').append($dialog);

				$xhr = $.ajax({
					url: Core.ROOT_WDIR + 'gallery/images/rotate/{$album.id}.json',
					data: {
						image: image,
						rotate: $this.attr('rotate')
					},
					type: 'GET',
					dataType: 'json',
					error: function(){
						$dialog.dialog('destroy').remove();
						alert('There was an error while rotating the image.');
					},
					success: function(){
						window.location.reload();
					}
				});

				$dialog.show().dialog({
					modal:   true,
					autoOpen:false,
					title:   windowtitle,
					width:   '500px',
					close: function(){ $xhr.abort(); $(this).remove(); }
				}).dialog('open');

				return false;
			});
		});
	</script>

{/if}

<script>
	var $container = $('#gallery-images');

	$container.imagesLoaded( function(){
		$container.masonry({
			itemSelector : '.gallery-image-wrapper',
			isAnimated: true,
			columnWidth: 208
		});
	});

	$('.gallery-image-wrapper')
		.mouseover(function(){
			$(this).addClass('hover');
		})
		.mouseout(function(){
			$(this).removeClass('hover');
		});

</script>