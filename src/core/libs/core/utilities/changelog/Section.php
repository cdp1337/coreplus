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
	 * @var ChangelogEntry
	 */
	private $_lastentry;

	public function parseHeader($line){
		// The name of this section will be the first part followed by one space, followed by any non-space character.
		// ie: Core Plus 1.2.3
		$version = preg_replace('/^.* ([^ ]*)$/', '$1', $line);
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
	 * @param $line
	 */
	public function addLine($line){
		if(trim($line) == '') return;

		$this->_lastentry = new Entry();
		$this->_lastentry->parseLine($line);

		// And append it to the list of entries.
		$this->_entries[] = $this->_lastentry;
	}

	public function clearEntries(){
		$this->_entries = array();
		$this->_lastentry = null;
	}

	public function getVersion(){
		return $this->_version;
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

		$bugs = [];
		$features = [];
		$other = [];

		// First for sortting them and putting them in the right category.
		foreach($this->_entries as $e){
			switch($e->getType()){
				case Entry::TYPE_BUG:
					$bugs[] = $e->getLineFormatted();
					break;
				case Entry::TYPE_FEATURE:
					$features[] = $e->getLineFormatted();
					break;
				default:
					$other[] = $e->getLineFormatted();
					break;
			}
		}

		// Because I want bugs, then features, then other.
		foreach(array_merge($bugs, $features, $other) as $line){
			$out .= $line . "\n";
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
	 * @return string (HTML)
	 */
	public function fetchAsHTML(){
		$out = '';

		$out .= '<h2>' . $this->_name . ' ' . $this->_version . '</h2>' . "\n";
		if($this->_packageddate){
			$out .= sprintf(
				'<p>Packaged by %s on %s</p>',
				$this->_packagername,
				$this->_packageddate
			);
		}

		$bugs = [];
		$features = [];
		$other = [];

		// First for sortting them and putting them in the right category.
		foreach($this->_entries as $e){
			switch($e->getType()){
				case Entry::TYPE_BUG:
					$bugs[] = $e;
					break;
				case Entry::TYPE_FEATURE:
					$features[] = $e;
					break;
				default:
					$other[] = $e;
					break;
			}
		}

		// Because I want bugs, then features, then other.
		$out .= '<ul>' . "\n";
		foreach(array_merge($bugs, $features) as $line){
			$out .= sprintf("\t<li><b>%s</b> - %s</li>\n", $line->getType(), $line->getComment());
		}
		foreach($other as $line){
			$out .= sprintf("\t<li>%s</li>\n", $line->getComment());
		}
		$out .= '</ul>';

		return $out;
	}
}
