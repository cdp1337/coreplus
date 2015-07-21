<?php
/**
 * Email wrapper around the system mail utility, (phpMailer in this case).
 *
 * @package Core
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2015  Charlie Powell
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

class Email {

	/**
	 * The template to render this email with.
	 *
	 * @var Template
	 */
	private $_template = null;

	/**
	 * The mailer object to send this email with.
	 *
	 * @var PHPMailer
	 */
	private $_mailer;

	/**
	 * The template to render this view with.
	 * Should be the partial path of the template, including emails/
	 *
	 * @example emails/mycomponent/thishappened.tpl
	 * @var string
	 */
	public $templatename;


	public function __construct() {

	}


	/**
	 * Get the template responsible for rendering this email.
	 *
	 * @return Template
	 */
	public function getTemplate() {
		if (!$this->_template) {
			$this->_template = \Core\Templates\Template::Factory($this->templatename);
			//$this->_template->setBaseURL($this->baseurl);
		}

		return $this->_template;
	}

	/**
	 * Get the mailer responsible for sending this email.
	 *
	 * @return PHPMailer
	 */
	public function getMailer()	{
		if (!$this->_mailer) {
			$this->_mailer = new PHPMailer(true);

			// Load in some default options for this email based on the configuration options.
			$this->_mailer->From = ConfigHandler::Get('/core/email/from');
			if (!$this->_mailer->From) $this->_mailer->From = 'website@' . $_SERVER['HTTP_HOST'];
			$this->_mailer->Sender = $this->_mailer->From;

			$this->_mailer->FromName = ConfigHandler::Get('/core/email/from_name');
			$this->_mailer->Mailer   = ConfigHandler::Get('/core/email/mailer');
			$this->_mailer->Sendmail = ConfigHandler::Get('/core/email/sendmail_path');
			if ($this->_mailer->Mailer == 'smtp') {
				$this->_mailer->Host       = ConfigHandler::Get('/core/email/smtp_host');
				$this->_mailer->Port       = ConfigHandler::Get('/core/email/smtp_port');
				$this->_mailer->Username   = ConfigHandler::Get('/core/email/smtp_user');
				$this->_mailer->Password   = ConfigHandler::Get('/core/email/smtp_password');
				$this->_mailer->SMTPSecure =
					(ConfigHandler::Get('/core/email/smtp_security') == 'none') ?
						'' : ConfigHandler::Get('/core/email/smtp_security');
				if ($this->_mailer->Username != '') $this->_mailer->SMTPAuth = true;
			}

			// Tack on some anti-abuse headers.
			// If these bug you, feel free to safely remove them, as they don't /actually/ do anything.
			// @todo If this bugs enough people, I might add it as a config option.

			$this->_mailer->AddCustomHeader('X-AntiAbuse: This header was added to track abuse, please include it with any abuse report');
			if (Core::User()->exists()) {
				$this->_mailer->AddCustomHeader('X-AntiAbuse: User_id - ' . Core::User()->get('id'));
				$this->_mailer->AddCustomHeader('X-AntiAbuse: User_name - ' . Core::User()->getDisplayName());
			}

			$this->_mailer->AddCustomHeader('X-AntiAbuse: Original Domain - ' . SERVERNAME);
			$this->_mailer->AddCustomHeader('X-AntiAbuse: Sitename - ' . SITENAME);
			$this->_mailer->AddCustomHeader('MimeOLE: Core Plus');
			$this->_mailer->AddCustomHeader('X-Content-Encoded-By: Core Plus ' . Core::GetComponent()->getVersion());

			// Default to set the formatting to HTML.
			//$this->_mailer->isHTML(true);
		}

		return $this->_mailer;
	}

	/**
	 * Add a custom header to the email message
	 * @param $val
	 */
	public function addCustomHeader($val){
		$this->getMailer()->AddCustomHeader($val);
	}

	/**
	 * Assign a value to this emails' template.
	 *
	 * Just serves as a pass-through for the Template::assign() method.
	 *
	 * @param string $key
	 * @param mixed  $val
	 */
	public function assign($key, $val) {
		$this->getTemplate()->assign($key, $val);
	}

	/**
	 * Get the rendered body (taking the template into consideration)
	 *
	 * @return string (HTML or plain text)
	 */
	public function renderBody() {
		if ($this->templatename) {
			return $this->getTemplate()->fetch($this->templatename);
		}
		else {
			// I can't render a template with no template....
			return $this->getMailer()->Body;
		}
	}

	/**
	 * Set the "to" address for this email.
	 *
	 * Will clear out any other to address.
	 *
	 * @param string $address
	 * @param string $name
	 */
	public function to($address, $name = '') {
		// Reset any "to" address already on the mailer.
		$m = $this->getMailer();
		$m->ClearAddresses();
		$m->AddAddress($address, $name);
	}

	public function from($address, $name = ''){
		// Will only affect the from, not the sender!
		$this->getMailer()->From = $address;
		if($name) $this->getMailer()->FromName = $name;
	}

	public function addBCC($address, $name = ''){
		$this->getMailer()->AddBCC($address, $name);
	}

	/**
	 * Add a "to" address for this email.
	 *
	 * Can be called multiple times for sending to multiple people, will
	 * add addresses to the "to" recipient on each call.
	 *
	 * @param string $address
	 * @param string $name
	 */
	public function addAddress($address, $name = '') {
		$this->getMailer()->AddAddress($address, $name);
	}

	/**
	 * Set the Reply To address for this email.
	 *
	 * @param $address
	 * @param string $name
	 */
	public function setReplyTo($address, $name = '') {
		$this->getMailer()->AddReplyTo($address, $name);
	}

	/**
	 * Set the body for this email.
	 *
	 * This is typically not used, as the Template system should be used whenever possible,
	 * but this is available for simple emails, ie: administrative "IT BROKE!" emails.
	 *
	 * @param string  $body
	 * @param boolean $ishtml Set to true if the $body is HTML.
	 */
	public function setBody($body, $ishtml = false) {
		$m = $this->getMailer();

		if($ishtml){
			// Message is an HTML message, OK!  The internal mailer will handle all conversions.
			$m->MsgHTML($body);
		}
		elseif($m->ContentType == 'text/html'){
			// No, but there is already an HTML message set, so update the alt body only.
			$m->AltBody = $body;
		}
		else{
			// Nope, and there's no message anyway, OK!
			$m->ContentType = 'text/plain';
			$m->Body = $body;
		}

		// Make sure the template is blanked out too!
		// This is because either the template method OR the set body method must be used... NOT BOTH
		$this->_template = null;
		$this->templatename = '';
	}

	/**
	 * Set the subject of this email.
	 *
	 * @param string $subject
	 */
	public function setSubject($subject) {
		$this->getMailer()->Subject = $subject;
	}

	/**
	 * Send the message
	 *
	 * @throws phpmailerException
	 * @return bool
	 */
	public function send() {
		$m = $this->getMailer();

		if(!\ConfigHandler::Get('/core/email/enable_sending')){
			// Allow a config option to disable sending entirely.
			SystemLogModel::LogInfoEvent('/email/disabled', 'Email sending is disabled, not sending email ' . $m->Subject . '!');
			return false;
		}

		if(\ConfigHandler::Get('/core/email/sandbox_to')){
			$to  = $m->getToAddresses();
			$cc  = $m->getCCAddresses();
			$bcc = $m->getBCCAddresses();
			$all = [];

			if(sizeof($to)){
				foreach($to as $e){
					$all[] = ['type' => 'To', 'email' => $e[0], 'name' => $e[1]];
				}
			}
			if(sizeof($cc)){
				foreach($cc as $e){
					$all[] = ['type' => 'CC', 'email' => $e[0], 'name' => $e[1]];
				}
			}
			if(sizeof($bcc)){
				foreach($bcc as $e){
					$all[] = ['type' => 'BCC', 'email' => $e[0], 'name' => $e[1]];
				}
			}

			foreach($all as $e){
				$m->AddCustomHeader('X-Original-' . $e['type'], ($e['name'] ? $e['name'] . ' <' . $e['email'] . '>' : $e['email']));
			}

			// Allow a config option to override the "To" address, useful for testing with production data.
			$m->ClearAllRecipients();
			$m->AddAddress(\ConfigHandler::Get('/core/email/sandbox_to'));
		}

		// Now, do some formatting to the body, ie: it NEEDS an alt body!
		//if(!$this->AltBody && $this->ContentType == 'text/html'){
		//	$this->MsgHTML($this->Body);
		//}


		// Render out the body.  Will be either HTML or text...
		$body = $this->renderBody();

		// Wrap this body with the main email template if it's set.
		if(ConfigHandler::Get('/theme/default_email_template')){
			$skintpl = \Core\Templates\Template::Factory('emailskins/' . ConfigHandler::Get('/theme/default_email_template'));
			$skintpl->assign('body', $body);
			$m->MsgHTML($skintpl->fetch());
		}
		elseif (strpos($body, '<html>') === false) {
			// Ensuring that the body is wrapped with <html> tags helps with spam checks with spamassassin.
			$m->MsgHTML('<html><body>' . $body . '</body></html>');
		}
		else{
			$m->MsgHTML($body);
		}


		return $m->Send();
	}
}