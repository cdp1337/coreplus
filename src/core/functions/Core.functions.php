<?php
/**
 * Collection of useful utilities that don't quite fit into any class.
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

namespace Core;

use Core\Datamodel;
use Core\Filestore\FTP\FTPConnection;
use DMI;

/**
 * Shortcut function to get the current system database/datamodel interface.
 * @return \Core\Datamodel\BackendInterface;
 */
function db(){
	return DMI::GetSystemDMI()->connection();
}

/**
 * Get the global FTP connection.
 *
 * This is used for local assets and public files by some sites.
 *
 * Returns the FTP resource or false on failure.
 *
 * @return FTPConnection | false
 */
function ftp(){
	static $ftp = null;

	if($ftp === null){

		if(!defined('FTP_USERNAME')){
			// Prevent the installer from freaking out.
			$ftp = false;
			return false;
		}

		if(!defined('FTP_PASSWORD')){
			// Prevent the installer from freaking out.
			$ftp = false;
			return false;
		}

		if(!defined('FTP_PATH')){
			// Prevent the installer from freaking out.
			$ftp = false;
			return false;
		}

		if(!FTP_USERNAME){
			// If there is no username for the FTP server, don't try to connect either.
			$ftp = false;
			return false;
		}

		$ftp = new FTPConnection();
		$ftp->host = '127.0.0.1';
		$ftp->username = FTP_USERNAME;
		$ftp->password = FTP_PASSWORD;
		$ftp->root = FTP_PATH;
		$ftp->url = ROOT_WDIR;

		try{
			$ftp->connect();
		}
		catch(\Exception $e){
			\Core\ErrorManagement\exception_handler($e);
			$ftp = false;
			return false;
		}
	}

	if($ftp && $ftp instanceof FTPConnection){
		try{
			$ftp->reset();
		}
		catch(\Exception $e){
			\Core\ErrorManagement\exception_handler($e);
			$ftp = false;
			return false;
		}
	}

	return $ftp;
}

/**
 * Get the current user model that is logged in.
 *
 * To support legacy systems, this will also return the User object if it's available instead.
 * This support is for < 2.8.x Core installations and will be removed after some amount of time TBD.
 *
 * If no user systems are currently available, null is returned.
 *
 * @return \UserModel
 */
function user(){
	static $_CurrentUserAccount = null;

	if(!class_exists('\\UserModel')){
		return null;
	}

	if($_CurrentUserAccount !== null){
		// Cache this for the page load.
		return $_CurrentUserAccount;
	}

	if(isset($_SERVER['HTTP_X_CORE_AUTH_KEY'])){
		// Allow an auth key to be used to authentication the requested user instead!
		$user = \UserModel::Find(['apikey = ' . $_SERVER['HTTP_X_CORE_AUTH_KEY']], 1);
		if($user){
			$_CurrentUserAccount = $user;
		}
	}
	elseif(Session::Get('user') instanceof \UserModel){
		// There is a valid user account in the session!
		// But check if this user is forced to be resynced first.
		if(isset(Session::$Externals['user_forcesync'])){
			// A force sync was requested by something that modified the original UserModel object.
			// Keep the user logged in, but reload the data from the database.
			$_CurrentUserAccount = \UserModel::Construct(Session::Get('user')->get('id'));
			// And cache this updated user model back to the session.
			Session::Set('user', $_CurrentUserAccount);
			unset(Session::$Externals['user_forcesync']);
		}
		else{
			$_CurrentUserAccount = Session::Get('user');
		}
	}

	if($_CurrentUserAccount === null){
		// No valid user found.
		$_CurrentUserAccount = new \UserModel();
	}

	// If this is in multisite mode, blank out the access string cache too!
	// This is because siteA may have some groups, while siteB may have another.
	// We don't want a user going to a site they have full access to, hopping to another and having cached permissions!
	if(\Core::IsComponentAvailable('multisite') && class_exists('MultiSiteHelper') && \MultiSiteHelper::IsEnabled()){
		$_CurrentUserAccount->clearAccessStringCache();
	}

	// Did this user request sudo access for another user?
	if(Session::Get('user_sudo') !== null){
		$sudo = Session::Get('user_sudo');

		if($sudo instanceof \UserModel){
			// It's a valid user!

			if($_CurrentUserAccount->checkAccess('p:/user/users/sudo')){
				// This user can SUDO!
				// (only if the other user is < SA or current == SA).
				if($sudo->checkAccess('g:admin') && !$_CurrentUserAccount->checkAccess('g:admin')){
					Session::UnsetKey('user_sudo');
					\SystemLogModel::LogSecurityEvent('/user/sudo', 'Authorized but non-SA user requested sudo access to a system admin!', null, $sudo->get('id'));
				}
				else{
					// Ok, everything is good.
					// Remap the current user over to this sudo'd account!
					$_CurrentUserAccount = $sudo;
				}
			}
			else{
				// This user can NOT sudo!!!
				Session::UnsetKey('user_sudo');
				\SystemLogModel::LogSecurityEvent('/user/sudo', 'Unauthorized user requested sudo access to another user!', null, $sudo->get('id'));
			}
		}
		else{
			Session::UnsetKey('user_sudo');
		}
	}

	return $_CurrentUserAccount;
}


