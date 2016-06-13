<h1>Test General UI/UX (H1 tag)</h1>

<form action="" method="GET">
	<select name="skin">
		{foreach $skins as $s => $t}
			<option value="{$s}" {if $skin == $s}selected="selected"{/if}>{$t}</option>
		{/foreach}
	</select>

	<input type="submit" value="Set"/>
</form>
<hr/>

{foreach ['note', 'info', 'success', 'deprecated', 'warning', 'error', 'tutorial'] as $class}
	<p class="message-{$class}">This is a "message-{$class}" type message box!</p>
{/foreach}


<a href="#">
	Generic Link
</a>
<a class="button" href="#">
	Generic Button/Link
</a>
<a class="button" href="#">
	<i class="icon icon-ok"></i>
	<span>Generic Button/Link (Now with 100% more icons!)</span>
</a>
<br/>

<div>
<ul class="controls">
	<li>
		<a href="#">
			<i class="icon icon-edit"></i>
			<span>Edit Something</span>
		</a>
	</li>
	<li>
		<a href="#">
			<i class="icon icon-delete"></i>
			<span>Delete Something</span>
		</a>
	</li>
	<li>
		<a href="#">
			<span>Non-descript something</span>
		</a>
	</li>
	<li>
		<span>Not even a link!</span>
	</li>
</ul>
</div>
<br/>

<fieldset class="collapsible collapsed">
	<legend>Collapsed Fieldset w/legend (click me!)</legend>
	<div>
		Content!
	</div>
</fieldset>

<fieldset class="collapsible collapsed">
	<div class="fieldset-title">
		Collapsed Fieldset w/.fieldset-title (click me!)
		<i class="icon icon-chevron-down expandable-hint"></i>
		<i class="icon icon-chevron-up collapsible-hint"></i>
	</div>
	<div>
		Content!
	</div>
</fieldset>

<h2>Header #2</h2>
<h3>Header #3</h3>
<h4>Header #4</h4>

{$lorem_p}

<ul>
	{foreach $lis as $li}
		<li>{$li}</li>
	{/foreach}
</ul>

<ol>
	{foreach $lis as $li}
		<li>{$li}</li>
	{/foreach}
</ol>