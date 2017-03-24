<?php
/**
 * Email wrapper around the system mail utility in Core.
 *
 * @package Core
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

namespace Core;

/**
 * Description of Email
 *
 * @author charlie
 */
class Email implements EmailInterface {
	
	/**
	 * The template to render this view with.
	 * Should be the partial path of the template, including emails/
	 *
	 * @example emails/mycomponent/thishappened.tpl
	 * @var string
	 */
	public $templatename;
	
	/**
	 * @var EmailInterface The backend provider for this system
	 */
	private $_backend;
	
	/**
	 * @var Template The template to render this email with.
	 */
	private $_template = null;
	
	/**
	 * @var View The view response for this email, (only applicable with template-based emails)
	 */
	private $_view;
	
	/**
	 * @var string An optional source to track this email in the logging system.
	 */
	private $_source = '';
	
	/**
	 * @var string|bool Set to true to request GPG encryption, or to a specific GPG key.
	 */
	private $_encryption = false;
	
	private $_isDebug = false;
	
	public function __construct() {
		$backend = \ConfigHandler::Get('/core/email/mailer');
		if(class_exists($backend)){
			$ref = new \ReflectionClass($backend);
			$this->_backend = $ref->newInstance();
		}
		else{
			throw new \Exception('Backend provider ' . $backend . ' not available.');
		}

		// Load in some default options for this email based on the configuration options.
		$fromEmail = \ConfigHandler::Get('/core/email/from');
		// Ensure the email is set to something.
		if(!$fromEmail && isset($_SERVER['HTTP_HOST'])){
			$fromEmail = 'website@' . $_SERVER['HTTP_HOST'];
		}
		$fromName = \ConfigHandler::Get('/core/email/from_name');
		
		$this->_backend->setFrom($fromEmail, $fromName);

		// Tack on some anti-abuse and meta headers.
		// These don't actually serve an explict function, but are added because.
		$this->_backend->setHeader('X-AntiAbuse', 'This header was added to track abuse, please include it with any abuse report');
		if (\Core\user()->exists()) {
			$this->_backend->setHeader('X-AntiAbuse', 'User_id - ' . \Core\user()->get('id'));
			$this->_backend->setHeader('X-AntiAbuse', 'User_name - ' . \Core\user()->getDisplayName());
		}
		$this->_backend->setHeader('X-AntiAbuse', 'Original Domain - ' . SERVERNAME);
		$this->_backend->setHeader('X-AntiAbuse', 'Sitename - ' . SITENAME);
		$this->_backend->setHeader('MimeOLE', 'Core Plus');
		$this->_backend->setHeader('X-Content-Encoded-By', 'Core Plus ' . \Core::GetComponent()->getVersion());
		$this->_backend->setHeader('X-Mailer', 'Core Plus ' . \Core::GetComponent()->getVersion() . ' (http://corepl.us)');
	}

	public function addAttachment(Filestore\File $file, $inline = false): EmailInterface {
		// Passthru Only
		return $this->_backend->addAttachment($file, $inline);
	}
	
	public function addData(string $data, string $filename, string $mimetype, $inline = false): EmailInterface {
		// Passthru Only
		return $this->_backend->addData($data, $filename, $mimetype, $inline);
	}

	/**
	 * Add a blind carbon copy email address.
	 * 
	 * Return the parent object to allow chaining.
	 * 
	 * @param string $address
	 * @param string $name
	 * 
	 * @return \Core\EmailInterface
	 */
	public function addBCC(string $address, string $name = ''): EmailInterface {
		// Passthru Only
		return $this->_backend->addBCC($address, $name);
	}
	
	/**
	 * Add a carbon copy email address.
	 * 
	 * Return the parent object to allow chaining.
	 * 
	 * @param string $address
	 * @param string $name
	 * 
	 * @return \Core\EmailInterface
	 */
	public function addCC(string $address, string $name = ''): EmailInterface {
		// Passthru Only
		return $this->_backend->addCC($address, $name);
	}
	
	/**
	 * Add a "to" address for this email.
	 *
	 * Can be called multiple times for sending to multiple people, will
	 * add addresses to the "to" recipient on each call.
	 *
	 * @param string $address
	 * @param string $name
	 * 
	 * @return \Core\EmailInterface
	 */
	public function addTo(string $address, string $name = ''): EmailInterface {
		// Passthru Only
		return $this->_backend->addTo($address, $name);
	}
	
	/**
	 * Add a Reply To address for this email.
	 *
	 * @param $address
	 * @param string $name
	 * 
	 * @return \Core\EmailInterface
	 */
	public function addReplyTo(string $address, string $name = ''): EmailInterface {
		// Passthru Only
		return $this->_backend->addReplyTo($address, $name);
	}
	
