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
		
		if($request->ext == 'json'){
			// Get a list of all licensed features for this server ID,
			// or nothing at all if an invalid server ID was requested.
			$view->contenttype = View::CTYPE_JSON;
			if(!$serverid){
				$view->jsondata = ['status' => false, 'message' => 'Invalid server ID provided!'];
				return;
			}
		}
	}

	public function admin(){
		$request = $this->getPageRequest();
		$view = $this->getView();

		$manager = \Core\user()->checkAccess('p:/package_repository/licenses/manager');

		if(!$manager){
			return View::ERROR_ACCESSDENIED;
		}

		$generateform = new Form();
		$generateform->set('callsmethod', 'PackageRepositoryLicenseController::_GenerateLicenses');
		$generateform->addElement(
			'text',
			[
				'name' => 'qty',
				'title' => 'Number of Licenses',
			    'value' => 1,
			]
		);
		$generateform->addElement(
			'select',
			[
				'name' => 'duration',
			    'title' => 'Valid Duration',
			    'options' => [
				    '1 month' => '1 Month',
				    '3 month' => '3 Months',
			        '6 month' => '6 Months',
			        '12 month' => '1 Year',
			        '18 month' => '18 Month',
			        '24 month' => '2 Years',
			        '36 month' => '3 Years',
			        '5 year' => '5 Years',
			    ],
			    'value' => '12 month',
			]
		);
		$generateform->addElement('submit', ['value' => 'Generate License(s)']);

		$table = new \Core\ListingTable\Table();
		$table->setModelName('PackageRepositoryLicenseModel');
		$table->setDefaultSort('expires', 'ASC');
		$table->addColumn('License Key/Server ID', 'id');
		$table->addColumn('License Password', 'password');
		$table->addColumn('Comment', 'comment');
		$table->addColumn('Expiration Date', 'expires');
		$table->addColumn('IP Restriction', 'ip_restriction');

		$table->loadFiltersFromRequest($request);

		$view->title = 'Package Repository License Manager';
		$view->assign('generate_form', $generateform);
		$view->assign('listings', $table);

		$view->addControl('Manually Add License', '/packagerepositorylicense/add', 'add');
	}

	public function add(){
		$request = $this->getPageRequest();
		$view = $this->getView();

		$manager = \Core\user()->checkAccess('p:/package_repository/licenses/manager');

		if(!$manager){
			return View::ERROR_ACCESSDENIED;
		}

		$form = new Form();
		$form->set('callsmethod', 'PackageRepositoryLicenseController::_SaveLicense');
		$form->addModel(new PackageRepositoryLicenseModel());
		$form->addElement('submit', ['value' => 'Create License']);

		$view->title = 'Create License';
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
		$form->addElement('submit', ['value' => 'Update License']);

		$view->title = 'Edit License';
		$view->assign('form', $form);
	}

	public static function _GenerateLicenses(Form $form){
		$qty = $form->getElementValue('qty');
		if(!is_numeric($qty)){
			Core::SetMessage('Please set a valid quantity', 'error');
			return false;
		}

		if($qty < 1){
			Core::SetMessage('Please set a quantity greater than 0', 'error');
			return false;
		}

		if($qty > 999){
			Core::SetMessage('Quantity limited to 999', 'warning');
			$qty = 999;
		}

		$expires = new \Core\Date\DateTime();
		$expires->modify($form->getElementValue('duration'));
		$expires = $expires->format('Y-m-d');

		for($i = 0; $i < $qty; $i++){
			$license = new PackageRepositoryLicenseModel();
			$license->set('password', \Core\random_hex(rand(35, 49)));
			$license->set('expires', $expires);
			$license->save();
		}

		Core::SetMessage('Generated ' . $qty . ' license(s)!', 'success');
		return '/packagerepositorylicense/admin';
	}

	public static function _SaveLicense(Form $form) {
		try{
			$model = $form->getModel();
			$msg = $model->exists() ? 'Updated' : 'Created';
			$model->save();
		}
		catch(ModelValidationException $e){
			Core::SetMessage($e->getMessage(), 'error');
			return false;
		}
		catch(Exception $e){
			\Core\ErrorManagement\exception_handler($e);
			Core::SetMessage($e->getMessage(), 'error');
			return false;
		}

		Core::SetMessage($msg . ' license successfully!','success');
		return '/packagerepositorylicense/admin';
	}
}