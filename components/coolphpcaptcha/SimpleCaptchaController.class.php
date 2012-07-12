<?php
/**
 * The controller that handles generating the captcha image
 *
 * @package CoolPHPCaptcha
 * @since 0.1
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2012  Charlie Powell
 * @license GPLv3
 */

class SimpleCaptchaController extends Controller_2_1{
	public function index(){
		$view = $this->getView();
		$req  = $this->getPageRequest();
		// This will tell the system not to actually output anything.
		$view->mode = View::MODE_NOOUTPUT;
		
		$captcha = new SimpleCaptcha();
		
		// I like PNG's
		$captcha->imageFormat = 'png';
		
		// And make'em a bit blurry.
		$captcha->blur = true;

		$captcha->minWordLength = 6;
		$captcha->maxWordLength = 9;
		$captcha->wordsFile = '';
		$captcha->scale = 1;
		$captcha->lineWidth = rand(1, 3);
		
		// Ensure it knows where to look for the "resources"...

		$captcha->resourcesPath = Core::GetComponent('coolphpcaptcha')->getBaseDir() . "libs/cool-php-captcha/resources";
		
		$captcha->CreateImage();
	}
}

