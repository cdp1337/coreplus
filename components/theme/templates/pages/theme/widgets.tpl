{script library="jqueryui"}{/script}
{script library="jquery.json"}{/script}
{script src="js/theme/widgets.js"}{/script}
{css href="css/theme/widgets.css"}{/css}

<div id="widgetcanvas" attr:theme="{$theme}" attr:template="{$template}">
	<div class="widget-bucket-source">
		{if !count($widgets)}
			<p>There are no widgets currently installed on the site.</p>
		{/if}
		{foreach from=$widgets item='widget'}
			<div class="widget-dragsource">
				<input type="hidden" class="baseurl" name="widgets[0][baseurl]" value="{$widget->get('baseurl')}"/>
				<input type="hidden" class="widgetarea" name="widgets[0][widgetarea]" value=""/>
				
				{$widget->get('title')} [{$widget->get('baseurl')}]
				
				<a href="#" class="control control-delete" style="float:right;">Delete</a>
				<a href="#" class="control control-edit" style="float:right;">Edit</a>
			</div>
		{/foreach}
		{*
		{a href="/Theme/Widgets/Add" class="button add"}Add Widget{/a}
		*}
	</div>
	
	<form action="" method="POST">

		<div class="widget-bucket-destination">
			{foreach from=$widget_areas item='area'}
				<div class="widgetarea" attr:area="{$area.name}">
					{$area.name}
					<div class="widget-dragtarget">
						{foreach from=$area.instances item='widget'}
							<div class="widget-dragdropped" attr:instanceid="{$widget.id}">
								<input type="hidden" class="baseurl" name="widgetarea[{$widget.id}][baseurl]" value="{$widget.baseurl}"/>
								<input type="hidden" class="widgetarea" name="widgetarea[{$widget.id}][widgetarea]" value="{$area.name}"/>

								{$widget.title} [{$widget.baseurl}]

								<a href="#" class="control control-delete" style="float:right;">Delete</a>
								<a href="#" class="control control-edit" style="float:right;">Edit</a>
							</div>
						{/foreach}
					</div>
				</div>
			{/foreach}
		</div>
		
		<div style="clear:both;"></div>
		<input type="submit" value="Update Widgets"/>
	
	</form>
</div>
