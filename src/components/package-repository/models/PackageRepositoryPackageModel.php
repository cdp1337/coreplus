<?php

/**
 * Created by PhpStorm.
 * User: charlie
 * Date: 3/31/16
 * Time: 11:12 PM
 */
class PackageRepositoryPackageModel extends Model {

	/*
	<package type="theme" name="Base V3" version="2.1.1" key="B2BEDCCB">
		<packager version="5.0.1"/>
		<upgrade from="1.0.0" to="1.1.0"/>
		<upgrade from="1.1.0" to="1.1.1"/>
		<upgrade from="1.1.1" to="1.2.0"/>
		<upgrade from="1.2.0" to="1.2.1"/>
		<upgrade from="1.2.1" to="1.3.0"/>
		<upgrade from="1.3.0" to="2.0.0"/>
		<upgrade from="2.0.0" to="2.1.0"/>
		<upgrade from="2.1.0" to="2.1.1"/>
		<require name="core" type="component" version="2.5.7" operation="ge"/>
		<provide type="library" name="JSONjs" version="2015-02-25"/>
		<provide type="component" name="core" version="5.0.1"/>
		<location>
			http://localhost/~charlie/coreplus/packagerepository/download?file=themes/base-v3-2.1.1.tgz.asc
		</location>
		<description>
			The core application, including all libraries required for a base application.
		</description>
	</package>
	 */
	
	public static $Schema = [
		'id' => [
			'type' => Model::ATT_TYPE_UUID,
		],
		'type' => [
			'type' => Model::ATT_TYPE_STRING,
		],
		'key' => [
			'type' => Model::ATT_TYPE_STRING,
		],
		'version' => [
			'type' => Model::ATT_TYPE_STRING,
		],
		'name' => [
			'type' => Model::ATT_TYPE_STRING,
		],
		'gpg_key' => [
			'type' => Model::ATT_TYPE_STRING,
		],
		'packager' => [
			'type' => Model::ATT_TYPE_STRING,
		],
		'file' => [
			'type' => Model::ATT_TYPE_STRING,
		],
		'description' => [
			'type' => Model::ATT_TYPE_TEXT,
		],
		'changelog' => [
			'type' => Model::ATT_TYPE_TEXT,
		],
		'packager_name' => [
			'type' => Model::ATT_TYPE_STRING,
		],
		'packager_email' => [
			'type' => Model::ATT_TYPE_STRING,
		],
		'datetime_released' => [
			'type' => Model::ATT_TYPE_INT,
		],
		'requires' => [
			'type' => Model::ATT_TYPE_DATA,
		],
		'provides' => [
			'type' => Model::ATT_TYPE_DATA,
		],
		'screenshots' => [
			'type' => Model::ATT_TYPE_DATA,
		],
		'upgrades' => [
			'type' => Model::ATT_TYPE_DATA,
		],
	];
	
	public static $Indexes = [
		'primary' => 'id',
		'unique:type_key_version' => ['type', 'key', 'version'],
	];
	
	public function set($key, $value){
		if($key == 'requires' || $key == 'provides' || $key == 'screenshots' || $key == 'upgrades'){
			return parent::set($key, serialize($value));
		}
		else{
			return parent::set($key, $value);
		}
	}
	
	public function get($key){
		if($key == 'requires' || $key == 'provides' || $key == 'screenshots' || $key == 'upgrades'){
			return unserialize(parent::get($key));
		}
		else{
			return parent::get($key);
		}
	}
	
	public function getScreenshot(){
		$screens = $this->get('screenshots');
		if(!sizeof($screens)){
			return '';
		}
		
		// Return the first screenshot found.
		return $screens[0];
	}

