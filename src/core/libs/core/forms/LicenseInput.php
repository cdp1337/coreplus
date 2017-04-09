<?php
/**
 * File for class FormLicenseInput definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130716.1408
 * @package Core\Forms
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

namespace Core\Forms;

/**
 * Class FormLicenseInput provides a select box with a pre-populated list of many of the common licenses in the wild.
 *
 * @package Core\Forms
 */
class LicenseInput extends SelectInput {
	public function  __construct($atts = null) {
		parent::__construct($atts);

		$opts = \Core\Licenses\Factory::GetAsOptions();

		$this->set('options', $opts);
	}
}