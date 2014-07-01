<?php
/**
 * Collection of useful utilities that don't quite fit into any class.
 *
 * @package Core
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

namespace Core;

use Core\Datamodel;
use DMI;
use Cache;

/**
 * Shortcut function to get the current system database/datamodel interface.
 * @return Datamodel\BackendInterface;
 */
function db(){
	return DMI::GetSystemDMI()->connection();
}

/**
 * Get the global FTP connection.
 *
 * Returns the FTP resource or false on failure.
 *
 * @return resource | false
 */
function FTP(){
	static $ftp = null;

	if($ftp === null){
		// Is FTP enabled?
		$ftpuser = FTP_USERNAME;
		$ftppass = FTP_PASSWORD;

		if(!($ftpuser && $ftppass)){
			// This is the most common case; if the either the username or password is not set,
			// just don't try to connect to anything and set the FTP to false immediately.
			// This is usually because the admin never entered in credentials and wishes to use direct file access.
			$ftp = false;
			return false;
		}

		$ftp = ftp_connect('127.0.0.1');
		if(!$ftp){
			error_log('FTP enabled, but connection to "127.0.0.1" failed!');

			$ftp = false;
			return false;
		}

		if(!ftp_login($ftp, $ftpuser, $ftppass)){
			error_log('FTP enabled, but a bad username or password was used!');

			$ftp = false;
			return false;
		}
	}

	// if FTP is not enabled, I can't chdir...
	if($ftp){
		// Make sure the FTP directory is always as root whenever this is called.
		$ftproot = FTP_PATH;

		// This serves two purposes, one it resets the location of the FTP back to the home directory
		// and two, it ensures that the directory exists!
		if(!ftp_chdir($ftp, $ftproot)){
			error_log('FTP enabled, but FTP root of [' . $ftproot . '] was not valid or does not exist!');
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
 * @return \UserModel|\User|null
 */
function user(){

	if(!class_exists('\\UserModel')){
		return null;
	}

	if(!isset($_SESSION['user'])){
		$_SESSION['user'] = new \UserModel();
	}
	elseif(!$_SESSION['user'] instanceof \UserModel){
		// Clear out this user too!
		// This may happen with pre-2.8.x systems.
		$_SESSION['user'] = new \UserModel();
	}
	elseif(isset(\Session::$Externals['user_forcesync'])){
		// A force sync was requested by something that modified the original UserModel object.
		// Keep the user logged in, but reload the data from the database.
		$tmpuser = $_SESSION['user'];

		$_SESSION['user'] = \UserModel::Construct($tmpuser->get('id'));
		unset(\Session::$Externals['user_forcesync']);
	}

	/** @var $user \UserModel */
	$user = $_SESSION['user'];

	// If this is in multisite mode, blank out the access string cache too!
	// This is because siteA may have some groups, while siteB may have another.
	// We don't want a user going to a site they have full access to, hopping to another and having cached permissions!
	if(\Core::IsComponentAvailable('enterprise') && class_exists('MultiSiteHelper') && \MultiSiteHelper::IsEnabled()){
		$user->clearAccessStringCache();
	}

	// Did this user request sudo access for another user?
	if(isset($_SESSION['user_sudo'])){
		$sudo = $_SESSION['user_sudo'];

		if($sudo instanceof \UserModel){
			// It's a valid user!

			if($user->checkAccess('p:/user/users/sudo')){
				// This user can SUDO!
				// (only if the other user is < SA or current == SA).
				if($sudo->checkAccess('g:admin') && !$user->checkAccess('g:admin')){
					unset($_SESSION['user_sudo']);
					\SystemLogModel::LogSecurityEvent('/user/sudo', 'Authorized but non-SA user requested sudo access to a system admin!', null, $sudo->get('id'));
				}
				else{
					// Ok, everything is good.
					return $sudo;
				}
			}
			else{
				// This user can NOT sudo!!!
				unset($_SESSION['user_sudo']);
				\SystemLogModel::LogSecurityEvent('/user/sudo', 'Unauthorized user requested sudo access to another user!', null, $sudo->get('id'));
			}
		}
		else{
			unset($_SESSION['user_sudo']);
		}
	}

	return $_SESSION['user'];
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
 * @return array | string
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
 * @todo Add support for external assets.
 *
 * @param string $asset
 * @return string The full url of the asset, including the http://...
 */
function resolve_asset($asset){
	// Allow already-resolved links to be returned verbatim.
	if(strpos($asset, '://') !== false) return $asset;

	// Since an asset is just a file, I'll use the builtin file store system.
	// (although every file coming in should be assumed to be an asset, so
	//  allow for a partial path name to come in, assuming asset/).

	if(strpos($asset, 'assets/') !== 0) $asset = 'assets/' . $asset;

	// Maybe it's cached :)
	$keyname = 'asset-resolveurl';
	$cachevalue = \Core\Cache::Get($keyname, (3600 * 24));

	if(!$cachevalue) $cachevalue = array();

	if(!isset($cachevalue[$asset])){
		// Well, look it up!
		$f = \Core::File($asset);

		$cachevalue[$asset] = $f->getURL();
		// Save this for future lookups.
		\Core\Cache::Set($keyname, $cachevalue, (3600 * 24));
	}

	return $cachevalue[$asset];
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
	$url = strtolower($url);

	// Allow links starting with ? to be read as the current page.
	if($url{0} == '?'){
		$url = REL_REQUEST_PATH . $url;
	}

	// Allow multisite URLs to be passed in natively.
	if(strpos($url, 'site:') === 0){
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
 * Resolve filename to ... script.
 * Useful for converting a physical filename to an accessable URL.
 * @deprecated
 */
function ResolveFilenameTo($filename, $base = ROOT_URL){
	// If it starts with a '/', figure out if that's the ROOT_PDIR or ROOT_DIR.
	$file = preg_replace('/^(' . str_replace('/', '\\/', ROOT_PDIR . '|' . ROOT_URL) . ')/', '', $filename);
	// swap the requested base onto that.
	return $base . $file;
	//return preg_replace('/^' . str_replace('/', '\\/', ROOT_PDIR) . '/', $base, $filename);
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

	$page = \Core::ResolveLink($page);

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
	header('HTTP/1.1 ' . $movetext);
	header('Location: ' . $page);

	// Just before the page stops execution...
	\HookHandler::DispatchHook('/core/page/postrender');

	\Session::ForceSave();
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
	header('HTTP/1.1 302 Moved Temporarily');
	header('Location:' . CUR_CALL);

	// Just before the page stops execution...
	\HookHandler::DispatchHook('/core/page/postrender');

	\Session::ForceSave();
	die('If your browser does not refresh, please <a href="' . CUR_CALL . '">Click Here</a>');
}

/**
 * Utility function to just go back to a page before this one.
 *
 * @param int $depth The amount of pages back to go
 */
function go_back($depth=1) {
	$hist = \Core::GetHistory($depth);

	if($depth == 1 && CUR_CALL == $hist){
		// If the user requested the last page, but the last page is this page...
		// go back to the page before that!
		// This can happen commonly on form submissions.
		// You display a form on page X, submit it, and request to go back,
		// but simply displaying the same page should be done with reload.
		$hist = \Core::GetHistory(2);
	}

	redirect($hist);
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
 * If this is called from any page, the user is forced to redirect to the SSL version if available.
 * @return void
 */
function RequireSSL(){
	// No ssl, nothing much to do about nothing.
	if(!ENABLE_SSL) return;

	if(!isset($_SERVER['HTTPS'])){
		$page = ViewClass::ResolveURL($_SERVER['REQUEST_URI'], true);
		//$page = ROOT_URL_SSL . $_SERVER['REQUEST_URI'];

		header("Location:" . $page);
		die("If your browser does not refresh, please <a href=\"{$page}\">Click Here</a>");
	}
}

/**
 * Return the page the user viewed x amount of pages ago based on the navigation stack.
 *
 * @param string $base The base URL to lookup history for
 * @return string
 */
function GetNavigation($base){
	//var_dump($_SESSION); die();
	// NO nav history, guess I can't do much of anything...
	if(!isset($_SESSION['nav'])) return $base;

	if(!isset($_SESSION['nav'][$base])) return $base;

	// Else, it must have been found!
	$coreparams = array();
	$extraparams = array();
	foreach($_SESSION['nav'][$base]['parameters'] as $k => $v){
		if(is_numeric($k)) $coreparams[] = $v;
		else $extraparams[] = $k . '=' . $v;
	}
	return $base .
		( sizeof($coreparams) ? '/' . implode('/', $coreparams) : '') .
		( sizeof($extraparams) ? '?' . implode('&', $extraparams) : '');
}

/**
 * Record this page into the navigation history.
 *
 * @param string $page
 */
function RecordNavigation(PageModel $page){
	//echo "Setting navRecord.";
	if(!isset($_SESSION['nav'])) $_SESSION['nav'] = array();

	// Get just the application base, this will contain the parameters for it.
	// So example, if /App/SomeView/myparam?something=foo is the URL, 
	// /App/SomeView will be able to be used to lookup the parameters.
	$c = $page->getControllerClass();
	// I don't need the 'Controller' part of it.
	if(strpos($c, 'Controller') == strlen($c) - 10) $c = substr($c, 0, -10);

	$base = '/' . $c . '/' . $page->getControllerMethod();

	$_SESSION['nav'][$base] = array(
		'parameters' => $page->getParameters(),
		'time' => Time::GetCurrent(),
	);
}

/**
 * Add a message to the user's stack.
 *	It will be displayed the next time the user (or session) renders the page.
 *
 * @param string $message_text
 * @param string $message_type
 * @return boolean (on success)
 */
function SetMessage($messageText, $messageType = 'info'){

	if(trim($messageText) == '') return;

	$messageType = strtolower($messageType);

	// CLI doesn't use sessions.
	if(EXEC_MODE == 'CLI'){
		$messageText = preg_replace('/<br[^>]*>/i', "\n", $messageText);
		echo "[" . $messageType . "] - " . $messageText . "\n";
	}
	else{
		if(!isset($_SESSION['message_stack'])) $_SESSION['message_stack'] = array();
		$_SESSION['message_stack'][] = array(
			'mtext' => $messageText,
			'mtype' => $messageType,
		);
	}
}

function AddMessage($messageText, $messageType = 'info'){
	Core::SetMessage($messageText, $messageType);
}

/**
 * Retrieve the messages and optionally clear the message stack.
 *
 * @param unknown_type $return_type
 * @return unknown
 */
function GetMessages($returnSorted = FALSE, $clearStack = TRUE){
	/*
	global $_DB;
	global $_SESS;

	$fetches = $_DB->Execute(
		"SELECT `mtext`, `mtype` FROM `" . DB_PREFIX . "messages` WHERE `sid` = '{$_SESS->sid}'"
	);

	if($fetches->fields === FALSE) return array(); //Return a blank array, there are no messages.

	foreach($fetches as $fetch){
		$return[] = $fetch;
	}
	*/
	if(!isset($_SESSION['message_stack'])) return array();

	$return = $_SESSION['message_stack'];
	if($returnSorted) $return = Core::SortByKey($return, 'mtype');

	if($clearStack) unset($_SESSION['message_stack']);
	return $return;
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
 * @author Charlie Powell <charlie@eval.bz>
 */
function ImplodeKey($glue, &$array){
	$arrayKeys = array();
	foreach($array as $key => $value){
		$arrayKeys[] = $key;
	}
	return implode($glue, $arrayKeys);
}


/**
 * Generate a random hex-deciman value of a given length.
 *
 * @param int  $length
 * @param bool $casesensitive [false] Set to true to return a case-sensitive string.
 *                            Otherwise the resulting string will simply be all uppercase.
 *
 * @return string
 */
function random_hex($length = 1, $casesensitive = false){
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
 * Simple method to compare two values with each other in a more restrictive manner than == but not quite fully typecasted.
 *
 * This is useful for the scenarios that involve needing to check that "3" == 3, but "" != 0.
 *
 * @param $val1
 * @param $val2
 *
 * @return boolean
 */
function compare_values($val1, $val2){
	if($val1 === $val2){
		// Exact same values and exact same typecasts.  They're the same!
		return true;
	}
	if(is_numeric($val1) && is_numeric($val2) && $val1 == $val2){
		// Both values are numeric and seem to be the same value, ie: "3" and 3.
		return true;
	}
	if(is_scalar($val1) && is_scalar($val2) && strlen($val1) == strlen($val2) && $val1 == $val2){
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
	if (ConfigHandler::Get('/core/email/verify_with_dns') &&  !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"))) {
		// domain not found in DNS
		return false;
	}

	// All checks passed?
	return true;
}


/**
 * Clone of the php version_compare function, with the exception that it treats
 * version numbers the same that Debian treats them.
 *
 * @param string $version1 Version to compare
 * @param string $version2 Version to compare against
 * @param string $operation Operation to use or null
 * @return bool | int Boolean if $operation is provided, int if omited.
 */
function VersionCompare($version1, $version2, $operation = null){
	// Just to make sure they're strings at least.
	if(!$version1) $version1 = 0;
	if(!$version2) $version2 = 0;

	$version1 = Core::VersionSplit($version1);
	$version2 = Core::VersionSplit($version2);

	// version1 and 2 are now standardized.
	$keys = array('major', 'minor', 'point', 'core', 'user', 'stability');

	// @todo Support user and stability checks.

	// The standard keys I can compare pretty easily.
	$v1 = $version1['major'] . '.' . $version1['minor'] . '.' . $version1['point'] . '.' . $version1['core'];
	$v2 = $version2['major'] . '.' . $version2['minor'] . '.' . $version2['point'] . '.' . $version2['core'];
	$check = version_compare($v1, $v2);
	if($check != 0){
		// Will preserve PHP's -1, 0, 1 nature.
		if($operation == null) return $check;

		// Otherwise
		switch($operation){
			case 'lt':
			case 'le':
			case '<':
			case '<=':
				return ($check == -1);
			default:
				return true;
		}
	}
	else{
		// it's 0.
		if($operation == null) return $check;

		// Otherwise
		switch($operation){
			case 'le':
			case '<=':
			case 'ge':
			case '>=':
			case 'eq':
			case '=':
				return true;
			default:
				return false;
		}
	}
}

/**
 * Break a version string into the corresponding parts.
 *
 * Major Version
 * Minor Version
 * Point Release
 * Core Version
 * Developer-Specific Version
 * Development Status
 *
 * @param string $version
 * @return array
 */
function VersionSplit($version){
	$ret = array(
		'major' => 0,
		'minor' => 0,
		'point' => 0,
		'core' => 0,
		'user' => 0,
		'stability' => '',
	);

	$v = array();

	// dev < alpha = a < beta = b < RC = rc < # < pl = p
	$lengthall = strlen($version);
	$pos = 0;
	$x = 0;
	//while(($pos = strpos($version, '.')) !== false){
	while($pos < $lengthall && $x < 10){
		$nextpos = strpos($version, '.', $pos) - $pos;

		$part = ($nextpos > 0) ? substr($version, $pos, $nextpos) : substr($version, $pos);

		if(($subpos = strpos($part, '-')) !== false){
			$subpart = strtolower(substr($part, $subpos + 1));
			if(is_numeric($subpart)){
				$ret['core'] = $subpart;
			}
			elseif($subpart == 'a'){
				$ret['stability'] = 'alpha';
			}
			elseif($subpart == 'b'){
				$ret['stability'] = 'beta';
			}
			else{
				$ret['stability'] = $subpart;
			}

			$part = substr($part, 0, $subpos);
		}

		$v[] = (int)$part;
		$pos = ($nextpos > 0) ? $pos + $nextpos + 1 : $lengthall;
		$x++; // Just in case something really bad happens here...
	}

	for($i = 0; $i < 3; $i++){
		if(!isset($v[$i])) $v[$i] = 0;
	}

	$ret['major'] = $v[0];
	$ret['minor'] = $v[1];
	$ret['point'] = $v[2];
	return $ret;
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