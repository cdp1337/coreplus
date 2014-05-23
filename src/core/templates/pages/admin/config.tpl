{*css src="assets/css/admin/config.css"}{/css*}

{if $config_count}
	{script library="jquery"}{/script}

	{script location="foot"}<script>
		$(function(){
			var $configform = $('#system-config-form'),
				$els        = $configform.find('.formelement').not('.formsubmitinput'),
				$groups     = $configform.find('.system-config-group'),
				els         = [],
				groups      = [],
				groupids    = { };

			$els.each(function(){
				$this = $(this);
				els.push(
					{
						$el: $this,
						str: $this.find('.form-element-label').text().toLowerCase() + ' ' + $this.find('.form-element-description').text().toLowerCase(),
						group: $this.closest('.system-config-group').attr('id')
					}
				)
			});

			$groups.each(function(){
				$this = $(this);

				groups.push(
					{
						id: $this.attr('id'),
						$el: $this,
						str: $this.find('legend').text().toLowerCase(),
						$els: $this.find('.formelement')
					}
				);

				groupids[$this.attr('id')] = {
					display: true,
					$el: $this
				};
			});

			$('#quicksearch').keyup(function(){
				var val = $(this).val().toLowerCase(), i;

				if(!val){
					$els.show();
					$groups.show();
					return true;
				}

				for(i in groupids){
					groupids[i].display = false;
				}

				i = 0;
				while(i < els.length){
					if( els[i].str.indexOf(val) == -1 ){
						els[i].$el.hide();
					}
					else{
						els[i].$el.show();
						groupids[ els[i].group ].display = true;
					}

					i++;
				}

				i = 0;
				while(i < groups.length){
					if( groups[i].str.indexOf(val) != -1 ){
						groups[i].$els.show();
						groupids[ groups[i].id ].display = true;
					}

					i++;
				}

				for(i in groupids){
					if(groupids[i].display){
						groupids[i].$el.show();
					}
					else{
						groupids[i].$el.hide();
					}
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