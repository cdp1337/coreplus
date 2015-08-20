<div class="content-body">
	{insertable name="page_h1" assign="page_h1" title="Page Heading" type="text" description="The page H1 tag."}
		{if $page_h1}<h1>{$page_h1}</h1>{/if}
	{/insertable}

	{insertable name="body" title="Body Content" type="markdown"}
		This is some example content.  Insert your own content here.
	{/insertable}
</div>
{widgetarea name="After Content"}