	public function getBCC(): array {
		// Passthru Only
		return $this->_backend->getBCC();
	}

	public function getCC(): array {
		// Passthru Only
		return $this->_backend->getCC();
	}

	public function getTo(): array {
		// Passthru Only
		return $this->_backend->getTo();
	}
	
	public function getFrom(): array {
		// Passthru Only
		return $this->_backend->getFrom();
	}
	
	public function getReplyTo(): array {
		// Passthru Only
		return $this->_backend->getReplyTo();
	}
	
	public function getSubject(): string {
		// Passthru Only
		return $this->_backend->getSubject();
	}
	
	public function getHeaders(): array {
		// Passthru Only
		return $this->_backend->getHeaders();
	}
	
	/**
	 * Get the raw and complete EML for this email, with headers, body, and all attachments.
	 * 
	 * @return string
	 */
	public function getFullEML(): string {
		// Passthru Only
		return $this->_backend->getFullEML();
	}
	
	public function setBody(string $body, bool $ishtml = false): EmailInterface {
		// Passthru Only
		return $this->_backend->setBody($body, $ishtml);
	}

	public function setFrom(string $address, string $name = ''): EmailInterface {
		// Passthru Only
		return $this->_backend->setFrom($address, $name);
	}

	public function setReplyTo(string $address, string $name = ''): EmailInterface {
		// Passthru Only
		return $this->_backend->setReplyTo($address, $name);
	}
	
	public function setSubject(string $subject): EmailInterface {
		// Passthru Only
		return $this->_backend->setSubject($subject);
	}
	
	public function setTo(string $address, string $name = ''): EmailInterface {
		// Passthru Only
		return $this->_backend->setTo($address, $name);
	}
	
	public function setHeader(string $key, string $value): EmailInterface {
		// Passthru Only
		return $this->_backend->setHeader($key, $value);
	}

	public function clearRecipients(): EmailInterface {
		// Passthru Only
		return $this->_backend->clearRecipients();
	}


	public function disableDebug() {
		$this->_isDebug = false;
		return $this->_backend->disableDebug();
	}

	public function enableDebug() {
		$this->_isDebug = true;
		return $this->_backend->enableDebug();
	}

	/**
	 * Assign a value to this email template.
	 *
	 * Just serves as a pass-through for the Template::assign() method.
	 *
	 * @param string $key
	 * @param mixed  $val
	 */
	public function assign(string $key, $val) {
		$this->getTemplate()->assign($key, $val);
	}

	/**
	 * Get the template responsible for rendering this email.
	 *
	 * @return \Core\Templates\TemplateInterface
	 */
	public function getTemplate() {
		if (!$this->_template) {
			$this->_view = new \View();
			$this->_view->mode = \View::MODE_EMAILORPRINT;
			$this->_view->templatename = $this->templatename;

			if(\ConfigHandler::Get('/theme/default_email_template')){
				$this->_view->mastertemplate = \ConfigHandler::Get('/theme/default_email_template');
			}
			else{
				$this->_view->mastertemplate = false;
			}

			$this->_template = $this->_view->getTemplate();
		}

		return $this->_template;
	}

	/**
	 * Send this email!
	 *
	 * @throws \Exception
	 */
	public function send() {
		if(!\ConfigHandler::Get('/core/email/enable_sending')){
			// Allow a config option to disable sending entirely.
			SystemLogModel::LogInfoEvent('/email/disabled', 'Email sending is disabled, not sending email ' . $m->Subject . '!');
			return false;
		}

		if(\ConfigHandler::Get('/core/email/sandbox_to')){
			$to  = $this->_backend->getTo();
			$cc  = $this->_backend->getCC();
			$bcc = $this->_backend->getBCC();
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
				$this->_backend->setHeader('X-Original-' . $e['type'], ($e['name'] ? $e['name'] . ' <' . $e['email'] . '>' : $e['email']));
			}

			// Allow a config option to override the "To" address, useful for testing with production data.
			$this->_backend->clearRecipients();
			$this->_backend->setTo(\ConfigHandler::Get('/core/email/sandbox_to'));
		}
		
		// If a template is requested for this email, use that for rendering.
		if($this->templatename){
			$this->_renderBody();
		}
		
		$recipients = $this->_backend->getTo();
		$errors     = [];

		if($this->_encryption !== false){
			$errors = $this->_sendEncrypted();
		}
		else{
			try{
				$this->_backend->send();
			}
			catch (\Exception $ex) {
				$errors[] = $ex->getMessage();
			}
		}
		
		// Record this in the system log.
		$to = [];
		foreach($recipients as $t){
			$to[] = $t[0];
		}
		$to = implode(',', $to);
		
