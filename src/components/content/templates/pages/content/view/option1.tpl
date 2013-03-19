<h2>{insertable name="header" title="Header"}Header{/insertable}</h2>

<div class="content-body">
	{insertable name="body" title="Body Content"}
		<p>
			This is some example content!
		</p>
	{/insertable}

	{insertable name="img1" type="file" assign="img1"}
		{img src="public/insertable/`$img1`" placeholder="generic" dimensions="800x400" title="`$img1`"}
	{/insertable}
</div>