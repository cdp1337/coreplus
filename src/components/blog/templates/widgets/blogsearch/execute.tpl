{css src="assets/css/blog.css"}{/css}

<form action="{$url}" method="GET" class="blog-search-widget clearfix">

	<div class="blog-search-widget-entry">
		<input type="text" placeholder="Search Blog Articles" name="q" value="{$query}"/>
		<i class="icon-search"></i>
	</div>

	<input type="submit" value="Search" class="blog-search-widget-submit"/>
</form>
<br/>