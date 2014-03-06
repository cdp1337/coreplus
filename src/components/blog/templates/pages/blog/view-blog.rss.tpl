<?xml version="1.0"?>
<rss version="2.0"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:media="http://search.yahoo.com/mrss/"
>
	<channel>
		<title>{$page.title|escape}</title>
		<link>{$canonical_url}/</link>
		<description>{$page.title|escape}</description>
		<language>en-us</language>
		<lastBuildDate>{date date="`$last_updated`" format="r"}</lastBuildDate>
		<docs>http://www.rssboard.org/rss-specification</docs>
		<generator>Core Plus{if $smarty.const.DEVELOPMENT_MODE} {Core::GetComponent('core')->getVersion()}{/if}</generator>

		<atom:link href="{$canonical_url}.rss"  rel="self" type="application/rss+xml" />
		<atom:link href="{$canonical_url}"      rel="alternate" type="text/html" />
		<atom:link href="{$canonical_url}.atom" rel="alternate" type="application/atom+xml" />


		{foreach $articles as $article}

		<item>
			<title>{$article.title|escape}</title>
			<link>{link $article.baseurl}</link>
			<description><![CDATA[{$article->getTeaser()}]]></description>
			<pubDate>{date format='r' date="`$article.published`"}</pubDate>
			<guid>{$servername}{$article.baseurl}</guid>
			{if $article->getAuthor() && Core::IsComponentAvailable('user-social')}
<dc:creator>{$article->getAuthor()->getDisplayName()|escape}</dc:creator>{/if}

			{if $article->getImage()}
<media:thumbnail url="{$article->getImage()->getPreviewURL('200x200')}"/>
			{/if}

		</item>

		{/foreach}

	</channel>
</rss>
