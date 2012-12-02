<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 8/13/12
 * Time: 9:42 PM
 * To change this template use File | Settings | File Templates.
 */

/**
 * Controller to handle all of the jQuery File Upload plugin serverside logic.
 * This is directly tied to the form system, so it cannot run without a valid MultiFileInput element being
 * rendered in a template prior to calling.
 */
class JQueryFileUploadController extends Controller_2_1 {
	/**
	 * The form element of the original source.
	 * Set by the index method.
	 *
	 * @var MultiFileFinput
	 */
	private $_formelement;

	/**
	 * Main controller interceptor for all multi upload methods with this utility.
	 */
	public function index(){
		$request = $this->getPageRequest();
		$view = $this->getView();

		// This page is ALWAYS json.
		$view->contenttype = View::CTYPE_JSON;
		$view->mode = View::MODE_AJAX;

		if(!(isset($_SESSION['multifileinputobjects']) && is_array($_SESSION['multifileinputobjects']))){
			return View::ERROR_BADREQUEST;
		}

		// Whatever the method, it should have a matching key that identifies which form it came from,
		// (since that form element has the metadata attached to it)
		// This can either be in the POST (for full uploads), or in the headers.
		$key = false;
		if(isset($_POST['key'])) $key = $_POST['key'];
		if(isset($_SERVER['HTTP_X_KEY'])) $key = $_SERVER['HTTP_X_KEY'];
		if(!$key){
			return View::ERROR_BADREQUEST;
		}


		// The key also must exist!
		if(!isset($_SESSION['multifileinputobjects'][$key])){
			return View::ERROR_BADREQUEST;
		}

		$this->_formelement = unserialize($_SESSION['multifileinputobjects'][$key]['obj']);

		if($request->method == View::METHOD_POST){

			// Damn browsers that don't support DELETE...
			if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
				$view->jsondata = $this->_doDelete();
			}

			// Standard POST upload
			elseif(sizeof($_FILES)){
				$view->jsondata = $this->_doPost();
			}

			// A streaming request
			elseif(isset($_SERVER['HTTP_X_FILE_NAME'])){
				$view->jsondata = $this->_doStream();
			}

			else{
				// NO COOKIE FOR YOU!
				return View::ERROR_BADREQUEST;
			}
		}
		else{
			// What, it's not a post even?
			return View::ERROR_BADREQUEST;
		}
	}

	/**
	 * Handle the upload as a series of binary streams.
	 * @return array
	 */
	private function _doStream(){
		// Read INPUT and write it directly to a temporary file based on the requested filename.

		$file = array(
			'name' => $_SERVER['HTTP_X_FILE_NAME'],
			'size' => 0,
			'remaining' => 0,
			'type' => null,
			'url' => '', // to be populated later
			'thumbnail_url' => '', // to be populated later
			'error' => '', // also may be populated later.
		);

		$finalsize = $_SERVER['HTTP_X_FILE_SIZE'];
		$incomingsize = $_SERVER['CONTENT_LENGTH'];
		// Just used to prevent multiple pageloads from appending to the same file should something happen.
		$datestamp = (isset($_SERVER['HTTP_X_UPLOAD_TIME'])) ? $_SERVER['HTTP_X_UPLOAD_TIME'] : 0;
		$tmpfile = TMP_DIR . md5($this->_formelement->get('key') . $file['name'] . $datestamp) . '.part.dat';

		// Record the filesize before and after so I can confirm I got all the data the client sent.
		if(file_exists($tmpfile)) $file['size'] = filesize($tmpfile);
		else $file['size'] = 0;

		file_put_contents($tmpfile, file_get_contents('php://input'), FILE_APPEND);

		// And update the size.
		clearstatcache();
		$newsize = filesize($tmpfile);

		if($newsize - $file['size'] != $incomingsize){
			$file['error'] = 'Did not receive all data, unable to process upload';
			unlink($tmpfile);
			return array($file);
		}

		$file['size'] = $newsize;
		$file['remaining'] = $finalsize - $file['size'];

		// Is the file upload complete?
		if($file['size'] == $finalsize){
			// Source
			$f = new File_local_backend($tmpfile);
			// Destination
			$nf = Core::File($this->_formelement->get('basedir') . $file['name']);
			$file['type'] = $f->getMimetype();

			// do NOT copy the contents over until the accept check has been ran!

			// Now that I have a file object, (in the temp filesystem still), I should validate the filetype
			// to see if the developer wanted a strict "accept" type to be requested.
			// If present, I'll have something to run through and see if the file matches.
			// I need the destination now because I need to full filename if an extension is requested in the accept.
			if($this->_formelement->get('accept')){
				$acceptcheck = \Core\check_file_mimetype($this->_formelement->get('accept'), $f->getMimetype(), $nf->getExtension());

				// Now that all the mimetypes have run through, I can see if one matched.
				if($acceptcheck != ''){
					$file['error'] = $acceptcheck;
					unlink($tmpfile);
					return array($file);
				}
			}

			// Now all the checks should be completed and I can safely copy the file away from the temporary filesystem.
			$f->copyTo($nf);
			unlink($tmpfile);

			// And now all the file's attributes will be visible.
			$file['name'] = $nf->getBaseFilename();
			$file['url'] = $nf->getURL();
			$file['thumbnail_url'] = $nf->getPreviewURL('50x50');
		}

		return array($file);
	}

	/**
	 * Handle the entire upload as a standard multitype POST
	 * @return array
	 */
	private function _doPost(){

		$info = array();
		if(sizeof($_FILES) == 1){
			// $upload will be the current index of FILES, which should contain all the uploaded files,
			// in an associative array as typical with FILES.
			$upload = current($_FILES);
		}

		if ($upload && is_array($upload['tmp_name'])) {
			// param_name is an array identifier like "files[]",
			// $_FILES is a multi-dimensional array:
			foreach ($upload['tmp_name'] as $index => $value) {
				// Source
				$f = new File_local_backend($upload['tmp_name'][$index]);
				// Destination
				$nf = Core::File($this->_formelement->get('basedir') . '/' . $upload['name'][$index]);

				// This is the object that is returned in the json array.
				// It needs to contain something.
				$file = array(
					'name' => '', // to be populated later
					'size' => $f->getFilesize(),
					'type' => $f->getMimetype(),
					'url' => '', // to be populated later
					'thumbnail_url' => '', // to be populated later
					'error' => '', // also may be populated later.
				);


				// do NOT copy the contents over until the accept check has been ran!

				// Now that I have a file object, (in the temp filesystem still), I should validate the filetype
				// to see if the developer wanted a strict "accept" type to be requested.
				// If present, I'll have something to run through and see if the file matches.
				// I need the destination now because I need to full filename if an extension is requested in the accept.
				if($this->_formelement->get('accept')){
					$acceptcheck = \Core\check_file_mimetype($this->_formelement->get('accept'), $f->getMimetype(), $nf->getExtension());

					// Now that all the mimetypes have run through, I can see if one matched.
					if($acceptcheck != ''){
						$file['error'] = $acceptcheck;
						$info[] = $file;
						continue; // skip to the next file upload.
					}
				}

				// Now all the checks should be completed and I can safely copy the file away from the temporary filesystem.
				$f->copyTo($nf);

				// And now all the file's attributes will be visible.
				$file['name'] = $nf->getBaseFilename();
				$file['url'] = $nf->getURL();
				$file['thumbnail_url'] = $nf->getPreviewURL('50x50');

				$info[] = $file;
			}
		} elseif ($upload || isset($_SERVER['HTTP_X_FILE_NAME'])) {
			// param_name is a single object identifier like "file",
			// $_FILES is a one-dimensional array:
			$info[] = $this->handle_file_upload(
				isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
				isset($_SERVER['HTTP_X_FILE_NAME']) ? $_SERVER['HTTP_X_FILE_NAME'] : (isset($upload['name']) ? $upload['name'] : null),
				isset($_SERVER['HTTP_X_FILE_SIZE']) ? $_SERVER['HTTP_X_FILE_SIZE'] : (isset($upload['size']) ? $upload['size'] : null),
				isset($_SERVER['HTTP_X_FILE_TYPE']) ? $_SERVER['HTTP_X_FILE_TYPE'] : (isset($upload['type']) ? $upload['type'] : null),
				isset($upload['error']) ? $upload['error'] : null
			);
		}

		return $info;
	}

	protected function handle_file_upload($uploaded_file, $name, $size, $type, $error, $index = null) {
		$file = new stdClass();
		$file->name = $this->trim_file_name($name, $type, $index);
		$file->size = intval($size);
		$file->type = $type;
		if ($this->validate($uploaded_file, $file, $error, $index)) {
			$this->handle_form_data($file, $index);
			$file_path = $this->options['upload_dir'].$file->name;
			$append_file = !$this->options['discard_aborted_uploads'] &&
				is_file($file_path) && $file->size > filesize($file_path);
			clearstatcache();
			if ($uploaded_file && is_uploaded_file($uploaded_file)) {
				// multipart/formdata uploads (POST method uploads)
				if ($append_file) {
					file_put_contents(
						$file_path,
						fopen($uploaded_file, 'r'),
						FILE_APPEND
					);
				} else {
					move_uploaded_file($uploaded_file, $file_path);
				}
			} else {
				// Non-multipart uploads (PUT method support)
				file_put_contents(
					$file_path,
					fopen('php://input', 'r'),
					$append_file ? FILE_APPEND : 0
				);
			}
			$file_size = filesize($file_path);
			if ($file_size === $file->size) {
				if ($this->options['orient_image']) {
					$this->orient_image($file_path);
				}
				$file->url = $this->options['upload_url'].rawurlencode($file->name);
				foreach($this->options['image_versions'] as $version => $options) {
					if ($this->create_scaled_image($file->name, $options)) {
						if ($this->options['upload_dir'] !== $options['upload_dir']) {
							$file->{$version.'_url'} = $options['upload_url']
								.rawurlencode($file->name);
						} else {
							clearstatcache();
							$file_size = filesize($file_path);
						}
					}
				}
			} else if ($this->options['discard_aborted_uploads']) {
				unlink($file_path);
				$file->error = 'abort';
			}
			$file->size = $file_size;
			$this->set_file_delete_url($file);
		}
		return $file;
	}
}
