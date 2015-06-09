<?php
/**
 * File for class VersionString definition in the coreplus project
 * 
 * @package Core
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20140414.1544
 * @copyright Copyright (C) 2009-2015  Charlie Powell
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


/**
 * A short teaser of what VersionString does.
 *
 * More lengthy description of what VersionString does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for VersionString
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
 * @package Core
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class VersionString implements \ArrayAccess {
	/** @var int X.n.n number of the version. */
	public $major;
	/** @var int n.X.n number of the version. */
	public $minor = 0;
	/** @var int n.n.X number of the version. */
	public $point = 0;
	/** @var string|null User "~" fork of the version. */
	public $user;
	/** @var string|null A single or couple characters illustrating the stability version. */
	public $stability;
	/** @var string|null The build string, if applicable. */
	public $build;

	public function __construct($version = null){
		if($version){
			$this->parseString($version);
		}
	}

	/**
	 * Get this version as a string
	 *
	 * @return string
	 */
	public function __toString(){
		$ret = $this->major . '.' . $this->minor . '.' . $this->point;

		if($this->stability){
			$ret .= $this->stability;
		}
		if($this->build){
			$ret .= '.' . $this->build;
		}
		if($this->user){
			$ret .= '~' . $this->user;
		}

		return $ret;
	}

	/**
	 * Break a version string into the corresponding parts.
	 *
	 * Major Version
	 * Minor Version
	 * Point Release
	 * Core Version
	 * Developer-Specific Version
	 * Development Status
	 *
	 * Optimized 2013.08.17
	 *
	 * @param string $version
	 *
	 * @return array
	 */
	public function parseString($version) {
		// dev < alpha = a < beta = b < RC = rc < # < pl = p

		$parts = explode('.', strtolower($version));

		// This version of the code executes about twice as fast as the traditional foreach version below!

		if(isset($parts[0])){
			$this->major = $parts[0];
		}
		if(isset($parts[1])){
			if(is_numeric($parts[1])){
				// Strictly an int... process as usual.
				$this->minor = $parts[1];
			}
			else{
				$digit = $parts[1];

				if(($pos = strpos($digit, '~')) !== false){
					$this->minor = substr($digit, 0, $pos);
					$this->user = substr($digit, $pos);
				}
				elseif(($pos = strpos($digit, 'a')) !== false){
					$this->minor = substr($digit, 0, $pos);
					$this->stability = substr($digit, $pos);
				}
				elseif(($pos = strpos($digit, 'b')) !== false){
					$this->minor = substr($digit, 0, $pos);
					$this->stability = substr($digit, $pos);
				}
				elseif(($pos = strpos($digit, 'rc')) !== false){
					$this->minor = substr($digit, 0, $pos);
					$this->stability = substr($digit, $pos);
				}
			}
		}
		if(isset($parts[2])){
			if(is_numeric($parts[2])){
				// Strictly an int... process as usual.
				$this->point = $parts[2];
			}
			else{
				$digit = $parts[2];

				if(($pos = strpos($digit, '~')) !== false){
					$this->point = substr($digit, 0, $pos);
					$this->user = substr($digit, $pos);
				}
				elseif(($pos = strpos($digit, 'a')) !== false){
					$this->point = substr($digit, 0, $pos);
					$this->stability = substr($digit, $pos);
				}
				elseif(($pos = strpos($digit, 'b')) !== false){
					$this->point = substr($digit, 0, $pos);
					$this->stability = substr($digit, $pos);
				}
				elseif(($pos = strpos($digit, 'rc')) !== false){
					$this->point = substr($digit, 0, $pos);
					$this->stability = substr($digit, $pos);
				}
			}
		}
		if(isset($parts[3])){
			if(is_numeric($parts[3])){
				// Strictly an int... process as usual.
				$this->build = $parts[3];
			}
			else{
				$digit = $parts[3];

				if(($pos = strpos($digit, '~')) !== false){
					$this->build = substr($digit, 0, $pos);
					$this->user = substr($digit, $pos);
				}
				else{
					$this->build = $digit;
				}
			}
		}
	}

	/**
	 * @param int $int
	 */
	public function setMajor($int){
		$this->major = $int;
	}

	/**
	 * @param int $int
	 */
	public function setMinor($int){
		$this->minor = $int;
	}

	/**
	 * @param int $int
	 */
	public function setPoint($int){
		$this->point = $int;
	}

	/**
	 * @param string|null $string
	 */
	public function setUser($string){
		$this->user = $string;
	}

	/**
	 * @param string|null $string
	 */
	public function setBuild($string){
		$this->build = $string;
	}

	/**
	 * @param string|null $type
	 */
	public function setStability($type){
		$type = strtolower($type);
		switch($type){
			case 'dev':
				$this->stability = 'dev';
				break;
			case 'a':
			case 'alpha':
				$this->stability = 'a';
				break;
			case 'b':
			case 'beta':
				$this->stability = 'b';
				break;
			case 'rc':
				$this->stability = 'rc';
				break;
			default:
				$this->stability = null;
				break;
		}
	}

	/**
	 * Mimic php's version_compare, only with more advanced and accurate version comparisons.
	 *
	 * This has the additional support for Debian-style version strings.
	 *
	 * @param string|VersionString $other     Version to compare against
	 * @param null|string          $operation Operation to use or null
	 *
	 * @return bool|int Boolean if $operation is provided, int if omitted.
	 */
	public function compare($other, $operation = null){

		if(!$other instanceof VersionString){
			// Make sure that the other version is actually a Version object.
			$other = new VersionString($other);
		}

		// version1 and 2 are now standardized.
		//$keys = array('major', 'minor', 'point', 'core', 'user', 'stability');

		// @todo Support user and stability checks.

		// The standard keys I can compare pretty easily.
		$v1    = $this->major . '.' . $this->minor . '.' . $this->point;
		$v2    = $other->major . '.' . $other->minor . '.' . $other->point;
		$check = version_compare($v1, $v2);

		// If both upstream versions are identical, drop into the "user" version, (or core-specific).
		// This is used as both user and core versions because both essentially indicate the same thing;
		// that the original package maintainer of the project is *not* the one creating the core plus package.

		// If the check is the same and one or the other version doesn't care about the user string....
		// don't even run the check, they're close enough.
		// If both strings request the user string, then check it too.
		// This is done so that maintainer X can say a package requires AcmeABC version 1.2, without
		// distinguishing if maintainer Y or maintainer Z did the actual package creation.
		// HOWEVER if versions 1.2.0~core3 and 1.2.0~core5 are compared, it'll use the user version.
		if($check == 0 && $this->user && $other->user){
			$check = version_compare($this->user, $other->user);
		}

		// Apply the same to stability
		if($check == 0 && ($this->stability || $other->stability)){
			$check = version_compare($this->stability, $other->stability);
		}

		// Will preserve PHP's -1, 0, 1 nature.
		if ($operation === null){
			return $check;
		}
		elseif($check == -1){
			// v1 is less than v2...
			switch($operation){
				case 'lt':
				case '<':
				case 'le':
				case '<=':
					return true;
				default:
					return false;
			}
		}
		elseif($check == 0){
			// v1 is identical to v2...
			switch($operation){
				case 'le':
				case '<=':
				case 'eq':
				case '=':
				case '==':
				case 'ge':
				case '>=':
					return true;
				default:
					return false;
			}
		}
		else{
			// v1 is greater than v2...
			switch($operation){
				case 'ge':
				case '>=':
				case 'gt':
				case '>':
					return true;
				default:
					return false;
			}
		}
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Whether a offset exists
	 * @link http://php.net/manual/en/arrayaccess.offsetexists.php
	 *
	 * @param mixed $offset <p>
	 *                      An offset to check for.
	 *                      </p>
	 *
	 * @return boolean true on success or false on failure.
	 * </p>
	 * <p>
	 * The return value will be casted to boolean if non-boolean was returned.
	 */
	public function offsetExists($offset) {
		return property_exists($this, $offset);
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to retrieve
	 * @link http://php.net/manual/en/arrayaccess.offsetget.php
	 *
	 * @param mixed $offset <p>
	 *                      The offset to retrieve.
	 *                      </p>
	 *
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset) {
		return $this->$offset;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to set
	 * @link http://php.net/manual/en/arrayaccess.offsetset.php
	 *
	 * @param mixed $offset <p>
	 *                      The offset to assign the value to.
	 *                      </p>
	 * @param mixed $value  <p>
	 *                      The value to set.
	 *                      </p>
	 *
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		$this->$offset = $value;
	}

	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Offset to unset
	 * @link http://php.net/manual/en/arrayaccess.offsetunset.php
	 *
	 * @param mixed $offset <p>
	 *                      The offset to unset.
	 *                      </p>
	 *
	 * @return void
	 */
	public function offsetUnset($offset) {
		$this->$offset = null;
	}
}