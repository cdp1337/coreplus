<?php
/**
 * 
 * 
 * @package Core
 * @since 1.9
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2017  Charlie Powell
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

namespace Core;


use Core\Filestore\Contents\ContentCSV;
use Core\Filestore\Factory;
use Core\Templates\Template;

class CSVImporter {
	
	public $counts = null;
	public $filename = null;
	public $key = null;
	public $sessionKey = null;
	public $columns = [];
	public $aliases = [];
	public $maps = null;
	public $hasHeader = null;
	
	private $_file = null;
	/** @var ContentCSV|null */
	private $_obj = null;

	/**
	 * Construct a new object with a session key that will get used throughout the import process.
	 * 
	 * @param $key
	 */
	public function __construct($key){
		$this->key = \Core\str_to_url($key);
		$this->sessionKey = $this->key . '-importer';
		
		$this->counts = \Core\Session::Get($this->sessionKey . '/counts');
		$this->filename = \Core\Session::Get($this->sessionKey . '/file');
		$this->maps = \Core\Session::Get($this->sessionKey . '/maps');
		$this->hasHeader = \Core\Session::Get($this->sessionKey . '/header');
	}

	/**
	 * Add a named column, (and optionally a nice title), for the user to select from.
	 * 
	 * Only named columns are returned in the getChunk operation!
	 * 
	 * @param string      $key   The key name for this named column, (is used in the returning chunk array).
	 * @param null|string $title Optionally a human-friendly name for this named column to display in the selection dropdown.
	 */
	public function addColumn($key, $title = null){
		if($title === null){
			$title = ucwords(str_replace(['_', '-'], ' ', $key));
		}
		$this->columns[$key] = $title;
	}

	/**
	 * Add an alias for a named column that may be used by common spreadsheets.
	 * 
	 * Used if you have a field "email", whereas the CSV has a column "email_address".
	 * 
	 * @param string $key   The key name of the column, (that must already be registered)
	 * @param string $alias The alias to match also for this named column, (gets remapped to the base key name).
	 *
	 * @throws \Exception
	 */
	public function addAlias($key, $alias){
		if(!isset($this->columns[$key])){
			throw new \Exception('Column ' . $key . ' does not exist for new alias ' . $alias . ', please create the column first!');
		}
		$this->aliases[$alias] = $key;
	}

	/**
	 * Set the file for this importer, usually done automatically.
	 * 
	 * @param Filestore\File $file
	 *
	 * @throws \Exception
	 */
	public function setFile(\Core\Filestore\File $file){
		
		if(!$file->exists()){
			throw new \Exception('File ' . $file->getFilename() . ' does not exist!');
		}
		if(!$file->isReadable()){
			throw new \Exception('File ' . $file->getFilename() . ' is not readable!');
		}
		if(!$file->isLocal()){
			throw new \Exception('Refusing to import a file that is not local!');
		}

		$contents = $file->getContentsObject();
		if(!$contents instanceof \Core\Filestore\Contents\ContentCSV){
			throw new \Exception($file->getBaseFilename() . ' does not appear to be a valid CSV file!', 'error');
		}
		
		$this->filename = $file->getFilename();
		\Core\Session::Set($this->sessionKey . '/file', $this->filename);
	}

	/**
	 * Cleanup the process and remove all temporary files.
	 */
	public function abortAndDestroy(){
		if($this->filename){
			$file = Factory::File($this->filename);
			$file->delete();
			$this->filename = null;
		}
		
		if($this->counts){
			$this->counts = null;
		}
		
		// And the session.
		\Core\Session::UnsetKey($this->sessionKey . '/*');
	}

	/**
	 * Get the rendered HTML of this process, based on the current stage where it's at.
	 * 
	 * @return string
	 */
	public function render(){
		switch($this->getStep()){
			case 1:
				return $this->_renderImport1();
			case 2:
				return $this->_renderImport2();
			case 3:
				return $this->_renderImport3();
			default:
				// Last step is all up to the calling function, this is where the custom logic is at!
				return '';
		}
	}

	/**
	 * Get which step this import is on, 1-3.
	 * 
	 * 1: no file uploaded, present the option to upload.
	 * 2: file uploaded and saved to temp; give the user options to select the column mapping.
	 * 3: Mapping available and import ready to start.
	 * 
	 * @return int
	 */
	public function getStep(){
		if($this->maps !== null){
			return 3;
		}
		elseif($this->filename !== null){
			// The file is set, that's step two.
			return 2;
		}
		else{
			return 1;
		}
	}

	/**
	 * Get a chunk of N lines from the CSV.
	 * 
	 * This array will contain sub arrays that are keyed with the named columns and have their respective content for that record.
	 * 
	 * Will return false when at the end of the file.
	 * 
	 * @param int $lines
	 *
	 * @return array|false
	 * @throws \Exception
	 */
	public function getChunk($lines = 100){
		if($this->_file === null){
			$this->_file = Factory::File($this->filename);
			
			$this->_obj = $this->_file->getContentsObject();
		}
		
		$ret = $this->_obj->parseChunked(',', $lines);
		
		// Is ret an array?  If so, it's still a valid chunk.
		// Otherwise it's at the end of the file and no need to continue.
		if(!is_array($ret)){
			return $ret;
		}
		
		// Use the map to transpose this to the set of elements expected to be returned.
		$mapped = [];
		foreach($ret as $r){
			$newr = [];
			foreach($this->maps as $k => $v){
				if($v && isset($r[$k])){
					$newr[$v] = $r[$k];
				}
			}
			
			$mapped[] = $newr;
		}
		
		return $mapped;
	}

	/**
	 * Get the total records that are to be imported.
	 * 
	 * @return int
	 * @throws \Exception
	 */
	public function getTotalRecords(){
		if($this->_file === null){
			$this->_file = Factory::File($this->filename);

			$this->_obj = $this->_file->getContentsObject();
		}
		
		return $this->_obj->getTotalLines();
	}
	
	private function _renderImport1(){
		$tmpl = Template::Factory('includes/csvimporter/import1.tpl');
		
		$form = new \Core\Forms\Form();
		$form->set('callsmethod', 'Core\\CSVImporter::FormHandler1');
		$form->addElement('system', ['name' => 'key', 'value' => $this->key]);
		$form->addElement(
			'file',
			[
				'name' => 'file',
				'title' => 'File To Import',
				'basedir' => 'tmp/' . $this->key . '-importer',
				'required' => true,
				'accept' => '.csv',
			]
		);
		$form->addElement('submit', ['name' => 'submit', 'value' => 'Next']);
		
		$tmpl->assign('form', $form);
		return $tmpl->fetch();
	}

	private function _renderImport2(){
		$tmpl = Template::Factory('includes/csvimporter/import2.tpl');

		$file = \Core\Filestore\Factory::File($this->filename);
		/** @var ContentCSV $contents */
		$contents = $file->getContentsObject();

		$hasheader = $contents->hasHeader();
		$preview = $contents->parse(',', 10);
		$total = $contents->getTotalLines();

		// Since I don't want to display the entire dataset in the preview...
		if($hasheader){
			$header = $contents->getHeader();
		}
		else{
			$header = array();
			$i=0;
			foreach($preview[0] as $k => $v){
				$header[$i] = 'Column ' . ($i+1);
				$i++;
			}
		}
		$colcount = sizeof($header);

		$form = new \Core\Forms\Form();
		$form->set('callsmethod', 'Core\\CSVImporter::FormHandler2');
		$form->addElement('system', ['name' => 'key', 'value' => $this->key]);
		$form->addElement('hidden', ['name' => 'cancel', 'value' => 0]);
		$form->addElement(
			'checkbox',
			[
				'name' => 'has_header',
				'title' => 'Has Header',
				'value' => 1,
				'checked' => $hasheader,
				'description' => 'If this CSV has a header record on line 1, (as illustrated below), check this to ignore that line.'
			]
		);
/*
		$form->addElement(
			'checkbox',
			[
				'name' => 'merge_duplicates',
				'title' => 'Merge Duplicate Records',
				'value' => 1,
				'checked' => true,
				'description' => 'Merge duplicate records that may be found in the import.'
			]
		);
*/

		// Get the map-to options.
		// This consists of a DO NOT MAP option followed by every column created by the calling script.
		$headerElementNames = [];
		$maptos = ['' => '-- Do Not Map --'];
		foreach($this->columns as $k => $t){
			$maptos[$k] = $t;
		}
		
		foreach($header as $key => $title){
			$value = '';
			
			if(isset($this->columns[$key])){
				// This header is set from a column verbatim.
				$value = $key;
			}
			elseif(isset($this->columns[$title])){
				$value = $key;
			}
			elseif(($s = array_search($key, $this->columns))){
				$value = $s;
			}
			elseif(($s = array_search($title, $this->columns))){
				$value = $s;
			}
			elseif(isset($this->aliases[$key])){
				// An alias is set, good enough too.
				$value = $this->aliases[$key];
			}
			elseif(isset($this->aliases[$title])){
				// An alias is set, good enough too.
				$value = $this->aliases[$key];
			}

			$headerElementNames[] = 'mapto[' . $key . ']';
			$form->addElement(
				'select',
				[
					'name' => 'mapto[' . $key . ']',
					'title' => $title,
					'options' => $maptos,
					'value' => $value
				]
			);
		}

		$tmpl->assign('has_header', $hasheader);
		$tmpl->assign('header', $header);
		$tmpl->assign('preview', $preview);
		$tmpl->assign('form', $form);
		$tmpl->assign('total', $total);
		$tmpl->assign('col_count', $colcount);
		$tmpl->assign('header_element_names', $headerElementNames);
		
		$tmpl->assign('form', $form);
		return $tmpl->fetch();
	}

	private function _renderImport3(){
		$tmpl = Template::Factory('includes/csvimporter/import3.tpl');
		
		$tmpl->assign('csv_importer', $this);
		$tmpl->assign('total_records', $this->getTotalRecords());
		return $tmpl->fetch();
	}

	/**
	 * Handler to save the CSV file locally.
	 *
	 * @param \Form $form
	 *
	 * @return bool
	 */
	public static function FormHandler1(\Core\Forms\Form $form) {
		
		$importer = new CSVImporter($form->getElementValue('key'));

		// If it's gotten here, all the form validation has succeeded!
		// Pretty simple eh? :p
		/** @var $el \FormFileInput */
		$el = $form->getElement('file');
		$file = $el->getFile();
		
		$importer->setFile($file);
		return true;
	}

	public static function FormHandler2(\Core\Forms\Form $form) {
		$importer = new CSVImporter($form->getElementValue('key'));
		
		if($form->getElementValue('cancel') == '1'){
			$importer->abortAndDestroy();
			\Core\set_message('Cancelled CSV Import!', 'success');
			return true;
		}
		
		$maps = [];
		$mapElements = $form->getElementsByName('mapto\[.*\]');
		foreach($mapElements as $el){
			/** @var \FormElement $el */
			$k = substr($el->get('name'), 6, -1);
			$v = $el->get('value');
			$maps[$k] = $v;
		}
		
		\Core\Session::Set($importer->sessionKey . '/maps', $maps);
		\Core\Session::Set($importer->sessionKey . '/header', $form->getElementValue('has_header'));
		return true;
	}
}