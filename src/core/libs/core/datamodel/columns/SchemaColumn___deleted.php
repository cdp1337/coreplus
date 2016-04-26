<?php
/**
 * Created by PhpStorm.
 * User: charlie
 * Date: 4/12/16
 * Time: 3:26 PM
 */

namespace Core\Datamodel\Columns;


class SchemaColumn___deleted extends SchemaColumn {
	public function __construct(){
		// Defaults
		$this->type = \Model::ATT_TYPE_DELETED;
		$this->maxlength = 15;
		$this->default = 0;
		$this->formAttributes['type'] = 'disabled';
	}
}