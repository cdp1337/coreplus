<?php

class GoogleAnalyticsHelper {
	public static function InstallTracking(){
		$gacode = ConfigHandler::Get('/google-analytics/accountid');
		$min = true;
		
		// If there's no code available, don't display anything.
		if(!$gacode) return;
		
		// This version of the script is Google's newest version as of 2011.09.22
		$script = <<<EOD
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', '$gacode', 'eval.bz');
  ga('send', 'pageview');

</script>
EOD;
		// Just to make it a little smaller...
		if($min) $script = trim(str_replace(array("\n", "\r"), '', $script));
		
		// Add the necessary script
		CurrentPage::AddScript($script, 'head');
		
		return true;
	}
}