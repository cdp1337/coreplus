<?php
/**
 * Standard datamodel-backed user system
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

class User_datamodel_Backend extends User implements User_Backend{

	/**
	 * Cache of groups for this user.
	 *
	 * @var null|array
	 */
	private $_groups = null;

	public function __construct(){

	}

	public function checkPassword($password) {
		$hasher = new PasswordHash(User::HASH_ITERATIONS);
		return $hasher->checkPassword($password, $this->_getModel()->get('password'));
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

			// Failover, if the user's group is a flat array of integers and multisite mode is enabled,
			// convert that to an array of sites, 0 being the first (root), with that list of groups.
			// This is useful for a site that has existing users that then converts to multisite.
			if(Core::IsComponentAvailable('enterprise') && MultiSiteHelper::IsEnabled()){
				if(isset($g[0]) && !is_array($g[0])){

					$g = array(0 => $g);
					$this->_getModel()->set('groups', json_encode($g));
					// It'll get saved on save() (if called).
				}
			}

			$this->_groups = $g;
		}

		// Only return this site's groups if in multisite mode
		if(Core::IsComponentAvailable('enterprise') && MultiSiteHelper::IsEnabled()){
			$site = MultiSiteHelper::GetCurrentSiteID();
			return (isset($this->_groups[$site])) ? $this->_groups[$site] : array();
		}
		else{
			return $this->_groups;
		}
	}

	/**
	 * Set all groups for a given user on the current site.
	 *
	 * @param array $groups
	 */
	public function setGroups($groups){
		// If multimode is enabled, this will have to be a list of groups JUST for that site.
		if(Core::IsComponentAvailable('enterprise') && MultiSiteHelper::IsEnabled()){
			// setSiteGroups will handle all the logic for mutlisite mode.
			$this->setSiteGroups(MultiSiteHelper::GetCurrentSiteID(), $groups);
		}
		else{
			$this->_groups = $groups;


			if(sizeof($this->_groups) == 0){
				$this->_getModel()->set('groups', '');
			}
			else{
				$this->_getModel()->set('groups', json_encode($this->_groups));
			}
		}

		// And clear the cache!
		$this->clearAccessStringCache();
	}

	/**
	 * Set the groups for a given user on a specified site.
	 *
	 * @param int   $site
	 * @param array $groups
	 */
	public function setSiteGroups($site, $groups){
		// If multimode is enabled, this will have to be a list of groups JUST for that site.
		if(Core::IsComponentAvailable('enterprise') && MultiSiteHelper::IsEnabled()){
			// In this case, I need to be sure that the groups are loaded correctly.  getGroups will take care of that.
			$this->getGroups();
			// This subset will be blanked out.
			$this->_groups[$site] = $groups;
		}
		else{
			$this->_groups = $groups;
		}

		if(sizeof($this->_groups) == 0){
			$this->_getModel()->set('groups', '');
		}
		else{
			$this->_getModel()->set('groups', json_encode($this->_groups));
		}

		// And clear the cache!
		$this->clearAccessStringCache();
	}



	public function delete(){
		$this->_getModel()->delete();
	}

	/**
	 * Utilize the builtin datamodel systems to look for a facebook user
	 * that matches the requested clause.
	 *
	 * @param array|string $where Where clause
	 * @param int          $limit Limit clause
	 * @param string|null  $order Order clause
	 *
	 * @return User_datamodel_Backend|array
	 */
	public static function Find($where = array(), $limit = 1, $order = null){
		// Tack on the facebook backend requirement.
		$where['backend'] = 'datamodel';

		$res = new self();
		$res->_find($where, $limit, $order);

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
