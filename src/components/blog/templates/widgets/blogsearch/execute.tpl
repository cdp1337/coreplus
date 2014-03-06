{css src="assets/css/blog.css"}{/css}

<form action="{$url}" method="GET" class="blog-widget-search clearfix">

	<div class="blog-widget-search-entry">
		<input type="text" placeholder="Search Blog Articles" name="q" value="{$query}"/>
		<i class="icon-search"></i>
	</div>

	<input type="submit" value="Search" class="blog-widget-search-submit"/>
</form>
<br/>