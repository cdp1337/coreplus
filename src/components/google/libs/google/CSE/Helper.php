<?php
/**
 * File for class Helper definition in the coreplus project
 *
 * @package Google\Analytics
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130606.1922
 * @copyright Copyright (C) 2009-2016  Charlie Powell
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

namespace Google\CSE;


/**
 * A short teaser of what Helper does.
 *
 * More lengthy description of what Helper does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
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
 * @package Google\CSE
 * @author Nicholas Hinsch <nicholas@evalagency.com>
 *
 */
abstract class Helper {
	public static function SiteSearch(){
		$key = \ConfigHandler::Get('/google/cse/key');
		$min = \ConfigHandler::Get('/core/markup/minified');

		// If there's no code available, don't display anything.
		if(!$key) return;

		$script = <<<EOD

		<script>
			(function() {
				var cx = '$key';
				var gcse = document.createElement('script');
				gcse.type = 'text/javascript';
				gcse.async = true;
				gcse.src = (document.location.protocol == 'https:' ? 'https:' : 'http:') +
						'//www.google.com/cse/cse.js?cx=' + cx;
				var s = document.getElementsByTagName('script')[0];
				s.parentNode.insertBefore(gcse, s);
			})();
		</script>
EOD;
		// Just to make it a little smaller...
		if($min) $script = trim(str_replace(array("\n", "\r"), '', $script));

		// Add the necessary script
		\Core\view()->addScript($script, 'foot');

		return true;
	}
}