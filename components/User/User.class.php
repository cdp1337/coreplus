<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


// I has some dependencies...
define('__USER_PDIR', dirname(__FILE__) . '/');
require_once(__USER_PDIR . 'User_Backend.interface.php');


/**
 * Description of User
 *
 * @author powellc
 */
class User {
	/**
	 * The backend for this user object.
	 * @var User_Backend
	 */
	protected $_backend;
	
	/**
	 * This points to the system/global User object.
	 * 
	 * @var User
	 */
	static protected $_Interface = null;
	
	public function __construct($backend = null){
		// Provide shortcut to set the backend directly in the constructor.
		if($backend) $this->setBackend($backend);
		
	}
	
	public function setBackend($backend){
		if($this->_backend) throw new User_Exception('Backend already set');
		
		$class = 'User_' . $backend . '_backend';
		$classfile = strtolower($backend);
		if(!file_exists(__USER_PDIR . 'backends/' . strtolower($classfile) . '.backend.php')){
			throw new User_Exception('Could not locate backend file for ' . $class);
		}
		
		require_once(__USER_PDIR . 'backends/' . strtolower($classfile) . '.backend.php');
		
		$this->_backend = new $class();
	}
	
	/**
	 *
	 * @return User_Backend
	 */
	public static function GetSystemBackend(){
		if(self::$_Interface !== null) return self::$_Interface->_backend;
		
		self::$_Interface = new User();
		
		self::$_Interface->setBackend(ConfigHandler::GetValue('/user/backend'));
		
		return self::$_Interface->_backend;
	}
}

class User_Exception extends Exception{
	
}