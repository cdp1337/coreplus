<?php
/**
 * // enter a good description here
 * 
 * @package Core
 * @since 2011.06
 * @author Charlie Powell <powellc@powelltechs.com>
 * @copyright Copyright 2011, Charlie Powell
 * @license GNU Lesser General Public License v3 <http://www.gnu.org/licenses/lgpl-3.0.html>
 * This system is licensed under the GNU LGPL, feel free to incorporate it into
 * custom applications, but keep all references of the original authors intact,
 * read the full license terms at <http://www.gnu.org/licenses/lgpl-3.0.html>, 
 * and please contribute back to the community :)
 */

class Core implements ISingleton{
//class Core extends InstallArchiveAPI implements ISingleton{

	private static $instance;
	
	//public $version;
	//public $versionDB = false;
	//
	//public $valid = true;
	//
	private $_loaded = false;
	//
	//public $name = 'Core';
	
	/**
	 * The component object that contains the 'Core' definition.
	 * @var Component
	 */
	private $_componentobj;
	
	/**
	 * Events and the microtime it took to get there from initialization.
	 * 
	 * Useful for benchmarking and performance tuning.
	 * @var array
	 */
	private $_profiletimes = array();
	
	private static $_User = null;
	
	
	/*****     PUBLIC METHODS       *********/
	
	
	public function load(){
		return;
		if($this->_loaded) return;
		
		// Get the filename for the component, MUST follow a specific naming convention.
		$XMLFilename = ROOT_PDIR . 'core/core.xml';
		
		// Start the load procedure.
		$this->setFilename($XMLFilename);
		$this->setRootName('core');
		
		
		if(!parent::load()){
			$this->error = $this->error | Component::ERROR_INVALID;
			$this->errstrs[] = $XMLFilename . ' parsing failed, not valid XML.';
			$this->valid = false;
			return;
		}
		
		/*
		// Can't read the file? nothing to load...
		if(!is_readable($XMLFilename)){
			$this->valid = false;
			return;
		}
		
		// Save the DOM object so I have it in the future.
		$this->DOM = new DOMDocument();
		if(!@$this->DOM->load($XMLFilename)){
			$this->valid = false;
			return;
		}
		
		$xmlObjRoot = $this->DOM->getElementsByTagName("core")->item(0);
		
		$this->version = $xmlObjRoot->getAttribute("version");
		*/
		$this->version = $this->getRootDOM()->getAttribute("version");
		$this->_loaded = true;
	}
	
	/**
	 * Just a simple function to make this object compatable with the Component objects.
	 * @return boolean
	 */
	public function isLoadable(){
		return $this->_isInstalled();
	}
	
	public function isValid(){
		return $this->valid;
	}
	
	/**
	 * Another simple function to make this object compatible with the Component objects.
	 * @return unknown_type
	 */
	public function loadFiles(){
		return true;
	}
	
	public function hasLibrary(){
		return true;
	}
	public function hasModule(){
		return true;
	}
	public function hasJSLibrary(){
		return false;
	}
	public function getClassList(){
		return array('Core' => ROOT_PDIR . 'core/Core.class.php', 'CoreView' => ROOT_PDIR . 'core/CoreView.class.php');
	}
	public function getViewClassList(){
		return array('CoreView' => ROOT_PDIR . 'core/CoreView.class.php');
	}
	public function getLibraryList(){
		return array('Core' => $this->versionDB);
	}
	public function getViewSearchDirs(){
		return array(ROOT_PDIR . 'core/view/');
	}
	public function getIncludePaths(){
		return array();
	}
	
	public function install(){
	
		if($this->_isInstalled()) return;
		
		if(!class_exists('DB')) return; // I need a database present before I can install.
		
		InstallTask::ParseNode(
			$this->getRootDOM()->getElementsByTagName('install')->item(0),
			ROOT_PDIR . 'core/'
		);
		
		DB::Execute("REPLACE INTO `".DB_PREFIX."component` (`name`, `version`) VALUES (?, ?)", array('Core', $this->version));
		$this->versionDB = $this->version;
	}
	
	public function upgrade(){
		if(!$this->_isInstalled()) return false;
		
		if(!class_exists('DB')) return; // I need a database present before I can install.
		
		$canBeUpgraded = true;
		while($canBeUpgraded){
			// Set as false to begin with, (will be set back to true if an upgrade is ran).
			$canBeUpgraded = false;
			foreach($this->getElements('upgrade') as $u){
				// look for a valid upgrade path.
				if(Core::GetComponent()->getVersionInstalled() == @$u->getAttribute('from')){
					// w00t, found one...
					$canBeUpgraded = true;
					
					InstallTask::ParseNode($u, ROOT_PDIR . 'core/');
					
					$this->versionDB = @$u->getAttribute('to');
					DB::Execute("REPLACE INTO `" . DB_PREFIX . "component` (`name`, `version`) VALUES (?, ?)", array($this->name, $this->versionDB));
				}
			}
		}
	}
	
	
	
