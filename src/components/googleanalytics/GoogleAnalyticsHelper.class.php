<?php

class GoogleAnalyticsHelper {
	public static function InstallTracking(){
		$gacode = ConfigHandler::Get('/google-analytics/accountid');
		$min = true;
		
		// If there's no code available, don't display anything.
		if(!$gacode) return;
		
		// This version of the script is Google's newest version as of 2011.09.22
		$script = <<<EOD
<script type="text/javascript">

  var _gaq = _gaq || [];
  var pluginUrl = '//www.google-analytics.com/plugins/ga/inpage_linkid.js';
  _gaq.push(['_require', 'inpage_linkid', pluginUrl]);
  _gaq.push(['_setAccount', '$gacode']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
EOD;
		// Just to make it a little smaller...
		if($min) $script = trim(str_replace(array("\n", "\r"), '', $script));
		
		// Add the necessary script
		CurrentPage::AddScript($script, 'head');
		
		return true;
	}
}