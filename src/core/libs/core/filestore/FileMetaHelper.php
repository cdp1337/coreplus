<?php
/**
 * File for class FileMetaHelper definition in the coreplus project
 * 
 * @package Core\Filestore
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130716.1703
 * @copyright Copyright (C) 2009-2013  Author
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

namespace Core\Filestore;


/**
 * A short teaser of what FileMetaHelper does.
 *
 * More lengthy description of what FileMetaHelper does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for FileMetaHelper
 * <h4>Example 1</h4>
 * <p>Description 1</p>
 * <code>
 * // Some code for example 1
 * $a = $b;
 * </code>
 *
 *
 * <h4>Example 2</h4>
 * <p>Description 2</p>
 * <code>
 * // Some code for example 2
 * $b = $a;
 * </code>
 *
 * 
 * @package Core\Filestore
 * @author Charlie Powell <charlie@eval.bz>
 *
 */
class FileMetaHelper implements \ArrayAccess {
	/**
	 * @var File The file object for this metahelper
	 */
	protected $_file;

	/**
	 * @var string The core-ified filename of this file.
	 */
	protected $_filename;

	/**
	 * @var array|null Array of the metatags associated to this file, or null if not set yet.
	 */
	protected $_metas;

	public function __construct($file){
		if(is_scalar($file)){
			$this->_file = Factory::File($file);
		}
		elseif($file instanceof File){
			$this->_file = $file;
		}
		else{
			throw new \Exception('Unsupported parameter for FileMetaHelper, please ensure it is either a string of a valid File object.');
		}

		$this->_filename = $this->_file->getFilename(false);
	}

	public function setMeta($key, $value){
		$metas = $this->getMetas();

		// keywords behave slightly differently here.
		if($key == 'keywords' || $key == 'keywords[]'){
			if(!is_array($value)) $value = array($value => $value);

			// I need to make sure that each value is a key/value pair.
			foreach($value as $valueidx => $valueval){
				if(trim($valueval) == ''){
					// Empty value, empty value!
					unset($value[$valueidx]);
				}
				elseif(is_numeric($valueidx)){
					// This will replace any numeric based key with the url version of the value.
					// This may have an odd side effect of transposing numeric values like 2013,
					// but since the url version of a number is just the number itself, it should be ok.
					unset($value[$valueidx]);
					$value[ \Core\str_to_url($valueval) ] = $valueval;
				}
				// No else needed, it's acceptable as-is.
			}

			foreach($metas as $idx => $meta){
				/** @var $meta \FileMetaModel */

				// I'm only interested in these!
				if($meta->get('meta_key') != 'keyword') continue;

				if(isset($value[ $meta->get('meta_value') ])){
					// Yay, update the value title
					$meta->set('meta_value_title', $value[ $meta->get('meta_value') ]);
					$meta->save();
					unset($value[ $meta->get('meta_value') ]);
				}
				else{
					// Nope?  Delete it!
					$meta->delete();
					unset($this->_metas[$idx]);
				}
			}

			// Any new incoming keywords left?
			foreach($value as $metavalue => $metavaluetitle){
				$meta = new \FileMetaModel($this->_filename, 'keyword', $metavalue);
				$meta->set('meta_value_title', $metavaluetitle);
				$meta->save();
				$this->_metas[] = $meta;
			}
		}
		elseif($key == 'license'){
			// Match this up to the value if it's available.
			$all = \Core\Licenses\Factory::GetLicenses();
			if(isset($all[$value])){
				$valuetitle = $all[$value]['title'];
			}
			else{
				$valuetitle = $value;
			}

			foreach($metas as $idx => $meta){
				/** @var $meta \FileMetaModel */

				// I'm only interested in this one!
				if($meta->get('meta_key') != $key) continue;

				// It must be the one I'm looking for.
				$meta->set('meta_value', $value);
				$meta->set('meta_value_title', $valuetitle);
				$meta->save();
				return; // :)
			}

			// Doesn't exist?
			$meta = new \FileMetaModel($this->_filename, $key, $value);
			$meta->set('meta_value_title', $valuetitle);
			$meta->save();
			$this->_metas[] = $meta;
		}
		elseif($key == 'authorid'){
			// This affects the author tag instead, look for that!
			foreach($metas as $idx => $meta){
				/** @var $meta \FileMetaModel */

				// I'm only interested in this one!
				if($meta->get('meta_key') != 'author') continue;

				// It must be the one I'm looking for.
				$meta->set('meta_value', $value);
				$meta->save();
				return; // :)
			}

			// Doesn't exist?
			$meta = new \FileMetaModel($this->_filename, 'author', $value);
			$meta->save();
			$this->_metas[] = $meta;
		}
		elseif($key == 'file'){
			// Skip this one!
		}
		// Default action, one to one!
		else{
			// Look for this key to see if it exists.
			foreach($metas as $idx => $meta){
				/** @var $meta \FileMetaModel */

				// I'm only interested in this one!
				if($meta->get('meta_key') != $key) continue;

				// It must be the one I'm looking for.
				if($value){
					// Does it have a value?
					$meta->set('meta_value_title', $value);
					$meta->save();
				}
				else{
					// It's blank but exists... I can fix that :p
					$meta->delete();
					unset($this->_metas[$idx]);
				}
				return; // :)
			}

			// Doesn't exist?
			if($value){
				$meta = new \FileMetaModel($this->_filename, $key, '');
				$meta->set('meta_value_title', $value);
				$meta->save();
				$this->_metas[] = $meta;
			}
		}
	}

