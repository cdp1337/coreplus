<?php

abstract class UserHelper{

	/**
	 * Function to record activity, ie: a page view.
	 *
	 * @static
	 *
	 */
	public static function RecordActivity(){

		$request = PageRequest::GetSystemRequest();
		$view = $request->getView();

		if(!$view->record) return;

		$log = new UserActivityModel();
		$log->setFromArray(
			array(
				'session_id' => session_id(),
				'user_id' => \Core\user()->get('id'),
				'ip_addr' => REMOTE_IP,
				'useragent' => $_SERVER['HTTP_USER_AGENT'],
				'referrer' => (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''),
				'type' => $_SERVER['REQUEST_METHOD'],
				'request' => $_SERVER['REQUEST_URI'],
				'baseurl' => $request->getBaseURL(),
				'status' => $view->error,
				'db_reads' => Core::DB()->readCount(),
				'db_writes' => (Core::DB()->writeCount() + 1),
				'processing_time' => (round(Core::GetProfileTimeTotal(), 4) * 1000)
			)
		);
		try{
			$log->save();
		}
		catch(Exception $e){
			// I don't actually care if it couldn't save.
			// This could happen if the user refreshes the page twice with in a second.
			// (and with a system that responds in about 100ms, it's very possible).
		}
	}


	/**
	 * Form Handler for logging in.
	 *
	 * @static
	 *
	 * @param Form $form
	 *
	 * @return bool|null|string
	 */
	public static function LoginHandler(Form $form){
		$e = $form->getElement('email');
		$p = $form->getElement('pass');


		$u = User::Find(array('email' => $e->get('value')));
		if(!$u){
			$e->setError('Requested email is not registered.');
			return false;
		}

		// A few exceptions for backends.
		if($u instanceof User_facebook_Backend){
			$e->setError('That is a Facebook account, please use the Facebook connect button to login.');
			return false;
		}

		if(!$u->checkPassword($p->get('value'))){

			if(!isset($_SESSION['invalidpasswordattempts'])) $_SESSION['invalidpasswordattempts'] = 1;
			else $_SESSION['invalidpasswordattempts']++;

			if($_SESSION['invalidpasswordattempts'] > 4){
				// Start slowing down the response.  This should help deter brute force attempts.
				sleep( ($_SESSION['invalidpasswordattempts'] - 4) ^ 1.5 );
			}

			$p->setError('Invalid password');
			$p->set('value', '');
			return false;
		}

		// yay...
		Session::SetUser($u);

		// Where shall I return to?
		if(REL_REQUEST_PATH == '/user/login') return '/';
		else return REL_REQUEST_PATH;
	}

	/**
	 * Form handler for a basic datastore backend user.
	 *
	 * @param Form $form
	 *
	 * @return bool|string
	 */
	public static function RegisterHandler(Form $form){
		$p1 = $form->getElement('pass');
		$p2 = $form->getElement('pass2');

		///////       VALIDATION     \\\\\\\\

		// All other validation can be done from the model.
		// All set calls will throw a ModelValidationException if the validation fails.
		try{
			$user = new User_datamodel_Backend();

			// setFromForm will handle all attributes and custom values.
			$user->setFromForm($form);
			$user->setPassword($p1->get('value'), $p2->get('value'));
		}
		catch(ModelValidationException $e){
			Core::SetMessage($e->getMessage(), 'error');
			return false;
		}
		catch(Exception $e){
			if(DEVELOPMENT_MODE) Core::SetMessage($e->getMessage(), 'error');
			else Core::SetMessage('An unknown error occured', 'error');

			return false;
		}

		// Check if there are no users already registered on the system.  If
		// none, register this user as an admin automatically.
		if(UserModel::Count() == 0){
			$user->set('admin', true);
		}
		else{
			if(\ConfigHandler::Get('/user/register/requireapproval')){
				$user->set('active', false);
			}
		}

		$user->save();

		// "login" this user if not already logged in.
		if(!\Core\user()->exists()){
			Session::SetUser($user);
			return '/';
		}
		// It was created administratively; redirect there instead.
		else{
			Core::SetMessage('Created user successfully', 'success');
			return '/useradmin';
		}

	}

	public static function UpdateHandler(Form $form){

		$userid = $form->getElement('id')->get('value');

		// Only allow this if the user is either the same user or has the user manage permission.
		if(!($userid == \Core\user()->get('id') || \Core\user()->checkAccess('p:user_manage'))){
			Core::SetMessage('Insufficient Permissions', 'error');
			return false;
		}

		/** @var $user User */
		$user = User::Find(array('id' => $userid));

		if(!$user->exists()){
			Core::SetMessage('User not found', 'error');
			return false;
		}


		try{
			$user->setFromForm($form);
		}
		catch(ModelValidationException $e){
			Core::SetMessage($e->getMessage());
			return false;
		}
		catch(Exception $e){
			if(DEVELOPMENT_MODE) Core::SetMessage($e->getMessage(), 'error');
			else Core::SetMessage('An unknown error occured', 'error');

			return false;
		}

		$user->save();

		// If this was the current user, update the session data too!
		if($user->get('id') == \core\user()->get('id')){
			$_SESSION['user'] = $user;
			Core::SetMessage('Updated your account successfully', 'success');
		}
		else{
			Core::SetMessage('Updated user successfully', 'success');
		}


		return true;
	}

	/**
	 * Get the control links for a given user based on the current user's access permissions.
	 *
	 * @param UserBackend|int $user
	 * @return array
	 */
	public static function GetControlLinks($user){
		$a = array();

		if(is_int($user)){
			// Transpose the ID to a user backend object.
			$user = User::Construct($user);
		}
		elseif($user instanceof UserModel){
			// Transpose the model to a user backend object.
			$user = User::Construct($user->get('id'));
		}
		elseif(is_subclass_of($user, 'UserBackend')){
			// NO change needed :)
		}
		else{
			// Umm, wtf was it?
			return array();
		}

		// still nothing?
		if(!$user) return array();

		if(\Core\user()->checkAccess('p:user_manage')){
			$a[] = array(
				'title' => 'Edit',
				'icon' => 'edit',
				'link' => '/user/edit/' . $user->get('id'),
			);

			$a[] = array(
				'title' => 'Change Password',
				'icon' => 'key',
				'link' => '/user/password/' . $user->get('id'),
			);

			// Even though this user has admin access, he/she cannot remove his/her own account!
			if(\Core\user()->get('id') != $user->get('id')){
				$a[] = array(
					'title' => 'Delete',
					'icon' => 'remove',
					'link' => '/useradmin/delete/' . $user->get('id'),
					'confirm' => 'Really delete user ' . $user->getDisplayName(),
				);
			}
		}

		// @todo Implement a hook here.

		// Now that I have them all, I need to go through and make sure that they have the appropriate data at least.
		foreach($a as $k => $dat){
			if(!isset($dat['class'])) $a[$k]['class'] = '';
			if(!isset($dat['confirm'])) $a[$k]['confirm'] = false;
			if(!isset($dat['icon'])) $a[$k]['icon'] = false;
		}
		return $a;
	}
}