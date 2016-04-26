<?php
/**
 * Created by PhpStorm.
 * User: charlie
 * Date: 4/12/16
 * Time: 3:26 PM
 */

namespace Core\Datamodel\Columns;

use Core\Date\DateTime;

class SchemaColumn___created extends SchemaColumn {
	public function __construct(){
		// Defaults
		$this->type = \Model::ATT_TYPE_CREATED;
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
		// CREATED is an auto flag for the timestamp NOW on saves (inserts).
		if(!$this->value){
			$this->setValueFromApp(DateTime::NowGMT());
		}
		
		return $this->value;
	}
}