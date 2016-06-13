<table class="listing">
	<tr>
		<td>Site</td>
		<td>Description</td>
		<td>Status</td>
		<td>&nbsp;</td>
	</tr>
	{foreach from=$sites item=site}
		<tr>
			<td>
				{$site.url}
			</td>
			<td>
				{$site.description|truncate:200}
			</td>
			<td>
				{if $site->isValid()}
					<span class="repository-status-good" title="Repository active and ready!">
						<i class="icon icon-check-circle-o"></i> OK
					</span>
				{elseif !$site->getFile()->exists()}
					<span class="repository-status-bad" title="Repository was not found, is the URL correct?">
						<i class="icon icon-times-circle"></i> Repo Not Found
					</span>
				{elseif $site->getFile()->getStatus() == 410}
					<span class="repository-status-bad" title="Repository license has expired, please renew your license.">
						<i class="icon icon-exclamation-triangle"></i> License Expired
					</span>
				{elseif $site->getFile()->requiresAuthentication()}
					<span class="repository-status-bad" title="Repository requires a username/password, did you enter them in correctly?">
						<i class="icon icon-ban"></i> Bad Authentication
					</span>
				{else}
					<span class="repository-status-bad" title="Repository bad or other unknown status?...">
						<i class="icon icon-question-circle"></i> Bad Repo [ {$site->getFile()->getStatus()} ]
					</span>
				{/if}
			</td>
			<td>
				<ul class="controls">
					<li>
						{a href="/updater/repos/edit/`$site.id`"}
							<i class="icon icon-edit"></i>
							<span>Edit Repository</span>
						{/a}
					</li>
					<li>
						{a href="/updater/repos/delete/`$site.id`" confirm="Really remove repo?"}
							<i class="icon icon-remove"></i>
							<span>Remove Repository</span>
						{/a}
					</li>
				</ul>
			</td>
		</tr>
	{/foreach}
</table>