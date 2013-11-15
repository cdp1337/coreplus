
{$form->render('head')}
{$form->render('body')}

<p class="message-tutorial">
	Below is a preview of the data found from the CSV file.
</p>
<table class="listing">
	<tr>
		{foreach $header as $val}
			<th>{$val}</th>
		{/foreach}
	</tr>
	{foreach $preview as $line}
		<tr>
			{foreach $line as $val}
				<td>{$val}</td>
			{/foreach}
		</tr>
	{/foreach}
	{if $total > 11}
		<tr>
			<td colspan="{$col_count}">
				... and {($total-10)} more records
			</td>
		</tr>
	{/if}
</table>


<br/>
{a href="/user/import/cancel" class="button"}
	Cancel Import
{/a}

<input type="submit" value="Perform Import"/>

{$form->render('foot')}