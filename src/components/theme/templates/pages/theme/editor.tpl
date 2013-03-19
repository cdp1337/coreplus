{script library="jqueryui"}{/script}

<p class="message-error">
	This editor is not ready for production quite yet, don't use this page yet
</p>

{if $activefile == 'skin'}
	<p class="message-info">
		Editing the skin files will modify the actual theme file.  This means that any further update of the theme from the official sources <b>will overwrite</b> your changes here!
	</p>
{elseif $activefile == 'template'}
	<p class="message-info">
		Editing template files will copy the changes into the current theme.  This ensures that updates to the component will not overwrite your changes here.
	</p>
{/if}

<div class="editor-container">
	<div id="theme-editor-wysiwyg">

		<h2>{$filename}</h2>

		{$form->render()}
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