	/**
	 * Get the repository XML as a string that can be returned to the browser or cached for future use.
	 *
	 * @param string|null $serverid      The server ID making the request, or null for anonymous.
	 * @param string|null $limitPackager Limit the packager returned to at least version X.Y.
	 * 
	 * @return RepoXML
	 */
	public static function GetAsRepoXML($serverid, $limitPackager) {
		$repo = new RepoXML();
		$repo->setDescription(ConfigHandler::Get('/package_repository/description'));
		$gpg          = new Core\GPG\GPG();
		$keysfound    = [];

		$where = [];
		if($limitPackager){
			$where[] = 'packager LIKE ' . $limitPackager . '%';
		}
		$packages = PackageRepositoryPackageModel::Find($where, null, 'type DESC, key ASC, version');
		
		foreach($packages as $pkg) {
			/** @var PackageRepositoryPackageModel $pkg */
			$package = new PackageXML(null);
			$package->setType($pkg->get('type'));
			$package->setName($pkg->get('name'));
			$package->setVersion($pkg->get('version'));
			$package->setPackager($pkg->get('packager'));
			$package->setDescription($pkg->get('description'));
			$package->setKey($pkg->get('gpg_key'));
			if(!in_array($pkg->get('gpg_key'), $keysfound)){
				$keysfound[] = $pkg->get('gpg_key');
			}

			$package->setFileLocation(\Core\resolve_link('/packagerepository/download?file=' . $pkg->get('file')));
			
			$upgrades = $pkg->get('requires');
			foreach($upgrades as $dat){
				$package->setRequire($dat['type'], $dat['name'], $dat['version'], $dat['operation']);
			}

			$upgrades = $pkg->get('provides');
			foreach($upgrades as $dat){
				$package->setProvide($dat['type'], $dat['name'], $dat['version']);
			}

			$upgrades = $pkg->get('upgrades');
			foreach($upgrades as $dat){
				$package->setUpgrade($dat['from'], $dat['to']);
			}

			$screens = $pkg->get('screenshots');
			foreach($screens as $dat){
				$f = \Core\Filestore\Factory::File($dat);
				$package->setScreenshot($f->getURL());
			}
			
			$package->setChangelog($pkg->get('changelog'));
			
			$repo->addPackage($package);
		}
		
		return $repo;
	}
	
