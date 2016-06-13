<div class="page-search-widget">
	{if $title}
		<h3>{$title}</h3>
	{/if}

	<form action="{$url}" method="GET" class="form-orientation-vertical page-search-widget clearfix">

		<div class="formelement formelementtext">
			<div class="form-element-value">
				<input type="text" name="q" value="{$query}" placeholder="Search Site"/>
				<i class="icon icon-search"></i>
			</div>
		</div>
		<input type="submit" value="Search" class="submit-button"/>

	</form>
</div>

{script location="foot"}<script>
	$('.page-search-widget').find('i').css('cursor', 'pointer').click(function(){
		$(this).closest('form').submit();
		return false;
	});
</script>{/script}