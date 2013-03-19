<?php
/**
 *
 * User: powellc
 * Date: 7/6/12
 * Time: 5:10 PM
 * To change this template use File | Settings | File Templates.
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