/**
 * Instantiate a new File object, ready for manipulation or access.
 *
 * @since 2011.07.09
 * @deprecated 2013.05.30
 * @param string $filename
 * @return \Core\Filestore\File
 */
function file($filename = null){
	return \Core\Filestore\Factory::File($filename);
}

/**
 * Instantiate a new Directory object, ready for manipulation or access.
 *
 * @since 2011.07.09
 * @param string $directory
 * @return \Directory_Backend
 */
function directory($directory){
	return \Core\Filestore\Factory::Directory($directory);
}

/**
 * Get the system page request
 *
 * @return \PageRequest
 */
function page_request(){
	return \PageRequest::GetSystemRequest();
}

/**
 * Get the system View
 *
 * @return \View
 */
function view(){
	return page_request()->getView();
}


/**
 * Get the standard HTTP request headers for retrieving remote files.
 *
 * @param bool $forcurl
 * @return array|string
 */
function get_standard_http_headers($forcurl = false, $autoclose = false){
	$headers = array(
		'User-Agent: Core Plus ' . \Core::GetComponent()->getVersion() . ' (http://corepl.us)',
		'Servername: ' . SERVERNAME,
	);

	if($autoclose){
		$headers[] = 'Connection: close';
	}

	if($forcurl){
		return $headers;
	}
	else{
		return implode("\r\n", $headers);
	}
}


/**
 * Resolve an asset to a fully-resolved URL.
 *
 * @param string $asset
 *
 * @return string The full url of the asset, including the http://...
 */
function resolve_asset($asset){
	// Allow already-resolved links to be returned verbatim.
	if(strpos($asset, '://') !== false){
		return $asset;
	}

	// Since an asset is just a file, I'll use the builtin file store system.
	// (although every file coming in should be assumed to be an asset, so
	//  allow for a partial path name to come in, assuming asset/).
	if(strpos($asset, 'assets/') !== 0){
		$asset = 'assets/' . $asset;
	}
	
	$file = \Core\Filestore\Factory::File($asset);
	return $file->getURL();
}

/**
 * Resolve a url or application path to a fully-resolved URL.
 *
 * This can also be an already-resolved link.  If so, no action is taken
 *  and the original URL is returned unchanged.
 *
 * @param string $url
 *
 * @return string The full url of the link, including the http://...
 */
function resolve_link($url) {
	// Allow "#" to be verbatim without translation.
	if ($url == '#') return $url;

	// Allow already-resolved links to be returned verbatim.
	if (strpos($url, '://') !== false) return $url;

	// <strike>FIRST</strike> Second THING!?!?!
	// All URLs should be case insensitive.
	// As such, I *should* be able to safely strlower everything and be fine.
	// This is particularly important because all URL lookups from the database are performed in lowercase.
	//$url = strtolower($url);

	// Allow links starting with ? to be read as the current page.
	if($url{0} == '?'){
		$url = REL_REQUEST_PATH . $url;
	}

	// Allow multisite URLs to be passed in natively.
	if(stripos($url, 'site:') === 0){
		$slashpos = strpos($url, '/');
		$site = substr($url, 5, $slashpos-5);
		$url = substr($url, $slashpos);
	}
	else{
		$site = null;
	}

	try{
		$a = \PageModel::SplitBaseURL($url, $site);
	}
	catch(\Exception $e){
		// Well, this isn't a fatal error, so just warn the admin and continue on.
		\Core\ErrorManagement\exception_handler($e);
		error_log('Unable to resolve URL [' . $url . '] due to exception [' . $e->getMessage() . ']');
		return '';
	}

	// Instead of going through the overhead of a pagemodel call, SplitBaseURL provides what I need!
	return $a['fullurl'];
}

/**
 * Redirect the user to another page via sending the Location header.
 *    Prevents any POST data from being reloaded.
 *
 * @param  string $page The page URL to redirect to
 * @param  int    $code  The HTTP status code to send to the browser, MUST be 301 or 302.
 *
 * @throws \Exception
 */
