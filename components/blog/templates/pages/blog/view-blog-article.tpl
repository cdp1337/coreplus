{if $article.image}
	{img class="blog-img" src="public/blog/`$article.image`" width='620' height='400'}
{/if}
{$article.body}


<script type='text/javascript' src='http://zor.livefyre.com/wjs/v1.0/javascripts/livefyre_init.js'></script>
<script type='text/javascript'>
	var fyre = LF({
		site_id:308829
	});
</script>