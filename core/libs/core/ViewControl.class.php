<?php
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
				$this->link = $value;
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
