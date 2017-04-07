{css href="assets/css/theme/admin.css"}{/css}

{function name=printTemplateList}
	<ul>
		{foreach $items as $key => $item}
			{if isset($item.obj)}
				<li class="file">
					{assign var="imgsrc" value="`$item.obj->getPreviewURL('22x22')`"}
					{*img file=$item.obj dimensions="24x24" alt="thing" assign="imgsrc"*}
					
					<img src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="{$imgsrc}" data-isloaded="0"/>
					<span class="filename" title="{$key|escape}">{$key}</span>
					
					<span class="file-modified" title="Date Modified">{date format="SDT" $item.obj->getMTime()}</span>
					
					{if $item.haswidgets}
						<a class="inline-control" href="{$url_themewidgets}?page={$item.file}" title="Manage Widgets">
							<i class="icon icon-cogs"></i>
							<span>Widgets</span>
						</a>
					{/if}
					{if $item.has_stylesheets}
						<a class="inline-control" href="{$url_themestylesheets}?template=skins/{$item.file}" title="Optional Stylesheets">
							<i class="icon icon-strikethrough"></i>
							<span>Optional Stylesheets</span>
						</a>
					{/if}
					{if $item.type == 'template'}
						<a class="inline-control" href="{$url_themeeditor}?template={$item.file}" title="Edit Template">
							<i class="icon icon-pencil"></i>
							<span>Edit</span>
						</a>
					{/if}
					{* <a href="{$url_themeeditor}?file={$item.file}" title="Edit Asset"><i class="icon icon-pencil"></i></a> *}
				</li>
			{else}
				<li class="collapsed">
					<span class="collapsed-hint" title="Click to expand">
						<i class="icon icon-folder-close"></i>
						<span class="folder-name">{$key}</span>
						<span class="folder-children-count">{t 'STRING_N_ITEM' sizeof($item)}</span>
					</span>
					<span class="expanded-hint" title="Click to close">
						<i class="icon icon-folder-open"></i>
						<span class="folder-name">{$key}</span>
					</span>
					
					{call name=printTemplateList items=$item}	
				</li>
			{/if}
		{/foreach}
	</ul>
{/function}

{if !$multisite}
	<fieldset class="collapsed collapsible theme-section" id="theme-expandable-assets">
		<h3 class="fieldset-title">
			Assets
			<i class="icon icon-chevron-down expandable-hint"></i>
			<i class="icon icon-chevron-up collapsible-hint"></i>
		</h3>
		<p class="message-tutorial">
			Assets are stylesheets, javascript files, and other static resources used by components that get installed to your CDN.
		</p>
		<div class="directory-listing">
			{*call name=printAssetList items=$assets.assets*}
			{call name=printTemplateList items=$assets.assets}
		</div>
	</fieldset>
{/if}


{script location="foot"}<script>
	(function($){
		"use strict";
		
		function loadImg(targetLI){
			var $ct = $(targetLI),
				$img = $ct.children('img');
			//console.log($img);
			//console.log($img.data('isloaded'));
			if($img.data('isloaded') === 0){
				$img.attr('src', $img.data('src'));
				$img.data('isloaded', 1);
			}
		}
		
		$('.expanded-hint').click(function(){
			$(this).closest('li').removeClass('expanded').addClass('collapsed');
			return false;
		});
		$('.collapsed-hint').click(function(){
			var $this = $(this),
				$li = $this.closest('li'),
				$immediateFiles = $li.children('ul').children('li.file');

			$li.removeClass('collapsed').addClass('expanded');

			// Now, run through every img located herein and set the src if necessary.
			// This facilitates the lazy-loading operation.
			$immediateFiles.each(function(){
				loadImg(this);
			});

			return false;
		});

		// Load the top-level images.
		$('.directory-listing > ul > li.file').each(function(){
			loadImg(this);
		});
	})(jQuery);
</script>{/script}
