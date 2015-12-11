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
		{t 'MESSAGE_SITE_CONFIGURATION_TUTORIAL'}
	</p>

	<div id="system-config-quicksearch">
		<input type="text" id="quicksearch" placeholder="{t 'STRING_QUICK_SEARCH'}"/>
	</div>

	<div id="system-config-form">
		{$form->set('orientation', 'grid')}
		{$form->render()}
	</div>
{else}
	<p class="message-info">
		{t 'MESSAGE_NO_CONFIGURABLE_OPTIONS_ON_SITE'}
	</p>
{/if}