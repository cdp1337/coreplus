<?php
/**
 * The controller that handles generating the captcha image
 *
 * @package CoolPHPCaptcha
 * @since 0.1
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2014  Charlie Powell
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

class SimpleCaptchaController extends Controller_2_1{
	public function index(){
		$view = $this->getView();
		$req  = $this->getPageRequest();
		// This will tell the system not to actually output anything.
		$view->record = false;
		$view->mode = View::MODE_NOOUTPUT;
		
		$captcha = new SimpleCaptcha();
		$captcha->createImage();
	}

	/**
	 * Administrative page for configuring the Captcha settings.
	 */
	public function admin() {
		$view = $this->getView();
		$request = $this->getPageRequest();

		// This is an admin-only page.
		if(!\Core\user()->checkAccess('g:admin')){
			return View::ERROR_ACCESSDENIED;
		}

		// width, height
		// colors

		$presets = [
			'simple' => [
				'name' => 'I trust and like my visitors',
				'alt' => 'Simple and easy',
				'configs' => [
					'/captcha/minlength' => 4,
					'/captcha/maxlength' => 5,
					'/captcha/linethrough' => 0,
					'/captcha/yperiod' => 12,
					'/captcha/yamplitude' => 14,
					'/captcha/xperiod' => 11,
					'/captcha/xamplitude' => 2,
					'/captcha/maxrotation' => 4,
					'/captcha/blur' => false,
				]
			],

			'med' => [
				'name' => 'Meh...',
				'alt' => 'Moderate level of difficulty',
				'configs' => [
					'/captcha/minlength' => 5,
					'/captcha/maxlength' => 7,
					'/captcha/linethrough' => 1,
					'/captcha/yperiod' => 12,
					'/captcha/yamplitude' => 14,
					'/captcha/xperiod' => 11,
					'/captcha/xamplitude' => 2,
					'/captcha/maxrotation' => 8,
					'/captcha/blur' => true,
				]
			],

			'hard' => [
				'name' => 'All visitors deserve to be punished!',
				'alt' => 'Hieroglyphics are easier',
				'configs' => [
					'/captcha/minlength' => 6,
					'/captcha/maxlength' => 9,
					'/captcha/linethrough' => 4,
					'/captcha/yperiod' => 12,
					'/captcha/yamplitude' => 20,
					'/captcha/xperiod' => 11,
					'/captcha/xamplitude' => 6,
					'/captcha/maxrotation' => 12,
					'/captcha/blur' => true,
				]
			],
		];

		// See if there's a preset option selected.

		$current = null;
		foreach($presets as $key => $preset){
			// This will skim through each preset and if all the options are set to this preset, then it must be the current.

			foreach($preset['configs'] as $k => $v){
				if(ConfigHandler::Get($k) != $v){
					continue 2;
				}
			}

			// Did it not continue?  Must be the current preset.
			//$current = $preset;
			$current = $key;
			break;
		}




		// This page uses a traditional form post.
		if($request->isPost()){
			// See if there's a preset
			$postpreset = $request->getPost('preset');
			if($postpreset && $postpreset != $current && isset($presets[$postpreset])){
				foreach($presets[$postpreset]['configs'] as $k => $v){
					ConfigHandler::Set($k, $v);
				}

				Core::SetMessage('Switched to ' . $presets[$postpreset]['name'] . ' preset.', 'success');
			}

			// And the blah post options.
			$postwidth  = $request->getPost('width');
			$postheight = $request->getPost('height');
			$posttext   = $request->getPost('formtext');

			if($postwidth <= 0) $postwidth = 100;
			if($postwidth > 400) $postwidth = 400;

			if($postheight <= 0) $postheight = 100;
			if($postheight > 200) $postheight = 200;

			if(!$posttext) $posttext = 'Are you a Human?';


			ConfigHandler::Set('/captcha/width', $postwidth);
			ConfigHandler::Set('/captcha/height', $postheight);
			ConfigHandler::Set('/captcha/formtext', $posttext);

			\Core\reload();
		}


		// Build the form.  This will be pretty simple :p
		$form = new Form();
		$presetoptions = array();
		if(!$current){
			// Add the custom settings option.
			$presetoptions[''] = '-- Custom Settings --';
		}

		foreach($presets as $key => $preset){
			$presetoptions[$key] = $preset['name'] . ' (' . $preset['alt'] . ')';
		}

		$form->addElement(
			'select',
			[
				'name' => 'preset',
				'title' => 'Difficulty Level',
				'options' => $presetoptions,
				'value' => ($current ? $current : ''),
			]
		);

		$form->addElement('text', ['name' => 'width', 'title' => 'Image Width', 'value' => ConfigHandler::Get('/captcha/width')]);
		$form->addElement('text', ['name' => 'height', 'title' => 'Image Height', 'value' => ConfigHandler::Get('/captcha/height')]);
		$form->addElement('text', ['name' => 'formtext', 'title' => 'Form Text', 'value' => ConfigHandler::Get('/captcha/formtext')]);
		// @todo Colors for foreground and background.

		$form->addElement('submit', ['name' => 'submit', 'value' => 'Save Settings']);

		$view->mastertemplate = 'admin';
		$view->title = 'Captcha Tweaks';
		$view->assign('form', $form);
	}
}

