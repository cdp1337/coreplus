<?php
/**
 * Email wrapper around the system mail utility, (phpMailer in this case).
 *
 * @package PHPMailer
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2017  Charlie Powell
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

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PHPMailerCore;
use \Core\EmailInterface;

/**
 * Description of Mail
 *
 * @author charlie
 */
class Mail extends EmailBackend implements EmailInterface {
	/**
	 * Construct a new email backend to send an email.
	 */
	public function __construct() {
		parent::__construct();
		
		$this->_mailer->Mailer = 'smtp';
		$this->_mailer->Host   = \ConfigHandler::Get('/phpmailer/smtp/host');
		$this->_mailer->Port   = \ConfigHandler::Get('/phpmailer/smtp/port');

		switch(\ConfigHandler::Get('/phpmailer/smtp/auth')){
			case 'LOGIN':
			case 'PLAIN':
				$this->_mailer->AuthType = \ConfigHandler::Get('/phpmailer/smtp/auth'); 
				$this->_mailer->Username = \ConfigHandler::Get('/phpmailer/smtp/user');
				$this->_mailer->Password = \ConfigHandler::Get('/phpmailer/smtp/password');
				$this->_mailer->SMTPAuth = true;
				break;
			case 'NTLM':
				$this->_mailer->AuthType = \ConfigHandler::Get('/phpmailer/smtp/auth');
				$this->_mailer->Username = \ConfigHandler::Get('/phpmailer/smtp/user');
				$this->_mailer->Password = \ConfigHandler::Get('/phpmailer/smtp/password');
				$this->_mailer->Realm    = \ConfigHandler::Get('/phpmailer/smtp/domain');
				$this->_mailer->SMTPAuth = true;
				break;
			case 'NONE':
				$this->_mailer->SMTPAuth = false;
				break;
		}

		$security = \ConfigHandler::Get('/phpmailer/smtp/security');
		$this->_mailer->SMTPSecure = $security == 'none' ? '' : $security;
	}
	
	/**
	 * Enable debug operations on this email send, if supported.
	 */
	public function enableDebug() {
		
	}
	
	/**
	 * Disable debug operations on this email send, if supported.
	 */
	public function disableDebug() {
		
	}
}
