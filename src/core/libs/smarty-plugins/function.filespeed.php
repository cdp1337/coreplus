<?php
/**
 * @package Core\Templates\Smarty
 * @since 1.9
 * @author Charlie Powell <charlie@evalagency.com>
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

/**
 * Display a filesize in a human-readable format.
 *
 * #### Example Usage
 *
 * ```
 * {filesize 123} => "123 bytes"
 * {filesize 2048} => "2 kiB"
 * {filesize 2050} => '2.1 kiB"
 * {filesize 2050 round=0} => '2 kiB"
 * ```
 *
 * @param array  $params  Associative (and/or indexed) array of smarty parameters passed in from the template
 * @param Smarty $smarty  Parent Smarty template object
 *
 * @return string
 * @throws SmartyException
 */
function smarty_function_filespeed($params, $smarty){
	
	$size = $params[0];
	
	$round = isset($params['round']) ? $params['round'] : 1; 
	
	$suf = array('bps', 'Kbps', 'Mbps', 'Gbps', 'Tbps', 'Pbps', 'Ebps', 'Zbps', 'Ybps');
	$c   = 0;
	while ($size >= 1024) {
		$c++;
		$size = $size / 1024;
	}

	return \Core\i18n\I18NLoader::FormatNumber($size, $round) . ' ' . $suf[$c];
}