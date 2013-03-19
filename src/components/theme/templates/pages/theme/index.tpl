<h3>Themes and Skins</h3>

<table class="listing">
	<tr>
		<th>Theme</th>
		<th colspan="2">Site Template</th>
		<th width="100">&nbsp;</th>
	</tr>
	{foreach from=$themes item=theme}
		<tr>
			<td rowspan="{sizeof($theme.templates) + 1}">{$theme.name}</td>
			<td colspan="3"></td>
		</tr>
		{foreach from=$theme.templates item=template}
			<tr>
				<td>{$template.file}</td>
				<td>{$template.title}</td>
				<td>
					<ul class="controls controls-hover">
						<li>
							{if $template.default}
								<i class="icon-star"></i>
								Current Default
							{else}
								{a href="/theme/setdefault/`$theme.name`?template=`$template.file`" class="set-default"}
									<i class="icon-ok"></i>
									<span>Set As Default</span>
								{/a}
							{/if}
						</li>
						<li>
							{a href="/theme/widgets/`$theme.name`?template=`$template.file`"}
								<i class="icon-cogs"></i>
								<span>Widgets</span>
							{/a}
						</li>
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
{script location="foot"}<script>
	$(function(){ 
		$('.set-default').click(function(){
			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: $(this).attr('href'),
				success: function(dat){
					if(dat && dat.status){
						window.location.reload();
					}
					else if(dat && dat.message){
						alert(dat.message);
					}
					else {
						alert('An unknown error occurred.');
						console.log(dat);
					}
				}
			});
			return false;
		});
	});
</script>{/script}