	public static function RebuildPackages(){
		$dir = \Core\Filestore\Factory::Directory(\ConfigHandler::Get('/package_repository/base_directory'));

		$coredir      = $dir->getPath() . 'core/';
		$componentdir = $dir->getPath() . 'components/';
		$themedir     = $dir->getPath() . 'themes/';
		$tmpdir       = \Core\Filestore\Factory::Directory('tmp/exports/');
		$gpg          = new Core\GPG\GPG();
		$keysfound    = [];

		$addedpackages   = 0;
		$failedpackages  = 0;
		$skippedpackages = 0;

		$ls = $dir->ls('asc', true);

		\Core\CLI\CLI::PrintProgressBar(0);
		$totalPackages = sizeof($ls);
		$percentEach = 100 / $totalPackages;
		$currentPercent = 0;

		// Ensure that the necessary temp directory exists.
		$tmpdir->mkdir();

		foreach($ls as $file) {
			/** @var \Core\Filestore\File $file */

			$fullpath   = $file->getFilename();
			$relpath    = substr($file->getFilename(), strlen($dir->getPath()));
			$tmpdirpath = $tmpdir->getPath();

			// Drop the .asc extension.
			$basename = $file->getBasename(true);

			// Tarball of the temporary package
			$tgz = \Core\Filestore\Factory::File($tmpdirpath . $basename);

			$output = [];
			// I need to 1) retrieve and 2) verify the key for this package.
			try{
				$signature = $gpg->verifyFileSignature($fullpath);
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

			// Extract the package.xml metafile, this is critical!
			exec('tar -xzf "' . $tgz->getFilename() . '" -C "' . $tmpdirpath . '" ./package.xml', $output, $ret);
			if($ret) {
				trigger_error('Unable to extract package.xml from' . $tgz->getFilename());
				unlink($tmpdirpath . $basename);
				$failedpackages++;
				continue;
			}

			// Read in that package file and append it to the repo xml.
			$package = new PackageXML($tmpdirpath . 'package.xml');
			$package->getRootDOM()->setAttribute('key', $signature->keyID);
			$package->setFileLocation($relpath);

			// Core has a few differences than most components.
			if($package->getKeyName() == 'core'){
				$pkgName = 'Core Plus';
				$chngName = 'Core Plus';
				$type = 'core';
				$chngDepth = 3;
				$chngFile = './data/core/CHANGELOG';
				$xmlFile  = './data/core/component.xml';
			}
			else{
				$pkgName = $package->getName();
				$chngName = ($package->getType() == 'theme' ? 'Theme/' : '') . $package->getName();
				$type = $package->getType();
				$chngDepth = 2;
				$chngFile = './data/CHANGELOG';
				$xmlFile  = './data/' . ($package->getType() == 'theme' ? 'theme.xml' : 'component.xml');
			}

			// Lookup this package in the database or create if it doesn't exist.
			$model = PackageRepositoryPackageModel::Find(['type = ' . $package->getType(), 'key = ' . $package->getKeyName(), 'version = ' . $package->getVersion()], 1);
			if(!$model){
				$model = new PackageRepositoryPackageModel();
				$model->set('type', $type);
				$model->set('key', $package->getKeyName());
				$model->set('version', $package->getVersion());
			}

			// Set the data provided by the package.xml file.
			$model->set('name', $pkgName);
			$model->set('gpg_key', $package->getKey());
			$model->set('packager', $package->getPackager());
			$model->set('file', $relpath);
			$model->set('description', $package->getDescription());
			$model->set('requires', $package->getRequires());
			$model->set('provides', $package->getProvides());
			$model->set('upgrades', $package->getUpgrades());

			unlink($tmpdirpath . 'package.xml');


			// Extract out the CHANGELOG file, this is not so critical.
			// I need strip-components=2 to drop off the "." and "data" prefixes.
			exec('tar -xzf "' . $tgz->getFilename() . '" -C "' . $tmpdirpath . '" --strip-components=' . $chngDepth . ' ' . $chngFile, $output, $ret);

			// If there is a CHANGELOG, parse that too!
			if(file_exists($tmpdirpath . 'CHANGELOG')){
				try{
					$ch = new Core\Utilities\Changelog\Parser($chngName, $tmpdirpath . 'CHANGELOG');
					$ch->parse();

					// Get the version for this iteration.
					$chsec = $ch->getSection($model->get('version'));
					$model->set('packager_name', $chsec->getPackagerName());
					$model->set('packager_email', $chsec->getPackagerEmail());
					$model->set('datetime_released', $chsec->getReleasedDateUTC());
					$model->set('changelog', $chsec->fetchAsHTML(null));
				}
				catch(Exception $e){
					// meh, we just won't have a changelog.
				}
				finally{
					if(file_exists($tmpdirpath . 'CHANGELOG')){
						// Cleanup
						unlink($tmpdirpath . 'CHANGELOG');
					}
				}
			}

			// Retrieve out the screenshots from this component.
			exec('tar -xzf "' . $tgz->getFilename() . '" -O ' . $xmlFile . ' > "' . $tmpdirpath . 'comp.xml"', $output, $ret);
			if(file_exists($tmpdirpath . 'comp.xml')){
				try{
					$images = [];
					$c = new Component_2_1($tmpdirpath . 'comp.xml');
					$screens = $c->getScreenshots();

					if(sizeof($screens)){
						foreach($screens as $s){
							// Extract out this screen and save it to the filesystem.
							$archivedFile = dirname($xmlFile) . '/' . $s;
							$localFile = \Core\Filestore\Factory::File('public/packagerepo-screens/' . $model->get('type') . '-' . $model->get('key') . '-' . $model->get('version') . '/' . basename($s));

							// Write something into the file so that it exists on the filesystem.
							$localFile->putContents('');

							// And now tar can extract directly to that destination!
							exec('tar -xzf "' . $tgz->getFilename() . '" -O ' . $archivedFile . ' > "' . $localFile->getFilename() . '"', $output, $ret);
							if(!$ret) {
								// Return code should be 0 on a successful write.
								$images[] = $localFile->getFilename(false);
							}
						}
					}

					$model->set('screenshots', $images);
				}
				catch(Exception $e){
					// meh, we just won't have images..
				}
				finally{
					if(file_exists($tmpdirpath . 'comp.xml')){
						// Cleanup
						unlink($tmpdirpath . 'comp.xml');
					}
				}
			}
			
			if($model->changed()){
				$model->save(true);
				$addedpackages++;	
			}
			else{
				$skippedpackages++;
			}

			// But I can still cleanup!
			$tgz->delete();

			$currentPercent += $percentEach;
			\Core\CLI\CLI::PrintProgressBar($currentPercent);
		}

		// Commit everything!
		PackageRepositoryPackageModel::CommitSaves();
		
		return [
			'updated' => $addedpackages,
			'skipped' => $skippedpackages,
			'failed' => $failedpackages,
		];
	}
}