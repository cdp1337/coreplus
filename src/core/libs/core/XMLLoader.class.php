<?php
/**
 * The XMLLoader object, capable of easily loading and saving XML data documents.
 *
 * This class is slightly more complex than the SimpleXML system in PHP5, but simplier than direct DOM manipulation.
 *
 * @todo Make use of some form of caching.
 *       Ideally each file set can have a cache TTL, and the contents of the XML
 *       file or the DOM itself is contained in memory for that set amount of time.
 *
 * @package Core
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2013  Charlie Powell
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

class XMLLoader implements Serializable {
	/**
	 * The name of the root node.
	 * This IS required for loading the xml file, as every file MUST have exactly one root node.
	 *
	 * @var string
	 */
	protected $_rootname;

	/**
	 * The filename of the original XML file.
	 * This IS required and is used in loading and saving of the data.
	 *
	 * @var string
	 */
	protected $_filename;

	/**
	 * The file object of this XML Loader.
	 * This is an option parameter for advanced usage, (ie: loading an XML file from a remote server).
	 *
	 * @var \Core\Filestore\File
	 */
	protected $_file;

	/**
	 * The original DOM document for this object.
	 *
	 * @var DOMDocument
	 */
	protected $_DOM;

	/**
	 * Root node cache, used to make the getRootDOM faster by bypassing the lookup.
	 * @var null|DOMElement
	 */
	private $_rootnode = null;

	/**
	 * Set this to a valid URL string to ensure that the document is set that for its root node.
	 *
	 * @var null|string
	 */
	protected $_schema = null;


	/************ SERIALIZE and UNSERIALIZE METHODS **********/

	/**
	 * Serialize this object, preserving the underlying DOMDocument, (which otherwise wouldn't be perserved).
	 *
	 * @return string
	 */
	public function serialize(){
		$dat = array(
			'rootname' => $this->_rootname,
			'filename' => $this->_filename,
			'file' => $this->_file,
			'dom' => $this->getDOM()->saveXML()
		);

		// Compress the dom XML, since they may get rather large.
		// This function expects alphanumeric output, so gzip is out of the question.
		// HOWEVER, I can base64 the gzip'd data, and since gzip compression offers about 400% compression for text and
		// b64 inflates data by about 30%, I'm still left with data that is about 33% smaller total :)
		$dat['dom'] = base64_encode(gzcompress($dat['dom']));
		return serialize($dat);
	}

	/**
	 * Magic method called to convert a serialized object back to a valid XMLLoader object.
	 *
	 * @param string $serialized
	 *
	 * @return mixed|void
	 */
	public function unserialize($serialized){
		$dat = unserialize($serialized);
		$this->_rootname = $dat['rootname'];
		$this->_filenme = $dat['filename'];
		$this->_file = $dat['file'];
		$this->_rootnode = null;
		$this->_DOM = new DOMDocument();
		$this->_DOM->formatOutput = true;
		$this->_DOM->loadXML(gzuncompress(base64_decode($dat['dom'])));
	}


	/**
	 * Setup the internal DOMDocument for usage.
	 *
	 * This MUST be called before any operations are applied to this object!
	 *
	 * @return bool
	 */
	public function load() {
		// I need a filename.
		// Actually I don't........ creating a DOM on-the-fly is a possible use of this class too...... 0.o
		//if(!$this->_filename) return false;

		// I need a root node name.
		if (!$this->_rootname) return false;

		// w00t, new support for a schema declaration!
		if($this->_schema){
			$implementation = new DOMImplementation();
			$dtd = $implementation->createDocumentType($this->_rootname, 'SYSTEM', $this->_schema);
			$this->_DOM = $implementation->createDocument('', '', $dtd);
		}
		else{
			$this->_DOM = new DOMDocument();
		}

		$this->_DOM->encoding = 'UTF-8';

		// we want a nice output
		$this->_DOM->formatOutput = true;

		if ($this->_file) {
			$contents = $this->_file->getContentsObject();
			if (is_a($contents, '\Core\Filestore\Contents\ContentGZ')) {
				$dat = $contents->uncompress();
			}
			else {
				$dat = $contents->getContents();
			}

			// If an empty string is submitted...
			if(!$dat){
				return false;
			}

			$this->_DOM->loadXML($dat);
		}
		elseif ($this->_filename) {
			if (!$this->_DOM->load($this->_filename)) return false;
		}
		else {
			return false;
		}

		return true;
	}

	/**
	 * Load the document from a valid File object or a filename.
	 *
	 * @param \Core\Filestore\File|string $file
	 * @return bool
	 */
	public function loadFromFile($file) {
		if (is_a($file, '\\Core\\Filestore\\File')) {
			$this->_file = $file;
		}
		else {
			// Make sure the file is fully resolved.
			// To do that, Core has some built in systems.
			$this->_file = \Core\Filestore\Factory::File($file);
			//$this->_filename = $file;
		}

		return $this->load();
	}

	/**
	 * Load from a DOMNode
	 *
	 * @param DOMNode $node
	 *
	 * @return bool
	 */
	public function loadFromNode(DOMNode $node) {
		// Save the DOM object so I have it in the future.
		$this->_DOM = new DOMDocument();

		// we want a nice output
		$this->_DOM->formatOutput = true;

		$nn = $this->_DOM->importNode($node, true);
		$this->_DOM->appendChild($nn);

		return true;
	}

	/**
	 * Load from an XML string
	 *
	 * @param $string
	 *  @return bool
	 */
	public function loadFromString($string){
		// I need a root node name.
		if (!$this->_rootname) return false;

		// Save the DOM object so I have it in the future.
		$this->_DOM = new DOMDocument();

		// we want a nice output
		$this->_DOM->formatOutput = true;

		$this->_DOM->loadXML($string);

		return true;
	}

	/**
	 * Set the filename for this XML document
	 *
	 * @param string $file
	 */
	public function setFilename($file) {
		$this->_filename = $file;
	}

	/**
	 * Set the root name for this XML document
	 *
	 * @param string $name
	 */
	public function setRootName($name) {
		$this->_rootname = $name;
	}

	/**
	 * Method to set the schema externally.
	 *
	 * This will update the DOM object if it's different.
	 *
	 * @param $url
	 */
	public function setSchema($url){
		$this->_schema = $url;

		// Update the document if it was already loaded!
		if($this->_DOM !== null && $this->_schema != $this->_DOM->doctype->systemId){
			$implementation = new DOMImplementation();
			$dtd = $implementation->createDocumentType($this->_rootname, 'SYSTEM', $this->_schema);
			$newdom = $implementation->createDocument('', '', $dtd);

			// Now I can migrate the data from the previous DOM to the new one.
			//$string = $this->_DOM->saveXML($this->_DOM->getElementsByTagName($this->_rootname)->item(0));
			$root = $this->_DOM->getElementsByTagName($this->_rootname)->item(0);

			$newroot = $newdom->importNode($root, true);
			$newdom->appendChild($newroot);

			$this->_DOM = $newdom;
			$this->_rootnode = null;
		}
	}

	/**
	 * Get the DOM root node
	 *
	 * @return DOMElement
	 */
	public function getRootDOM() {

		// First of all, if the dom hasn't been set yet...
		if($this->_DOM === null){
			$this->load();
		}

		if($this->_rootnode === null){
			$root = $this->_DOM->getElementsByTagName($this->_rootname);
			if ($root->item(0) === null) {
				$root = $this->_DOM->createElement($this->_rootname);
				$this->_DOM->appendChild($root);

				$this->_rootnode = $root; // Because it's already the item.
			}
			else {
				$this->_rootnode = $root->item(0);
			}
		}

		return $this->_rootnode;
	}

	/**
	 * Get the complete DOM object.
	 *
	 * @return DOMDocument
	 */
	public function getDOM() {
		return $this->_DOM;
	}

	/**
	 * Searches for all elements with given tag name
	 *
	 * @param string $name
	 *
	 * @return DOMNodeList
	 */
	public function getElementsByTagName($name) {
		return $this->_DOM->getElementsByTagName($name);
	}

	/**
	 * Get the first element with the given tag name.
	 *
	 * @param string $name
	 *
	 * @return DOMNode
	 */
	public function getElementByTagName($name) {
		return $this->_DOM->getElementsByTagName($name)->item(0);
	}

	/**
	 * This behaves just like getElementByTagName, with the exception that you can pass
	 * '/' seperated paths of a node you want.
	 * In simple, you can send it book/chapter/page and it will find the first book and its first chapter and its first page.
	 *
	 * In addition, if the node does not exist it will be created automagically.
	 *
	 * In addition, you can send arbitrary attributes and their values.
	 * It will search for those, and again create them if they don't exist.
	 *
	 * Everything is relative to the root, and /book is the same as book.
	 *
	 * Examples:
	 * <code>
	 * // XML:
	 * // <book>
	 * //   <chapter chapter="1">
	 * //     <page number="1">...</page>
	 * //     ...
	 * //     <page number="25">...</page>
	 * //   </chapter>
	 * // </book>
	 *
	 * $this->getElement('page'); // Will return page 1 of chapter 1.
	 * $this->getElement('chapter[chapter=1]/page[number=25]'); // Will return page 25 of chapter 1.
	 * </code>
	 *
	 * @param string  $path
	 * @param boolean $autocreate Automatically create the element if it does not exist.
	 *
	 * @return DOMElement
	 */
	public function getElement($path, $autocreate = true) {
		return $this->getElementFrom($path, false, $autocreate);
	}

	/*
	protected function getElement($path, $autocreate = true){
		return $this->getElementFrom($path, $this->getRootDOM(), $autocreate);
	}
	*/

	/**
	 * Lookup an element using XPath.
	 *
	 * @param string  $path The path to search for.
	 * @param DOMNode $el The element to search for the path in.
	 * @param boolean $autocreate Automatically create the element if it does not exist.
	 *
	 * @return DOMElement
	 */
	public function getElementFrom($path, $el = false, $autocreate = true) {
		// I need something to start from...
		if (!$el) $el = $this->getRootDOM();

		$path = $this->_translatePath($path);

		$list = $this->getElementsFrom($path, $el);
		if ($list->item(0)) return $list->item(0);

		// Not found and autocreate is set to false.
		if (!$autocreate) return null;

		// User choose to create it if it didn't exist.... so fire it up!
		return $this->createElement($path, $el);
	}

	/**
	 * Ensure a path is a valid one and absolute to the root node or relative.
	 *
	 * @param string $path
	 * @return string
	 */
	private function _translatePath($path) {

		// Translate a single prepending slash to double slash, (means root path).
		if (preg_match(':^/[^/]:', $path)) {
			if(strpos($path,  '/' . $this->getRootDOM()->tagName) === 0){
				// Path already has the root node before it, do not add another, (it just needs another slash).
				$path = '/' . $path;
			}
			else{
				$path = '//' . $this->getRootDOM()->tagName . $path;
			}
		}
		return $path;
	}


	/**
	 * Create an XML node based on the given path.
	 *
	 * This will by default not create duplicate nodes of the same name, but can be forced to by using the $forcecreate option.
	 *
	 * @param string $path        Pathname to create, should be absolutely resolved if no $el is provided, otherwise relative is preferred.
	 * @param bool   $el          Element to create this node as a child of, set to false to just use root node.
	 * @param int    $forcecreate Instructions on how to handle duplicate nodes.
	 *                            0 - do not create any duplicate nodes, ie: unique attributes have to exist to create a different node
	 *                            1 - create duplicate a node at the final tree level, (useful for nodes with no attributes)
	 *                            2 - create all duplicate nodes from the root level on up, useful for creating completely different trees
	 *
	 * @return bool|DOMElement|DOMNode
	 * @throws Exception
	 */
	public function createElement($path, $el = false, $forcecreate = 0) {
		// I need something to start from...
		if (!$el){
			// No element, piece of cake!  Just grab the root node and drop off that node from the translated path.
			$el = $this->getRootDOM();
			$path = $this->_translatePath($path);

			// The path should be absolutely resolved, but not necessarily required.
			if(strpos($path, '//' . $this->getRootDOM()->nodeName) === 0){
				// I can safely trim that part off.
				$path = substr($path, strlen($this->getRootDOM()->nodeName) + 3);
			}
		}
		else{
			// I need to verify that the two are compatible.
			$path = $this->_translatePath($path);

			// In this case, (an element was requested), it cannot be fully resolved!
			// Unless of course it was the root node to begin with.
			if($el == $this->getRootDOM()){
				// The path should be absolutely resolved, but not necessarily required.
				if(strpos($path, '//' . $this->getRootDOM()->nodeName) === 0){
					// I can safely trim that part off.
					$path = substr($path, strlen($this->getRootDOM()->nodeName) + 3);
				}
			}
			elseif($path{0} == '/'){
				throw new Exception('Unable to append path ' . $path . ' onto an element from an absolute url!');
			}
		}


		if($forcecreate == 0){
			$createlast = false;
			$createall  = false;
		}
		elseif($forcecreate == 1){
			$createlast = true;
			$createall  = false;
		}
		elseif($forcecreate == 2){
			$createlast = true;
			$createall  = true;
		}
		else{
			throw new Exception('Unknown value provided for $forcecreate, please ensure it is one of the following [0, 1, 2]');
		}


		// Starting element, can be the root node or the current element it's at.
		//$el = $this->getRootDOM();
		$xpath = new DOMXPath($this->_DOM);

		//echo "Incoming path $path\n"; // DEBUG //

		// I can't just do a simple explode, as '/node/subnode[@something="here/there/blah"]' is a valid XQuery string.
		$patharray = array();
		if (strpos($path, '/') === false) {
			// Easy one, only 1 node depth requested.
			$patharray[] = $path;
		}
		elseif (strpos($path, '[') === false) {
			// Not as difficult, there are no attributes so I can just do an explode like usual.
			$patharray = explode('/', $path);
		}
		else {
			//$prevchar;
			$len    = strlen($path);
			$inatt  = false;
			$curstr = '';
			//echo "STRLEN is $len...\n"; // DEBUG //
			for ($x = 0; $x < $len; $x++) {
				$chr = $path{$x};
				if ($chr == '/' && !$inatt && $curstr) {
					$patharray[] = $curstr;
					$curstr      = '';
				}
				elseif ($chr == '[') {
					$inatt = true;
					$curstr .= $chr;
				}
				elseif ($chr == ']') {
					$inatt = false;
					$curstr .= $chr;
				}
				else {
					$curstr .= $chr;
				}
			}
			// Don't forget the final one... (if there's no trailing '/'  there will still be information in the buffer)
			if ($curstr) {
				$patharray[] = $curstr;
				$curstr      = '';
			}
		}


		//var_dump($patharray); // DEBUG //


		foreach ($patharray as $k => $s) {
			//echo "Querying for element " . $s . "\n";
			//if($s == '*') return $el->childNodes;

			// Skip blanks.
			if ($s == '') continue;

			//var_dump($s); // DEBUG //

			$entries = $xpath->query($s, $el);
			if (!$entries) {
				trigger_error("Invalid query - " . $s, E_USER_WARNING);
				return false;
			}
			if (
				$entries->item(0) == null ||
				$createall ||
				($createlast && $k == sizeof($patharray) - 1)
			) {

				// :( it doesn't exist.... guess we'll have to create it!
				// Did the user request attributes on this element?
				if (strpos($s, '[') !== false) {
					// This will get just the name of the tag the user requested.
					$tag = trim(substr($s, 0, strpos($s, '[')));

					// And create the new node
					$node = $this->_DOM->createElement($tag);

					// This will get each attribute and its value so I can add it to the node.
					//preg_match_all('/([^=,\]]*)=([^\[,]*)/', substr($s, strpos($s, '[')+1, (strpos($s, ']') - strpos($s, '[') - 1 ) ), $matches);
					preg_match_all('/\[([^=,\]]*)=([^\[]*)\]/', $s, $matches);
					foreach ($matches[1] as $k => $v) {
						$node->setAttribute(trim(trim($v), '@'), trim(trim($matches[2][$k]), '"'));
					}
				}
				else {
					// No attributes... much easier of a process.
					$tag = trim($s);

					// And create the new node
					$node = $this->_DOM->createElement($tag);
				}

				// And finally I can add this node to the current stack position.
				$el->appendChild($node);

				// And the el is now this newly created node.
				$el = $node;
			}
			else {
				// Even easier... it found it!
				$el = $entries->item(0);
			}

		}
		return $el;
	}

	/**
	 * @param $path
	 *
	 * @return DOMNodeList
	 */
	public function getElements($path) {
		return $this->getElementsFrom($path, $this->getRootDOM());
	}

	/**
	 * @param string       $path The path to search for.
	 * @param bool|DomNode $el   The element to start the search in, defaults to the root node.
	 *
	 * @return DOMNodeList
	 */
	public function getElementsFrom($path, $el = false) {
		if (!$el) $el = $this->getRootDOM();

		$path = $this->_translatePath($path);

		// First thing's first, trim the prepending and trailing '/'s... they're unneeded.
		//$path = trim($path, '/');
		// Starting element, can be the root node or the current element it's at.
		//$el = $this->getRootDOM();
		$xpath   = new DOMXPath($this->_DOM);
		$entries = $xpath->query($path, $el);
		return $entries;

	}

	/**
	 * Remove elements that match the requested path from the XML object.
	 *
	 * Shortcut of removeElementsFrom
	 *
	 * @param $path
	 *
	 * @return bool
	 */
	public function removeElements($path) {
		return $this->removeElementsFrom($path, $this->getRootDOM());
	}

	/**
	 * Remove elements that match the requested path from the XML object.
	 *
	 * @param string  $path
	 * @param DOMNode $el
	 *
	 * @return bool
	 */
	public function removeElementsFrom($path, $el) {
		$path = $this->_translatePath($path);

		// Starting element, can be the root node or the current element it's at.
		//$el = $this->getRootDOM();
		$xpath   = new DOMXPath($this->_DOM);
		$entries = $xpath->query($path, $el);
		foreach ($entries as $e) {
			$e->parentNode->removeChild($e);
		}
		return true;
	}

	/**
	 * Converts a given element and its children into an associative array
	 * Much like the simplexml function.
	 *
	 * @param string $el
	 * @param bool   $nesting
	 *
	 * @return array
	 */
	public function elementToArray($el, $nesting = true) {
		$ret = array();
		foreach ($this->getElementsFrom('*', $el, false) as $node) {
			$c           = $node->childNodes->item(0);
			$haschildren = ($c instanceof DOMElement);

			if (isset($ret[$node->tagName])) {
				// More than one element... needs to be an array!
				if (!is_array($ret[$node->tagName])) {
					$v                   = $ret[$node->tagName];
					$ret[$node->tagName] = array($v);
				}
				if ($haschildren && $nesting) {
					$ret[$node->tagName][] = $this->elementToArray($node, true);
				}
				else {
					$ret[$node->tagName][] = ($node->getAttribute('xsi:nil') == 'true') ? null : $node->nodeValue;
				}
			}
			else {
				if ($haschildren && $nesting) {
					$ret[$node->tagName] = $this->elementToArray($node, true);
				}
				else {
					$ret[$node->tagName] = ($node->getAttribute('xsi:nil') == 'true') ? null : $node->nodeValue;
				}
			}
		}

		return $ret;
	}

	/**
	 * Get this XML object as a minified string
	 *
	 * @return string
	 */
	public function asMinifiedXML() {
		// Get the XML output as a string.
		$string = $this->getDOM()->saveXML();

		// Ensure standard line-endings.
		$string = str_replace(array("\r\n", "\r", "\n"), NL, $string);

		// Remove any whitespace for <...> lines.
		$string = preg_replace('/^(\s*)</m', '<', $string);

		// Remove the first newline... it's probably there.
		$string = preg_replace('/^' . NL . '/', '', $string);

		// Remove extra blank newlines.
		$string = preg_replace('/' . NL . '+/', NL, $string);

		// Remove newlines after tags, they're not needed.
		$string = preg_replace('/>$' . NL . '/m', '>', $string);

		// A few special tags need their own lines.
		$string = preg_replace('/(<\?xml version="1.0" encoding="UTF-8"\?>)/', '$1' . NL, $string);
		$string = preg_replace('/(<!DOCTYPE component>)/', '$1' . NL, $string);

		return $string;
	}

	/**
	 * Prettifies an XML string into a human-readable and indented work of art
	 *
	 * @param boolean $html_output True if the output should be escaped (for use in HTML)
	 *
	 * @return string
	 */
	public function asPrettyXML($html_output = false) {
		// Get the XML output as a string.
		$string = $this->getDOM()->saveXML();

		// Ensure standard line-endings.
		$string = str_replace(array("\r\n", "\r", "\n"), NL, $string);

		// Remove any whitespace for <...> lines.
		$string = preg_replace('/^(\s*)</m', '<', $string);

		// Put each <...> on its own line, may produce multiple blank lines, but we'll get to that later.
		$string = preg_replace('/<([^>]*)>/', NL . '<$1>' . NL, $string);

		// Remove the first newline... it's probably there.
		$string = preg_replace('/^' . NL . '/', '', $string);

		// Remove extra blank newlines.
		$string = preg_replace('/' . NL . '+/', NL, $string);

		// Split this string on the newline character so I can iterate through it one line at a time.
		$lines = explode(NL, $string);

		$indent     = 0;
		$tab        = "\t";
		$out        = '';
		$_incomment = false;
		$skip       = 0; // Counter used for skipping lines.

		foreach ($lines as $k => $line) {
			if ($skip > 0) {
				$skip--;
				continue;
			}
			// Comments are the exception... they don't get any logic.
			if ($_incomment && !preg_match('/-->/', $line)) {
				$out .= str_repeat($tab, $indent) . trim($line) . NL;
				continue;
			}

			// Will match: < ?xml version="1.0"? >
			if (preg_match('/<\?[^\?]*\?>/', $line)) {
				// This is the file header.  No indentation needed.
				$out .= trim($line) . NL;
			}
			// Will match: <!DOCTYPE component>
			elseif (preg_match('/<!DOCTYPE[^>]*>/', $line)) {
				// This is the doctype.  No indentation needed.
				$out .= trim($line) . NL;
			}
			// Will match: <!-- ... -->
			elseif (preg_match('/<\!--.*-->/', $line)) {
				// Single line comments don't affect the indent or require the incomment flag.
				$out .= str_repeat($tab, $indent) . trim($line) . NL;
			}
			// Will match: <!--
			elseif (preg_match('/<\!--/', $line)) {
				$_incomment = true;
				$out .= str_repeat($tab, $indent) . trim($line) . NL;
				$indent++;
			}
			// Will match: -->
			elseif ($_incomment && preg_match('/-->/', $line)) {
				$_incomment = false;
				$indent--;
				$out .= str_repeat($tab, $indent) . trim($line) . NL;
			}
			// Will match: <something/>
			elseif (preg_match('/<[^>]*(?<=\/)>/', $line)) {
				// Self-enclosed tags do not affect indent level, but just echo on the current one.
				$out .= str_repeat($tab, $indent) . trim($line) . NL;
			}
			// Will match: </something>
			elseif (preg_match('/<\/[^>]*>/', $line)) {
				// Ending multi-part tags need to jump back to the previous indent level.
				$indent--;
				$out .= str_repeat($tab, $indent) . trim($line) . NL;
			}
			// Will match: <something>
			elseif (preg_match('/<[^>]*(?<!\/)>/', $line)) {
				// Enable look-ahead for these lines.
				// If the following line appears as: </something>... just toss that onto this line and skip the indent.
				if (isset($lines[$k + 1]) && preg_match('/<\/[^>]*>/', $lines[$k + 1])) {
					$out .= str_repeat($tab, $indent) . trim($line) . trim($lines[$k + 1]) . NL;
					$skip = 1;
				}
				// Also enable look-ahead for NL (<20 characters) NL </...>.  This can be concat'ed too.
				elseif (isset($lines[$k + 2]) && strpos($lines[$k + 1], '<') === false && strlen(trim($lines[$k + 1])) <= 31 && preg_match('/<\/[^>]*>/', $lines[$k + 2])) {
					$out .= str_repeat($tab, $indent) . trim($line) . trim($lines[$k + 1]) . trim($lines[$k + 2]) . NL;
					$skip = 2;
				}
				else {
					// Multi-part tags affect the indent level for their contents.
					$out .= str_repeat($tab, $indent) . trim($line) . NL;
					$indent++;
				}
			}
			// Will match: well... everything else
			else {
				// Eh, use the current indent level and ensure to trim it; text may have preserved its whitespace from before.
				$out .= str_repeat($tab, $indent) . trim($line) . NL;
			}
		}

		return $out;

		$xml_obj = simplexml_import_dom($this->getDOM());
		//$xml_obj = new SimpleXMLElement($xml);
		$xml_lines    = explode("\n", $xml_obj->asXML());
		$indent_level = 0;
		$tab          = "\t"; // Optionally, have this be "    " for a space'd version.

		$new_xml_lines = array();
		foreach ($xml_lines as $xml_line) {
			if (preg_match('#^(<[a-z0-9_:-]+((s+[a-z0-9_:-]+="[^"]+")*)?>.*<s*/s*[^>]+>)|(<[a-z0-9_:-]+((s+[a-z0-9_:-]+="[^"]+")*)?s*/s*>)#i', ltrim($xml_line))) {
				$new_line        = str_repeat($tab, $indent_level) . ltrim($xml_line);
				$new_xml_lines[] = $new_line;
			} elseif (preg_match('#^<[a-z0-9_:-]+((s+[a-z0-9_:-]+="[^"]+")*)?>#i', ltrim($xml_line))) {
				$new_line = str_repeat($tab, $indent_level) . ltrim($xml_line);
				$indent_level++;
				$new_xml_lines[] = $new_line;
			} elseif (preg_match('#<s*/s*[^>/]+>#i', $xml_line)) {
				$indent_level--;
				if (trim($new_xml_lines[sizeof($new_xml_lines) - 1]) == trim(str_replace("/", "", $xml_line))) {
					$new_xml_lines[sizeof($new_xml_lines) - 1] .= $xml_line;
				} else {
					if ($indent_level < 0) $indent_level = 0;
					$new_line        = str_repeat($tab, $indent_level) . $xml_line;
					$new_xml_lines[] = $new_line;
				}
			} else {
				$new_line        = str_repeat($tab, $indent_level) . $xml_line;
				$new_xml_lines[] = $new_line;
			}
		}

		$xml = join("\n", $new_xml_lines);
		return ($html_output) ? '<pre>' . htmlentities($xml) . '</pre>' : $xml;
	}
}
