{script library="jqueryui"}{/script}
{script src="js/core.fileupload.js"}{/script}
{script library="jqueryui.timepicker"}{/script}
{css src="css/gallery.css"}{/css}
{script library="core.ajaxlinks"}{/script}


{if $prev}
	<div class="gallery-previous-image">
		{a href="`$prev->getRewriteURL()`" title="`$prev.title`"}
			{img file=$prev->getFile() width="75" height="75" title="`$prev.title`"}
			<i class="icon icon-chevron-left"></i>
		{/a}
	</div>
{/if}

{if $next}
	<div class="gallery-next-image">
		{a href="`$next->getRewriteURL()`" title="`$next.title`"}
			{img file=$next->getFile() width="75" height="75" title="`$next.title`"}
			<i class="icon icon-chevron-right"></i>
		{/a}
	</div>
{/if}

<div class="clear"></div>


<div class="gallery-{$image->getFileType()}-details">

	{*
	Yeah I know this is the audio view, but the variable is called image... deal with it
	*}

	{**
	 * Only a subset of video codecs are actually supported to be played inline.
	 * M4V, OGG (/OGV), WEBMV, and FLV.
	 *}

	{if ( $image->getFileExtension() == 'm4a' || $image->getFileExtension() == 'mp4' ) }
		{assign var='playable' value=true}
		{assign var='playkey' value='m4a'}
	{elseif ( $image->getFileExtension() == 'mp3') }
		{assign var='playable' value=true}
		{assign var='playkey' value='mp3'}
	{elseif ( $image->getFileExtension() == 'ogg' || $image->getFileExtension() == 'oga' ) }
		{assign var='playable' value=true}
		{assign var='playkey' value='oga'}
	{elseif $image->getFileExtension() == 'webma' }
		{assign var='playable' value=true}
		{assign var='playkey' value='webma'}
	{elseif ( $image->getFileExtension() == 'wav') }
		{assign var='playable' value=true}
		{assign var='playkey' value='wav'}
	{elseif $image->getFileExtension() == 'fla'}
		{assign var='playable' value=true}
		{assign var='playkey' value='fla'}
	{else}
		{assign var='playable' value=false}
	{/if}

	{if $playable}

		<div id="jquery_jplayer_1" class="jp-jplayer"></div>
		<div id="jp_container_1" class="jp-audio">
			<div class="jp-type-single">
				<div class="jp-gui jp-interface">
					<ul class="jp-controls">
						<li><a href="javascript:;" class="jp-play" tabindex="1">play</a></li>
						<li><a href="javascript:;" class="jp-pause" tabindex="1">pause</a></li>
						<li><a href="javascript:;" class="jp-stop" tabindex="1">stop</a></li>
						<li><a href="javascript:;" class="jp-mute" tabindex="1" title="mute">mute</a></li>
						<li><a href="javascript:;" class="jp-unmute" tabindex="1" title="unmute">unmute</a></li>
						<li><a href="javascript:;" class="jp-volume-max" tabindex="1" title="max volume">max volume</a></li>
					</ul>
					<div class="jp-progress">
						<div class="jp-seek-bar">
							<div class="jp-play-bar"></div>

						</div>
					</div>
					<div class="jp-volume-bar">
						<div class="jp-volume-bar-value"></div>
					</div>
					<div class="jp-current-time"></div>
					<div class="jp-duration"></div>
					<ul class="jp-toggles">
						<li><a href="javascript:;" class="jp-repeat" tabindex="1" title="repeat">repeat</a></li>
						<li><a href="javascript:;" class="jp-repeat-off" tabindex="1" title="repeat off">repeat off</a></li>
					</ul>
				</div>
				<div class="jp-title">
					<ul>
						<li>{$image.title}</li>
					</ul>
				</div>
				<div class="jp-no-solution">
					<span>Update Required</span>
					To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
				</div>
			</div>
		</div>

	{else}

		{img file=$image->getFile() width="48" height="48" title="`$image.title`"}
		<a href="{$image->getFile()->getURL()}">
			{$image.title}
		</a><br/>
		Filesize: {Core::FormatSize($image->getFile()->getFilesize())}<br/>
		Filetype: {$image->getFile()->getMimetype()}

	{/if}

</div>

<div class="gallery-image-description">
	{$image.description}
</div>

<div class="gallery-image-keywords">
	{$image.keywords}
</div>



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
					$(this).remove();
				}
			}).dialog('open');

			$dialog.load(
				Core.ROOT_WDIR + 'gallery/images/update/{$album.id}?image=' + image,
				function(){
					$dialog.dialog('option', 'position', 'center');
				}
			);

			return false;
		});

	});
</script>

{/if}

{if $playable}
	{script library="jplayer"}{/script}
	<script type="text/javascript">
		$(function(){
			$("#jquery_jplayer_1").jPlayer({
				ready: function () {
					$(this).jPlayer("setMedia", {
						"{$playkey}": "{$image->getFile()->getURL()}"
					});
				},
				swfPath: "{dirname(Core::ResolveAsset('swf/Jplayer.swf'))}",
				supplied: "{$playkey}",
				wmode: "window"
			});
		});
	</script>
{/if}