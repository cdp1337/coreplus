<?php
/**
 * Enter a meaningful file description here!
 *
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130222.1011
 * @package PackageName
 *
 * Created with JetBrains PhpStorm.
 */
/**
 * Class description here
 */
class FacebookWidget extends Widget_2_1{
	/**
	 * Display the login widget for facebook.  This is actually just a button and FB handles the rest :)
	 */
	public function login(){
		// If facebook isn't in the list of allowed user backends, just exit out.
		if(!in_array('facebook', ConfigHandler::Get('/user/backends'))){
			return '';
		}

		$view = $this->getView();

		$facebook = new Facebook([
			'appId'  => FACEBOOK_APP_ID,
			'secret' => FACEBOOK_APP_SECRET,
		]);

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

		$view->assign('facebooklink', $facebooklink);
	}
}