		$log = \SystemLogModel::Factory();
		$log->set('icon', 'envelope-o');
		$log->set('code', '/email/sent');
		$log->set('source', $this->_source);
		if(sizeof($errors)){
			$log->set('type', 'error');
			$log->set('message', 'FAILED to send ' . $this->_backend->getSubject() . ' to ' . $to);
			$log->set('details', implode("\n", $errors) . "\n" . $this->_backend->getFullEML());
		}
		else {
			$log->set('type', 'info');
			$log->set('message', 'Sent ' . $this->_backend->getSubject() . ' to ' . $to);
			$log->set('details', $this->_backend->getFullEML());
		}
		$log->save();
		
		// Lastly if there were errors...
		if(sizeof($errors)){
			throw new \Exception(implode("\n<br/>", $errors));
		}
	}

	/**
	 * Enable GPG encryption on this email.
	 * 
	 * The parameter is expected to be the public key fingerprint of the recipient.
	 *
	 * @param string $fingerprint
	 * 
	 * @return \Core\Email
	 */
	public function setEncryption(string $fingerprint): Email {
		$this->_encryption = $fingerprint;
		
		return $this;
	}
	
	/**
	 * Set the "source" for this email to be recorded in the system logger.
	 * 
	 * @param string $source
	 * 
	 * @return \Core\Email
	 */
	public function setSource(string $source): Email {
		$this->_source = $source;
		
		return $this;
	}
	
	private function _renderBody(){
		// If a template is requested for this email, use that for rendering.
		if(
			$this->templatename && 
			($template = $this->getTemplate())
		){
			$html = $this->_view->fetch();
			if(strpos($html, '<html') === false){
				$html = '<html><body>' . $html . '</body></html>';
			}
			// This version includes HTML tags and all that.
			$this->_backend->setBody($html, true);
			
			// Now generate a plain text version of this template.
			// Use markdown for conversion;
			// it produces better results that phpMailer's built-in system!
			$converter = new \HTMLToMD\Converter();
			
			// Manually strip out the head content.
			// This was throwing the converters for a loop and injecting weird characters!
			$html = preg_replace('#<head[^>]*?>.*</head>#ms', '', $html);
			
			$text = $converter->convert($html);
			$this->_backend->setBody($text, false);
		}
	}

	private function _lookupGPGKey($email){
		$user = UserModel::Find(['email = ' . $email], 1);
		
		if(
			$user &&
			($key = $user->get('gpgauth_pubkey'))
		){
			return $key;
		}
		else{
			return null;
		}
	}
	
	private function _sendEncrypted(){
		// Encrypt this complete message.
		$recipients                 = $this->_backend->getTo();
		$fullMessage                = $this->_backend->getFullEML();
		list($fromEmail, $fromName) = $this->_backend->getFrom();
		$gpg                        = new \Core\GPG\GPG();
		$backendClassName           = get_class($this->_backend);
		$backendReflection          = new \ReflectionClass($backendClassName);
		$errors                     = [];

		// This is allowed for mutliple recipients!
		// This requires a little more overhead, as I need to lookup each recipient's user account
		// to retrieve their GPG key.
		foreach($recipients as $dat){
			$email = $dat[0];

			// If encryption is set as a blank/generic "yes"...
			// then the user must have the attached GPG data attached to their profile.
			$key = $this->_encryption === true ? $this->_lookupGPGKey($email) : $this->_encryption;

			try{
				if($key){
					$enc = $gpg->encryptData($fullMessage, $key);

					// Create a clone of the email object to send this data.
					/** @var $clone EmailInterface */
					$clone = $backendReflection->newInstance();
					$clone->setFrom($fromEmail, $fromName);
					$clone->setSubject('GPG Encrypted Message');
					$clone->setBody('Please use a GPG-compatible client to decrypt this message.', false);
					$clone->addData($enc, 'EncryptedMessage.gpg', 'application/octet-stream', true);
				}
				else{
					// Just send this email normally, creating a clone to ensure that it is sent to only one person.
					$errors[] = 'Unable to locate GPG key for ' . $email;
					$clone = clone($this->_backend);
					$clone->clearRecipients();
				}

				$clone->setTo($email);
				$clone->send();
			}
			catch (\Exception $ex) {
				// Save this exception to the log of issues.
				$errors[] = $ex->getMessage();
			}
		}
		
		return $errors;
	}

	
	/**
	 * Get an array with classname => title of all registered email providers available.
	 * 
	 * @return array
	 */
	public static function GetBackends(){
		$components = \Core::GetComponents();
		$backends = [];
		foreach($components as $c){
			$backends = array_merge($backends, $c->getEmailBackends());
		}
		
		return $backends;
	}

	

}
