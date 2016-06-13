{script library="jqueryui"}{/script}
{script library="jquery.masonry"}{/script}
{script src="js/core.fileupload.js"}{/script}
{script library="jqueryui.timepicker"}{/script}
{css src="css/gallery.css"}{/css}
{script library="core.ajaxlinks"}{/script}

{if $uploader}
	{if Core::IsComponentAvailable('jQuery-File-Upload')}
		{$uploadform->render()}
	{else}
		<a class="button update-link" title="Upload New Image">
			<i class="icon icon-upload"></i>
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
		{if $i.previewsize == 'xs'}
			{assign var="dimensions" value="44x44"}
		{elseif $i.previewsize == 'sm'}
			{assign var="dimensions" value="94x94"}
		{elseif $i.previewsize == 'med'}
			{assign var="dimensions" value="194x194"}
		{elseif $i.previewsize == 'lg'}
			{assign var="dimensions" value="394x394"}
		{elseif $i.previewsize == 'xl'}
			{assign var="dimensions" value="794x794"}
		{else}
			{assign var="dimensions" value="194x194"}
		{/if}

		<div class="gallery-image-wrapper gallery-image-wrapper-{$i.previewsize}">
			<div class="gallery-image">
				{if $editor || $userid == $i.uploaderid}
					<div class="gallery-admin-image-utils">
						<ul class="controls" data-proxy-text="Image Controls">
							<li class="control-edit">
								{a href="gallery/images/update/`$album.id`?image=`$i.id`" title="Edit {$i->getFileType()}" class="ajax-link" image="`$i.id`"}
									<i class="icon icon-edit"></i>
									<span>Edit {$i->getFileType()}</span>
								{/a}
							</li>
							{if ($i->getFileType() == 'image')}
								<li class="control-rotate-ccw">
									<a href="#" title="Rotate Image CCW" class="rotate-link" image="{$i.id}" rotate="ccw">
										<i class="icon icon-undo"></i>
										<span>Rotate Image CCW</span>
									</a>
								</li>

								<li class="control-rotate-cw">
									<a href="#" title="Rotate Image CW" class="rotate-link" image="{$i.id}" rotate="cw">
										<i class="icon icon-repeat"></i>
										<span>Rotate Image CW</span>
									</a>
								</li>
							{/if}
							<li class="control-remove">
								{a href="gallery/images/delete/`$album.id`?image=`$i.id`" title="Remove `$i->getFileType()`" confirm="Confirm deleting `$i->getFileType()`?"}
									<i class="icon icon-remove"></i>
									<span>Remove {$i->getFileType()}</span>
								{/a}
							</li>
						</ul>
					</div>
				{/if}
				{a href="`$i.rewriteurl`"}
					{img alt="`$i.title|escape`" file=$i->getPreviewFile() dimensions="`$dimensions`" title="`$i.title|escape`"}
				{/a}
			</div>
			<div class="gallery-image-title">
				{$i.title}
			</div>


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
					width:   '600px',
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
	$(function() {
		var $container = $('#gallery-images');

		$container.masonry({
			itemSelector : '.gallery-image-wrapper',
			isAnimated: true,
			columnWidth: 50
		});

		$('.gallery-image-wrapper')
			.mouseover(function(){
				$(this).addClass('hover');
			})
			.mouseout(function(){
				$(this).removeClass('hover');
			});
	});
</script>