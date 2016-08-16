<?php
/**
 * Class file for the controller GPGKeyManagerController
 *
 * @package GPG Key Manager
 * @author Charlie Powell <charlie@evalagency.com>
 */
class GPGKeyManagerController extends Controller_2_1 {
	public function index(){
		$view = $this->getView();
		$request = $this->getPageRequest();
		
		if(!\Core\user()->checkAccess('g:admin')){
			return View::ERROR_ACCESSDENIED;
		}

		if(!is_writable(GPG_HOMEDIR)){
			\Core\set_message(GPG_HOMEDIR . ' is not writable!  Please ensure that ' . exec('whoami') . ' can write to it!', 'error');
			\Core\go_back();
		}
		
		// Load all the keys available on the local system for displaying to the administrator.
		$gpg = new \Core\GPG\GPG();
		
		$keys = $gpg->listKeys();
		$secrets = $gpg->listSecretKeys();
		
		// Combine these by the fingerprint, as it would make sense to display a public along with its corresponding private key.
		$combined = [];
		foreach($keys as $k){
			$combined[ $k->fingerprint ] = [
				'public' => $k,
				'private' => null,
			];
			
			//var_dump($k->uids[0]); die();
		}
		foreach($secrets as $k){
			// All private keys require the corresponding public key.
			$combined[ $k->fingerprint ]['private'] = $k;
		}
		
		$view->addControl('t:STRING_GPGKEYMANAGER_GENERATE_KEY', '/gpgkeymanager/generate', 'add');
		$view->addControl('t:STRING_GPGKEYMANAGER_UPLOAD_KEY', '/gpgkeymanager/upload', 'upload');
		$view->title = 't:STRING_GPGKEYMANAGER';
		$view->assign('keys', $combined);
	}
	
	public function deleteKey(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		if(!\Core\user()->checkAccess('g:admin')){
			return View::ERROR_ACCESSDENIED;
		}
		
		if(!$request->isPost()){
			return View::ERROR_BADREQUEST;
		}

		$key = $request->getParameter(0);
		$type = $request->getParameter('type');

		if(!preg_match('/^[A-Fa-f0-9]*$/', $key)){
			// Sanity check to ensure that they key only contains base16 characters. 
			return View::ERROR_BADREQUEST;
		}

		if(!$type){
			$type = 'public';
		}

		$gpg = new \Core\GPG\GPG();
		if($type == 'public' || $type == 'combined'){
			$k = $gpg->getKey($key);
		}
		elseif($type == 'private'){
			$k = $gpg->getSecretKey($key);
		}
		else{
			$k = null;
		}

		if(!$k){
			return View::ERROR_NOTFOUND;
		}
		
		if($type == 'public'){
			$gpg->deleteKey($k->fingerprint);
		}
		elseif($type == 'private'){
			$gpg->deleteSecretKey($k->fingerprint);
		}
		else{
			\Core\set_message('Invalid request.', 'error');
			\Core\go_back();
		}
		
		\Core\set_message('Deleted key successfully!', 'success');
		\Core\go_back();
	}
	
	public function getKey(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		if(!\Core\user()->checkAccess('g:admin')){
			return View::ERROR_ACCESSDENIED;
		}

		$key = $request->getParameter(0);
		$type = $request->getParameter('type');
		
		if(!preg_match('/^[A-Fa-f0-9]*$/', $key)){
			// Sanity check to ensure that they key only contains base16 characters. 
			return View::ERROR_BADREQUEST;
		}
		
		if(!$type){
			$type = 'public';
		}
		
		$gpg = new \Core\GPG\GPG();
		if($type == 'public' || $type == 'combined'){
			$k = $gpg->getKey($key);	
		}
		elseif($type == 'private'){
			$k = $gpg->getSecretKey($key);
		}
		else{
			$k = null;
		}
		
		if(!$k){
			return View::ERROR_NOTFOUND;
		}

		switch($type){
			case 'public':
				$file = $k->fingerprint . '.asc';
				break;
			case 'private':
				$file = $k->fingerprint . '.sec';
				break;
			case 'combined':
				$file = $k->fingerprint . '-COMBINED.asc';
				break;
			default:
				return View::ERROR_BADREQUEST;
		}
		
		$contents = $k->getAscii();
		if($type == 'combined'){
			// User requested a combined key, so append the private key for this one!
			$p = $gpg->getSecretKey($key);
			if($p){
				$contents .= "\n" . $p->getAscii();
			}
		}
		
		$view->mode = View::MODE_NOOUTPUT;
		if($request->getParameter('download')){
			$view->contenttype = View::CTYPE_PLAIN;
			$view->headers['Content-Disposition'] = 'attachment; filename="' . $file . '"';
			$view->headers['Cache-Control'] = 'no-cache, must-revalidate';
			$view->render();
			echo $contents;
		}
		else{
			$view->render();
			echo '<pre>' . $contents . '</pre>';
		}
	}
	
