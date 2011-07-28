<?php

class FacebookHelper {
	public static function Includejs($async = false){
		$src = 'connect.facebook.net/en_US/all.js';
		$p = (SSL)? 'https://' : 'http://';
		$appid = FACEBOOK_APP_ID;
		
		// Facebook also requires a location to render to.
		CurrentPage::AddBodyContent('<div id="fb-root"></div>', 'post');
		
		if($async){
			// This script does the init and the include in one go.
			$script = <<<EOD
<script type="text/javascript">
window.fbAsyncInit = function() {
	FB.init({
		appId: '$appid',
		status: true,
		cookie: true,
		xfbml: true,
		oauth: true
	});
};

(function() {
	var e = document.createElement('script'); e.async = true;
	e.src = "$p" + "$src";
	document.getElementById('fb-root').appendChild(e);
}());
</script>
EOD;
			// Add the necessary script
			CurrentPage::AddScript($script, 'foot');
		}
		else{
			// Just the simple script. (which is actually in 2 parts)
			$script = $p . $src;
			CurrentPage::AddScript($script, 'foot');
			
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
			CurrentPage::AddScript($script, 'foot');
			//CurrentPage::AddBodyContent($script);
		}
		
		
		CurrentPage::SetHTMLAttribute('xmlns:fb', 'http://www.facebook.com/2008/fbml');
		
		return true;
	}
}