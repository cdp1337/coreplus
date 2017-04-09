<?php
/**
 * File for class Loader definition in the coreplus project
 * 
 * @package Core\i18n
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20140326.2321
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

namespace Core\i18n;


class I18NString {

	/** @var string The string key to lookup */
	private $_key;
	/** @var array Any parameters to inject into the string */
	private $_params = [];
	/** @var null|string The language to request  */
	private $_lang = null;

	private $_resultIsFound;
	private $_resultMatchedLang;
	private $_resultMatchedString;
	private $_resultAllResults;

	/**
	 * Create a new String translation request based on the given string key
	 *
	 * @param string $key
	 */
	public function __construct($key){
		$this->_key = $key;
	}

	/**
	 * Set the parameters for this translation key
	 *
	 * @var array $params
	 */
	public function setParameters($params){
		$this->_params = $params;
	}

	public function setLanguage($lang){
		$this->_lang = $lang;
	}

	/**
	 * Get the translation for this requested string in the requested language
	 *
	 * Will also update the resolved metadata on this object.
	 *
	 * @return string
	 */
	public function getTranslation(){

		// Allow the key to be changed from _N_ to _0_ or _1_ if exactly two arguments are provided (the key and one number),
		// and of course if _N_ is present in the originally requested key.

		if(substr_count($this->_key, '_N_') === 1 && sizeof($this->_params) == 2 && is_numeric($this->_params[1])){
			if($this->_params[1] == 0){
				$key = str_replace('_N_', '_0_', $this->_key);
			}
			elseif($this->_params[1] == 1){
				$key = str_replace('_N_', '_1_', $this->_key);
			}
			else{
				$key = $this->_key;
			}
		}
		else{
			$key = $this->_key;
		}

		$result   = I18NLoader::Get($key, $this->_lang);

		$this->_resultIsFound       = $result['found'];
		$this->_resultMatchedLang   = $result['lang'];
		$this->_resultMatchedString = $result['match_str'];
		$this->_resultAllResults    = $result['results'];

		if(!$this->_resultIsFound){
			if(DEVELOPMENT_MODE){
				// Provide some feedback to developers if this key was not found.
				$opts = implode(', ', $this->_params);
				$str = '[' . $this->_resultMatchedLang . ':' . $opts . ']';
			}
			else{
				$str = $this->_key;
			}
		}
		else{
			// Replace "[%KEY%]" with the parameters, (if there are any).
			$str = $this->_resultMatchedString;
			foreach($this->_params as $k => $v){
				$str = str_replace("[%{$k}%]", $v, $str);
			}
		}

		return $str;
	}
}