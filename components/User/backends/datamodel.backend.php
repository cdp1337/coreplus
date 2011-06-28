<?php

/**
 * Description of User_datamodel_backend
 *
 * @author powellc
 */
class User_datamodel_backend {
	protected $_model = null;
	
	
	public function __construct(){
		
	}
	
	/**
	 *
	 * @return UserModel
	 */
	protected function _getModel(){
		if($this->_model == null) $this->_model = new UserModel();
		
		return $this->_model;
	}
	
	
	public static function Login($email, $password){
		$userobj = self::Find(array('email' => $email));
		var_dump($userobj);
	}
	
	public static function Register($email, $password, $attributes = array()){
		$ub = new self();
		// hash the password.
		$hasher = new PasswordHash(15);
		$password = $hasher->hashPassword($password);
		
		$ub->_getModel()->set('email', $email);
		$ub->_getModel()->set('password', $password);
		$ub->_getModel()->set('apikey', Core::RandomHex(64, true));
		$ub->_getModel()->save();
		
		return $ub;
		
	}
	
	public static function Find($where){
		return UserModel::Find($where);
	}
}

?>
