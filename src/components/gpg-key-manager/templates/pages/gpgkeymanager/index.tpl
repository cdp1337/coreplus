{css}<style>
	.gpg-keys-signature-count {
		border: 1px solid rgba(102, 102, 102, 0.5);
		border-radius: 0.5em;
		padding: 0 0.25em;
		margin-right: 0.5em;
	}
</style>{/css}

{if sizeof($keys)}
<table class="listing">
	<tr>
		<th>Key ID</th>
		<th>Encryption</th>
		<th>Created</th>
		<th>Expires</th>
		<th>Identities</th>
		<th>&nbsp;</th>
	</tr>
	{foreach $keys as $k}
		<tr>
			<td>
				{if $k.private}
					<i class="icon icon-lock" title="Secret Key Available"></i>
				{/if}
				
				{$k.public->id_short}
			</td>
			<td>
				{$k.public->encryptionBits} / {$k.public->encryptionType}
			</td>
			<td>
				{date format="SD" $k.public->created}
			</td>
			<td>
				{if $k.public->expires}
					{date format="SD" $k.public->expires}
				{else}
					NEVER
				{/if}
			</td>
			<td>
				{if $k.public->getPhoto()}
					<img src="{$k.public->getPhoto()->getPreviewURL('64x64')}" style="float:left;"/>
				{/if}
				{foreach $k.public->uids as $uid}
					{assign var='trust' value="`$uid->getTrustLevel()`"}
					
					{if $trust >= 0 && $uid->comment}
						{* Display the comment on the line above the UID info if it is valid. *}
						{$uid->comment}<br/>
					{/if}

					{if $trust == 0}
						<span class="validity-level" title="Unknown Trust Level">
							&nbsp;&nbsp;
						</span>
					{elseif $trust == -1}
						<span class="validity-level" title="REVOKED">
							<i class="icon icon-exclamation"></i>
						</span>
					{elseif $trust == -2}
						<span class="validity-level" title="Expired">
							<i class="icon icon-clock-o"></i>
						</span>
					{elseif $trust == -3}
						<span class="validity-level" title="Trust Calculation Failed!">
							<i class="icon icon-exclamation-triangle"></i>
						</span>
					{elseif $trust == -999}
						<span class="validity-level" title="NEVER TRUST">
							<i class="icon icon-thumbs-down"></i>
						</span>
					{elseif $trust == 0}
						<span class="validity-level" title="Unknown Trust Level (Not enough info)">
							<i class="icon icon-star-o"></i>
						</span>
					{elseif $trust == 1}
						<span class="validity-level" title="Marginally Trusted">
							<i class="icon icon-star-half"></i>
						</span>
					{elseif $trust == 2}
						<span class="validity-level" title="Fully Trusted">
							<i class="icon icon-star"></i>
						</span>
					{/if}
					
					{if $trust < 0}
						<strike>{$uid->fullname} &lt;{$uid->email}&gt;</strike>
					{else}
						{if sizeof($uid->sigs)}
							<span class="gpg-keys-signature-count" title="Signed by {sizeof($uid->sigs)} people!">
								<i class="icon icon-thumbs-up"></i> {sizeof($uid->sigs)}	
							</span>
						{/if}
						
						{$uid->fullname} &lt;{$uid->email}&gt;
					{/if}
					<br/>
				{/foreach}
			</td>
			<td>
				<ul class="controls" data-proxy-force="1">
					<li>
						{a href="/gpgkeymanager/getkey/`$k.public->fingerprint`?type=public" class="ajax-link" title="View Public Key `$k.public->id_short`"}
							<i class="icon icon-eye"></i>
							<span>View Public Key</span>
						{/a}
					</li>
					<li>
						{a href="/gpgkeymanager/getkey/`$k.public->fingerprint`?type=public&download=1" target="_blank"}
							<i class="icon icon-download"></i>
							<span>Download Public Key</span>
						{/a}
					</li>
					
					{if !$k.private}
						<li>
							{a href="/gpgkeymanager/deletekey/`$k.public->fingerprint`?type=public" confirm="Are you sure you want to completely delete the public key `$k.public->id_short`?"}
								<i class="icon icon-delete"></i>
								<span>Delete Public Key</span>
							{/a}
						</li>
					{/if}
					
					{if $k.private}
						<li>
							{a href="/gpgkeymanager/getkey/`$k.private->fingerprint`?type=combined&download=1" target="_blank"}
								<i class="icon icon-download"></i>
								<span>Download Combined Key</span>
							{/a}
						</li>
						<li>
							{a href="/gpgkeymanager/getkey/`$k.private->fingerprint`?type=private&download=1" target="_blank"}
								<i class="icon icon-download"></i>
								<span>Download Private Key</span>
							{/a}
						</li>
						<li>
							{a href="/gpgkeymanager/deletekey/`$k.private->fingerprint`?type=private" confirm="Are you sure you want to completely delete the secret key `$k.private->id_short`?"}
								<i class="icon icon-delete"></i>
								<span>Delete Private Key</span>
							{/a}
						</li>
					{/if}
				</ul>
			</td>
		</tr>
	{/foreach}
</table>
{else}
	<p class="message-tutorial">
		There are no GPG keys on the local system!  Go ahead and<br/>
		{a href="/gpgkeymanager/generate" class="button"}
			<i class="icon icon-plus"></i>
			Generate a New Key
		{/a}
		or
		{a href="/gpgkeymanager/upload" class="button"}
			<i class="icon icon-upload"></i>
			Upload an Existing Key
		{/a}
	</p>
{/if}

{script library="core.ajaxlinks"}{/script}