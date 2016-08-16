{if $manager_available}
	<p class="message-tutorial">
		A more {a href="/gpgkeymanager"}advanced GPG key manager{/a} is available!
	</p>
{/if}

{if sizeof($keys)}
	<table class="listing">
		<tr>
			<th>Key</th>
			<th>Date Created</th>
			<th>Owner</th>
			<th>&nbsp;</th>
		</tr>
		{foreach from=$keys item=k}
			<tr>
				<td>{$k->id_short}</td>
				<td>{date format="SD" $k->created}</td>
				<td>{$k->getName()} {$k->getEmail()}</td>
				<td>
					{a href="/updater/keys/delete/`$k->id_short`" confirm="Really delete key `$k->id_short`?" class="control-delete"}Delete{/a}
				</td>
			</tr>
		{/foreach}
	</table>
{else}
	<p class="message-info">No keys are installed on the system, go {a href="updater/keys/import"}add one{/a}</p>
{/if}