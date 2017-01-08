<?php
/**
 * Collection of useful utilities that don't quite fit into any class and need to be in the global scope.
 *
 * @package Core
 * @author Charlie Powell <charlie@evalagency.com>
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

/**
 * Translate a KEY_STRING to its i18n version
 *
 * @return string
 */
function t(){
	$params   = func_get_args();
	$key      = $params[0];
	static $__tLookupCache = [];
	
	if(func_num_args() == 1){
		// This is a simple lookup with no additional parameters,
		// try to speed up repeated lookups if possible.
		// That is also why this function is written in a longer form than necessary;
		// it's designed to process faster with minimal instruction requirements.
		if(!isset($__tLookupCache[$key])){
			$string = new \Core\i18n\I18NString($key);
			$string->setParameters($params);
			$__tLookupCache[$key] = $string->getTranslation();
		}
		
		return $__tLookupCache[$key];
	}
	else{
		// Complex strings simply get processed by the underlying system as normal.
		$string = new \Core\i18n\I18NString($key);
		$string->setParameters($params);
		return $string->getTranslation();
	}
}