	/**
	 * Get the array of meta tags that are associated to this file object.
	 *
	 * @return array
	 */
	public function getMetas(){
		if($this->_metas === null){
			$this->_metas = \FileMetaModel::Find(['file = ' . $this->_filename]);
		}

		return $this->_metas;
	}

	/**
	 * Get either a single Model object, null if it doesn't exist, or an array of them, (if keywords is requested).
	 *
	 * @param $key
	 *
	 * @return array|\FileMetaModel|null
	 */
	public function getMeta($key){
		if($key == 'keywords'){
			$keywords = array();

			foreach($this->getMetas() as $m){
				if($m->get('meta_key') == 'keyword') $keywords[] = $m;
			}

			return $keywords;
		}
		else{
			foreach($this->getMetas() as $m){
				if($m->get('meta_key') == $key) return $m;
			}
		}

		// None found?
		return null;
	}

	public function getMetaTitle($key){
		$meta = $this->getMeta($key);

		if(!$meta){
			return '';
		}
		elseif(is_array($meta)){
			$out = [];
			foreach($meta as $m){
				$out[] = $m->get('meta_value_title');
			}
			return $out;
		}
		else{
			return $meta->get('meta_value_title');
		}
	}

	/**
	 * Get this file's metadata as an HTML string, useful for giving credit for photos or sources.
	 *
	 * @return string
	 */
	public function getAsHTML(){
		if($this->_file->isImage()){
			return $this->_getAsImageHTML();
		}
		else{
			return '';
		}
	}

	/**
	 * Get a form object pre-populated with this file's metadata.
	 *
	 * @param string $prefix Form prefix for elements
	 * @return \Form
	 */
	public function getForm($prefix = 'metas'){
		$form = new \Form();
		$this->addElementsToForm($form, $prefix);
		return $form;
	}

	/**
	 * Just add the appropriate elements to a given form.
	 *
	 * @param \Form  $form
	 * @param string $prefix
	 */
	public function addElementsToForm(\Form $form, $prefix = 'metas'){
		$allmetas   = self::GetMetaElements();
		$metavalues = $this->getMetas();
		$keywords   = [];

		// Tack on the actual file object too.
		// This just makes the logic cleaner.
		$allmetas['file'] = [
			'type'  => 'system',
			'value' => $this->_file->getFilename(false),
		];

		// Merge these two together.
		foreach($metavalues as $meta){
			/** @var $meta \FileMetaModel */
			$key = $meta->get('meta_key');
			if($key == 'keyword'){
				$keywords[ $meta->get('meta_value') ] = $meta->get('meta_value_title');
			}
			elseif($key == 'author'){
				$allmetas['authorid'] = [
					'type' => 'hidden',
					'value' => $meta->get('meta_value'),
				];

				// And the author field always displays this.
				$allmetas[$key]['value'] = $meta->get('meta_value_title');
			}
			else{
				$value = $meta->get('meta_value') ? $meta->get('meta_value') : $meta->get('meta_value_title');

				if(isset($allmetas[$key])){
					$allmetas[$key]['value'] = $value;
				}
			}
		}

		foreach($allmetas as $name => $dat){

			if($prefix) $dat['name'] = $prefix . '[' . $name . ']';
			else $dat['name'] = $name;

			// And...
			if($name == 'keywords'){
				$dat['value'] = $keywords;
			}

			$form->addElement($dat['type'], $dat);
		}
	}




