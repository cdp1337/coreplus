{foreach $drivers as $name => $d}
	<div class="user-register-include user-authdriver-{$name}">
		{$d->renderRegister()}
	</div>
{/foreach}