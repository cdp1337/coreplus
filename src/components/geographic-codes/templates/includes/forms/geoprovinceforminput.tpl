{script library="jquery"}{/script}
{css src='assets/css/geographic-codes.css'}{/css}


<div class="{$element->getClass()} {$element->get('id')}">
	<label class="form-element-label">
		{$element->get('title')|escape}
		{if $element->get('required')}<span class="form-element-required-mark" title="Required Field"> *</span>{/if}
	</label>

	<p class="form-element-description">{$element->get('description')}</p>

	<div class="form-element-value">
		<div id="{$element->get('id')}-country-wrapper" class="geoprovince-country-wrapper">
			<select class="geoprovince-country" id="{$element->get('id')}-country">
				{foreach $countries as $c}
					<option value="{$c.iso2}" {if $c.iso2 == $country}selected="selected"{/if}>{$c.name}</option>
				{/foreach}
			</select>
		</div>
		
		<div id="{$element->get('id')}-province-wrapper" class="geoprovince-province-wrapper">
			<!-- noscript logic -->
			<input type="text" class="geoprovince-province" name="{$element->get('name')}" value="{$country}:{$province}" maxlength="7"/>
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

{script location="foot"}<script>
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
			$provinceselect.attr('name', name);

			for (i in provinces){
				html = '<option value="' + current_country + ':' + provinces[i].code + '"';
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
</script>{/script}