<div class="search-result search-result-model search-result-page clearfix" itemscope itemtype="http://schema.org/Thing">

	<link itemprop="url" href="{link "`$model.baseurl`"}"/>
	{a href="`$model.baseurl`" class="page-title" itemprop="name"}
		{$model.title}
	{/a}

	{assign var=rel value=$result.relevancy|round:2}
	<div class="search-relevancy" title="Relevancy {$rel}%">
		REL:
		{if $rel > 90}
			<i class="icon icon-star"></i>
			<i class="icon icon-star"></i>
			<i class="icon icon-star"></i>
		{elseif $rel > 66}
			<i class="icon icon-star"></i>
			<i class="icon icon-star"></i>
			<i class="icon icon-star-o"></i>
		{else}
			<i class="icon icon-star"></i>
			<i class="icon icon-star-o"></i>
			<i class="icon icon-star-o"></i>
		{/if}
	</div>

	{if $model->getImage()}
		<div class="page-image">
			{a href="`$model.baseurl`"}
				{img file=$model->getImage() itemprop="image" placeholder="place" dimensions="80x80^" itemprop="image"}
			{/a}
		</div>
	{/if}

	{if $model->getTeaser()}
		<p class="page-teaser" itemprop="description">
			{$model->getTeaser()}
		</p>
	{/if}
</div>