function redirect($page, $code = 302){
	if(!($code == 301 || $code == 302)){
		throw new \Exception('Invalid response code requested for redirect, [' . $code . '].  Please ensure it is either a 301 (permanent), or 302 (temporary) redirect!');
	}
	//This is NOT designed to refresh the current page.	If the pageto redirect to IS
	// this current page, simply do nothing.

	// If the index page is requested, just return there regardless if anything exists there or not.
	// This is to get around an infinite redirect loop bug when there is no page set as the index page,
	// despite many systems redirecting back to "/" when there is an unexpected error or behaviour.
	$hp = ($page == '/');

	$page = resolve_link($page);

	if(!$page && $hp) $page = ROOT_URL;

	// Do nothing if the page is the current page.... that is Reload()'s job.
	if ($page == CUR_CALL) return;

	// Determine the string to send with the code.
	switch($code){
		case 301:
			$movetext = '301 Moved Permanently';
			break;
		case 302:
			$movetext = '302 Moved Temporarily';
			break;
		default:
			// Umm...
			$movetext = $code . ' Moved Temporarily';
			break;
	}

	header('X-Content-Encoded-By: Core Plus ' . (DEVELOPMENT_MODE ? \Core::GetComponent()->getVersion() : ''));
	if(\ConfigHandler::Get('/core/security/x-frame-options')){
		header('X-Frame-Options: ' . \ConfigHandler::Get('/core/security/x-frame-options'));
	}
	if(\ConfigHandler::Get('/core/security/csp-frame-ancestors')){
		header('Content-Security-Policy: frame-ancestors \'self\' ' . \ConfigHandler::Get('/core/security/content-security-policy'));
	}
	header('HTTP/1.1 ' . $movetext);
	header('Location: ' . $page);

	// Just before the page stops execution...
	\HookHandler::DispatchHook('/core/page/postrender');

	die('If your browser does not refresh, please <a href="' . $page . '">Click Here</a>');
}

/**
 * Utility function to reload the current page
 */
function reload(){
	$movetext = '302 Moved Temporarily';

	header('X-Content-Encoded-By: Core Plus ' . (DEVELOPMENT_MODE ? \Core::GetComponent()->getVersion() : ''));
	if(\ConfigHandler::Get('/core/security/x-frame-options')){
		header('X-Frame-Options: ' . \ConfigHandler::Get('/core/security/x-frame-options'));
	}
	if(\ConfigHandler::Get('/core/security/csp-frame-ancestors')){
		header('Content-Security-Policy: frame-ancestors \'self\' ' . \ConfigHandler::Get('/core/security/content-security-policy'));
	}
	header('HTTP/1.1 302 Moved Temporarily');
	header('Location:' . CUR_CALL);

	// Just before the page stops execution...
	\HookHandler::DispatchHook('/core/page/postrender');

	die('If your browser does not refresh, please <a href="' . CUR_CALL . '">Click Here</a>');
}

/**
 * Utility function to just go back to a page before this one.
 */
function go_back() {
	$request = page_request();
	$history = $request->getReferrer();

	if($history != CUR_CALL){
		redirect($history);
	}
	else{
		reload();
	}
}

/**
 * Utility to Core-ify a given HTML string.
 *
 * Will use a tokenizer to scan for <img /> tags and <a /> tags.
 *
 * @param $html
 * @return string
 */
