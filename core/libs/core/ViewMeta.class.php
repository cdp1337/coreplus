<?php
/**
 * Systems for the meta data and tags on Views.
 *
 * @package Core Plus\Core
 * @author Charlie Powell <charlie@eval.bz>
 * @since 2.4.0
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

//echo '<pre>';
//debug_print_backtrace();
//die();

/**
 * The main controller for a set of controls, can be instantiated with either page level or inline operations,
 * it doesn't care which.
 */
class ViewMetas implements Iterator, ArrayAccess {

	private $_links = array();

	private $_pos = 0;

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Return the current element
	 * @link http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 */
	public function current() {
		return $this->_links[$this->_pos];
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Move forward to next element
	 * @link http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 */
	public function next() {
		++$this->_pos;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Return the key of the current element
	 * @link http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 */
	public function key() {
		return $this->_pos;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Checks if current position is valid
	 * @link http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 */
	public function valid() {
		return isset($this->_links[$this->_pos]);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Rewind the Iterator to the first element
	 * @link http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 */
	public function rewind() {
		$this->_pos = 0;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Whether a offset exists
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 *
	 * @param mixed $offset <p>
	 * An offset to check for.
	 * </p>
	 *
	 * @return boolean true on success or false on failure.
	 * </p>
	 * <p>
	 * The return value will be casted to boolean if non-boolean was returned.
	 */
	public function offsetExists($offset) {
		return array_key_exists($offset, $this->_links);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to retrieve
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 * @param mixed $offset <p>
	 * The offset to retrieve.
	 * </p>
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset) {
		return $this->_links[$offset];
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to set
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 * @param mixed $offset <p>
	 * The offset to assign the value to.
	 * </p>
	 * @param mixed $value <p>
	 * The value to set.
	 * </p>
	 * @return void
	 *
	 * @throws Exception
	 */
	public function offsetSet($offset, $value) {
		if($offset === null){
			// The user just wants the next available one.

			if($this->valid()){
				$this->next();

			}
			$offset = $this->key();
		}

		// Is it already set?
		if(isset($this->_links[$offset])){
			/** @var $meta ViewMeta */
			$meta = $this->_links[$offset];
		}
		else{
			// Create it!
			/** @var $meta ViewMeta */
			$meta = ViewMeta::Factory($offset);
			$this->_links[$offset] = $meta;
		}

		$meta->content = $value;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to unset
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 * @param mixed $offset <p>
	 * The offset to unset.
	 * </p>
	 * @return void
	 */
	public function offsetUnset($offset) {
		unset($this->_links[$offset]);
	}

	/**
	 * Add an array of links to this control set.
	 *
	 * @param array $links
	 */
	public function addLinks(array $links){
		foreach($links as $l){
			$this[] = $l;
		}
	}

	/**
	 * Get this control set as HTML
	 *
	 * @return array
	 */
	public function fetch(){
		$data = array();
		foreach($this->_links as $l){
			$ea = $l->fetch();
			if(is_array($ea) && sizeof($ea)){
				$data = array_merge($data, $l->fetch());
			}
		}
		return $data;
	}
}


/**
 * Just a tiny class for handling control links in the main page view.
 *
 * These are usually tiny icons or snippets of text that provide a bit of inline administrative
 * functionality for pages.
 *
 * @package Core Plus\Core
 * @author Charlie Powell <charlie@eval.bz>
 * @since 2.1.2
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
class ViewMeta {

	const BASE_META = 'meta';
	const BASE_LINK = 'link';

	/**
	 * The base type of this view tag, change as necessary in your tag.
	 *
	 * @var string
	 */
	public $base = ViewMeta::BASE_META;

	/**
	 * Links support href attributes.
	 *
	 * @var string
	 */
	public $href = '';

	/**
	 * This is the property attribute for meta tags and the rel attribute for link tags.
	 * @var string
	 */
	public $property = '';

	/**
	 * The content or value, usually for meta tags.
	 *
	 * @var string
	 */
	public $content = '';

	/**
	 * Any other attributes for the a tag.
	 *
	 * @var array
	 */
	public $otherattributes = array();


	public function __toString(){
		return $this->content;
	}

	/**
	 * Get this control as HTML
	 *
	 * @return array
	 */
	public function fetch(){
		switch($this->base){
			case ViewMeta::BASE_META: return $this->_fetchMeta();
			case ViewMeta::BASE_LINK: return $this->_fetchLink();
		}
	}

	/**
	 * Internal function to render <meta/> tags.
	 *
	 * @return string
	 */
	private function _fetchMeta(){
		// If this tag has no content, just return a blank string.
		if(!$this->content) return '';

		return array(
			$this->property => '<meta property="' . $this->property . '" content="' . str_replace('"', '&quot;', $this->content) . '"/>'
		);
	}

	/**
	 * Internal function to render <link/> tags
	 *
	 * @return string
	 */
	private function _fetchLink(){
		// Links can be so much more exciting!
		die('finish fetchLink');
	}

	/**
	 * Create a new property
	 * @param $property
	 *
	 * @return ViewMeta
	 */
	public static function Factory($property){
		// First, check and see if there is an extended class for this type.
		$classcheck = 'ViewMeta_' . preg_replace('/[^a-zA-Z]/', '_', $property);
		if(class_exists($classcheck)){
			$meta = new $classcheck();
		}
		else{
			$meta = new ViewMeta();
		}

		$meta->property = $property;
		return $meta;
	}
}

class ViewMeta_description extends ViewMeta {
	public function fetch(){
		if(!$this->content) return array();

		// The description should be limited to 350 characters.
		$content = $this->content;
		if(strlen($content) > 300) $content = substr($content, 0, 297) . '...';

		return array('description' => '<meta name="description" content="' . str_replace('"', '&quot;', $content) . '"/>');
	}
}

class ViewMeta_keywords extends ViewMeta {
	public function fetch(){
		if(!$this->content) return array();

		return array('keywords' => '<meta name="keywords" content="' . str_replace('"', '&quot;', $this->content) . '"/>');
	}
}

class ViewMeta_name extends ViewMeta {
	public function fetch(){
		if(!$this->content) return array();

		return array('name' => '<meta name="name" content="' . str_replace('"', '&quot;', $this->content) . '"/>');
	}
}

class ViewMeta_author extends ViewMeta {
	public function __toString(){
		if(is_subclass_of($this->content, 'User')){
			return $this->content->getDisplayName();
		}
		else{
			return $this->content;
		}
	}
	public function fetch(){
		if(!$this->content) return '';

		$data = array();

		// It's probably a User object!
		if(is_subclass_of($this->content, 'User')){
			// All profiles get at least the meta tag.
			$data['author'] = '<meta property="author" content="' . str_replace('"', '&quot;', $this->content->getDisplayName()) . '"/>';
			// "Socially enabled" sites also get the link attribute!
			if(Core::IsComponentAvailable('user-social')){
				$data['link-author'] = '<link rel="author" href="' . UserSocialHelper::ResolveProfileLink($this->content) . '"/>';
			}
		}
		else{
			$data['author'] = '<meta property="author" content="' . str_replace('"', '&quot;', $this->content) . '"/>';
		}

		return $data;
	}
}

class ViewMeta_canonical extends ViewMeta {
	public function fetch(){
		if(!$this->content) return '';
		$data = array();

		$data['link-canonical'] = '<link rel="canonical" href="' . $this->content . '" />';
		$data['og:url'] = '<meta property="og:url" content="' . str_replace('"', '&quot;', $this->content) . '"/>';

		return $data;
	}
}

class ViewMeta_generator extends ViewMeta {
	public function fetch(){
		return array('generator' => '<meta name="generator" content="Core Plus ' . Core::GetComponent()->getVersion() . '"/>');
	}
}