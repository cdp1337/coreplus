<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">

	<title>{$page.title|escape}</title>
	<link rel="self" href="{$canonical_url}.atom" type="application/atom+xml" title="{$page.title|escape} Atom Feed"/>
	<link rel="alternate" href="{$canonical_url}" type="text/html" title="{$page.title|escape}"/>
	<link rel="alternate" href="{$canonical_url}.rss" type="application/rss+xml" title="{$page.title|escape} RSS Feed"/>
	<updated>{date date="`$last_updated`" format="c"}</updated>

	<id>{$servername}/blog/view/{$blog.id}</id>

	<generator uri="http://corepl.us" {if $smarty.const.DEVELOPMENT_MODE}version="{Core::GetComponent('core')->getVersion()}"{/if}>
		Core Plus
	</generator>

	{foreach $articles as $article}

	<entry>
		<title>{$article.title|escape}</title>
		<link href="{link $article.baseurl}"/>
		<id>{$servername}{$article.baseurl}</id>
		{if $article->getAuthor() && Core::IsComponentAvailable('user-social')}

		<author>
			<name>{$article->getAuthor()->getDisplayName()|escape}</name>
			<uri>{UserSocialHelper::ResolveProfileLink($article->getAuthor())}</uri>
		</author>
		{/if}

		<updated>{date format='c' date="`$article.updated`"}</updated>
		<published>{date format='c' date="`$article.published`"}</published>
		<summary><![CDATA[{$article->getTeaser()}]]></summary>
	</entry>
	{/foreach}

</feed>
