{if sizeof($links) > 0}
	<a href="#" class="button toggle-create-links" title="Create New ...">
		<i class="icon icon-add"></i>
		<span>Create New ...</span>
	</a>
{/if}


<div class="links-create-options" style="display:none;">
	{foreach $links as $l}
		{a href="`$l.baseurl`" class="button hover-info-trigger" title="`$l.title`" data-baseurl="{$l.baseurl}"}
			<i class="icon icon-add"></i>
			<span>{$l.title}</span>
		{/a}
	{/foreach}
	<hr/>
	<div class="hover-info-area">
		{foreach $links as $l}
			<p class="hover-info" data-baseurl="{$l.baseurl}" style="display:none;">
				{if $l.description}
					{$l.description}
				{else}
					Create a new {$l.title} page.
				{/if}
			</p>
		{/foreach}
	</div>
</div>



{$listing->render()}

{css}<style>
	.links-create-options a.button {
		margin: 0.5em;
	}
	.hover-info-area {
		height: 8em;
	}
</style>{/css}

{script name="jqueryui"}{/script}
{script location="foot"}<script>
	$(function() {
		var $targets = $('.hover-info'),
			$overlay = $('.links-create-options').dialog({
				modal: true,
				width: '75%',
				//height: 300,
				title: 'Create New ...',
				autoOpen: false
			});
		
		$('.toggle-create-links').click(function() {
			$overlay.dialog('open');
			return false;
		});
		
		$('.hover-info-trigger').mouseover(function() {
			var b = $(this).data('baseurl');
			// Hide the other ones, if any are displayed.
			$targets.each(function() {
				var $this = $(this);
				if($this.data('baseurl') == b){
					$this.show();
				}
				else{
					$this.hide();
				}
			});
		});
	});
</script>{/script}