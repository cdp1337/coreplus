<?php
/**
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
 * The interface for email systems to implement to ensure consistent functionality.
 */
interface EmailInterface {
	
	/**
	 * Construct a new email backend to send an email.
	 */
	public function __construct();
	
	/**
	 * Get all "to" addresses for this email.
	 * 
	 * @return array
	 */
	public function getTo(): array;
	
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
	public function getFrom(): array;
	
	/**
	 * Get all "CC" addresses for this email.
	 * 
	 * @return array
	 */
	public function getCC(): array;
	
	/**
	 * Get all "BCC" addresses for this email.
	 * 
	 * @return array
	 */
	public function getBCC(): array;
	
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
	public function getReplyTo(): array;
	
	/**
	 * Get the subject for this email, as set by setSubject usually.
	 * 
	 * @return string
	 */
	public function getSubject(): string;
	
	/**
	 * Get all custom headers for this email
	 * 
	 * @return array
	 */
	public function getHeaders(): array;
	
	/**
	 * Get the raw and complete EML for this email, with headers, body, and all attachments.
	 * 
	 * @return string
	 */
	public function getFullEML(): string;
	
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
	public function setTo(string $address, string $name = ''): EmailInterface;

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
	public function setFrom(string $address, string $name = ''): EmailInterface;
	
	/**
	 * Set the Reply To address for this email.
	 *
	 * @param $address
	 * @param string $name
	 * 
	 * @return \Core\EmailInterface
	 */
	public function setReplyTo(string $address, string $name = ''): EmailInterface;
	
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
	public function setBody(string $body, bool $ishtml = false): EmailInterface;

	/**
	 * Set the subject of this email.
	 *
	 * @param string $subject
	 * 
	 * @return \Core\EmailInterface
	 */
	public function setSubject(string $subject): EmailInterface;
	
	/**
	 * Set a custom header on this Email object.
	 * 
	 * @param string $key
	 * @param string $value
	 * 
	 * @return \Core\EmailInterface
	 */
	public function setHeader(string $key, string $value): EmailInterface;
	
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
	public function addTo(string $address, string $name = ''): EmailInterface;
	
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
	public function addCC(string $address, string $name = ''): EmailInterface;
	
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
	public function addBCC(string $address, string $name = ''): EmailInterface;

	/**
	 * Add a Reply To address for this email.
	 *
	 * @param $address
	 * @param string $name
	 * 
	 * @return \Core\EmailInterface
	 */
	public function addReplyTo(string $address, string $name = ''): EmailInterface;

	/**
	 * Add a file as an attachment
	 *
	 * @param \Core\Filestore\File $file   The file to add as an attachment.
	 * @param bool                 $inline Set to true to request this attachment to be viewed inline.
	 *
	 * @throws \Exception
	 * 
	 * @return \Core\EmailInterface
	 */
	public function addAttachment(\Core\Filestore\File $file, $inline = false): EmailInterface;

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
	public function addData(string $data, string $filename, string $mimetype, $inline = false): EmailInterface;
	
	/**
	 * Send this email!
	 *
	 * @throws \Exception
	 */
	public function send();
	
	/**
	 * Clear all recipients, (to, cc, and bcc), for this email.
	 *
	 * @throws \Exception
	 * 
	 * @return \Core\EmailInterface
	 */
	public function clearRecipients(): EmailInterface;
	
	/**
	 * Enable debug operations on this email send, if supported.
	 */
	public function enableDebug();
	
	/**
	 * Disable debug operations on this email send, if supported.
	 */
	public function disableDebug();
}
