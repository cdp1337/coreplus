{if $title}
	<h3>{$title}</h3>
{/if}

{foreach $links as $l}
	{a href="`$l.baseurl`"}
		{$l.title}
	{/a}
{/foreach}