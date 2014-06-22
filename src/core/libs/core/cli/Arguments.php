<?php
/**
 * File for class Arguments definition in the coreplus project
 *
 * @package Core\CLI
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20131204.1445
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

namespace Core\CLI;

if(!defined('NL')){
	define('NL', "\n");
}


/**
 * Provides a utility layer to easily manage and use command line arguments.
 *
 * <h3>Usage Examples</h3>
 *
 * $arguments = new \Core\CLI\Arguments(
 *     [
 *         'help' => [
 *             'description' => 'Display help and exit.',
 *             'value' => false,
 *             'shorthand' => ['?', 'h'],
 *         ],
 *         'component' => [
 *             'description' => 'Operate on a component with the given name.',
 *             'value' => true,
 *             'shorthand' => ['c'],
 *             ],
 *         ],
 *     ]
 * );
 * $arguments->usageHeader = 'A little more information to the user as to what this script does.';
 *
 * // Process and validate those arguments now.
 * $arguments->processArguments();
 *
 * if($arguments->getArgumentValue('help')){
 *     $arguments->printUsage();
 *     exit;
 * }
 *
 * if($arguments->getArgumentValue('component')){
 *     // Do something with this option.
 * }
 *
 *
 * @package Core\CLI
 * @author Charlie Powell <charlie@eval.bz>
 *
 */
class Arguments {
	protected $_args = [];

	public $usageHeader = 'Usage:';

	/**
	 * Construct a new Arguments set with the set of arguments allowed.
	 *
	 * @param array $allowed_arguments Allowed arguments to send.
	 */
	public function __construct($allowed_arguments = array()){
		foreach($allowed_arguments as $key => $arg){
			if(!isset($arg['name'])) $arg['name'] = $key;

			$this->addAllowedArgument($arg);
		}
	}

	public function addAllowedArgument($argument_data){
		/*
		'description' => 'List out the available components along with  their versions and exit.',
		'value' => false,
		'shorthand' => [],
		*/
		$this->_args[] = new Argument($argument_data);
	}

	public function printUsage(){
		$out = $this->usageHeader . NL . NL;

		$advancedopts = [];
		$maxlen = 0;

		foreach($this->_args as $arg){
			/** @var Argument $arg */

			$line = '';
			if(sizeof($arg->shorthands)){
				foreach($arg->shorthands as $s){
					$line .= '-' . $s . ', ';
				}
			}
			$line .= '--' . $arg->name;
			if($arg->requireValue){
				$line .= '=VALUE';
			}

			$maxlen = max($maxlen, strlen($line));

			$advancedopts[$line] = $arg->description;
		}

		foreach($advancedopts as $opt => $desc){
			$lineprefix = '  ' . $opt . ' ' . str_repeat('.', ($maxlen - strlen($opt)) ) . '.. ';
			$descparts = explode("\n", wordwrap($desc, 80));

			$out .= $lineprefix;
			foreach($descparts as $i => $idesc){
				if($i == 0){
					$out .= $idesc . NL;
				}
				else{
					$out .= str_repeat(' ', strlen($lineprefix)) . $idesc . NL;
				}
			}
		}

		echo $out;
	}

	/**
	 * Process the actual arguments passed in from the user.
	 */
	public function processArguments(){
		global $argc, $argv;

		if($argc > 1){

			$args = $argv;
			// Drop the first, that is the filename.
			array_shift($args);

			$unnamed_args = 0;

			// Shorthand map of arguments.
			$shorthands = [];
			foreach($this->_args as $arg){
				/** @var Argument $arg */
				foreach($arg->shorthands as $s){
					$shorthands['-' . $s] = $arg;
				}
			}

			// And a map of the regular arguments.
			$arguments = [];
			foreach($this->_args as $arg){
				/** @var Argument $arg */
				$arguments[ $arg->name ] = $arg;
			}


			// I'm using a for here instead of a foreach so I can increment $i artificially if an argument is two part,
			// ie: --option value_for_option --option2 value_for_option2
			for($i = 0; $i < sizeof($args); $i++){
				/** @var string $arg */
				$arg = $args[$i];

				if(strlen($arg) == 2 && $arg{0} == '-'){
					// Lookup this argument from the shorthand arguments.
					if(isset($shorthands[$arg])){
						/** @var Argument $dest */
						$dest = $shorthands[$arg];

						if($dest->requireValue){
							// This option requires a value.
							// This value is the next argument.
							$dest->setValue($args[ ++$i ]);
						}
						else{
							$dest->setValue(true);
						}
					}
					else{
						$this->printError('Invalid shorthand argument provided: ' . $arg);
					}
				}
				elseif(preg_match('#--([^=]*)=#', $arg)){
					$opt = preg_replace('#--([^=]*)=.*#', '$1', $arg);
					if(isset($arguments[$opt])){
						/** @var Argument $dest */
						$dest = $arguments[$opt];

						if($dest->requireValue){
							$dest->setValue( substr($arg, strpos($arg, '=')+1) );
						}
						else{
							$this->printError($opt . ' does not support any values.');
						}
					}
					else{
						$this->printError('Invalid argument provided: ' . $opt);
					}
				}
				elseif(strpos($arg, '--') === 0){
					$opt = substr($arg, 2);
					if(isset($arguments[$opt])){
						/** @var Argument $dest */
						$dest = $arguments[$opt];

						if($dest->requireValue){
							$this->printError($opt . ' requires a value.');
						}
						else{
							$dest->setValue(true);
						}
					}
					else{
						$this->printError('Invalid argument provided: ' . $opt);
					}
				}
				else{
					$arguments[$unnamed_args] = new Argument();
					/** @var Argument $dest */
					$dest = $arguments[$unnamed_args];
					$dest->name = $unnamed_args;
					$dest->description = 'Unnamed Argument ' . $unnamed_args;
					$dest->setValue($arg);

					$unnamed_args++;
				}
			}
		}
	}

	/**
	 * Get all the arguments as an array.
	 *
	 * @return array
	 */
	public function getArguments(){
		return $this->_args;
	}

	/**
	 * Get the argument object itself, or null if it doesn't exist.
	 *
	 * @param string $name
	 *
	 * @return Argument|null
	 */
	public function getArgument($name){
		foreach($this->_args as $arg){
			/** @var Argument $arg */
			if($arg->name == $name){
				return $arg;
			}
		}

		return null;
	}

	/**
	 * Get the argument value or null if it doesn't exist.
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function getArgumentValue($name){
		return (($arg = $this->getArgument($name)) === null) ? null : $arg->value;
	}

	/**
	 * Shortcut of getArgumentValue.
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function getVal($name){
		return $this->getArgumentValue($name);
	}

	/**
	 * Print an error to the STDOUT and exit the script.
	 *
	 * @param string $error
	 */
	public function printError($error){
		echo 'ERROR: ' . $error . NL;
		$this->printUsage();
		exit;
	}
} 