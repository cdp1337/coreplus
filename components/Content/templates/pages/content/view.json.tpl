{strip}
{insertable name="header" title="Header" assign="header"}Header{/insertable}
{insertable name="body" title="Body Content" assign="body"}
		<p>
			This is some example content!
		</p>
	{/insertable}
{json_encode header="$header" body="$body"}
{/strip}