<div class="search-result search-result-model search-result-page clearfix" itemscope itemtype="http://schema.org/Thing">

	<link itemprop="url" href="{link "`$model.baseurl`"}"/>
	{a href="`$model.baseurl`" class="page-title" itemprop="name"}
		{$model.title}
	{/a}

	<div class="search-relevancy">REL: {$result.relevancy|round:2}%</div>

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
