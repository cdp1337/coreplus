<?php
/**
 * Systems for the control widgets used for page-level operations and inline operations.
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


/**
 * The main controller for a set of controls, can be instantiated with either page level or inline operations,
 * it doesn't care which.
 */
class ViewControls implements Iterator, ArrayAccess {

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

		if($value instanceof ViewControl){
			$this->_links[$offset] = $value;
		}
		elseif(is_array($value)){
			$control = new ViewControl();

			// Completely associative-array based version!
			foreach($value as $k => $v){
				$control->set($k, $v);
			}

			// Some legacy updates for the icon.
			if(!$control->icon){
				switch($control->class){
					case 'add':
					case 'edit':
					case 'directory':
						$control->icon = $control->class;
						break;
					case 'delete':
						$control->icon = 'remove';
						break;
					case 'view':
						$control->icon = 'eye-open';
						break;
				}
			}

			$this->_links[] = $control;
		}
		else{
			throw new Exception('Invalid offset type for ViewControls::offsetSet, please only set a ViewControl or an associative array');
		}
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
	 * @return string HTML
	 */
	public function fetch(){
		$html = '<ul class="controls">';

		foreach($this->_links as $l){
			$html .= $l->fetch();
		}

		$html .= '</ul>';

		return $html;
	}

	/**
	 * Shortcut function to dispatch the /core/controllinks hook to request functions for a given subject.
	 *
	 * @param string $baseurl The baseurl, (excluding /core/controllinks), of the request
	 * @param mixed  $subject The subject matter of this hook, (if any)
	 *
	 * @return ViewControls
	 */
	public static function Dispatch($baseurl, $subject){
		$links = HookHandler::DispatchHook('/core/controllinks' . $baseurl, $subject);

		$controls = new ViewControls();
		$controls->addLinks($links);

		return $controls;
	}

	/**
	 * Shortcut function to dispatch the /core/controllinks hook to request functions for a given subject.
	 *
	 * @param string $baseurl The baseurl, (excluding /core/controllinks), of the request
	 * @param mixed  $subject The subject matter of this hook, (if any)
	 *
	 * @return string HTML of the <ul/> tag.
	 */
	public static function DispatchAndFetch($baseurl, $subject){
		$links = HookHandler::DispatchHook('/core/controllinks' . $baseurl, $subject);

		$controls = new ViewControls();
		$controls->addLinks($links);

		return $controls->fetch();
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
class ViewControl implements ArrayAccess {

	/**
	 * Link for this control
	 *
	 * @var string
	 */
	public $link = '#';

	/**
	 * Title for this control
	 *
	 * @var string
	 */
	public $title = '';

	/**
	 * CSS class name for this control
	 *
	 * @var string
	 */
	public $class = '';

	/**
	 * Icon class name for this control
	 *
	 * Set to blank to omit the icon
	 *
	 * @var string
	 */
	public $icon = '';

	/**
	 * Confirm text for this link, useful for setting them as POST links.
	 *
	 * @var string
	 */
	public $confirm = '';

	/**
	 * Any other attributes for the a tag.
	 *
	 * @var array
	 */
	public $otherattributes = array();

	/**
	 * Get this control as HTML
	 *
	 * @return string (HTML)
	 */
	public function fetch(){
		$html = '';

		$html .= '<li' . ($this->class ? (' class="' . $this->class . '"') : '') . '>';
		if($this->link){
			$html .= $this->_fetchA();
		}

		if($this->icon){
			$html .= '<i class="icon-' . $this->icon . '"></i>';
		}

		$html .= '<span>' . $this->title . '</span>';

		// Close the a tag if it's a link
		if($this->link){
			$html .= '</a>';
		}

		// And close the li tag
		$html .= '</li>';

		return $html;
	}

	/**
	 * Fetch the A tag for this element.
	 *
	 * This is broken out into its own function since it has a decent amount of logic contained herein.
	 *
	 * @return string (HTML fragment)
	 */
	private function _fetchA(){
		if(!$this->link) return null;

		// Start by grabbing the "other" attributes, these will get overwrote with the specific elements.
		$dat = $this->otherattributes;

		if($this->confirm){
			$dat['onclick'] = "if(confirm('" . str_replace("'", "\\'", $this->confirm) . "')){" .
				"Core.PostURL('" . str_replace("'", "\\'", Core::ResolveLink($this->link)) . "');" .
				"} return false; ";
			$dat['href'] = '#';
		}
		else{
			$dat['href'] = $this->link;
		}

		$dat['title'] = $this->title;
		if($this->class) $dat['class'] = $this->class;

		$html = '<a ';
		foreach($dat as $k => $v){
			$html .= " $k=\"$v\"";
		}
		$html .= '>';
		return $html;
	}


	public function set($key, $value){
		switch($key){
			case 'class':
				$this->class = $value;
				break;
			case 'confirm':
				$this->confirm = $value;
				break;
			case 'icon':
				$this->icon = $value;
				break;
			case 'link':
			case 'href': // Just for an alias of the link.
				$this->link = Core::ResolveLink($value);
				break;
			case 'title':
				$this->title = $value;
				break;
			default:
				$this->otherattributes[$key] = $value;
				break;
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
		return(property_exists($this, $offset));
	}

	/**
	 * Offset to retrieve
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 *
	 * @param mixed $offset The offset to retrieve.
	 *
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset) {
		$dat = get_object_vars($this);

		if(isset($dat[$offset])){
			return $dat[$offset];
		}
		elseif(isset($this->otherattributes[$offset])){
			return $this->otherattributes[$offset];
		}
		else{
			return null;
		}
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
		$this->set($offset, $value);
	}

	/**
	 * Offset to unset
	 *
	 * This actually doesn't do anything.
	 *
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 *
	 * @param mixed $offset The offset to unset.
	 *
	 * @return void
	 */
	public function offsetUnset($offset) {
		return void;
	}
}