	//// A few array access functions \\\\

	/**
	 * Whether an offset exists
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 *
	 * @param mixed $offset An offset to check for.
	 *
	 * @return boolean Returns true on success or false on failure.
	 */
	public function offsetExists($offset) {
		return ($this->getMeta($offset));
	}

	/**
	 * Offset to retrieve
	 *
	 * Alias of Model::get()
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 *
	 * @param mixed $offset The offset to retrieve.
	 *
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset) {
		return $this->getMetaTitle($offset);
	}

	/**
	 * Offset to set
	 *
	 * Alias of Model::set()
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 *
	 * @param mixed $offset The offset to assign the value to.
	 * @param mixed $value The value to set.
	 *
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		$this->setMeta($offset, $value);
	}

	/**
	 * Offset to unset
	 *
	 * This just sets the value to null.
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 *
	 * @param mixed $offset The offset to unset.
	 *
	 * @return void
	 */
	public function offsetUnset($offset) {
		$this->setMeta($offset, null);
	}




	private function _getAsImageHTML(){
		if(sizeof($this->getMetas())){
			// If there are no meta tags associated, nothing to do!
			$metatitle   = $this->getMetaTitle('title');
			$metaauthor  = $this->getMetaTitle('author');
			$metalicense = $this->getMeta('license');
			$metaurl     = $this->getMetaTitle('url');
			$metatags    = $this->getMetaTitle('keywords');
			$url         = $this->_file->getURL();

			$metacredithtml = '';
			if($metaauthor){
				$metacredithtml .=
					'Photo by <span itemprop="author">' .
					($metaurl ? '<a href="' . $metaurl . '" target="_BLANK">' . $metaauthor . '</a>' : $metaauthor) .
					'</span>';
			}
			if($metalicense){
				$lic = \Core\Licenses\Factory::GetLicense($metalicense->get('meta_value'));
				$licimg = \Core::ResolveAsset('assets/images/licenses/' . $metalicense->get('meta_value') . '-sm.png');

				if($lic){
					$metacredithtml .= ($metacredithtml ? ' ' : '') . '<a href="' . $lic['url'] . '" target="_BLANK"><img src="' . $licimg . '" alt="' . $lic['title'] . '" title="' . $lic['title'] . '"/></a>';
				}
				else{
					$metacredithtml .= ($metacredithtml ? ' // ' : '') . $metalicense->get('meta_value_title');
				}
			}

			// Wrap it!
			$metaheader = '<div class="image-metadata" itemscope itemtype="http://schema.org/ImageObject">';
			$metafooter = '</div><!-- END .image-metadata-wrapper -->';

			// Some extra inline attributes
			$metacredithtml .=
				'<meta itemprop="image" content="' . $url . '"/>' .
				'<meta itemprop="url" content="' . ($metaurl ? $metaurl : $url) . '"/>' .
				($metatitle ? '<meta itemprop="name" content="' . $metatitle . '"/>' : '') .
				(is_array($metatags) && sizeof($metatags) ? '<meta itemprop="keywords" content="' . htmlspecialchars(implode(', ', $metatags)) . '"/>' : '');


			// Merge everything together!
			return $metaheader . $metacredithtml . $metafooter;
		}
	}

	/**
	 * Get an array of the form elements to use in the metadata edit page.
	 *
	 * @return array
	 */
	public static function GetMetaElements(){
		$elements = array(
			'title' => [
				'title' => 'Name or Title',
				'type' => 'text',
				'description' => 'If this work has a specific name or title, enter it here to display along with the alt and title tags'
			],
			'author' => [
				'title' => 'Author',
				'type' => 'pagemetaauthor',
				'description' => 'Original author of this work',
			],
			'url' => [
				'title' => 'URL',
				'type' => 'text',
				'description' => 'Link back to the original source or author'
			],
			'license' => [
				'title' => 'License',
				'type' => 'license',
				'description' => 'The license this work is distributed under',
			],
			'keywords' => [
				'title' => 'Keywords',
				'type' => 'pagemetakeywords',
				'description' => 'Any keywords or tags for this work',
			],
			'description' => [
				'title' => 'Description',
				'type' => 'textarea',
			],
		);

		return $elements;
	}
}