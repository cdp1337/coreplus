<div class="page-search-widget">
	{if $display_settings.title}
		<h3>{$display_settings.title}</h3>
	{/if}

	<form action="{$url}" method="GET" class="form-orientation-vertical page-search-widget clearfix">

		<div class="formelement formelementtext">
			<div class="form-element-value">
				<input type="text" name="q" value="{$query}" placeholder="{$display_settings.placeholder}"/>
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