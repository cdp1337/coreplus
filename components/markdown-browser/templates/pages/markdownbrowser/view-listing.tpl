<ul>
	{foreach from=$directories item=l}
		<li>
			{a href="`$l.href`"}{$l.title}{/a}
		</li>
	{/foreach}

	{foreach from=$files item=l}
		<li>
			{a href="`$l.href`"}{$l.title}{/a}
		</li>
	{/foreach}
</ul>
