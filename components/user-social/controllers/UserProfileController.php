<?php
/**
 * Class file for the controller UserProfileController
 *
 * @package User-Social
 * @author Charlie Powell <charlie@eval.bz
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
		$manager = \Core\user()->checkAccess('p:user_manage'); // Current user an admin?

		// First argument here will either be the username or user id.
		$arg1 = $request->getParameter(0);
		if(is_numeric($arg1)){
			$user = User::Find(array('id' => $arg1), 1);
		}
		else{
			$match = UserUserConfigModel::Find(array('key' => 'username', 'value' => $arg1), 1);
			if(!$match) return View::ERROR_NOTFOUND;
			$user = User::Find(array('id' => $match->get('user_id')), 1);
		}

		if(!$user) return View::ERROR_NOTFOUND;

		// If the UA requested the user by ID but the user has a username set, return a 404 as well.
		// This should help cut down on scanning attempts for userdata.
		if(is_numeric($arg1) && $user->get('username')) return View::ERROR_NOTFOUND;

		// Now see why username needs to not begin with a number? :p
		/** @var $user User */

		// Only allow this if the user is either the same user or has the user manage permission.
		if($user->get('id') == \Core\user()->get('id') || $manager){
			$editor = true;
		}
		else{
			$editor = false;
		}

		if($editor){
			$view->addControl(
				array(
					'title' => 'Connected Profiles',
					'link' => '/userprofile/connectedprofiles/' . $user->get('id'),
					'icon' => 'link',
				)
			);
		}

		$view->title = $user->getDisplayName();
		$view->assign('user', $user);
		$view->assign('profiles', json_decode($user->get('json:profiles'), true));
	}

	/**
	 * Function to edit the user's connected profiles.
	 */
	public function connectedprofiles(){
		$view    = $this->getView();
		$req     = $this->getPageRequest();
		$userid  = $req->getParameter(0);
		$manager = \Core\user()->checkAccess('p:user_manage'); // Current user an admin?

		if($userid === null) $userid = \Core\user()->get('id'); // Default to current user.

		// Only allow this if the user is either the same user or has the user manage permission.
		if(!($userid == \Core\user()->get('id') || $manager)){
			return View::ERROR_ACCESSDENIED;
		}

		$user = User::Find(array('id' => $userid));
		// I will be dealing with only one custom field...
		$jsonprofiles = $user->get('json:profiles');
		$profiles = json_decode($jsonprofiles, true);

		if($req->isPost()){
			// Update the new profiles.... yay
			$error = false;
			$profiles = array();
			foreach($_POST['type'] as $k => $v){
				// Check that this looks like a URL.
				if(!preg_match(Model::VALIDATION_URL_WEB, $_POST['url'][$k])){
					$error = true;
					Core::SetMessage($_POST['url'][$k] . ' does not appear to be a valid URL!  Please ensure that it starts with http:// or https://', 'error');
				}

				$profiles[] = array(
					'type' => $_POST['type'][$k],
					'url' => $_POST['url'][$k],
					'title' => $_POST['title'][$k],
				);
			}

			if(!$error){
				$user->set('json:profiles', json_encode($profiles));
				$user->save();
				Core::SetMessage('Updated profiles successfully', 'success');
				Core::GoBack();
			}
			else{
				$jsonprofiles = json_encode($profiles);
			}
		}

		$view->addBreadcrumb($user->getDisplayName(), UserSocialHelper::ResolveProfileLinkById($user->get('id')));
		$view->title = 'Edit Connected Profiles';
		$view->assign('profiles_json', $jsonprofiles);
		$view->assign('profiles', $profiles);
	}
}