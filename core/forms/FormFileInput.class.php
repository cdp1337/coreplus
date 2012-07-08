<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 7/6/12
 * Time: 5:12 PM
 * To change this template use File | Settings | File Templates.
 */

class FormFileInput extends FormElement {
	private static $_AutoID = 0;

	public function __construct($atts = null) {
		// Some defaults
		$this->_attributes      = array(
			'class'             => 'formelement formfileinput',
			'previewdimensions' => '200x100',
			'browsable'         => false,
			'basedir'           => '',
		);
		$this->_validattributes = array();
		$this->requiresupload   = true;

		parent::__construct($atts);
	}

	public function render() {
		if (!$this->get('id')) {
			// This system requires a valid id.
			++self::$_AutoID;
			$this->set('id', 'formfileinput-' . self::$_AutoID);
		}

		if (!$this->get('basedir')) {
			throw new Exception('FormFileInput cannot be rendered without a basedir attribute!');
		}

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
						$this->_error = 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
						return false;
					case UPLOAD_ERR_FORM_SIZE:
						$this->_error = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form. ';
						return false;
					default:
						$this->_error = 'An error occured while trying to upload the file for ' . $this->get('label');
						return false;
				}

				// Source
				$f = new File_local_backend($in['tmp_name']);
				// Destination
				$nf = Core::File($this->get('basedir') . '/' . $in['name']);
				$f->copyTo($nf);

				$value = $nf->getBaseFilename();
			}
		}

		$this->_attributes['value'] = $value;
		return true;
	}
}