<a href="#{$group->getID()}" class="formtabsgroup-tab-link"><span>{$group->get('title')}</span></a>


<div id="{$group->getID()}" class="{$group->getClass()}"{$group->getGroupAttributes()}>
	{if $group->get('description')}
		<p class="formdescription">{$group->get('description')}</p>
	{/if}

	{$elements}
</div>

{script library="jquery"}{/script}
{script library="jqueryui"}{/script}
{script location="foot"}<script>
	$(function(){
		$('.formtabsgroup-tab-link').each(function(){
			var $this  = $(this),
				$form  = $this.closest('form'),
				$group = $form.find('.formtabsgroup-form'),
				$ul    = $form.find('.formtabsgroup-header'),
				id     = $this.attr('href').substr(1),
				$li    = $('<li/>');

			if($group.length == 0){
				$group = $('<div class="formtabsgroup-form"/>');
				$form.children().first().before($group);
			}
			if($ul.length == 0){
				$ul = $('<ul class="formtabsgroup-header"></ul>');
				$group.append($ul);
			}

			$ul.append($li);
			$li.append($this);

			$group.append($('#' + id))
		});

		$('.formtabsgroup-form').tabs();
	});
</script>{/script}