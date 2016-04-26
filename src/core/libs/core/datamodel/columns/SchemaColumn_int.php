<?php
/**
 * Created by PhpStorm.
 * User: charlie
 * Date: 4/12/16
 * Time: 3:26 PM
 */

namespace Core\Datamodel\Columns;


class SchemaColumn_int extends SchemaColumn {
	public function __construct(){
		// Defaults
		$this->type = \Model::ATT_TYPE_INT;
		$this->maxlength = 15;
		$this->default = 0;
		$this->formAttributes['type'] = 'text';
	}

	/**
	 * Get an array of the form element attributes for this column.
	 *
	 * @return array
	 */
	public function getFormElementAttributes(){
		if($this->formAttributes['type'] == 'datetime'){
			$defaults = [ 
				'datetimepicker_dateformat' => 'yy-mm-dd',
				'datetimepicker_timeformat' => 'HH:mm',
				'displayformat' => 'Y-m-d H:i',
				'saveformat' => 'U',
			];
		}
		else{
			$defaults = [];
		}
		
		$na = parent::getFormElementAttributes();
		
		return array_merge($defaults, $na);
	}
}