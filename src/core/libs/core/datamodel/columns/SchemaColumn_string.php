<?php
/**
 * Created by PhpStorm.
 * User: charlie
 * Date: 4/12/16
 * Time: 3:26 PM
 */

namespace Core\Datamodel\Columns;


class SchemaColumn_string extends SchemaColumn {
	public function __construct(){
		// Defaults
		$this->type = \Model::ATT_TYPE_STRING;
		$this->maxlength = 255;
		$this->encoding = \Model::ATT_ENCODING_UTF8;
		$this->default = '';
		$this->formAttributes['type'] = 'text';
	}

	/**
	 * Set the value from the database for this column
	 *
	 * Handles all translations and conversions as necessary.
	 *
	 * @param mixed $val
	 */
	public function setValueFromDB($val){
		$this->valueDB = $val;
		$this->value = $val;

		if($this->encrypted){
			// Decrypt the value first.
			$val = \Model::DecryptValue($val);
		}

		$this->valueTranslated = (string)$val;
	}

	/**
	 * Set the value from the application/userspace for this column
	 *
	 * Handles all translations and conversions as necessary.
	 *
	 * @param mixed $val
	 */
	public function setValueFromApp($val){
		$this->valueTranslated = $val;

		if($val === null && !$this->null){
			$val = '';
		}
		
		if($this->encrypted){
			// Decrypt the value last.
			$val = \Model::EncryptValue($val);
		}

		$this->value = $val;
	}

	/**
	 * Check if this value has changed between the database and working copy.
	 *
	 * @return bool
	 */
	public function changed(){
		return !\Core\compare_strings($this->valueDB, $this->value);
	}
}