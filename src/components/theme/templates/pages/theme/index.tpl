<h3>Themes and Skins</h3>

<table class="listing">
	<tr>
		<th>Theme</th>
		<th colspan="2">Site Template</th>
		<th width="100">&nbsp;</th>
	</tr>
	{foreach from=$themes item=theme}
		{foreach from=$theme.templates item=template name="tplforeach"}
			<tr>
				{* Add the theme name on the first template foreach iteration. *}
				{if $smarty.foreach.tplforeach.index == 0}
					<td rowspan="{sizeof($theme.templates)}">
						{if $theme.default}
							<i class="icon-ok" title="Current Theme"></i>
						{/if}

						{$theme.name}
					</td>
				{/if}
				<td>

					{$template.file}
				</td>
				<td>{$template.title}</td>
				<td>
					<ul class="controls controls-hover">
						{if $theme.default}
							<li>
								{if $template.default}
									<i class="icon-star"></i>
									<span>Current Public Default</span>
								{else}
									{a href="/theme/setdefault/`$theme.name`?template=`$template.file`" confirm="Set `$template.file` as default?"}
										<i class="icon-ok"></i>
										<span>Set As Public Default</span>
									{/a}
								{/if}
							</li>
							<li>
								{if $template.admindefault}
									<i class="icon-star"></i>
									<span>Current Admin Default</span>
								{else}
									{a href="/theme/setadmindefault/`$theme.name`?template=`$template.file`" confirm="Set `$template.file` as default for admin pages?"}
										<i class="icon-ok"></i>
										<span>Set As Admin Default</span>
									{/a}
								{/if}

							</li>
						{else}
							<li>
								{a href="/theme/setdefault/`$theme.name`?template=`$template.file`" confirm="Set `$template.file` as default?"}
									<i class="icon-ok"></i>
									<span>Set Default</span>
								{/a}
							</li>
						{/if}
						<li>
							{a href="/theme/widgets/`$theme.name`?template=`$template.file`"}
								<i class="icon-cogs"></i>
								<span>Widgets</span>
							{/a}
						</li>


						{if $template.has_stylesheets}
							<li>
								{a href="/theme/selectstylesheets/?template=themes/`$theme.name`/skins/`$template.file`"}
									<i class="icon-strikethrough"></i>
									<span>Optional Stylesheets</span>
								{/a}
							</li>
						{/if}

						{* This will be finished in the next version. *}

						<li>
							{a href="/theme/editor?skin=themes/`$theme.name`/skins/`$template.file`"}
								<i class="icon-pencil"></i>
								<span>Editor</span>
							{/a}
						</li>

					</ul>

				</td>
			</tr>
		{/foreach}
	{/foreach}
</table>

{* This will be finished in the next version. *}

{if sizeof($css)}
	<br/>
	<h3>CSS Assets</h3>
	<table class="listing">
		<tr>
			<th>Component</th>
			<th>File</th>
			<th>&nbsp;</th>
		</tr>
		{foreach $css as $page}
			<tr>
				<td>{$page.component}</td>
				<td>{$page.file}</td>
				<td>
					<ul class="controls controls-hover">
						<li>
							{a href="/theme/editor?css=`$page.file`"}
								<i class="icon-pencil"></i>
								<span>Editor</span>
							{/a}
						</li>
					</ul>
				</td>
			</tr>
		{/foreach}
	</table>
{/if}


{* This will be finished in the next version. *}

{if sizeof($pages)}
	<br/>
	<h3>Page templates</h3>
	<table class="listing">
		<tr>
			<th>Component</th>
			<th>File</th>
			<th>&nbsp;</th>
		</tr>
		{foreach $pages as $page}
			<tr>
				<td>{$page.component}</td>
				<td>{$page.file}</td>
				<td>
					<ul class="controls controls-hover">
						{if $page.haswidgets}
							<li>
								{a href="/theme/widgets/?page=`$page.file`"}
									<i class="icon-cogs"></i>
									<span>Widgets</span>
								{/a}
							</li>
						{/if}
						<li>
							{a href="/theme/editor?tpl=`$page.file`"}
								<i class="icon-pencil"></i>
								<span>Editor</span>
							{/a}
						</li>
					</ul>
				</td>
			</tr>
		{/foreach}
	</table>
{/if}


{script library="jquery"}{/script}