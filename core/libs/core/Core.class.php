<?php

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
	
	public static function Singleton(){
		if(is_null(self::$instance)) self::$instance = new self();
		return self::$instance;
	}
	
	public static function GetInstance(){ return self::Singleton(); }
	
	
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
	
	private function _isInstalled(){
		//var_dump($this->_componentobj, $this->_componentobj->getVersion()); die();
		return ($this->_componentobj->getVersionInstalled() === false)? false : true;
	}
	
	private function _needsUpdated(){
		return ($this->_componentobj->getVersionInstalled() != $this->_componentobj->getVersion());
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
		$t = ConfigHandler::GetValue('/core/theme');
		
		if(file_exists(ROOT_PDIR . '/assets/' . $t . '/' . $asset)) return ROOT_URL . 'assets/' . $t . '/' . $asset;
		else return ROOT_URL . 'assets/default/' . $asset;
	}
	
	/**
	 * Resolve a url or application path to a fully-resolved URL.
	 * 
	 * @param string $url 
	 * @return string The full url of the link, including the http://...
	 */
	public static function ResolveLink($url){
		$a = PageModel::SplitBaseURL($url);
		//var_dump($a);
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
		//var_dump($page);
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
	 * @return string
	 */
	static public function RandomHex($length = 1){
		$output = '';
		for($x=1;$x<=$length;$x++){
			$output .= strtoupper(dechex(rand(0, 15)));
		}
		return $output;
	}
	
}

// Listen for when the database becomes available.
//HookHandler::AttachToHook('db_ready', 'Core::_LoadFromDatabase');
