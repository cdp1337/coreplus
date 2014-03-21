{*css src="assets/css/admin/config.css"}{/css*}

{if $config_count}
	{script library="jquery"}{/script}

	{script location="foot"}<script>
		$(function(){
			var $configform = $('#system-config-form'),
				$els = $configform.find('.formelement'),
				$groups = $configform.find('.system-config-group'),
				els = [],
				groups = [];

			// Make a shortcut of these so keyup has less work to perform.
			$els.each(function(){
				$this = $(this);
				els.push(
					{
						$el: $this,
						str: $this.find('.form-element-label').text().toLowerCase() + ' ' + $this.find('.form-element-description').text().toLowerCase()
					}
				)
			});

			$groups.each(function(){
				$this = $(this);

				groups.push(
					{
						$el: $this,
						str: $this.find('legend').text().toLowerCase(),
						$els: $this.find('.formelement')
					}
				)
			});

			$('#quicksearch').keyup(function(){
				var val = $(this).val().toLowerCase(), i;

				if(!val){
					$els.show();
					return true;
				}

				i = 0;
				while(i < els.length){
					if( els[i].str.indexOf(val) == -1 ){
						els[i].$el.hide();
					}
					else{
						els[i].$el.show();
					}

					i++;
				}

				i = 0;
				while(i < groups.length){
					if( groups[i].str.indexOf(val) != -1 ){
						groups[i].$els.show();
					}

					i++;
				}
			});
		});
	</script>{/script}

	<p class="message-tutorial">
		The system config is a low-level utility for managing any and all configuration options of your site.
		If there is a component-provided utility available, it is recommended to use that, as you can break your site
		if you improperly configure this page.
		<br/><br/>You've been warned, tread with caution ;)
	</p>

	<div id="system-config-quicksearch">
		<input type="text" id="quicksearch" placeholder="Quick Search"/>
	</div>

	<div id="system-config-form">
		{$form->set('orientation', 'grid')}
		{$form->render()}
	</div>
{else}
	<p class="message-info">
		There are no configurable options for your site.
	</p>
{/if}