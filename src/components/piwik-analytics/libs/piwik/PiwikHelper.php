<?php
/**
 * File for class PiwikHelper definition in the coreplus project
 * 
 * @package Piwik
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130619.0232
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

namespace Piwik;
use Core\Search\SearchResults;


/**
 * A short teaser of what PiwikHelper does.
 *
 * More lengthy description of what PiwikHelper does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for PiwikHelper
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
 * @package Piwik
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
abstract class PiwikHelper {
	public static function InstallTracking() {
		$siteid       = \ConfigHandler::Get('/piwik/siteid');
		$server       = \ConfigHandler::Get('/piwik/server/host');
		$all_domains  = \ConfigHandler::Get('/piwik/tracking/all_subdomains');
		$domain_title = \ConfigHandler::Get('/piwik/tracking/domain_title');

		// If there's no code available, don't display anything.
		if(!($siteid && $server)) return;

		// Trim the server.
		$server = trim($server, '/');

		$paqopts = [];

		$paqopts[] = '"trackPageView"';
		$paqopts[] = '"enableLinkTracking"';
		if($all_domains){
			$h = '*.' . HOST;
			if(strpos($h, '*.www.') === 0){
				$h = str_replace('*.www.', '*.', $h);
			}
			$paqopts[] = '"setCookieDomain", "' . $h . '"';
		}
		if($domain_title){
			$paqopts[] = '"setDocumentTitle", document.domain + "/" + document.title';
		}

		$paqstr = '';
		foreach($paqopts as $k => $v){
			$paqstr .= '    _paq.push([' . $v . ']);' . "\n";
		}

		// This version of the script is Piwik's newest version as of 2013.06.19
		$script = <<<EOD

<!-- Piwik -->
<script type="text/javascript">
  var _paq = _paq || [];
$paqstr
  (function() {
    var u=(("https:" == document.location.protocol) ? "https" : "http") + "://$server/";
    _paq.push(['setTrackerUrl', u+'piwik.php']);
    _paq.push(['setSiteId', $siteid]);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0]; g.type='text/javascript';
    g.defer=true; g.async=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
  })();

</script>
<!-- End Piwik Code -->

EOD;

		// Add the necessary scripts
		\Core\view()->addScript($script, 'head');
		\Core\view()->appendBodyContent('<noscript><p><img src="http://' . $server . '/piwik.php?idsite=1" style="border:0" alt="" /></p></noscript>');

		return true;
	}



	public static function SearchResultsTracking(SearchResults $search){
		// trackSiteSearch(keyword, [category], [resultsCount]
		$snippet = '<script>if(typeof(Piwik) != "undefined"){ Piwik.getAsyncTracker().trackSiteSearch("' . htmlentities($search->query) . '", null, ' . $search->getCount() . ');}</script>';
		\Core\view()->addScript($snippet, 'foot');

		return true;
	}

	//public static function SearchHook($dat) {
	//	// Piwik.getAsyncTracker()
	//}
}