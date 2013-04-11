<?php
/**
 * [PAGE DESCRIPTION HERE]
 *
 * @package Core
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2012  Charlie Powell
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

class HookHandler implements ISingleton {

	private static $RegisteredHooks = array();

	private static $Instance = null;

	private static $EarlyRegisteredHooks = array();

	private function __construct() {

	}

	public static function Singleton() {
		if (is_null(self::$Instance)) self::$Instance = new self();
		return self::$Instance;
	}

	public static function GetInstance() {
		return self::singleton();
	}

	/**
	 * Attach a call onto an existing hook.
	 *
	 * @param string       $hookName The name of the hook to bind to.
	 * @param string|array $callFunction The function to call.
	 *
	 * @return void
	 */
	public static function AttachToHook($hookName, $callFunction) {
		$hookName = strtolower($hookName); // Case insensitive will prevent errors later on.
		Core\Utilities\Logger\write_debug('Registering function ' . $callFunction . ' to hook ' . $hookName);
		//if(!isset(HookHandler::$RegisteredHooks[$hookName])) HookHandler::$RegisteredHooks[$hookName] = array();
		if (!isset(HookHandler::$RegisteredHooks[$hookName])) {

			// This hook registration may have happened before the hook is 
			// actually registered... throw this into a stack for later.
			if (!isset(self::$EarlyRegisteredHooks[$hookName])) self::$EarlyRegisteredHooks[$hookName] = array();
			self::$EarlyRegisteredHooks[$hookName][] = array('call' => $callFunction);

			return;
		}
		HookHandler::$RegisteredHooks[$hookName]->attach($callFunction);
	}

	/**
	 * Register a hook object with the global HookHandler object.
	 *
	 * Allows for abstract calling of the hook.
	 *
	 * @param Hook $hook
	 *
	 * @return void
	 */
	public static function RegisterHook(Hook $hook) {
		$name = $hook->getName();

		Core\Utilities\Logger\write_debug('Registering new hook [' . $name . ']');

		if(isset(HookHandler::$RegisteredHooks[$name])){
			trigger_error('Registering hook that is already registered [' . $name . ']', E_USER_NOTICE);
		}

		HookHandler::$RegisteredHooks[$name] = $hook;
		//var_dump(self::$EarlyRegisteredHooks);
		// Attach any bindings that may have existed.
		if (isset(self::$EarlyRegisteredHooks[$name])) {
			foreach (self::$EarlyRegisteredHooks[$name] as $b) {
				$hook->attach($b['call']);
			}

			unset(self::$EarlyRegisteredHooks[$name]);
		}
	}

	public static function RegisterNewHook($hookName, $description = null) {
		$hook = new Hook($hookName);
		if($description) $hook->description = $description;
		HookHandler::RegisterHook($hook);
	}

	/**
	 * Dispatch an event, optionally passing 1 or more parameters.
	 *
	 * @param string $hookName
	 * @param mixed  $_
	 *
	 * @return boolean
	 */
	public static function DispatchHook($hookName, $args = null) {
		$hookName = strtolower($hookName); // Case insensitive will prevent errors later on.
		Core\Utilities\Logger\write_debug('Dispatching hook ' . $hookName);
		//echo "Calling hook $hookName<br>";
		//var_dump(HookHandler::$RegisteredHooks[$hookName]);
		if (!isset(HookHandler::$RegisteredHooks[$hookName])) {
			trigger_error('Tried to dispatch an undefined hook ' . $hookName, E_USER_NOTICE);
			return;
		}

		$args = func_get_args();
		// Drop off the hook name from the arguments.
		array_shift($args);

		$hook   = HookHandler::$RegisteredHooks[$hookName];
		$result = call_user_func_array(array(&$hook, 'dispatch'), $args);

		Core\Utilities\Logger\write_debug('Dispatched hook ' . $hookName);
		return $result;
	}

	/**
	 * Simple function to return all hooks currently registered on the system.
	 *
	 * @return array
	 */
	public static function GetAllHooks() {
		return self::$RegisteredHooks;
	}

	/**
	 * Just a simple debugging function to print out a list of the currently
	 * registered hooks on the system.
	 */
	public static function PrintHooks() {
		echo '<dl class="xdebug-var-dump">';
		foreach (self::$RegisteredHooks as $h) {
			echo '<dt>' . $h->name . '</dt>';
			if ($h->description) echo '<dd>' . $h->description . '</dd>';
			echo "<br/>\n";
		}
		echo '</dl>';
	}
}


