Unassigned Directories
<table class="listing">
	<tr>
		<th>Directory</th>
		<th width="100">&nbsp;</th>
	</tr>
	{foreach from=$newdirectories item=dir}
		<tr>
			<td>{$dir}</td>
			<td>
				<ul class="controls">
					<li class="add">{a href="/markdownbrowser/update/`$dir`"}Create Page{/a}</li>
				</ul>
			</td>
		</tr>
	{/foreach}
	{if !count($newdirectories)}<tr><td colspan="2">No Listings</td></tr>{/if}
</table>


<br/><br/>
Current Directory Listings
<table class="listing">
	<tr>
		<th>Directory</th>
		<th width="100">&nbsp;</th>
	</tr>
	{foreach from=$pages item=dir}
		<tr>
			<td>{$dir}</td>
			<td>
				<ul class="controls">
					<li class="view">{a href="/markdownbrowser/view/`$dir`"}View{/a}</li>
					<li class="edit">{a href="/markdownbrowser/update/`$dir`"}Edit{/a}</li>
					<li class="delete">{a href="/markdownbrowser/delete/`$dir`" confirm="Are you sure you want to remove the page listing for `$dir`?"}Delete{/a}</li>
				</ul>
			</td>
		</tr>
	{/foreach}
	{if !count($pages)}<tr><td colspan="2">No Listings</td></tr>{/if}
</table>


{if count($orphaned)}
	<br/><br/>
	Orphaned Directory Listings
	<table class="listing">
		<tr>
			<th>Directory</th>
			<th width="100">&nbsp;</th>
		</tr>
		{foreach from=$orphaned item=dir}
			<tr>
				<td>{$dir}</td>
				<td>
					<ul class="controls">
						<li class="delete">{a href="/markdownbrowser/delete/`$dir`" confirm="Are you sure you want to remove the page listing for `$dir`?"}Delete{/a}</li>
					</ul>
				</td>
			</tr>
		{/foreach}
	</table>
{/if}