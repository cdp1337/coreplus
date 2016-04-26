<?php
/**
 * Created by PhpStorm.
 * User: charlie
 * Date: 4/12/16
 * Time: 3:26 PM
 */

namespace Core\Datamodel\Columns;


class SchemaColumn___uuid_fk extends SchemaColumn {
	public function __construct(){
		// Defaults
		$this->type                       = \Model::ATT_TYPE_UUID_FK;
		$this->maxlength                  = 32;
		$this->encoding                   = \Model::ATT_ENCODING_UTF8;
		$this->formAttributes['type']     = 'system';
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
			$val = '0';
		}


		if($this->encrypted){
			// Decrypt the value last.
			$val = \Model::EncryptValue($val);
		}

		$this->value = $val;
	}
}