<?php
use Core\Filestore\Factory;

/**
 * Class file for the controller PackageRepositoryController
 *
 * @package Package Repository
 * @author Charlie Powell <charlie@eval.bz>
 */
class PackageRepositoryController extends Controller_2_1 {

	public function index(){
		$request = $this->getPageRequest();
		$view    = $this->getView();

		$error = $this->_checkPermissions('browse');

		if(!$error){
			// Only proceed with the application if no errors were thrown.
			if($request->ctype == 'application/xml'){

				$cached = \Core\Cache::Get('packagerepository-repo-xml');
				if(!$cached){
					$cached = $this->_getRepoXML();
					\Core\Cache::Set('packagerepository-repo-xml', $cached, (86400*14));
				}

				$view->mode = View::MODE_NOOUTPUT;
				$view->contenttype = 'application/xml';
				$view->render();
				echo $cached;
				return;
			}
			elseif($request->ext == 'xml.gz'){
				$cached = \Core\Cache::Get('packagerepository-repo-xml');
				if(!$cached){
					$cached = $this->_getRepoXML();
					\Core\Cache::Set('packagerepository-repo-xml', $cached, (86400*14));
				}

				$view->mode = View::MODE_NOOUTPUT;
				$view->contenttype = 'application/gzip';
				$view->render();

				echo gzencode($cached);
			}
		}
		else{
			$view->error = $error['status'];
		}

		$view->title = 'Package Repository';
		$view->assign('error', $error);
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
		// @todo Config options for repos
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
			return [
				'status' => View::ERROR_SERVERERROR,
				'message' => 'The package repository is not setup on this server.'
	        ];
		}

		$dir = Factory::Directory(\ConfigHandler::Get('/package_repository/base_directory'));

		if(!$dir->exists()){
			return [
				'status' => View::ERROR_SERVERERROR,
				'message' => $dir->getPath() . ' does not seem to exist!'
			];
		}
		elseif(!$dir->isReadable()){
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
					if($licvalid & PackageRepositoryLicenseModel::VALID_PASSWORD == PackageRepositoryLicenseModel::VALID_PASSWORD){
						$autherror = '[' . $user . '] Invalid license password';
						SystemLogModel::LogSecurityEvent('/packagerepository/password_failure', $autherror);
					}
					if($licvalid & PackageRepositoryLicenseModel::VALID_ACCESS == PackageRepositoryLicenseModel::VALID_ACCESS){
						$autherror = '[' . $user . '] IP address not authorized';
						SystemLogModel::LogSecurityEvent('/packagerepository/ip_restriction', $autherror);
					}

					if($licvalid & PackageRepositoryLicenseModel::VALID_EXPIRED == PackageRepositoryLicenseModel::VALID_EXPIRED){
						$autherror = '[' . $user . '] License provided has expired, please request a new one.';
						SystemLogModel::LogSecurityEvent('/packagerepository/expired_license', $autherror);
					}
					if($licvalid & PackageRepositoryLicenseModel::VALID_INVALID == PackageRepositoryLicenseModel::VALID_INVALID){
						$autherror = '[' . $user . '] License does not exist';
						SystemLogModel::LogSecurityEvent('/packagerepository/invalid_license', $autherror);
					}

					return [
						'status' => View::ERROR_ACCESSDENIED,
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
				$relpath = Core::ResolveLink('/packagerepository/download?file=' . substr($file->getFilename(), strlen($dir->getPath())));
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
			exec(
				'gpg --homedir "' . GPG_HOMEDIR . '" --verify "' . $fullpath . '" 2>&1 | grep "key ID" | sed \'s:.*key ID \([A-Z0-9]*\)$:\1:\'',
				$output,
				$ret
			);
			if($ret){
				trigger_error($fullpath . ' was not able to be verified as authentic, (probably because the GPG public key was not available)');
				$failedpackages++;
				continue;
			}
			$key = $output[0];

			if(!in_array($key, $keysfound)){
				$repo->addKey($key, null, null);
				$keysfound[] = $key;
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
			$package->getRootDOM()->setAttribute('key', $key);
			$package->setFileLocation($relpath);
			$repo->addPackage($package);
			$addedpackages++;

			// But I can still cleanup!
			unlink($tmpdir->getPath() . 'package.xml');
			unlink($tgz->getFilename());
		}

		return $repo->asPrettyXML();
	}
}