<?php
/**
 * Facebook-based user backend
 *
 * @package User
 * @since 1.9
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

/**
 * Facebook-based user backend
 * Provides the core functionality with facebook logins and registrations.
 */
class User_facebook_Backend extends User implements User_Backend{

	/**
	 * Cache of groups for this user.
	 *
	 * @var null|array
	 */
	private $_groups = null;
	
	public function canResetPassword() {
		return 'Please reset your password with facebook.';
	}
	
	/**
	 * Utilize the builtin datamodel systems to look for a facebook user 
	 * that matches the requested clause.
	 *
	 * @param array|string $where Where clause
	 * @param int          $limit Limit clause
	 * @param string|null  $order Order clause
	 * 
	 * @return User_facebook_Backend 
	 */
	public static function Find($where = array(), $limit = 1, $order = null){
		// Tack on the facebook backend requirement.
		$where['backend'] = 'facebook';
		
		$res = new self();
		$res->_find($where, $limit, $order);
		
		return $res;
	}
	
	/**
	 * Try to log the user in from facebook.
	 * 
	 * @param Facebook $facebook 
	 */
	public static function Login(Facebook $facebook){
		$user = $facebook->getUser();
		$user_profile = $facebook->api('/me');
		// User logged in or was already logged in.
		$m = self::Find(array('email' => $user_profile['email']));
		if(!$m->exists()){
			// User doesn't exist on the local system, create that user.
			// This is because facebook logins are auto-registration.
			$m->set('email', $user_profile['email']);
			$m->set('backend', 'facebook');
			//$m->generateNewApiKey();
			
			// Save it!
			$m->save();
		}

		// Sync the user avatar.
		$f = new File_remote_backend('http://graph.facebook.com/' . $user_profile['id'] . '/picture?type=large');

		$dest = Core::File('public/user/' . $f->getBaseFilename());
		$f->copyTo($dest);
		$m->set('avatar', $dest->getBaseFilename());
		
		// Get all user configs and load in anything possible.
		foreach($m->getConfigs() as $k => $v){
			// Facebook can import several configs...
			switch($k){
				case 'first_name':
				case 'last_name':
				case 'gender':
				case 'username':
					$m->set($k, $user_profile[$k]);
					break;
				case 'facebook_id':
					$m->set($k, $user_profile['id']);
					break;
				case 'facebook_link':
					$m->set($k, $user_profile['link']);
					break;
				case 'facebook_access_token':
					$m->set($k, $facebook->getAccessToken());
					break;
			}
		}
		
		// Save any configs that may have changed :)
		$m->save();
		
		// "login" this user.
		Session::SetUser($m);
		
		return true;
	}

	/**
	 * Get the group IDs this user is a member of.
	 *
	 * @return array
	 */
	public function getGroups(){
		if($this->_groups === null){
			// datamodel backed users have the groups listed in their column "groups".
			$g = json_decode($this->_getModel()->get('groups'), true);
			if(!$g) $g = array();

			$this->_groups = $g;
		}

		return $this->_groups;
	}

	public function setGroups($groups){
		// First, blank out the cache just as a precaution.
		$this->_groups = null;

		if(sizeof($groups) == 0){
			$this->_getModel()->set('groups', '');
		}
		else{
			$this->_getModel()->set('groups', json_encode($groups));
		}
	}

	public function delete(){
		//stuff
	}
}
