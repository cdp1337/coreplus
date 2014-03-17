<form action="{link "/page/search"}" method="GET" class="form-orientation-vertical page-search-form">
	<div class="formelement formelementtext">
		<div class="form-element-value">
			<input type="text" name="q" value="" placeholder="Search Site"/>
			<i class="icon-search"></i>
		</div>
	</div>
	<input type="submit" value="Search" class="submit-button"/>
</form>

{script location="foot"}<script>
$('.page-search-form').find('i').css('cursor', 'pointer').click(function(){
	$(this).closest('form').submit();
	return false;
});
</script>{/script}


<ul class="page-sitemap">
	{foreach $pages as $page}
		<li itemscope itemtype="http://schema.org/Thing">
			{a href="`$page.baseurl`" title="`$page.title`" class="page-title" itemprop="url"}
				<span itemprop="name">{$page.title}</span>
			{/a}
			{if $page->getTeaser()}
				<span class="page-teaser" itemprop="description">
					- {$page->getTeaser()|truncate:100}
				</span>
			{/if}
		</li>
	{/foreach}
</ul>
