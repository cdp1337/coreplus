<?php
/**
 * Created by PhpStorm.
 * User: charlie
 * Date: 12/7/15
 * Time: 1:42 PM
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

		if(substr_count($this->_key, '_N_') === 1 && sizeof($this->_params) == 2 && is_numeric($this->_params)){
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

		if(!$this->_resultIsFound && DEVELOPMENT_MODE){
			// Provide some feedback to developers if this key was not found.
			$str = '[' . $this->_resultMatchedLang . ':' . $key . ']';
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