function parse_html($html){
	// Counter for the current position of the tokenizer.
	$x = 0;
	// Set to the position of the current image
	$imagestart = null;

	// @todo a rel=nofollow tags for external/untrusted links
	//       This can make use of an external utility to allow the admin to set which links are allowed.

	// @todo a tags that are absolutely resolved or have a core prefix such as core:///about-us or what not.

	while($x < strlen($html)){
		// Replace images with the optimized version.
		if(substr($html, $x, 4) == '<img'){
			$imagestart = $x;
			$x+= 3;
			continue;
		}

		$fullimagetag = null;

		if($imagestart !== null && $html{$x} == '>'){
			// This will equal the full image HTML tag, ie: <img src="blah"/>...
			$fullimagetag = substr($html, $imagestart, $x-$imagestart+1);
		}
		elseif($imagestart !== null && substr($html, $x, 2) == '/>'){
			// This will equal the full image HTML tag, ie: <img src="blah"/>...
			$fullimagetag = substr($html, $imagestart, $x-$imagestart+2);
		}

		if($imagestart !== null && $fullimagetag){
			// Convert it to a DOM element so I can process it.
			$simple = new \SimpleXMLElement($fullimagetag);
			$attributes = array();
			foreach($simple->attributes() as $k => $v){
				$attributes[$k] = (string)$v;
			}

			$file = \Core\Filestore\Factory::File($attributes['src']);

			// All images need alt tags.
			if(!isset($attributes['alt']) || $attributes['alt'] == ''){
				// Since this is usually only used to render content and the images contained therein,
				// and tinymce auto-adds a blank alt="" attribute,
				// I can safely add the alt based on the file's rendered title.
				$attributes['alt'] = $file->getTitle();
			}

			if(isset($attributes['width']) || isset($attributes['height'])){

				if(isset($attributes['width']) && isset($attributes['height'])){
					$dimension = $attributes['width'] . 'x' . $attributes['height'] . '!';
					unset($attributes['width'], $attributes['height']);
				}
				elseif(isset($attributes['width'])){
					$dimension = $attributes['width'];
					unset($attributes['width']);
				}
				else{
					$dimension = $attributes['height'];
					unset($attributes['height']);
				}

				$attributes['src'] = $file->getPreviewURL($dimension);
			}

			// And rebuild.
			$img = '<img';
			foreach($attributes as $k => $v){
				$img .= ' ' . $k . '="' . str_replace('"', '&quot;', $v) . '"';
			}
			$img .= '/>';

			$metahelper  = new \Core\Filestore\FileMetaHelper($file);
			$metacontent = $metahelper->getAsHTML();
			if($metacontent){
				$img = '<div class="image-metadata-wrapper">' . $img . $metacontent . '</div>';
			}

			// Figure out the offset for X.  I'll need to modify this after I merge it in.
			$x += strlen($img) - strlen($fullimagetag);
			// Split this string back in.
			$html = substr_replace($html, $img, $imagestart, strlen($fullimagetag));

			// Reset...
			$imagestart = null;
		}
		$x++;
	}
	//var_dump($html);

	return $html;
}

/**
 * Add a message to the user's stack.
 *	It will be displayed the next time the user (or session) renders the page.
 *
 * @param string $message_text The message text or the MESSAGE_ string constant for i18n and automatic type detection!
 * @param string $message_type
 *
 * @return boolean (on success)
 */
function set_message($messageText, $messageType = 'info'){
	if(strpos($messageText, 't:MESSAGE_') === 0){
		// It's an i18n message!  Retrieve the locale version of text and the message type.
		$messageText = substr($messageText, 2);

		if(strpos($messageText, 'MESSAGE_SUCCESS_') === 0){
			$messageType = 'success';
		}
		elseif(strpos($messageText, 'MESSAGE_ERROR_') === 0){
			$messageType = 'error';
		}
		elseif(strpos($messageText, 'MESSAGE_TUTORIAL_') === 0){
			$messageType = 'tutorial';
		}
		elseif(strpos($messageText, 'MESSAGE_WARNING_') === 0){
			$messageType = 'warning';
		}
		elseif(strpos($messageText, 'MESSAGE_INFO_') === 0){
			$messageType = 'info';
		}
		else{
			$messageType = 'info';
		}

		if(func_num_args() > 1){
			// Use func_call to call 1, as I need to pass in the other options too!
			$messageText = call_user_func_array('t', func_get_args());
		}
		else{
			$messageText = t($messageText);
		}
	}

	// CLI doesn't use sessions, echo directly to stdout instead.
	if(EXEC_MODE == 'CLI'){
		$messageText = preg_replace('/<br[^>]*>/i', "\n", $messageText);
		echo "[" . $messageType . "] - " . $messageText . "\n";
	}
	else{
		$stack = Session::Get('message_stack', []);

		$stack[] = array(
			'mtext' => $messageText,
			'mtype' => $messageType,
		);
		Session::Set('message_stack', $stack);
	}
}


/**
 * Retrieve the messages and optionally clear the message stack.
 *
 * @param bool $returnSorted Set to true to sort the message by message type
 * @param bool $clearStack   Set to false to NOT clear the message stack
 *
 * @return array
 */
function get_messages($returnSorted = FALSE, $clearStack = TRUE){
	return \Core::GetMessages($returnSorted, $clearStack);
}

function SortByKey($named_recs, $order_by, $rev=false, $flags=0){
	// Create 1-dimensional named array with just
	// sortfield (in stead of record) values
	$named_hash = array();
	foreach($named_recs as $key=>$fields) $named_hash["$key"] = $fields[$order_by];

	// Order 1-dimensional array,
	// maintaining key-value relations
	if($rev) arsort($named_hash,$flags) ;
	else asort($named_hash, $flags);

	// Create copy of named records array
	// in order of sortarray
	$sorted_records = array();
	foreach($named_hash as $key=>$val) $sorted_records["$key"]= $named_recs[$key];

	return $sorted_records;
}


