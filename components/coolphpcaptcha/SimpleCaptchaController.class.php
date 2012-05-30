<?php
/**
 * DESCRIPTION
 *
 * @package CoolPHPCaptcha
 * @since 0.1
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2012  Charlie Powell
 * @license GPLv3
 */

class SimpleCaptchaController extends Controller{
	public static function Index(View $page){
		// This will tell the system not to actually output anything.
		$page->mode = View::MODE_NOOUTPUT;
		
		$captcha = new SimpleCaptcha();
		
		// I like PNG's
		$captcha->imageFormat = 'png';
		
		// And make'em a bit blurry.
		$captcha->blur = true;
		
		// Ensure it knows where to look for the "resources"...
		$captcha->resourcesPath = ROOT_PDIR . "components/CoolPHPCaptcha/libs/cool-php-captcha/resources";
		
		$captcha->CreateImage();
	}
}

?>
