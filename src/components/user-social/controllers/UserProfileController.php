<?php
/**
 * Class file for the controller UserProfileController
 *
 * @package User-Social
 * @author Charlie Powell <charlie@evalagency.com>
 */
class UserProfileController extends Controller_2_1 {
	// Each controller can have many views, each defined by a different method.
	// These methods should be regular public functions that DO NOT begin with an underscore (_).
	// Any method that begins with an underscore or is static will be assumed as an internal method
	// and cannot be called externally via a url.

	/**
	 * View a user's public profile
	 */
	public function view(){
		$view    = $this->getView();
		$request = $this->getPageRequest();
		$manager = \Core\user()->checkAccess('p:/user/users/manage'); // Current user an admin?

		// First argument here will either be the username or user id.
		$arg1 = $request->getParameter(0);

		$user = UserModel::Construct($arg1);

		if(!($user && $user->exists())){
			// Try by username instead.
			$match = UserUserConfigModel::Find(array('key' => 'username', 'value' => $arg1), 1);
			if(!$match) return View::ERROR_NOTFOUND;
			$user = UserModel::Construct($match->get('user_id'));
		}

		if(!$user) return View::ERROR_NOTFOUND;

		// If the UA requested the user by ID but the user has a username set, return a 404 as well.
		// This should help cut down on scanning attempts for userdata.
		if(is_numeric($arg1) && $user->get('username')) return View::ERROR_NOTFOUND;

		// Now see why username needs to not begin with a number? :p
		/** @var $user UserModel */

		// Only allow this if the user is either the same user or has the user manage permission.
		if($user->get('id') == \Core\user()->get('id') || $manager){
			$editor = true;
		}
		else{
			$editor = false;
		}

		$view->controls = ViewControls::DispatchModel($user);

		$view->title = $user->getDisplayName();
		$view->assign('user', $user);
		$view->assign('profiles', $user->get('external_profiles'));
	}
}