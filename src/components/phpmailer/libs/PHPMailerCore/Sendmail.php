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

/**
 * Description of Sendmail
 *
 * @author charlie
 */
class Sendmail extends EmailBackend implements \Core\EmailInterface {
	/**
	 * Construct a new email backend to send an email.
	 */
	public function __construct() {
		parent::__construct();
		
		$this->_mailer->Mailer = 'sendmail';
		$this->_mailer->Sendmail = \ConfigHandler::Get('/phpmailer/sendmail/path');
	}

	public function disableDebug() {
		
	}

	public function enableDebug() {
		
	}

}
