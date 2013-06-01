<?php
/**
 * Class file for FormFileInput
 *
 * @package Core\Forms
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2012  Charlie Powell
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses/agpl-3.0.txt.
 */

/**
 * Class FormFileInput provides built-in support for file uploads in forms.
 *
 * Destination directory, filetypes, max file sizes, etc are all typically handled automatically,
 * as with standard form elements.
 *
 * <h3>Options</h3>
 *
 * <p>The following standard options are supported for File Input types:</p>
 *
 * <dl>
 *     <dt>accept</dt>
 *     <dd>
 *         A comma-separated list of accept mimetypes for this file.  (Also supports extensions if prefixed with a ".".<br/>
 *         Examples:<br/>
 *         <code>
 * // Accept only PNG, JPEG, and GIF images.
 * accept: 'image/png, image/jpg, image/gif'</code>
 *         <code>// Accept any (rasterized) image.
 * accept: 'image/*'</code>
 *         <code>// Accept CSV, XLS, or ODS files
 * accept: '.csv, .xsl, .ods';</code>
 *     </dd>
 *
 *     <dt>allowlink</dt>
 *     <dd>I can't remember what this does...</dd>
 *
 *     <dt>basedir</dt>
 *     <dd>
 *         The destination directory this file will get saved to.  This is rendered with Core's File system, so
 *         any valid File prefix will work, ie: "public/foo", "private/blah", "tmp/this-will-get-deleted-next-reboot/", etc.
 * </dl>
 *
 *
 * @package Core\Forms
 */
class FormFileInput extends FormElement {
	/**
	 * @var int
	 */
	private static $_AutoID = 0;

	/**
	 * @param null $atts
	 */
	public function __construct($atts = null) {
		// Some defaults
		$this->_attributes      = array(
			'class'             => 'formelement formfileinput',
			'previewdimensions' => '200x100',
			'browsable'         => false,
			'basedir'           => '',
			'allowlink'         => true,
		);
		$this->_validattributes = array();
		$this->requiresupload   = true;

		parent::__construct($atts);
	}

	/**
	 * @return string
	 * @throws Exception
	 */
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

	/**
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function setValue($value) {
		if ($this->get('required') && !$value) {
			$this->_error = $this->get('label') . ' is required.';
			return false;
		}

		// _link_ allows users to paste in a URL for a given file.  This is then copied locally as normal.
		// In order to detect this, I need to look for the presence of a protocol indicator and this element needs
		// to have allowlink set.
		if($this->get('allowlink') && strpos($value, '_link_://') === 0){
			$n = $this->get('name');
			$value = substr($value, 9);

			// Source
			$f = new File_remote_backend($value);

			if(!$f->exists()){
				$this->_error = 'Remote file does not seem to exist';
				return false;
			}

			// Destination
			$nf = Core::File($this->get('basedir') . '/' . $f->getBaseFilename());

			// do NOT copy the contents over until the accept check has been ran!

			// Now that I have a file object, (in the temp filesystem still), I should validate the filetype
			// to see if the developer wanted a strict "accept" type to be requested.
			// If present, I'll have something to run through and see if the file matches.
			// I need the destination now because I need to full filename if an extension is requested in the accept.
			if($this->get('accept')){
				$acceptcheck = \Core\check_file_mimetype($this->get('accept'), $f->getMimetype(), $nf->getExtension());

				// Now that all the mimetypes have run through, I can see if one matched.
				if($acceptcheck != ''){
					$this->_error = $acceptcheck;
					return false;
				}
			}

			// Now all the checks should be completed and I can safely copy the file away from the temporary filesystem.
			$f->copyTo($nf);

			$value = $nf->getBaseFilename();
		}
		elseif ($value == '_upload_') {
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
				$error = \Core\translate_upload_error($in['error']);
				if($error != ''){
					$this->_error = $error;
					return false;
				}

				// Source
				$f = \Core\Filestore\factory($in['tmp_name']);
				// Destination
				$nf = \Core\Filestore\factory($this->get('basedir') . '/' . $in['name']);

				// do NOT copy the contents over until the accept check has been ran!

				// Now that I have a file object, (in the temp filesystem still), I should validate the filetype
				// to see if the developer wanted a strict "accept" type to be requested.
				// If present, I'll have something to run through and see if the file matches.
				// I need the destination now because I need to full filename if an extension is requested in the accept.
				if($this->get('accept')){
					$acceptcheck = \Core\check_file_mimetype($this->get('accept'), $f->getMimetype(), $nf->getExtension());

					// Now that all the mimetypes have run through, I can see if one matched.
					if($acceptcheck != ''){
						$this->_error = $acceptcheck;
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