{script library="jqueryui"}{/script}
{if Core::IsComponentAvailable('codemirror')}
	{script library="codemirror_html"}{/script}
	{script library="codemirror_smarty"}{/script}
	{script library="codemirror_css"}{/script}
	{css href="assets/codemirror/theme/ambiance.css"}{/css}
{/if}


<div class="editor-container">
	<div id="theme-editor-wysiwyg">

		<h2>
			{$activefile|ucwords}
			{$file}
			{if $revision && !$islatest}
				(as of {date date="`$revision.updated`"})
			{/if}
		</h2>
		<br/>

		{$form->render()}
	</div>
</div>

<div class="clear"></div>

<div id="revisions">
	<h2>Revisions</h2>

	{if !$revisions}
		There are no revisions for this file, yet.
	{else}
		<p>Click a revision to load or restore it.</p>
		<ul>
			{foreach $revisions as $i => $r}
				<li class="revision">
					<span class="revision-date">
						{if $r.comment}
							{$r.comment} ({date date="`$r.updated`"})
						{else}
							Version as of {date date="`$r.updated`"}
						{/if}

					</span>
					{if $revision && $revision.id == $r.id}
						<i class="icon icon-star"></i>
					{else}
						{a href="?`$activefile`=`$file`&revision=`$r.id`" title="Load File"}<i class="icon icon-view"></i>{/a}
					{/if}
				</li>
			{/foreach}
		</ul>
	{/if}
</div>

<!--
<div id="dialog-confirm" title="Restore revision?">
	<p>Don't panic. You can still click revert :)</p>
</div>
-->

{if $content}<script>
$(function(){

	var $content = $('#formtextareainput-model-content'),
		$elementwrapper = $content.closest('.formelement-labelinputgroup');

	$elementwrapper.css('width', '99%');


	{if Core::IsComponentAvailable('codemirror')}
		var editor = CodeMirror.fromTextArea(document.getElementById("formtextareainput-model-content"), {
			//theme: 'ambiance',
			lineNumbers: true,
			lineWrapping: true,
			mode: '{$mode}',
			/*
			onCursorActivity: function() {
				editor.setLineClass(hlLine, null, null);
				hlLine = editor.setLineClass(editor.getCursor().line, null, "activeline");
			}*/
			__end: null
		});
	{/if}
});
</script>{/if}