/**
 * The actual hook object that will have the events attached to it.
 * Also allows for extra information
 *
 */
class Hook {

	/**
	 * Only a false return will trigger a failed hook deployment.
	 */
	const RETURN_TYPE_BOOL = 'bool';

	/**
	 * No return types are given any concern, and no feedback is available.
	 */
	const RETURN_TYPE_VOID = 'void';

	/**
	 * The return statuses from hooks are expected to be an array,
	 *
	 * Multiple calls are merged together in the overall result.
	 */
	const RETURN_TYPE_ARRAY = 'array';

	/**
	 * The name of this hook.  MUST be system unique.
	 * @var string
	 */
	public $name;

	/*
	 * Description of this hook, provides some human-friendly information about what it does and how it's used.
	 * @var string
	 */
	public $description;

	/**
	 * The return type of this hook, MUST be one of the RETURN_TYPE_* strings.
	 * @var string
	 */
	public $returnType = self::RETURN_TYPE_BOOL;

	/**
	 * An array of bound function/methods to call when this event is dispatched.
	 * @var array <<array>>
	 */
	private $_bindings = array();

	/**
	 * Instantiate a new generic hook object and register it with the global HookHandler.
	 *
	 * @param string $name
	 */
	public function __construct($name) {
		$this->name = $name;
		HookHandler::RegisterHook($this);
	}

	public function attach($function) {
		//echo "Binding event " . $function . " to " . $this->getName() . "<br/>";
		$this->_bindings[] = array('call' => $function);
	}

	/**
	 * Dispatch the event, calling any bound functions.
	 *
	 * @param mixed $_
	 *
	 * @return bool
	 */
	public function dispatch($args = null) {
		//echo "Dispatching event " . $this->getName() . "<br/>";
		//$args = func_get_args();
		//array_shift($args); // Drop the hookName off of the arguments.
		//echo '<pre>'; var_dump($args); echo '</pre>';

		switch($this->returnType){
			case self::RETURN_TYPE_BOOL:
				// Default status for void, either no calls made or all returned successfully.
				$return = true;
				break;
			case self::RETURN_TYPE_ARRAY:
				$return = array();
				break;
			case self::RETURN_TYPE_VOID:
				$return = null;
		}

		foreach ($this->_bindings as $call) {
			// If the type is set to something and the call type is that
			// OR
			// The call type is not set/null.
			$result = call_user_func_array($call['call'], func_get_args());

			switch($this->returnType){
				case self::RETURN_TYPE_BOOL:
					// This will allow a hook to prevent continuation of a script.
					if ($result === false){
						return false;
					}
					break;
				case self::RETURN_TYPE_ARRAY:
					if(is_array($result)){
						$return = array_merge($return, $result);
					}
					break;
				case self::RETURN_TYPE_VOID:
					// I DON'T CARE!  :p
					break;
			}

		}

		return $return;
	}

	public function __toString() {
		return $this->getName();
	}

	public function getName() {
		return strtolower($this->name);
	}

	public function getBindingCount(){
		return sizeof($this->_bindings);
	}
}


HookHandler::singleton();


// Create some pre-system hooks that are used before the component system is functional.
//HookHandler::RegisterNewHook('/core/db/ready', 'Called immediately after the database is available, but before any component is registered.');;
