<div style="float:left; width:50%;">
	{foreach from=$widgets item='widget'}
		<div class="widget-dragsource">{$widget->get('title')} [{$widget->get('class')}]</div>
	{/foreach}
	{a href="/Theme/Widgets/Add" class="button add"}Add Widget{/a}
</div>
<div style="float:right; width:50%;">
	{foreach from=$widget_areas item='area'}
		<div class="">
			{$area}
			<div class="widget-dragtarget"></div>
		</div>
	{/foreach}
</div>

{script library="jqueryui"}{/script}
{script src="js/theme/widgets.js"}{/script}