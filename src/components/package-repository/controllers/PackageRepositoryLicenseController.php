<?php
/**
 * Class file for the controller PackageRepositoryLicenseController
 *
 * @package Package Repository
 * @author Charlie Powell <charlie@eval.bz>
 */
class PackageRepositoryLicenseController extends Controller_2_1 {

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
		$table->addColumn('License Key', 'id');
		$table->addColumn('License Password', 'password');
		$table->addColumn('Comment', 'comment');
		$table->addColumn('Expiration Date', 'expires');
		$table->addColumn('IP Restriction', 'ip_restriction');

		$table->loadFiltersFromRequest($request);

		$view->title = 'Package Repository License Manager';
		$view->assign('generate_form', $generateform);
		$view->assign('listings', $table);
	}

	public function add(){
		$request = $this->getPageRequest();
		$view = $this->getView();

		$manager = \Core\user()->checkAccess('p:/package_repository/licenses/manager');

		if(!$manager){
			return View::ERROR_ACCESSDENIED;
		}

		$form = new Form();
		$form->addModel(new PackageRepositoryLicenseModel());
		$form->addElement('submit', ['value' => 'Create License']);

		$view->title = 'Create License';
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
}