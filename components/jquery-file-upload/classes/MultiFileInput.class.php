<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 7/6/12
 * Time: 5:12 PM
 * To change this template use File | Settings | File Templates.
 */

class MultiFileInput extends FormElement {
	public function __construct($atts = null) {
		// Some defaults
		$this->_attributes      = array(
			'class'             => 'formelement multifileinput',
			'previewdimensions' => '200x100',
			'browsable'         => false,
			'basedir'           => '',
		);
		$this->_validattributes = array();
		$this->requiresupload   = true;

		parent::__construct($atts);
	}

	public function render() {

		if (!$this->get('basedir')) {
			throw new Exception('MultiFileInput cannot be rendered without a basedir attribute!');
		}

		//var_dump($_SESSION['multifileinputobjects'], serialize($this->_attributes)); die();
		// This is a slightly different element than the traditional form system, as it must be able to be called without
		// the rest of the form system on submit.
		// This is because this system will do an ajax submit to do the actual upload.
		if(!(isset($_SESSION['multifileinputobjects']) && is_array($_SESSION['multifileinputobjects']))){
			$_SESSION['multifileinputobjects'] = array();
		}

		// I don't need this key to be cryptographically secure, just generally unique.
		$key = md5(serialize($this->_attributes));
		foreach($_SESSION['multifileinputobjects'] as $obj){
			if(!isset($obj['key'])) continue;
			if($obj['key'] == $key){
				$this->set('id', $obj['id']);
			}
		}

		if (!isset($this->_attributes['id'])) {
			// This system requires a valid id.
			$this->set('id', 'multifileinput-' . Core::RandomHex('2'));
		}

		$this->set('key', $key);
		$this->set('uploadkey', $key);

		// Convert the string representation of a filesize to the raw bytes.
		$size = strtoupper(str_replace(' ', '', ini_get('upload_max_filesize')));
		if(strpos($size, 'G') !== false){
			$size = preg_replace('/[^0-9]/', '', $size);
			$size = $size * (1024*1024*1024);
		}
		elseif(strpos($size, 'M') !== false){
			$size = preg_replace('/[^0-9]/', '', $size);
			$size = $size * (1024*1024);
		}
		elseif(strpos($size, 'K') !== false){
			$size = preg_replace('/[^0-9]/', '', $size);
			$size = $size * (1024);
		}
		$this->set('maxsize', $size);

		$_SESSION['multifileinputobjects'][$key] = array(
			//'expire' => (Time::GetCurrent() + 3600),
			'obj' => serialize($this),
			'key' => $key,
			'id' => $this->_attributes['id'],
		);

		return parent::render();
	}

	/**
	 * Get the respective File object for this element.
	 * Use the Core system to ensure compatibility with CDNs.
	 *
	 * @return File_Backend
	 */
	public function getFile() {
		if ($this->get('value')) {
			$f = Core::File($this->get('basedir') . '/' . $this->get('value'));
		}
		else {
			$f = Core::File();
		}
		return $f;
	}

	public function setValue($value) {
		if ($this->get('required') && !$value) {
			$this->_error = $this->get('label') . ' is required.';
			return false;
		}

		if ($value == '_upload_') {
			$n = $this->get('name');

			// Because PHP will have different sources depending if the name has [] in it...
			if (strpos($n, '[') !== false) {
				$p1 = substr($n, 0, strpos($n, '['));
				$p2 = substr($n, strpos($n, '[') + 1, -1);

				if (!isset($_FILES[$p1])) {
					$this->_error = 'No file uploaded for ' . $this->get('label');
					return false;
				}

				$in = array(
					'name'     => $_FILES[$p1]['name'][$p2],
					'type'     => $_FILES[$p1]['type'][$p2],
					'tmp_name' => $_FILES[$p1]['tmp_name'][$p2],
					'error'    => $_FILES[$p1]['error'][$p2],
					'size'     => $_FILES[$p1]['size'][$p2],
				);
			}
			else {
				$in =& $_FILES[$n];
			}


			if (!isset($in)) {
				$this->_error = 'No file uploaded for ' . $this->get('label');
				return false;
			}
			else {
				switch ($in['error']) {
					case UPLOAD_ERR_OK:
						// Don't do anything, just avoid the default.
						break;
					case UPLOAD_ERR_INI_SIZE:
						if(DEVELOPMENT_MODE){
							$this->_error = 'The uploaded file exceeds the upload_max_filesize directive in php.ini [' . ini_get('upload_max_filesize') . ']';
						}
						else{
							$this->_error = 'The uploaded file is too large, maximum size is ' . ini_get('upload_max_filesize');
						}

						return false;
					case UPLOAD_ERR_FORM_SIZE:
						if(DEVELOPMENT_MODE){
							$this->_error = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form. ';
						}
						else{
							$this->_error = 'The uploaded file is too large.';
						}


						return false;
					default:
						$this->_error = 'An error occured while trying to upload the file for ' . $this->get('label');
						return false;
				}

				// Source
				$f = new File_local_backend($in['tmp_name']);
				// Destination
				$nf = Core::File($this->get('basedir') . '/' . $in['name']);

				// do NOT copy the contents over until the accept check has been ran!

				// Now that I have a file object, (in the temp filesystem still), I should validate the filetype
				// to see if the developer wanted a strict "accept" type to be requested.
				// If present, I'll have something to run through and see if the file matches.
				// I need the destination now because I need to full filename if an extension is requested in the accept.
				if($this->get('accept')){
					$filemime   = $f->getMimetype();
					$acceptgood = false;
					$accepts = array_map(
						'trim',
						explode(
							',',
							strtolower($this->get('accept'))
						)
					);
					foreach($accepts as $accepttype){
						// '*' is the wildcard to accept any filetype....
						// why would this even be set?!?
						if($accepttype == '*'){
							$acceptgood = true;
							break;
						}
						// accepts that are standard full mimetypes are also pretty easy.
						elseif(preg_match('#^[a-z\-\+]+/[0-9a-z\-\+\.]+#', $accepttype)){
							if($accepttype == $filemime){
								$acceptgood = true;
								break;
							}
						}
						// wildcard mimetypes are allowed too.
						elseif(preg_match('#^[a-z\-\+]+/\*#', $accepttype)){
							if(strpos($filemime, substr($accepttype, 0, -1)) === 0){
								$acceptgood = true;
								break;
							}
						}
						// extensions are allowed as well.
						elseif(preg_match('#^\.*#', $accepttype)){
							if(substr($accepttype, 1) == $nf->getExtension()){
								$acceptgood = true;
								break;
							}
						}
						// Umm....
						else{
							$this->_error = 'Unsupported accept option, ' . $accepttype;
							return false;
						}
					}

					// Now that all the mimetypes have run through, I can see if one matched.
					if(!$acceptgood){
						$this->_error = 'Invalid file uploaded, please ensure it is one of [' . implode(', ', $accepts) . ']';
						return false;
					}
				}

				// Now all the checks should be completed and I can safely copy the file away from the temporary filesystem.
				$f->copyTo($nf);

				$value = $nf->getBaseFilename();
			}
		}

		$this->_attributes['value'] = $value;
		return true;
	}
}