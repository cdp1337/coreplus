<?php
/**
 * // enter a good description here
 * 
 * @package Core
 * @since 2011.06
 * @author Charlie Powell <powellc@powelltechs.com>
 * @copyright Copyright 2011, Charlie Powell
 * @license GNU Lesser General Public License v3 <http://www.gnu.org/licenses/lgpl-3.0.html>
 * This system is licensed under the GNU LGPL, feel free to incorporate it into
 * custom applications, but keep all references of the original authors intact,
 * read the full license terms at <http://www.gnu.org/licenses/lgpl-3.0.html>, 
 * and please contribute back to the community :)
 */

class Debug{
	
	public static function Write($text){
		if(!FULL_DEBUG) return;
		/*
		$out = '';
		foreach($argv as $arg){
			if($arg typeof 'Array') $out .= '<span class="cae2_debug_array">Array ' . 
		}*/
		if(EXEC_MODE == 'CLI') echo '[ DEBUG ] - ' . $text . "\n";
		else echo "<div class='cae2_debug'>" . $text . "</div>"; 
	}
}
