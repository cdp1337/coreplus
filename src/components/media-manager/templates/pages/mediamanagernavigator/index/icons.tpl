{script library="jquery"}{/script}
{css src="assets/css/mediamanager/navigator.css"}{/css}
{script src="assets/js/mediamanager/navigator.js"}{/script}
{script library="jqueryui.readonly"}{/script}
{script library="Core.AjaxLinks"}{/script}
{script location="foot"}<script>Navigator.Setup();</script>{/script}

<div class="mediamanagernavigator mediamanagernavigator-icons" location="{$location}" mode="icon">
	<div class="mediamanagernavigator-addressbar">
		<span class="bargraph-inner"></span>
		<a href="{$baseurl}"><i class="icon icon-home"></i></a>
		{foreach $location_tree as $dir}
			<a href="{$dir.href}">/{$dir.name}</a>
		{/foreach}
	</div>

	<div class="mediamanagernavigator-files clearfix">
		{if $uploadform}
			{$uploadform->render()}
		{/if}

		{if !(sizeof($directories) || sizeof($files))}
			<p class="message-info">This directory is empty.</p>
		{/if}

		{if $uplink}
			<div class="directory">
				<div class="directory-image-wrapper">
					<a href="{$uplink}">
						<i class="icon icon-angle-double-up" style="font-size:96px;"></i>
					</a>
				</div>
				<a href="{$uplink}">
					Up One Directory
				</a>
			</div>
		{/if}


		{foreach $directories as $dir}
			<div class="directory" browsename="{$dir.browsename}">
				<div class="directory-image-wrapper">
					<a href="{$dir.href}">
						{img src="assets/images/mimetypes/directory.png" dimensions="96x96"}
					</a>
				</div>
				<a href="{$dir.href}">
					{$dir.name}
				</a>
				<br/>
				Contains {$dir.children} {if $dir.children == 1}child{else}children{/if}<br/>
				{if $usecontrols}
					<div class="controls-wrapper">
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
					</div>
				{/if}
			</div>
		{/foreach}

		{foreach $files as $file}
			<div class="file" selectname="{$file.selectname}">
				<div class="file-image-wrapper">
					<a href="#" class="file-select" browsename="{$file.object->getBasename()}" selectname="{$file.selectname}" corename="{$file.corename}">
						{img file=$file.object dimensions="96x96"}
					</a>
				</div>
				<a href="#" class="file-select" browsename="{$file.object->getBasename()}" selectname="{$file.selectname}" corename="{$file.corename}">
					{$file.name}
				</a>
				<br/>
				Size: {Core::FormatSize("`$file.object->getFilesize()`")}

				{if $usecontrols}
					<div class="controls-wrapper">
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
					</div>
				{/if}
			</div>
		{/foreach}

		<div class="clear"></div>
	</div>

</div>