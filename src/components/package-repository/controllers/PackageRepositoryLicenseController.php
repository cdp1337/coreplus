<?php
/**
 * Class file for the controller PackageRepositoryLicenseController
 *
 * @package Package Repository
 * @author Charlie Powell <charlie@evalagency.com>
 */
class PackageRepositoryLicenseController extends Controller_2_1 {
	
	public function index(){
		$request = $this->getPageRequest();
		$view    = $this->getView();
		
		$view->mode = View::MODE_NOOUTPUT;
		

		if($request->isPost() && $request->getPost('serverid')){
			$serverid = $request->getPost('serverid');
		}
		else{
			$serverid = null;
		}
		
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
		
		// Get a list of all licensed features for this server ID,
		// or nothing at all if an invalid server ID was requested.
		if(!$serverid){
			$view->error = View::ERROR_EXPECTATIONFAILED;
			$view->render();
			echo 'Invalid server ID provided!';
			return;
		}

		$key = ConfigHandler::Get('/package_repository/license_key');

		if($key){
			// Convert this key to the actual key.
			$gpg = new \Core\GPG\GPG();
			$key = $gpg->getKey($key);
		}
		
		if(!$key){
			// Not setup yet!
			$view->error = View::ERROR_SERVERERROR;
			$view->render();
			echo 'Licensing repository is not ready yet, please contact the package maintainer!';
			return;
		}

		$signedData = null;
		$valid = $licmod->isValid();
		if($valid == PackageRepositoryLicenseModel::VALID_VALID){
			// It's a valid license!
			$signedData = [
				'status' => true,
				'expires' => $licmod->get('expires'),
				'features' => $licmod->get('features'),
			];
		}
		elseif($valid == PackageRepositoryLicenseModel::VALID_EXPIRED){
			// Expired
			$signedData = [
				'status' => false,
				'message' => 'License expired on ' . \Core\Date\DateTime::FormatString($licmod->get('expires'), \Core\Date\DateTime::SHORTDATE)
			];
		}
		elseif($valid == PackageRepositoryLicenseModel::VALID_ACCESS){
			// Bad IP
			$signedData = [
				'status' => false,
				'message' => 'License not allowed for IP ' . REMOTE_IP
			];
		}
		elseif($valid == PackageRepositoryLicenseModel::VALID_PASSWORD){
			// Bad IP
			$signedData = [
				'status' => false,
				'message' => 'Bad password supplied!'
			];
		}
		else{
			$view->error = View::ERROR_EXPECTATIONFAILED;
			$view->render();
			echo 'Invalid server ID provided!';
			return;
		}

		$gpg = new \Core\GPG\GPG();
		$view->render();
		echo $gpg->signData(json_encode($signedData), $key);
		return;
	}

	public function admin(){
		$request = $this->getPageRequest();
		$view = $this->getView();

		$manager = \Core\user()->checkAccess('p:/package_repository/licenses/manager');

		if(!$manager){
			return View::ERROR_ACCESSDENIED;
		}

		$table = new \Core\ListingTable\Table();
		$table->setModelName('PackageRepositoryLicenseModel');
		$table->setDefaultSort('expires', 'ASC');
		$table->addColumn('License Key/Comment');
		$table->addColumn('License Password', 'password', false);
		$table->addColumn('IP Restriction', 'ip_restriction');
		$table->addColumn('Features');
		$table->addColumn('Last Checkin');

		$table->loadFiltersFromRequest($request);

		$view->title = 't:STRING_PACKAGE_REPOSITORY_LICENSE_MANAGER';
		$view->assign('listings', $table);

		$view->addControl('t:STRING_PACKAGE_REPOSITORY_LICENSE_FEATURE_MANAGER', '/packagerepositorylicense/features', 'cog');
	}
	
