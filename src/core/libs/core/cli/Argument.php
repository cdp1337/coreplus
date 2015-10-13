<?php
/**
 * File for class Argument definition in the coreplus project
 * 
 * @package Core\CLI
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20131204.1445
 * @copyright Copyright (C) 2009-2015  Charlie Powell
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

namespace Core\CLI;


/**
 * A short teaser of what Argument does.
 *
 * More lengthy description of what Argument does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for Argument
 * <h4>Example 1</h4>
 * <p>Description 1</p>
 * <code>
 * // Some code for example 1
 * $a = $b;
 * </code>
 *
 *
 * <h4>Example 2</h4>
 * <p>Description 2</p>
 * <code>
 * // Some code for example 2
 * $b = $a;
 * </code>
 *
 * 
 * @package Core\CLI
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class Argument {
	public $name;
	public $shorthands = [];
	public $description;
	public $requireValue = false;
	public $value = null;
	public $multiple = false;
	public $assign = null;

	public function __construct($dat = array()){
		if(isset($dat['name'])){
			$this->name = $dat['name'];
		}
		if(isset($dat['shorthand'])){
			$this->shorthands = $dat['shorthand'];
		}
		if(isset($dat['description'])){
			$this->description = $dat['description'];
		}
		if(isset($dat['value'])){
			$this->requireValue = $dat['value'];
		}
		if(isset($dat['multiple'])){
			$this->multiple = $dat['multiple'];
		}
		if(array_key_exists('assign', $dat)){
			$this->assign =& $dat['assign'];
		}
		if(array_key_exists('default', $dat)){
			$this->setValue($dat['default']);
		}

		// Allow shorthands to be sent as a single string.
		if(!is_array($this->shorthands)){
			$this->shorthands = [$this->shorthands];
		}
	}

	public function setValue($val){
		if($this->multiple){
			if(!is_array($this->value)) $this->value = [];

			$this->value[] = $val;
		}
		else{
			$this->value = $val;
		}

		$this->assign = $this->value;
	}
} 