<?php
/**
 * File for class Helper definition in the coreplus project
 * 
 * @package Google\Analytics
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130606.1922
 * @copyright Copyright (C) 2009-2017  Charlie Powell
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses/agpl-3.0.txt.
 */

namespace Google\Analytics;


/**
 * A short teaser of what Helper does.
 *
 * More lengthy description of what Helper does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for Helper
 * <h4>Example 1</h4>
 * <p>Description 1</p>
 * <code>
 * // Some code for example 1
 * $a = $b;
 * </code>
 *
 *
 * <h4>Example 2</h4>
 * <p>Description 2</p>
 * <code>
 * // Some code for example 2
 * $b = $a;
 * </code>
 *
 * 
 * @package Google\Analytics
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
abstract class Helper {
	public static function InstallTracking(){
		$gacode  = \ConfigHandler::Get('/google-analytics/accountid');
		$tagcode = \ConfigHandler::Get('/google/tagmanager/tagid');
		//$site = HOST;
		$min = \ConfigHandler::Get('/core/markup/minified');

		// If there's no code available, don't display anything.


		if($tagcode){
			// Tag Manager, load EITHER this or analytics.
			$script = <<<EOD
<noscript>
	<iframe src="//www.googletagmanager.com/ns.html?id=$tagcode" height="0" width="0" style="display:none;visibility:hidden"></iframe>
</noscript>
<script>
	(function(w,d,s,l,i){
		w[l]=w[l]||[];
		w[l].push( {
			'gtm.start': new Date().getTime(),
			event:'gtm.js'
		});
		var f=d.getElementsByTagName(s)[0],
			j=d.createElement(s),
			dl=l!='dataLayer'?'&l='+l:'';
		j.async=true;
		j.src='//www.googletagmanager.com/gtm.js?id='+i+dl;
		f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','$tagcode');
</script>
EOD;
		}
		elseif($gacode){
			// This version of the script is Google's newest version as of 2014.05
			$script = <<<EOD
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', '$gacode', 'auto');
  ga('send', 'pageview');
</script>
EOD;
		}
		else{
			return null;
		}

		// Just to make it a little smaller...
		if($min) $script = trim(str_replace(array("\n", "\r"), '', $script));

		// Add the necessary script
		\Core\view()->addScript($script, 'foot');

		return true;
	}
}