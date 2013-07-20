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
				'processing_time' => (round(Core\Utilities\Profiler\Profiler::GetDefaultProfiler()->getTime(), 4) * 1000)
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
			// Log this as a login attempt!
			$logmsg = 'Email not registered' . "\n" . 'Email: ' . $e->get('value') . "\n";
			SecurityLogModel::Log('/user/login', 'fail', null, $logmsg);
			$e->setError('Requested email is not registered.');
			return false;
		}

		// A few exceptions for backends.
		if($u instanceof User_facebook_Backend){
			// This isn't a log-worthy event (at least yet)
			$e->setError('That is a Facebook account, please use the Facebook connect button to login.');
			return false;
		}

		// This is a special case if the password isn't set yet.
		// It can happen with imported users or if a password is invalidated.
		if($u->get('password') == '' && $u->canResetPassword() === true){
			// Use the Nonce system to generate a one-time key with this user's data.
			$nonce = NonceModel::Generate(
				'20 minutes',
				['type' => 'password-reset', 'user' => $u->get('id')]
			);

			$link = '/user/forgotpassword?e=' . urlencode($u->get('email')) . '&n=' . $nonce;

			$e = new Email();
			$e->setSubject('Initial Password Request');
			$e->to($u->get('email'));
			$e->assign('link', Core::ResolveLink($link));
			$e->assign('ip', REMOTE_IP);
			$e->templatename = 'emails/user/initialpassword.tpl';
			try{
				$e->send();
				SecurityLogModel::Log('/user/initialpassword/send', 'success', $u->get('id'), 'Initial password request sent successfully');

				Core::SetMessage('You must set a new password.  An email has been sent to your inbox containing a link and instructions on setting a new password.', 'info');
				return true;
			}
			catch(Exception $e){
				error_log($e->getMessage());
				Core::SetMessage('Unable to send new password link to your email, please contact the system administrator!', 'error');
				return false;
			}
		}


		if(!$u->checkPassword($p->get('value'))){

			// Log this as a login attempt!
			$logmsg = 'Invalid password' . "\n" . 'Email: ' . $e->get('value') . "\n";
			SecurityLogModel::Log('/user/login', 'fail', $u->get('id'), $logmsg);

			// Also, I want to look up and see how many login attempts there have been in the past couple minutes.
			// If there are too many, I need to start slowing the attempts.
			$time = new CoreDateTime();
			$time->modify('-5 minutes');

			$securityfactory = new ModelFactory('SecurityLogModel');
			$securityfactory->where('action = /user/login');
			$securityfactory->where('status = fail');
			$securityfactory->where('datetime > ' . $time->getFormatted(Time::FORMAT_EPOCH, Time::TIMEZONE_GMT));
			$securityfactory->where('ip_addr = ' . REMOTE_IP);

			$attempts = $securityfactory->count();
			if($attempts > 4){
				// Start slowing down the response.  This should help deter brute force attempts.
				// (x+((x-7)/4)^3)-4
				sleep( ($attempts+(($attempts-7)/4)^3)-4 );
				// This makes a nice little curve with the following delays:
				// 5th  attempt: 0.85
				// 6th  attempt: 2.05
				// 7th  attempt: 3.02
				// 8th  attempt: 4.05
				// 9th  attempt: 5.15
				// 10th attempt: 6.52
				// 11th attempt: 8.10
				// 12th attempt: 10.05
			}

			$p->setError('Invalid password');
			$p->set('value', '');
			return false;
		}

		// If the user came from the registration page, get the page before that.
		if(REL_REQUEST_PATH == '/user/login') $url = Core::GetHistory(2);
		// else the registration link is now on the same page as the 403 handler.
		else $url = REL_REQUEST_PATH;

		// Well, record this too!
		SecurityLogModel::Log('/user/login', 'success', $u->get('id'));

		// yay...
		Session::SetUser($u);

		// Allow an external script to override the redirecting URL.
		$overrideurl = HookHandler::DispatchHook('/user/postlogin/getredirecturl');
		if($overrideurl){
			$url = $overrideurl;
		}

		return $url;
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

			// Users can be created with no password.  They will be prompted to set it on first login.
			if($p1->get('value') || $p2->get('value')){
				$user->setPassword($p1->get('value'), $p2->get('value'));
			}
		}
		catch(ModelValidationException $e){
			// Make a note of this!
			SecurityLogModel::Log('/user/register', 'fail', null, $e->getMessage());

			Core::SetMessage($e->getMessage(), 'error');
			return false;
		}
		catch(Exception $e){
			// Make a note of this!
			SecurityLogModel::Log('/user/register', 'fail', null, $e->getMessage());

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

		// User created... make a log of this!
		SecurityLogModel::Log('/user/register', 'success', $user->get('id'));

		// Send a thank you for registering email to the user.
		try{
			$email = new Email();
			$email->assign('user', $user);
			$email->assign('sitename', SITENAME);
			$email->assign('rooturl', ROOT_URL);
			$email->assign('loginurl', Core::ResolveLink('/user/login'));
			$email->setSubject('Welcome to ' . SITENAME);
			$email->templatename = 'emails/user/registration.tpl';
			$email->to($user->get('email'));

			// TESTING
			//error_log($email->renderBody());
			$email->send();
		}
		catch(\Exception $e){
			error_log($e->getMessage());
			Core::SetMessage('Unable to send welcome email', 'error');
		}



		// "login" this user if not already logged in.
		if(!\Core\user()->exists()){

			// If the user came from the registration page, get the page before that.
			if(REL_REQUEST_PATH == '/user/register') $url = Core::GetHistory(1);
			// else the registration link is now on the same page as the 403 handler.
			else $url = REL_REQUEST_PATH;

			//$url = Core::GetHistory(2);
			Session::SetUser($user);
			//var_dump($url); echo '<pre>'; debug_print_backtrace();
			Core::SetMessage('Registered account successfully!', 'success');

			// Allow an external script to override the redirecting URL.
			$overrideurl = HookHandler::DispatchHook('/user/postlogin/getredirecturl');
			if($overrideurl){
				$url = $overrideurl;
			}

			return $url;
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

		$userisactive = $user->get('active');

		try{
			$user->setFromForm($form);
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

		$user->save();


		if(!$userisactive && $user->get('active')){
			// If the user wasn't active before, but is now....
			// Send an activation notice email to the user.
			try{
				$email = new Email();
				$email->assign('user', $user);
				$email->assign('sitename', SITENAME);
				$email->assign('rooturl', ROOT_URL);
				$email->assign('loginurl', Core::ResolveLink('/user/login'));
				$email->setSubject('Welcome to ' . SITENAME);
				$email->templatename = 'emails/user/activation.tpl';
				$email->to($user->get('email'));

				// TESTING
				//error_log($email->renderBody());
				$email->send();
			}
			catch(\Exception $e){
				error_log($e->getMessage());
			}
		}


		// If this was the current user, update the session data too!
		if($user->get('id') == \core\user()->get('id')){
			$_SESSION['user'] = $user;

			if(ConfigHandler::Get('/user/profileedits/requireapproval') && Core::IsComponentAvailable('model-audit')){
				Core::SetMessage('Updated your account successfully, but an administrator will need to approve all changes.', 'success');
			}
			else{
				Core::SetMessage('Updated your account successfully', 'success');
			}
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

		if(is_numeric($user)){
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

		return $a;
	}

	/**
	 * Method to purge the user activity cron.
	 *
	 * This is useful because on an extremely busy site, this table can grow to several gigs within not much time.
	 */
	public static function PurgeUserActivityCron() {
		$opt = \ConfigHandler::Get('/user/activity/keephistory');

		if($opt == 'all' || !$opt){
			echo 'Not purging any user activity.' . "\n";
			return true;
		}

		// Convert the key to a datestring value.
		$date = new CoreDateTime();
		switch($opt){
			case '1-week':
				$date->modify('-1 week');
				break;
			case '1-month':
				$date->modify('-1 month');
				break;
			case '2-months':
				$date->modify('-2 month');
				break;
			case '3-months':
				$date->modify('-3 month');
				break;
			case '6-months':
				$date->modify('-6 month');
				break;
			case '12-months':
				$date->modify('-12 month');
				break;
			case '24-months':
				$date->modify('-24 month');
				break;
			case '36-months':
				$date->modify('-36 month');
				break;
			default:
				echo 'Invalid value for /user/activity/keephistory: [' . $opt . ']';
				return false;
		}

		// And delete any activity older than this date.
		echo 'Purging user activity older than ' . $date->getFormatted('r') . "...\n";
		$ds = new Dataset();
		$ds->delete()->table('user_activity')->where('datetime < ' . $date->getFormatted('U', TIME::TIMEZONE_GMT))->execute();
		echo 'Removed ' . $ds->num_rows . ' record(s).' . "\n";
		return true;
	}
}