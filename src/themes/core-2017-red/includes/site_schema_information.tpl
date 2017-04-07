{if ConfigHandler::Get('/theme/listing_type') != 'None'}
	{assign var='listing_type' value=ConfigHandler::Get('/theme/listing_type')}
	{assign var='listing_name' value=ConfigHandler::Get('/theme/listing_name')}
	{assign var='listing_address' value=ConfigHandler::Get('/theme/listing_address')}
	{assign var='listing_city' value=ConfigHandler::Get('/theme/listing_city')}
	{assign var='listing_province' value=ConfigHandler::Get('/theme/listing_province')}
	{assign var='listing_postal' value=ConfigHandler::Get('/theme/listing_postal')}
	{assign var='listing_phone' value=ConfigHandler::Get('/theme/listing_phone')}
	{assign var='listing_fax' value=ConfigHandler::Get('/theme/listing_fax')}
	{assign var='listing_email' value=ConfigHandler::Get('/theme/listing_email')}
	{assign var='listing_hours' value=ConfigHandler::Get('/theme/listing_hours')}
	
	<div vocab="http://schema.org/" typeof="{$listing_type}" class="listing-schema-information">
		{if $listing_name}
			<span property="name" class="listing-schema-name">{$listing_name}</span>
		{/if}

		{if $listing_address}
			<div property="address" typeof="PostalAddress" class="listing-schema-address">
				<span property="streetAddress" class="listing-schema-address-address">{$listing_address}</span>
				{if $listing_city && $listing_province}
					<span property="addressLocality" class="listing-schema-address-city">{$listing_city}</span>,
					<span property="addressRegion" class="listing-schema-address-province">{$listing_province}</span>
				{/if}
				{if $listing_postal}
					<span property="postalCode" class="listing-schema-address-postal">{$listing_postal}</span>
				{/if}
			</div>
		{/if}
		{if $listing_phone}
			<span class="listing-schema-phonelabel">Phone: </span>
			<span property="telephone" class="listing-schema-phone">{$listing_phone}</span>
		{/if}
		{if $listing_fax}
			<span class="listing-schema-faxlabel">Fax: </span>
			<span property="faxNumber" class="listing-schema-fax">{$listing_fax}</span>
		{/if}
		{if $listing_email}
			<span class="listing-schema-emaillabel">Email: </span>
			<span property="email" class="listing-schema-email">{$listing_email}</span>
		{/if}
		{if $listing_hours}
			<div class="listing-schema-hours">
				<span class="listing-schema-hours-header">Hours:</span>
				{assign var='h' value=$listing_hours|replace:'\r':'\n'}
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