	/******      PRIVATE METHODS     *******/
	
	
	
	private function __construct(){
		//$this->load();
		$this->_componentobj = new Component('core');
		//$this->_componentobj->load();
		//var_dump($this->_componentobj); die();
	}
	
	private function _addProfileTime($event, $microtime = null){
		// If no microtime requested, grab the current.
		if($microtime === null) $microtime = microtime(true);
		// Find the differences between the first and now.
		$time = (sizeof($this->_profiletimes))? ($microtime - $this->_profiletimes[0]['microtime']) : 0;
		
		// And record!
		$this->_profiletimes[] = array(
			'event' => $event,
			'microtime' => $microtime,
			'timetotal' => $time
		);
	}
	
	
	private function _isInstalled(){
		//var_dump($this->_componentobj, $this->_componentobj->getVersion()); die();
		return ($this->_componentobj->getVersionInstalled() === false)? false : true;
	}
	
	private function _needsUpdated(){
		return ($this->_componentobj->getVersionInstalled() != $this->_componentobj->getVersion());
	}
	
	
	
	
	
	
	/*****      PUBLIC STATIC METHODS       *******/
	
	
	/**
	 * Shortcut function to get the current system database/datamodel interface.
	 * 
	 * @return DMI_Interface
	 */
	public static function DB(){
		return DMI::GetSystemDMI()->connection();
	}
	
	/**
	 * Shortcut function to get the current system cache interface.
	 * 
	 * @return Cache
	 */
	public static function Cache(){
		return Cache::GetSystemCache();
	}
	
	/**
	 * Get the global FTP connection.
	 * 
	 * Returns the FTP resource or false on failure.
	 * 
	 * @return resource | false
	 */
	public static function FTP(){
		static $ftp = null;
		
		if($ftp === null){
			// Is FTP enabled?
			$ftpuser = ConfigHandler::Get('/core/ftp/username');
			$ftppass = ConfigHandler::Get('/core/ftp/password');
			
			if(!($ftpuser && $ftppass)){
				$ftp = false;
				return false;
			}
			
			$ftp = ftp_connect('127.0.0.1');
			if(!$ftp){
				$ftp = false;
				return false;
			}
			ftp_login($ftp, $ftpuser, $ftppass);
		}
		
		// if FTP is not enabled, I can't chdir...
		if($ftp){
			// Make sure the FTP directory is always as root whenever this is called.
			$ftproot = ConfigHandler::Get('/core/ftp/path');
			ftp_chdir($ftp, $ftproot);
		}
		
		return $ftp;
	}
	
	/**
	 * Get the current user model that is logged in.
	 * 
	 * @return User
	 */
	public static function User(){
		if(self::$_User === null){
			// Is the session data present?
			if(!isset($_SESSION['user'])){
				self::$_User = User::Factory();
			}
			else{
				self::$_User = $_SESSION['user'];
			}
		}
		
		return self::$_User;
	}
	
	/**
	 * Instantiate a new File object, ready for manipulation or access.
	 * 
	 * @since 2011.07.09
	 * @param string $filename
	 * @return File_Backend 
	 */
	public static function File($filename = null){
		$backend = ConfigHandler::Get('/core/filestore/backend');
		switch($backend){
			case 'aws':
				return new File_awss3_backend($filename);
				break;
			case 'local':
			default:
				// Automatically resolve this file.
				//$filename = File_local_backend:
				return new File_local_backend($filename);
				break;
		}
	}
	
	
	/**
	 * Instantiate a new Directory object, ready for manipulation or access.
	 * 
	 * @since 2011.07.09
	 * @param string $directory
	 * @return Directory_Backend 
	 */
	public static function Directory($directory){
		$backend = ConfigHandler::Get('/core/filestore/backend');
		switch($backend){
			case 'aws':
				return new Directory_awss3_backend($directory);
				break;
			case 'local':
			default:
				// Automatically resolve this file.
				//$filename = File_local_backend:
				return new Directory_local_backend($directory);
				break;
		}
	}
	
