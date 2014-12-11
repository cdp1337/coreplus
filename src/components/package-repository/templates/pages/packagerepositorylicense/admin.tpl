{$generate_form->render()}


{$listings->render('head')}
	{foreach $listings as $l}
		<tr>
			<td>{$l.id}</td>
			<td>
				<a href="#" class="toggle-password" title="Click to toggle password">
					<span class="plain-text"><i class="icon-eye"></i></span>
					<span class="password" style="display:none;"><i class="icon-eye-slash"></i></span>
				</a>
				<span class="plain-text">**********</span>
				<span class="password" style="display:none;">{$l.password}</span>
			</td>
			<td>{$l.comment}</td>
			<td>{$l.expires}</td>
			<td>{$l.ip_restriction}</td>
			<td>
				{controls model=$l}
			</td>
		</tr>
	{/foreach}
{$listings->render('foot')}

{script location="foot"}<script>
	$('.toggle-password').click(function() {
		$(this).closest('td').find('.plain-text, .password').toggle();
		return false;
	});
</script>{/script}