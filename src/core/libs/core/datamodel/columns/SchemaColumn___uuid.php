<?php
/**
 * Created by PhpStorm.
 * User: charlie
 * Date: 4/12/16
 * Time: 3:26 PM
 */

namespace Core\Datamodel\Columns;


class SchemaColumn___uuid extends SchemaColumn {
	public function __construct(){
		// Defaults
		$this->type = \Model::ATT_TYPE_UUID;
		$this->maxlength = 32;
		$this->encoding = \Model::ATT_ENCODING_UTF8;
		$this->formAttributes['type']     = 'system';
	}

	/**
	 * Get the value appropriate for INSERT statements.
	 *
	 * @return string
	 */
	public function getInsertValue(){
		if(!$this->value){
			$this->setValueFromApp(\Core\generate_uuid());
		}

		return $this->value;
	}
}