	/**
	 * Translate a dimension, (or dimensions), to a "preview size"
	 * of sm, med, lg or xl.
	 * 
	 * @param string $dimensions Dimensions to translate
	 * @param [optional] int $width If second parameter is sent, assume width, height.
	 * @return string
	 */
	public static function TranslateDimensionToPreviewSize($dimensions){
		// Load in the theme sizes for reference.
		$themesizes = array(
			'sm' => ConfigHandler::Get('/theme/filestore/preview-size-sm'),
			'med' => ConfigHandler::Get('/theme/filestore/preview-size-med'),
			'lg' => ConfigHandler::Get('/theme/filestore/preview-size-lg'),
			'xl' => ConfigHandler::Get('/theme/filestore/preview-size-xl'),
		);
		
		if(sizeof(func_get_args()) == 2){
			// Assume $width, $height.
			$width = (int) func_get_arg(0);
			$height = (int) func_get_arg(1);
		}
		elseif(is_numeric($dimensions)){
			// It's a straight single number, use that for both dimensions.
			$width = $dimensions;
			$height = $dimensions;
		}
		elseif(stripos($dimensions, 'x') !== false){
			// It's a string joining both dimensions.
			$ds = explode('x', strtolower($dimensions));
			$width = trim($ds[0]);
			$height = trim($ds[1]);
		}
		else{
			// Invalid size given.
			return null;
		}
		
		$smaller = min($width, $height);
		
		if($smaller >= $themesizes['xl']) return 'xl';
		elseif($smaller >= $themesizes['lg']) return 'lg';
		elseif($smaller >= $themesizes['med']) return 'med';
		else return 'sm';
	}
	
	
	
	public static function AddProfileTime($event, $microtime = null){
		self::Singleton()->_addProfileTime($event, $microtime);
	}
	
	public static function GetProfileTimeTotal(){
		$microtime = microtime(true);
		// Find the differences between the first and now.
		return (sizeof(self::Singleton()->_profiletimes))? ($microtime - self::Singleton()->_profiletimes[0]['microtime']) : 0;
	}
	
	/**
	 * Get the component object for the core.
	 * @return Component
	 */
	public static function GetComponent(){
		return self::Singleton()->_componentobj;
	}
	
