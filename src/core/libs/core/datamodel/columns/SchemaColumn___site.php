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


class SchemaColumn___site extends SchemaColumn {
	public function __construct(){
		// Defaults
		$this->type = \Model::ATT_TYPE_SITE;
		$this->maxlength = 15;
		$this->default = 0;
		$this->comment = 'The site id in multisite mode, (or -1 if global)';
		$this->formAttributes['type'] = 'system';
		// Sites have the option to pull from the multisite system when enabled.
		$this->formAttributes['source'] = 'MultiSiteModel::GetAllAsOptions';
	}

	/**
	 * Get the value appropriate for INSERT statements.
	 *
	 * @return string
	 */
	public function getInsertValue(){
		if($this->value === null || $this->value === false){
			if(\Core::IsComponentAvailable('multisite') && \MultiSiteHelper::IsEnabled()){
				$this->setValueFromApp(\MultiSiteHelper::GetCurrentSiteID());	
			}
			else{
				$this->setValueFromApp(0);
			}
		}

		return $this->value;
	}
}