	public function features(){
		$request = $this->getPageRequest();
		$view = $this->getView();

		$manager = \Core\user()->checkAccess('p:/package_repository/licenses/manager');

		if(!$manager){
			return View::ERROR_ACCESSDENIED;
		}
		
		$key = ConfigHandler::Get('/package_repository/license_key');
		$gpg = new \Core\GPG\GPG();
		
		if($key){
			// Convert this key to the actual key.
			$key = $gpg->getKey($key);
		}
		
		if($key && $key->isValid()){
			$table = new \Core\ListingTable\Table();
			$table->setModelName('PackageRepositoryFeatureModel');
			$table->setDefaultSort('feature');
			$table->addColumn('Feature', 'feature');
			$table->addColumn('Type', 'type');
			$table->addColumn('Options');

			$table->loadFiltersFromRequest($request);

			$view->addControl([
				'title' => 't:STRING_PACKAGE_REPOSITORY_LICENSE_FEATURE_CREATE',
				'icon' => 'add',
				'href' => '/packagerepositorylicense/feature/create',
			]);
			$genForm = null;
		}
		else{
			$genForm = new Form();
			$genForm->set('callsmethod', 'PackageRepositoryLicenseController::LicenseKeySave');
			
			$privates = $gpg->listSecretKeys();
			if(sizeof($privates)){
				// There is at least one private key installed on the system already! :)
				$tab0 = new FormTabsGroup(['name' => 'tabsel', 'title' => 'Select Key']);
				$opts = ['' => '-- Select Existing Key --'];
				foreach($privates as $p){
					// I need to convert this to the public version so I can get the information attached!
					$pub = $gpg->getKey($p->fingerprint);
					
					$opts[$pub->fingerprint] = $pub->getName() . ' &lt;' . $pub->getEmail() . '&gt; (' . $pub->id_short . ')';
				}
				
				$tab0->addElement(
					'select',
					[
						'name' => 'sel',
						'title' => 'Select Existing Key',
						'options' => $opts,
					]
				);
				$tab0->addElement('submit', ['value' => 'Select Key']);
				$genForm->addElement($tab0);
			}
			
			$tab1 = new FormTabsGroup(['name' => 'tabgen', 'title' => 'Generate Key']);
			$tab2 = new FormTabsGroup(['name' => 'tabman', 'title' => 'Upload Key']);
			
			$tab1->addElement(
				'text',
				[
					'name' => 'name',
					'title' => 'Signing Name',
					'value' => SITENAME . ' License Key',
				]
			);

			$tab1->addElement(
				'text',
				[
					'name' => 'email',
					'title' => 'Signing/Support Email',
				]
			);
			
			$tab1->addElement('submit', ['value' => 'Generate Key']);
			
			$tab2->addElement(
				'textarea',
				[
					'name' => 'upload',
					'title' => 'Private Key',
				]
			);
			$tab2->addElement('submit', ['value' => 'Upload Key']);
			
			$genForm->addElement($tab1);
			$genForm->addElement($tab2);
			$table = null;
		}

		
		$view->addBreadcrumb('t:STRING_PACKAGE_REPOSITORY_LICENSE_MANAGER', '/packagerepositorylicense/admin');
		$view->title = 't:STRING_PACKAGE_REPOSITORY_LICENSE_FEATURE_MANAGER';
		$view->assign('listings', $table);
		$view->assign('key', $key);
		$view->assign('gen_form', $genForm);
	}

	public function feature_create() {
		$request = $this->getPageRequest();
		$view = $this->getView();

		$manager = \Core\user()->checkAccess('p:/package_repository/licenses/manager');

		if(!$manager){
			return View::ERROR_ACCESSDENIED;
		}
		
		$feature = new PackageRepositoryFeatureModel();
		$form = new Form();
		$form->set('callsmethod', 'PackageRepositoryLicenseController::FeatureSave');
		$form->addModel($feature);
		$form->addElement('submit', ['value' => t('STRING_PACKAGE_REPOSITORY_LICENSE_FEATURE_CREATE')]);

		$view->addBreadcrumb('t:STRING_PACKAGE_REPOSITORY_LICENSE_MANAGER', '/packagerepositorylicense/admin');
		$view->addBreadcrumb('t:STRING_PACKAGE_REPOSITORY_LICENSE_FEATURE_MANAGER', '/packagerepositorylicense/features');
		$view->title = 't:STRING_PACKAGE_REPOSITORY_LICENSE_FEATURE_CREATE';
		$view->assign('form', $form);
	}