	/**
	 * Get the standard HTTP request headers for retrieving remote files.
	 * 
	 * @param bool $forcurl 
	 * @return array | string
	 */
	public static function GetStandardHTTPHeaders($forcurl = false, $autoclose = false){
		$headers = array(
			'User-Agent: Core Plus ' . self::GetComponent()->getVersion() . ' (http://corepl.us)',
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
	
	public static function Singleton(){
		if(is_null(self::$instance)) self::$instance = new self();
		return self::$instance;
	}
	
	public static function GetInstance(){ return self::Singleton(); }
	
	// @todo Is this really needed?...
	public static function _LoadFromDatabase(){
		if(!self::GetComponent()->load()){
			// Guess the core isn't installed.  If it's in development mode install it!
			if(DEVELOPMENT_MODE){
				self::GetComponent()->install();
				die('Installed core!  <a href="' . ROOT_WDIR . '">continue</a>');
			}
			else{
				die('There was a server error, please notify the administrator of this.');
			}
		}
		return;
		/*
		// Retrieve some information from the database when it becomes available.
		$q = @DB::Execute("SELECT `version` FROM `" . DB_PREFIX . "component` WHERE `name` = 'Core'");
		if(!$q) return;
		if($q->numRows() > 0){
			Core::Singleton()->versionDB = $q->fields['version'];
		}
		else{
			Core::Singleton()->versionDB = false;
		}
		 */
	}
	
	
	public static function IsInstalled(){
		return Core::Singleton()->_isInstalled();
	}
	
	public static function NeedsUpdated(){
		return Core::Singleton()->_needsUpdated();
	}
	
	public static function GetVersion(){
		return Core::GetComponent()->getVersionInstalled();
	}
	
	/**
	 * Resolve an asset to a fully-resolved URL.
	 * 
	 * @todo Add support for external assets.
	 * 
	 * @param string $asset 
	 * @return string The full url of the asset, including the http://...
	 */
	public static function ResolveAsset($asset){
		// Allow already-resolved links to be returned verbatim.
		if(strpos($asset, '://') !== false) return $asset;
		
		// Since an asset is just a file, I'll use the builtin file store system.
		// (although every file coming in should be assumed to be an asset, so
		//  allow for a partial path name to come in, assuming asset/).
		
		if(strpos($asset, 'assets/') !== 0) $asset = 'assets/' . $asset;
		
		// Maybe it's cached :)
		$keyname = 'asset-resolveurl';
		$cachevalue = self::Cache()->get($keyname, (3600 * 24));
		
		if(!$cachevalue) $cachevalue = array();
		
		if(!isset($cachevalue[$asset])){
			// Well, look it up!
			$f = self::File($asset);
			
			$cachevalue[$asset] = $f->getURL();
			// Save this for future lookups.
			self::Cache()->set($keyname, $cachevalue, (3600 * 24));
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
	public static function ResolveLink($url){
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
	 * Resolve filename to ... script.
	 * Useful for converting a physical filename to an accessable URL.
	 * @deprecated
	 */
	public static function ResolveFilenameTo($filename, $base = ROOT_URL){
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
	static public function Redirect($page){
		//This is NOT designed to refresh the current page.	If the pageto redirect to IS
		// this current page, simply do nothing.
		
		$page = self::ResolveLink($page);
		
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

	static public function Reload(){
		header('Location:' . CUR_CALL);
		die("If your browser does not refresh, please <a href=\"" . CUR_CALL . "\">Click Here</a>");
	}

	static public function GoBack(){
		CAEUtils::redirect(CAEUtils::GetNavigation());
	}

	/**
	 * If this is called from any page, the user is forced to redirect to the SSL version if available.
	 * @return void
	 */
	static public function RequireSSL(){
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
	 * @param int $amount
	 * @return string
	 */
	static public function GetNavigation($amount = 0){
		//var_dump($_SESSION); die();
		// NO nav history, guess I can't do much of anything...
		if(!isset($_SESSION['nav'])) return ROOT_URL;

		if($amount > 5 || $amount < 0) $amount = 1;
		$amount++;
		$amount = sizeof($_SESSION['nav']) - $amount;

		if(isset($_SESSION['nav'][$amount])) return $_SESSION['nav'][$amount];
		else return ROOT_URL;
	}

	/**
	 * Record this page into the navigation history.
	 *
	 * @param string $page
	 */
	static public function SetNavigation($page = CUR_CALL){
		//echo "Setting navRecord.";
		if(!isset($_SESSION['nav'])) $_SESSION['nav'] = array();

		// Do not record the same page twice.
		if(sizeof($_SESSION['nav']) != 0 && $_SESSION['nav'][sizeof($_SESSION['nav'])-1] == $page) return;

		if(sizeof($_SESSION['nav']) >= 5) array_shift($_SESSION['nav']);

		$_SESSION['nav'][] = $page;
	}

	/**
	 * Add a message to the user's stack.
	 *	It will be displayed the next time the user (or session) renders the page.
	 *
	 * @param string $message_text
	 * @param string $message_type
	 * @return boolean (on success)
	 */
	static public function SetMessage($messageText, $messageType = 'info'){

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

	 static public function AddMessage($messageText, $messageType = 'info'){
	 	 Core::SetMessage($messageText, $messageType);
	 }

	/**
	 * Retrieve the messages and optionally clear the message stack.
	 *
	 * @param unknown_type $return_type
	 * @return unknown
	 */
	static public function GetMessages($returnSorted = FALSE, $clearStack = TRUE){
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

	static public function SortByKey($named_recs, $order_by, $rev=false, $flags=0){
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
	 * @author Charlie Powell <powellc@powelltechs.com>
	 */
	static public function ImplodeKey($glue, &$array){
		$arrayKeys = array();
		foreach($array as $key => $value){
			$arrayKeys[] = $key;
		}
		return implode($glue, $arrayKeys);
	}

	
	/**
	 * Generate a random hex-deciman value of a given length.
	 *
	 * @param int $length
	 * @param bolean $casesensitive [false] Set to true to return a case-sensitive string.
	 *                              Otherwise the resulting string will simply be all uppercase.
	 * @return string
	 */
	static public function RandomHex($length = 1, $casesensitive = false){
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
	 * @param int $filesize Filesize in bytes
	 * @param int $round Precision to round to
	 * @return string 
	 */
	public static function FormatSize($filesize, $round = 2){
		$suf = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
		$c = 0;
		while($filesize >= 1024){
			$c++;
			$filesize = $filesize / 1024;
		}
		return (round($filesize, $round) . ' ' . $suf[$c]);
	}
	
	public static function GetExtensionFromString($str){
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
	public static function CheckEmailValidity($email){
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
	public static function _AttachCoreJavascript(){
		
		$script = '<script type="text/javascript">
	var Core = {
		ROOT_WDIR: "' . ROOT_WDIR . '",
		ROOT_URL: "' . ROOT_URL . '",
		ROOT_URL_SSL: "' . ROOT_URL_SSL . '",
		ROOT_URL_NOSSL: "' . ROOT_URL_NOSSL . '"
	};
</script>';
		
		CurrentPage::AddScript($script, 'head');
	}
	
}

// Listen for when the database becomes available.
//HookHandler::AttachToHook('db_ready', 'Core::_LoadFromDatabase');
