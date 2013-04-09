<!-- START: Livefyre Embed -->
<div id="livefyre-comments"></div>
{script location="head" src="http://zor.livefyre.com/wjs/v3.0/javascripts/livefyre.js"}{/script}
{script location="foot"}<script type="text/javascript">
	(function () {
		var articleId = "{$articleId}", url = "{$url}";
		// If Core can't provide an article id... generate it automatically
		if(!articleId) articleId = fyre.conv.load.makeArticleId(null);
		if(!url) url = fyre.conv.load.makeCollectionUrl();

		fyre.conv.load(
			{  },
			[
				{
					el: 'livefyre-comments',
					network: "livefyre.com",
					siteId: "{$siteId}",
					articleId: articleId,
					signed: false,
					collectionMeta: {
						articleId: articleId,
						url: url,
						title: "{$title}"
					}
				}
			],
			function() {  }
		);
	}());
</script>{/script}
<!-- END: Livefyre Embed -->
