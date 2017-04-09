<?php
/**
 * File for class Arguments definition in the coreplus project
 *
 * @package Core\CLI
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20131204.1445
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
 *             'shorthand' => 'c',
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
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class Arguments {
	protected $_args = [];

	/** @var string Short header message when executing printUsage(). */
	public $usageHeader = 'Usage:';

	/** @var bool Keep track of if processArguments has been executed. */
	private $_processed = false;

	/**
	 * Construct a new Arguments set with the set of arguments allowed.
	 *
	 * @param array $allowed_arguments Allowed arguments to send.
	 */
	public function __construct($allowed_arguments = []){
		// Provide smart defaults
		if(!isset($allowed_arguments['help'])){
			$allowed_arguments['help'] = [
				'description' => 'Display help and exit.',
				'value' => false,
				'shorthand' => ['?', 'h'],
			];
		}

		foreach($allowed_arguments as $key => $arg){
			if(!isset($arg['name'])) $arg['name'] = $key;

			$this->addAllowedArgument($arg);
		}
	}

	/**
	 * Add a new allowed argument to the script.
	 *
	 * The array supports the following keys:
	 * <dl>
	 *
	 * <dt>name (required)</dt>
	 * <dd>Name of this argument, required and must not contain spaces, used to generate the "--[name]" context.
	 *
	 * <dt>description (recommended)</dt>
	 * <dd>Help text to print to the user when executing printUsage().</dd>
	 *
	 * <dt>value</dt>
	 * <dd>
	 * True/false if this argument requires or supports a value set.
	 * If false, simply --[name] is used.
	 * If true, --[name]="blah" is used.
	 * </dd>
	 *
	 * <dt>shorthand</dt>
	 * <dd>
	 * Any shorthand arguments that are allowed, these are exposed via a single dash on the CLI.
	 * Can be either an array or a string.
	 * </dd>
	 *
	 * <dt>multiple</dt>
	 * <dd>
	 * Set to true if multiple instances of the same argument are allowed.
	 * This is useful if you need to allow the user to provide a list of something.
	 * Setting this to true will force getVal and getArgumentValue to always return an array.
	 * </dd>
	 *
	 * </dl>
	 *
	 * @param array $argument_data Array data of the argument to add
	 */
	public function addAllowedArgument($argument_data){
		/*
		 *
		'description' => 'List out the available components along with  their versions and exit.',
		'value' => false,
		'shorthand' => [],
		*/
		$this->_args[] = new Argument($argument_data);
	}

	/**
	 * Print usage of this argument set to STDOUT.
	 */
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

		// The new hint!
		$out .= NL . 'HINT!' . NL . 'You can specify a short version of any "--" argument!' . NL .
			'For example, if the arguments are --dofoo and --dobaz, you can use the shortest' . NL .
			'unique version of them, in this case, --dof and --dob.' . NL;

		echo $out;
	}

	/**
	 * Process the actual arguments passed in from the user.
	 *
	 * This is a required step if you want this system to work as intended.
	 */
	public function processArguments(){
		global $argc, $argv;

		$this->_processed = true;

		if($argc > 1){

			$args = $argv;
			// Drop the first, that is the filename.
			array_shift($args);

			$unnamed_args = 0;

			// Shorthand map of arguments.
			$shorthands = [];
			foreach($this->_args as $arg){
				/** @var Argument $arg */
				if(!is_array($arg->shorthands)){
					$arg->shorthands = [$arg->shorthands];
				}

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
			$size = sizeof($args);
			for($i = 0; $i < $size; $i++){
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
						$this->printError('Unknown shorthand argument provided: ' . $arg);
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
						// Try to find this argument as a shortcut!
						// This will allow a user to provide "--verb=2" instead of "verbose=2"
						// Or even as low as "--v=2" if there are no other "v*" words!
						$matches = [];
						foreach($arguments as $k => $ka){
							if(strpos($k, $opt) === 0){
								$matches[] = $k;
							}
						}
						if(sizeof($matches) == 0){
							// Goldilox found the bed to be too small!
							$this->printError('Unknown argument provided: ' . $opt);
						}
						elseif(sizeof($matches) > 1){
							// Goldilox found this bed to be too effing big!
							$this->printError('Ambiguous argument provided: ' . $opt . ' (Matches the following arguments: ' . implode(', ', $matches) . ')');
						}
						else{
							// Just right for one Goldilox!
							/** @var Argument $dest */
							$dest = $arguments[$matches[0]];

							if($dest->requireValue){
								$dest->setValue( substr($arg, strpos($arg, '=')+1) );
							}
							else{
								$this->printError($opt . ' does not support any values.');
							}
						}
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
						// Try to find this argument as a shortcut!
						// This will allow a user to provide "--verb=2" instead of "verbose=2"
						// Or even as low as "--v=2" if there are no other "v*" words!
						$matches = [];
						foreach($arguments as $k => $ka){
							if(strpos($k, $opt) === 0){
								$matches[] = $k;
							}
						}
						if(sizeof($matches) == 0){
							// Goldilox found the bed to be too small!
							$this->printError('Unknown argument provided: ' . $opt);
						}
						elseif(sizeof($matches) > 1){
							// Goldilox found this bed to be too effing big!
							$this->printError('Ambiguous argument provided: ' . $opt . ' (Matches the following arguments: ' . implode(', ', $matches) . ')');
						}
						else{
							// Just right for one Goldilox!
							/** @var Argument $dest */
							$dest = $arguments[$matches[0]];

							if($dest->requireValue){
								$this->printError($matches[0] . ' requires a value.');
							}
							else{
								$dest->setValue(true);
							}
						}
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

		// Should the help be rendered out?
		if($this->getArgumentValue('help')){
			$this->printUsage();
			exit;
		}
	}

	/**
	 * Get all the arguments as an array.
	 *
	 * @return array
	 */
	public function getArguments(){
		if(!$this->_processed){
			// This methods requires arguments to have been processed from STDIN!
			$this->processArguments();
		}

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
		if(!$this->_processed){
			// This methods requires arguments to have been processed from STDIN!
			$this->processArguments();
		}

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
		CLI::PrintError('ERROR: ' . $error);
		$this->printUsage();
		exit;
	}
} 