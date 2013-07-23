<?php
/**
 * File for class Section definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130409.1100
 * @package Core\Utilities\Changelog
 */

namespace Core\Utilities\Changelog;


/**
 * Class Section description
 * 
 * @package Core\Utilities\Changelog
 */
class Section {
	/**
	 * The name of the component or theme.
	 * @var string
	 */
	private $_name;
	/**
	 * The version of this changelog entry.
	 * @var
	 */
	private $_version;

	private $_entries = [];

	private $_packagername;

	private $_packageremail;

	private $_packageddate;


	/**
	 * The last entry processed, useful for parseLine and its continuation ability.
	 * @var Entry
	 */
	private $_lastentry;

	public function parseHeader($line){
		// The name of this section will be the first part followed by one space, followed by any non-space character.
		// ie: Core Plus 1.2.3
		$version = preg_replace('/^.* ([^ ]+)$/', '$1', $line);
		$name = substr($line, 0, (-1-strlen($version)));

		$this->_name = $name;
		$this->_version = $version;
	}

	/**
	 * Parse (and add), a line from the CHANGELOG format.
	 * Can handle being called multiple times for the same continued line.
	 *
	 * @param $line
	 */
	public function parseLine($line){
		$line = trim($line); // trim whitespace and newlines.
		if(strpos($line, '--') === 0){
			// Line starts with --, it's the "released" timestamp!
			$line = trim(substr($line, 2));

			if(preg_match('/^([^<]*) <([^>]*)>  ([a-z0-9:\+\-, ]*)$/i', $line, $matches)){
				$this->_packagername  = $matches[1];
				$this->_packageremail = $matches[2];
				$this->_packageddate  = $matches[3];
			}
			//else{
			//	echo '!!Unsupported release date format, ' . $line . "\n";
			//}
		}
		elseif(strpos($line, '* ') === 0){
			// line starts with *[space], it's a new entry!
			$this->_lastentry = new Entry();
			$this->_lastentry->parseLine($line);

			// And append it to the list of entries.
			$this->_entries[] = $this->_lastentry;
		}
		elseif($this->_lastentry){
			// Ok, I'll accept a continuation of the last entry.
			$this->_lastentry->appendLine($line);
		}
	}

	/**
	 * Add a single line that's just a plain string.
	 * Meant to be called with user-submitted data.
	 *
	 * This can be called with duplicate lines and it will not produce duplicate entries.
	 *
	 * @param $line
	 */
	public function addLine($line){
		if(trim($line) == '') return;

		$entry = new Entry();
		$entry->parseLine($line);

		// Before I go and add it, I need to skim through the current set of entries and see if this one matches.
		$newlineformatted = $entry->getLine();
		foreach($this->_entries as $e){
			/** @var $e Entry */
			if($e->getLine() == $newlineformatted){
				// Abort the addition!
				return;
			}
		}

		// Otherwise... all good!
		$this->_lastentry = $entry;

		// And append it to the list of entries.
		$this->_entries[] = $entry;
	}

	public function clearEntries(){
		$this->_entries = array();
		$this->_lastentry = null;
	}

	/**
	 * Get the version string of this section
	 *
	 * @return mixed
	 */
	public function getVersion(){
		return $this->_version;
	}

	/**
	 * Get the released/packaged date of this changelog section.
	 *
	 * @return mixed
	 */
	public function getReleasedDate(){
		return $this->_packageddate;
	}

	/**
	 * Fetch this section as a plain string.
	 *
	 * @return string
	 */
	public function fetch(){
		$out = '';

		foreach($this->_entries as $e){
			$out .= $e->getLine() . "\n";
		}

		return $out;
	}

	/**
	 * Fetch this section as a fully formatted string.
	 *
	 * @return string
	 */
	public function fetchFormatted(){
		$out = $this->_name . ' ' . $this->_version . "\n\n";

		// Because I want bugs, then performance notes, then features, then other.
		foreach($this->_getEntriesSorted() as $e){
			/** @var $e Entry */
			$out .= $e->getLineFormatted() . "\n";
		}

		if($this->_packageddate){
			$out .= sprintf(
				"\t-- %s <%s>  %s\n",
				$this->_packagername,
				$this->_packageremail,
				$this->_packageddate
			);
		}

		return $out;
	}

	/**
	 * Fetch this changelog section as HTML; useful for reports.
	 *
	 * @param int $startinglevel The starting <h#> level to start with.
	 *
	 * @return string (HTML)
	 */
	public function fetchAsHTML($startinglevel = 2){
		$out = '';

		$out .= '<h' . $startinglevel . '>' . $this->_name . ' ' . $this->_version . '</h' . $startinglevel . '>' . "\n";
		if($this->_packageddate){
			$out .= sprintf(
				'<p>Packaged by %s on %s</p>',
				$this->_packagername,
				$this->_packageddate
			);
		}

		// Because I want bugs, then features, then other.
		$out .= '<ul>' . "\n";
		foreach($this->_getEntriesSorted() as $e){
			/** @var $e Entry */
			if($e->getType() == Entry::TYPE_OTHER){
				$out .= sprintf("\t<li>%s</li>\n", $e->getComment());
			}
			else{
				$out .= sprintf("\t<li><b>%s</b> - %s</li>\n", $e->getType(), $e->getComment());
			}
		}
		$out .= '</ul>';

		return $out;
	}

	/**
	 * Get the entries of this section sorted by importance.
	 *
	 * @return array
	 */
	private function _getEntriesSorted(){
		$security = [];
		$bugs     = [];
		$perfs    = [];
		$features = [];
		$other    = [];

		// First for sortting them and putting them in the right category.
		foreach($this->_entries as $e){
			/** @var $e Entry */
			switch($e->getType()){
				case Entry::TYPE_SECURITY:
					$security[] = $e;
					break;
				case Entry::TYPE_BUG:
					$bugs[] = $e;
					break;
				case Entry::TYPE_PERFORMANCE:
					$perfs[] = $e;
					break;
				case Entry::TYPE_FEATURE:
					$features[] = $e;
					break;
				default:
					$other[] = $e;
					break;
			}
		}

		// Order:
		// 1) Security Fixes
		// 2) Bugs
		// 3) Performance
		// 4) Features
		// 5) Everything else
		return array_merge($security, $bugs, $perfs, $features, $other);
	}
}
