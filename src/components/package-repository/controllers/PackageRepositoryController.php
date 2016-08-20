<?php
use Core\Filestore\Factory;

/**
 * Class file for the controller PackageRepositoryController
 *
 * @package Package Repository
 * @author Charlie Powell <charlie@evalagency.com>
 */
class PackageRepositoryController extends Controller_2_1 {

	/**
	 * @todo what should this view have?... 
	 * 
	 * @return int
	 */
	public function admin(){
		$request = $this->getPageRequest();
		$view    = $this->getView();
		
		if(!\Core\user()->checkAccess('g:admin')){
			return View::ERROR_ACCESSDENIED;
		}
	}
	
	public function rebuild(){
		$request = $this->getPageRequest();
		$view    = $this->getView();

		if(!\Core\user()->checkAccess('g:admin')){
			return View::ERROR_ACCESSDENIED;
		}
		
		if(!$request->isPost()){
			return View::ERROR_BADREQUEST;
		}

		$view->mode = View::MODE_NOOUTPUT;

		$changes = PackageRepositoryPackageModel::RebuildPackages();
		
		$msgs = [];
		if($changes['updated']){
			$msgs[] = 'Updated ' . $changes['updated'] . ' packages.';
		}
		if($changes['skipped']){
			$msgs[] = 'Skipped ' . $changes['skipped'] . ' packages.';
		}
		if($changes['failed']){
			$msgs[] = 'Ignored ' . $changes['failed'] . ' corrupt packages.';
		}
		
		echo implode('<br/>', $msgs);
		//\Core\set_message(implode(' ', $msgs), 'success');
		//\Core\go_back();
	}

