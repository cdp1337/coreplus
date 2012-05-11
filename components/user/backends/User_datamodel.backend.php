<?php

/**
 * Description of User_datamodel_backend
 *
 * @author powellc
 */
class User_datamodel_Backend extends User implements User_Backend{
	
	public function checkPassword($password) {
		$hasher = new PasswordHash(15);
		return $hasher->checkPassword($password, $this->_getModel()->get('password'));
	}
	
	/**
	 * Utilize the builtin datamodel systems to look for a facebook user 
	 * that matches the requested clause.
	 * 
	 * @param type $where
	 * @param type $limit
	 * @param type $order
	 * 
	 * @return User_datamodel_Backend 
	 */
	public static function Find($where = array()){
		// Tack on the facebook backend requirement.
		$where['backend'] = 'datamodel';
		
		$res = new self();
		$res->_find($where);
		
		return $res;
	}
	
	
	public static function Register($email, $password, $attributes = array()){
		$ub = new self();
		
		$ub->set('password', $password);
		$ub->set('email', $email);
		//$ub->generateNewApiKey();
		
		// Save the extended attributes or 'UserConfig' options too!
		foreach($attributes as $k => $v){
			$ub->set($k, $v);
		}
		
		// whee!
		$ub->save();
		
		return $ub;
	}
}
