<?php
/**
 * Created by PhpStorm.
 * User: charlie
 * Date: 4/12/16
 * Time: 3:26 PM
 */

namespace Core\Datamodel\Columns;

use Core\Date\DateTime;

class SchemaColumn___updated extends SchemaColumn {
	public function __construct(){
		// Defaults
		$this->type = \Model::ATT_TYPE_UPDATED;
		$this->maxlength = 15;
		$this->default = 0;
		$this->formAttributes['type'] = 'disabled';
	}

	/**
	 * Get the value appropriate for INSERT statements.
	 *
	 * @return string
	 */
	public function getInsertValue(){
		// Updated mimics that of created for inserts. is an auto flag for the timestamp NOW on saves (inserts).
		if(!$this->value){
			$this->setValueFromApp(DateTime::NowGMT());
		}

		return $this->value;
	}
	
	/**
	 * Get the value appropriate for UPDATE statements.
	 *
	 * @return string
	 */
	public function getUpdateValue(){
		$this->setValueFromApp(DateTime::NowGMT());

		return $this->value;
	}
}