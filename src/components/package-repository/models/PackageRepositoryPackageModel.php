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
			'maxlength' => 32,
		],
		'key' => [
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
		],
		'version' => [
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 32,
		],
		'name' => [
			'type' => Model::ATT_TYPE_STRING,
		],
		'gpg_key' => [
			'type' => Model::ATT_TYPE_STRING,
		],
		'lic_key' => [
			'type' => Model::ATT_TYPE_STRING,
		],
		'logo' => [
			'type' => Model::ATT_TYPE_STRING,
		],
		'packager' => [
			'type' => Model::ATT_TYPE_STRING,
		],
		'file' => [
			'type' => Model::ATT_TYPE_STRING,
		],
		'md5' => [
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
			'encoding' => Model::ATT_ENCODING_JSON,
		],
		'provides' => [
			'type' => Model::ATT_TYPE_DATA,
			'encoding' => Model::ATT_ENCODING_JSON,
		],
		'screenshots' => [
			'type' => Model::ATT_TYPE_DATA,
			'encoding' => Model::ATT_ENCODING_JSON,
		],
		'upgrades' => [
			'type' => Model::ATT_TYPE_DATA,
			'encoding' => Model::ATT_ENCODING_JSON,
		],
		'enabled' => [
			'type' => Model::ATT_TYPE_BOOL,
			'default' => 0,
		],
	];
	
	public static $Indexes = [
		'primary' => 'id',
		'unique:type_key_version' => ['type', 'key', 'version'],
	];
	

	/**
	 * Get all the screenshots associated with this package.
	 * 
	 * Will return an array of Files.
	 * 
	 * @return array
	 */
	public function getScreenshots(){
		$screens = $this->get('screenshots');
		
		if(!sizeof($screens)){
			return [];
		}
		
		$ret = [];
		
		foreach($screens as $s){
			$ret[] = \Core\Filestore\Factory::File($s);
		}
		
		return $ret;
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
			
			if($pkg->get('lic_key') && !in_array($pkg->get('lic_key'), $keysfound)){
				$keysfound[] = $pkg->get('lic_key');
			}

			$package->setFileLocation(\Core\resolve_link('/packagerepository/download?file=' . $pkg->get('file')));
			
			$requires = $pkg->get('requires');
			if(is_array($requires)){
				foreach($requires as $dat){
					$package->setRequire($dat['type'], $dat['name'], $dat['version'], $dat['operation']);
				}
			}

			$provides = $pkg->get('provides');
			if(is_array($provides)){
				foreach($provides as $dat){
					$package->setProvide($dat['type'], $dat['name'], $dat['version']);
				}	
			}

			$upgrades = $pkg->get('upgrades');
			if(is_array($upgrades)){
				foreach($upgrades as $dat){
					$package->setUpgrade($dat['from'], $dat['to']);
				}
			}

			$screens = $pkg->get('screenshots');
			if(is_array($screens)){
				foreach($screens as $dat){
					$f = \Core\Filestore\Factory::File($dat);
					$package->setScreenshot($f->getURL());
				}	
			}
			
			$package->setChangelog($pkg->get('changelog'));
			
			$repo->addPackage($package);
		}

		$gpg          = new Core\GPG\GPG();
		foreach($keysfound as $k){
			$repo->addKey($gpg->getKey($k));
			//var_dump($key->getAscii()); die();
		}
		
		return $repo;
	}
	
	public static function RebuildPackages(){
		$dir = \Core\Filestore\Factory::Directory(\ConfigHandler::Get('/package_repository/base_directory'));
		
		if($dir->getPath() == '/'){
			trigger_error('Base directory is set to "/", this is probably not what you want!');
			return false;
		}
		$tmpdir       = \Core\Filestore\Factory::Directory('tmp/exports/');
		$gpg          = new Core\GPG\GPG();
		$keysfound    = [];

		$addedpackages   = 0;
		$failedpackages  = 0;
		$skippedpackages = 0;

		$ls = $dir->ls('asc', true);

		\Core\CLI\CLI::PrintHeader('Rebuilding Packages');
		\Core\CLI\CLI::PrintProgressBar(0);
		$totalPackages = sizeof($ls);
		$percentEach = '+' . (100 / $totalPackages);
		
		// Cache of models on the system currently to save on queries.
		$models = PackageRepositoryPackageModel::Find();
		$modelsByHash = [];
		$modelsByKeys = [];
		foreach($models as $m){
			/** @var PackageRepositoryPackageModel $m */
			$k = $m->get('type') . ':' . $m->get('key') . ':' . $m->get('version');
			$modelsByHash[ $m->get('md5') ] = $m;
			$modelsByKeys[ $k ] = $m;
		}
		
		$features = PackageRepositoryFeatureModel::Find();
		$featuresByKey = [];
		foreach($features as $f){
			/** @var PackageRepositoryFeatureModel $f */
			$featuresByKey[ $f->get('feature') ] = $f;
		}

		// Ensure that the necessary temp directory exists.
		$tmpdir->mkdir();

		foreach($ls as $file) {
			/** @var \Core\Filestore\File $file */

			$fullpath   = $file->getFilename();
			$relpath    = substr($file->getFilename(), strlen($dir->getPath()));
			$tmpdirpath = $tmpdir->getPath();
			$hash       = $file->getHash();
			
			\Core\CLI\CLI::PrintActionStart('Processing ' . $file->getBasename());
			
			// If this hash already exists, then it hasn't been updated!
			/*if(isset($modelsByHash[ $hash ])){
				Core\CLI\CLI::PrintLine('Skipping, not modified');
				\Core\CLI\CLI::PrintProgressBar($percentEach);
			}*/

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
				\Core\CLI\CLI::PrintActionStatus(false);
				\Core\CLI\CLI::PrintError($e->getMessage());
				$failedpackages++;
				\Core\CLI\CLI::PrintProgressBar($percentEach);
				continue;
			}
			
			if(!isset($keysfound[ $signature->keyID ])){
				// Look this key data up!
				$keysfound[ $signature->keyID ] = [
					'public' => $gpg->getKey($signature->keyID),
					'private' => $gpg->getSecretKey($signature->keyID)
				];
			}

			// decode and untar it in a temp directory to get the package.xml file.
			exec('gpg --homedir "' . GPG_HOMEDIR . '" -q -d "' . $fullpath . '" > "' . $tgz->getFilename() . '" 2>/dev/null', $output, $ret);
			if($ret) {
				\Core\CLI\CLI::PrintActionStatus(false);
				\Core\CLI\CLI::PrintError('Decryption of file ' . $fullpath . ' failed!');
				$failedpackages++;
				\Core\CLI\CLI::PrintProgressBar($percentEach);
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
			$key = $type . ':' . $package->getKeyName() . ':' . $package->getVersion();
			if(isset($modelsByKeys[ $key ])){
				$model = $modelsByKeys[ $key ];
			}
			else{
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
			$model->set('md5', $hash);
			$model->set('enabled', true);

			if(!$keysfound[ $signature->keyID ]['public']){
				trigger_error($fullpath . ' was not able to be verified as authentic, (probably because the GPG public key was not available)');
				$failedpackages++;
				$model->set('enabled', false);
			}
			if($keysfound[ $signature->keyID ]['private']){
				trigger_error('Refusing to enable package with the private key available as a security precaution!  (The server that distributes the files should not be able to modify the packages!)');
				$failedpackages++;
				$model->set('enabled', false);
			}

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
					Core\CLI\CLI::PrintWarning('Unable to read CHANGELOG for ' . $chngName);
					Core\CLI\CLI::PrintWarning($e->getMessage());
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
					
					
					$logo = $c->getXML()->getRootDOM()->getAttribute('logo');
					if($logo){
						// Extract out this logo and save it to the filesystem.
						$archivedFile = './data/' . $logo;
						$localFile = \Core\Filestore\Factory::File('public/packagerepo-logos/' . $model->get('type') . '-' . $model->get('key') . '-' . $model->get('version') . '/' . basename($logo));

						// Write something into the file so that it exists on the filesystem.
						$localFile->putContents('');

						// And now tar can extract directly to that destination!
						exec('tar -xzf "' . $tgz->getFilename() . '" -O ' . $archivedFile . ' > "' . $localFile->getFilename() . '"', $output, $ret);
						if(!$ret) {
							// Return code should be 0 on a successful write.
							$model->set('logo', $localFile->getFilename(false));
						}
						else{
							Core\CLI\CLI::PrintWarning('Unable to read logo file ' . $archivedFile . ' from ' . $chngName);
						}
					}
					
					
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
					Core\CLI\CLI::PrintWarning($e->getMessage());
					// meh, we just won't have images..
				}
				finally{
					if(file_exists($tmpdirpath . 'comp.xml')){
						// Cleanup
						unlink($tmpdirpath . 'comp.xml');
					}
				}
			}

			$archivedFile = dirname($xmlFile) . '/LICENSER.php';
			exec('tar -xzf "' . $tgz->getFilename() . '" -O ' . $archivedFile . ' > "' . $tmpdirpath . 'LICENSER.php"', $output, $ret);
			if(!$ret) {
				// Return code should be 0 on a successful write.
				$ret = include($tmpdirpath . 'LICENSER.php');
				// This should be an array of the licenser data, including the key!
				if(is_array($ret) && isset($ret['key']) && isset($ret['features'])){
					// Lookup this key and see if it's present.
					// Since the point of this will be to sign content for the features, we'll need the PRIVATE key here.
					if(!isset($keysfound[ $ret['key'] ])){
						// Look this key data up!
						$keysfound[ $ret['key'] ] = [
							'public' => $gpg->getKey($ret['key']),
							'private' => $gpg->getSecretKey($ret['key'])
						];
					}
					
					if(!$keysfound[ $ret['key'] ]['private']){
						\Core\CLI\CLI::PrintWarning('Private key ' . $ret['key'] . ' not available, please install that if you wish to make use of managing the licensed features.');
					}
					else{
						// Private key is available!  Install these features so they can be managed!
						// NOTE, the private key is NOT required if this repository will not be managing the features,
						// eg: mirroring someone else's packages.
						foreach($ret['features'] as $feature){
							if(!isset($featuresByKey[$feature])){
								// Create it!
								$f = new PackageRepositoryFeatureModel();
								$f->set('feature', $feature);
								$f->save();
								\Core\CLI\CLI::PrintLine('Registered Feature ' . $feature);
								
								$featuresByKey[$feature] = $f;
							}
						}
						
						$model->set('lic_key', $ret['key']);
					}
				}
				// @TODO
				unlink($tmpdirpath . 'LICENSER.php');
			}
			
			if($model->changed()){
				\Core\CLI\CLI::PrintActionStatus(true);
				$model->save(true);
				$addedpackages++;	
			}
			else{
				\Core\CLI\CLI::PrintActionStatus('skip');
				$skippedpackages++;
			}

			// But I can still cleanup!
			$tgz->delete();

			\Core\CLI\CLI::PrintProgressBar($percentEach);
		}

		// Commit everything!
		\Core\CLI\CLI::PrintLine('Committing Model Changes');
		PackageRepositoryPackageModel::CommitSaves();

		$msgs = [];
		if($addedpackages){
			$msgs[] = 'Updated ' . $addedpackages . ' packages.';
		}
		if($skippedpackages){
			$msgs[] = 'Skipped ' . $skippedpackages . ' packages.';
		}
		if($failedpackages){
			$msgs[] = 'Ignored ' . $failedpackages . ' corrupt packages.';
		}
		
		\Core\CLI\CLI::PrintLine((sizeof($msgs) > 0 ? implode('; ', $msgs) : 'No changes detected.'));
		
		return [
			'updated' => $addedpackages,
			'skipped' => $skippedpackages,
			'failed' => $failedpackages,
		];
	}
}