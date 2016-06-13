<?php
/**
 * Created by PhpStorm.
 * User: charlie
 * Date: 4/12/16
 * Time: 3:26 PM
 */

namespace Core\Datamodel\Columns;


class SchemaColumn_boolean extends SchemaColumn {
	public function __construct(){
		// Defaults
		$this->type = \Model::ATT_TYPE_BOOL;
		$this->encoding = \Model::ATT_ENCODING_UTF8;
		$this->default = '0';
		$this->formAttributes['type'] = 'radio';
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

		$this->valueTranslated = ($val == '0' || $val == '') ? false : true;
	}

	/**
	 * Set the value from the application/userspace for this column
	 *
	 * Handles all translations and conversions as necessary.
	 *
	 * @param mixed $val
	 */
	public function setValueFromApp($val){
		// Convert yes-ish values to TRUE and no-ish values to FALSE.
		if(
			$val === '0' || $val === 0 || 
			$val === '' || 
			$val === 'no' || $val === 'NO'
		){
			$val = false;
		}
		elseif(
			$val == '1' || $val === 1 || 
			$val === 'yes' || $val === 'YES' || 
			$val === 'on' || $val === 'ON' ||
			$val === 'true' || $val === 'TRUE'
		){
			$val = true;
		}
		
		$this->valueTranslated = $val;

		$this->value = $val ? '1' : '0';
	}

	/**
	 * Get an array of the form element attributes for this column.
	 *
	 * @return array
	 */
	public function getFormElementAttributes(){
		$na = parent::getFormElementAttributes();

		$na['options'] = ['yes' => t('STRING_YES'), 'no' => t('STRING_NO')];
		
		// The application is expecting true/false, but those don't translate too well to html :p
		if($this->valueTranslated === null){
			$na['value'] = null;
		}
		else{
			$na['value'] = $this->valueTranslated ? 'yes' : 'no';	
		}

		return $na;
	}
}