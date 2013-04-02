<?php
/**
 * Defines the schema for the Config table
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


/**
 * Model for ConfigModel
 *
 * Generated automatically from the mysql_model_gen script.
 * Please update result to your preferences and copy to the final location.
 *
 * @author Charlie Powell <charlie@eval.bz>
 * @date 2011-06-09 01:14:48
 */
class ConfigModel extends Model {
	public static $Schema = array(
		'key'           => array(
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 255,
			'required'  => true,
			'null'      => false,
		),
		'type'          => array(
			'type'    => Model::ATT_TYPE_ENUM,
			'options' => array('string', 'int', 'boolean', 'enum', 'set'),
			'default' => 'string',
			'null'    => false,
		),
		'encrypted' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'default' => 0,
		),
		'default_value' => array(
			'type'    => Model::ATT_TYPE_TEXT,
			'default' => null,
			'null'    => true,
		),
		'value'         => array(
			'type'    => Model::ATT_TYPE_TEXT,
			'default' => null,
			'null'    => true,
		),
		'options'       => array(
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 511,
			'default'   => null,
			'null'      => true,
		),
		'description'   => array(
			'type'    => Model::ATT_TYPE_TEXT,
			'default' => null,
			'null'    => true,
		),
		'mapto'         => array(
			'type'      => Model::ATT_TYPE_STRING,
			'maxlength' => 32,
			'default'   => null,
			'comment'   => 'The define constant to map the value to on system load.',
			'null'      => true,
		),
		'overrideable' => array(
			'type' => Model::ATT_TYPE_BOOL,
			'default' => false,
			'comment' => 'If children sites can override this configuration option',
		),
		'created'       => array(
			'type' => Model::ATT_TYPE_CREATED
		),
		'updated'       => array(
			'type' => Model::ATT_TYPE_UPDATED
		)
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

} // END class ConfigModel extends Model
