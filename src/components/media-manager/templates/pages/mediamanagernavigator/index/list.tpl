{script library="jquery"}{/script}
{css src="assets/css/mediamanager/navigator.css"}{/css}
{script src="assets/js/mediamanager/navigator.js"}{/script}
{script library="jqueryui.readonly"}{/script}
{script library="Core.AjaxLinks"}{/script}
{script location="foot"}<script>Navigator.Setup();</script>{/script}

<div class="mediamanagernavigator mediamanagernavigator-list" location="{$location}" mode="list">
	<div class="mediamanagernavigator-addressbar">
		<span class="bargraph-inner"></span>
		<a href="{$baseurl}"><i class="icon-home"></i></a>
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
				<th>&nbsp;</th>
				<th>File/Directory</th>
				<th>Size</th>
				<th>Type</th>
				<th>Modified</th>
				<th>&nbsp;</th>
			</tr>

			{if $uplink}
				<tr class="directory">
					<td>
						<div class="directory-image-wrapper">
							<a href="{$uplink}">
								<i class="icon-double-angle-up" style="font-size:36px;"></i>
							</a>
						</div>
					</td>
					<td>
						<a href="{$uplink}">
							Up One Directory
						</a>
					</td>
					<td colspan="4"></td>
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
				<td>
					Contains {$dir.children} {if $dir.children == 1}child{else}children{/if}
				</td>
				<td>Directory</td>
				<td>
					<!-- @todo Support directory modified time-->&nbsp;
				</td>
				<td>
					{if $usecontrols}
						<ul class="controls">
							<li>
								<a href="{$dir.href}" title="Open">
									<i class="icon-folder-open"></i>
									<span>Open</span>
								</a>
							</li>
							{if $canupload}
								<li>
									<a href="#" class="directory-rename" browsename="{$dir.browsename}" title="Rename">
										<i class="icon-font"></i>
										<span>Rename</span>
									</a>
								</li>
								<li>
									<a href="#" class="directory-delete" browsename="{$dir.browsename}" title="Delete">
										<i class="icon-trash"></i>
										<span>Delete</span>
									</a>
								</li>
							{/if}
						</ul>
					{/if}
				</td>
			</tr>
		{/foreach}

		{foreach $files as $file}
			<tr class="file" selectname="{$file.selectname}">
				<td>
					<div class="file-image-wrapper">
						<a href="#" class="file-select" browsename="{$file.object->getBasename()}" selectname="{$file.selectname}" corename="{$file.corename}">
							{img file="`$file.object`" dimensions="36x36"}
						</a>
					</div>
				</td>
				<td>
					<a href="#" class="file-select" browsename="{$file.object->getBasename()}" selectname="{$file.selectname}" corename="{$file.corename}">
						{$file.name}
					</a>
				</td>
				<td>
					{Core::FormatSize("`$file.object->getFilesize()`")}
				</td>
				<td>
					{$file.object->getExtension()}
				</td>
				<td>
					{date date="`$file.object->getMTime()`"}
				</td>
				<td>
					{if $usecontrols}
						<ul class="controls">
							<li>
								<a href="{$file.object->getURL()}" class="defaultaction" target="_BLANK" title="Download">
									<i class="icon-download"></i>
									<span>Download</span>
								</a>
							</li>
							{if $canupload}
								<li>
									{a href="/mediamanagernavigator/file/metadata?file=`$file.browsename`" class="file-meta ajax-link" title="Manage Metadata"}
										<i class="icon-bullseye"></i>
										<span>Metadata</span>
									{/a}
								</li>
								<li>
									<a href="#" class="file-rename" browsename="{$file.object->getBasename()}" title="Rename">
										<i class="icon-font"></i>
										<span>Rename</span>
									</a>
								</li>
								<li>
									<a href="#" class="file-delete" browsename="{$file.object->getBasename()}" title="Delete">
										<i class="icon-trash"></i>
										<span>Delete</span>
									</a>
								</li>
							{/if}
						</ul>
					{/if}
				</td>
			</tr>
		{/foreach}

		{if !(sizeof($directories) || sizeof($files))}
			<tr class="file">
				<td colspan="6">
					<p class="message-info">This directory is empty.</p>
				</td>
			</tr>
		{/if}

		</table>

		<div class="clear"></div>
	</div>

</div>