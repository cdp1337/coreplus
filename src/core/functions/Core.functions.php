<?php
/**
 * Collection of useful utilities that don't quite fit into any class.
 *
 * @package Core Plus\Core
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2012  Charlie Powell
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

use DMI;
use Cache;

/**
 * Shortcut function to get the current system database/datamodel interface.
 * @return \DMI_Backend
 */
function db(){
	return DMI::GetSystemDMI()->connection();
}

/**
 * Shortcut function to get the current system cache interface.
 *
 * @return Cache
 */
function cache(){
	return Cache::GetSystemCache();
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
 * @return \User_Backend
 */
function user(){
	// Is the session data present?
	if(!isset($_SESSION['user'])){
		$_SESSION['user'] = \User::Factory();
	}
	else{
		/** @var $user \User */
		$user = $_SESSION['user'];

		// If this is in multisite mode, blank out the access string cache too!
		// This is because siteA may have some groups, while siteB may have another.
		// We don't want a user going to a site they have full access to, hopping to another and having cached permissions!
		if(\Core::IsComponentAvailable('enterprise') && \MultiSiteHelper::IsEnabled()){
			$user->clearAccessStringCache();
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
	return \Core\Filestore\factory($filename);
}

/**
 * Instantiate a new Directory object, ready for manipulation or access.
 *
 * @since 2011.07.09
 * @param string $directory
 * @return \Directory_Backend
 */
function directory($directory){
	switch(CDN_TYPE){
		case 'aws':
			return new \Directory_awss3_backend($directory);
			break;
		case 'local':
		default:
			// Automatically resolve this file.
			//$filename = \Core\Filestore\Backends\FileLocal:
			return new \Directory_local_backend($directory);
			break;
	}
}


/**
 * Get the standard HTTP request headers for retrieving remote files.
 *
 * @param bool $forcurl
 * @return array | string
 */
function get_standard_http_headers($forcurl = false, $autoclose = false){
	$headers = array(
		'User-Agent: Core Plus ' . Core::GetComponent()->getVersion() . ' (http://corepl.us)',
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
	$cachevalue = \Core::Cache()->get($keyname, (3600 * 24));

	if(!$cachevalue) $cachevalue = array();

	if(!isset($cachevalue[$asset])){
		// Well, look it up!
		$f = \Core::File($asset);

		$cachevalue[$asset] = $f->getURL();
		// Save this for future lookups.
		\Core::Cache()->set($keyname, $cachevalue, (3600 * 24));
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
 * @return string The full url of the link, including the http://...
 */
function resolve_link($url){
	// Allow "#" to be verbatim without translation.
	if($url == '#') return $url;

	// Allow already-resolved links to be returned verbatim.
	if(strpos($url, '://') !== false) return $url;

	$a = PageModel::SplitBaseURL($url);

	// Instead of going through the overhead of a pagemodel call, SplitBaseURL provides what I need!
	return ROOT_URL . substr($a['rewriteurl'], 1);

	$p = new PageModel($url);

	// @todo Add support for already-resolved links.

	return $p->getResolvedURL();

	//if($p->exists()) return $p->getResolvedURL();
	//else return ROOT_URL . substr($url, 1);

}

/**
 * Get an extension from a given filename.
 *
 * Will return just the extension itself without the ".", or a blank string if empty.
 *
 * @param $str
 *
 * @return string
 */
function get_extension_from_string($str) {
	// File doesn't have any extension... easy enough!
	if (strpos($str, '.') === false) return '';

	return substr($str, strrpos($str, '.') + 1);
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
 *	Prevents any POST data from being reloaded.
 *
 * @param string $page_to_redirect_to
 */
function Redirect($page){
	//This is NOT designed to refresh the current page.	If the pageto redirect to IS
	// this current page, simply do nothing.

	$page = \Core::ResolveLink($page);

	//if(!preg_match('/^[a-zA-Z]{0,7}:\/\//', $page)){
	//	$m = PageModel::Find(array('baseurl' => $page), 1);
	//	if(!$m) $page = ROOT_WDIR;
	//	else $page = $m->getResolvedURL();
	//}
	//var_dump($page);
	//die();
	// Do nothing if the page is the current page.... that is Reload()'s job.
	if($page == CUR_CALL) return false;

	header("Location:" . $page);
	die("If your browser does not refresh, please <a href=\"{$page}\">Click Here</a>");
}

function Reload(){
	header('Location:' . CUR_CALL);
	die("If your browser does not refresh, please <a href=\"" . CUR_CALL . "\">Click Here</a>");
}

function GoBack(){
	CAEUtils::redirect(CAEUtils::GetNavigation());
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
 * Function that attaches the core javascript to the page.
 *
 * This should be called automatically from the hook /core/page/prerender.
 */
function _AttachCoreJavascript(){

	$script = '<script type="text/javascript">
var Core = {
	Version: "' . Core::GetComponent()->getVersion() . '",
	ROOT_WDIR: "' . ROOT_WDIR . '",
	ROOT_URL: "' . ROOT_URL . '",
	ROOT_URL_SSL: "' . ROOT_URL_SSL . '",
	ROOT_URL_NOSSL: "' . ROOT_URL_NOSSL . '"
};
</script>';

	CurrentPage::AddScript($script, 'head');
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
 * Cleanup a string and ensure it can make a valid URL.
 *
 * @param string
 * @return string
 */
function str_to_url($string){
	// URLs should only be in latin.
	$string = str_to_latin($string);

	// Spaces get replaced with a separator
	$string = str_replace(' ', '-', $string);

	// Anything else I missed?  Get rid of it!
	$string = preg_replace('/[^a-z0-9\-]/i', '', $string);

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
	switch($errno){
		case UPLOAD_ERR_OK:
			return '';
		case UPLOAD_ERR_INI_SIZE:
			if(DEVELOPMENT_MODE){
				return 'The uploaded file exceeds the upload_max_filesize directive in php.ini [' . ini_get('upload_max_filesize') . ']';
			}
			else{
				return 'The uploaded file is too large, maximum size is ' . ini_get('upload_max_filesize');
			}
		case UPLOAD_ERR_FORM_SIZE:
			if(DEVELOPMENT_MODE){
				return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form. ';
			}
			else{
				return 'The uploaded file is too large.';
			}
		default:
			return 'An error occurred while trying to upload the file.';
	}
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
	$acceptgood = false;
	$accepts = array_map(
		'trim',
		explode(
			',',
			strtolower($acceptlist)
		)
	);

	// Also lowercase the incoming extension.
	$extension = strtolower($extension);

	foreach($accepts as $accepttype){
		// '*' is the wildcard to accept any filetype....
		// why would this even be set?!?
		if($accepttype == '*'){
			$acceptgood = true;
			break;
		}
		// accepts that are standard full mimetypes are also pretty easy.
		elseif(preg_match('#^[a-z\-\+]+/[0-9a-z\-\+\.]+#', $accepttype)){
			if($accepttype == $mimetype){
				$acceptgood = true;
				break;
			}
		}
		// wildcard mimetypes are allowed too.
		elseif(preg_match('#^[a-z\-\+]+/\*#', $accepttype)){
			if(strpos($mimetype, substr($accepttype, 0, -1)) === 0){
				$acceptgood = true;
				break;
			}
		}
		// extensions are allowed as well, (if provided)
		elseif($extension && preg_match('#^\.*#', $accepttype)){
			if(substr($accepttype, 1) == $extension){
				$acceptgood = true;
				break;
			}
		}
		// Umm....
		else{
			return 'Unsupported accept option, ' . $accepttype;
		}
	}

	// Now that all the mimetypes have run through, I can see if one matched.
	if(!$acceptgood){
		if(sizeof($accepts) > 1){
			$err = 'matches one of [ ' . implode(', ', $accepts) . ' ]';
		}
		else{
			$err = 'is a ' . $accepts[0] . ' file';
		}
		return 'Invalid file uploaded, please ensure it ' . $err;
	}
	else{
		return '';
	}
}

/**
 * Check if an array is a plain numerically indexed array, or not.
 *
 * It's useful for checking if an array's keys are meant to be used, or simply ignored.
 * This is important because sometimes a selectbox will have options set like array('foo', 'bar', 'baz')
 * and other times it's set as array(id => blah, id => foo, id => mep), where the id is important.
 *
 * @param $array array
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
