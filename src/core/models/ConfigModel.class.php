<?php
/**
 * Defines the schema for the Config table
 *
 * @package Core
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2015  Charlie Powell
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
 * Model for ConfigModel
 *
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 *
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 2011-06-09 01:14:48
 */
class ConfigModel extends Model {
	public static $Schema = array(
		'key'           => [
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 255,
			'required'  => true,
			'null'      => false,
		],
		'type'          => [
			'type'    => Model::ATT_TYPE_ENUM,
			'options' => ['string', 'text', 'int', 'boolean', 'enum', 'set'],
			'default' => 'string',
			'null'    => false,
		],
		'encrypted' => [
			'type' => Model::ATT_TYPE_BOOL,
			'default' => 0,
		],
		'default_value' => [
			'type'    => Model::ATT_TYPE_TEXT,
			'default' => null,
			'null'    => true,
		],
		'value'         => [
			'type'    => Model::ATT_TYPE_TEXT,
			'default' => null,
			'null'    => true,
		],
		'options'       => [
			'type'      => Model::ATT_TYPE_TEXT,
			'default'   => null,
			'null'      => true,
		],
		'title'         => [
			'type'    => Model::ATT_TYPE_STRING,
			'comment' => 'The title from the config parameter, optional',
		],
		'description'   => [
			'type'    => Model::ATT_TYPE_TEXT,
			'default' => null,
			'null'    => true,
		],
		'mapto'         => [
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 32,
			'default'   => null,
			'comment'   => 'The define constant to map the value to on system load.',
			'null'      => true,
		],
		'overrideable' => [
			'type' => Model::ATT_TYPE_BOOL,
			'default' => false,
			'comment' => 'If children sites can override this configuration option',
		],
		'form_attributes' => [
			'type' => Model::ATT_TYPE_TEXT,
		    'comment' => 'Set from the content of the form-attributes XML parameter.',
		],
		'created'       => [
			'type' => Model::ATT_TYPE_CREATED
		],
		'updated'       => [
			'type' => Model::ATT_TYPE_UPDATED
		]
	);

	public static $Indexes = array(
		'primary' => array('key'),
	);

	/**
	 * Get either the set value or the default value if that is null.
	 *
	 * This value will also be typecasted to the correct type.
	 *
	 * @return mixed
	 */
	public function getValue() {
		$v = $this->get('value');

		// If it's null, it just get the default value.
		if ($v === null){
			$v = $this->get('default');
		}
		// If it's encrypted, decrypt it first!
		// This must be done outside the regular model encryption logic because it's dynamic for each config.
		elseif($this->get('encrypted') && $v !== ''){
			preg_match('/^\$([^$]*)\$([0-9]*)\$(.*)$/m', $v, $matches);

			$cipher = $matches[1];
			$passes = $matches[2];
			$size = openssl_cipher_iv_length($cipher);
			// Now I can trim off the beginning crap from the encrypted string.
			$dec = substr($v, strlen($cipher) + 5, 0-$size);
			$iv = substr($v, 0-$size);

			for($i=0; $i<$passes; $i++){
				$dec = openssl_decrypt($dec, $cipher, SECRET_ENCRYPTION_PASSPHRASE, true, $iv);
			}

			$v = $dec;
		}

		return self::TranslateValue($this->get('type'), $v);
	}

	public function setValue($value){
		if($this->get('encrypted')){
			$cipher = 'AES-256-CBC';
			$passes = 10;
			$size = openssl_cipher_iv_length($cipher);
			$iv = mcrypt_create_iv($size, MCRYPT_RAND);

			$enc = $value;
			for($i=0; $i<$passes; $i++){
				$enc = openssl_encrypt($enc, $cipher, SECRET_ENCRYPTION_PASSPHRASE, true, $iv);
			}

			$payload = '$' . $cipher . '$' . str_pad($passes, 2, '0', STR_PAD_LEFT) . '$' . $enc . $iv;

			return parent::set('value', $payload);
		}
		else{
			return parent::set('value', $value);
		}
	}

	/**
	 * Translate the database value to a strict datatype.
	 * This will ensure that a "boolean" config with value of "0" actually returns (===) false.
	 *
	 * @param $type
	 * @param $value
	 *
	 * @return array|bool|int
	 */
	public static function TranslateValue($type, $value){
		switch ($type) {
			case 'int':
				return (int)$value;
			case 'boolean':
				return ($value == '1' || $value == 'true') ? true : false;
			case 'set':
				return array_map('trim', explode('|', $value));
			default:
				return $value;
		}
	}

