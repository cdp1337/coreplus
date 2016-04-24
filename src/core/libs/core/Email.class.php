<?php
/**
 * Email wrapper around the system mail utility, (phpMailer in this case).
 *
 * @package Core
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2016  Charlie Powell
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
	 * @var Template The template to render this email with.
	 */
	private $_template = null;

	/**
	 * @var View The view response for this email, (only applicable with template-based emails)
	 */
	private $_view;

	/**
	 * @var PHPMailer The mailer object to send this email with.
	 */
	private $_mailer;

	private $_encryption;

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
	 * @return \Core\Templates\TemplateInterface
	 */
	public function getTemplate() {
		if (!$this->_template) {
			$this->_view = new View();
			$this->_view->mode = View::MODE_EMAILORPRINT;
			$this->_view->templatename = $this->templatename;

			if(ConfigHandler::Get('/theme/default_email_template')){
				$this->_view->mastertemplate = ConfigHandler::Get('/theme/default_email_template');
			}
			else{
				$this->_view->mastertemplate = false;
			}

			$this->_template = $this->_view->getTemplate();
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
				
				switch(ConfigHandler::Get('/core/email/smtp_auth')){
					case 'LOGIN':
					case 'PLAIN':
						$this->_mailer->AuthType = ConfigHandler::Get('/core/email/smtp_auth'); 
						$this->_mailer->Username = ConfigHandler::Get('/core/email/smtp_user');
						$this->_mailer->Password = ConfigHandler::Get('/core/email/smtp_password');
						$this->_mailer->SMTPAuth = true;
						break;
					case 'NTLM':
						$this->_mailer->AuthType = ConfigHandler::Get('/core/email/smtp_auth');
						$this->_mailer->Username = ConfigHandler::Get('/core/email/smtp_user');
						$this->_mailer->Password = ConfigHandler::Get('/core/email/smtp_password');
						$this->_mailer->Realm    = ConfigHandler::Get('/core/email/smtp_domain');
						$this->_mailer->SMTPAuth = true;
						break;
					case 'NONE':
						$this->_mailer->SMTPAuth = false;
						break;
				}
				
				$this->_mailer->SMTPSecure =
					(ConfigHandler::Get('/core/email/smtp_security') == 'none') ?
					'' : ConfigHandler::Get('/core/email/smtp_security');
			}

			// Tack on some anti-abuse and meta headers.
			// These don't actually serve an explict function, but are added because.
			$this->_mailer->AddCustomHeader('X-AntiAbuse: This header was added to track abuse, please include it with any abuse report');
			if (\Core\user()->exists()) {
				$this->_mailer->AddCustomHeader('X-AntiAbuse: User_id - ' . \Core\user()->get('id'));
				$this->_mailer->AddCustomHeader('X-AntiAbuse: User_name - ' . \Core\user()->getDisplayName());
			}

			$this->_mailer->AddCustomHeader('X-AntiAbuse: Original Domain - ' . SERVERNAME);
			$this->_mailer->AddCustomHeader('X-AntiAbuse: Sitename - ' . SITENAME);
			$this->_mailer->AddCustomHeader('MimeOLE: Core Plus');
			$this->_mailer->AddCustomHeader('X-Content-Encoded-By: Core Plus ' . Core::GetComponent()->getVersion());
			$this->_mailer->XMailer = 'Core Plus ' . Core::GetComponent()->getVersion() . ' (http://corepl.us)';
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
		if ($this->templatename && $this->_view) {
			$html = $this->_view->fetch();
			if(strpos($html, '<html') === false){
				$html = '<html>' . $html . '</html>';
			}
			return $html;
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
	 * Add a file as an attachment!
	 *
	 * @param \Core\Filestore\File $file
	 *
	 * @throws phpmailerException
	 */
	public function addAttachment(\Core\Filestore\File $file){
		$this->getMailer()->AddAttachment(
			$file->getFilename(), // Full Path
			$file->getBasename(), // Base Filename (to be exposed in client)
			'base64', // Yup, just do this
			$file->getMimetype() // Mimetype, try to use correct hinting for client
		);
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
	 * Enable encryption on this email.
	 *
	 * @param string $fingerprint
	 */
	public function setEncryption($fingerprint){
		$this->_encryption = $fingerprint;
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

		// Render out the body.  Will be either HTML or text...
		$body = $this->renderBody();

		// Wrap this body with the main email template if it's set.
		if($this->templatename && $this->_view){
			// This version includes HTML tags and all that.
			$m->Body = $body;
			$m->IsHTML(true);
			// Use markdown for conversion.
			// It produces better results that phpMailer's built-in system!
			$converter = new \HTMLToMD\Converter();

			// Manually strip out the head content.
			// This was throwing the converters for a loop and injecting weird characters!
			$body = preg_replace('#<head[^>]*?>.*</head>#ms', '', $body);

			$m->AltBody = $converter->convert($body);
		}
		elseif (strpos($body, '<html>') === false) {
			// Ensuring that the body is wrapped with <html> tags helps with spam checks with spamassassin.
			$m->MsgHTML('<html><body>' . $body . '</body></html>');
		}
		else{
			$m->MsgHTML($body);
		}

		if($this->_encryption){
			// Encrypt this message, (both HTML and Alt), and all attachments.
			// I need to request the full EML from phpMailer so I can encrypt everything.
			// Then, the body will be recreated after Send is called.
			$m->PreSend();
			$header = $m->CreateHeader();
			$body   = $m->CreateBody();
			$gpg    = new \Core\GPG\GPG();

			if($this->_encryption === true){
				// This is allowed for mutliple recipients!
				// This requires a little more overhead, as I need to lookup each recipient's user account
				// to retrieve their GPG key.
				$recipients = $m->getToAddresses();

				foreach($recipients as $dat){
					$email = $dat[0];
					$user = UserModel::Find(['email = ' . $email], 1);
					if(!$user){
						SystemLogModel::LogErrorEvent('/core/email/failed', 'Unable to locate GPG key for ' . $email . ', cannot send encrypted email to recipient!');
					}
					else{
						$key = $user->get('gpgauth_pubkey');
						if(!$key){
							SystemLogModel::LogErrorEvent('/core/email/failed', 'No GPG key uploaded for ' . $email . ', cannot send encrypted email to recipient!');
						}
						else{
							$enc = $gpg->encryptData($header . $body, $key);

							// Create a clone of the email object to send this data.
							/** @var PHPMailer $clone */
							$clone = clone $m;
							$clone->ClearAddresses();
							$clone->AddAddress($email);
							$clone->Body = $enc;
							$clone->AltBody = '';
							$clone->Send();
						}
					}
				}
				return true;
			}
			else{
				// Single recipient!
				$enc = $gpg->encryptData($header . $body, $this->_encryption);

				$m->Body = $enc;
				$m->AltBody = '';
				return $m->Send();
			}
		}

		return $m->Send();
	}
}