<?php
/**
 * Created by PhpStorm.
 * User: charlie
 * Date: 4/12/16
 * Time: 3:26 PM
 */

namespace Core\Datamodel\Columns;


class SchemaColumn_ISO_8601_datetime extends SchemaColumn {
	public function __construct(){
		// Defaults
		$this->type = \Model::ATT_TYPE_ISO_8601_DATETIME;
		$this->default = '0000-00-00 00:00:00';
		$this->formAttributes['datepicker_dateformat'] = 'yy-mm-dd';
		$this->formAttributes['datetimepicker_timeformat'] = 'HH:mm';
		$this->formAttributes['saveformat'] = 'Y-m-d H:i:00';
		$this->formAttributes['type'] = 'datetime';
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

		if($val === '' || $val === '0000-00-00 00:00:00' || $val === null){
			$val = $this->null ? null : $this->default;
		}

		if($this->encrypted){
			// Decrypt the value last.
			$val = \Model::EncryptValue($val);
		}

		$this->value = $val;
	}
}