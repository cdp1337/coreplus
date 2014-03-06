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
<br/>

<h2>Header #2</h2>
<h3>Header #3</h3>

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