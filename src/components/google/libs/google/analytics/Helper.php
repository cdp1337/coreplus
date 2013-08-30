<?php
/**
 * File for class Helper definition in the coreplus project
 * 
 * @package Google\Analytics
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130606.1922
 * @copyright Copyright (C) 2009-2013  Author
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
 * @author Charlie Powell <charlie@eval.bz>
 *
 */
abstract class Helper {
	public static function InstallTracking(){
		$gacode = \ConfigHandler::Get('/google-analytics/accountid');
		//$site = HOST;
		$min = \ConfigHandler::Get('/core/markup/minified');

		// If there's no code available, don't display anything.
		if(!$gacode) return;

		// This version of the script is Google's newest version as of 2013.07
		$script = <<<EOD

		<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '$gacode']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script');
    ga.type = 'text/javascript';
    ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(ga, s);
  })();

</script>
EOD;
		// Just to make it a little smaller...
		if($min) $script = trim(str_replace(array("\n", "\r"), '', $script));

		// Add the necessary script
		\Core\view()->addScript($script, 'head');

		return true;
	}
}