/**
 * Return a string of the keys of the given array glued together.
 *
 * @param $glue string
 * @param $array array
 * @return string
 *
 * @version 2008.06.05
 * @author Charlie Powell <charlie@evalagency.com>
 */
function ImplodeKey($glue, &$array){
	$arrayKeys = array();
	foreach($array as $key => $value){
		$arrayKeys[] = $key;
	}
	return implode($glue, $arrayKeys);
}


/**
 * Generate a random hex-decimal value of a given length.
 *
 * @param int  $length
 * @param bool $casesensitive [false] Set to true to return a case-sensitive string.
 *                            Otherwise the resulting string will simply be all uppercase.
 *
 * @return string
 */
function random_hex($length = 1, $casesensitive = false){
	
	// @todo Bug #1757
	$output = '';
	if($casesensitive){
		$chars = '0123456789ABCDEFabcdef';
		$charlen = 21; // (needs to be -1 of the actual length)
	}
	else{
		$chars = '0123456789ABCDEF';
		$charlen = 15; // (needs to be -1 of the actual length)
	}

	$output = '';

	for ($i = 0; $i < $length; $i++){
		$pos = rand(0, $charlen);
		$output .= $chars{$pos};
	}

	return $output;
}


/**
 * Simple method to compare two values with each other in a more restrictive manner
 * than == but not quite fully typecasted.
 *
 * This is useful for the scenarios that involve needing to check that "3" == 3, but "" != 0.
 * 
 * Returns true if they seem to be the same, false if they differ.
 *
 * @param mixed $val1
 * @param mixed $val2
 *
 * @return boolean
 */
function compare_values($val1, $val2){
	if($val1 === $val2){
		// Exact same values and exact same typecasts.  They're the same!
		return true;
	}
	elseif(is_numeric($val1) && is_numeric($val2) && $val1 == $val2){
		// Both values are numeric and seem to be the same value, ie: "3" and 3.
		return true;
	}
	elseif(is_scalar($val1) && is_scalar($val2) && strlen($val1) == strlen($val2) && $val1 == $val2){
		// If they're both strings of the same length and equal to each other... same value.
		return true;
	}

	return false;
}

/**
 * Compare two values as strings explictly.
 * This is useful for numbers that need to behave like strings, ie: postal codes with their leading zeros.
 *
 * @param $val1
 * @param $val2
 *
 * @return boolean
 */
function compare_strings($val1, $val2) {
	if($val1 === $val2){
		// Exact same values and exact same typecasts.  They're the same!
		return true;
	}
	if(strlen($val1) == strlen($val2) && $val1 == $val2){
		// If they're both strings of the same length and equal to each other... same value.
		return true;
	}

	return false;
}


/**
 * Utility function to translate a filesize in bytes into a human-readable version.
 *
 * @deprecated 2013.06.01
 * @param int $filesize Filesize in bytes
 * @param int $round Precision to round to
 * @return string
 */
function FormatSize($filesize, $round = 2){
	return \Core\Filestore\format_size($filesize, $round);
}

function GetExtensionFromString($str){
	// File doesn't have any extension... easy enough!
	if(strpos($str, '.') === false) return '';

	return substr($str, strrpos($str, '.') + 1 );
}

/**
 * Validate an email address.
 * Provide email address (raw input)
 * Returns true if the email address has the email
 * address format and the domain exists.
 *
 * Copied (almost) verbatim from http://www.linuxjournal.com/article/9585?page=0,3
 * @author Douglas Lovell @ Linux Journal
 *
 * @return boolean
 */
function CheckEmailValidity($email){
	$atIndex = strrpos($email, "@");
	if (is_bool($atIndex) && !$atIndex) return false;

	$domain = substr($email, $atIndex+1);
	$local = substr($email, 0, $atIndex);
	$localLen = strlen($local);
	$domainLen = strlen($domain);
	if ($localLen < 1 || $localLen > 64) {
		// local part length exceeded
		return false;
	}

	if ($domainLen < 1 || $domainLen > 255) {
		// domain part length exceeded
		return false;
	}

	if ($local[0] == '.' || $local[$localLen-1] == '.') {
		// local part starts or ends with '.'
		return false;
	}

	if (preg_match('/\\.\\./', $local)) {
		// local part has two consecutive dots
		return false;
	}
	if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
		// character not valid in domain part
		return false;
	}

	if (preg_match('/\\.\\./', $domain)) {
		// domain part has two consecutive dots
		return false;
	}

	if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local))) {
		// character not valid in local part unless local part is quoted
		if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\","",$local))) {
			return false;
		}
	}

	// Allow the admin to skip DNS checks via config.
	if (\ConfigHandler::Get('/core/email/verify_with_dns') &&  !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"))) {
		// domain not found in DNS
		return false;
	}

	// All checks passed?
	return true;
}

