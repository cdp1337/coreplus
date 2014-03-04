{script library="jquery"}{/script}

{*css}<style>
	.geoaddressforminput label {
		color: rgb(74, 74, 74);
	}
	.geoaddressforminput input[type="text"] {
		background: none repeat scroll 0 0 rgb(246, 246, 246);
		border: 1px solid rgb(203, 203, 203);
		border-radius: 0.4em;
		color: rgb(60, 60, 60);
		min-width: 260px;
		padding: 10px 0 6px 10px;
	}

	.geoaddressforminput select {
		background: none repeat scroll 0 0 rgb(246, 246, 246);
		border: 1px solid rgb(203, 203, 203);
		border-radius: 0.4em 0.4em 0 0;
		color: rgb(60, 60, 60);
		padding: 7px 4px 7px 9px;
		min-width: 100px;
		cursor: pointer;
	}

	.geoaddressforminput .address-label,
	.geoaddressforminput .address-address1,
	.geoaddressforminput .address-address2,
	.geoaddressforminput .address-city {
		margin-bottom: 2px;
	}
	.geoaddressforminput .address-province-wrapper {
		float: left;
		margin-bottom: 2px;
	}

	.geoaddressforminput input.address-postal {
		min-width: 80px;
		width: 100px;
		margin-left: 2px;
		margin-bottom: 2px;
	}
</style>{/css*}


<div class="{$element->getClass()} {$element->get('id')} clearfix">
	<label class="form-element-label">
		{$element->get('title')|escape}
		{if $element->get('required')}<span class="form-element-required-mark" title="Required Field"> *</span>{/if}
	</label>

	<p class="form-element-description">{$element->get('description')}</p>

	<div class="form-element-value">
		<input type="hidden" name="{$element->get('name')}[id]" value="{$id}"/>

		<input type="text" class="address-label" name="{$element->get('name')}[label]" placeholder="Label / Address Nickname" {if $req}required="required"{/if} value="{$label|escape}"/>

		<input type="text" class="address-address1" name="{$element->get('name')}[address1]" placeholder="Address 1" {if $req}required="required"{/if} value="{$address1|escape}"/>

		<input type="text" class="address-address2" name="{$element->get('name')}[address2]" placeholder="Address 2" value="{$address2|escape}"/>

		<input type="text" class="address-city" name="{$element->get('name')}[city]" placeholder="City" value="{$city|escape}"/>

		<div id="{$element->get('id')}-province-wrapper" class="address-province-wrapper">
			<!-- noscript logic -->
			<input type="text" class="address-province" name="{$element->get('name')}[province]" value="{$province}" maxlength="2"/>
		</div>


		<input type="text" class="address-postal" name="{$element->get('name')}[postal]" value="{$postal|escape}" {if $req}required="required"{/if} maxlength="10" placeholder="Zip/Postal"/>

		<div id="{$element->get('id')}-country-wrapper" class="address-country-wrapper">
			<select class="address-country" id="{$element->get('id')}-country" name="{$element->get('name')}[country]">
				{foreach $countries as $c}
					<option value="{$c.iso2}" {if $c.iso2 == $country}selected="selected"{/if}>{$c.name}</option>
				{/foreach}
			</select>
		</div>

	</div>


</div>


<!-- The template for the province select box -->
<template id="{$element->get('id')}-province-tpl">
	<select id="{$element->get('id')}-province">
		{if !$req}
			<option value="">-- Select a State/Province --</option>
		{/if}
	</select>
</template>

<script>
	$(function(){
		var provinces        = {$province_json},
			current_province = "{$province}",
			current_country  = "{$country}",
			id               = "{$element->get('id')}",
			name             = "{$element->get('name')}",
			$provincewraper  = $('#' + id + '-province-wrapper'),
			$provincetpl     = $('#' + id + '-province-tpl'),
			$provinceselect  = null,
			$countryselect   = $('#' + id + '-country'),
			i, html, renderprovinces;

		renderprovinces = function(){
			$provincewraper.html($provincetpl.html());
			$provinceselect = $provincewraper.find('select');

			// Set the name correctly, (since firefox has a bug where form elements in a <template> tag are still being submitted.
			$provinceselect.attr('name', name + '[province]');

			for (i in provinces){
				html = '<option value="' + provinces[i].code + '"';
				if(provinces[i].code == current_province) html += ' selected="selected"';
				html += '>' + provinces[i].name + '</option>';

				$provinceselect.append(html);
			}

			if(provinces.length > 0){
				$provinceselect.show();
			}
			else{
				$provinceselect.hide();
			}
		}


		// On first load.
		renderprovinces();

		$countryselect.change(function(){
			current_country = $countryselect.val();
			$provincewraper.html('Loading Provinces...');
			$.ajax({
				url: Core.ROOT_URL + 'geoaddress/getprovinces/' + current_country,
				dataType: 'json',
				success: function(d){
					provinces = d;

					renderprovinces();
				}
			});
		});

	});
</script>