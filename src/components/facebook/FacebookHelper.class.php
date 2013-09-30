<?php

class FacebookHelper {
	/**
	 * Hook on the page render, use this to look for generic metadata and map those over to opengraph data.
	 *
	 * @param View $view
	 */
	public static function HookRenderPage(View $view){
		if(!isset($view->meta['og:type'])) $view->meta['og:type'] = 'website';
		if(!isset($view->meta['og:title'])) $view->meta['og:title'] = $view->title;
		if($view->canonicalurl) $view->meta['og:url'] = $view->canonicalurl;
		if(isset($view->meta['description'])) $view->meta['og:description'] = str_replace(array("\r\n", "\r", "\n"), ' ', $view->meta['description']);

		// Articles can have some specific information.
		if($view->meta['og:type'] == 'article'){
			$view->meta['og:article:modified_time'] = Time::FormatGMT($view->updated, Time::TIMEZONE_GMT, 'r');
			if(isset($view->meta['author'])) $view->meta['og:article:author'] = $view->meta['author'];
		}

		if(FACEBOOK_APP_ID){
			$view->meta['fb:app_id'] = FACEBOOK_APP_ID;
		}
/*
 * other article tags:
article:published_time - datetime - When the article was first published.
article:modified_time - datetime - When the article was last changed.
article:expiration_time - datetime - When the article is out of date after.
article:author - profile array - Writers of the article.
article:section - string - A high-level section name. E.g. Technology
article:tag - string array - Tag words associated with this article.
*/
	}


	public static function Includejs($async = true){
		$src = 'connect.facebook.net/en_US/all.js';
		$p = (SSL)? 'https://' : 'http://';
		$appid = FACEBOOK_APP_ID;
		$token = \Core\user()->get('facebook_access_token');
		$id = \Core\user()->get('facebook_id');
		if(!$token) $token = "null";
		else $token = '"' . $token . '"';
		if(!$id) $id = "null";
		
		// Facebook also requires a location to render to.
		\Core\view()->appendBodyContent('<div id="fb-root"></div>');
		
		if($async){
			// This script does the init and the include in one go.
			$script = <<<EOD
<script type="text/javascript">
Core.FB = {
	status: "loading",
	ready: false,
	access_token: $token,
	id: $id,
	appid: "$appid",
	onReady: function(fn){
		if(Core.FB.ready) fn();
		else Core.FB._onReadyStack.push(fn);
	},
	_onReadyStack: []
};

if(Core.FB.appid){
	window.fbAsyncInit = function() {
		FB.init({
			appId: '$appid',
			status: true,
			cookie: true,
			xfbml: true,
			oauth: true
		});
		Core.FB.ready = true;
		Core.FB.status = 'loaded';
		for(i in Core.FB._onReadyStack){
			if(typeof Core.FB._onReadyStack[i] == 'function') Core.FB._onReadyStack[i]();
		}
	};

	(function() {
		var e = document.createElement('script'); e.async = true;
		e.src = "$p" + "$src";
		document.getElementById('fb-root').appendChild(e);
	}());
}
else{
	console.log('Refusing to try to load facebook with no appid set.  Please configure it first!');
}


</script>
EOD;
			// Add the necessary script
			\Core\view()->addScript($script, 'foot');
		}
		else{
			// Just the simple script. (which is actually in 2 parts)
			$script = $p . $src;
			\Core\view()->addScript($script, 'foot');
			
			$script = <<<EOD
<script type="text/javascript">
	FB.init({
		appId: '$appid',
		status: true,
		cookie: true,
		xfbml: true,
		oauth: true
	});
</script>
EOD;
			// And the second part.
			\Core\view()->addScript($script, 'foot');
		}

		\Core\view()->setHTMLAttribute('xmlns:fb', 'http://www.facebook.com/2008/fbml');
		return true;
	}
}