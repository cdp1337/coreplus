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

{if $content}
	<div id="theme-editor-wysiwyg">
		<form>
			<textarea id="code" name="code">
			{$content}
			</textarea>
			<br/>
			<input type="submit" name="submit" value="Update"/>
		</form>
	</div>
{/if}

{if $image}
	{img src="`$image`"}
{/if}

<div id="theme-editor-browser">
	<div class="browser-container">
		<h2>Stylesheets <i class="icon icon-plus-sign"></i></h2>
		<ul class="theme-editor styles">
			{foreach $styles as $f}
				<li><a href="/themeeditor?txt={$f}">{$f|regex_replace:"|^(.*[\\\/])|":""}</a></li>
			{/foreach}
		</ul>
	</div>

	<div class="browser-container">
		<h2>Page Templates <i class="icon icon-plus-sign"></i></h2>
		<ul class="theme-editor skins">
		{foreach $skins as $f}
			<li><a href="/themeeditor?txt={$f}">{$f|regex_replace:"|^(.*[\\\/])|":""}</a></li>
		{/foreach}
		</ul>
	</div>

	<div class="browser-container">
		<h2>Images <i class="icon icon-plus-sign"></i></h2>
		<ul class="theme-editor images">
		{foreach $images as $f}
			<li><a href="/themeeditor?img={$f}">{$f|regex_replace:"|^(.*[\\\/])|":""}</a></li>
		{/foreach}
		</ul>
	</div>

	<div class="browser-container">
		<h2>Icons <i class="icon icon-plus-sign"></i></h2>
		<ul class="theme-editor icons">
		{foreach $icons as $f}
			<li><a href="/themeeditor?img={$f}">{$f|regex_replace:"|^(.*[\\\/])|":""}</a></li>
		{/foreach}
		</ul>
	</div>

	<div class="browser-container" style="display:none;">
		<h2>Fonts <i class="icon icon-plus-sign"></i></h2>
		<ul class="theme-editor fonts">
		{foreach $fonts as $f}
			<li><a href="/themeeditor?font={$f}">{$f|regex_replace:"|^(.*[\\\/])|":""}</a></li>
		{/foreach}
		</ul>
	</div>
</div>

<div class="clear"></div>

{if $content}<script>
$(function(){

	var editor = CodeMirror.fromTextArea(document.getElementById("code"), {
		//mode: "application/css",
		theme: 'ambiance',
		lineNumbers: true,
		lineWrapping: true,
		onCursorActivity: function() {
			editor.setLineClass(hlLine, null, null);
			hlLine = editor.setLineClass(editor.getCursor().line, null, "activeline");
		}
	});
	var hlLine = editor.setLineClass(0, "activeline");
});
</script>{/if}

<script>
	$('.browser-container h2').click(function(){
		$(this).parent().find('.icon')
			.toggleClass('icon-plus-sign')
			.toggleClass('icon-minus-sign');

		$(this).parent().find('.theme-editor').slideToggle();

	});

	$('.theme-editor').first().slideToggle().parent().find('.icon')
		.toggleClass('icon-plus-sign')
		.toggleClass('icon-minus-sign');
</script>