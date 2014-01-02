<?php
/**
 * Class file for FormTimeInput
 *
 * @package Core\Forms
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2014  Charlie Powell
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

/**
 * Class FormTimeInput provides a select box with times in 15-minute intervals.
 *
 * @package Core\Forms
 */
class FormTimeInput extends FormSelectInput {
	public function  __construct($atts = null) {
		parent::__construct($atts);

		// Set the options for this input as the times in 15-minute intervals
		// @todo Implement a config option to allow this to be changed to different intervals.
		// @todo also, allow for switching the time view based on a configuration preference.
		$times = array();

		// Default (blank)
		$times[''] = '---';

		for ($x = 0; $x < 24; $x++) {
			$hk = $hd = $x;
			if (strlen($hk) == 1) $hk = '0' . $hk;
			if($hd == 12){
				$ap = 'pm';
			}
			elseif ($hd > 12) {
				$hd -= 12;
				$ap = 'pm';
			}
			elseif ($hd == 0) {
				$hd = 12;
				$ap = 'am';
			}
			else {
				$ap = 'am';
			}

			$times["$hk:00"] = "$hd:00 $ap";
			$times["$hk:15"] = "$hd:15 $ap";
			$times["$hk:30"] = "$hd:30 $ap";
			$times["$hk:45"] = "$hd:45 $ap";
		}

		$this->set('options', $times);
	}
}