<?php
/**
 * File for class Entry definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130409.1100
 * @package Core\Utilities\Changelog
 */

namespace Core\Utilities\Changelog;


/**
 * Class Entry description
 * 
 * @package Core\Utilities\Changelog
 */
class Entry {

	const TYPE_OTHER   = 'Change';
	const TYPE_BUG     = 'Bug';
	const TYPE_FEATURE = 'Feature';

	private $_comment;

	private $_type;

	/**
	 * Parse a line for either a current CHANGELOG file or a user-submitted line.
	 *
	 * @param $line
	 */
	public function parseLine($line){
		// Parse is meant to be a new entry, wipe out any existing data here.
		$this->_comment = '';
		$this->_type = '';

		if(strpos($line, '* ') === 0){
			// line starts with *[space], it's an entry!
			// But we already knew that here.
			$line = substr($line, 2);
		}

		// First-lines of the entry may have some special keywords.
		if(stripos($line, '[bug]') === 0){
			$this->_type = self::TYPE_BUG;
			$line = trim(substr($line, 5));
		}
		elseif(stripos($line, 'bug') === 0){
			$this->_type = self::TYPE_BUG;
			$line = trim(substr($line, 3));
		}
		elseif(stripos($line, 'fix') === 0){
			$this->_type = self::TYPE_BUG;
		}
		elseif(stripos($line, 'added') === 0){
			$this->_type = self::TYPE_FEATURE;
		}
		elseif(stripos($line, '[feature]') === 0){
			$this->_type = self::TYPE_FEATURE;
			$line = trim(substr($line, 9));
		}
		elseif(stripos($line, 'feature') === 0){
			$this->_type = self::TYPE_FEATURE;
			$line = trim(substr($line, 7));
		}

		$this->_comment = $line;
	}

	/**
	 * Append a line to this entry.
	 * Useful because changelogs are wordwrapped.
	 *
	 * @param $line
	 */
	public function appendLine($line){
		$this->_comment .= ' ' . $line;
	}

	/**
	 * Get this entry as a single line with the prefix type.
	 * @return string
	 */
	public function getLine(){

		$type = $this->getType();
		$line = $this->_comment;

		if($type != self::TYPE_OTHER){
			$line = '[' . $type . '] ' . $line;
		}

		return $line;
	}

	public function getLineFormatted(){
		/// hehehe, just because I can do this all in one "line".... :p
		return "\t* " .
			implode(
				"\n\t  ",
				array_map(
					'trim',
					explode(
						"\n",
						wordwrap($this->getLine(), 100, "\n")
					)
				)
			);
	}

	/**
	 * Get the type of this entry
	 *
	 * @return string
	 */
	public function getType(){
		if($this->_type == ''){
			// And if the type isn't set already...
			// This handles a more of a linguistic analysis approach, trying to decypher what the line says.
			if(stripos($this->_comment, 'fixes bug') !== false){
				$this->_type = self::TYPE_BUG;
			}
			elseif(stripos($this->_comment, 'fix bug') !== false){
				$this->_type = self::TYPE_BUG;
			}
			elseif(stripos($this->_comment, 'fixed bug') !== false){
				$this->_type = self::TYPE_BUG;
			}
			elseif(stripos($this->_comment, 'new feature') !== false){
				$this->_type = self::TYPE_FEATURE;
			}
			else{
				$this->_type = self::TYPE_OTHER;
			}
		}

		return $this->_type;
	}

	/**
	 * Get just the comment of this entry
	 *
	 * @return mixed
	 */
	public function getComment(){
		return $this->_comment;
	}
}
