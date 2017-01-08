{if sizeof($links) > 0}
	<a href="#" class="button toggle-create-links" title="Create New ...">
		<i class="icon icon-add"></i>
		<span>Create New ...</span>
	</a>
{/if}


<div class="links-create-options" style="display:none;">
	{foreach $links as $l}
		{a href="`$l.baseurl`" class="button hover-info-trigger" title="`$l.title`" data-baseurl="{$l.baseurl}"}
			<i class="icon icon-add"></i>
			<span>{$l.title}</span>
		{/a}
	{/foreach}
	<hr/>
	<div class="hover-info-area">
		{foreach $links as $l}
			<p class="hover-info" data-baseurl="{$l.baseurl}" style="display:none;">
				{if $l.description}
					{$l.description}
				{else}
					Create a new {$l.title} page.
				{/if}
			</p>
		{/foreach}
	</div>
</div>



{$listing->render()}
{*
{foreach $listing as $entry}
	<tr>
		{if $multisite}
			<td>
				{if $entry.site == -1}
					Global
				{elseif $entry.site == 0}
					Root-Only
				{else}
					Local ({$entry.site})
				{/if}
			</td>
		{/if}
		<td>
			{if $entry->getLogoURL()}
				<img src="{$entry->getLogoURL()}"/>
			{/if}
			{if $entry->getParent()}
				{$entry->getParent()->get('title')} &raquo;<br/>
			{/if}
			{$entry.title}
		</td>
		<td>
			{$entry.rewriteurl}
		</td>
		<td>
			{$entry.pageviews}
		</td>
		<td>
			{if $entry.indexable}
				{$entry.popularity}
			{else}
				N/A
			{/if}
		</td>
		<td>
			{if $entry.expires == 0}
				{t 'STRING_DISABLED'}
			{elseif $entry.expires < 60}
				{$entry.expires} secs
			{elseif $entry.expires < 3600}
				{$entry.expires/60} min
			{else}
				{$entry.expires/3600} hr
			{/if}
		</td>
		<td>
			{date format="SD" $entry.created}
		</td>
		<td>
			{date format="SD" $entry.updated}
		</td>
		<td>
			{$entry->getPublishedStatus()}
		</td>
		<td>
			{if $entry.published}
				{date format="SD" $entry.published}
			{else}
				{t 'STRING_NOT_PUBLISHED'}
			{/if}
		</td>
		<td>
			{if $entry.published_expires}
				{date format="SD" $entry.published_expires}
			{else}
				{t 'STRING_NO_EXPIRATION'}
			{/if}
		</td>
		<td>
			{$entry->getSEOTitle()}
		</td>
		<td>
			{$entry->getTeaser()}
		</td>
		<td>
			{if $entry.access == 'g:admin'}
				Only Super Admins
			{elseif $entry.access == '*'}
				Anyone, (guests and users)
			{elseif $entry.access == 'g:authenticated'}
				Only Authenticated Users
			{elseif $entry.access == '!g:authenticated'}
				Only Anonymous Guests
			{else}
				{$entry.access}
			{/if}
		</td>
		<td>
			{$entry.component}
		</td>

		<td>
			<ul class="controls">
				<li>
					{a href="`$entry.baseurl`"}
						<i class="icon icon-view"></i>
						<span>{t 'STRING_VIEW'}</span>
					{/a}
				</li>
				{if $entry.editurl}
					<li>
						{a href="`$entry.editurl`"}
							<i class="icon icon-edit"></i>
							<span>{t 'STRING_EDIT'}</span>
						{/a}
					</li>
				{/if}

				{if $entry.published_status == 'draft'}
					<li>
						{a href="/admin/page/publish?baseurl=`$entry.baseurl`" title="t:STRING_PUBLISH_PAGE" confirm=""}
							<i class="icon icon-thumbs-up"></i><span>{t 'STRING_PUBLISH_PAGE'}</span>
						{/a}
					</li>
				{else}
					<li>
						{a href="/admin/page/unpublish?baseurl=`$entry.baseurl`" title="t:STRING_UNPUBLISH_PAGE" confirm=""}
							<i class="icon icon-thumbs-down"></i><span>{t 'STRING_UNPUBLISH_PAGE'}</span>
						{/a}
					</li>
				{/if}

				{if $entry.deleteurl}
					<li>
						{a href="`$entry.deleteurl`" confirm="t:MESSAGE_ASK_COMPLETEY_DELETE_PAGE"}
							<i class="icon icon-remove"></i>
							<span>{t 'STRING_DELETE'}</span>
						{/a}
					</li>
				{/if}
			</ul>
		</td>
	</tr>

{/foreach}
{$listing->render('foot')}
*}

{css}<style>
	.links-create-options a.button {
		margin: 0.5em;
	}
	.hover-info-area {
		height: 8em;
	}
</style>{/css}

{script}<script>
	$(function() {
		var $targets = $('.hover-info'),
			$overlay = $('.links-create-options').dialog({
				modal: true,
				width: '75%',
				//height: 300,
				title: 'Create New ...',
				autoOpen: false
			});
		
		$('.toggle-create-links').click(function() {
			$overlay.dialog('open');
			return false;
		});
		
		$('.hover-info-trigger').mouseover(function() {
			var b = $(this).data('baseurl');
			// Hide the other ones, if any are displayed.
			$targets.each(function() {
				var $this = $(this);
				if($this.data('baseurl') == b){
					$this.show();
				}
				else{
					$this.hide();
				}
			});
		});
	});
</script>{/script}