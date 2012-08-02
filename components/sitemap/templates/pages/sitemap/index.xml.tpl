<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
	{foreach $pages as $page}
		<url>
			<loc>{$page->getResolvedURL()}</loc>
			<lastmod>{date date=$page.updated format='r'}</lastmod>
			<changefreq>monthly</changefreq>
			<priority>0.8</priority>
		</url>
	{/foreach}
</urlset>