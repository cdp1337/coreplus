{script library="jqueryui"}{/script}
{script library="jquery.json"}{/script}
{script src="js/user/user.js"}{/script}
{script src="js/admin/widgets.js"}{/script}
{css href="css/admin/widgets.css"}{/css}

<p class="message-tutorial">
	The top section of this page allows you to drag and drop widgets to install them to the various widget areas on the skin.
	<br/><br/>
	You can drag widgets from the bottom table to the top area to install them, and rearrange widgets within an area.
</p>

<div>
	<form method="GET" action="" id="skin-selection-form" class="form-orientation-vertical">
		<div class="formelement formelementselect">
			<label class="form-element-label">Theme Skin</label>
			<div class="form-element-value">
				<select name="skin" id="skin-selection-select">
					{foreach $skins as $s}
						<option value="{$s.value}" {if $s.selected}selected="selected"{/if}>{$s.title}</option>
					{/foreach}
				</select>
			</div>
		</div>
	</form>


	<form action="{link '/admin/widgetinstances/save'}" method="POST">

		<input type="hidden" name="theme" value="{$theme}"/>
		<input type="hidden" name="skin" value="{$skin}"/>

		<div class="widget-bucket-destination clearfix">
			{foreach from=$areas item='area'}
				<div class="widgetarea clearfix" data-area="{$area.name}">
					{$area.name}
					<div class="widget-droptarget">
						{foreach from=$area.widgets item='widget'}
							<div class="widget-dragdropped" data-instanceid="{$widget.id}">
								<input type="hidden" class="baseurl" name="widgetarea[{$widget.id}][baseurl]" value="{$widget.baseurl}"/>
								<input type="hidden" class="widgetarea" name="widgetarea[{$widget.id}][widgetarea]" value="{$area.name}"/>
								<input type="hidden" class="widgetaccess" name="widgetarea[{$widget.id}][widgetaccess]" value="{$widget.access}"/>

								<span class="widget-title">
									{if $widget.Widget.title}
										{$widget.Widget.title} <br/>
										[{$widget.baseurl}]
									{else}
										{$widget.baseurl}
									{/if}
								</span>

								<div class="widget-controls">
									<a href="#" class="control control-edit" title="Edit Widget Instance"><i class="icon-edit"></i></a>&nbsp;
									<a href="#" class="control control-delete" title="Remove Widget Instance"><i class="icon-remove"></i></a>
								</div>
							</div>
						{/foreach}
					</div>
				</div>
			{/foreach}

			{if sizeof($areas)}
				<br/>
				<input type="submit" value="Update Widgets"/>
			{else}
				<p class="message-info">
					The skin {$skin} does not appear to have any widget areas!
				</p>
			{/if}
		</div>
	</form>

</div>







{$filters->render()}

{$filters->pagination()}
<table class="listing column-sortable">
	<tr>
		<th sortkey="title">Title</th>
		<th sortkey="baseurl">Base URL</th>
		<th>Installable</th>
		<th sortkey="created">Created</th>
		<th width="50">&nbsp;</th>
	</tr>
	{foreach $listings as $entry}
		<tr>
			<td>
				<div class="widget-dragsource" style="position: relative;">
					<input type="hidden" class="baseurl" name="widgets[0][baseurl]" value="{$entry.baseurl}"/>
					<input type="hidden" class="widgetarea" name="widgets[0][widgetarea]" value=""/>
					<input type="hidden" class="widgetaccess" name="widgets[0][widgetaccess]" value="*"/>

					<span class="widget-title-listing">
						<i class="icon-arrows"></i>
						{$entry.title}
					</span>

					<span class="widget-title">
						{if $entry.title}
							{$entry.title} <br/>
							[{$entry.baseurl}]
						{else}
							{$entry.baseurl}
						{/if}
					</span>

					<div class="widget-controls">
						<a href="#" class="control control-edit"><i class="icon-edit"></i></a>&nbsp;
						<a href="#" class="control control-delete"><i class="icon-remove"></i></a>
					</div>
				</div>
			</td>
			<td>{$entry.baseurl}</td>
			<td>{$entry.installable}</td>
			<td>{date format="SD" $entry.created}</td>

			<td>
				{if $manager}
				<ul class="controls">
					{if $entry.editurl}
						<li>
							{a href="`$entry.editurl`"}
								<i class="icon-edit"></i>
								<span>Edit</span>
							{/a}
						</li>
					{/if}
					{if $entry.deleteurl}
						<li>
							{a href="`$entry.deleteurl`" confirm="Are you sure you want to completely delete this widget?"}
								<i class="icon-remove"></i>
								<span>Delete</span>
							{/a}
						</li>
					{/if}
				</ul>
				{/if}
			</td>
		</tr>

	{/foreach}

	{if $manager}
		{foreach $links as $l}
			<tr>
				<td>
					{a href="`$l.baseurl`" title="Create New `$l.title` Widget"}
						<i class="icon-add"></i>
						<span>New {$l.title} Widget</span>
					{/a}
				</td>
				<td colspan="4"></td>
			</tr>
		{/foreach}
	{/if}
</table>
{$filters->pagination()}