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

namespace PHPMailerCore;
use \Core\EmailInterface;

class EmailBackend {
	/**
	 * @var \PHPMailer The mailer object to send this email with.
	 */
	protected $_mailer;

	/**
	 * Construct a new email backend to send an email.
	 */
	public function __construct() {
		$this->_mailer = new \PHPMailer(true);
	}
	
	public function clearRecipients(): EmailInterface {
		$this->_mailer->clearAllRecipients();
		
		return $this;
	}

	/**
	 * Get all "BCC" addresses for this email.
	 * 
	 * @return array
	 */
	public function getBCC(): array {
		return $this->_mailer->getBccAddresses();
	}

	/**
	 * Get all "CC" addresses for this email.
	 * 
	 * @return array
	 */
	public function getCC(): array {
		return $this->_mailer->getCcAddresses();
	}

	/**
	 * Get all "to" addresses for this email.
	 * 
	 * @return array
	 */
	public function getTo(): array {
		return $this->_mailer->getToAddresses();
	}
	
	/**
	 * Get the "from" address for this email.
	 * 
	 * The return key must be an array with 2 items,
	 * 
	 * * index 0 must be the address.
	 * * index 1 must be empty or the name of the from name.
	 * 
	 * @return array
	 */
	public function getFrom(): array {
		return [
			$this->_mailer->From,
			$this->_mailer->FromName
		];
	}
	
	/**
	 * Get the "Reply-To" address for this email.
	 * 
	 * The return key must be an array with 2 items,
	 * 
	 * * index 0 must be the address.
	 * * index 1 must be empty or the name of the from name.
	 * 
	 * @return array
	 */
	public function getReplyTo(): array {
		return array_values($this->_mailer->getReplyToAddresses());
	}
	
	/**
	 * Get the subject for this email, as set by setSubject usually.
	 * 
	 * @return string
	 */
	public function getSubject(): string {
		return $this->_mailer->Subject;
	}
	
	/**
	 * Get all custom headers for this email
	 * 
	 * @return array
	 */
	public function getHeaders(): array {
		$hdr = $this->_mailer->getCustomHeaders();
		$ret = [];
		foreach($hdr as $dat){
			// The header key is the first parameter.
			$k = $dat[0];
			// phpMailer for some reason prepends the value with a space...
			$v = trim($dat[1]);
			
			if(isset($ret[$k]) && !is_array($ret[$k])){
				// Key is already set, but is not an array.
				$ret[$k] = [
					$ret[$k],
					$v
				];
			}
			elseif(isset($ret[$k]) && is_array($ret[$k])){
				// Key already set and IS an array.
				$ret[$k][] = $v;
			}
			else{
				// Just plain set.
				$ret[$k] = $v;
			}
		}
		
		return $ret;
	}
	
	/**
	 * Get the raw and complete EML for this email, with headers, body, and all attachments.
	 * 
	 * @return string
	 */
	public function getFullEML(): string {
		// The EML contains everything, so I need to render the email as it will be sent.
		// To do that, call phpMailer's preSend method to prep the data.
		$this->_mailer->PreSend();
		$header = $this->_mailer->CreateHeader();
		$body   = $this->_mailer->CreateBody();
		
		// Now, the EML is simply those two elements combined.
		return $header . $body;
	}
	
	/**
	 * Set the "to" address for this email.
	 *
	 * Will clear out any other to address.
	 * 
	 * Return the parent object to allow chaining.
	 *
	 * @param string $address
	 * @param string $name
	 * 
	 * @return \Core\EmailInterface
	 */
	public function setTo(string $address, string $name = ''): EmailInterface{
		// Reset any "to" address already on the mailer.
		$this->_mailer->ClearAddresses();
		$this->_mailer->AddAddress($address, $name);
		
		return $this;
	}

	/**
	 * Set the "from" address for this email
	 * 
	 * Return the parent object to allow chaining.
	 * 
	 * @param string $address
	 * @param string $name
	 * 
	 * @return \Core\EmailInterface
	 */
	public function setFrom(string $address, string $name = ''): EmailInterface{
		// Will only affect the from, not the sender!
		$this->_mailer->From = $address;
		if($name){
			$this->_mailer->FromName = $name;
		}
		
		return $this;
	}
	
	/**
	 * Set the Reply To address for this email.
	 *
	 * @param $address
	 * @param string $name
	 * 
	 * @return \Core\EmailInterface
	 */
	public function setReplyTo(string $address, string $name = ''): EmailInterface {
		$this->_mailer->clearReplyTos();
		$this->_mailer->AddReplyTo($address, $name);
		
		return $this;
	}
	
