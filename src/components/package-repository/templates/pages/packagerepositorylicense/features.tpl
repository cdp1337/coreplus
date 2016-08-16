{if !$key}
	<p class="message-tutorial">
		In order to support Feature management, please upload or create a private GPG key that will be used to create signed packages.
		<br/><br/>
		For security reasons, it is VERY important to use a "throw-away" key here as the private key needs to be available on the public server!
		This key should NOT be used personally and should NOT be used for package signing.
	</p>
{else}
	<p class="message-tutorial">
		Features are licensed options that are deployed to end-user systems via their registered server ID.<br/><br/>
		{if $key->isValid()}
			Protection is ensured by the GPG key {$key->id}
			{if $key->expires}
				and is valid until {date format="SD" $key->expires}.
			{/if}	
		{else}
			The GPG key {$key->id} is either expired or otherwise invalid.  Please generate a new one!
		{/if}
	</p>
{/if}

{if $gen_form}
	{$gen_form->render()}
{/if}

{if $listings}
	{$listings->render('head')}

	{foreach $listings as $l}
		<tr>
			<td>{$l.feature}</td>
			<td>{$l.type}</td>
			<td>{$l.options|nl2br}</td>
			<td>
				<ul class="controls">
					<li>
						{a href="/packagerepositorylicense/feature/update/`$l.id`"}
							<i class="icon icon-edit"></i>
							<span>{t 'STRING_PACKAGE_REPOSITORY_LICENSE_FEATURE_UPDATE'}</span>
						{/a}
					</li>
				</ul>
			</td>
		</tr>
	{/foreach}

	{$listings->render('foot')}
{/if}

