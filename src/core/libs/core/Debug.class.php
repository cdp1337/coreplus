<?php
/**
 * [PAGE DESCRIPTION HERE]
 *
 * @package Core Plus\Core
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2012  Charlie Powell
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

class Debug {

	public static function Write($text) {
		if (!FULL_DEBUG) return;
		/*
		$out = '';
		foreach($argv as $arg){
			if($arg typeof 'Array') $out .= '<span class="cae2_debug_array">Array ' . 
		}*/
		if (EXEC_MODE == 'CLI') echo '[ DEBUG ] - ' . $text . "\n";
		else echo '<pre class="xdebug-var-dump screen">' . $text . '</pre>';
	}
}