	public function getFormAttributes(){
		$opts = [];

		$formOptions = $this->get('form_attributes');
		if($formOptions != ''){
			// Explode them by a semicolon
			$formOptions = array_map('trim', explode(';', $formOptions));
			foreach($formOptions as $o){
				if(($cpos = strpos($o, ':')) !== false){
					$k = substr($o, 0, $cpos);
					$v = substr($o, $cpos+1);

					$opts[$k] = $v;
				}
			}
		}

		if(!isset($opts['type'])){
			// Determine this automatically by the config data type.
			switch ($this->get('type')) {
				case 'string':
				case 'int':
					$opts['type'] = 'text';
					break;
				case 'text':
					$opts['type'] = 'textarea';
					break;
				case 'enum':
					$opts['type'] = 'select';
					break;
				case 'boolean':
					$opts['type'] = 'radio';
					break;
				case 'set':
					$opts['type'] ='checkboxes';
					break;
				default:
					$opts['type'] = 'text';
					break;
			}
		}

		// SELECT
		if($opts['type'] == 'select' || $opts['type'] == 'checkboxes'){
			// This is set from the main option set.
			$opts['options'] =  array_map('trim', explode('|', $this->get('options')));
		}

		// RADIO
		if($opts['type'] == 'radio'){
			$opts['options'] = ['false' => 'No/False', 'true'  => 'Yes/True'];
		}

		$key = $this->get('key');

		$gname = substr($key, 1);
		$gname = ucwords(substr($gname, 0, strpos($gname, '/')));

		// Generate the title dynamically from either the title attribute or the key attribute.
		if(!isset($opts['title'])){
			// If the title is set, use that.  Otherwise pull it from the key name less the group.
			if($this->get('title')){
				$opts['title'] = $this->get('title');
			}
			else{
				$title = substr($key, strlen($gname) + 2);
				// Split the title on '/' and capitalize it to make it more user-friendly.
				$title = str_replace('/', ' ', $title);
				// Same thing for underscores '_', remove them and capitalize the words.
				$title = str_replace('_', ' ', $title);
				$title = ucwords($title);

				$opts['title'] = $title;
			}
		}

		// Description can be set dynamically or pulled from the attributes.
		if(!isset($opts['description'])){
			$desc = $this->get('description');
			if ($this->get('default_value') && $desc) $desc .= ' (default value is ' . $this->get('default_value') . ')';
			elseif ($this->get('default_value')) $desc = 'Default value is ' . $this->get('default_value');

			$opts['description'] = $desc;
		}

		if(!isset($opts['group'])){
			$opts['group'] = $gname;
		}

		// The name can't be set by the XML metadata, but it is based on the type of form element, slightly.
		if($opts['type'] == 'checkboxes'){
			// Append "[]" to the name as there are many checkboxes!
			$opts['name'] = 'config[' . $key . '][]';
		}
		else{
			$opts['name'] = 'config[' . $key . ']';
		}

		return $opts;
	}

	/**
	 * Transpose a populated form element from the underlying ConfigModel object.
	 * Will populate the name, options, validation, etc.
	 *
	 * @return \FormElement
	 *
	 * @throws \Exception
	 */
	public function getAsFormElement(){
		// key is in the format of:
		// /user/displayname/displayoptions

		$key        = $this->get('key');
		$attributes = $this->getFormAttributes();
		$val        = \ConfigHandler::Get($key);
		$type       = $attributes['type'];
		$el         = \FormElement::Factory($type, $attributes);

		if($type == 'radio'){
			// Ensure that this matches what the radios will have.
			if ($val == '1' || $val == 'true' || $val == 'yes') $val = 'true';
			else $val = 'false';
		}

		if($this->get('type') == 'int' && $type == 'text'){
			$el->validation        = '/^[0-9]*$/';
			$el->validationmessage = $attributes['group'] . ' - ' . $attributes['title'] . ' expects only whole numbers with no punctuation.';
		}

		if($type == 'checkboxes' && !is_array($val)){
			// Convert the found value to an array so it matches what checkboxes are expecting.
			$val  = array_map('trim', explode('|', $val));
		}

		$el->set('value', $val);

		return $el;
	}

	/**
	 * Alias of getAsFormElement
	 *
	 * @return \FormElement
	 *
	 * @throws \Exception
	 */
	public function asFormElement(){
		return self::getAsFormElement();
	}
} // END class ConfigModel extends Model
