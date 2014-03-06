{if !sizeof($widgets)}
	<p class="message-info">
		There are no gallery widgets created, {a href="/galleryadmin/widgets/update"}Go create one{/a}
	</p>
{else}
	{if $can_manage_theme}
		<p class="message-info">
			To install a gallery widget into the theme or a page, use the {a href="/theme"}Theme Manager{/a}.
		</p>
	{/if}
{/if}

<table class="listing">
	<tr>
		<th>Nickname</th>
		<th width="90">&nbsp;</th>
	</tr>

	{foreach $widgets as $widget}

		<tr>
			<td>{$widget.title}</td>
			<td>
				<ul class="controls">
				{*{a href="/calendaradmin/delete/`$e.id`" confirm="Do you really want to delete the event?" class="control control-delete"}Delete{/a}*}
					<li>
						{a href="/gallerywidget/update/`$widget->getID()`" class="control-edit"}
							<i class="icon-edit"></i>
							<span>Edit</span>
						{/a}
					</li>
				</ul>
			</td>
		</tr>

	{/foreach}
</table>