function str_to_latin($string){

	$internationalmappings = array(
		'À' => 'A',
		'Á' => 'A',
		'Â' => 'A',
		'Ã' => 'A',
		'Ä' => 'A',
		'Å' => 'AA',
		'Æ' => 'AE',
		'Ç' => 'C',
		'È' => 'E',
		'É' => 'E',
		'Ê' => 'E',
		'Ë' => 'E',
		'Ì' => 'I',
		'Í' => 'I',
		'Î' => 'I',
		'Ï' => 'I',
		'Ð' => 'D',
		'Ł' => 'L',
		'Ñ' => 'N',
		'Ò' => 'O',
		'Ó' => 'O',
		'Ô' => 'O',
		'Õ' => 'O',
		'Ö' => 'O',
		'Ø' => 'OE',
		'Ù' => 'U',
		'Ú' => 'U',
		'Ü' => 'U',
		'Û' => 'U',
		'Ý' => 'Y',
		'Þ' => 'Th',
		'ß' => 'ss',
		'à' => 'a',
		'á' => 'a',
		'â' => 'a',
		'ã' => 'a',
		'ä' => 'a',
		'å' => 'aa',
		'æ' => 'ae',
		'ç' => 'c',
		'è' => 'e',
		'é' => 'e',
		'ê' => 'e',
		'ë' => 'e',
		'ì' => 'i',
		'í' => 'i',
		'î' => 'i',
		'ï' => 'i',
		'ð' => 'd',
		'ł' => 'l',
		'ñ' => 'n',
		'ń' => 'n',
		'ò' => 'o',
		'ó' => 'o',
		'ô' => 'o',
		'õ' => 'o',
		'ō' => 'o',
		'ö' => 'o',
		'ø' => 'oe',
		'ś' => 's',
		'ù' => 'u',
		'ú' => 'u',
		'û' => 'u',
		'ū' => 'u',
		'ü' => 'u',
		'ý' => 'y',
		'þ' => 'th',
		'ÿ' => 'y',
		'ż' => 'z',
		'Œ' => 'OE',
		'œ' => 'oe',
		'&' => 'and'
	);

	// This is slightly more simple than the javascript version.
	return str_replace(array_keys($internationalmappings), array_values($internationalmappings), $string);
}

/**
 * Cleanup a string and ensure it can make a valid URL component.
 *
 * Note, this is only meant for an individual directory level of a URL, such as the filename or a directory name.
 * This is because it removes slashes "/".
 *
 * <h3>Usage</h3>
 *
 * <p>Typical Usage</p>
 * <code>
 * // Will print "something-foo"
 * echo \Core\str_to_url('Something Foo!');
 *
 * // International characters are also handled gracefully
 * // Will print "thors hammer"
 * echo \Core\str_to_url('Þors hammer');
 * </code>
 *
 * <p>If you are dealing with a filename instead of a url, you may want to preserve the extensions.</p>
 * <code>
 * // Will print awesome-hot-image.jpg
 * echo \Core\str_to_url('AWESOME höt Image!!!!!!!.JPG');
 * </code>
 *
 * @param string $string  Incoming string to convert
 * @param bool   $keepdots Set to true if you want to keep dots "." and file extensions.
 *
 * @return string
 */
function str_to_url($string, $keepdots = false){
	// URLs should only be in latin.
	$string = str_to_latin($string);

	if(\ConfigHandler::Get('/core/page/url_remove_stop_words')){
		$stopwords = get_stop_words();

		$exploded = explode(' ', $string);
		$nt = '';
		foreach($exploded as $w){
			$lw = strtolower($w);
			if(!in_array($lw, $stopwords)){
				$nt .= ' ' . $w;
			}
		}
		$string = trim($string);
	}

	// Spaces get replaced with a separator
	$string = str_replace(' ', '-', $string);

	// Anything else I missed?  Get rid of it!
	if($keepdots){
		$string = preg_replace('/[^a-z0-9\-\.]/i', '', $string);
	}
	else{
		$string = preg_replace('/[^a-z0-9\-]/i', '', $string);
	}

	// Multiple separators should get truncated, along with beginning and trailing ones.
	$string = preg_replace('/[-]+/', '-', $string);
	$string = preg_replace('/^-/', '', $string);
	$string = preg_replace('/-$/', '', $string);

	// And lowercase it.
	$string = strtolower($string);

	return $string;
}