	/**
	 * Set the body for this email.
	 * 
	 * This can be either HTML or plain text.
	 * 
	 * If called multiple times with each version, then the body is set with HTML
	 * and the alt body is set as plain text.
	 *
	 * @param string  $body
	 * @param boolean $ishtml Set to true if the $body is HTML.
	 * 
	 * @return \Core\EmailInterface
	 */
	public function setBody(string $body, bool $ishtml = false): EmailInterface{
		if($ishtml){
			// Message is an HTML message, OK!  The internal mailer will handle all conversions.
			$this->_mailer->MsgHTML($body);
		}
		elseif($this->_mailer->ContentType == 'text/html'){
			// No, but there is already an HTML message set, so update the alt body only.
			$this->_mailer->AltBody = $body;
		}
		else{
			// Nope, and there's no message anyway, OK!
			$this->_mailer->ContentType = 'text/plain';
			$this->_mailer->Body = $body;
		}
		
		return $this;
	}
	
	/**
	 * Set the subject of this email.
	 *
	 * @param string $subject
	 * 
	 * @return \Core\EmailInterface
	 */
	public function setSubject(string $subject): EmailInterface {
		$this->_mailer->Subject = $subject;
		
		return $this;
	}
	
	/**
	 * Set a custom header on this Email object.
	 * 
	 * @param string $key
	 * @param string $value
	 * 
	 * @return \Core\EmailInterface
	 */
	public function setHeader(string $key, string $value): EmailInterface {
		switch($key){
			case 'X-Mailer':
				$this->_mailer->XMailer = $value;
				break;
			default:
				$this->_mailer->addCustomHeader($key . ': ' . $value);
				break;
		}
		
		return $this;
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
		$this->_mailer->AddBCC($address, $name);
		
		return $this;
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
		$this->_mailer->AddCC($address, $name);
		
		return $this;
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
		$this->_mailer->AddAddress($address, $name);
		
		return $this;
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
		$this->_mailer->AddReplyTo($address, $name);
		
		return $this;
	}
	
	/**
	 * Add a file as an attachment
	 *
	 * @param \Core\Filestore\File $file
	 * @param bool                 $inline Set to true to request this attachment to be viewed inline.
	 *
	 * @throws \Exception
	 * 
	 * @return \Core\EmailInterface
	 */
	public function addAttachment(\Core\Filestore\File $file, $inline = false): EmailInterface {
		$this->_mailer->addAttachment(
			$file->getFilename(),               // Full Path
			$file->getBasename(),               // Base Filename (to be exposed in client)
			'base64',                           // Yup, just do this
			$file->getMimetype(),               // Mimetype, try to use correct hinting for client
			($inline ? 'inline' : 'attachment') // Set the disposition based on inline or not.
		);
		
		return $this;
	}
	
	/**
	 * Add file data as an inline attachment
	 *
	 * @param string $data     The data to attach to this email
	 * @param string $filename Client Rendering Hint for file name
	 * @param string $mimetype Client Rendering Hint for file mimetype
	 * @param bool   $inline   Set to true to request this attachment to be viewed inline.
	 *
	 * @throws \Exception
	 * 
	 * @return \Core\EmailInterface
	 */
	public function addData(string $data, string $filename, string $mimetype, $inline = false): EmailInterface {
		$this->_mailer->addStringAttachment(
			$data,                              // Full data contents of the attachment
			$filename,                          // Base filename to render to the client
			'base64',                           // Base64 Encode the data
			$mimetype,                          // Mimetype, to assist with the client for hinting.
			($inline ? 'inline' : 'attachment') // Render this attachment inline if possible, more client hinting.
		);
		
		return $this;
	}
	
	/**
	 * Send this email!
	 *
	 * @throws \Exception
	 */
	public function send() {
		$this->_mailer->send();
	}
	
	
	
	

	public function setOnBehalfOf($address, $name = ''){
		if($this->_mailer->From){
			// Move these over to the Sender header.
			$sender = $this->_mailer->FromName;
			if($sender){
				$sender .= ' <' . $this->_mailer->From . '>';
			}
			else{
				$sender = $this->_mailer->From;
			}
			$this->_mailer->AddCustomHeader('Sender', $sender);
		}
		
		$this->_mailer->From = $address;
		if($name){
			$this->_mailer->FromName = $name;
		}
	}
}