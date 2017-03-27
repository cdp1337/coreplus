<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PHPMailerController
 *
 * @author charlie
 */
class PHPMailerController extends Controller_2_1 {
	public function config(){
		if(!\Core\user()->checkAccess('g:admin')){
			return View::ERROR_ACCESSDENIED;
		}
		
		$view = $this->getView();
		$request = $this->getPageRequest();
		
		$keys = [
			'/core/email/enable_sending',
			'/core/email/from',
			'/core/email/from_name',
			'/core/email/sandbox_to',
			'/core/email/mailer',
			'/phpmailer/sendmail/path',
			'/phpmailer/smtp/auth',
			'/phpmailer/smtp/host',
			'/phpmailer/smtp/domain',
			'/phpmailer/smtp/user',
			'/phpmailer/smtp/password',
			'/phpmailer/smtp/port',
			'/phpmailer/smtp/security',
		];

		$form = new \Core\Forms\Form();
		$form->set('callsmethod', 'AdminController::_ConfigSubmit');

		foreach($keys as $k){
			$c = ConfigHandler::GetConfig($k);
			$f = $c->asFormElement();
			// Don't need them grouped
			$f->set('group', '');
			$form->addElement($f);
		}
		$form->addElement('submit', ['value' => t('STRING_SAVE')]);

		$view->title = 't:STRING_PHPMAILER_CONFIG';
		$view->assign('form', $form);
	}
}
