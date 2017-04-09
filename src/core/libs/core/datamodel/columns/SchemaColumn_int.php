<?php
/**
 * File for class Schema definition in the coreplus project
 * 
 * @package Core\Datamodel
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20131022.1655
 * @copyright Copyright (C) 2009-2017  Charlie Powell
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