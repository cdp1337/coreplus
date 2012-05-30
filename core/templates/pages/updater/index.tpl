{script library="jquery"}{/script}

{if $sitecount == 0}
	<p class="message-error">
		There are no update repositories currently enabled.  Go {a href='updater/repos'}Manage Them{/a}!
	</p>
{/if}

{if $sitecount == 1}
	<p>
		There is {$sitecount} update repository currently enabled.  {a href='updater/repos'}Manage Them{/a}
	</p>

	<p>
		<span id="updates"></span>
		<span>Browse Packages</span>
	</p>

	<script>$(function(){ perform_check($('#updates')); });</script>
{/if}

{if $sitecount > 1}
	<p>
		There are {$sitecount} update repositories currently enabled.  {a href='updater/repos'}Manage Them{/a}
	</p>

	<p>
		<span id="updates"></span>
		<span>Browse Packages</span>
	</p>
	<script>$(function(){ perform_check($('#updates')); });</script>
{/if}


<table class="listing">
	<tr>
		<th>Component</th>
		<th>Version</th>
		<th>Installed</th>
		<th>Enabled</th>
		<th>&nbsp;</th>
	</tr>
	{foreach from=$components item=c}
		<tr componentname="{$c->getName()}">
			<td>{$c->getName()}</td>
			<td>{$c->getVersion()}</td>
			<td>{if $c->isInstalled()}yes{else}---{/if}</td>
			<td>{if $c->isEnabled()}yes{else}---{/if}</td>
			<td>
				{if $c->isEnabled()}
					<a href="#" class="disable-link">Disable</a>
				{else}
					<a href="#" class="enable-link">Enable</a>
				{/if}
			</td>
		</tr>
	{/foreach}
</table>


<!-- @todo Move this to its own file. -->
<script>
	function perform_check($target){
		$target.html('Checking for updates...');

		$.ajax({
			url: Core.ROOT_WDIR + 'updater/check.json',
			type: 'get',
			dataType: 'json',
			success: function(d){
				if(d){
					$target.html('<a href="' + Core.ROOT_WDIR + 'updater/update">Updates Available!</a>');
				}
				else{
					$target.html('No updates available');
				}
			},
			error: function(){
				$target.html('An error occured while checking for updates.');
			}
		});
	}

	$(function(){
		var xhr = null;

		// This function is the exact same for enable or disable, just the verbiage is changed slightly.
		$('.disable-link, .enable-link').click(function(){
			var $this = $(this),
				$tr = $this.closest('tr'),
				name = $tr.attr('componentname'),
				action = ($this.text() == 'Enable') ? 'enable' : 'disable';

			// Cancel the last request.
			if(xhr !== null) xhr.abort();

			// Do a dry run
			xhr = $.ajax({
				url: Core.ROOT_WDIR + 'updater/component/' + action + '/' + name + '?dryrun=1',
				type: 'POST',
				dataType: 'json',
				success: function(r){
					// If there was an error, "message" will be populated.
					if(r.message){
						alert(r.message);
						return;
					}

					// If the length is more than one and the user accepts that more than one component will be disabled,
					// or if there's only one.
					if(
						(r.changes.length > 1 && confirm('The following components will be ' + action + 'd: \n' + r.changes.join('\n')) ) ||
						(r.changes.length == 1)
					){
						xhr = $.ajax({
							url: Core.ROOT_WDIR + 'updater/component/' + action + '/' + name + '?dryrun=0',
							type: 'POST',
							dataType: 'json',
							success: function(r){
								// Done, just reload the page!
								Core.Reload();
							},
							error: function(jqxhr, data, error){
								alert(error);
							}
						});
					}
				},
				error: function(jqxhr, data, error){
					alert(error);
				}
			});
			return false;
		});
	});
</script>