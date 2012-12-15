{script library="jqueryui"}{/script}
{script src="js/codemirror.js"}{/script}
{css src="css/codemirror.css"}{/css}
{script src="mode/css/css.js"}{/script}
{script src="mode/diff/diff.js"}{/script}
{script src="mode/htmlembedded/htmlembedded.js"}{/script}
{script src="mode/htmlmixed/htmlmixed.js"}{/script}
{script src="mode/javascript/javascript.js"}{/script}
{script src="mode/less/less.js"}{/script}
{script src="mode/markdown/markdown.js"}{/script}
{script src="mode/mysql/mysql.js"}{/script}
{script src="mode/perl/perl.js"}{/script}
{script src="mode/php/php.js"}{/script}
{script src="mode/properties/properties.js"}{/script}
{script src="mode/python/python.js"}{/script}
{script src="mode/smarty/smarty.js"}{/script}
{script src="mode/xml/xml.js"}{/script}

<div class="editor-container" xmlns="http://www.w3.org/1999/html">
	<div id="theme-editor-wysiwyg">

			{if $content}
				<h2>{$filename}</h2>

				{$form->render()}

			{/if}
	</div>

	<div id="theme-editor-browser">
		<div class="left">
			<div class="browser-container {if $activefile eq 'style'}current-file{/if}">
				<h2>Stylesheets <i class="icon icon-plus-sign"></i></h2>
				<ul class="theme-editor styles">
				{foreach $styles as $k => $f}
					{if $f instanceof 'File_local_backend'}
						<li>{a href="/themeeditor?css={$f->getFilename()}" alt="{$f->getBasename()}"}{$k}{/a}</li>
					{/if}
				{/foreach}

				{foreach $styles as $f}
					{if $f instanceof 'Directory_local_backend'}
						{if $f->getBasename() != "images" && $f->getBasename() != "skin"}
						<li>{$f->getBasename()} <i class="icon icon-plus-sign"></i>
							<ul class="sub">
								{foreach $f->ls() as $s}
									<li>{a href="/themeeditor?css={$s->getFilename()}" alt="{$f->getBasename()}"}{$s->getBasename()|truncate:26}{/a}</li>
								{/foreach}
							</ul>
						</li>
						{/if}
					{/if}
				{/foreach}
				</ul>
			</div>

			<div class="browser-container {if $activefile eq 'template'}current-file{/if}">
				<h2>Theme Skins <i class="icon icon-plus-sign"></i></h2>
				<ul class="theme-editor skins">
				{foreach $skins as $f}
					{if $f instanceof 'File_local_backend'}
						<li>{a href="/themeeditor?tpl={$f->getFilename()}" alt="{$f->getBasename()}"}{$f->getBasename()}{/a}</li>
					{/if}
				{/foreach}

				{foreach $skins as $f}
					{if $f instanceof 'Directory_local_backend'}
						<li>{$f->getBasename()} <i class="icon icon-plus-sign"></i>
							<ul class="sub">
								{foreach $f->ls() as $s}
									<li>{a href="/themeeditor?tpl={$s->getFilename()}" alt="{$f->getBasename()}"}{$s->getBasename()|truncate:26}{/a}</li>
								{/foreach}
							</ul>
						</li>

					{/if}
				{/foreach}
				</ul>
			</div>

		</div>
		<div class="right">
			<i class="icon-backward"></i>
		</div>
	</div>
</div>

<div class="clear"></div>

<div id="revisions">
	<h2>Revisions</h2>

	{if !$revisions}
		There are no revisions for this file, yet.
	{else}
		<p>Click a revision to restore, or <span id="revert">Revert Changes</span></p>
		<ul>
			{foreach $revisions as $r}
				<li class="revision">
					<span class="revision-title">{$r.filename|basename}</span> <span class="revision-date">{$r.updated|date_format:"%Y-%m-%d %I:%M:%S %p"}</span> [delete]
					<br />
					<span class="revision-content" style="display:none;">{$r.content}</span>
				</li>
			{/foreach}
		</ul>
	{/if}
</div>

<div id="dialog-confirm" title="Restore revision?">
	<p>Don't panic. You can still click revert :)</p>
</div>

{if $content}<script>
$(function(){

	var editor = CodeMirror.fromTextArea(document.getElementById("formtextareainput-model-content"), {
		theme: 'ambiance',
		lineNumbers: true,
		lineWrapping: true,
		onCursorActivity: function() {
			editor.setLineClass(hlLine, null, null);
			hlLine = editor.setLineClass(editor.getCursor().line, null, "activeline");
		}
	});

	var hlLine = editor.setLineClass(0, "activeline");

	var origContent = $('#formtextareainput-model-content').html();

	$('#revert').click(function(){
		editor.setValue(origContent);
	});

	$('.revision .revision-title').click(function(){

		var revContent = $(this).parent().find('.revision-content').html();

		$( "#dialog-confirm" ).dialog({
			resizable: false,
			height:180,
			width:350,
			modal: true,
			buttons: {
				"Restore revision": function() {
					editor.setValue(revContent);
					$( this ).dialog( "close" );
				},
				Cancel: function() {
					$( this ).dialog( "close" );
				}
			}
		});
	});
});
</script>{/if}


<script>
	$(function(){
		if( $('.current-file').length > 0){
			$('.browser-container.current-file').find('.theme-editor').slideToggle().parent().find('h2 .icon')
				.toggleClass('icon-plus-sign')
				.toggleClass('icon-minus-sign');
		} else {
			$('.theme-editor').first().slideToggle().parent().find('h2 .icon')
				.toggleClass('icon-plus-sign')
				.toggleClass('icon-minus-sign');
		}
	});

	$('#theme-editor-browser .right').toggle(
		function(){
			$(this).find('i')
				.toggleClass('icon-backward')
				.toggleClass('icon-forward');
			$(this).parent().animate({ left: '-=314' }, 500);
		},
		function(){
			$(this).find('i')
				.toggleClass('icon-backward')
				.toggleClass('icon-forward');
			$(this).parent().animate({ left: '0' }, 500);
		}
	);

	$('.browser-container h2').click(function(){
		$(this).parent().find('h2 .icon')
			.toggleClass('icon-plus-sign')
			.toggleClass('icon-minus-sign');

		$(this).parent().find('.theme-editor').slideToggle();

	});

	$('li .icon').click(function(){
		$(this)
			.toggleClass('icon-plus-sign')
			.toggleClass('icon-minus-sign')
			.parent().find('.sub').slideToggle();
	});
</script>