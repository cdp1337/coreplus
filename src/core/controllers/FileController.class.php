<?php
/**
 * DESCRIPTION
 *
 * @package Core
 * @since 2.5.4
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

class FileController extends Controller_2_1 {

	public function preview() {
		$view = $this->getView();
		$request = $this->getPageRequest();

		// This is designed to only return data!
		$view->mode = View::MODE_NOOUTPUT;
		// And it's going to be an image of some sorts.
		$view->contenttype = 'image/png';
		// And it shouldn't be recorded in navigation.
		$view->record = false;

		// The filename should be something like files/public/blah... or files/assets/foo...
		// This will get fed into the core system and checked internally.
		$filename = $request->getParameter('f');


		// Was there a resize-to dimension requested?
		$d = $request->getParameter('d');


		// The inbound filename must be in the format of base64:[b64data].
		if (strpos($filename, 'base64:') !== 0) {
			if(DEVELOPMENT_MODE){
				error_log('Invalid request made for /file/preview!  Expecting: [base64:*b64data*], Received: [' . $filename . ']');
			}

			$file = \Core\file('assets/images/mimetypes/notfound.png');
			$file->displayPreview($d);
			return;
		}

		// This preview ONLY supports assets and public files!
		// This is a security precaution.
		$base = base64_decode(substr($filename, 7));

		if(!(strpos($base, 'public/') === 0 || strpos($base, 'assets/') === 0 || strpos($base, 'asset/') === 0)){
			SecurityLogModel::Log('/file/preview', 'fail', null, 'Invalid file requested: ' . $base);

			if(DEVELOPMENT_MODE){
				error_log('Invalid request made for /file/preview!  Expecting: [public/* or asset[s]/*], Received: [' . $base . ']');
			}

			$file = \Core\file('assets/images/mimetypes/notfound.png');
			$file->displayPreview($d);
			return;
		}

		$file = \Core\file($filename);
		if(!$file->exists()){
			if(DEVELOPMENT_MODE){
				error_log('File not found for /file/preview!  Looking For: [' . $file->getFilename('') . ' (' . $filename . ') ]');
			}

			$file = \Core\file('assets/images/mimetypes/notfound.png');
			$file->displayPreview($d);
			return;
		}

		// And this will render it to the browser.
		$file->displayPreview($d);
	}
}
