{if $smarty.const.THEME_LISTING_TYPE != 'None'}
	<div vocab="http://schema.org/" typeof="{$smarty.const.THEME_LISTING_TYPE}" class="listing-schema-information">
		{if $smarty.const.THEME_LISTING_NAME}
			<span property="name" class="listing-schema-name">{$smarty.const.THEME_LISTING_NAME}</span>
		{/if}

		{if $smarty.const.THEME_LISTING_ADDRESS}
			<div property="address" typeof="PostalAddress" class="listing-schema-address">
				<span property="streetAddress" class="listing-schema-address-address">{$smarty.const.THEME_LISTING_ADDRESS}</span>
				{if $smarty.const.THEME_LISTING_CITY && $smarty.const.THEME_LISTING_PROVINCE}
					<span property="addressLocality" class="listing-schema-address-city">{$smarty.const.THEME_LISTING_CITY}</span>,
					<span property="addressRegion" class="listing-schema-address-province">{$smarty.const.THEME_LISTING_PROVINCE}</span>
				{/if}
				{if $smarty.const.THEME_LISTING_POSTAL}
					<span property="postalCode" class="listing-schema-address-postal">{$smarty.const.THEME_LISTING_POSTAL}</span>
				{/if}
			</div>
		{/if}
		{if $smarty.const.THEME_LISTING_PHONE}
			<span class="listing-schema-phonelabel">Phone: </span>
			<span property="telephone" class="listing-schema-phone">{$smarty.const.THEME_LISTING_PHONE}</span>
		{/if}
		{if $smarty.const.THEME_LISTING_FAX}
			<span class="listing-schema-faxlabel">Fax: </span>
			<span property="faxNumber" class="listing-schema-fax">{$smarty.const.THEME_LISTING_FAX}</span>
		{/if}
		{if $smarty.const.THEME_LISTING_HOURS}
			<div class="listing-schema-hours">
				<span class="listing-schema-hours-header">Hours:</span>
				{assign var='h' value=$smarty.const.THEME_LISTING_HOURS|replace:'\r':'\n'}
				{foreach explode("\n", $h) as $hour}
					{if $hour}
						{assign var='hk' value=substr($hour, 0, strpos($hour, '|'))}
						{assign var='hv' value=substr($hour, strpos($hour, '|')+1)}
						<time property="openingHours" content="{$hk}" class="listing-schema-hours-hour">{$hv}</time>
					{/if}
				{/foreach}
			</div>
		{/if}
	</div>
{/if}
