<p>There is at least one issue that needs attention with your site.</p>

{foreach $checks as $check}
	<p>
		<strong>{$check->title}</strong><br/>
		{$check->description}
		
		{if $check->link}
			<br/><br/>
			{a href="`$check->link`" title="t:STRING_FIX"}{t 'STRING_FIX'}{/a}
		{/if}
	</p>
	<hr/>
{/foreach}