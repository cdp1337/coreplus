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


	public static function Includejs(){
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
		
		// This script does the init and the include in one go.
		$script = <<<EOD
<script>
  window.fbAsyncInit = function() {
    FB.init({
      appId      : '$appid',
      xfbml      : true,
      version    : 'v2.7'
    });
  };

  (function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = "//connect.facebook.net/en_US/sdk.js";
     fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));
</script>
EOD;
		// Add the necessary script
		\Core\view()->addScript($script, 'head');

		\Core\view()->setHTMLAttribute('xmlns:fb', 'http://www.facebook.com/2008/fbml');
		return true;
	}
}