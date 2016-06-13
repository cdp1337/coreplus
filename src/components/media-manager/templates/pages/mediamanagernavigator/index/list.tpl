{script library="jquery"}{/script}
{css src="assets/css/mediamanager/navigator.css"}{/css}
{script src="assets/js/mediamanager/navigator.js"}{/script}
{script library="jqueryui.readonly"}{/script}
{script library="Core.AjaxLinks"}{/script}
{script location="foot"}<script>Navigator.Setup();</script>{/script}

{if $usecontrols}
	{assign var='col_width' value='6'}
{else}
	{assign var='col_width' value='5'}
{/if}

<div class="mediamanagernavigator mediamanagernavigator-list" location="{$location}" mode="list">
	<div class="mediamanagernavigator-addressbar">
		<span class="bargraph-inner"></span>
		<a href="{$baseurl}&dir=/"><i class="icon icon-home"></i></a>
		{foreach $location_tree as $dir}
			<a href="{$dir.href}">/{$dir.name}</a>
		{/foreach}
	</div>

	<div class="mediamanagernavigator-files clearfix">
		{if $uploadform}
			{$uploadform->render()}
		{/if}

		<table>

			<tr>
				<th class="mediamanagernavigator-list-col-icon">&nbsp;</th>
				<th class="mediamanagernavigator-list-col-name">File/Directory</th>
				<th class="mediamanagernavigator-list-col-size">Size</th>
				<th class="mediamanagernavigator-list-col-type">Type</th>
				<th class="mediamanagernavigator-list-col-modified">Modified</th>
				{if $usecontrols}<th class="mediamanagernavigator-list-col-controls">&nbsp;</th>{/if}
			</tr>

			{if $uplink}
				<tr class="directory">
					<td>
						<div class="directory-image-wrapper">
							<a href="{$uplink}">
								<i class="icon icon-angle-double-up" style="font-size:36px;"></i>
							</a>
						</div>
					</td>
					<td>
						<a href="{$uplink}">
							Up One Directory
						</a>
					</td>
					<td class="mediamanagernavigator-list-col-size"></td>
					<td class="mediamanagernavigator-list-col-type"></td>
					<td class="mediamanagernavigator-list-col-modified"></td>
					{if $usecontrols}<td></td>{/if}
				</tr>
			{/if}


			{foreach $directories as $dir}
				<tr class="directory" browsename="{$dir.browsename}">
					<td>
						<div class="directory-image-wrapper">
							<a href="{$dir.href}">
								{img src="assets/images/mimetypes/directory.png" dimensions="36x36"}
							</a>
						</div>
					</td>
					<td>
						<a href="{$dir.href}">
							{$dir.name}
						</a>
					</td>
					<td class="mediamanagernavigator-list-col-size">
						{$dir.children} {if $dir.children == 1}child{else}children{/if}
					</td>
					<td class="mediamanagernavigator-list-col-type">Dir</td>
					<td class="mediamanagernavigator-list-col-modified">
						<!-- @todo Support directory modified time-->&nbsp;
					</td>
					{if $usecontrols}
						<td>
							<ul class="controls">
								<li>
									<a href="{$dir.href}" title="Open">
										<i class="icon icon-folder-open"></i>
										<span>Open</span>
									</a>
								</li>
								{if $canupload}
									<li>
										<a href="#" class="directory-rename" browsename="{$dir.browsename}" title="Rename">
											<i class="icon icon-font"></i>
											<span>Rename</span>
										</a>
									</li>
									<li>
										<a href="#" class="directory-delete" browsename="{$dir.browsename}" title="Delete">
											<i class="icon icon-trash"></i>
											<span>Delete</span>
										</a>
									</li>
								{/if}
							</ul>
						</td>
					{/if}
				</tr>
			{/foreach}

			{foreach $files as $file}
				<tr class="file" selectname="{$file.selectname}">
					<td>
						<div class="file-image-wrapper">
							<a href="#" class="file-select" browsename="{$file.object->getBasename()}" selectname="{$file.selectname}" corename="{$file.corename}">
								{img file=$file.object dimensions="36x36"}
							</a>
						</div>
					</td>
					<td>
						<a href="#" class="file-select" browsename="{$file.object->getBasename()}" selectname="{$file.selectname}" corename="{$file.corename}">
							{$file.name}
						</a>
					</td>
					<td class="mediamanagernavigator-list-col-size">
						{Core::FormatSize("`$file.object->getFilesize()`")}
					</td>
					<td class="mediamanagernavigator-list-col-type">
						{$file.object->getExtension()}
					</td>
					<td class="mediamanagernavigator-list-col-modified">
						{date date="`$file.object->getMTime()`"}
					</td>
					{if $usecontrols}
						<td>
							<ul class="controls">
								<li>
									<a href="{$file.object->getURL()}" class="defaultaction" target="_BLANK" title="Download">
										<i class="icon icon-download"></i>
										<span>Download</span>
									</a>
								</li>
								{if $canupload}
									<li>
										{a href="/mediamanagernavigator/file/metadata?file=`$file.browsename`" class="file-meta ajax-link" title="Manage Metadata"}
											<i class="icon icon-bullseye"></i>
											<span>Metadata</span>
										{/a}
									</li>
									<li>
										<a href="#" class="file-rename" browsename="{$file.object->getBasename()}" title="Rename">
											<i class="icon icon-font"></i>
											<span>Rename</span>
										</a>
									</li>
									<li>
										<a href="#" class="file-delete" browsename="{$file.object->getBasename()}" title="Delete">
											<i class="icon icon-trash"></i>
											<span>Delete</span>
										</a>
									</li>
								{/if}
							</ul>
						</td>
					{/if}
				</tr>
			{/foreach}

			{if !(sizeof($directories) || sizeof($files))}
				<tr class="file">
					<td colspan="{$col_width}">
						<p class="message-info">This directory is empty.</p>
					</td>
				</tr>
			{/if}

		</table>

		<div class="clear"></div>
	</div>

</div>