/**
 * Function to translate a PHP upload error into a human-readable string.
 *
 * If no error is seen, a blank string is returned.
 *
 * @param int
 * @return string
 */
function translate_upload_error($errno){
	return \Core\Filestore\translate_upload_error($errno);
}

/**
 * Check a file mimetype against a base.
 *
 * Useful for upload scripts.
 *
 * @param $acceptlist string List of accept types to accept.
 *                    Can be wildcard types too, for example image/*, or simply *.
 * @param $mimetype string File Mimetype to check against
 * @param $extension string Extension of the file to check against.
 *                  (not required, but provides better true positives, [and potentially more false positives] ).
 *
 * @return string Empty string if passed, else the error.
 */
function check_file_mimetype($acceptlist, $mimetype, $extension = null){
	return \Core\Filestore\check_file_mimetype($acceptlist, $mimetype, $extension);
}

/**
 * Check if an array is a plain numerically indexed array, or not.
 *
 * It's useful for checking if an array's keys are meant to be used, or simply ignored.
 * This is important because sometimes a selectbox will have options set like array('foo', 'bar', 'baz')
 * and other times it's set as array(id => blah, id => foo, id => mep), where the id is important.
 *
 * @param $array array
 *
 * @return bool
 */
function is_numeric_array($array){
	if(!is_array($array)) return false;

	reset($array);

	// First check.. if the first index a 0?  If not... it's not a numeric array!
	if(key($array) !== 0) return false;

	// Numerically indexed arrays will have a size == to the final key.
	$c = count($array) - 1;
	end($array);
	if(key($array) !== $c) return false;

	// Hopefully.... checking the first and last keys.
	return true;
}

/**
 * Get an array of English "stop" words.
 *
 * These are words that are insignificant to search engines and text parsing scripts.
 *
 * @return array
 */
function get_stop_words(){
	$stopwords = array('a', 'about', 'above', 'above', 'across', 'after', 'afterwards', 'again', 'against', 'all', 'almost', 'alone', 'along', 'already', 'also','although','always','am','among', 'amongst', 'amoungst', 'amount',  'an', 'and', 'another', 'any','anyhow','anyone','anything','anyway', 'anywhere', 'are', 'around', 'as',  'at', 'back','be','became', 'because','become','becomes', 'becoming', 'been', 'before', 'beforehand', 'behind', 'being', 'below', 'beside', 'besides', 'between', 'beyond', 'bill', 'both', 'bottom','but', 'by', 'call', 'can', 'cannot', 'cant', 'co', 'con', 'could', 'couldnt', 'cry', 'de', 'describe', 'detail', 'do', 'done', 'down', 'due', 'during', 'each', 'eg', 'eight', 'either', 'eleven','else', 'elsewhere', 'empty', 'enough', 'etc', 'even', 'ever', 'every', 'everyone', 'everything', 'everywhere', 'except', 'few', 'fifteen', 'fify', 'fill', 'find', 'fire', 'first', 'five', 'for', 'former', 'formerly', 'forty', 'found', 'four', 'from', 'front', 'full', 'further', 'get', 'give', 'go', 'had', 'has', 'hasnt', 'have', 'he', 'hence', 'her', 'here', 'hereafter', 'hereby', 'herein', 'hereupon', 'hers', 'herself', 'him', 'himself', 'his', 'how', 'however', 'hundred', 'ie', 'if', 'in', 'inc', 'indeed', 'interest', 'into', 'is', 'it', 'its', 'itself', 'keep', 'last', 'latter', 'latterly', 'least', 'less', 'ltd', 'made', 'many', 'may', 'me', 'meanwhile', 'might', 'mill', 'mine', 'more', 'moreover', 'most', 'mostly', 'move', 'much', 'must', 'my', 'myself', 'name', 'namely', 'neither', 'never', 'nevertheless', 'next', 'nine', 'no', 'nobody', 'none', 'noone', 'nor', 'not', 'nothing', 'now', 'nowhere', 'of', 'off', 'often', 'on', 'once', 'one', 'only', 'onto', 'or', 'other', 'others', 'otherwise', 'our', 'ours', 'ourselves', 'out', 'over', 'own','part', 'per', 'perhaps', 'please', 'put', 'rather', 're', 'same', 'see', 'seem', 'seemed', 'seeming', 'seems', 'serious', 'several', 'she', 'should', 'show', 'side', 'since', 'sincere', 'six', 'sixty', 'so', 'some', 'somehow', 'someone', 'something', 'sometime', 'sometimes', 'somewhere', 'still', 'such', 'system', 'take', 'ten', 'than', 'that', 'the', 'their', 'them', 'themselves', 'then', 'thence', 'there', 'thereafter', 'thereby', 'therefore', 'therein', 'thereupon', 'these', 'they', 'thickv', 'thin', 'third', 'this', 'those', 'though', 'three', 'through', 'throughout', 'thru', 'thus', 'to', 'together', 'too', 'top', 'toward', 'towards', 'twelve', 'twenty', 'two', 'un', 'under', 'until', 'up', 'upon', 'us', 'very', 'via', 'was', 'we', 'well', 'were', 'what', 'whatever', 'when', 'whence', 'whenever', 'where', 'whereafter', 'whereas', 'whereby', 'wherein', 'whereupon', 'wherever', 'whether', 'which', 'while', 'whither', 'who', 'whoever', 'whole', 'whom', 'whose', 'why', 'will', 'with', 'within', 'without', 'would', 'yet', 'you', 'your', 'yours', 'yourself', 'yourselves', 'the');
	return $stopwords;
}

