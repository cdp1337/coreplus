<div class="{$element->getClass()}">
	<label for="{$element->get('name')}" class="form-element-label">
		{$element->get('title')|escape}

		{if $element->get('required')}<span class="form-element-required-mark" title="Required Field"> *</span>{/if}

		<a class="reload-captcha" data-captcha="{$element->get('id')}-image" href="#">
			<i class="icon icon-refresh"></i>
			<span>Reload Image</span>
		</a>

	</label>

	<div class="form-element-value">
		<img src="{link href='/simplecaptcha.png'}" id="{$element->get('id')}-image"/>
		<input type="text"{$element->getInputAttributes()} placeholder="Letters from Image">
	</div>

	<p class="form-element-description">{$element->get('description')}</p>
</div>

{script location="foot"}<script>
	(function(){
		"use strict";
		
		var i, objs;
		
		objs = document.getElementsByClassName('reload-captcha');
		for(i = 0; i < objs.length; i++){
			objs[i].addEventListener('click', function(e){
				var d = new Date(),
					id = this.dataset.captcha,
					target = document.getElementById(id),
					link = "{link href='/simplecaptcha.png'}";
				
				target.src = link + '?date=' + d.getTime();
				
				// Prevent propagation
				e.preventDefault();
			});
		}
	})();
</script>{/script}