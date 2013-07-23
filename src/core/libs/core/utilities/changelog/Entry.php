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

	const TYPE_OTHER       = 'Change';
	const TYPE_BUG         = 'Bug';
	const TYPE_FEATURE     = 'Feature';
	const TYPE_PERFORMANCE = 'Performance';
	const TYPE_SECURITY    = 'Security';

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
		$word1keywords = [
			'[bug]'         => [ 'type' => self::TYPE_BUG,         'trim' => true  ],
			'bug'           => [ 'type' => self::TYPE_BUG,         'trim' => true  ],
			'fix'           => [ 'type' => self::TYPE_BUG,         'trim' => false ],
			'added'         => [ 'type' => self::TYPE_FEATURE,     'trim' => false ],
			'[feature]'     => [ 'type' => self::TYPE_FEATURE,     'trim' => true  ],
			'feature'       => [ 'type' => self::TYPE_FEATURE,     'trim' => true  ],
			'[performance]' => [ 'type' => self::TYPE_PERFORMANCE, 'trim' => true  ],
			'performance'   => [ 'type' => self::TYPE_PERFORMANCE, 'trim' => true  ],
			'perf '         => [ 'type' => self::TYPE_PERFORMANCE, 'trim' => true  ],
			'[security]'    => [ 'type' => self::TYPE_SECURITY,    'trim' => true  ],
			'security'      => [ 'type' => self::TYPE_SECURITY,    'trim' => true  ],
			'sec '          => [ 'type' => self::TYPE_SECURITY,    'trim' => true  ],
		];

		foreach($word1keywords as $word => $dat){
			if(stripos($line, $word) === 0){
				$this->_type = $dat['type'];
				if($dat['trim']) $line = trim(substr($line, strlen($word)));
				break;
			}
		}

		if(!$this->_type){
			$phrases = [
				'fixes bug'   => self::TYPE_BUG,
				'fix bug'     => self::TYPE_BUG,
				'fixed bug'   => self::TYPE_BUG,
				'bug #'       => self::TYPE_BUG,
				'new feature' => self::TYPE_FEATURE,
				'feature #'   => self::TYPE_FEATURE,
				'performance' => self::TYPE_PERFORMANCE,
				'faster'      => self::TYPE_PERFORMANCE,
				'secure'      => self::TYPE_SECURITY,
				'security'    => self::TYPE_SECURITY,
			];

			foreach($phrases as $word => $type){
				if(stripos($line, $word) !== false){
					$this->_type = $type;
					break;
				}
			}
		}


		// Still empty?
		if(!$this->_type){
			$this->_type = self::TYPE_OTHER;
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
