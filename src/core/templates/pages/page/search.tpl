<form action="{link "/page/search"}" method="GET" class="form-orientation-vertical page-search-form">
	<div class="formelement formelementtext">
		<div class="form-element-value">
			<input type="text" name="q" value="{$query|escape}" placeholder="Search Site"/>
			<i class="icon icon-search"></i>
		</div>
	</div>
	<input type="submit" value="Search" class="submit-button"/>
</form>

{script location="foot"}<script>
$('.page-search-form').find('i').css('cursor', 'pointer').click(function(){
	$(this).closest('form').submit();
	return false;
});
</script>{/script}

{if !$results->getCount()}
	<p class="message-error">No results found.</p>
{/if}

{$results->render()}