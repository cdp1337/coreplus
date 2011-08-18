<?php
/**
 * // enter a good description here
 * 
 * @package Core
 * @since 2011.06
 * @author Charlie Powell <powellc@powelltechs.com>
 * @copyright Copyright 2011, Charlie Powell
 * @license GNU Lesser General Public License v3 <http://www.gnu.org/licenses/lgpl-3.0.html>
 * This system is licensed under the GNU LGPL, feel free to incorporate it into
 * custom applications, but keep all references of the original authors intact,
 * read the full license terms at <http://www.gnu.org/licenses/lgpl-3.0.html>, 
 * and please contribute back to the community :)
 */

class HookHandler implements ISingleton{
	
	private static $RegisteredHooks = array();
	
	private static $Instance = null;
	
	private function __construct(){
		
	}
	
	public static function Singleton(){
		if(is_null(self::$Instance)) self::$Instance = new self();
		return self::$Instance;
	}
	
	public static function GetInstance(){ return self::singleton(); }
	
	/**
	 * Attach a call onto an existing hook.
	 * @param string $hookName The name of the hook to bind to.
	 * @param string|array $callFunction The function to call.
	 * @param string $type An option type string for the hook, various by event.
	 *                     If not null, only events with that type will call the function.
	 *                     This is useful for when you have a single event that 
	 *                     can contain different levels, such as errors.
	 * @return void
	 */
	public static function AttachToHook($hookName, $callFunction, $type = null){
		$hookName = strtolower($hookName); // Case insensitive will prevent errors later on.
		Debug::Write('Registering function ' . $callFunction . ' to hook ' . $hookName);
		//if(!isset(HookHandler::$RegisteredHooks[$hookName])) HookHandler::$RegisteredHooks[$hookName] = array();
		if(!isset(HookHandler::$RegisteredHooks[$hookName])){
			trigger_error('Tried to attach a function to undefined hook ' . $hookName, E_USER_NOTICE);
			return false;
		}
		HookHandler::$RegisteredHooks[$hookName]->attach($callFunction, $type); 
	}
	
	/**
	 * Register a hook object with the global HookHandler object.
	 * 
	 * Allows for abstract calling of the hook.
	 * 
	 * @param Hook $hook
	 * @return void
	 */
	public static function RegisterHook(Hook $hook){
		HookHandler::$RegisteredHooks[$hook->getName()] = $hook;
	}
	
	public static function RegisterNewHook($hookName){
		$hook = new Hook($hookName);
		HookHandler::RegisterHook($hook);
	}
	
	/**
	 * Dispatch an event, optionally passing 1 or more parameters.
	 * 
	 * @param string $hookName
	 * @param mixed $_
	 * @return boolean
	 */
	public static function DispatchHook($hookName, $args = null){
		$hookName = strtolower($hookName); // Case insensitive will prevent errors later on.
		Debug::Write('Dispatching hook ' . $hookName);
		Core::AddProfileTime('Calling hook ' . $hookName);
		//echo "Calling hook $hookName<br>";
		//var_dump(HookHandler::$RegisteredHooks[$hookName]);
		if(!isset(HookHandler::$RegisteredHooks[$hookName])){
			trigger_error('Tried to dispatch an undefined hook ' . $hookName, E_USER_NOTICE);
			return;
		}
		
		$args = func_get_args();
		// Drop off the hook name from the arguments.
		array_shift($args);
		
		$hook = HookHandler::$RegisteredHooks[$hookName];
		$result = call_user_func_array(array(&$hook, 'dispatch'), $args);
		
		Core::AddProfileTime('Called hook ' . $hookName);
		return $result;
	}

	/**
	 * Simple function to return all hooks currently registered on the system.
	 * 
	 * @return array
	 */
	public static function GetAllHooks(){
		return self::$RegisteredHooks;
	}
	
	/**
	 * Just a simple debugging function to print out a list of the currently
	 * registered hooks on the system. 
	 */
	public static function PrintHooks(){
		echo '<dl class="xdebug-var-dump">';
		foreach(self::$RegisteredHooks as $h){
			echo '<dt>' . $h->name . '</dt>';
			if($h->description) echo '<dd>' . $h->description . '</dd>';
			echo "<br/>\n";
		}
		echo '</dl>';
	}
}


/**
 * The actual hook object that will have the events attached to it.
 * Also allows for extra information
 * 
 * @author powellc
 *
 */
class Hook{
	
	/**
	 * The name of this hook.  MUST be system unique.
	 * @var string
	 */
	public $name;
	
	public $description;
	
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
	public function __construct($name){
		$this->name = $name;
		HookHandler::RegisterHook($this);
	}
	
	public function attach($function, $type = null){
		//echo "Binding event " . $function . " to " . $this->getName() . "<br/>";
		$this->_bindings[] = array('call' => $function, 'type' => $type);
	}
	
	/**
	 * Dispatch the event, calling any bound functions.
	 * @param mixed $_
	 * @return void
	 */
	public function dispatch($args = null){
		//echo "Dispatching event " . $this->getName() . "<br/>";
		//$args = func_get_args();
		//array_shift($args); // Drop the hookName off of the arguments.
		//echo '<pre>'; var_dump($args); echo '</pre>';
		// @todo This is legacy and will almost definitely be dropped.
		if($args && is_array($args) && isset($args['type'])) $type = $args['type'];
		else $type = false;
		
		foreach($this->_bindings as $call){
			// If the type is set to something and the call type is that
			// OR
			// The call type is not set/null.
			if((!$call['type']) || ($type && $call['type'] == $type)){
				$result = call_user_func_array($call['call'], func_get_args());
				// This will allow a hook to prevent continuation of a script.
				if($result === false) return false;
			}
		}
		
		// Either no calls made, or all returned successfully.
		return true;
	}
	
	public function __toString(){ return $this->getName(); }
	
	public function getName(){ return strtolower($this->name); }
}




HookHandler::singleton();


// Create some system-global hooks that will be used throughout.
HookHandler::RegisterNewHook('db_ready');
HookHandler::RegisterNewHook('libraries_loaded');
HookHandler::RegisterNewHook('libraries_ready');
HookHandler::RegisterNewHook('components_loaded');
HookHandler::RegisterNewHook('components_ready');
HookHandler::RegisterNewHook('session_ready');
HookHandler::RegisterNewHook('install_task');