	public function index(){
		$request = $this->getPageRequest();
		$view    = $this->getView();
		
		$isAdmin = \Core\user()->checkAccess('g:admin');
		
		$serverid = isset($_SERVER['HTTP_X_CORE_SERVER_ID']) ? $_SERVER['HTTP_X_CORE_SERVER_ID'] : null;
		// If the server ID is set, it should be a 32-digit character.
		// Anything else and omit.
		if(strlen($serverid) != 32){
			$serverid = null;
		}
		elseif(!preg_match('/^[A-Z0-9]*$/', $serverid)){
			// Invalid string.
			$serverid = null;
		}
		
		$ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

		if(strpos($ua, '(http://corepl.us)') !== false) {
			/** @var string $uav ex: "Core Plus 1.2.3" */
			$uav = str_replace(' (http://corepl.us)', '', $ua);
			/** @var string $version Just the version, ex: "1.2.3" */
			$version = str_replace('Core Plus ', '', $uav);

			// The set of logic to compare the current version of Core against the version connecting.
			// This is used primarily to set a class name onto the graphs so that they can be coloured specifically.
			$v = Core::VersionSplit($version);

			// These two values are used in the historical map, (as revision may be a bit useless at this scale).
			$briefVersion = $v['major'] . '.' . $v['minor'];
		}
		elseif($request->getParameter('packager')){
			$briefVersion = $request->getParameter('packager');
		}
		else{
			$briefVersion = null;
		}
		
		// Record this key as connected.
		if($serverid){
			$licmod = PackageRepositoryLicenseModel::Construct($serverid);
			$licmod->set('datetime_last_checkin', Core\Date\DateTime::NowGMT());
			$licmod->set('ip_last_checkin', REMOTE_IP);
			$licmod->set('referrer_last_checkin', isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');
			$licmod->set('useragent_last_checkin', isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
			$licmod->save();
		}

		if($request->ctype == 'application/xml'){
			// This is a repo.xml request, usually for debugging purposes, (as the app requests gz compressed versions).
			$xml = PackageRepositoryPackageModel::GetAsRepoXML($serverid, $briefVersion);
			$view->mode = View::MODE_NOOUTPUT;
			$view->contenttype = 'application/xml';
			$view->render();
			echo $xml->asXML();
			return;
		}
		elseif($request->ext == 'xml.gz'){
			// This is a normal from-client request; a compressed repo.xml structure.
			$xml = PackageRepositoryPackageModel::GetAsRepoXML($serverid, $briefVersion);
			$view->mode = View::MODE_NOOUTPUT;
			$view->contenttype = 'application/gzip';
			$view->render();
			$c = $xml->asXML();
			echo gzencode($c);
			return;
		}
		else{
			// This is a browse request.
			$packages = [];
			$where = [];
			if($briefVersion){
				$where[] = 'packager LIKE ' . $briefVersion . '%';
			}
			$allPackages = PackageRepositoryPackageModel::Find($where);
			
			foreach($allPackages as $pkg){
				/** @var PackageRepositoryPackageModel $pkg */
				
				$pkgKey = $pkg->get('type') . '-' . $pkg->get('key');
				$pkgPackager = $pkg->get('packager');
				$pkgVersion = $pkg->get('version');
				
				if(!isset($packages[$pkgKey])){
					$packages[$pkgKey] = [
						'package' => $pkg,
						'check'   => $pkgVersion,
						'min'     => $pkgPackager,
						'max'     => $pkgPackager,
					];
				}
				
				if(\Core\version_compare($pkgPackager, $packages[$pkgKey]['min'], 'lt')){
					// Record the lowest supported Core version for this package
					$packages[$pkgKey]['min'] = $pkgPackager;
				}
				if(\Core\version_compare($pkgPackager, $packages[$pkgKey]['max'], 'gt')){
					// Record the higest supported Core version for this package
					$packages[$pkgKey]['max'] = $pkgPackager;
				}
				if(\Core\version_compare($pkgVersion, $packages[$pkgKey]['check'], 'gt')){
					// Save the newest component as the reference package for all the display data.
					$packages[$pkgKey]['check'] = $pkgVersion;
					$packages[$pkgKey]['package'] = $pkg;
				}
				
			}
			
			
			
			// Build a list of all supported versions of Core in the database.
			$allCoreVersions = [];
			$packagerVersions = \Core\Datamodel\Dataset::Init()
				->unique(true)
				->select('packager')
				->table('package_repository_package')
				->order('packager')
				->executeAndGet();
			
			foreach($packagerVersions as $pkg){
				$pkgObject = new \Core\VersionString($pkg);
				$pkgBase = $pkgObject->major . '.' . $pkgObject->minor;
				
				if(!isset($allCoreVersions[$pkgBase])){
					$allCoreVersions[$pkgBase] = 'Core Plus ' . $pkgBase;
				}
			}
			krsort($allCoreVersions, SORT_NATURAL);
			$allCoreVersions = array_merge(['' => '-- All Versions --'], $allCoreVersions);
			
			$versionSelector = new Form();
			$versionSelector->set('method', 'get');
			$versionSelector->addElement('select', ['name' => 'packager', 'options' => $allCoreVersions, 'value' => $briefVersion]);
			$versionSelector->addElement('submit', ['value' => 'Filter']);
			
			$view->assign('version_selector', $versionSelector);
			$view->assign('packages', $packages);
			$view->assign('version_selected', $briefVersion);
		}

		$view->title = 'Package Repository';
		$view->templatename = 'pages/packagerepository/index.tpl';
		$view->assign('is_admin', $isAdmin);
	}

	/**
	 * View the details on a given package that is contained in this repo.
	 */
	public function details(){
		$request = $this->getPageRequest();
		$view    = $this->getView();
		
		$type = $request->getParameter('type');
		$key = $request->getParameter('key');
		
		// Lookup this package.
		
		$pkgs = PackageRepositoryPackageModel::Find(['type = ' . $type, 'key = ' . $key], null, 'datetime_released DESC');
		$latest = null;
		$latestVersion = null;
		
		foreach($pkgs as $p){
			// Determine the latest version.
			if($latestVersion === null || \Core\version_compare($p->get('version'), $latestVersion, 'gt')){
				$latest = $p;
				$latestVersion = $p->get('version');
			}
		}
		
		if(!$latest){
			return View::ERROR_NOTFOUND;
		}
		
		if(\Core\user()->checkAccess('g:admin')){
			// /~charlie/coreplus/licenser?component=core&version=6.0.3
			// Pull the page views for this file to provide stats on what versions are out in the wild.
			
			// This will be sorted by the incoming IP address.
			$agents = [];
			$views = UserActivityModel::FindRaw([
				'baseurl = /packagerepositorylicense', 
				'request LIKE %component=' . $key . '%', 
				'useragent LIKE Core Plus%'
			], null, 'datetime DESC');
			
			foreach($views as $rec){
				$v = preg_replace('/.*version=(.*)$/', '$1', $rec['request']);
				
				if(!isset($agents[ $rec['ip_addr'] ])){
					$agents[ $rec['ip_addr'] ] = [
						'site' => $rec['referrer'],
						'agent' => $rec['useragent'],
						'version' => $v,
						'datetime' => $rec['datetime'],
						'ip' => $rec['ip_addr'],
					];
				}
			}
		}
		else{
			$agents = null;
		}
		
		$view->addBreadcrumb('Package Repository', '/packagerepository');
		$view->title = $latest->get('name');
		$view->assign('latest', $latest);
		$view->assign('all', $pkgs);
		$view->assign('agents', $agents);
	}

	public function download(){
		$request = $this->getPageRequest();
		$view    = $this->getView();

		$error = $this->_checkPermissions('download');
		if($error){
			return View::ERROR_BADREQUEST;
		}

		if(!\ConfigHandler::Get('/package_repository/base_directory')){
			return View::ERROR_SERVERERROR;
		}

		$dir = Factory::Directory(\ConfigHandler::Get('/package_repository/base_directory'));

		if(!$dir->exists()){
			return View::ERROR_SERVERERROR;
		}
		elseif(!$dir->isReadable()){
			return View::ERROR_SERVERERROR;
		}

		if(!$request->getParameter('file')){
			return View::ERROR_BADREQUEST;
		}

		$file = $request->getParameter('file');
		if($file{0} == '/'){
			// A file shouldn't start with a slash.
			return View::ERROR_BADREQUEST;
		}
		if(strpos($file, '../') !== false){
			// This string shouldn't be present either!
			return View::ERROR_BADREQUEST;
		}

		$fileObject = Factory::File($dir->getPath() . $file);
		if(!$fileObject->exists()){
			return View::ERROR_NOTFOUND;
		}

		$fileObject->sendToUserAgent(true);
	}

	public function config(){
		// Admin-only page.
		if(!\Core\user()->checkAccess('g:admin')){
			return View::ERROR_ACCESSDENIED;
		}

		$view = $this->getView();

		$keys = [
				'/package_repository/base_directory',
				'/package_repository/is_private',
				'/package_repository/description',
				'/package_repository/auto_ip_restrict',
		];

		$form = new Form();
		$form->set('callsmethod', 'AdminController::_ConfigSubmit');

		foreach($keys as $k){
			$c = ConfigHandler::GetConfig($k);
			$f = $c->asFormElement();
			// Don't need them grouped
			$f->set('group', '');
			$form->addElement($f);
		}

		$form->addElement('submit', ['value' => 'Save Options']);

		$view->title = 'Package Repository Configuration';
		$view->mastertemplate = 'admin';
		$view->assign('form', $form);
	}
	
	public function analytics(){
		$request = $this->getPageRequest();
		$view = $this->getView();

		$manager = \Core\user()->checkAccess('p:/package_repository/view_analytics');

		if(!$manager){
			return View::ERROR_ACCESSDENIED;
		}
		
		// Retrieve a list of connections to this repo for both downloading and checks!
		$where = new \Core\Datamodel\DatasetWhereClause();
		$where->addWhereSub('OR', ['baseurl = /packagerepository', 'baseurl = /packagerepository/download']);
		
		// Default to a 3-month window for now just to have a relatively useful sample of data.
		// This will be expanded to include a filter at some point in time.
		$window = new \Core\Date\DateTime();
		$window->modify('-3 months');
		$window = $window->format('U');
		
		// Generate a boilerplate dataset for the all-history view.
		// This is required because the graphing software expects all data to be in the same columns,
		// and these columns are simply indexed arrays.
		// As we're pulling the data potentially out-of-order and across different versions,
		// this array will provide a consistent scaffold for unset versions.
		$allboilerplate = []; // Series Boilerplate
		$allmonths = [];      // Labels
		// This goes back 12 months.
		$date = new \Core\Date\DateTime();
		$date->modify('-11 months');
		for($i = 1; $i <= 12; $i++){
			$allboilerplate[ $date->format('Ym') ] = null;
			$allmonths[] = $date->format('M');
			$date->nextMonth();
		}
		
		$raw = UserActivityModel::FindRaw($where);
		
		// Will contain a list of useragents along with the count of how many access.
		$useragents     = [];
		// Will contain how many times a given IP has requested the site.
		// This is for a metric that currently is not enabled.
		$ipaddresses    = [];
		// A rich list of hosts along with the latest version, the IP connecting from, and the date of the last check.
		$hosts          = [];
		// All series for the bar graph at the top of the page, Keyed by version and contains the total number of hits.
		$allseries      = [];
		$allseries['Total'] = [
			'class'     => 'series-other',
			'name'      => 'Total',
			'title'     => 'Total',
			'useragent' => '',
			'values'    => $allboilerplate,
		];
		// Used so I can compare the version of the connecting useragent against the current version of Core.
		// This of course does noothing to ensure that this site is updated, but it at least should give some perspective.
		$currentVersion = Core::VersionSplit(Core::GetComponent('core')->getVersion());
		
		foreach($raw as $dat){
			if(strpos($dat['useragent'], '(http://corepl.us)') !== false){
				/** @var string $ua ex: "Core Plus 1.2.3" */
				$ua = str_replace(' (http://corepl.us)', '', $dat['useragent']);
				/** @var string $version Just the version, ex: "1.2.3" */
				$version = str_replace('Core Plus ', '', $ua);
				/** @var string $referrer Original Site/Server, ex: "http://corepl.us" */
				$referrer = $dat['referrer'] ? $dat['referrer'] : $dat['ip_addr'];
				
				// The set of logic to compare the current version of Core against the version connecting.
				// This is used primarily to set a class name onto the graphs so that they can be coloured specifically.
				$v = Core::VersionSplit($version);
				
				// These two values are used in the historical map, (as revision may be a bit useless at this scale).
				$briefVersion = $v['major'] . '.' . $v['minor'] . '.x';
				$briefUA      = 'Core Plus ' . $briefVersion;

				if($v['major'] == $currentVersion['major'] && $v['minor'] == $currentVersion['minor']){
					// Check is same version as current (or newer), blue!
					$class = 'series-current';
				}
				elseif($v['major'] + 2 <= $currentVersion['major']){
					// Check is at least 2 major versions out of date, red.
					$class = 'series-outdated-2';
				}
				elseif($v['major'] + 1 <= $currentVersion['major']){
					// Check is at least 1 major version out of date, green.
					$class = 'series-outdated-1';
				}
				else{
					// Same major version, close enough.
					$class = 'series-outdated-0';
				}
				
				$month = date('Ym', $dat['datetime']);
			}
			else{
				$ua           = 'Other';
				$briefUA      = 'Other';
				$version      = null;
				$briefVersion = null;
				$referrer     = null;
				$class        = 'series-other';
				$month        = null;
			}
			
			// All Data!
			if($month && array_key_exists($month, $allboilerplate)){
				if(!isset($allseries[$briefUA])){
					$allseries[$briefUA] = [
						'class'     => $class,
						'name'      => $briefVersion,
						'title'     => $briefUA,
						'useragent' => $briefUA,
					    'values'    => $allboilerplate,
					];
				}
				
				$allseries[$briefUA]['values'][$month]++;
				//$allseries['Total']['values'][$month]++;
			}
			
			// Is this data new enough to display on the graph?
			// This is required because the "all" graph at the top needs all-time, (or at least the past 12 months).
			if($dat['datetime'] >= $window){
				
				// USER AGENT DATA
				if(!isset($useragents[$ua])){
					$useragents[$ua] = [
						'value'     => 0,
						'class'     => $class,
						'name'      => $version,
						'title'     => $ua,
						'useragent' => $ua,
					];
				}
				$useragents[$ua]['value']++;

				// IP ADDRESS DATA
				if(!isset($ipaddresses[ $dat['ip_addr'] ])){
					$ipaddresses[ $dat['ip_addr'] ] = [
						'ip_addr' => $dat['ip_addr'],
						'count'   => 0,
					];
				}
				$ipaddresses[ $dat['ip_addr'] ]['count']++;
				
				// HOSTS DATA
				if($version && $referrer){
					$k = $referrer . '-' . $dat['ip_addr'];

					if(!isset($hosts[ $k ])){
						$hosts[ $k ] = [
							'servername' => $referrer,
							'ip_addr'    => $dat['ip_addr'],
							'version'    => '0.0',
							'datetime'   => 1,
						];
					}

					if(Core::VersionCompare($hosts[ $k ]['version'], $version, 'lt')){
						$hosts[ $k ]['version'] = $version;
					}
					if($hosts[ $k ]['datetime'] < $dat['datetime']){
						$hosts[ $k ]['datetime'] = $dat['datetime'];
					}
				}
			}
			
		}
		
		ksort($useragents);
		ksort($hosts, SORT_NATURAL);
		ksort($allseries, SORT_NATURAL);
		ksort($ipaddresses, SORT_NATURAL);
		
		// Update the title of the values now that the totals have been created.
		// Also, take this opportunity to set the chart data as necessary, (as its format will be slightly different).
		$chart = [
			'versions' => ['labels' => [], 'series' => []],
			'all'      => ['labels' => array_values($allmonths), 'series' => []], 
			'ips'      => [],
		];
		
		foreach($useragents as &$dat){
			$dat['title'] .= ' (' . $dat['value'] . ' Total Hits)';
			
			$chart['versions']['labels'][] = $dat['name'];
			$chart['versions']['series'][] = [
				'value'     => $dat['value'],
				'className' => $dat['class'],
				'name'      => $dat['name'],
				'title'     => $dat['title'],
			];
		}
		
		foreach($allseries as &$dat){
			$data = [];
			foreach($dat['values'] as $v){
				$data[] = [
					'value' => $v,
				    'title' => $dat['title'] . ' (' . $v . ' Monthly Hits)',
				];
				//$data[] = $v;
			}
			$chart['all']['series'][] = [
				'data'      => $data,
				'className' => $dat['class'],
				'name'      => $dat['name'],
				'title'     => $dat['title'],
			];
		}
		
		// Convert these to JSON data!
		$chart['versions'] = json_encode($chart['versions']);
		$chart['all'] = json_encode($chart['all']);
		//$chart['ips'] = json_encode($chart['ips']);
		
		$view->title = 't:STRING_PACKAGE_REPOSITORY_ANALYTICS';
		$view->assign('chart', $chart);
		$view->assign('raw', $raw);
		$view->assign('ip_addresses', $ipaddresses);
		$view->assign('useragents', $useragents);
		$view->assign('hosts', $hosts);
		
		
		//var_dump($chart, $useragents, $ipaddresses, $ipversion, $raw); die();
	}

	/**
	 * Check permissions on the user and system and return either blank or a string containing the error.
	 *
	 * @param string $step
	 *
	 * @return array|null
	 */
	private function _checkPermissions($step){
		$error   = null;

		if(!\ConfigHandler::Get('/package_repository/base_directory')){
			// Check if the config is even set, can't proceed if it's not.
			trigger_error('The package repository does not appear to be setup yet.  Please browse to Configuration and the appropriate options.');

			return [
				'status' => View::ERROR_SERVERERROR,
				'message' => 'The package repository is not setup on this server.'
	        ];
		}

		$dir = Factory::Directory(\ConfigHandler::Get('/package_repository/base_directory'));

		if(!$dir->exists()){
			trigger_error($dir->getPath() . ' does not appear to exist!  Unable to browse repo.xml without it.');

			return [
				'status' => View::ERROR_SERVERERROR,
				'message' => $dir->getPath() . ' does not seem to exist!'
			];
		}
		elseif(!$dir->isReadable()){
			trigger_error($dir->getPath() . ' does not appear to be readable!  Unable to browse repo.xml without it.');

			return [
				'status' => View::ERROR_SERVERERROR,
				'message' => $dir->getPath() . ' does not seem to be readable!'
			];
		}

		if(ConfigHandler::Get('/package_repository/is_private')){
			// Lookup this license key, (or request one if not present).
			$valid = false;
			$autherror = 'Access to ' . SITENAME . ' (Package Repository) requires a license key and password.';

			if(isset($_SERVER['PHP_AUTH_PW']) && isset($_SERVER['PHP_AUTH_USER'])){
				$user = $_SERVER['PHP_AUTH_USER'];
				$pw = $_SERVER['PHP_AUTH_PW'];
			}
			else{
				$user = $pw = null;
			}

			if($user && $pw){
				/** @var PackageRepositoryLicenseModel $license */
				$license = PackageRepositoryLicenseModel::Construct($user);

				$licvalid = $license->isValid($pw);
				if($licvalid == 0){

					// Lock this license to the remote IP, if requested by the admin.
					if(ConfigHandler::Get('/package_repository/auto_ip_restrict') && !$license->get('ip_restriction')){
						$license->set('ip_restriction', REMOTE_IP);
						$license->save();
					}

					SystemLogModel::LogInfoEvent('/packagerepository/' . $step, '[' . $user . '] accessed repository successfully');
					return null;
				}
				else{
					if(($licvalid & PackageRepositoryLicenseModel::VALID_PASSWORD) == PackageRepositoryLicenseModel::VALID_PASSWORD){
						$autherror = '[' . $user . '] Invalid license password';
						$status = View::ERROR_ACCESSDENIED;
						SystemLogModel::LogSecurityEvent('/packagerepository/password_failure', $autherror);
					}
					if(($licvalid & PackageRepositoryLicenseModel::VALID_ACCESS) == PackageRepositoryLicenseModel::VALID_ACCESS){
						$autherror = '[' . $user . '] IP address not authorized';
						$status = View::ERROR_ACCESSDENIED;
						SystemLogModel::LogSecurityEvent('/packagerepository/ip_restriction', $autherror);
					}

					if(($licvalid & PackageRepositoryLicenseModel::VALID_EXPIRED) == PackageRepositoryLicenseModel::VALID_EXPIRED){
						$autherror = '[' . $user . '] License provided has expired, please request a new one.';
						$status = View::ERROR_GONE;
						SystemLogModel::LogSecurityEvent('/packagerepository/expired_license', $autherror);
					}
					if(($licvalid & PackageRepositoryLicenseModel::VALID_INVALID) == PackageRepositoryLicenseModel::VALID_INVALID){
						$autherror = '[' . $user . '] License does not exist';
						$status = View::ERROR_EXPECTATIONFAILED;
						SystemLogModel::LogSecurityEvent('/packagerepository/invalid_license', $autherror);
					}

					return [
						'status' => $status,
						'message' => $autherror
			        ];
				}
			}

			if(!$valid){
				header('WWW-Authenticate: Basic realm="' . SITENAME . ' (Package Repository)"');
				header('HTTP/1.0 401 Unauthorized');
				echo $autherror;
				exit;
			}
		}
		else{
			SystemLogModel::LogInfoEvent('/packagerepository/' . $step, '[anonymous connection] accessed repository successfully');
			return null;
		}
	}

	/**
	 * Get the repository XML as a string that can be returned to the browser or cached for future use.
	 *
	 * @return string
	 */
	private function _getRepoXML() {
		$repo = new RepoXML();
		$repo->setDescription(ConfigHandler::Get('/package_repository/description'));

		$dir = Factory::Directory(\ConfigHandler::Get('/package_repository/base_directory'));

		$coredir      = $dir->getPath() . 'core/';
		$componentdir = $dir->getPath() . 'components/';
		$themedir     = $dir->getPath() . 'themes/';
		$tmpdir       = Factory::Directory('tmp/exports/');
		$gpg          = new Core\GPG\GPG();
		$keysfound    = [];

		$private = (ConfigHandler::Get('/package_repository/is_private') || (strpos($dir->getPath(), ROOT_PDIR) !== 0));

		$addedpackages  = 0;
		$failedpackages = 0;

		$iterator = new \Core\Filestore\DirectoryIterator($dir);
		// Only find signed packages.
		$iterator->findExtensions = ['asc'];
		// Recurse into sub directories
		$iterator->recursive = true;
		// No directories
		$iterator->findDirectories = false;
		// Just files
		$iterator->findFiles = true;
		// And sort them by their filename to make things easy.
		$iterator->sortBy('filename');

		// Ensure that the necessary temp directory exists.
		$tmpdir->mkdir();

		foreach($iterator as $file) {
			/** @var \Core\Filestore\File $file */

			$fullpath = $file->getFilename();
			// Used in the XML file.
			if($private){
				$relpath = \Core\resolve_link('/packagerepository/download?file=' . substr($file->getFilename(), strlen($dir->getPath())));
			}
			else{
				$relpath = $file->getFilename(ROOT_PDIR);
			}

			// Drop the .asc extension.
			$basename = $file->getBasename(true);

			// Tarball of the temporary package
			$tgz = Factory::File($tmpdir->getPath() . $basename);

			$output = [];
			// I need to 1) retrieve and 2) verify the key for this package.
			try{
				$signature = $gpg->verifyFileSignature($fullpath);
				if(!in_array($signature->keyID, $keysfound)){
					$repo->addKey($signature->keyID, null, null);
					$keysfound[] = $signature->keyID;
				}
			}
			catch(\Exception $e){
				trigger_error($fullpath . ' was not able to be verified as authentic, (probably because the GPG public key was not available)');
				$failedpackages++;
				continue;
			}

			// decode and untar it in a temp directory to get the package.xml file.
			exec('gpg --homedir "' . GPG_HOMEDIR . '" -q -d "' . $fullpath . '" > "' . $tgz->getFilename() . '" 2>/dev/null', $output, $ret);
			if($ret) {
				trigger_error('Decryption of file ' . $fullpath . ' failed!');
				$failedpackages++;
				continue;
			}

			exec('tar -xzf "' . $tgz->getFilename() . '" -C "' . $tmpdir->getPath() . '" ./package.xml', $output, $ret);
			if($ret) {
				trigger_error('Unable to extract package.xml from' . $tgz->getFilename());
				unlink($tmpdir->getPath() . $basename);
				$failedpackages++;
				continue;
			}



			// Read in that package file and append it to the repo xml.
			$package = new PackageXML($tmpdir->getPath() . 'package.xml');
			$package->getRootDOM()->setAttribute('key', $signature->keyID);
			$package->setFileLocation($relpath);
			$repo->addPackage($package);
			$addedpackages++;

			// But I can still cleanup!
			unlink($tmpdir->getPath() . 'package.xml');
			$tgz->delete();
		}

		return $repo->asPrettyXML();
	}
}