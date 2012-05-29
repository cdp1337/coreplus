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

	<p id="updates"></p>
	<script>$(function(){ perform_check($('#updates')); });</script>
{/if}

{if $sitecount > 1}
	<p>
		There are {$sitecount} update repositories currently enabled.  {a href='updater/repos'}Manage Them{/a}
	</p>

	<p id="updates"></p>
	<script>$(function(){ perform_check($('#updates')); });</script>
{/if}


<table>
	<tr>
		<th>Component</th>
		<th>Version</th>
		<th>Installed</th>
		<th>Enabled</th>
		<th>&nbsp;</th>
	</tr>
	{foreach from=$components item=c}
		<tr>
			<td>{$c->getName()}</td>
			<td>{$c->getVersion()}</td>
			<td>{if $c->isInstalled()}yes{/if}</td>
			<td>{if $c->isEnabled()}yes{/if}</td>
			<td>
				Disable
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
</script>