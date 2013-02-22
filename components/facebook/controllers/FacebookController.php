<?php
/**
 * Enter a meaningful file description here!
 *
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130222.1028
 * @package PackageName
 *
 * Created with JetBrains PhpStorm.
 */
/**
 * Class description here
 */
class FacebookController extends Controller_2_1{
	/**
	 * View to accept and process the FB login post.
	 */
	public function login(){
		if(in_array('facebook', ConfigHandler::Get('/user/backends'))){
			$facebook = new Facebook(array(
				'appId'  => FACEBOOK_APP_ID,
				'secret' => FACEBOOK_APP_SECRET,
			));

			// Did the user submit the facebook login request?
			if(
				$_SERVER['REQUEST_METHOD'] == 'POST' &&
				isset($_POST['login-method']) &&
				$_POST['login-method'] == 'facebook' &&
				$_POST['access-token']
			){
				try{
					$facebook->setAccessToken($_POST['access-token']);
					User_facebook_Backend::Login($facebook);


					// Redirect to the home page or the page originally requested.
					// The exception to this is if the user went straight to the user login page.
					if(REL_REQUEST_PATH == '/user/login'){
						$redirect = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '/user/me';
					}
					elseif(strpos($_SERVER['HTTP_REFERER'], '/user/login') !== false){
						$redirect = '/';
					}
					else{
						$redirect = null;
					}

					if($redirect){
						Core::Redirect($redirect);
					}
					else{
						Core::Reload();
					}
				}
				catch(Exception $e){
					$error = $e->getMessage();
				}
			}

			$user = $facebook->getUser();
			if($user){
				// User was already logged in.
				try{
					$user_profile = $facebook->api('/me');
					$facebooklink = false;
				}
				catch(Exception $c){
					$facebooklink = $facebook->getLoginUrl();
				}

				// $logoutUrl = $facebook->getLogoutUrl();
			}
			else{
				$facebooklink = $facebook->getLoginUrl();
			}
		}
		else{
			$facebooklink = false;
		}
	}
}
