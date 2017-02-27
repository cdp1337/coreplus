<?php
/**
 * File for FacebookWidget
 *
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130222.1011
 * @package Facebook
 */

/**
 * Provides the Facebook login widget.
 *
 * @package Facebook
 */
class FacebookWidget extends \Core\Widget {
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