	public function feature_update() {
		$request = $this->getPageRequest();
		$view = $this->getView();

		$manager = \Core\user()->checkAccess('p:/package_repository/licenses/manager');

		if(!$manager){
			return View::ERROR_ACCESSDENIED;
		}

		$fid = $request->getParameter(0);
		$feature = PackageRepositoryFeatureModel::Construct($fid);
		
		if(!$feature->exists()){
			return View::ERROR_NOTFOUND;
		}
		
		$form = new Form();
		$form->set('callsmethod', 'PackageRepositoryLicenseController::FeatureSave');
		$form->addModel($feature);
		$form->addElement('submit', ['value' => t('STRING_PACKAGE_REPOSITORY_LICENSE_FEATURE_UPDATE')]);

		$view->templatename = 'pages/packagerepositorylicense/feature_create.tpl';
		$view->addBreadcrumb('t:STRING_PACKAGE_REPOSITORY_LICENSE_MANAGER', '/packagerepositorylicense/admin');
		$view->addBreadcrumb('t:STRING_PACKAGE_REPOSITORY_LICENSE_FEATURE_MANAGER', '/packagerepositorylicense/features');
		$view->title = 't:STRING_PACKAGE_REPOSITORY_LICENSE_FEATURE_UPDATE';
		$view->assign('form', $form);
	}

	public function edit(){
		$request = $this->getPageRequest();
		$view = $this->getView();

		$manager = \Core\user()->checkAccess('p:/package_repository/licenses/manager');

		if(!$manager){
			return View::ERROR_ACCESSDENIED;
		}

		$model = PackageRepositoryLicenseModel::Construct($request->getParameter(0));
		if(!$model->exists()){
			return View::ERROR_NOTFOUND;
		}

		$form = new Form();
		$form->set('callsmethod', 'PackageRepositoryLicenseController::_SaveLicense');
		$form->addModel($model);
		
		$currentFeatures = $model->get('features');
		
		// Pull all the features registered on this system and add them!
		$features = PackageRepositoryFeatureModel::Find(null, null, 'feature');
		foreach($features as $f){
			/** @var PackageRepositoryFeatureModel $f */
			
			$key = $f->get('feature');
			$val = (isset($currentFeatures[$key])) ? $currentFeatures[$key] : null;
			
			switch($f->get('type')){
				case 'bool':
					$form->addElement(
						'select',
						[
							'title' => $f->get('feature'),
							'name' => 'feature[' . $key . ']',
							'options' => [
								'1' => 'Yes/Enabled',
								'0' => 'No/Disabled',
							],
							'value' => $val,
						]
					);
					break;
				case 'text':
					$form->addElement(
						'text',
						[
							'title' => $f->get('feature'),
							'name' => 'feature[' . $key . ']',
							'value' => $val,
						]
					);
					break;
				case 'enum':
					$form->addElement(
						'select',
						[
							'title' => $f->get('feature'),
							'name' => 'feature[' . $key . ']',
							'options' => $f->getOptionsAsArray(),
							'value' => $val,
						]
					);
					break;
				default:
					\Core\set_message('Unsupported feature type for ' . $key . ': ' . $f->get('type'), 'warning');
			}
		}
		
		$form->addElement('submit', ['value' => 'Update License']);

		$view->title = 'Edit License';
		$view->assign('form', $form);
	}
	

	public static function _SaveLicense(Form $form) {
		$model = $form->getModel();
		$msg = $model->exists() ? 'Updated' : 'Created';
		
		$features = $form->getElementsByName('feature\[.*\]');
		$f = [];
		foreach($features as $feat){
			$key = substr($feat->get('name'), 8, -1);
			
			$f[ $key ] = $feat->get('value');
		}
		$model->set('features', $f);
		
		$model->save();

		Core::SetMessage($msg . ' license successfully!','success');
		return '/packagerepositorylicense/admin';
	}

	public static function FeatureSave(Form $form) {
		$model = $form->getModel();
		$model->save();
		
		return '/packagerepositorylicense/features';
	}

	public static function LicenseKeySave(Form $form) {
		$name = $form->getElementValue('name');
		$email = $form->getElementValue('email');
		$upload = $form->getElementValue('upload');
		$select = $form->getElementValue('sel');
		
		$gpg = new \Core\GPG\GPG();
		
		if($upload){
			// Save this key into the keystore!
			$key = $gpg->importKey($upload);
			ConfigHandler::Set('/package_repository/license_key', $key->fingerprint);
			return true;
		}
		elseif($name && $email){
			// Generate a new one.
			$key = $gpg->generateKey($name, $email, '', 'RSA', 2048, '1y');
			ConfigHandler::Set('/package_repository/license_key', $key->fingerprint);
			return true;
		}
		elseif($select){
			ConfigHandler::Set('/package_repository/license_key', $select);
			return true;
		}
		else{
			\Core\set_message('Please either set the name AND email or upload a private key in ASCII format!', 'error');
			return false;
		}
	}
}