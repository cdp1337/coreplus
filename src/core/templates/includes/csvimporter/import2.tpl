
{$form->render('head')}
{*$form->render('body')*}

{$form->render('has_header')}

<p class="message-tutorial">
	Below is a preview of the data found from the CSV file.
</p>
<table class="listing">
	<tr>
		{foreach $header_element_names as $n}
			<td>{$form->render($n)}</td>
		{/foreach}
	</tr>
	{foreach $preview as $line}
		<tr>
			{foreach $line as $val}
				<td>{$val|escape}</td>
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
<a href="#" class="button cancel-import-trigger">
	Cancel Import
</a>

<input type="submit" value="Perform Import"/>

{$form->render('foot')}

{script location="foot"}<script>
	$('.cancel-import-trigger').click(function() {
		var $form = $(this).closest('form'),
			$cancelInput = $form.find('input[name=cancel]');

		$cancelInput.val('1');
		$form.submit();

		return false;
	});
</script>{/script}
