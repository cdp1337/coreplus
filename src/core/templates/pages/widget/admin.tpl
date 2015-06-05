{script library="jqueryui"}{/script}
{script library="jquery.json"}{/script}
{script src="js/user/user.js"}{/script}
{*if $manager}{script src="js/admin/widgets.js"}{/script}{/if*}
{css href="css/admin/widgets.css"}{/css}

<p class="message-tutorial">
	{if $manager}
		This page is broken into three sections, "Installed Widgets", "Available Widgets", and "New Widget Types".
		<br/><br/>
		<strong>Installed Widgets</strong><br/>
		Installed Widgets display what widgets are currently installed in the selected template or URL.
		Here you have the ability to reorder displayed widgets, edit their access permissions, display template,
		and any other display-oriented option, and uninstall from the selected area.
		<br/><br/>
		<strong>Available Widgets</strong><br/>
		Available Widgets display all widgets that are available to be created and/or enabled on the site.
		This provides a pool of widgets to install from.
		Some widgets require a new one to be created before it can be installed in an area and support custom settings,
		whereas others only allow installing and have no configurable options from within this widget management interface.
		<br/><br/>
		<strong>New Widget Types</strong><br/>
		New Widget Types is a section to register a given widget type as an available widget that can then be installed on the site.
	{else}
		Displays all widgets installed on the site.
	{/if}
</p>

<fieldset>
	<h3 class="fieldset-title">Installed Widgets</h3>
	<form method="GET" action="" id="skin-selection-form" class="form-orientation-horizontal">
		<div class="formelement formelementselect">
			<label class="form-element-label">Template</label>
			<div class="form-element-value">
				<select name="template" id="skin-selection-select">
					{foreach $options as $k => $v}
						<option value="{$k}" {if $k == $template}selected="selected"{/if}>{$v}</option>
					{/foreach}
				</select>
			</div>
		</div>
	</form>


	<form action="{link '/widget/instances/save'}" method="POST">

		<input type="hidden" name="selected" value="{$selected}"/>
		{*<input type="hidden" name="theme" value="{$theme}"/>*}
		<input type="hidden" name="template" value="{$template}"/>
		{*<input type="hidden" name="page_template" value="{$page_template}"/>
		<input type="hidden" name="page_baseurl" value="{$page_baseurl}"/>*}

		{*
		<input type="hidden" name="theme" value="{$theme}"/>
		<input type="hidden" name="skin" value="{$skin}"/>
		*}

		<div class="widget-bucket-destination clearfix">
			{foreach from=$areas item='area'}
				<div class="widgetarea clearfix" data-area="{$area.name}">
					{$area.name}
					<div class="widget-droptarget">
						{foreach from=$area.widgets item='widget' name='widgets'}
							<div class="widget-instance">
								<span class="widget-title">
									{if $widget.Widget.title}
										{$widget.Widget.title}
									{/if}
								</span>

								{if $widget->getWidget()->getPreviewImage()}
									{img width="210" src="`$widget->getWidget()->getPreviewImage()`"}
								{else}
									{$widget.baseurl}
								{/if}

								{if $manager}
									<div>
									<ul class="controls">
										<li>
											<a href="/widget/instance/update/{$widget.id}" class="control control-edit" title="Edit Options">
												<i class="icon-edit"></i>
												<span>Edit Installed Options</span>
											</a>
										</li>
										{if $widget.Widget.editurl}
											<li>
												{a href="`$widget.Widget.editurl`"}
													<i class="icon-wrench"></i>
													<span>Edit Widget Settings</span>
												{/a}
											</li>
										{/if}
										{if !$smarty.foreach.widgets.last}
											<li>
												{a href="/widget/instance/movedown/`$widget.id`" class="control control-delete" title="Move Widget Instance Down" confirm=""}
													<i class="icon-arrow-down"></i>
													<span>Move Down</span>
												{/a}
											</li>
										{/if}
										{if !$smarty.foreach.widgets.first}
											<li>
												{a href="/widget/instance/moveup/`$widget.id`" class="control control-delete" title="Move Widget Instance Up" confirm=""}
													<i class="icon-arrow-up"></i>
													<span>Move Up</span>
												{/a}
											</li>
										{/if}
										<li>
											{a href="/widget/instance/remove/`$widget.id`" class="control control-delete" title="Remove Widget Instance" confirm="Confirm removing the widget from this area?"}
												<i class="icon-trash-o"></i>
												<span>Uninstall Instance</span>
											{/a}
										</li>
									</ul>
									</div>
								{/if}
							</div>
						{/foreach}
					</div>
				</div>
			{foreachelse}
				<p class="message-info">
					The skin {$skin} does not appear to have any widget areas!
				</p>
			{/foreach}
		</div>
	</form>

</fieldset>




<fieldset class="widget-bucket-source">
	<h3 class="fieldset-title">Available Widgets</h3>
	{foreach $available_widgets as $widget}
		<div class="widget-source">
			<span class="widget-title">
				{$widget.title}
			</span>

			{if $manager && ($widget.editurl || $widget.deleteurl)}
				<div>
				<ul class="controls">
					{if $widget.editurl}
						<li>
							{a href="`$widget.editurl`"}
								<i class="icon-wrench"></i>
								<span>Edit Widget Settings</span>
							{/a}
						</li>
					{/if}
					{if $widget.deleteurl}
						<li>
							{a href="`$widget.deleteurl`" confirm="Are you sure you want to completely delete this widget?"}
								<i class="icon-remove"></i>
								<span>Delete Widget</span>
							{/a}
						</li>
					{/if}
				</ul>
				</div>
			{/if}

			{if $widget->getWidget() === null}
				[ Widget Not Found! ]<br/>
				{$widget.baseurl}
			{elseif $widget->getWidget()->getPreviewImage()}
				{img width="210" src="`$widget->getWidget()->getPreviewImage()`"}
			{else}
				{$widget.baseurl}
			{/if}

			{if $widget->getWidget() !== null}
				<form action="{link '/widget/instance/install'}" method="POST">
					<select name="area">
						<option value="">-- Select Widget Area --</option>
						{foreach from=$areas item='area'}
							<option value="{$area.name}">{$area.name}</option>
						{/foreach}
					</select>
					<input type="hidden" name="template" value="{$template}"/>
					<input type="hidden" name="widget_baseurl" value="{$widget.baseurl}"/>
					<input type="submit" value="Install Widget"/>
				</form>
			{/if}

			{*widget.installable*}
		</div>
	{/foreach}
</fieldset>

{if $manager}
	<fieldset class="widget-bucket-registration">
		<h3 class="fieldset-title">New Widget Types</h3>
		{foreach $links as $l}
			<div class="widget-registration">
				<span class="widget-title">
					{$l.title}
				</span>

				{if $l.preview}
					{img width="210" src="`$l.preview`"}
				{/if}
				{a href="`$l.baseurl`" title="Register New `$l.title` Widget" class="button"}
					<i class="icon-add"></i>
					<span>Register New</span>
				{/a}
			</div>
		{/foreach}
	</fieldset>
{/if}


{script location="foot"}<script>
	$('#skin-selection-select').change(function(){
		$(this).closest('form').submit();
	});
</script>{/script}