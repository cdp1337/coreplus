{script library="jqueryui"}{/script}
{script library="jquery.json"}{/script}
{script src="js/theme/widgets.js"}{/script}
{css href="css/theme/widgets.css"}{/css}

<div id="widgetcanvas" attr:theme="{$theme}" attr:template="{$template}">
	<div style="float:left; width:50%;">
		{foreach from=$widgets item='widget'}
			<div class="widget-dragsource" attr:widgetid="{$widget->get('id')}" attr:instanceid="0">{$widget->get('title')} [{$widget->get('class')}]</div>
		{/foreach}
		{a href="/Theme/Widgets/Add" class="button add"}Add Widget{/a}
	</div>

	<div style="float:right; width:50%;">
		{foreach from=$widget_areas item='area'}
			<div class="widgetarea" attr:area="{$area}">
				{$area}
				<div class="widget-dragtarget"></div>
			</div>
		{/foreach}
	</div>
</div>
