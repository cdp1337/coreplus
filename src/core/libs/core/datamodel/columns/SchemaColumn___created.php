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