/**
 * Generate a globally unique identifier that can be used as a replacement for an autoinc or similar.
 *
 * This method IS compatible with multiple servers on a single codebase!
 *
 * An example of a UUID returned by this function would be: "1a3f-c5dbcaaf9db-8d77"
 *
 * @since 2.4.2
 *
 * @return string
 */
function generate_uuid(){
	static $__serverid = null;
	
	if($__serverid === null){
		$serverid = defined('SERVER_ID') ? SERVER_ID : '0001';

		if($serverid == '1'){
			// Catch for legacy servers that had '1' as the server ID.
			// after 5.0.1, this has been migrated to a global UUID of its own.
			$serverid = '0001';
		}
		
		if(strlen($serverid) > 4){
			// It should be honestly!  It's 32-digits long.
			$serverid = substr($serverid, -4);
		}
		
		$__serverid = $serverid;
	}
	else{
		$serverid = $__serverid;
	}
	
	//var_dump($serverid); die();
	
	return strtolower(
		$serverid . '-' . 
		dechex(microtime(true) * 10000) . '-' .
		random_hex(4)	
	);
}

/**
 * Clone of the php version_compare function, with the exception that it treats
 * version numbers the same that Debian treats them.
 *
 * @param string $version1 Version to compare
 * @param string $version2 Version to compare against
 * @param string $operation Operation to use or null
 *
 * @return bool | int Boolean if $operation is provided, int if omited.
 */
function version_compare($version1, $version2, $operation = null) {
	$version1 = new \Core\VersionString($version1);
	return $version1->compare($version2, $operation);
}

/**
 * Format a time duration in a human-readable format.
 * 
 * The formatting returned is in the format of:
 *
 * 0.0000001 = "100 ns"
 * 0.0000010 = "1 µs" (10E-6)
 * 0.0000100 = "10 µs"
 * 0.0001000 = "100 µs"
 * 0.0010000 = "1 ms" (10E-3)
 * 0.0100000 = "10 ms"
 * 0.1000000 = "100 ms"
 * 1.0000000 = "1 s"
 * 60.000000 = "1 m"
 * 70.000000 = "1 m 10 s"
 * 3600.0000 = "1 h"
 * 3720.0000 = "1 h 2 m"
 * 
 * @param float $time_in_seconds Time (in seconds) to format
 * @param int   $round           Optionally rounding precision
 *
 * @return string
 */
function time_duration_format($time_in_seconds, $round = 4){
	if($time_in_seconds < 0.000001){
		$suffix = 'ns';
		$time = $time_in_seconds * 1000000000; 
	}
	elseif($time_in_seconds < 0.001){
		$suffix = 'µs';
		$time = $time_in_seconds * 1000000;
	}
	elseif($time_in_seconds < 1){
		$suffix = 'ms';
		$time = $time_in_seconds * 1000;
	}
	elseif($time_in_seconds < 60){
		$suffix = 's';
		$time = $time_in_seconds;
	}
	elseif($time_in_seconds < 3600){
		$m = floor($time_in_seconds / 60);
		$s = round($time_in_seconds - $m*60, 0);
		return $m . ' m ' . $s . ' s';
	}
	else{
		$h = floor($time_in_seconds / 3600);
		$m = round($time_in_seconds - $h*3600, 0);
		return $h . ' h ' . $m . ' m';
	}

	return number_format(round($time, $round), $round)  . ' ' . $suffix;
}