	public function generate(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		if(!\Core\user()->checkAccess('g:admin')){
			return View::ERROR_ACCESSDENIED;
		}
		
		$form = new Form();
		$form->set('callsmethod', 'GPGKeyManagerController::_GenerateSave');
		$form->addElement(
			'text',
			[
				'name' => 'name',
				'required' => true,
				'title' => t('STRING_GPGKEYMANAGER_FORM_GPG_KEY_NAME'),
				'description' => t('MESSAGE_GPGKEYMANAGER_FORM_GPG_KEY_NAME'),
			]
		);
		$form->addElement(
			'text',
			[
				'name' => 'email',
				'required' => true,
				'title' => t('STRING_GPGKEYMANAGER_FORM_GPG_KEY_EMAIL'),
				'description' => t('MESSAGE_GPGKEYMANAGER_FORM_GPG_KEY_EMAIL'),
			]
		);
		$form->addElement(
			'select',
			[
				'name' => 'enc',
				'required' => true,
				'title' => t('STRING_GPGKEYMANAGER_FORM_GPG_KEY_ENCRYPTION'),
				'description' => t('MESSAGE_GPGKEYMANAGER_FORM_GPG_KEY_ENCRYPTION'),
				'value' => '2048/RSA',
				'options' => [
					'1024/RSA' => 'RSA 1024 Key * WEAK *',
					'2048/RSA' => 'RSA 2048 Key * GOOD *',
					'3072/RSA' => 'RSA 3072 Key',
					'4096/RSA' => 'RSA 4096 Key * BEST *',
					'1024/DSA' => 'DSA 1024 Key',
					'1536/DSA' => 'DSA 1536 Key',
					'2048/DSA' => 'DSA 2048 Key',
					'3072/DSA' => 'DSA 3072 Key',
				]
			]
		);
		$form->addElement(
			'select',
			[
				'name' => 'expires',
				'required' => true,
				'title' => t('STRING_GPGKEYMANAGER_FORM_GPG_KEY_EXPIRES'),
				'description' => t('MESSAGE_GPGKEYMANAGER_FORM_GPG_KEY_EXPIRES'),
				'value' => '1y',
				'options' => [
					'1m'    => '1 Month',
					'6m'    => '6 Months',
					'1y'    => '1 Year',
					'2y'    => '2 Years',
					'3y'    => '3 Years',
					'5y'    => '5 Years',
					'10y'   => '10 Years',
					'never' => '- NEVER -'
				]
			]
		);
		$form->addElement(
			'textarea',
			[
				'name' => 'comment',
				'required' => false,
				'title' => t('STRING_GPGKEYMANAGER_FORM_GPG_KEY_COMMENT'),
				'description' => t('MESSAGE_GPGKEYMANAGER_FORM_GPG_KEY_COMMENT'),
			]
		);
		
		$form->addElement('submit', ['value' => 'Generate GPG Key']);
		
		$view->title = 't:STRING_GPGKEYMANAGER_GENERATE_KEY';
		$view->assign('form', $form);
	}

	public function upload(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		if(!\Core\user()->checkAccess('g:admin')){
			return View::ERROR_ACCESSDENIED;
		}

		$form = new Form();
		$form->set('callsmethod', 'GPGKeyManagerController::_UploadSave');
		
		$form->addElement(
			'textarea',
			[
				'name' => 'upload',
				'required' => true,
				'title' => t('STRING_GPGKEYMANAGER_FORM_GPG_KEY_UPLOAD'),
				'description' => t('MESSAGE_GPGKEYMANAGER_FORM_GPG_KEY_UPLOAD'),
			]
		);

		$form->addElement('submit', ['value' => 'Upload GPG Key']);

		$view->title = 't:STRING_GPGKEYMANAGER_UPLOAD_KEY';
		$view->assign('form', $form);
	}

	public static function _GenerateSave(Form $form) {
		$name = $form->getElementValue('name');
		$email = $form->getElementValue('email');
		$enc = $form->getElementValue('enc');
		$expires = $form->getElementValue('expires');
		$comment = $form->getElementValue('comment');

		$gpg = new \Core\GPG\GPG();
		
		$encParts = explode('/', $enc);
		
		// Generate a new one.
		$key = $gpg->generateKey($name, $email, $comment, $encParts[1], $encParts[0], $expires);
		\Core\set_message('Generated Key ' . $key->fingerprint . ' successfully!', 'success');
		return '/gpgkeymanager';
	}

	public static function _UploadSave(Form $form) {
		$upload = $form->getElementValue('upload');

		$gpg = new \Core\GPG\GPG();
		
		$key = $gpg->importKey($upload);
		
		\Core\set_message('Imported Key ' . $key->fingerprint . ' successfully!', 'success');
		return '/gpgkeymanager';
	}
}