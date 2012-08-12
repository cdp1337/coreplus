<?php
/**
 * Core bootstrap (COMPILED) file that kicks off the entire application
 *
 * This file is the core of the application; it's responsible for setting up
 *  all the necessary paths, settings and includes.
 *
 * In addition, it has been compiled to include the source from the many included files automatically.
 * To manage some code here, please see which file the code is being included from, (as stated in the comment above
 * the respective code), edit there and re-run utilities/compiler.php
 *
 * @package Core Plus\Core
 * @since 2.1.5
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2012  Charlie Powell
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 *
 * @compiled Sun, 12 Aug 2012 04:10:31 -0400
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
if (basename($_SERVER['SCRIPT_NAME']) == 'bootstrap.php') die('You cannot call that file directly.');
if (PHP_VERSION < '6.0.0' && ini_get('magic_quotes_gpc')) {
die('This application cannot run with magic_quotes_gpc enabled, please disable them now!');
}
if (PHP_VERSION < '5.3.0') {
die('This application requires at least PHP 5.3 to run!');
}
umask(0);
$start_time = microtime(true);
mb_internal_encoding('UTF-8');
### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/bootstrap_predefines.php
if (PHP_VERSION < '6.0.0' && ini_get('magic_quotes_gpc')) {
die('This application cannot run with magic_quotes_gpc enabled, please disable them now!' . "\n");
}
if (PHP_VERSION < '5.3.0') {
die('This application requires at least PHP 5.3 to run!' . "\n");
}
if (isset($_SERVER['SHELL'])) {
$em = 'CLI';
$rpdr = realpath(__DIR__ . '/../') . '/';
$rwdr = null;
$rip  = '127.0.0.1';
}
else {
$em  = 'WEB';
$rip = '127.0.0.1';
$rpdr = pathinfo($_SERVER['SCRIPT_FILENAME'], PATHINFO_DIRNAME);
if ($rpdr != '/') $rpdr .= '/'; // Append a slash if it's not the root dir itself.
$rwdr = pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_DIRNAME);
if ($rwdr != '/') $rwdr .= '/'; // Append a slash if it's not the root dir itself.
$rip = $_SERVER['REMOTE_ADDR'];
}
define('EXEC_MODE', $em);
if (!defined('ROOT_PDIR')) define('ROOT_PDIR', $rpdr);
if (!defined('ROOT_WDIR')) define('ROOT_WDIR', $rwdr);
define('REMOTE_IP', $rip);
define('FULL_DEBUG', false);
define('NL', "\n");
define('TAB', "\t");
define('DS', DIRECTORY_SEPARATOR);
unset($em, $rpdr, $rwdr, $rip);

$predefines_time = microtime(true);
### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/bootstrap_preincludes.php
### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/core/Debug.class.php
class Debug {
public static function Write($text) {
if (!FULL_DEBUG) return;
if (EXEC_MODE == 'CLI') echo '[ DEBUG ] - ' . $text . "\n";
else echo "<div class='cae2_debug'>" . $text . "</div>";
}
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/core/ISingleton.interface.php
Interface ISingleton {
public static function Singleton();
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/core/XMLLoader.class.php
class XMLLoader implements Serializable {
protected $_rootname;
protected $_filename;
protected $_file;
protected $_DOM;
private $_rootnode = null;
public function serialize(){
$dat = array(
'rootname' => $this->_rootname,
'filename' => $this->_filename,
'file' => $this->_file,
'dom' => $this->getDOM()->saveXML()
);
$dat['dom'] = base64_encode(gzcompress($dat['dom']));
return serialize($dat);
}
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
public function load() {
if (!$this->_rootname) return false;
$this->_DOM = new DOMDocument();
$this->_DOM->formatOutput = true;
if ($this->_file) {
$contents = $this->_file->getContentsObject();
if (is_a($contents, 'File_gz_contents')) {
$dat = $contents->uncompress();
}
else {
$dat = $contents->getContents();
}
$this->_DOM->loadXML($dat);
}
elseif ($this->_filename) {
if (!@$this->_DOM->load($this->_filename)) return false;
}
else {
return false;
}
return true;
}
public function loadFromFile($file) {
if (is_a($file, 'File_Backend')) {
$this->_file = $file;
}
else {
$this->_filename = $file;
}
return $this->load();
}
public function loadFromNode(DOMNode $node) {
$this->_DOM = new DOMDocument();
$this->_DOM->formatOutput = true;
$nn = $this->_DOM->importNode($node, true);
$this->_DOM->appendChild($nn);
return true;
}
public function setFilename($file) {
$this->_filename = $file;
}
public function setRootName($name) {
$this->_rootname = $name;
}
public function getRootDOM() {
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
public function getDOM() {
return $this->_DOM;
}
public function getElementsByTagName($name) {
return $this->_DOM->getElementsByTagName($name);
}
public function getElementByTagName($name) {
return $this->_DOM->getElementsByTagName($name)->item(0);
}
public function getElement($path, $autocreate = true) {
return $this->getElementFrom($path, false, $autocreate);
}
public function getElementFrom($path, $el = false, $autocreate = true) {
if (!$el) $el = $this->getRootDOM();
$path = $this->_translatePath($path);
$list = $this->getElementsFrom($path, $el);
if ($list->item(0)) return $list->item(0);
if (!$autocreate) return null;
return $this->createElement($path, $el);
}
private function _translatePath($path) {
if (preg_match(':^/[^/]:', $path)) {
$path = '//' . $this->getRootDOM()->tagName . $path;
}
return $path;
}
public function createElement($path, $el = false) {
if (!$el) $el = $this->getRootDOM();
$path = $this->_translatePath($path);
$xpath = new DOMXPath($this->_DOM);
$patharray = array();
if (strpos($path, '/') === false) {
$patharray[] = $path;
}
elseif (strpos($path, '[') === false) {
$patharray = explode('/', $path);
}
else {
$len    = strlen($path);
$inatt  = false;
$curstr = '';
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
if ($curstr) {
$patharray[] = $curstr;
$curstr      = '';
}
}
foreach ($patharray as $s) {
if ($s == '') continue;
$entries = $xpath->query($s, $el);
if (!$entries) {
trigger_error("Invalid query - " . $s, E_USER_WARNING);
return false;
}
if ($entries->item(0) == null) {
if (strpos($s, '[') !== false) {
$tag = trim(substr($s, 0, strpos($s, '[')));
$node = $this->_DOM->createElement($tag);
preg_match_all('/\[([^=,\]]*)=([^\[]*)\]/', $s, $matches);
foreach ($matches[1] as $k => $v) {
$node->setAttribute(trim(trim($v), '@'), trim(trim($matches[2][$k]), '"'));
}
}
else {
$tag = trim($s);
$node = $this->_DOM->createElement($tag);
}
$el->appendChild($node);
$el = $node;
}
else {
$el = $entries->item(0);
}
}
return $el;
}
public function getElements($path) {
return $this->getElementsFrom($path, $this->getRootDOM());
}
public function getElementsFrom($path, $el = false) {
if (!$el) $el = $this->getRootDOM();
$path = $this->_translatePath($path);
$xpath   = new DOMXPath($this->_DOM);
$entries = $xpath->query($path, $el);
return $entries;
}
public function removeElements($path) {
return $this->removeElementsFrom($path, $this->getRootDOM());
}
public function removeElementsFrom($path, $el) {
$path = $this->_translatePath($path);
$xpath   = new DOMXPath($this->_DOM);
$entries = $xpath->query($path, $el);
foreach ($entries as $e) {
$e->parentNode->removeChild($e);
}
return true;
}
public function elementToArray($el, $nesting = true) {
$ret = array();
foreach ($this->getElementsFrom('*', $el, false) as $node) {
$c           = $node->childNodes->item(0);
$haschildren = ($c instanceof DOMElement);
if (isset($ret[$node->tagName])) {
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
public function asMinifiedXML() {
$string = $this->getDOM()->saveXML();
$string = str_replace(array("\r\n", "\r", "\n"), NL, $string);
$string = preg_replace('/^(\s*)</m', '<', $string);
$string = preg_replace('/^' . NL . '/', '', $string);
$string = preg_replace('/' . NL . '+/', NL, $string);
$string = preg_replace('/>$' . NL . '/m', '>', $string);
$string = preg_replace('/(<\?xml version="1.0" encoding="UTF-8"\?>)/', '$1' . NL, $string);
$string = preg_replace('/(<!DOCTYPE component>)/', '$1' . NL, $string);
return $string;
}
public function asPrettyXML($html_output = false) {
$string = $this->getDOM()->saveXML();
$string = str_replace(array("\r\n", "\r", "\n"), NL, $string);
$string = preg_replace('/^(\s*)</m', '<', $string);
$string = preg_replace('/<([^>]*)>/', NL . '<$1>' . NL, $string);
$string = preg_replace('/^' . NL . '/', '', $string);
$string = preg_replace('/' . NL . '+/', NL, $string);
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
if ($_incomment && !preg_match('/-->/', $line)) {
$out .= str_repeat($tab, $indent) . trim($line) . NL;
continue;
}
if (preg_match('/<\?[^\?]*\?>/', $line)) {
$out .= trim($line) . NL;
}
elseif (preg_match('/<!DOCTYPE[^>]*>/', $line)) {
$out .= trim($line) . NL;
}
elseif (preg_match('/<\!--.*-->/', $line)) {
$out .= str_repeat($tab, $indent) . trim($line) . NL;
}
elseif (preg_match('/<\!--/', $line)) {
$_incomment = true;
$out .= str_repeat($tab, $indent) . trim($line) . NL;
$indent++;
}
elseif ($_incomment && preg_match('/-->/', $line)) {
$_incomment = false;
$indent--;
$out .= str_repeat($tab, $indent) . trim($line) . NL;
}
elseif (preg_match('/<[^>]*(?<=\/)>/', $line)) {
$out .= str_repeat($tab, $indent) . trim($line) . NL;
}
elseif (preg_match('/<\/[^>]*>/', $line)) {
$indent--;
$out .= str_repeat($tab, $indent) . trim($line) . NL;
}
elseif (preg_match('/<[^>]*(?<!\/)>/', $line)) {
if (isset($lines[$k + 1]) && preg_match('/<\/[^>]*>/', $lines[$k + 1])) {
$out .= str_repeat($tab, $indent) . trim($line) . trim($lines[$k + 1]) . NL;
$skip = 1;
}
elseif (isset($lines[$k + 2]) && strpos($lines[$k + 1], '<') === false && strlen(trim($lines[$k + 1])) <= 31 && preg_match('/<\/[^>]*>/', $lines[$k + 2])) {
$out .= str_repeat($tab, $indent) . trim($line) . trim($lines[$k + 1]) . trim($lines[$k + 2]) . NL;
$skip = 2;
}
else {
$out .= str_repeat($tab, $indent) . trim($line) . NL;
$indent++;
}
}
else {
$out .= str_repeat($tab, $indent) . trim($line) . NL;
}
}
return $out;
$xml_obj = simplexml_import_dom($this->getDOM());
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
public function _blahasPrettyXML() {
$string = $this->getDOM()->saveXML();
$string = preg_replace("/>\s*</", ">\n<", $string);
$xmlArray = explode("\n", $string);
$currIndent = 0;
$string = array_shift($xmlArray) . "\n";
foreach ($xmlArray as $element) {
$element = trim($element);
if (preg_match('/^<([\w])+[^>]*\/>$/U', $element)) {
$string .= str_repeat("\t", $currIndent) . $element . "\n";
}
elseif (preg_match('/^<([\w])+[^>]*>$/U', $element)) {
$string .= str_repeat("\t", $currIndent) . $element . "\n";
$currIndent++;
}
elseif (preg_match('/^<\/.+>$/', $element)) {
$currIndent--;
$string .= str_repeat("\t", $currIndent) . $element . "\n";
}
else {
$string .= str_repeat("\t", $currIndent) . $element . "\n";
}
}
return $string;
}
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/core/InstallArchive.class.php
class InstallArchive {
const SIGNATURE_NONE    = 0;
const SIGNATURE_VALID   = 1;
const SIGNATURE_INVALID = 2;
private $_file;
private $_manifestdata;
private $_signature;
private $_fileconflicts;
private $_filelist;
public function __construct($file) {
if ($file instanceof File) {
$this->_file = $file;
}
else {
$this->_file = new File_local_backend($file);
}
}
public function hasValidSignature() {
$sig = $this->checkSignature();
return ($sig['state'] == InstallArchive::SIGNATURE_VALID);
}
public function checkSignature() {
if (is_null($this->_signature)) {
switch ($this->_file->getMimetype()) {
case 'application/pgp':
$this->_signature = $this->_checkGPGSignature();
break;
default:
$this->_signature = array('state' => InstallArchive::SIGNATURE_NONE,
'key'   => null,
'email' => null,
'name'  => null);
break;
}
}
return $this->_signature;
}
private function _checkGPGSignature() {
$crypt_gpg = GPG::Singleton();
try {
list($out) = $crypt_gpg->verifyFile($this->_file->getFilename());
}
catch (Exception $e) {
return array('state' => InstallArchive::SIGNATURE_INVALID,
'key'   => null,
'email' => null,
'name'  => null);
}
if (!$out->isValid()) {
return array('state' => InstallArchive::SIGNATURE_INVALID,
'key'   => null,
'email' => null,
'name'  => null);
}
else {
return array(
'state' => InstallArchive::SIGNATURE_VALID,
'key'   => $out->getKeyFingerprint(),
'email' => $out->getUserId()->getEmail(),
'name'  => $out->getUserId()->getName()
);
}
}
public function getManifest() {
if (is_null($this->_manifestdata)) {
switch ($this->_file->getMimetype()) {
case 'application/pgp':
if (!$this->hasValidSignature()) {
$this->_manifestdata = $this->_getManifest(null);
}
else {
$tmpfile = '/tmp/outtarball-manifest.tgz';
$this->_decryptTo($tmpfile);
$this->_manifestdata = $this->_getManifest($tmpfile);
unlink($tmpfile);
}
break;
default:
$this->_manifestdata = $this->_getManifest($this->_file->getFilename());
break;
}
}
return $this->_manifestdata;
}
private function _getManifest($filename) {
if ($filename === null) {
$fn = $this->_file->getFilename();
if (strpos($fn, '-')) {
list($name, $version) = explode('-', substr($fn, strrpos($fn, '/') + 1, strrpos($fn, '.')));
}
else {
$name    = substr($fn, strrpos($fn, '.'));
$version = null;
}
return array(
'Manifest-Version' => '1.0',
'manifestversion'  => '1.0',
'Bundle-Type'      => 'unknown',
'bundletype'       => 'unknown',
'Bundle-Name'      => $name,
'bundlename'       => $name,
'Bundle-Version'   => $version,
'bundleversion'    => $version
);
}
exec('tar -xzvf "' . $filename . '" ./META-INF/MANIFEST.MF -O', $output);
$ret = array();
foreach ($output as $line) {
if (strpos($line, ':') === false) continue;
list($k, $v) = explode(':', $line);
$ret[trim($k)]                                   = trim($v);
$ret[trim(strtolower(str_replace('-', '', $k)))] = trim($v);
}
return $ret;
}
private function _decryptTo($filename) {
$crypt_gpg = GPG::Singleton();
$crypt_gpg->decryptFile($this->_file->getFilename(), $filename);
}
public function getFilelist() {
if (is_null($this->_filelist)) {
switch ($this->_file->getMimetype()) {
case 'application/pgp':
$tmpfile = '/tmp/outtarball-filelist.tgz';
$this->_decryptTo($tmpfile);
$this->_filelist = $this->_getFilelist($tmpfile);
unlink($tmpfile);
break;
default:
$this->_filelist = $this->_getFilelist($this->_file->getFilename());
break;
}
}
return $this->_filelist;
}
private function _getFilelist($filename) {
$man  = $this->getManifest();
$type = $man['Bundle-Type'];
exec('tar -tzf "' . $filename . '"', $output);
$ret = array();
foreach ($output as $line) {
if ($line == './') continue;
if (!preg_match(':\./data:', $line)) continue;
if (preg_match(':/$:', $line)) continue;
if ($line == './data/component.xml' && $type == 'component') continue;
$file = str_replace('./data/', '', $line);
$ret[] = $file;
}
return $ret;
}
public function extractFile($filename, $to = false) {
if (!$to) $to = $this->getBaseDir();
$fp  = $to . $filename;
$fb  = './data/' . $filename;
$dir = dirname($fp);
if (!is_dir($dir)) {
exec('mkdir -p "' . $dir . '"');
exec('chmod a+w "' . $dir . '"');
}
if (!is_writable($dir)) {
throw new Exception('Cannot write to directory ' . $dir);
}
if (!is_writable($fp)) {
throw new Exception('Cannot write to file ' . $fp);
}
switch ($this->_file->getMimetype()) {
case 'application/pgp':
$file   = '/tmp/outtarball-extractfile.tgz';
$istemp = true;
$this->_decryptTo($file);
break;
default:
$file   = $this->_file->getFilename();
$istemp = false;
break;
}
exec('tar -xzvf "' . $file . '" "' . $fb . '" -O > "' . $fp . '"');
if ($istemp) {
unlink($file);
}
}
public function getFileConflicts() {
if (is_null($this->_fileconflicts)) {
$man = $this->getManifest();
$files = $this->getFileList();
switch ($man['Bundle-Type']) {
case 'component':
return $this->_getFileConflictsComponent($files);
break;
}
}
return $this->_fileconflicts;
}
public function getBaseDir() {
$man = $this->getManifest();
switch ($man['Bundle-Type']) {
case 'component':
return ROOT_PDIR . 'components/' . $man['Bundle-Name'] . '/';
}
}
private function _getFileConflictsComponent($arrayoffiles) {
$man       = $this->getManifest();
$basedir   = $this->getBaseDir();
$component = ComponentHandler::GetComponent($man['Bundle-Name']);
$changedfiles = $component->getChangedFiles();
$ret = array();
foreach ($arrayoffiles as $line) {
if (in_array($line, $changedfiles)) $ret[] = $line;
}
return $ret;
}
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/core/InstallArchiveAPI.class.php
abstract class InstallArchiveAPI extends XMLLoader {
const TYPE_COMPONENT = 'component';
const TYPE_LIBRARY   = 'library';
const TYPE_THEME     = 'theme';
protected $_name;
protected $_version;
protected $_description;
protected $_updateSites = array();
protected $_authors = array();
protected $_iterator;
protected $_type;
public function load() {
$XMLFilename = $this->getXMLFilename();
if (!is_readable($XMLFilename)) {
throw new Exception('Unable to open XML Metafile [' . $XMLFilename . '] for reading.');
}
$this->setFilename($XMLFilename);
$this->setRootName($this->_type);
if (!parent::load()) {
throw new Exception('Parsing of XML Metafile [' . $XMLFilename . '] failed, not valid XML.');
}
if (strtolower($this->getRootDOM()->getAttribute("name")) != strtolower($this->_name)) {
throw new Exception('Name mismatch in XML Metafile [' . $XMLFilename . '], defined name does not match expected name.');
}
$this->_version = $this->getRootDOM()->getAttribute("version");
}
public function getRequires() {
$ret = array();
foreach ($this->getRootDOM()->getElementsByTagName('requires') as $r) {
$t  = $r->getAttribute('type');
$n  = $r->getAttribute('name');
$v  = @$r->getAttribute('version');
$op = @$r->getAttribute('operation');
if ($v == '') $v = false;
if ($op == '') $op = 'ge';
$ret[] = array(
'type'      => strtolower($t),
'name'      => $n,
'version'   => strtolower($v),
'operation' => strtolower($op),
);
}
return $ret;
}
public function getDescription() {
if (is_null($this->_description)) $this->_description = $this->getElement('//description')->nodeValue;
return $this->_description;
}
public function setDescription($desc) {
$this->_description = $desc;
$this->getElement('//description')->nodeValue = $desc;
}
public function setPackageMaintainer($name, $email) {
$this->getElement('/changelog[@version="' . $this->_version . '"]/packagemeta/date')->nodeValue = Time::GetCurrent(Time::TIMEZONE_GMT, 'r');
$this->getElement('/changelog[@version="' . $this->_version . '"]/packagemeta/maintainer[@name="' . $name . '"][@email="' . $email . '"]');
$this->getElement('/changelog[@version="' . $this->_version . '"]/packagemeta/packager')->nodeValue = 'CAE2 ' . ComponentHandler::GetComponent('core')->getVersion();
}
public function getChangelog($version = false) {
if (!$version) $version = $this->getVersion();
return $this->getElement('/changelog[@version="' . $version . '"]/notes')->nodeValue;
}
public function setChangelog($text, $version = false) {
if (!$version) $version = $this->getVersion();
$this->getElement('/changelog[@version="' . $version . '"]/notes')->nodeValue = $text;
}
public function getXMLFilename($prefix = ROOT_PDIR) {
switch ($this->_type) {
case InstallArchiveAPI::TYPE_COMPONENT:
if ($this->_name == 'core') return $prefix . 'core/' . 'component.xml';
else return $prefix . 'components/' . $this->_name . '/' . 'component.xml';
break;
case InstallArchiveAPI::TYPE_LIBRARY:
return $prefix . 'libraries/' . $this->_name . '/' . 'library.xml';
break;
case InstallArchiveAPI::TYPE_THEME:
return $prefix . 'themes/' . $this->_name . '/' . 'theme.xml';
break;
}
}
public function getBaseDir($prefix = ROOT_PDIR) {
switch ($this->_type) {
case InstallArchiveAPI::TYPE_COMPONENT:
if ($this->_name == 'core') return $prefix;
else return $prefix . 'components/' . $this->_name . '/';
break;
case InstallArchiveAPI::TYPE_LIBRARY:
return $prefix . 'libraries/' . $this->_name . '/';
break;
case InstallArchiveAPI::TYPE_THEME:
return $prefix . 'themes/' . $this->_name . '/';
break;
}
}
public function getChangedFiles() {
$ret = array();
foreach ($this->getElementsByTagName('file') as $node) {
if (!($filename = @$node->getAttribute('filename'))) continue;
if ($node->getAttribute('md5') != md5_file($this->getBaseDir() . $filename)) {
$ret[] = $filename;
}
}
return $ret;
}
public function getName() {
return $this->_name;
}
public function getVersion() {
return $this->_version;
}
public function setVersion($vers) {
if ($vers == $this->_version) return;
if (($upg = $this->getElement('/upgrade[@from=""][@to=""]', false))) {
$upg->setAttribute('from', $this->_version);
$upg->setAttribute('to', $vers);
}
elseif (($upg = $this->getElement('/upgrade[@from="' . $this->_version . '"][@to=""]', false))) {
$upg->setAttribute('to', $vers);
}
else {
$newupgrade = $this->getElement('/upgrade[@from="' . $this->_version . '"][@to="' . $vers . '"]');
}
$newchangelog = $this->getElement('/changelog[@version="' . $vers . '"]');
foreach ($this->getElementsByTagName('changelog') as $el) {
if (!@$el->getAttribute('version')) {
$newchangelog->nodeValue .= "\n" . $el->nodeValue;
$el->nodeValue = '';
break;
}
}
$this->_version = $vers;
$this->getRootDOM()->setAttribute('version', $vers);
}
public function getRawXML() {
return $this->asPrettyXML();
}
public function getLicenses() {
$ret = array();
foreach ($this->getRootDOM()->getElementsByTagName('license') as $el) {
$url   = @$el->getAttribute('url');
$ret[] = array(
'title' => $el->nodeValue,
'url'   => $url
);
}
return $ret;
}
public function setLicenses($licenses) {
$this->removeElements('/license');
foreach ($licenses as $lic) {
$str          = '/license' . ((isset($lic['url']) && $lic['url']) ? '[@url="' . $lic['url'] . '"]' : '');
$l            = $this->getElement($str);
$l->nodeValue = $lic['title'];
}
}
public function getAuthors() {
$ret = array();
foreach ($this->getRootDOM()->getElementsByTagName('author') as $el) {
$ret[] = array(
'name'  => $el->getAttribute('name'),
'email' => @$el->getAttribute('email'),
);
}
return $ret;
}
public function setAuthors($authors) {
$this->removeElements('/author');
foreach ($authors as $a) {
if (isset($a['email']) && $a['email']) {
$this->getElement('//component/author[@name="' . $a['name'] . '"][@email="' . $a['email'] . '"]');
}
else {
$this->getElement('//component/author[@name="' . $a['name'] . '"]');
}
}
}
public function getAllFilenames() {
$ret  = array();
$list = $this->getElements('//component/library/file|//component/module/file|//component/view/file|//component/otherfiles/file|//component/assets/file');
foreach ($list as $el) {
$md5   = @$el->getAttribute('md5');
$ret[] = array(
'file' => $el->getAttribute('filename'),
'md5'  => $md5
);
}
return $ret;
}
public function getDirectoryIterator() {
if (is_null($this->_iterator)) {
$this->_iterator = new CAEDirectoryIterator();
$this->_iterator->addIgnore($this->getXMLFilename());
if ($this->_name == 'core') {
$this->_iterator->addIgnores('components/', 'config/', 'dropins/', 'exports/', 'nbproject/', 'scripts/', 'themes/', 'update_site/', 'utils/');
if (ConfigHandler::Get('/core/filestore/assetdir')) $this->_iterator->addIgnore(ConfigHandler::Get('/core/filestore/assetdir'));
if (ConfigHandler::Get('/core/filestore/publicdir')) $this->_iterator->addIgnore(ConfigHandler::Get('/core/filestore/publicdir'));
}
$list = $this->getElements('/ignorefiles/file');
foreach ($list as $el) {
$this->_iterator->addIgnores($this->getBaseDir() . $el->getAttribute('filename'));
}
$this->_iterator->setPath($this->getBaseDir());
$this->_iterator->scan();
}
return clone $this->_iterator;
}
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/core/Model.class.php
class Model implements ArrayAccess {
const ATT_TYPE_STRING = 'string';
const ATT_TYPE_TEXT = 'text';
const ATT_TYPE_DATA = 'data';
const ATT_TYPE_INT = 'int';
const ATT_TYPE_FLOAT = 'float';
const ATT_TYPE_BOOL = 'boolean';
const ATT_TYPE_ENUM = 'enum';
const ATT_TYPE_ID = '__id';
const ATT_TYPE_UPDATED = '__updated';
const ATT_TYPE_CREATED = '__created';
const VALIDATION_NOTBLANK = "/^.+$/";
const VALIDATION_EMAIL = 'Core::CheckEmailValidity';
const LINK_HASONE  = 'one';
const LINK_HASMANY = 'many';
const LINK_BELONGSTOONE = 'belongs_one';
const LINK_BELONGSTOMANY = 'belongs_many';
public $interface = null;
protected $_data = array();
protected $_datainit = array();
protected $_dataother = array();
protected $_dirty = false;
protected $_exists = false;
protected $_linked = array();
protected $_cacheable = true;
protected $_schemacache = null;
public static $Schema = array();
public static $Indexes = array();
public static $_ModelCache = array();
public function __construct($key = null) {
$s = self::GetSchema();
foreach ($s as $k => $v) {
$this->_data[$k] = (isset($v['default'])) ? $v['default'] : null;
}
$i = self::GetIndexes();
if (isset($i['primary']) && func_num_args() == sizeof($i['primary'])) {
foreach ($i['primary'] as $k => $v) {
$this->_data[$v] = func_get_arg($k);
}
}
if($key !== null){
$this->load();
}
}
public function load() {
if (!self::GetTableName()) {
return;
}
$i = self::GetIndexes();
$keys = array();
if (isset($i['primary']) && sizeof($i['primary'])) {
foreach ($i['primary'] as $k) {
if (($v = $this->get($k)) === null) return;
$keys[$k] = $v;
}
}
if ($this->_cacheable) {
$cachekey = $this->_getCacheKey();
$cache    = Core::Cache()->get($cachekey);
}
$data = Dataset::Init()
->select('*')
->table(self::GetTableName())
->where($keys)
->execute($this->interface);
if ($data->num_rows) {
$this->_data     = $data->current();
$this->_datainit = $data->current();
$this->_dirty  = false;
$this->_exists = true;
}
else {
$this->_dirty  = true;
$this->_exists = false;
}
return;
}
public function save() {
$save = false;
if(!$this->_exists){
$save = true;
}
elseif($this->_dirty){
$save = true;
}
else{
foreach($this->_linked as $k => $l){
if(isset($l['records'])){
$save = true;
break;
}
}
}
if(!$save){
return false;
}
if ($this->_exists) $this->_saveExisting();
else $this->_saveNew();
foreach($this->_linked as $k => $l){
if(!(isset($l['records']))) continue;
if(!(isset($l['records']) || $this->_dirty)) continue; // No need to save if it was never loaded.
switch($l['link']){
case Model::LINK_HASONE:
case Model::LINK_HASMANY:
$models = (is_array($l['records']))? $l['records'] : array($l['records']);
foreach($models as $model){
$model->setFromArray($this->_getLinkWhereArray($k));
$model->save();
}
break;
}
}
$this->_exists     = true;
$this->_dirty      = false;
$this->_dataatinit = $this->_data;
return true;
}
public function offsetExists($offset) {
return (array_key_exists($offset, $this->_data));
}
public function offsetGet($offset) {
return $this->get($offset);
}
public function offsetSet($offset, $value) {
$this->set($offset, $value);
}
public function offsetUnset($offset) {
$this->set($offset, null);
}
public function getKeySchemas() {
if ($this->_schemacache === null) {
$this->_schemacache = self::GetSchema();
foreach ($this->_schemacache as $k => $v) {
if (!isset($v['type'])) $this->_schemacache[$k]['type'] = Model::ATT_TYPE_TEXT; // Default if not present.
if (!isset($v['maxlength'])) $this->_schemacache[$k]['maxlength'] = false;
if (!isset($v['null'])) $this->_schemacache[$k]['null'] = false;
if (!isset($v['comment'])) $this->_schemacache[$k]['comment'] = false;
if (!isset($v['default'])) $this->_schemacache[$k]['default'] = false;
}
}
return $this->_schemacache;
}
public function getKeySchema($key) {
$s = $this->getKeySchemas();
if (!isset($s[$key])) return null;
return $s[$key];
}
private function _saveNew() {
$i = self::GetIndexes();
$s = self::GetSchema();
if (!isset($i['primary'])) $i['primary'] = array(); // No primary schema defined... just don't make the in_array bail out.
$dat = new Dataset();
$dat->table(self::GetTableName());
$idcol = false;
foreach ($this->_data as $k => $v) {
$keyschema = $s[$k];
switch ($keyschema['type']) {
case Model::ATT_TYPE_CREATED:
case Model::ATT_TYPE_UPDATED:
$nv = Time::GetCurrentGMT();
$dat->insert($k, $nv);
$this->_data[$k] = $nv;
break;
case Model::ATT_TYPE_ID:
$dat->setID($k, $this->_data[$k]);
$idcol = $k; // Remember this for after the save.
break;
default:
$dat->insert($k, $v);
break;
}
}
$dat->execute($this->interface);
if ($idcol) $this->_data[$idcol] = $dat->getID();
}
private function _saveExisting() {
$i = self::GetIndexes();
$s = self::GetSchema();
if (!isset($i['primary'])) $i['primary'] = array();
$dat = new Dataset();
$dat->table(self::GetTableName());
$idcol = false;
foreach ($this->_data as $k => $v) {
$keyschema = $s[$k];
switch ($keyschema['type']) {
case Model::ATT_TYPE_CREATED:
continue 2;
case Model::ATT_TYPE_UPDATED:
$nv = Time::GetCurrentGMT();
$dat->update($k, $nv);
$this->_data[$k] = $nv;
continue 2;
case Model::ATT_TYPE_ID:
$dat->setID($k, $this->_data[$k]);
$idcol = $k; // Remember this for after the save.
continue 2;
}
if (in_array($k, $i['primary'])) {
if ($this->_datainit[$k] != $v) $dat->update($k, $v);
$dat->where($k, $this->_datainit[$k]);
$this->_data[$k] = $v;
}
else {
if ($this->_datainit[$k] == $v) continue; // Skip non-changed columns
$dat->update($k, $v);
}
}
if(!sizeof($dat->_sets)){
return false;
}
$dat->execute($this->interface);
}
public function _loadFromRecord($record) {
$this->_data = $record;
$this->_datainit = $this->_data;
$this->_dirty    = false;
$this->_exists   = true;
}
public function delete() {
if ($this->exists()) {
$dat = new Dataset();
$dat->table(self::GetTableName());
$i = self::GetIndexes();
if (!isset($i['primary'])) {
throw new Exception('Unable to delete model [ ' . get_class($this) . ' ] without any primary keys.');
}
foreach ($i['primary'] as $k) {
$dat->where(array($k => $this->_data[$k]));
}
$dat->limit(1)->delete();
if ($dat->execute($this->interface)) {
$this->_dirty  = false;
$this->_exists = false;
}
}
foreach ($this->_linked as $k => $l) {
switch($l['link']){
case Model::LINK_HASONE:
case Model::LINK_HASMANY:
$c     = $this->_getLinkClassName($k);
$model = new $c();
Dataset::Init()
->table($model->getTableName())
->where($this->_getLinkWhereArray($k))
->delete()
->execute($this->interface);
break;
}
if (isset($this->_linked[$k]['records'])) unset($this->_linked[$k]['records']);
}
}
public function validate($k, $v, $throwexception = false) {
$s = self::GetSchema();
$valid = true;
if (isset($s[$k]['validation'])) {
$check = $s[$k]['validation'];
if (is_array($check) && sizeof($check) == 2 && $check[0] == 'this') {
$valid = call_user_func(array($this, $check[1]), $v);
}
elseif (strpos($check, '::') !== false) {
$valid = call_user_func($check, $v);
}
elseif (
($check{0} == '/' && !preg_match($check, $v)) ||
($check{0} == '#' && !preg_match($check, $v))
) {
$valid = false;
}
}
if ($valid === true) {
return true;
}
if ($valid === false) $msg = isset($s[$k]['validationmessage']) ? $s[$k]['validationmessage'] : $k . ' fails validation';
else $msg = $valid;
if ($throwexception) {
throw new ModelValidationException($msg);
}
else {
return $msg;
}
}
public function translateKey($k, $v){
$s = self::GetSchema();
if(!isset($s[$k])) return $v;
$type = $s[$k]['type']; // Type is one of the required properties.
if ($type == Model::ATT_TYPE_BOOL) {
switch(strtolower($v)){
case 'yes':
case 'on':
case 1:
case 'true':
$v = 1;
break;
default:
$v = 0;
}
}
return $v;
}
public function set($k, $v) {
if (array_key_exists($k, $this->_data)) {
if ($this->_data[$k] == $v) return false; // No change needed.
$this->validate($k, $v, true);
$v = $this->translateKey($k, $v);
$this->_setLinkKeyPropagation($k, $v);
$this->_data[$k] = $v;
$this->_dirty    = true;
return true;
}
else {
$this->_dataother[$k] = $v;
return true;
}
}
protected function _setLinkKeyPropagation($key, $newval) {
foreach ($this->_linked as $lk => $l) {
$dolink = false;
if (!isset($l['on'])) {
}
elseif (is_array($l['on'])) {
foreach ($l['on'] as $k => $v) {
if (is_numeric($k) && $v == $key) $dolink = true;
elseif (!is_numeric($k) && $k == $key) $dolink = true;
}
}
else {
if ($l['on'] == $key) $dolink = true;
}
if (!$dolink) continue;
$links = $this->getLink($lk);
if (!is_array($links)) $links = array($links);
foreach ($links as $model) {
$model->set($key, $newval);
}
}
}
protected function _getLinkClassName($linkname) {
$c = (isset($this->_linked[$linkname]['class'])) ? $this->_linked[$linkname]['class'] : $linkname . 'Model';
if (!is_subclass_of($c, 'Model')) return null; // @todo Error Handling
return $c;
}
protected function _getLinkWhereArray($linkname) {
if (!isset($this->_linked[$linkname])) return null; // @todo Error Handling
$wheres = array();
if (!isset($this->_linked[$linkname]['on'])) {
return null; // @todo automatic linking.
}
elseif (is_array($this->_linked[$linkname]['on'])) {
foreach ($this->_linked[$linkname]['on'] as $k => $v) {
if (is_numeric($k)) $wheres[$v] = $this->get($v);
else $wheres[$k] = $this->get($v);
}
}
else {
$k          = $this->_linked[$linkname]['on'];
$wheres[$k] = $this->get($k);
}
return $wheres;
}
public function getLink($linkname, $order = null) {
if (!isset($this->_linked[$linkname])) return null; // @todo Error Handling
if (!isset($this->_linked[$linkname]['records'])) {
$c = $this->_getLinkClassName($linkname);
$f = new ModelFactory($c);
switch($this->_linked[$linkname]['link']){
case Model::LINK_HASONE:
case Model::LINK_BELONGSTOONE:
$f->limit(1);
break;
}
$wheres = $this->_getLinkWhereArray($linkname);
$f->where($wheres);
if ($order) $f->order($order);
$this->_linked[$linkname]['records'] = $f->get();
if ($this->_linked[$linkname]['records'] === null) {
$this->_linked[$linkname]['records'] = new $c();
foreach ($wheres as $k => $v) {
$this->_linked[$linkname]['records']->set($k, $v);
}
}
}
return $this->_linked[$linkname]['records'];
}
public function findLink($linkname, $searchkeys = array()) {
$l = $this->getLink($linkname);
if ($l === null) return null;
if (!is_array($l)) {
$f = true;
foreach ($searchkeys as $k => $v) {
if ($l->get($k) != $v) {
$f = false;
break;
}
}
return ($f) ? $l : false;
}
else {
foreach ($l as $model) {
$f = true;
foreach ($searchkeys as $k => $v) {
if ($model->get($k) != $v) {
$f = false;
break;
}
}
if ($f) return $model;
}
$c = $this->_getLinkClassName($linkname);
$model = new $c();
$model->setFromArray($this->_getLinkWhereArray($linkname));
$model->setFromArray($searchkeys);
$model->load();
$this->_linked[$linkname]['records'][] = $model;
return $model;
}
}
public function setFromArray($array) {
foreach ($array as $k => $v) {
$this->set($k, $v);
}
}
public function get($k) {
if (array_key_exists($k, $this->_data)) {
return $this->_data[$k];
}
elseif (array_key_exists($k, $this->_dataother)) {
return $this->_dataother[$k];
}
else {
return null;
}
}
public function getAsArray() {
return array_merge($this->_data, $this->_dataother);
}
public function exists() {
return $this->_exists;
}
public function isnew() {
return !$this->_exists;
}
protected function _getCacheKey() {
if (!$this->_cacheable) return false;
if (!(isset($i['primary']) && sizeof($i['primary']))) return false;
$cachekeys = array();
foreach ($i['primary'] as $k) {
$val = $this->get($k);
if ($val === null) $val = 'null';
elseif ($val === false) $val = 'false';
$cachekeys[] = $val;
}
return 'DATA:' . self::GetTableName() . ':' . implode('-', $cachekeys);
}
public static function Construct($keys = null){
$class = get_called_class();
if($keys === null){
return new $class();
}
$cache = '';
foreach(func_get_args() as $a){
$cache .= $a . '-';
}
$cache = substr($cache, 0, -1);
if(!isset(self::$_ModelCache[$class])){
self::$_ModelCache[$class] = array();
}
if(!isset(self::$_ModelCache[$class][$cache])){
$obj = new $class();
$i = $obj::GetIndexes();
if (isset($i['primary']) && func_num_args() == sizeof($i['primary'])) {
foreach ($i['primary'] as $k => $v) {
$obj->_data[$v] = func_get_arg($k);
}
}
$obj->load();
self::$_ModelCache[$class][$cache] = $obj;
}
return self::$_ModelCache[$class][$cache];
}
public static function Find($where = array(), $limit = null, $order = null) {
$fac = new ModelFactory(get_called_class());
$fac->where($where);
$fac->limit($limit);
$fac->order($order);
return $fac->get();
}
public static function FindRaw($where = array(), $limit = null, $order = null) {
$fac = new ModelFactory(get_called_class());
$fac->where($where);
$fac->limit($limit);
$fac->order($order);
return $fac->getRaw();
}
public static function Count($where = array()) {
$fac = new ModelFactory(get_called_class());
$fac->where($where);
return $fac->count();
}
public static function GetTableName() {
static $_tablenames = array();
$m = get_called_class();
if ($m == 'Model') return null;
if (!isset($_tablenames[$m])) {
$tbl = $m;
if (preg_match('/Model$/', $tbl)) $tbl = substr($tbl, 0, -5);
$tbl = preg_replace('/([A-Z])/', '_$1', $tbl);
if ($tbl{0} == '_') $tbl = substr($tbl, 1);
$tbl = strtolower($tbl);
$_tablenames[$m] = DB_PREFIX . $tbl;
}
return $_tablenames[$m];
}
public static function GetSchema() {
$m = get_called_class();
return $m::$Schema;
}
public static function GetIndexes() {
$m = get_called_class();
return $m::$Indexes;
}
}
class ModelFactory {
public $interface = null;
private $_model;
private $_dataset;
public function __construct($model) {
$this->_model = $model;
$m              = $this->_model;
$this->_dataset = new Dataset();
$this->_dataset->table($m::GetTablename());
$this->_dataset->select('*');
}
public function where() {
call_user_func_array(array($this->_dataset, 'where'), func_get_args());
}
public function whereGroup() {
call_user_func_array(array($this->_dataset, 'whereGroup'), func_get_args());
}
public function order() {
call_user_func_array(array($this->_dataset, 'order'), func_get_args());
}
public function limit() {
call_user_func_array(array($this->_dataset, 'limit'), func_get_args());
}
public function get() {
$rs = $this->_dataset->execute($this->interface);
$ret = array();
foreach ($rs as $row) {
$model = new $this->_model();
$model->_loadFromRecord($row);
$ret[] = $model;
}
if ($this->_dataset->_limit == 1) {
return (sizeof($ret)) ? $ret[0] : null;
}
else {
return $ret;
}
}
public function getRaw(){
$rs = $this->_dataset->execute($this->interface);
return $rs->_data;
}
public function count() {
$clone = clone $this->_dataset;
$rs    = $clone->count()->execute($this->interface);
return $rs->num_rows;
}
public function getDataset(){
return $this->_dataset;
}
}
class ModelException extends Exception {
}
class ModelValidationException extends ModelException {
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/core/Controller.class.php
class Controller {
public static $AccessString = null;
public function __construct() {
}
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/core/Time.class.php
class Time {
const TIMEZONE_GMT     = 0;
const TIMEZONE_DEFAULT = 100;
const TIMEZONE_USER    = 101;
const FORMAT_ISO8601 = 'c';
const FORMAT_RFC2822 = 'r';
const FORMAT_FULLDATETIME = self::FORMAT_ISO8601;
private static $_Instance = null;
private $timezones = array();
private function __construct() {
if (is_numeric(TIME_DEFAULT_TIMEZONE)) {
throw new Exception('Please ensure that the constant TIME_DEFAULT_TIMEZONE is set to a valid timezone string.');
}
$this->timezones[0]   = new DateTimeZone('GMT');
$this->timezones[100] = new DateTimeZone(TIME_DEFAULT_TIMEZONE);
}
private function _getTimezone($timezone) {
if ($timezone == Time::TIMEZONE_USER) {
$timezone = \Core\user()->get('timezone');
if($timezone === null) $timezone = date_default_timezone_get();
if (is_numeric($timezone)) $timezone = Time::TIMEZONE_DEFAULT;
}
if (!isset($this->timezones[$timezone])) {
$this->timezones[$timezone] = new DateTimeZone($timezone);
}
return $this->timezones[$timezone];
}
private static function _Singleton() {
if (self::$_Instance === null) {
self::$_Instance = new self();
}
return self::$_Instance;
}
public static function GetCurrentGMT($format = 'U') {
$date = new DateTime(null, self::_Singleton()->_getTimezone(0));
return $date->format($format);
}
public static function GetCurrent($timezone = Time::TIMEZONE_GMT, $format = 'U') {
$date = new DateTime(null, self::_Singleton()->_getTimezone($timezone));
return $date->format($format);
}
public static function GetRelativeAsString($time, $timezone = Time::TIMEZONE_GMT, $accuracy = 3, $timeformat = 'g:ia', $dateformat = 'M j, Y') {
$nowStamp = Time::GetCurrent($timezone, 'Ymd');
$cStamp   = Time::FormatGMT($time, $timezone, 'Ymd');
if ($nowStamp - $cStamp == 0) return 'Today at ' . Time::FormatGMT($time, $timezone, $timeformat);
elseif ($nowStamp - $cStamp == 1) return 'Yesterday at ' . Time::FormatGMT($time, $timezone, $timeformat);
elseif ($nowStamp - $cStamp == -1) return 'Tomorrow at ' . Time::FormatGMT($time, $timezone, $timeformat);
if ($accuracy <= 2) return Time::FormatGMT($time, $timezone, $dateformat);
if (abs($nowStamp - $cStamp) > 6) return Time::FormatGMT($time, $timezone, $dateformat);
return Time::FormatGMT($time, $timezone, 'l \a\t ' . $timeformat);
}
public static function FormatGMT($timeInGMT, $timezone = Time::TIMEZONE_GMT, $format = 'U') {
if ($timezone === null) $timezone = self::TIMEZONE_GMT;
if (is_numeric($timeInGMT)) $timeInGMT = '@' . $timeInGMT;
$date = new DateTime($timeInGMT, self::_Singleton()->_getTimezone(0));
if ($timezone != Time::TIMEZONE_GMT) $date->setTimezone(self::_Singleton()->_getTimezone($timezone));
return $date->format($format);
}
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/models/ComponentModel.class.php
class ComponentModel extends Model {
public static $Schema = array(
'name'    => array(
'type'      => Model::ATT_TYPE_STRING,
'maxlength' => 48,
'required'  => true,
'null'      => false,
),
'version' => array(
'type'      => Model::ATT_TYPE_STRING,
'maxlength' => 24,
'null'      => false,
),
'enabled' => array(
'type'    => Model::ATT_TYPE_BOOL,
'default' => '1',
'null'    => false,
),
);
public static $Indexes = array(
'primary' => array('name'),
);
} // END class ComponentModel extends Model

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/models/PageModel.class.php
class PageModel extends Model {
public static $Schema = array(
'title' => array(
'type'      => Model::ATT_TYPE_STRING,
'maxlength' => 128,
'default'   => null,
'comment'   => '[Cached] Title of the page',
'null'      => true,
'form'      => array(
'type' => 'text',
'description' => 'Every page needs a title to accompany it, this should be short but meaningful.'
),
),
'baseurl' => array(
'type' => Model::ATT_TYPE_STRING,
'maxlength' => 128,
'required' => true,
'null' => false,
'form' => array('type' => 'system'),
),
'rewriteurl' => array(
'type' => Model::ATT_TYPE_STRING,
'maxlength' => 128,
'null' => false,
'validation' => array('this', 'validateRewriteURL'),
'form' => array(
'title' => 'Rewrite URL',
'type' => 'pagerewriteurl',
'description' => 'Starts with a "/", omit the root web dir.',
),
),
'parenturl' => array(
'type' => Model::ATT_TYPE_STRING,
'maxlength' => 128,
'null' => true,
'formtype' => 'pageparentselect',
'formtitle' => 'Parent URL'
),
'metas' => array(
'type' => Model::ATT_TYPE_TEXT,
'comment' => '[Cached] Serialized array of metainformation',
'null' => false,
'default' => '',
'formtype' => 'pagemetas'
),
'theme_template' => array(
'type' => Model::ATT_TYPE_STRING,
'maxlength' => 128,
'default' => null,
'null' => true,
'comment' => 'Allows the page to define its own theme and widget information.',
'formtype' => 'pagethemeselect'
),
'page_template' => array(
'type' => Model::ATT_TYPE_STRING,
'maxlength' => 64,
'default' => null,
'null' => true,
'comment' => 'Allows the specific page template to be overridden.',
'formtype' => 'hidden'
),
'access' => array(
'type' => Model::ATT_TYPE_STRING,
'maxlength' => 512,
'comment' => 'Access string of the page',
'null' => false,
'default' => '*',
'formtype' => 'access',
'formtitle' => 'Access Permissions',
),
'fuzzy' => array(
'type' => Model::ATT_TYPE_BOOL,
'comment' => 'If this url is fuzzy or an exact match',
'null' => false,
'default' => '0',
'formtype' => 'system'
),
'admin' => array(
'type' => Model::ATT_TYPE_BOOL,
'comment' => 'If this page is an administration page',
'null' => false,
'default' => '0',
'formtype' => 'system'
),
'created' => array(
'type' => Model::ATT_TYPE_CREATED,
'null' => false,
),
'updated' => array(
'type' => Model::ATT_TYPE_UPDATED,
'null' => false,
),
);
public static $Indexes = array(
'primary' => array('baseurl'),
'unique:rewrite_url' => array('rewriteurl'),
);
private $_class;
private $_method;
private $_params;
private $_view;
private static $_RewriteCache = null;
private static $_FuzzyCache = null;
public function  __construct($key = null) {
$this->_linked = array(
'Insertable' => array(
'link' => Model::LINK_HASMANY,
'on' => 'baseurl'
),
);
parent::__construct($key);
}
public function getControllerClass() {
if (!$this->_class) {
$a = PageModel::SplitBaseURL($this->get('baseurl'));
$this->_class = ($a) ? $a['controller'] : null;
}
return $this->_class;
}
public function getControllerMethod() {
if (!$this->_method) {
$a = PageModel::SplitBaseURL($this->get('baseurl'));
$this->_method = ($a) ? $a['method'] : null;
}
return $this->_method;
}
public function getParameters() {
if (!$this->_params) {
$a = PageModel::SplitBaseURL($this->get('baseurl'));
$this->_params = ($a) ? $a['parameters'] : array();
}
return $this->_params;
}
public function getParameter($key) {
$p = $this->getParameters();
return (array_key_exists($key, $p)) ? $p[$key] : null;
}
public function setParameter($key, $val) {
$this->_params[$key] = $val;
}
public function validateRewriteURL($v) {
if (!$v) return true;
if ($v == $this->_data['baseurl']) return true;
if ($v{0} != '/') return "Rewrite URL must start with a '/'";
$ds = Dataset::Init()
->table('page')
->count()
->whereGroup('OR', 'baseurl = ' . $v, 'rewriteurl = ' . $v);
if ($this->exists()) $ds->where('baseurl != ' . $this->_data['baseurl']);
$ds->execute();
if ($ds->num_rows > 0) {
return 'Rewrite URL already taken';
}
return true;
}
public function getTemplateName() {
$t = 'pages/';
$c = $this->getControllerClass();
if (strlen($c) - strrpos($c, 'Controller') == 10) {
$c = substr($c, 0, -10);
}
$t .= strtolower($c) . '/';
if (($override = $this->get('page_template'))) $t .= $override;
else $t .= strtolower($this->getControllerMethod()) . '.tpl';
return $t;
}
public function getView() {
if (!$this->_view) {
$this->_view = new View();
$this->_populateView();
}
return $this->_view;
}
public function hijackView(View $view) {
$this->_view = $view;
$this->_populateView();
}
public function getMeta($name) {
$m = $this->getMetas();
return isset($m[$name]) ? $m[$name] : null;
}
public function getMetas() {
if (!$this->get('metas')) return array();
$m = $this->get('metas');
$m = json_decode($m, true);
if (!$m) return array();
else return $m;
}
public function setMetas($metaarray) {
if (is_array($metaarray) && count($metaarray)) $m = json_encode($metaarray);
else $m = '';
return $this->set('metas', $m);
}
public function setMeta($name, $value) {
$metas = $this->getMetas();
if ($value === '' || $value === null) {
if (isset($metas[$name])) unset($metas[$name]);
}
else {
$metas[$name] = $value;
}
$this->setMetas($metas);
}
public function getResolvedURL() {
if ($this->exists()) {
return ROOT_URL . substr($this->get('rewriteurl'), 1);
}
else {
$s = self::SplitBaseURL($this->get('baseurl'));
return ROOT_URL . substr($s['baseurl'], 1);
}
}
public function execute() {
$transport = $this->getView();
$c = $this->getControllerClass();
$m = $this->getControllerMethod();
if (!($c && $m)) {
$transport->error = View::ERROR_NOTFOUND;
return $transport;
}
if ($c::$AccessString !== null) {
$transport->access = $c::$AccessString;
if (!Core::User()->checkAccess($c::$AccessString)) {
$transport->error = View::ERROR_ACCESSDENIED;
return $transport;
}
}
if ($this->exists()) {
$transport->title  = $this->get('title');
$transport->access = $this->get('access');
}
$r = call_user_func(array($c, $m), $transport);
if ($r === null) {
$r = $transport;
}
elseif (is_numeric($r)) {
$transport->error = $r;
}
if ($transport->error == View::ERROR_NOERROR && $this->exists()) {
$this->set('title', $transport->title);
$this->set('access', $transport->access);
$this->save();
}
return $transport;
}
public function  save() {
if (!$this->get('rewriteurl')) $this->set('rewriteurl', $this->get('baseurl'));
if(!isset($this->_datainit['rewriteurl'])) $this->_datainit['rewriteurl'] = null;
if($this->_data['rewriteurl'] != $this->_datainit['rewriteurl']){
self::$_FuzzyCache = null;
self::$_RewriteCache = null;
}
return parent::save();
}
public function getParentTree() {
if (!$this->exists()) {
$m = strtolower($this->getControllerMethod());
$b = strtolower($this->get('baseurl'));
if ($m == 'edit' && method_exists($this->getControllerClass(), 'view')) {
$p = new PageModel(str_replace('/edit/', '/view/', $b));
if ($p->exists()) {
return array_merge($p->getParentTree(), array($p));
}
}
if ($m == 'delete' && method_exists($this->getControllerClass(), 'view')) {
$p = new PageModel(str_replace('/delete/', '/view/', $b));
if ($p->exists()) {
return array_merge($p->getParentTree(), array($p));
}
}
}
$ret = array();
foreach ($this->_getParentTree() as $p) {
if ($p->exists() || $p->get('title')) {
$ret[] = $p;
}
}
return $ret;
}
private function _getParentTree($antiinfiniteloopcounter = 5) {
if ($antiinfiniteloopcounter <= 0) return array();
if (!$this->exists()) {
self::_LookupUrl('/');
$url = strtolower($this->get('baseurl'));
do {
$url = substr($url, 0, strrpos($url, '/'));
if (isset(self::$_RewriteCache[$url])) {
$url = self::$_RewriteCache[$url];
}
$p = PageModel::Construct($url);
if ($p->get('fuzzy') && !$p->get('parenturl')) {
return array();
}
else {
return array_merge($p->_getParentTree(--$antiinfiniteloopcounter), array($p));
}
}
while ($url);
}
if (!$this->get('parenturl') && $this->get('admin') && strtolower($this->get('baseurl')) != '/admin') {
$url = '/admin';
if (isset(self::$_RewriteCache[$url])) {
$p = PageModel::Construct($url);
}
return array($p);
}
if (!$this->get('parenturl')) return array();
$p = PageModel::Construct($this->get('parenturl'));
return array_merge($p->_getParentTree(--$antiinfiniteloopcounter), array($p));
}
private function _populateView() {
$this->_view->error = View::ERROR_NOERROR;
$this->_view->baseurl = $this->get('baseurl');
$this->_view->setParameters($this->getParameters());
$this->_view->templatename = $this->getTemplateName();
$this->_view->mastertemplate = ($this->get('template')) ? $this->get('template') : ConfigHandler::Get('/theme/default_template');
$this->_view->setBreadcrumbs($this->getParentTree());
}
public static function SplitBaseURL($base) {
if (!$base) return null;
self::_LookupUrl(null);
if (isset(self::$_RewriteCache[$base])) {
$base = self::$_RewriteCache[$base];
} // or find a fuzzy page if there is one.
else {
$try = $base;
while($try != '' && $try != '/') {
if(isset(self::$_FuzzyCache[$try])) {
$base = self::$_FuzzyCache[$try] . substr($base, strlen($try));
break;
}
elseif(in_array($try, self::$_FuzzyCache)) {
$base = self::$_FuzzyCache[array_search($try, self::$_FuzzyCache)] . substr($base, strlen($try));
break;
}
$try = substr($try, 0, strrpos($try, '/'));
}
}
$base = trim($base, '/');
$args = null;
if (($qpos = strpos($base, '?')) !== false) {
$argstring = substr($base, $qpos + 1);
preg_match_all('/([^=&]*)={0,1}([^&]*)/', $argstring, $matches);
$args = array();
foreach ($matches[1] as $k => $v) {
if (!$v) continue;
$args[$v] = $matches[2][$k];
}
$base = substr($base, 0, $qpos);
}
$posofslash = strpos($base, '/');
if ($posofslash) $controller = substr($base, 0, $posofslash);
else $controller = $base;
if (class_exists($controller . 'Controller')) {
switch (true) {
case is_subclass_of($controller . 'Controller', 'Controller_2_1'):
case is_subclass_of($controller . 'Controller', 'Controller'):
$controller = $controller . 'Controller';
break;
default:
return null;
}
}
elseif (class_exists($controller)) {
switch (true) {
case is_subclass_of($controller, 'Controller_2_1'):
case is_subclass_of($controller, 'Controller'):
$controller = $controller;
break;
default:
return null;
}
}
else {
return null;
}
if ($posofslash !== false) $base = substr($base, $posofslash + 1);
else $base = false;
if ($base) {
$posofslash = strpos($base, '/');
if ($posofslash) {
$method = str_replace('/', '_', $base);
while (!method_exists($controller, $method) && strpos($method, '_')) {
$method = substr($method, 0, strrpos($method, '_'));
}
}
else {
$method = $base;
}
$base = substr($base, strlen($method) + 1);
}
else {
$method = 'Index';
}
if (!method_exists($controller, $method)) {
return null;
}
if ($method{0} == '_') return null;
$params = ($base !== false) ? explode('/', $base) : null;
$baseurl = '/' . ((strpos($controller, 'Controller') == strlen($controller) - 10) ? substr($controller, 0, -10) : $controller);
if (!($method == 'Index' && !$params)) $baseurl .= '/' . str_replace('_', '/', $method);
$baseurl .= ($params) ? '/' . implode('/', $params) : '';
$rewriteurl = self::_LookupReverseUrl($baseurl);
if ($args) {
$rewriteurl .= '?' . $argstring;
if ($params) $params = array_merge($params, $args);
else $params = $args;
}
return array('controller' => $controller,
'method' => $method,
'parameters' => $params,
'baseurl' => $baseurl,
'rewriteurl' => $rewriteurl);
}
private static function _LookupUrl($url = null) {
if (self::$_RewriteCache === null) {
$s = new Dataset();
$s->select('rewriteurl, baseurl, fuzzy');
$s->table(DB_PREFIX . 'page');
$rs = $s->execute();
self::$_RewriteCache = array();
self::$_FuzzyCache = array();
foreach ($rs as $row) {
self::$_RewriteCache[strtolower($row['rewriteurl'])] = strtolower($row['baseurl']);
if ($row['fuzzy']) self::$_FuzzyCache[strtolower($row['rewriteurl'])] = strtolower($row['baseurl']);
}
}
if ($url === null) return; // maybe this was just called to update the local rewrite and fuzzy caches.
return (isset(self::$_RewriteCache[$url])) ? self::$_RewriteCache[$url] : $url;
}
private static function _LookupReverseUrl($url) {
self::_LookupUrl(null);
$url = strtolower($url);
if (($key = array_search($url, self::$_RewriteCache)) !== false) {
return $key;
}
$try = $url;
while ($try != '' && $try != '/') {
if (in_array($try, self::$_FuzzyCache)) {
$url = array_search($try, self::$_FuzzyCache) . substr($url, strlen($try));
return $url;
}
$try = substr($try, 0, strrpos($try, '/'));
}
return $url;
}
public static function GetPagesAsOptions($where = false, $blanktext = false) {
if ($where instanceof ModelFactory) {
$f = $where;
}
elseif (!$where) {
$f = new ModelFactory('PageModel');
}
else {
$f = new ModelFactory('PageModel');
$f->where($where);
}
$pages = $f->get();
$opts = array();
foreach ($pages as $p) {
$t = '';
foreach ($p->getParentTree() as $subp) {
$t .= $subp->get('title') . ' &raquo; ';
}
$t .= $p->get('title');
$t .= ' ( ' . $p->get('rewriteurl') . ' )';
$opts[$p->get('baseurl')] = $t;
}
asort($opts);
if ($blanktext) $opts = array_merge(array("" => $blanktext), $opts);
return $opts;
}
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/core/Component.class.php
class Component extends XMLLoader {
protected $_name;
protected $_version;
protected $_description;
protected $_updateSites = array();
protected $_authors = array();
protected $_iterator;
protected $_type;
public $enabled = true;
public $_versionDB = false;
private $_requires = array();
private $_execMode = 'WEB';
const ERROR_NOERROR = 0; // 0000
const ERROR_INVALID = 1; // 0001
const ERROR_WRONGEXECMODE = 2; // 0010
const ERROR_MISSINGDEPENDENCY = 4; // 0100
const ERROR_CONFLICT = 8; // 1000
public $error = 0;
public $errstrs = array();
public function __construct($name = null) {
$this->_name     = $name;
$this->_type     = InstallArchiveAPI::TYPE_COMPONENT;
$this->_rootname = 'component';
}
public function load() {
try {
$XMLFilename = $this->getXMLFilename();
if (!is_readable($XMLFilename)) {
throw new Exception('Unable to open XML Metafile [' . $XMLFilename . '] for reading.');
}
$this->setFilename($XMLFilename);
$this->setRootName($this->_type);
if (!parent::load()) {
throw new Exception('Parsing of XML Metafile [' . $XMLFilename . '] failed, not valid XML.');
}
if (strtolower($this->getRootDOM()->getAttribute("name")) != strtolower($this->_name)) {
throw new Exception('Name mismatch in XML Metafile [' . $XMLFilename . '], defined name does not match expected name.');
}
$this->_version = $this->getRootDOM()->getAttribute("version");
$dat = ComponentFactory::_LookupComponentData($this->_name);
if (!$dat) return;
$this->_versionDB = $dat['version'];
$this->_enabled   = ($dat['enabled']) ? true : false;
}
catch (Exception $e) {
echo '<pre>' . $e->__toString() . '</pre>';
die("Could not load " . $this->getName());
}
if (($mode = @$this->getRootDOM()->getAttribute('execmode'))) {
$this->_execMode = strtoupper($mode);
}
return true;
}
public function save($minified = false) {
$this->getRootDOM()->setAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
$this->removeElements('//otherfiles');
$this->removeElements('//library/file');
$this->removeElements('//module/file');
$this->removeElements('//view/file');
$otherfilesnode = $this->getElement('//otherfiles');
$it      = $this->getDirectoryIterator();
$hasview = $this->hasView();
$viewd   = ($hasview) ? $this->getViewSearchDir() : null;
$assetd  = $this->getAssetDir();
$strlen  = strlen($this->getBaseDir());
foreach ($it as $file) {
$el    = false;
$fname = substr($file->getFilename(), $strlen);
if ($hasview && $file->inDirectory($viewd)) {
$el = $this->getElement('/view/file[@filename="' . $fname . '"]');
}
elseif ($assetd && $file->inDirectory($assetd)) {
$el = $this->getElement('/assets/file[@filename="' . $fname . '"]');
}
else {
$el = $this->getElement('//library/file[@filename="' . $fname . '"]|//module/file[@filename="' . $fname . '"]|//view/file[@filename="' . $fname . '"]', false);
if (preg_match('/\.php$/i', $fname)) {
$fconts = file_get_contents($file->getFilename());
$fconts = preg_replace(':/\*.*\*/:Us', '', $fconts);
$fconts = preg_replace('://.*$:', '', $fconts);
if ($el) {
$getnames = ($el->parentNode->nodeName == 'library' || $el->parentNode->nodeName == 'module');
}
else {
if (preg_match('/^(abstract ){0,1}class[ ]*[a-z0-9_\-]*[ ]*extends controller/im', $fconts)) {
$el       = $this->getElement('/module/file[@filename="' . $fname . '"]');
$getnames = true;
}
elseif (preg_match('/^class[ ]*[a-z0-9_\-]*[ ]*extends widget/im', $fconts)) {
$el       = $this->getElement('/module/file[@filename="' . $fname . '"]');
$getnames = true;
}
elseif (preg_match('/^(abstract |final ){0,1}class[ ]*[a-z0-9_\-]*/im', $fconts)) {
$el       = $this->getElement('/library/file[@filename="' . $fname . '"]');
$getnames = true;
}
elseif (preg_match('/^interface[ ]*[a-z0-9_\-]*/im', $fconts)) {
$el       = $this->getElement('/library/file[@filename="' . $fname . '"]');
$getnames = true;
}
else {
$el       = $this->getElement('/otherfiles/file[@filename="' . $fname . '"]');
$getnames = false;
}
}
if ($getnames) {
$viewclasses = array();
preg_match_all('/^(abstract |final ){0,1}class[ ]*([a-z0-9_\-]*)[ ]*extends[ ]*controller/im', $fconts, $ret);
foreach ($ret[2] as $foundclass) {
$this->getElementFrom('provides[@type="controller"][@name="' . $foundclass . '"]', $el);
$viewclasses[] = $foundclass;
}
preg_match_all('/^class[ ]*([a-z0-9_\-]*)[ ]*extends[ ]*widget/im', $fconts, $ret);
foreach ($ret[1] as $foundclass) {
$this->getElementFrom('provides[@type="widget"][@name="' . $foundclass . '"]', $el);
$viewclasses[] = $foundclass;
}
preg_match_all('/^(abstract |final ){0,1}class[ ]*([a-z0-9_\-]*)/im', $fconts, $ret);
foreach ($ret[2] as $foundclass) {
if (in_array($foundclass, $viewclasses)) continue;
$this->getElementFrom('provides[@type="class"][@name="' . $foundclass . '"]', $el);
}
preg_match_all('/^(interface)[ ]*([a-z0-9_\-]*)/im', $fconts, $ret);
foreach ($ret[2] as $foundclass) {
if (in_array($foundclass, $viewclasses)) continue;
$this->getElementFrom('provides[@type="interface"][@name="' . $foundclass . '"]', $el);
}
}
}
if (!$el) {
$el = $this->getElement('/otherfiles/file[@filename="' . $fname . '"]');
}
}
if ($el) {
$el->setAttribute('md5', $file->getHash());
}
}
if (!isset($viewclasses)) $viewclasses = array();
foreach ($viewclasses as $c) {
if (strlen($c) - strpos($c, 'Controller') == 10) $c = substr($c, 0, -10);
$data = Dataset::Init()->table('page')->select('*')->where("baseurl = /$c", 'admin=1', 'fuzzy=0')->execute();
foreach ($data as $row) {
$node = $this->getElement('/pages/page[@baseurl="' . $row['baseurl'] . '"]');
$node->setAttribute('admin', $row['admin']);
$node->setAttribute('widget', $row['widget']);
$node->setAttribute('access', $row['access']);
$node->setAttribute('title', $row['title']);
}
$data = Dataset::Init()->table('page')->select('*')->where("baseurl LIKE /$c/%", 'admin=1', 'fuzzy=0')->execute();
foreach ($data as $row) {
$node = $this->getElement('/pages/page[@baseurl="' . $row['baseurl'] . '"]');
$node->setAttribute('admin', $row['admin']);
$node->setAttribute('widget', $row['widget']);
$node->setAttribute('access', $row['access']);
$node->setAttribute('title', $row['title']);
}
}
$data = Dataset::Init()->table('config')->select('*')->where('key LIKE /' . $this->getName() . '/%')->execute();
foreach ($data as $row) {
$node = $this->getElement('/configs/config[@key="' . $row['key'] . '"]');
$node->setAttribute('type', $row['type']);
$node->setAttribute('default', $row['default_value']);
$node->setAttribute('description', $row['description']);
if ($row['options']) $node->setAttribute('options', $row['options']);
else $node->removeAttribute('options');
}
$XMLFilename = $this->getXMLFilename();
if ($minified) {
file_put_contents($XMLFilename, $this->asMinifiedXML());
}
else {
file_put_contents($XMLFilename, $this->asPrettyXML());
}
}
public function savePackageXML($minified = true, $filename = false) {
$dom = new XMLLoader();
$dom->setRootName('package');
$dom->load();
$dom->getRootDOM()->setAttribute('type', 'component');
$dom->getRootDOM()->setAttribute('name', $this->getName());
$dom->getRootDOM()->setAttribute('version', $this->getVersion());
$dom->createElement('packager[version="' . Core::GetComponent()->getVersion() . '"]');
foreach ($this->getRootDOM()->getElementsByTagName('provides') as $u) {
$newu = $dom->getDOM()->importNode($u);
$dom->getRootDOM()->appendChild($newu);
}
$dom->getElement('/provides[type="component"][name="' . strtolower($this->getName()) . '"][version="' . $this->getVersion() . '"]');
foreach ($this->getRootDOM()->getElementsByTagName('requires') as $u) {
$newu = $dom->getDOM()->importNode($u);
$dom->getRootDOM()->appendChild($newu);
}
foreach ($this->getRootDOM()->getElementsByTagName('upgrade') as $u) {
$newu = $dom->getDOM()->importNode($u);
$dom->getRootDOM()->appendChild($newu);
}
$desc = $this->getElement('/description', false);
if ($desc) {
$newd            = $dom->getDOM()->importNode($desc);
$newd->nodeValue = $desc->nodeValue;
$dom->getRootDOM()->appendChild($newd);
}
$out = ($minified) ? $dom->asMinifiedXML() : $dom->asPrettyXML();
if ($filename) {
file_put_contents($filename, $out);
}
else {
return $out;
}
}
public function loadFiles() {
if ($this->hasLibrary()) {
foreach ($this->getElementByTagName('library')->getElementsByTagName('file') as $f) {
$type = strtolower(@$f->getAttribute('type'));
if ($type == 'autoload') require_once($this->getBaseDir() . $f->getAttribute('filename'));
}
}
foreach ($this->getElementsByTagName('hookregister') as $h) {
$hook              = new Hook($h->getAttribute('name'));
$hook->description = $h->getAttribute('description');
HookHandler::RegisterHook($hook);
}
foreach ($this->getElementsByTagName('hook') as $h) {
$event = $h->getAttribute('name');
$call  = $h->getAttribute('call');
$type  = @$h->getAttribute('type');
HookHandler::AttachToHook($event, $call, $type);
}
foreach ($this->getElements('/forms/formelement') as $node) {
Form::$Mappings[$node->getAttribute('name')] = $node->getAttribute('class');
}
return true;
}
public function getLibraryList() {
$libs = array();
if ($this->hasLibrary()) {
$libs[strtolower($this->_name)] = $this->_versionDB;
}
foreach ($this->getElements('//provides') as $p) {
if (strtolower($p->getAttribute('type')) == 'library') {
$v = @$p->getAttribute('version');
if (!$v) $v = $this->_versionDB;
$libs[strtolower($p->getAttribute('name'))] = $v;
}
}
return $libs;
}
public function getClassList() {
$classes = array();
if ($this->hasLibrary()) {
foreach ($this->getElementByTagName('library')->getElementsByTagName('file') as $f) {
$filename = $this->getBaseDir() . $f->getAttribute('filename');
foreach ($f->getElementsByTagName('provides') as $p) {
$n = strtolower($p->getAttribute('name'));
if (strtolower($p->getAttribute('type')) == 'class') $classes[$n] = $filename;
if (strtolower($p->getAttribute('type')) == 'interface') $classes[$n] = $filename;
}
}
}
if ($this->hasModule()) {
foreach ($this->getElementByTagName('module')->getElementsByTagName('file') as $f) {
$filename = $this->getBaseDir() . $f->getAttribute('filename');
foreach ($f->getElementsByTagName('provides') as $p) {
$n = strtolower($p->getAttribute('name'));
switch (strtolower($p->getAttribute('type'))) {
case 'class':
case 'controller':
case 'widget':
$classes[$n] = $filename;
break;
}
}
}
}
return $classes;
}
public function getWidgetList() {
$widgets = array();
if ($this->hasModule()) {
foreach ($this->getElementByTagName('module')->getElementsByTagName('file') as $f) {
foreach ($f->getElementsByTagName('provides') as $p) {
if (strtolower($p->getAttribute('type')) == 'widget') {
$widgets[] = $p->getAttribute('name');
}
}
}
}
return $widgets;
}
public function getViewClassList() {
$classes = array();
if ($this->hasModule()) {
foreach ($this->getElementByTagName('module')->getElementsByTagName('file') as $f) {
$filename = $this->getBaseDir() . $f->getAttribute('filename');
foreach ($f->getElementsByTagName('provides') as $p) {
switch (strtolower($p->getAttribute('type'))) {
case 'viewclass':
case 'view_class':
$classes[$p->getAttribute('name')] = $filename;
break;
}
}
}
}
return $classes;
}
public function getViewList() {
$views = array();
if ($this->hasView()) {
foreach ($this->getElementByTagName('view')->getElementsByTagName('tpl') as $t) {
$filename     = $this->getBaseDir() . $t->getAttribute('filename');
$name         = $t->getAttribute('name');
$views[$name] = $filename;
}
}
return $views;
}
public function getControllerList() {
$classes = array();
if ($this->hasModule()) {
foreach ($this->getElementByTagName('module')->getElementsByTagName('file') as $f) {
$filename = $this->getBaseDir() . $f->getAttribute('filename');
foreach ($f->getElementsByTagName('provides') as $p) {
$n = strtolower($p->getAttribute('name'));
switch (strtolower($p->getAttribute('type'))) {
case 'controller':
$classes[$n] = $filename;
break;
}
}
}
}
return $classes;
}
public function getSmartyPluginDirectory() {
$d = $this->getElement('/smartyplugins')->getAttribute('directory');
if ($d) return $this->getBaseDir() . $d;
else return false;
}
public function getScriptLibraryList() {
$libs = array();
if ($this->hasLibrary()) {
foreach ($this->getElementByTagName('library')->getElementsByTagName('scriptlibrary') as $s) {
$libs[strtolower($s->getAttribute('name'))] = $s->getAttribute('call');
}
}
return $libs;
}
public function getViewSearchDir() {
if ($this->hasView()) {
$att = @$this->getElement('/view')->getAttribute('searchdir');
if ($att) {
return $this->getBaseDir() . $att . '/';
}
elseif (($att = $this->getElements('/view/searchdir')->item(0))) {
return $this->getBaseDir() . $att->getAttribute('dir') . '/';
}
elseif (is_dir($this->getBaseDir() . 'templates')) {
return $this->getBaseDir() . 'templates';
}
else return false;
}
}
public function getAssetDir() {
if ($this->getName() == 'core') $d = $this->getBaseDir() . 'core/assets';
else $d = $this->getBaseDir() . 'assets';
if (is_dir($d)) return $d;
else return null;
}
public function getIncludePaths() {
$dirs = array();
if ($this->hasLibrary()) {
foreach ($this->getElementByTagName('library')->getElementsByTagName('includepath') as $t) {
$dir = $t->getAttribute('dir');
if ($dir == '.') $dirs[] = $this->getBaseDir();
else $dirs[] = $this->getBaseDir() . $t->getAttribute('dir') . '/';
}
}
return $dirs;
}
public function getDBSchemaTableNames() {
$ret = array();
foreach ($this->getElement('dbschema')->getElementsByTagName('table') as $table) {
$ret[] = $table->getAttribute('name');
}
return $ret;
}
public function setDBSchemaTableNames($arr) {
$this->getRootDOM()->removeChild($this->getElement('/dbschema'));
$node = $this->getElement('/dbschema[@prefix="' . DB_PREFIX . '"]');
foreach ($arr as $k) {
if (!trim($k)) continue;
$tablenode = $this->getDOM()->createElement('table');
$tablenode->setAttribute('name', $k);
$node->appendChild($tablenode);
unset($tablenode);
}
}
public function getVersionInstalled() {
return $this->_versionDB;
}
public function getType() {
if ($this->_name == 'core') return 'core';
else return 'component';
}
public function isValid() {
return (!$this->error & Component::ERROR_INVALID);
}
public function isInstalled() {
return ($this->_versionDB === false) ? false : true;
}
public function needsUpdated() {
return ($this->_versionDB != $this->_version);
}
public function getErrors($glue = '<br/>') {
if ($glue) {
return implode($glue, $this->errstrs);
}
else {
return $this->errors;
}
}
public function isLoadable() {
if ($this->error & Component::ERROR_INVALID) {
return false;
}
$this->error   = 0;
$this->errstrs = array();
if ($this->_execMode != 'BOTH') {
if ($this->_execMode != EXEC_MODE) {
$this->error     = $this->error | Component::ERROR_WRONGEXECMODE;
$this->errstrs[] = 'Wrong execution mode, can only be ran in ' . $this->_execMode . ' mode';
}
}
foreach ($this->getRequires() as $r) {
switch ($r['type']) {
case 'library':
if (!Core::IsLibraryAvailable($r['name'], $r['version'], $r['operation'])) {
$this->error     = $this->error | Component::ERROR_MISSINGDEPENDENCY;
$this->errstrs[] = 'Requires missing library ' . $r['name'] . ' ' . $r['version'];
}
break;
case 'jslibrary':
if (!Core::IsJSLibraryAvailable($r['name'], $r['version'], $r['operation'])) {
$this->error     = $this->error | Component::ERROR_MISSINGDEPENDENCY;
$this->errstrs[] = 'Requires missing JSlibrary ' . $r['name'] . ' ' . $r['version'];
}
break;
case 'component':
if (!Core::IsComponentAvailable($r['name'], $r['version'], $r['operation'])) {
$this->error     = $this->error | Component::ERROR_MISSINGDEPENDENCY;
$this->errstrs[] = 'Requires missing component ' . $r['name'] . ' ' . $r['version'];
}
break;
case 'define':
if (!defined($r['name'])) {
$this->error     = $this->error | Component::ERROR_MISSINGDEPENDENCY;
$this->errstrs[] = 'Requires missing define ' . $r['name'];
}
if ($r['value'] != null && constant($r['name']) != $r['value']) {
$this->error     = $this->error | Component::ERROR_MISSINGDEPENDENCY;
$this->errstrs[] = 'Requires wrong define ' . $r['name'] . '(' . $r['value'] . ')';
}
break;
}
}
if ($this->error) return false;
$cs = $this->getClassList();
foreach ($cs as $c => $file) {
if (Core::IsClassAvailable($c)) {
$this->error     = $this->error | Component::ERROR_CONFLICT;
$this->errstrs[] = $c . ' already defined in another component';
break;
}
}
return (!$this->error) ? true : false;
}
public function getJSLibraries() {
$ret = array();
foreach ($this->getRootDOM()->getElementsByTagName('jslibrary') as $node) {
$lib       = new JSLibrary();
$lib->name = $node->getAttribute('name');
$lib->version                = (($v = @$node->getAttribute('version')) ? $v : $this->getRootDOM()->getAttribute('version'));
$lib->baseDirectory          = ROOT_PDIR . 'components/' . $this->getName() . '/';
$lib->DOMNode                = $node;
$ret[strtolower($lib->name)] = $lib;
}
return $ret;
}
public function hasLibrary() {
return ($this->getRootDOM()->getElementsByTagName('library')->length) ? true : false;
}
public function hasJSLibrary() {
return ($this->getRootDOM()->getElementsByTagName('jslibrary')->length) ? true : false;
}
public function hasModule() {
return ($this->getRootDOM()->getElementsByTagName('module')->length) ? true : false;
}
public function hasView() {
return ($this->getRootDOM()->getElementsByTagName('view')->length) ? true : false;
}
public function install() {
if ($this->isInstalled()) return false;
if (!$this->isLoadable()) return false;
$this->_parseDBSchema();
$this->_parseConfigs();
$this->_parsePages();
$this->_installAssets();
$c = new ComponentModel($this->_name);
$c->set('version', $this->_version);
$c->save();
$this->_versionDB = $this->_version;
$this->loadFiles();
if (class_exists('Core')) {
$ch = Core::Singleton();
$ch->_registerComponent($this);
}
return true;
}
public function reinstall() {
if (!$this->isInstalled()) return false;
$changed = false;
if ($this->_parseDBSchema()) $changed = true;
if ($this->_parseConfigs()) $changed = true;
if ($this->_parsePages()) $changed = true;
if ($this->_installAssets()) $changed = true;
return $changed;
}
public function upgrade() {
if (!$this->isInstalled()) return false;
$canBeUpgraded = true;
while ($canBeUpgraded) {
$canBeUpgraded = false;
foreach ($this->getRootDOM()->getElementsByTagName('upgrade') as $u) {
if ($this->_versionDB == @$u->getAttribute('from')) {
$canBeUpgraded = true;
$result = InstallTask::ParseNode($u, $this->getBaseDir());
if (!$result) {
if (DEVELOPMENT_MODE) {
trigger_error('Upgrade of Component ' . $this->_name . ' failed.', E_USER_NOTICE);
}
return;
}
$this->_versionDB = @$u->getAttribute('to');
$c                = new ComponentModel($this->_name);
$c->set('version', $this->_versionDB);
$c->save();
}
}
}
$this->_parseDBSchema();
$this->_parseConfigs();
$this->_parsePages();
$this->_installAssets();
}
public function getProvides() {
$ret = array();
$ret[] = array(
'name'    => strtolower($this->getName()),
'type'    => 'component',
'version' => $this->getVersion()
);
foreach ($this->getElements('provides') as $el) {
$ret[] = array(
'name'      => strtolower($el->getAttribute('name')),
'type'      => $el->getAttribute('type'),
'version'   => $el->getAttribute('version'),
'operation' => $el->getAttribute('operation'),
);
}
return $ret;
}
public function getRequires() {
$ret = array();
foreach ($this->getRootDOM()->getElementsByTagName('requires') as $r) {
$t  = $r->getAttribute('type');
$n  = $r->getAttribute('name');
$v  = @$r->getAttribute('version');
$op = @$r->getAttribute('operation');
if ($v == '') $v = false;
if ($op == '') $op = 'ge';
$ret[] = array(
'type'      => strtolower($t),
'name'      => $n,
'version'   => strtolower($v),
'operation' => strtolower($op),
);
}
return $ret;
}
public function getDescription() {
if (is_null($this->_description)) $this->_description = $this->getElement('//description')->nodeValue;
return $this->_description;
}
public function setDescription($desc) {
$this->_description = $desc;
$this->getElement('//description')->nodeValue = $desc;
}
public function setPackageMaintainer($name, $email) {
$this->getElement('/changelog[@version="' . $this->_version . '"]/packagemeta/date')->nodeValue = Time::GetCurrent(Time::TIMEZONE_GMT, 'r');
$this->getElement('/changelog[@version="' . $this->_version . '"]/packagemeta/maintainer[@name="' . $name . '"][@email="' . $email . '"]');
$this->getElement('/changelog[@version="' . $this->_version . '"]/packagemeta/packager')->nodeValue = 'CAE2 ' . ComponentHandler::GetComponent('core')->getVersion();
}
public function getChangelog($version = false) {
if (!$version) $version = $this->getVersion();
return $this->getElement('/changelog[@version="' . $version . '"]/notes')->nodeValue;
}
public function setChangelog($text, $version = false) {
if (!$version) $version = $this->getVersion();
$this->getElement('/changelog[@version="' . $version . '"]/notes')->nodeValue = $text;
}
public function getXMLFilename($prefix = ROOT_PDIR) {
if ($this->_name == 'core') return $prefix . 'core/' . 'component.xml';
else return $prefix . 'components/' . $this->_name . '/' . 'component.xml';
}
public function getBaseDir($prefix = ROOT_PDIR) {
switch ($this->_type) {
case InstallArchiveAPI::TYPE_COMPONENT:
if ($this->_name == 'core') return $prefix;
else return $prefix . 'components/' . $this->_name . '/';
break;
case InstallArchiveAPI::TYPE_LIBRARY:
return $prefix . 'libraries/' . $this->_name . '/';
break;
case InstallArchiveAPI::TYPE_THEME:
return $prefix . 'themes/' . $this->_name . '/';
break;
}
}
public function getChangedFiles() {
$ret = array();
foreach ($this->getElementsByTagName('file') as $node) {
if (!($filename = @$node->getAttribute('filename'))) continue;
if ($node->getAttribute('md5') != md5_file($this->getBaseDir() . $filename)) {
$ret[] = $filename;
}
}
return $ret;
}
public function getName() {
return $this->_name;
}
public function getVersion() {
return $this->_version;
}
public function setVersion($vers) {
if ($vers == $this->_version) return;
if (($upg = $this->getElement('/upgrade[@from=""][@to=""]', false))) {
$upg->setAttribute('from', $this->_version);
$upg->setAttribute('to', $vers);
}
elseif (($upg = $this->getElement('/upgrade[@from="' . $this->_version . '"][@to=""]', false))) {
$upg->setAttribute('to', $vers);
}
else {
$newupgrade = $this->getElement('/upgrade[@from="' . $this->_version . '"][@to="' . $vers . '"]');
}
$newchangelog = $this->getElement('/changelog[@version="' . $vers . '"]');
foreach ($this->getElementsByTagName('changelog') as $el) {
if (!@$el->getAttribute('version')) {
$newchangelog->nodeValue .= "\n" . $el->nodeValue;
$el->nodeValue = '';
break;
}
}
$this->_version = $vers;
$this->getRootDOM()->setAttribute('version', $vers);
}
public function getRawXML() {
return $this->asPrettyXML();
}
public function getLicenses() {
$ret = array();
foreach ($this->getRootDOM()->getElementsByTagName('license') as $el) {
$url   = @$el->getAttribute('url');
$ret[] = array(
'title' => $el->nodeValue,
'url'   => $url
);
}
return $ret;
}
public function setLicenses($licenses) {
$this->removeElements('/license');
foreach ($licenses as $lic) {
$str          = '/license' . ((isset($lic['url']) && $lic['url']) ? '[@url="' . $lic['url'] . '"]' : '');
$l            = $this->getElement($str);
$l->nodeValue = $lic['title'];
}
}
public function getAuthors() {
$ret = array();
foreach ($this->getRootDOM()->getElementsByTagName('author') as $el) {
$ret[] = array(
'name'  => $el->getAttribute('name'),
'email' => @$el->getAttribute('email'),
);
}
return $ret;
}
public function setAuthors($authors) {
$this->removeElements('/author');
foreach ($authors as $a) {
if (isset($a['email']) && $a['email']) {
$this->getElement('//component/author[@name="' . $a['name'] . '"][@email="' . $a['email'] . '"]');
}
else {
$this->getElement('//component/author[@name="' . $a['name'] . '"]');
}
}
}
public function getAllFilenames() {
$ret  = array();
$list = $this->getElements('//component/library/file|//component/module/file|//component/view/file|//component/otherfiles/file|//component/assets/file');
foreach ($list as $el) {
$md5   = @$el->getAttribute('md5');
$ret[] = array(
'file' => $el->getAttribute('filename'),
'md5'  => $md5
);
}
return $ret;
}
public function getDirectoryIterator() {
if (is_null($this->_iterator)) {
$this->_iterator = new CAEDirectoryIterator();
$this->_iterator->addIgnore($this->getXMLFilename());
if ($this->_name == 'core') {
$this->_iterator->addIgnores('components/', 'config/', 'dropins/', 'exports/', 'nbproject/', 'scripts/', 'themes/', 'update_site/', 'utils/');
if (ConfigHandler::Get('/core/filestore/assetdir')) $this->_iterator->addIgnore(ConfigHandler::Get('/core/filestore/assetdir'));
if (ConfigHandler::Get('/core/filestore/publicdir')) $this->_iterator->addIgnore(ConfigHandler::Get('/core/filestore/publicdir'));
}
$list = $this->getElements('/ignorefiles/file');
foreach ($list as $el) {
$this->_iterator->addIgnores($this->getBaseDir() . $el->getAttribute('filename'));
}
$this->_iterator->setPath($this->getBaseDir());
$this->_iterator->scan();
}
return clone $this->_iterator;
}
private function _parseConfigs() {
$changed = false;
$node = $this->getElement('configs');
foreach ($node->getElementsByTagName('config') as $confignode) {
$key = $confignode->getAttribute('key');
$m   = ConfigHandler::GetConfig($key);
$m->set('options', $confignode->getAttribute('options'));
$m->set('type', $confignode->getAttribute('type'));
$m->set('default_value', $confignode->getAttribute('default'));
$m->set('description', $confignode->getAttribute('description'));
$m->set('mapto', $confignode->getAttribute('mapto'));
if (!$m->get('value')) $m->set('value', $confignode->getAttribute('default'));
if ($m->save()) $changed = true;
}
return $changed;
} // private function _parseConfigs
private function _parsePages() {
$changed = false;
$node = $this->getElement('pages');
foreach ($node->getElementsByTagName('page') as $subnode) {
$m = new PageModel($subnode->getAttribute('baseurl'));
if (!$m->get('rewriteurl')) {
if ($subnode->getAttribute('rewriteurl')) $m->set('rewriteurl', $subnode->getAttribute('rewriteurl'));
else $m->set('rewriteurl', $subnode->getAttribute('baseurl'));
}
if (!$m->get('title')) $m->set('title', $subnode->getAttribute('title'));
if ($m->get('access') == '*') $m->set('access', $subnode->getAttribute('access'));
$m->set('widget', $subnode->getAttribute('widget'));
$m->set('admin', $subnode->getAttribute('admin'));
if ($m->save()) $changed = true;
}
return $changed;
}
private function _parseDBSchema() {
$node   = $this->getElement('dbschema');
$prefix = $node->getAttribute('prefix');
$changed = false;
$classes = $this->getClassList();
foreach ($classes as $k => $v) {
if ($k == 'model' || strpos($k, 'model') !== strlen($k) - 5) unset($classes[$k]);
}
foreach ($classes as $m => $file) {
require_once($file);
$s         = $m::GetSchema();
$i         = $m::GetIndexes();
$tablename = $m::GetTableName();
$schema = array('schema'  => $s,
'indexes' => $i);
if (Core::DB()->tableExists($tablename)) {
Core::DB()->modifyTable($tablename, $schema);
}
else {
Core::DB()->createTable($tablename, $schema);
}
}
return $changed;
} // private function _parseDBSchema()
private function _installAssets() {
$assetbase = ConfigHandler::Get('/core/filestore/assetdir');
$theme     = ConfigHandler::Get('/theme/selected');
$changed   = false;
foreach ($this->getElements('/assets/file') as $node) {
$b = $this->getBaseDir();
$f = new File_local_backend($b . $node->getAttribute('filename'));
$newfilename = 'assets' . substr($b . $node->getAttribute('filename'), strlen($this->getAssetDir()));
$nf          = Core::File($newfilename);
if ($theme === null) {
}
elseif ($theme != 'default' && strpos($nf->getFilename(), $assetbase . $theme) !== false) {
$nf->setFilename(str_replace($assetbase . $theme, $assetbase . 'default', $nf->getFilename()));
}
if ($nf->exists() && $nf->identicalTo($f)) continue;
$f->copyTo($nf, true);
$changed = true;
}
if (!$changed) return false;
Core::Cache()->delete('asset-resolveurl');
return true;
}
public function isEnabled() {
return ($this->_versionDB !== false);
}
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/core/Component_2_1.php
class Component_2_1 {
private $_xmlloader = null;
protected $_name;
protected $_version;
protected $_enabled;
protected $_description;
protected $_updateSites = array();
protected $_authors = array();
protected $_iterator;
protected $enabled = true;
private $_versionDB = false;
private $_execMode = 'WEB';
private $_file;
private $_permissions = array();
const ERROR_NOERROR = 0; // 0000
const ERROR_INVALID = 1; // 0001
const ERROR_WRONGEXECMODE = 2; // 0010
const ERROR_MISSINGDEPENDENCY = 4; // 0100
const ERROR_CONFLICT = 8; // 1000
public $error = 0;
public $errstrs = array();
private $_loaded = false;
private $_smartyPluginDirectory = null;
private $_viewSearchDirectory = null;
private $_classlist = null;
private $_widgetlist = null;
private $_requires = null;
public function __construct($filename = null) {
$this->_file = \Core\file($filename);
$this->_xmlloader = new XMLLoader();
$this->_xmlloader->setRootName('component');
if (!$this->_xmlloader->loadFromFile($filename)) {
throw new Exception('Parsing of XML Metafile [' . $filename . '] failed, not valid XML.');
}
}
public function load() {
if ($this->_loaded) return;
if (($mode = $this->_xmlloader->getRootDOM()->getAttribute('execmode'))) {
$this->_execMode = strtoupper($mode);
}
$this->_name    = $this->_xmlloader->getRootDOM()->getAttribute('name');
$this->_version = $this->_xmlloader->getRootDOM()->getAttribute("version");
$dat = ComponentFactory::_LookupComponentData($this->_name);
if (!$dat) return;
$this->_versionDB = $dat['version'];
$this->_enabled   = ($dat['enabled']) ? true : false;
$this->_loaded    = true;
$this->_permissions = array();
foreach($this->_xmlloader->getElements('/permissions/permission') as $el){
$this->_permissions[$el->getAttribute('key')] = $el->getAttribute('description');
}
}
public function save($minified = false) {
$this->_xmlloader->getRootDOM()->setAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
$XMLFilename = $this->_file->getFilename();
if ($minified) {
file_put_contents($XMLFilename, $this->_xmlloader->asMinifiedXML());
}
else {
file_put_contents($XMLFilename, $this->_xmlloader->asPrettyXML());
}
}
public function savePackageXML($minified = true, $filename = false) {
$dom = new XMLLoader();
$dom->setRootName('package');
$dom->load();
$dom->getRootDOM()->setAttribute('type', 'component');
$dom->getRootDOM()->setAttribute('name', $this->getName());
$dom->getRootDOM()->setAttribute('version', $this->getVersion());
$dom->createElement('packager[version="' . Core::GetComponent()->getVersion() . '"]');
foreach ($this->_xmlloader->getRootDOM()->getElementsByTagName('provides') as $u) {
$newu = $dom->getDOM()->importNode($u);
$dom->getRootDOM()->appendChild($newu);
}
$dom->getElement('/provides[type="component"][name="' . strtolower($this->getName()) . '"][version="' . $this->getVersion() . '"]');
foreach ($this->_xmlloader->getRootDOM()->getElementsByTagName('requires') as $u) {
$newu = $dom->getDOM()->importNode($u);
$dom->getRootDOM()->appendChild($newu);
}
foreach ($this->_xmlloader->getRootDOM()->getElementsByTagName('upgrade') as $u) {
$newu = $dom->getDOM()->importNode($u);
$dom->getRootDOM()->appendChild($newu);
}
$desc = $this->_xmlloader->getElement('/description', false);
if ($desc) {
$newd            = $dom->getDOM()->importNode($desc);
$newd->nodeValue = $desc->nodeValue;
$dom->getRootDOM()->appendChild($newd);
}
$out = ($minified) ? $dom->asMinifiedXML() : $dom->asPrettyXML();
if ($filename) {
file_put_contents($filename, $out);
}
else {
return $out;
}
}
public function getRequires() {
if($this->_requires === null){
$this->_requires = array();
foreach ($this->_xmlloader->getElements('//component/requires/require') as $r) {
$t  = $r->getAttribute('type');
$n  = $r->getAttribute('name');
$v  = @$r->getAttribute('version');
$op = @$r->getAttribute('operation');
if ($v == '') $v = false;
if ($op == '') $op = 'ge';
$this->_requires[] = array(
'type'      => strtolower($t),
'name'      => $n,
'version'   => strtolower($v),
'operation' => strtolower($op),
);
}
}
return $this->_requires;
}
public function getDescription() {
if ($this->_description === null) {
$this->_description = trim($this->_xmlloader->getElement('//description')->nodeValue);
}
return $this->_description;
}
public function setDescription($desc) {
$this->_description = $desc;
$this->_xmlloader->getElement('//description')->nodeValue = $desc;
}
public function getPermissions(){
return $this->_permissions;
}
public function setAuthors($authors) {
$this->_xmlloader->removeElements('/authors');
foreach ($authors as $a) {
if (isset($a['email']) && $a['email']) {
$this->_xmlloader->getElement('//component/authors/author[@name="' . $a['name'] . '"][@email="' . $a['email'] . '"]');
}
else {
$this->_xmlloader->getElement('//component/authors/author[@name="' . $a['name'] . '"]');
}
}
}
public function setLicenses($licenses) {
$this->_xmlloader->removeElements('/licenses');
foreach ($licenses as $lic) {
$str = '/licenses/license' . ((isset($lic['url']) && $lic['url']) ? '[@url="' . $lic['url'] . '"]' : '');
$l   = $this->_xmlloader->getElement($str);
if ($lic['title']) $l->nodeValue = $lic['title'];
}
}
public function loadFiles() {
if(!$this->isInstalled()) return false;
if(!$this->isEnabled()) return false;
foreach ($this->_xmlloader->getElements('/includes/include') as $f) {
require_once($this->getBaseDir() . $f->getAttribute('filename'));
}
foreach ($this->_xmlloader->getElementsByTagName('hookregister') as $h) {
$hook              = new Hook($h->getAttribute('name'));
$hook->description = $h->getAttribute('description');
HookHandler::RegisterHook($hook);
}
foreach ($this->_xmlloader->getElementsByTagName('hook') as $h) {
$event = $h->getAttribute('name');
$call  = $h->getAttribute('call');
$type  = @$h->getAttribute('type');
HookHandler::AttachToHook($event, $call, $type);
}
foreach ($this->_xmlloader->getElements('/forms/formelement') as $node) {
Form::$Mappings[$node->getAttribute('name')] = $node->getAttribute('class');
}
return true;
}
public function getLibraryList() {
$libs = array();
$libs[strtolower($this->_name)] = $this->_versionDB;
foreach ($this->_xmlloader->getElements('provides/provide') as $p) {
if (strtolower($p->getAttribute('type')) == 'library') {
$v = @$p->getAttribute('version');
if (!$v) $v = $this->_versionDB;
$libs[strtolower($p->getAttribute('name'))] = $v;
}
}
return $libs;
}
public function getClassList() {
if($this->_classlist === null){
$this->_classlist = array();
foreach ($this->_xmlloader->getElements('/files/file') as $f) {
$filename = $this->getBaseDir() . $f->getAttribute('filename');
foreach ($f->getElementsByTagName('class') as $p) {
$n           = strtolower($p->getAttribute('name'));
$this->_classlist[$n] = $filename;
}
foreach ($f->getElementsByTagName('interface') as $p) {
$n           = strtolower($p->getAttribute('name'));
$this->_classlist[$n] = $filename;
}
foreach ($f->getElementsByTagName('controller') as $p) {
$n           = strtolower($p->getAttribute('name'));
$this->_classlist[$n] = $filename;
}
foreach ($f->getElementsByTagName('widget') as $p) {
$n           = strtolower($p->getAttribute('name'));
$this->_classlist[$n] = $filename;
}
}
}
return $this->_classlist;
}
public function getWidgetList() {
if($this->_widgetlist === null){
$this->_widgetlist = array();
foreach ($this->_xmlloader->getElements('/files/file') as $f) {
$filename = $this->getBaseDir() . $f->getAttribute('filename');
foreach ($f->getElementsByTagName('widget') as $p) {
$this->_widgetlist[] = $p->getAttribute('name');
}
}
}
return $this->_widgetlist;
}
public function getViewClassList() {
$classes = array();
if ($this->hasModule()) {
foreach ($this->_xmlloader->getElementByTagName('module')->getElementsByTagName('file') as $f) {
$filename = $this->getBaseDir() . $f->getAttribute('filename');
foreach ($f->getElementsByTagName('provides') as $p) {
switch (strtolower($p->getAttribute('type'))) {
case 'viewclass':
case 'view_class':
$classes[$p->getAttribute('name')] = $filename;
break;
}
}
}
}
return $classes;
}
public function getViewList() {
$views = array();
if ($this->hasView()) {
foreach ($this->_xmlloader->getElementByTagName('view')->getElementsByTagName('tpl') as $t) {
$filename     = $this->getBaseDir() . $t->getAttribute('filename');
$name         = $t->getAttribute('name');
$views[$name] = $filename;
}
}
return $views;
}
public function getControllerList() {
$classes = array();
foreach ($this->_xmlloader->getElements('/files/file') as $f) {
$filename = $this->getBaseDir() . $f->getAttribute('filename');
foreach ($f->getElementsByTagName('controller') as $p) {
$n           = strtolower($p->getAttribute('name'));
$classes[$n] = $filename;
}
}
return $classes;
}
public function getSmartyPluginDirectory() {
if($this->_smartyPluginDirectory === null){
$d = $this->_xmlloader->getElement('/smartyplugins')->getAttribute('directory');
if ($d) $this->_smartyPluginDirectory = $this->getBaseDir() . $d;
else $this->_smartyPluginDirectory = false;
}
return $this->_smartyPluginDirectory;
}
public function getScriptLibraryList() {
$libs = array();
foreach ($this->_xmlloader->getElements('/provides/scriptlibrary') as $s) {
$libs[strtolower($s->getAttribute('name'))] = $s->getAttribute('call');
}
return $libs;
}
public function getViewSearchDir() {
if ($this->hasView()) {
if($this->_viewSearchDirectory === null){
$att = @$this->_xmlloader->getElement('/view')->getAttribute('searchdir');
if ($att) {
$this->_viewSearchDirectory = $this->getBaseDir() . $att . '/';
}
elseif (($att = $this->_xmlloader->getElements('/view/searchdir')->item(0))) {
$this->_viewSearchDirectory = $this->getBaseDir() . $att->getAttribute('dir') . '/';
}
elseif (is_dir($this->getBaseDir() . 'templates')) {
$this->_viewSearchDirectory = $this->getBaseDir() . 'templates';
}
else{
$this->_viewSearchDirectory = false;
}
}
return $this->_viewSearchDirectory;
}
}
public function getAssetDir() {
if ($this->getName() == 'core') $d = $this->getBaseDir() . 'core/assets';
else $d = $this->getBaseDir() . 'assets';
if (is_dir($d)) return $d;
else return null;
}
public function getIncludePaths() {
return array();
}
public function getDBSchemaTableNames() {
$ret = array();
foreach ($this->_xmlloader->getElement('dbschema')->getElementsByTagName('table') as $table) {
$ret[] = $table->getAttribute('name');
}
return $ret;
}
public function setDBSchemaTableNames($arr) {
$this->_xmlloader->getRootDOM()->removeChild($this->_xmlloader->getElement('/dbschema'));
$node = $this->_xmlloader->getElement('/dbschema[@prefix="' . DB_PREFIX . '"]');
foreach ($arr as $k) {
if (!trim($k)) continue;
$tablenode = $this->getDOM()->createElement('table');
$tablenode->setAttribute('name', $k);
$node->appendChild($tablenode);
unset($tablenode);
}
}
public function getVersionInstalled() {
return $this->_versionDB;
}
public function getType() {
if ($this->_name == 'core') return 'core';
else return 'component';
}
public function getName() {
return $this->_name;
}
public function getVersion() {
return $this->_version;
}
public function setVersion($vers) {
if ($vers == $this->_version) return;
if (($upg = $this->_xmlloader->getElement('/upgrades/upgrade[@from=""][@to=""]', false))) {
$upg->setAttribute('from', $this->_version);
$upg->setAttribute('to', $vers);
}
elseif (($upg = $this->_xmlloader->getElement('/upgrades/upgrade[@from="' . $this->_version . '"][@to=""]', false))) {
$upg->setAttribute('to', $vers);
}
else {
$this->_xmlloader->getElement('/upgrades/upgrade[@from="' . $this->_version . '"][@to="' . $vers . '"]');
}
$this->_version = $vers;
$this->_xmlloader->getRootDOM()->setAttribute('version', $vers);
}
public function setFiles($files) {
$this->_xmlloader->removeElements('//component/files/file');
$newarray = array();
foreach ($files as $f) {
$newarray[$f['file']] = $f;
}
ksort($newarray);
foreach ($newarray as $f) {
$el = $this->_xmlloader->createElement('//component/files/file[@filename="' . $f['file'] . '"][@md5="' . $f['md5'] . '"]');
if (isset($f['controllers'])) {
foreach ($f['controllers'] as $c) {
$this->_xmlloader->createElement('controller[@name="' . $c . '"]', $el);
}
}
if (isset($f['classes'])) {
foreach ($f['classes'] as $c) {
$this->_xmlloader->createElement('class[@name="' . $c . '"]', $el);
}
}
if (isset($f['interfaces'])) {
foreach ($f['interfaces'] as $i) {
$this->_xmlloader->createElement('interface[@name="' . $i . '"]', $el);
}
}
}
}
public function setAssetFiles($files) {
$this->_xmlloader->removeElements('//component/assets/file');
$newarray = array();
foreach ($files as $f) {
$newarray[$f['file']] = $f;
}
ksort($newarray);
foreach ($newarray as $f) {
$el = $this->_xmlloader->createElement('//component/assets/file[@filename="' . $f['file'] . '"][@md5="' . $f['md5'] . '"]');
}
}
public function setViewFiles($files) {
$this->_xmlloader->removeElements('//component/view/file');
$newarray = array();
foreach ($files as $f) {
$newarray[$f['file']] = $f;
}
ksort($newarray);
foreach ($newarray as $f) {
$el = $this->_xmlloader->createElement('//component/view/file[@filename="' . $f['file'] . '"][@md5="' . $f['md5'] . '"]');
}
}
public function getRawXML() {
return $this->_xmlloader->asPrettyXML();
}
public function isValid() {
return (!$this->error & Component::ERROR_INVALID);
}
public function isInstalled() {
return ($this->_versionDB === false) ? false : true;
}
public function needsUpdated() {
return ($this->_versionDB != $this->_version);
}
public function getErrors($glue = '<br/>') {
if ($glue) {
return implode($glue, $this->errstrs);
}
else {
return $this->errors;
}
}
public function isEnabled() {
return $this->_enabled;
}
public function isLoadable() {
if ($this->error & Component::ERROR_INVALID) {
return false;
}
$this->error   = 0;
$this->errstrs = array();
if ($this->_execMode != 'BOTH') {
if ($this->_execMode != EXEC_MODE) {
$this->error     = $this->error | Component::ERROR_WRONGEXECMODE;
$this->errstrs[] = 'Wrong execution mode, can only be ran in ' . $this->_execMode . ' mode';
}
}
foreach ($this->getRequires() as $r) {
switch ($r['type']) {
case 'library':
if (!Core::IsLibraryAvailable($r['name'], $r['version'], $r['operation'])) {
$this->error     = $this->error | Component::ERROR_MISSINGDEPENDENCY;
$this->errstrs[] = 'Requires missing library ' . $r['name'] . ' ' . $r['version'];
}
break;
case 'jslibrary':
if (!Core::IsJSLibraryAvailable($r['name'], $r['version'], $r['operation'])) {
$this->error     = $this->error | Component::ERROR_MISSINGDEPENDENCY;
$this->errstrs[] = 'Requires missing JSlibrary ' . $r['name'] . ' ' . $r['version'];
}
break;
case 'component':
if (!Core::IsComponentAvailable($r['name'], $r['version'], $r['operation'])) {
$this->error     = $this->error | Component::ERROR_MISSINGDEPENDENCY;
$this->errstrs[] = 'Requires missing component ' . $r['name'] . ' ' . $r['version'];
}
break;
case 'define':
if (!defined($r['name'])) {
$this->error     = $this->error | Component::ERROR_MISSINGDEPENDENCY;
$this->errstrs[] = 'Requires missing define ' . $r['name'];
}
if ($r['value'] != null && constant($r['name']) != $r['value']) {
$this->error     = $this->error | Component::ERROR_MISSINGDEPENDENCY;
$this->errstrs[] = 'Requires wrong define ' . $r['name'] . '(' . $r['value'] . ')';
}
break;
}
}
if ($this->error) return false;
$cs = $this->getClassList();
foreach ($cs as $c => $file) {
if (Core::IsClassAvailable($c)) {
$this->error     = $this->error | Component::ERROR_CONFLICT;
$this->errstrs[] = $c . ' already defined in another component';
break;
}
}
return (!$this->error) ? true : false;
}
public function getJSLibraries() {
$ret = array();
foreach ($this->_xmlloader->getRootDOM()->getElementsByTagName('jslibrary') as $node) {
$lib       = new JSLibrary();
$lib->name = $node->getAttribute('name');
$lib->version                = (($v = @$node->getAttribute('version')) ? $v : $this->_xmlloader->getRootDOM()->getAttribute('version'));
$lib->baseDirectory          = ROOT_PDIR . 'components/' . $this->getName() . '/';
$lib->DOMNode                = $node;
$ret[strtolower($lib->name)] = $lib;
}
return $ret;
}
public function hasLibrary() {
return true;
}
public function hasJSLibrary() {
return ($this->_xmlloader->getRootDOM()->getElementsByTagName('jslibrary')->length) ? true : false;
}
public function hasModule() {
return ($this->_xmlloader->getRootDOM()->getElementsByTagName('module')->length) ? true : false;
}
public function hasView() {
return ($this->_xmlloader->getRootDOM()->getElementsByTagName('view')->length) ? true : false;
}
public function install() {
if ($this->isInstalled()) return false;
if (!$this->isLoadable()) return false;
$changes = $this->_performInstall();
$c = new ComponentModel($this->_name);
$c->set('version', $this->_version);
$c->save();
$this->_versionDB = $this->_version;
$this->_enabled = ($c->get('enabled'));
$this->loadFiles();
if (class_exists('Core')) {
$ch = Core::Singleton();
$ch->_registerComponent($this);
}
return $changes;
}
public function reinstall() {
if (!$this->isInstalled()) return false;
return $this->_performInstall();
}
public function upgrade() {
if (!$this->isInstalled()) return false;
$changes = array();
$canBeUpgraded = true;
while ($canBeUpgraded) {
$canBeUpgraded = false;
foreach ($this->_xmlloader->getRootDOM()->getElementsByTagName('upgrade') as $u) {
if ($this->_versionDB == @$u->getAttribute('from')) {
$canBeUpgraded = true;
foreach($u->getElementsByTagName('dataset') as $datasetel){
$datachanges = $this->_parseDatasetNode($datasetel);
if($datachanges !== false) $changes = array_merge($changes, $datachanges);
}
$changes[] = 'Upgraded from [' . $this->_versionDB . '] to [' . $u->getAttribute('to') . ']';
$this->_versionDB = @$u->getAttribute('to');
$c                = new ComponentModel($this->_name);
$c->set('version', $this->_versionDB);
$c->save();
}
}
}
$otherchanges = $this->_performInstall();
if ($otherchanges !== false) $changes = array_merge($changes, $otherchanges);
return (sizeof($changes)) ? $changes : false;
}
public function disable(){
if(!$this->isInstalled()) return false;
$c = new ComponentModel($this->_name);
$c->set('enabled', false);
$c->save();
$this->_versionDB = null;
return true;
}
public function enable(){
if($this->isEnabled()) return false;
$c = new ComponentModel($this->_name);
$c->set('enabled', true);
$c->save();
$this->_enabled = true;
return true;
}
private function _performInstall() {
$changed = array();
$change = $this->_parseDBSchema();
if ($change !== false) $changed = array_merge($changed, $change);
$change = $this->_parseConfigs();
if ($change !== false) $changed = array_merge($changed, $change);
$change = $this->_parsePages();
if ($change !== false) $changed = array_merge($changed, $change);
$change = $this->_parseWidgets();
if ($change !== false) $changed = array_merge($changed, $change);
$change = $this->_installAssets();
if ($change !== false) $changed = array_merge($changed, $change);
foreach($this->_xmlloader->getElements('install/dataset') as $datasetel){
$datachanges = $this->_parseDatasetNode($datasetel);
if($datachanges !== false) $changes = array_merge($changes, $datachanges);
}
return (sizeof($changed)) ? $changed : false;
}
public function getProvides() {
$ret = array();
$ret[] = array(
'name'    => strtolower($this->getName()),
'type'    => 'component',
'version' => $this->getVersion()
);
foreach ($this->_xmlloader->getElements('provides/provide') as $el) {
$ret[] = array(
'name'    => strtolower($el->getAttribute('name')),
'type'    => $el->getAttribute('type'),
'version' => $el->getAttribute('version'),
);
}
return $ret;
}
private function _parseConfigs() {
$changes = array();
$node = $this->_xmlloader->getElement('configs');
foreach ($node->getElementsByTagName('config') as $confignode) {
$key = $confignode->getAttribute('key');
$m   = ConfigHandler::GetConfig($key);
$m->set('options', $confignode->getAttribute('options'));
$m->set('type', $confignode->getAttribute('type'));
$m->set('default_value', $confignode->getAttribute('default'));
$m->set('description', $confignode->getAttribute('description'));
$m->set('mapto', $confignode->getAttribute('mapto'));
if (!$m->get('value')) $m->set('value', $confignode->getAttribute('default'));
if (isset($_SESSION['configs']) && isset($_SESSION['configs'][$key])) $m->set('value', $_SESSION['configs'][$key]);
if ($m->save()) $changes[] = 'Set configuration [' . $m->get('key') . '] to [' . $m->get('value') . ']';
ConfigHandler::_Set($m);
}
return (sizeof($changes)) ? $changes : false;
} // private function _parseConfigs
private function _parsePages() {
$changes = array();
$node = $this->_xmlloader->getElement('pages');
foreach ($node->getElementsByTagName('page') as $subnode) {
$m = new PageModel($subnode->getAttribute('baseurl'));
$action = ($m->exists()) ? 'Updated' : 'Added';
if (!$m->get('rewriteurl')) {
if ($subnode->getAttribute('rewriteurl')) $m->set('rewriteurl', $subnode->getAttribute('rewriteurl'));
else $m->set('rewriteurl', $subnode->getAttribute('baseurl'));
}
if (!$m->get('title')) $m->set('title', $subnode->getAttribute('title'));
if ($m->get('access') == '*') $m->set('access', $subnode->getAttribute('access'));
if(!$m->exists()) $m->set('parenturl', $subnode->getAttribute('parenturl'));
$m->set('widget', $subnode->getAttribute('widget'));
$m->set('admin', $subnode->getAttribute('admin'));
if ($m->save()) $changes[] = $action . ' page [' . $m->get('baseurl') . ']';
}
return ($changes > 0) ? $changes : false;
}
private function _parseWidgets() {
$changes = array();
$node = $this->_xmlloader->getElement('widgets');
foreach ($node->getElementsByTagName('widget') as $subnode) {
$m = new WidgetModel($subnode->getAttribute('baseurl'));
$action = ($m->exists()) ? 'Updated' : 'Added';
if (!$m->get('title')) $m->set('title', $subnode->getAttribute('title'));
if ($m->save()) $changes[] = $action . ' widget [' . $m->get('baseurl') . ']';
}
return ($changes > 0) ? $changes : false;
}
private function _parseDBSchema() {
$node   = $this->_xmlloader->getElement('dbschema');
$prefix = $node->getAttribute('prefix');
$changes = array();
$classes = $this->getClassList();
foreach ($classes as $k => $v) {
if ($k == 'model' || strpos($k, 'model') !== strlen($k) - 5) unset($classes[$k]);
}
foreach ($classes as $m => $file) {
if(!class_exists($m)) require_once($file);
$s         = $m::GetSchema();
$i         = $m::GetIndexes();
$tablename = $m::GetTableName();
$schema = array('schema'  => $s,
'indexes' => $i);
if (Core::DB()->tableExists($tablename)) {
if(Core::DB()->modifyTable($tablename, $schema)){
$changes[] = 'Modified table ' . $tablename;
}
}
else {
Core::DB()->createTable($tablename, $schema);
$changes[] = 'Created table ' . $tablename;
}
}
return sizeof($changes) ? $changes : false;
} // private function _parseDBSchema()
private function _parseDatasetNode(DOMElement $node){
$action   = $node->getAttribute('action');
$table    = $node->getAttribute('table');
$haswhere = false;
$sets     = array();
$ds       = new Dataset();
$ds->table($table);
foreach($node->getElementsByTagName('datasetset') as $el){
$sets[$el->getAttribute('key')] = $el->nodeValue;
}
foreach($node->getElementsByTagName('datasetwhere') as $el){
$haswhere = true;
$ds->where(trim($el->nodeValue));
}
switch($action){
case 'update':
foreach($sets as $k => $v){
$ds->update($k, $v);
}
break;
case 'insert':
foreach($sets as $k => $v){
$ds->insert($k, $v);
}
break;
case 'delete':
if(sizeof($sets)) throw new InstallerException('Invalid mix of arguments on ' . $action . ' dataset request');
if(!$haswhere) throw new InstallerException('Cowardly refusing to delete with no where statement');
$ds->delete();
break;
default:
throw new InstallerException('Invalid action type, '. $action);
}
$ds->execute();
if($ds->num_rows){
return array($action . ' on table ' . $table . ' affected ' . $ds->num_rows . ' records.');
}
else{
return false;
}
}
private function _installAssets() {
$assetbase = ConfigHandler::Get('/core/filestore/assetdir');
$theme     = ConfigHandler::Get('/theme/selected');
$changes   = array();
foreach ($this->_xmlloader->getElements('/assets/file') as $node) {
$b = $this->getBaseDir();
$f = new File_local_backend($b . $node->getAttribute('filename'));
$newfilename = 'assets' . substr($b . $node->getAttribute('filename'), strlen($this->getAssetDir()));
$nf = Core::File($newfilename);
if ($theme === null) {
}
elseif ($theme != 'default' && strpos($nf->getFilename(), $assetbase . $theme) !== false) {
$nf->setFilename(str_replace($assetbase . $theme, $assetbase . 'default', $nf->getFilename()));
}
if ($nf->exists() && $nf->identicalTo($f)) {
continue;
}
elseif ($nf->exists()) {
$action = 'Replaced';
}
else {
$action = 'Installed';
}
try {
$f->copyTo($nf, true);
}
catch (Exception $e) {
throw new InstallerException('Unable to copy [' . $f->getFilename() . '] to [' . $nf->getFilename() . ']');
}
$changes[] = $action . ' ' . $nf->getFilename();
}
if (!sizeof($changes)) return false;
Core::Cache()->delete('asset-resolveurl');
return $changes;
}
public function getBaseDir($prefix = ROOT_PDIR) {
if ($this->_name == 'core') {
return $prefix;
}
else {
return $prefix . 'components/' . strtolower($this->_name) . '/';
}
}
}

require_once(ROOT_PDIR . 'core/functions/Core.functions.php');
### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/filestore/File_Backend.interface.php
interface File_Backend {
public function __construct($filename = null);
public function getFilesize($formatted = false);
public function getMimetype();
public function getExtension();
public function getURL();
public function getFilename($prefix = ROOT_PDIR);
public function getBaseFilename($withoutext = false);
public function getLocalFilename();
public function getHash();
public function delete();
public function isImage();
public function isText();
public function isPreviewable();
public function inDirectory($path);
public function identicalTo($otherfile);
public function copyTo($dest, $overwrite = false);
public function copyFrom($src, $overwrite = false);
public function getContents();
public function putContents($data);
public function getContentsObject();
public function exists();
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/filestore/Directory_Backend.interface.php
interface Directory_Backend {
public function __construct($directory);
public function ls();
public function isReadable();
public function isWritable();
public function mkdir();
public function getPath();
public function getBasename();
public function remove();
public function get($name);
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/filestore/FileContentFactory.class.php
class FileContentFactory {
public static function GetFromFile(File_Backend $file) {
switch ($file->getMimetype()) {
case 'application/x-gzip':
if (strtolower($file->getExtension()) == 'tgz') return new File_tgz_contents($file);
else return new File_gz_contents($file);
break;
case 'text/plain':
if (strtolower($file->getExtension()) == 'asc') return new File_asc_contents($file);
else return new File_unknown_contents($file);
break;
case 'application/pgp-signature':
return new File_asc_contents($file);
break;
default:
error_log('@fixme Unknown file mimetype [' . $file->getMimetype() . '] with extension [' . $file->getExtension() . ']');
return new File_unknown_contents($file);
}
}
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/filestore/backends/file_awss3.backend.php
class File_awss3_backend implements File_Backend {
private $_backend;
public $filename;
public $bucket;
public $acl = AmazonS3::ACL_PUBLIC;
public $storage = AmazonS3::STORAGE_STANDARD;
private $_metadata = null;
public function __construct($filename = null, $bucket = null) {
$this->_backend = new AmazonS3();
$this->filename = $filename;
$this->bucket   = $bucket;
}
private function _getMetadata() {
if ($this->_metadata === null) {
$this->_metadata = $this->_backend->get_object_metadata($this->bucket, $this->filename);
}
return $this->_metadata;
}
public function getFilesize($formatted = false) {
$d = $this->_getMetadata();
if (!$d) return null;
$f = $d['Size'];
return ($formatted) ? Core::FormatSize($f, 2) : $f;
}
public function getMimetype() {
$d = $this->_getMetadata();
if (!$d) return null;
return $d['ContentType'];
}
public function getExtension() {
return File::GetExtensionFromString($this->filename);
}
public function getURL() {
return $this->_backend->get_object_url($this->bucket, $this->filename);
}
public function getFilename($prefix = ROOT_PDIR) {
if ($prefix == ROOT_PDIR) return $this->filename;
return preg_replace('/^' . str_replace('/', '\\/', ROOT_PDIR) . '(.*)/', $prefix . '$1', $this->filename);
}
public function getBaseFilename($withoutext = false) {
$b = basename($this->filename);
if ($withoutext) {
return substr($b, 0, (-1 - strlen($this->getExtension())));
}
else {
return $b;
}
}
public function getLocalFilename() {
return $this->_getTmpLocal()->getFilename();
}
public function getHash() {
$d = $this->_getMetadata();
if (!$d) return null;
return str_replace('"', '', $d['ETag']);
}
public function delete() {
$this->_backend->delete_object($this->bucket, $this->filename);
$this->_metadata = false;
$this->filename  = null;
return true;
}
public function copyTo($dest, $overwrite = false) {
if (is_a($dest, 'File') || $dest instanceof File_Backend) {
}
else {
$file = $dest;
if ($file{0} != '/') {
$file = dirname($this->filename) . '/' . $file;
}
if (substr($file, -1) == '/') {
$file .= $this->getBaseFilename();
}
$dest = new File_awss3_backend($file);
}
if ($this->identicalTo($dest)) return $this;
$dest->copyFrom($this, $overwrite);
return $dest;
if (!is_dir(dirname($dest))) {
exec('mkdir -p "' . dirname($dest) . '"');
}
}
public function copyFrom($src, $overwrite = false) {
$this->putContents($src->getContents(), $src->getMimetype());
return;
var_dump($src, $this);
die();
if (!$overwrite) {
$c    = 0;
$ext  = $this->getExtension();
$base = $this->getBaseFilename(true);
$dir  = dirname($this->filename);
$f = $dir . '/' . $base . '.' . $ext;
while (file_exists($f)) {
$f = $dir . '/' . $base . ' (' . ++$c . ')' . '.' . $ext;
}
$this->filename = $f;
}
$ds = explode('/', dirname($this->filename));
$d  = '';
foreach ($ds as $dir) {
if ($dir == '') continue;
$d .= '/' . $dir;
if (!is_dir($d)) {
if (mkdir($d) === false) throw new Exception("Unable to make directory $d, please check permissions.");
}
}
}
public function getContents() {
return $this->_backend->get_object($this->bucket, $this->filename);
}
public function putContents($data, $mimetype = 'application/octet-stream') {
$opt = array(
'body'        => $data,
'acl'         => $this->acl,
'storage'     => $this->storage,
'contentType' => $mimetype,
);
return $this->_backend->create_object($this->bucket, $this->filename, $opt);
return file_put_contents($this->filename, $data);
}
public function getContentsObject() {
return FileContentFactory::GetFromFile($this);
}
public function isImage() {
$m = $this->getMimetype();
return (preg_match('/image\/jpeg|image\/png|image\/gif/', $m) ? true : false);
}
public function isText() {
$m = $this->getMimetype();
return (preg_match('/text\/.*|application\/x-shellscript/', $m) ? true : false);
}
public function isPreviewable() {
return ($this->isImage() || $this->isText());
}
public function inDirectory($path) {
if (strpos($path, ROOT_PDIR) === false) $path = ROOT_PDIR . $path;
return (strpos($this->filename, $path) !== false);
}
public function identicalTo($otherfile) {
if (is_a($otherfile, 'File') || $otherfile instanceof File_Backend) {
return ($this->getHash() == $otherfile->getHash());
}
else {
if (!file_exists($otherfile)) return false;
if (!file_exists($this->filename)) return false;
$result = exec('diff -q "' . $this->filename . '" "' . $otherfile . '"', $array, $return);
return ($return == 0);
}
}
public function exists() {
return ($this->_getMetadata());
}
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/filestore/backends/file_local.backend.php
class File_local_backend implements File_Backend {
private $_filename = null;
private $_type = null;
private static $_Root_pdir_assets = null;
private static $_Root_pdir_public = null;
private static $_Root_pdir_private = null;
private static $_Root_pdir_tmp = null;
public function __construct($filename = null) {
if ($filename) $this->setFilename($filename);
}
public function getFilesize($formatted = false) {
$f = filesize($this->_filename);
return ($formatted) ? Core::FormatSize($f, 2) : $f;
}
public function getMimetype() {
if (!$this->exists()) return null;
if (!function_exists('finfo_open')) {
$cli = exec('file -ib "' . $this->_filename . '"');
list($type,) = explode(';', $cli);
$type = trim($type);
}
else {
$finfo = finfo_open(FILEINFO_MIME);
$type  = finfo_file($finfo, $this->_filename);
finfo_close($finfo);
}
if (($pos = strpos($type, ';')) !== false) $type = substr($type, 0, $pos);
$type = trim($type);
$ext = strtolower($this->getExtension());
if ($ext == 'js' && $type == 'text/plain') $type = 'text/javascript';
elseif ($ext == 'js' && $type == 'text/x-c++') $type = 'text/javascript';
elseif ($ext == 'css' && $type == 'text/plain') $type = 'text/css';
elseif ($ext == 'css' && $type == 'text/x-c') $type = 'text/css';
elseif ($ext == 'html' && $type == 'text/plain') $type = 'text/html';
elseif ($ext == 'ttf' && $type == 'application/octet-stream') $type = 'font/ttf';
elseif ($ext == 'otf' && $type == 'application/octet-stream') $type = 'font/otf';
return $type;
}
public function getExtension() {
return Core::GetExtensionFromString($this->_filename);
}
public function getURL() {
if (!preg_match('/^' . str_replace('/', '\\/', ROOT_PDIR) . '/', $this->_filename)) return false;
return preg_replace('/^' . str_replace('/', '\\/', ROOT_PDIR) . '(.*)/', ROOT_URL . '$1', $this->_filename);
}
public function getFilename($prefix = ROOT_PDIR) {
if ($prefix == ROOT_PDIR) return $this->_filename;
if ($prefix === false) {
if ($this->_type == 'asset')
return 'asset/' . substr($this->_filename, strlen(self::$_Root_pdir_asset));
elseif ($this->_type == 'public')
return 'public/' . substr($this->_filename, strlen(self::$_Root_pdir_public));
elseif ($this->_type == 'private')
return 'private/' . substr($this->_filename, strlen(self::$_Root_pdir_private));
elseif ($this->_type == 'tmp')
return 'tmp/' . substr($this->_filename, strlen(self::$_Root_pdir_tmp));
else
return $this->_filename;
}
return preg_replace('/^' . str_replace('/', '\\/', ROOT_PDIR) . '(.*)/', $prefix . '$1', $this->_filename);
}
public function setFilename($filename) {
if (self::$_Root_pdir_assets === null) {
$dir = ConfigHandler::Get('/core/filestore/assetdir');
if ($dir{0} != '/') $dir = ROOT_PDIR . $dir; // Needs to be fully resolved
if (substr($dir, -1) != '/') $dir = $dir . '/'; // Needs to end in a '/'
self::$_Root_pdir_assets = $dir;
}
if (self::$_Root_pdir_public === null) {
$dir = ConfigHandler::Get('/core/filestore/publicdir');
if ($dir{0} != '/') $dir = ROOT_PDIR . $dir; // Needs to be fully resolved
if (substr($dir, -1) != '/') $dir = $dir . '/'; // Needs to end in a '/'
self::$_Root_pdir_public = $dir;
}
if (self::$_Root_pdir_private === null) {
$dir = ConfigHandler::Get('/core/filestore/privatedir');
if ($dir{0} != '/') $dir = ROOT_PDIR . $dir; // Needs to be fully resolved
if (substr($dir, -1) != '/') $dir = $dir . '/'; // Needs to end in a '/'
self::$_Root_pdir_private = $dir;
}
if (self::$_Root_pdir_tmp === null) {
$dir = TMP_DIR;
if ($dir{0} != '/') $dir = ROOT_PDIR . $dir; // Needs to be fully resolved
if (substr($dir, -1) != '/') $dir = $dir . '/'; // Needs to end in a '/'
self::$_Root_pdir_tmp = $dir;
}
if (strpos($filename, 'base64:') === 0) $filename = base64_decode(substr($filename, 7));
$filename = preg_replace(':/+:', '/', $filename);
if (strpos($filename, 'assets/') === 0) {
$theme    = ConfigHandler::Get('/theme/selected');
$filename = substr($filename, 7); // Trim off the 'asset/' prefix.
if (file_exists(self::$_Root_pdir_assets . $theme . '/' . $filename)) $filename = self::$_Root_pdir_assets . $theme . '/' . $filename;
else $filename = self::$_Root_pdir_assets . 'default/' . $filename;
$this->_type = 'asset';
}
elseif (strpos($filename, self::$_Root_pdir_assets) === 0) {
$this->_type = 'asset';
}
elseif (strpos($filename, 'public/') === 0) {
$filename = substr($filename, 7); // Trim off the 'public/' prefix.
$filename = self::$_Root_pdir_public . $filename;
$this->_type = 'public';
}
elseif (strpos($filename, self::$_Root_pdir_public) === 0) {
$this->_type = 'public';
}
elseif (strpos($filename, 'private/') === 0) {
$filename = substr($filename, 8); // Trim off the 'private/' prefix.
$filename = self::$_Root_pdir_private . $filename;
$this->_type = 'private';
}
elseif (strpos($filename, self::$_Root_pdir_private) === 0) {
$this->_type = 'private';
}
elseif (strpos($filename, 'tmp/') === 0) {
$filename = substr($filename, 4); // Trim off the 'tmp/' prefix.
$filename = self::$_Root_pdir_tmp . $filename;
$this->_type = 'tmp';
}
elseif (strpos($filename, self::$_Root_pdir_tmp) === 0) {
$this->_type = 'tmp';
}
else {
}
$this->_filename = $filename;
}
public function getBaseFilename($withoutext = false) {
$b = basename($this->_filename);
if ($withoutext) {
$ext = $this->getExtension();
if($ext != '') {
return substr($b, 0, (-1 - strlen($ext)));
}
}
return $b;
}
public function getBasename() {
return basename($this->_filename);
}
public function getLocalFilename() {
return $this->getFilename();
}
public function getFilenameHash() {
if ($this->_type == 'asset') $filename = 'asset/' . substr($this->_filename, strlen(self::$_Root_pdir_asset));
elseif ($this->_type == 'public') $filename = 'public/' . substr($this->_filename, strlen(self::$_Root_pdir_public));
elseif ($this->_type == 'private') $filename = 'private/' . substr($this->_filename, strlen(self::$_Root_pdir_private));
elseif ($this->_type == 'tmp') $filename = 'tmp/' . substr($this->_filename, strlen(self::$_Root_pdir_tmp));
else $filename = $this->_filename;
return 'base64:' . base64_encode($filename);
}
public function getHash() {
if (!file_exists($this->_filename)) return null;
return md5_file($this->_filename);
}
public function delete() {
if (!@unlink($this->getFilename())) return false;
$this->_filename = null;
return true;
}
public function copyTo($dest, $overwrite = false) {
if (is_a($dest, 'File') || $dest instanceof File_Backend) {
}
else {
$file = $dest;
if ($file{0} != '/') {
$file = dirname($this->_filename) . '/' . $file;
}
if (substr($file, -1) == '/') {
$file .= $this->getBaseFilename();
}
$dest = new File_local_backend($file);
}
if ($this->identicalTo($dest)) return $dest;
$dest->copyFrom($this, $overwrite);
return $dest;
}
public function copyFrom($src, $overwrite = false) {
if (!$overwrite) {
$c    = 0;
$ext  = $this->getExtension();
$base = $this->getBaseFilename(true);
$dir  = dirname($this->_filename);
$f = $dir . '/' . $base . (($ext == '') ? '' : '.' . $ext);
while (file_exists($f)) {
$f = $dir . '/' . $base . ' (' . ++$c . ')' . (($ext == '') ? '' : '.' . $ext);
}
$this->_filename = $f;
}
return $this->putContents($src->getContents());
}
public function getContents() {
return file_get_contents($this->_filename);
}
public function putContents($data) {
if (!is_dir(dirname($this->_filename))) {
if (!self::_Mkdir(dirname($this->_filename), null, true)) {
throw new Exception("Unable to make directory " . dirname($this->_filename) . ", please check permissions.");
}
}
return self::_PutContents($this->_filename, $data);
}
public function getContentsObject() {
return FileContentFactory::GetFromFile($this);
}
public function isImage() {
$m = $this->getMimetype();
return (preg_match('/image\/jpeg|image\/png|image\/gif/', $m) ? true : false);
}
public function isText() {
$m = $this->getMimetype();
return (preg_match('/text\/.*|application\/x-shellscript/', $m) ? true : false);
}
public function isPreviewable() {
return ($this->isImage());
}
public function displayPreview($dimensions = "300x300", $includeHeader = true) {
if (is_numeric($dimensions)) {
$width  = $dimensions;
$height = $dimensions;
}
elseif ($dimensions === null) {
$width  = 300;
$height = 300;
}
else {
$vals   = explode('x', strtolower($dimensions));
$width  = (int)$vals[0];
$height = (int)$vals[1];
}
$key = 'filepreview-' . $this->getHash() . '-' . $width . 'x' . $height . '.png';
if (file_exists(TMP_DIR . $key)) {
header('Content-Type: image/png');
echo file_get_contents(TMP_DIR . $key); // (should be binary)
return; // whee, nothing else to do!
}
$img2 = $this->_getResizedImage($width, $height);
imagepng($img2, TMP_DIR . $key);
if ($includeHeader) header('Content-Type: image/png');
imagepng($img2);
return;
}
public function getPreviewURL($dimensions = "300x300") {
if (is_numeric($dimensions)) {
$width  = $dimensions;
$height = $dimensions;
}
elseif ($dimensions === null) {
$width  = 300;
$height = 300;
}
else {
$vals   = explode('x', strtolower($dimensions));
$width  = (int)$vals[0];
$height = (int)$vals[1];
}
if (!$this->exists()) {
error_log('File not found [ ' . $this->_filename . ' ]', E_USER_NOTICE);
$size = Core::TranslateDimensionToPreviewSize($width, $height);
return Core::ResolveAsset('mimetype_icons/notfound-' . $size . '.png');
}
elseif ($this->isPreviewable()) {
$key = $this->getBaseFilename(true) . '-preview-' . $this->getHash() . '-' . $width . 'x' . $height . '.png';
$file = Core::File('public/tmp/' . $key);
if (!$file->exists()) {
$img2 = $this->_getResizedImage($width, $height);
imagepng($img2, TMP_DIR . $key);
$file->putContents(file_get_contents(TMP_DIR . $key));
}
return $file->getURL();
}
else {
return false;
}
}
public function inDirectory($path) {
if (strpos($path, ROOT_PDIR) === false) $path = ROOT_PDIR . $path;
return (strpos($this->_filename, $path) !== false);
}
public function identicalTo($otherfile) {
if (is_a($otherfile, 'File') || $otherfile instanceof File_Backend) {
return ($this->getHash() == $otherfile->getHash());
}
else {
if (!file_exists($otherfile)) return false;
if (!file_exists($this->_filename)) return false;
$result = exec('diff -q "' . $this->_filename . '" "' . $otherfile . '"', $array, $return);
return ($return == 0);
}
}
public function exists() {
return file_exists($this->_filename);
}
public function isReadable() {
return is_readable($this->_filename);
}
public function isLocal() {
return true;
}
public function getMTime() {
if (!$this->exists()) return false;
return filemtime($this->getFilename());
}
public static function _Mkdir($pathname, $mode = null, $recursive = false) {
$ftp    = \Core\FTP();
$tmpdir = TMP_DIR;
if ($tmpdir{0} != '/') $tmpdir = ROOT_PDIR . $tmpdir; // Needs to be fully resolved
if ($mode === null) {
$mode = (defined('DEFAULT_DIRECTORY_PERMS') ? DEFAULT_DIRECTORY_PERMS : 0777);
}
if (!$ftp) {
return mkdir($pathname, $mode, $recursive);
}
elseif (strpos($pathname, $tmpdir) === 0) {
return mkdir($pathname, $mode, $recursive);
}
else {
if (strpos($pathname, ROOT_PDIR) === 0) $pathname = substr($pathname, strlen(ROOT_PDIR));
$paths = explode('/', $pathname);
foreach ($paths as $p) {
if (!@ftp_chdir($ftp, $p)) {
if (!ftp_mkdir($ftp, $p)) return false;
if (!ftp_chmod($ftp, $mode, $p)) return false;
ftp_chdir($ftp, $p);
}
}
return true;
}
}
public static function _PutContents($filename, $data) {
$ftp    = \Core\FTP();
$tmpdir = TMP_DIR;
if ($tmpdir{0} != '/') $tmpdir = ROOT_PDIR . $tmpdir; // Needs to be fully resolved
$mode = (defined('DEFAULT_FILE_PERMS') ? DEFAULT_FILE_PERMS : 0644);
if (!$ftp) {
$ret = file_put_contents($filename, $data);
if ($ret === false) return $ret;
chmod($filename, $mode);
return $ret;
}
elseif (strpos($filename, $tmpdir) === 0) {
$ret = file_put_contents($filename, $data);
if ($ret === false) return $ret;
chmod($filename, $mode);
return $ret;
}
else {
if (strpos($filename, ROOT_PDIR) === 0) $filename = substr($filename, strlen(ROOT_PDIR));
$tmpfile = $tmpdir . 'ftpupload-' . Core::RandomHex(4);
file_put_contents($tmpfile, $data);
if (!ftp_put($ftp, $filename, $tmpfile, FTP_BINARY)) {
unlink($tmpfile);
return false;
}
if (!ftp_chmod($ftp, $mode, $filename)) return false;
unlink($tmpfile);
return true;
}
}
private function _getResizedImage($width, $height) {
$m = $this->getMimetype();
if ($this->isImage()) {
switch ($m) {
case 'image/jpeg':
$img = imagecreatefromjpeg($this->getFilename());
break;
case 'image/png':
$img = imagecreatefrompng($this->getFilename());
break;
case 'image/gif':
$img = imagecreatefromgif($this->getFilename());
break;
}
if ($img) {
$sW = imagesx($img);
$sH = imagesy($img);
$nW = $sW;
$nH = $sH;
if ($nW > $width) {
$nH = $width * $sH / $sW;
$nW = $width;
}
if ($nH > $height) {
$nW = $height * $sW / $sH;
$nH = $height;
}
$img2 = imagecreatetruecolor($nW, $nH);
imagealphablending($img2, false);
imagesavealpha($img2, true);
imagealphablending($img, true);
imagecopyresampled($img2, $img, 0, 0, 0, 0, $nW, $nH, $sW, $sH);
imagedestroy($img);
return $img2;
}
}
}
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/filestore/backends/directory_local.backend.php
class Directory_local_backend implements Directory_Backend {
private $_path;
private $_files = null;
private $_ignores = array();
private static $_Root_pdir_assets = null;
private static $_Root_pdir_public = null;
private static $_Root_pdir_private = null;
private static $_Root_pdir_tmp = null;
public function __construct($directory) {
if (!is_null($directory)) {
if (self::$_Root_pdir_assets === null) {
$dir = ConfigHandler::Get('/core/filestore/assetdir');
if ($dir{0} != '/') $dir = ROOT_PDIR . $dir; // Needs to be fully resolved
self::$_Root_pdir_assets = $dir;
}
if (self::$_Root_pdir_public === null) {
$dir = ConfigHandler::Get('/core/filestore/publicdir');
if ($dir{0} != '/') $dir = ROOT_PDIR . $dir; // Needs to be fully resolved
self::$_Root_pdir_public = $dir;
}
if (self::$_Root_pdir_private === null) {
$dir = ConfigHandler::Get('/core/filestore/privatedir');
if ($dir{0} != '/') $dir = ROOT_PDIR . $dir; // Needs to be fully resolved
self::$_Root_pdir_private = $dir;
}
if (self::$_Root_pdir_tmp === null) {
$dir = TMP_DIR;
if ($dir{0} != '/') $dir = ROOT_PDIR . $dir; // Needs to be fully resolved
self::$_Root_pdir_tmp = $dir;
}
$directory = $directory . '/';
$directory = preg_replace(':/+:', '/', $directory);
if (strpos($directory, 'assets/') === 0) {
$theme     = ConfigHandler::Get('/theme/selected');
$directory = substr($directory, 7); // Trim off the 'asset/' prefix.
if (file_exists(self::$_Root_pdir_assets . $theme . '/' . $directory)) $directory = self::$_Root_pdir_assets . $theme . '/' . $directory;
else $directory = self::$_Root_pdir_assets . 'default/' . $directory;
}
elseif (strpos($directory, 'public/') === 0) {
$directory = substr($directory, 7); // Trim off the 'public/' prefix.
$directory = self::$_Root_pdir_public . $directory;
}
elseif (strpos($directory, 'private/') === 0) {
$directory = substr($directory, 8); // Trim off the 'private/' prefix.
$directory = self::$_Root_pdir_private . $directory;
}
elseif (strpos($directory, 'tmp/') === 0) {
$directory = substr($directory, 4); // Trim off the 'tmp/' prefix.
$directory = self::$_Root_pdir_tmp . $directory;
}
else {
}
$this->_path = $directory;
}
}
public function ls() {
if (!$this->isReadable()) return array();
if ($this->_files === null) $this->_sift();
$ret = array();
foreach ($this->_files as $file => $obj) {
if (sizeof($this->_ignores) && in_array($file, $this->_ignores)) continue;
$ret[] = $obj;
}
return $ret;
}
public function isReadable() {
return is_readable($this->_path);
}
public function isWritable() {
$ftp    = \Core\FTP();
$tmpdir = TMP_DIR;
if ($tmpdir{0} != '/') $tmpdir = ROOT_PDIR . $tmpdir; // Needs to be fully resolved
if (!$ftp) {
$testpath = $this->_path;
while($testpath && !is_dir($testpath)){
$testpath = substr($testpath, 0, strrpos($testpath, '/'));
}
return is_writable($testpath);
}
elseif (strpos($this->_path, $tmpdir) === 0) {
return is_writable($this->_path);
}
else {
return true;
}
}
public function exists(){
return (is_dir($this->getPath()));
}
public function mkdir() {
if (is_dir($this->getPath())) return null;
else return File_local_backend::_Mkdir($this->getPath(), null, true);
}
public function getPath() {
return $this->_path;
}
public function getBasename() {
$p = trim($this->_path, '/');
return substr($p, strrpos($p, '/') + 1);
}
public function remove() {
$dirqueue = array($this->getPath());
$x        = 0;
do {
$x++;
foreach ($dirqueue as $k => $d) {
$isempty = true;
$dh      = opendir($d);
if (!$dh) return false;
while (($file = readdir($dh)) !== false) {
if ($file == '.') continue;
if ($file == '..') continue;
$isempty = false;
if (is_dir($d . $file)) $dirqueue[] = $d . $file . '/';
else unlink($d . $file);
}
closedir($dh);
if ($isempty) {
rmdir($d);
unset($dirqueue[$k]);
}
}
$dirqueue = array_unique($dirqueue);
krsort($dirqueue);
}
while (sizeof($dirqueue) && $x <= 10);
}
public function get($name) {
$name    = trim($name, '/');
$parts   = explode('/', $name);
$lastkey = sizeof($parts) - 1; // -1 because 0-indexed arrays.
$obj = $this;
foreach ($parts as $k => $step) {
$listing = $obj->ls();
foreach ($listing as $l) {
if ($l->getBasename() == $step) {
if ($k == $lastkey) return $l;
$obj = $l;
continue 2;
}
}
return null;
}
return null;
}
private function _sift() {
$this->_files = array();
$dh = opendir($this->_path);
if (!$dh) return;
while ($sub = readdir($dh)) {
if ($sub{0} == '.') continue;
if (is_dir($this->_path . $sub)) {
$this->_files[$sub] = new Directory_local_backend($this->_path . $sub);
}
else {
$this->_files[$sub] = new File_local_backend($this->_path . $sub);
}
}
closedir($dh);
} // private function _sift()
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/core/ComponentFactory.php
abstract class ComponentFactory {
private static $_DBCache = null;
public static function _LookupComponentData($componentname) {
if (self::$_DBCache === null) {
self::$_DBCache = array();
try {
$res = Dataset::Init()->table('component')->select('*')->execute();
}
catch (DMI_Exception $e) {
return false;
}
foreach ($res as $r) {
$n                  = strtolower($r['name']);
self::$_DBCache[$n] = $r;
}
}
$componentname = strtolower($componentname);
return (isset(self::$_DBCache[$componentname])) ? self::$_DBCache[$componentname] : null;
}
public static function Load($filename) {
$fh = fopen($filename, 'r');
if (!$fh) return null;
$line = fread($fh, 512);
fclose($fh);
if (strpos($line, 'http://corepl.us/api/2_1/component.dtd') !== false) {
return new Component_2_1($filename);
}
else {
$name = substr($filename, 0, -14);
$name = substr($name, strrpos($name, '/') + 1);
return new Component($name);
}
}
public static function ResolveNameToFile($name) {
$name = strtolower($name);
if ($name == 'core') return 'core/component.xml';
elseif (file_exists(ROOT_PDIR . 'components/' . $name . '/component.xml')) return 'components/' . $name . '/component.xml';
else return false;
}
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/core/ComponentHandler.class.php
class ComponentHandler implements ISingleton {
private static $instance = null;
private $_componentCache = array();
private $_classes = array();
private $_widgets = array();
private $_viewClasses = array();
private $_scriptlibraries = array();
private $_loaded = false;
private $_loadedComponents = array();
private $_viewSearchDirs = array();
public $_dbcache = array();
private function __construct() {
$this->_componentCache['core'] = ComponentHandler::_Factory(ROOT_PDIR . 'core/component.xml');
$dh = opendir(ROOT_PDIR . 'components');
if (!$dh) return;
while ($file = readdir($dh)) {
if ($file{0} == '.') continue;
if (!is_dir(ROOT_PDIR . 'components/' . $file)) continue;
if (!is_readable(ROOT_PDIR . 'components/' . $file . '/component.xml')) continue;
$c = ComponentHandler::_Factory(ROOT_PDIR . 'components/' . $file . '/component.xml');
$file = strtolower($file);
if (!$c->isValid()) {
if (DEVELOPMENT_MODE) {
CAEUtils::AddMessage('Component ' . $c->getName() . ' appears to be invalid.');
}
continue;
}
$this->_componentCache[$file] = $c;
unset($c);
}
closedir($dh);
}
private function load() {
if ($this->_loaded) return;
try {
$res            = Dataset::Init()->table('component')->select('*')->execute();
$this->_dbcache = array();
foreach ($res as $r) {
$n                  = strtolower($r['name']);
$this->_dbcache[$n] = $r;
}
}
catch (Exception $e) {
return;
}
foreach ($this->_componentCache as $n => $c) {
$c->load();
if (!isset($this->_dbcache[$n])) {
continue;
}
$c->_versionDB = $this->_dbcache[$n]['version'];
$c->enabled    = ($this->_dbcache[$n]['enabled']);
if (!$c->enabled) {
unset($this->_componentCache[$n]);
continue;
}
if (!$c->isValid()) {
if (DEVELOPMENT_MODE) {
echo 'Component ' . $c->getName() . ' appears to be invalid due to:<br/>' . $c->getErrors();
}
unset($this->_componentCache[$n]);
}
}
if (EXEC_MODE == 'CLI') {
$cli_component = $this->getComponent('CLI');
}
$list = $this->_componentCache;
do {
$size = sizeof($list);
foreach ($list as $n => $c) {
if ($c->isInstalled() && $c->isLoadable() && $c->loadFiles()) {
if ($c->needsUpdated()) {
$c->upgrade();
}
$this->_registerComponent($c);
unset($list[$n]);
continue;
}
if ($c->isInstalled() && $c->needsUpdated() && $c->isLoadable()) {
$c->upgrade();
$c->loadFiles();
$this->_registerComponent($c);
unset($list[$n]);
continue;
}
if (!$c->isInstalled() && DEVELOPMENT_MODE && $c->isLoadable()) {
$c->install();
$c->loadFiles();
$this->_registerComponent($c);
unset($list[$n]);
continue;
}
}
}
while ($size > 0 && ($size != sizeof($list)));
if (DEVELOPMENT_MODE) {
foreach ($list as $l) {
if ($l->error & Component::ERROR_WRONGEXECMODE) continue;
$msg = 'Could not load installed component ' . $l->getName() . ' due to requirement failed.<br/>' . $l->getErrors();
echo $msg . '<br/>';
}
}
$this->_loaded = true;
}
public function _registerComponent($c) {
$name = strtolower($c->getName());
if ($c->hasLibrary()) {
$this->_libraries = array_merge($this->_libraries, $c->getLibraryList());
foreach ($c->getIncludePaths() as $path) {
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
}
$this->_scriptlibraries = array_merge($this->_scriptlibraries, $c->getScriptLibraryList());
}
if ($c->hasModule()) $this->_modules[$name] = $c->getVersionInstalled();
$this->_classes                 = array_merge($this->_classes, $c->getClassList());
$this->_viewClasses             = array_merge($this->_viewClasses, $c->getViewClassList());
$this->_widgets                 = array_merge($this->_widgets, $c->getWidgetList());
$this->_loadedComponents[$name] = $c;
}
public static function Singleton() {
if (is_null(self::$instance)) {
$cached = false;
if ($cached) {
self::$instance = unserialize($cached);
}
else {
self::$instance = new self();
}
self::$instance->load();
}
return self::$instance;
}
public static function GetInstance() {
return self::Singleton();
}
public static function GetComponent($componentName) {
$componentName = strtolower($componentName);
if (isset(ComponentHandler::Singleton()->_componentCache[$componentName])) return ComponentHandler::Singleton()->_componentCache[$componentName];
else return false;
}
public static function IsLibraryAvailable($name, $version = false, $operation = 'ge') {
$ch   = ComponentHandler::Singleton();
$name = strtolower($name);
if (!isset($ch->_libraries[$name])) {
return false;
}
elseif ($version !== false) {
return Core::VersionCompare($ch->_libraries[$name], $version, $operation);
}
else return true;
}
public static function IsJSLibraryAvailable($name, $version = false, $operation = 'ge') {
$ch   = ComponentHandler::Singleton();
$name = strtolower($name);
if (!isset($ch->_jslibraries[$name])) return false;
elseif ($version) return version_compare(str_replace('~', '-', $ch->_jslibraries[$name]->version), $version, $operation);
else return true;
}
public static function GetJSLibrary($library) {
$library = strtolower($library);
return ComponentHandler::Singleton()->_jslibraries[$library];
}
public static function LoadScriptLibrary($library) {
return Core::LoadScriptLibrary($library);
$library = strtolower($library);
$obj     = ComponentHandler::Singleton();
if (isset($obj->_scriptlibraries[$library])) {
return call_user_func($obj->_scriptlibraries[$library]);
}
else {
return false;
}
}
public static function IsComponentAvailable($name, $version = false, $operation = 'ge') {
$ch   = ComponentHandler::Singleton();
$name = strtolower($name);
if ($name == 'DB') return ComponentHandler::IsLibraryAvailable($name, $version, $operation);
if (!isset($ch->_loadedComponents[$name])) return false;
elseif ($version) return version_compare(str_replace('~', '-', $ch->_loadedComponents[$name]->getVersionInstalled()), $version, $operation);
else return true;
}
public static function IsViewClassAvailable($name, $casesensitive = true) {
if (!$casesensitive) $name = strtolower($name);
foreach (ComponentHandler::Singleton()->_viewClasses as $c => $l) {
if (!$casesensitive && strtolower($c) == $name) return $c;
elseif ($c == $name) return $c;
}
return false;
}
public static function CheckClass($classname) {
if (class_exists($classname)) return;
$classname = strtolower($classname);
if (isset(ComponentHandler::Singleton()->_classes[$classname])) {
require_once(ComponentHandler::Singleton()->_classes[$classname]);
}
}
public static function IsClassAvailable($classname) {
return (isset(self::Singleton()->_classes[$classname]));
}
public static function GetAllComponents() {
return ComponentHandler::Singleton()->_componentCache;
}
public static function GetLoadedComponents() {
$ret = array();
foreach (ComponentHandler::Singleton()->_loadedComponents as $c) {
$ret[] = $c;
}
return $ret;
}
public static function GetLoadedClasses() {
return ComponentHandler::Singleton()->_classes;
}
public static function GetLoadedWidgets() {
return ComponentHandler::Singleton()->_widgets;
}
public static function GetLoadedViewClasses() {
return ComponentHandler::Singleton()->_viewClasses;
}
public static function GetLoadedLibraries() {
return ComponentHandler::Singleton()->_libraries;
}
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/cachecore/backends/icachecore.interface.php
interface ICacheCore
{
public function create($data);
public function read();
public function update($data);
public function delete();
public function is_expired();
public function timestamp();
public function reset();
public function flush();
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/cachecore/backends/cachecore.class.php
if (file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'icachecore.interface.php')  && !interface_exists('ICacheCore'))
{
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'icachecore.interface.php';
}
class CacheCore
{
var $name;
var $location;
var $expires;
var $id;
var $timestamp;
var $gzip;
public function __construct($name, $location, $expires, $gzip = true)
{
if (!extension_loaded('zlib'))
{
$gzip = false;
}
$this->name = $name;
$this->location = $location;
$this->expires = $expires;
$this->gzip = $gzip;
return $this;
}
public static function init($name, $location, $expires, $gzip = true)
{
if (version_compare(PHP_VERSION, '5.3.0', '<'))
{
throw new Exception('PHP 5.3 or newer is required to use CacheCore::init().');
}
$self = get_called_class();
return new $self($name, $location, $expires, $gzip);
}
public function response_manager($callback, $params = null)
{
$params = is_array($params) ? $params : array($params);
if ($data = $this->read())
{
if ($this->is_expired())
{
if ($data = call_user_func_array($callback, $params))
{
$this->update($data);
}
else
{
$this->reset();
$data = $this->read();
}
}
}
else
{
if ($data = call_user_func_array($callback, $params))
{
$this->create($data);
}
}
return $data;
}
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/cachecore/backends/cachefile.class.php
class CacheFile extends CacheCore implements ICacheCore
{
public function __construct($name, $location, $expires, $gzip = true)
{
parent::__construct($name, $location, $expires, $gzip);
$this->id = $this->location . '/' . $this->name . '.cache';
}
public function create($data)
{
if (file_exists($this->id))
{
return false;
}
elseif (file_exists($this->location) && is_writeable($this->location))
{
$data = serialize($data);
$data = $this->gzip ? gzcompress($data) : $data;
return (bool) file_put_contents($this->id, $data);
}
return false;
}
public function read()
{
if (file_exists($this->id) && is_readable($this->id))
{
$data = file_get_contents($this->id);
$data = $this->gzip ? gzuncompress($data) : $data;
$data = unserialize($data);
if ($data === false)
{
$this->delete();
return false;
}
return $data;
}
return false;
}
public function update($data)
{
if (file_exists($this->id) && is_writeable($this->id))
{
$data = serialize($data);
$data = $this->gzip ? gzcompress($data) : $data;
return (bool) file_put_contents($this->id, $data);
}
return false;
}
public function delete()
{
if (file_exists($this->id))
{
return unlink($this->id);
}
return false;
}
public function timestamp()
{
clearstatcache();
if (file_exists($this->id))
{
$this->timestamp = filemtime($this->id);
return $this->timestamp;
}
return false;
}
public function reset()
{
if (file_exists($this->id))
{
return touch($this->id);
}
return false;
}
public function is_expired()
{
if ($this->timestamp() + $this->expires < time())
{
return true;
}
return false;
}
public function flush()
{
return false;
}
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/cachecore/Cache.class.php
define('__CACHE_PDIR', ROOT_PDIR . 'core/libs/cachecore/');
if(!class_exists('CacheCore')){
require_once(__CACHE_PDIR . 'backends/cachecore.class.php');
}
class Cache{
private static $_cachecache;
static protected $_Interface = null;
private $_backend = null;
private static $_KeyCache = array();
public function __construct($backend = null){
if(!$backend){
$cs = ConfigHandler::LoadConfigFile("configuration");
$backend = $cs['cache_type'];
}
$this->_backend = $backend;
}
public function get($key, $expires = 7200){
if(!isset($this)){
throw new Exception('Cannot call Cache::get() statically, please use Core::Cache()->get() instead.');
}
if(!isset(self::$_KeyCache[$key])){
self::$_KeyCache[$key] = $this->_factory($key, $expires)->read();
}
return self::$_KeyCache[$key];
}
public function set($key, $value, $expires = 7200){
if(!isset($this)) throw new Exception('Cannot call Cache::set() statically, please use Core::Cache()->set() instead.');
$c = $this->_factory($key, $expires);
self::$_KeyCache[$key] = $value;
if($c->create($value)) return true;
elseif($c->update($value)) return true;
else return false;
}
public function delete($key){
return $this->_factory($key)->delete();
}
public function flush(){
self::$_KeyCache = array();
return $this->_factory(null)->flush();
}
public function _factory($key, $expires = 7200){
$obj = false;
switch($this->_backend){
case 'apc':
if(!class_exists('CacheAPC')) require_once(__CACHE_PDIR . 'backends/cacheapc.class.php');
$obj = new CacheAPC($key, null, $expires);
break;
case 'file':
default:
if(!class_exists('CacheFile')) require_once(__CACHE_PDIR . 'backends/cachefile.class.php');
if(!is_dir(TMP_DIR . 'cache')) mkdir(TMP_DIR . 'cache');
$obj = new CacheFile($key, TMP_DIR . 'cache', $expires);
break;
}
return $obj;
}
public static function GetSystemCache(){
if(self::$_Interface !== null) return self::$_Interface;
self::$_Interface = new Cache();
return self::$_Interface;
}
}
class Cache_Exception extends Exception{
}


Debug::Write('Loading hook handler');
### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/core/HookHandler.class.php
class HookHandler implements ISingleton {
private static $RegisteredHooks = array();
private static $Instance = null;
private static $EarlyRegisteredHooks = array();
private function __construct() {
}
public static function Singleton() {
if (is_null(self::$Instance)) self::$Instance = new self();
return self::$Instance;
}
public static function GetInstance() {
return self::singleton();
}
public static function AttachToHook($hookName, $callFunction) {
$hookName = strtolower($hookName); // Case insensitive will prevent errors later on.
Debug::Write('Registering function ' . $callFunction . ' to hook ' . $hookName);
if (!isset(HookHandler::$RegisteredHooks[$hookName])) {
if (!isset(self::$EarlyRegisteredHooks[$hookName])) self::$EarlyRegisteredHooks[$hookName] = array();
self::$EarlyRegisteredHooks[$hookName][] = array('call' => $callFunction);
return;
}
HookHandler::$RegisteredHooks[$hookName]->attach($callFunction);
}
public static function RegisterHook(Hook $hook) {
$name = $hook->getName();
HookHandler::$RegisteredHooks[$name] = $hook;
if (isset(self::$EarlyRegisteredHooks[$name])) {
foreach (self::$EarlyRegisteredHooks[$name] as $b) {
$hook->attach($b['call']);
}
unset(self::$EarlyRegisteredHooks[$name]);
}
}
public static function RegisterNewHook($hookName) {
$hook = new Hook($hookName);
HookHandler::RegisterHook($hook);
}
public static function DispatchHook($hookName, $args = null) {
$hookName = strtolower($hookName); // Case insensitive will prevent errors later on.
Debug::Write('Dispatching hook ' . $hookName);
Core::AddProfileTime('Calling hook ' . $hookName);
if (!isset(HookHandler::$RegisteredHooks[$hookName])) {
trigger_error('Tried to dispatch an undefined hook ' . $hookName, E_USER_NOTICE);
return;
}
$args = func_get_args();
array_shift($args);
$hook   = HookHandler::$RegisteredHooks[$hookName];
$result = call_user_func_array(array(&$hook, 'dispatch'), $args);
Core::AddProfileTime('Called hook ' . $hookName);
return $result;
}
public static function GetAllHooks() {
return self::$RegisteredHooks;
}
public static function PrintHooks() {
echo '<dl class="xdebug-var-dump">';
foreach (self::$RegisteredHooks as $h) {
echo '<dt>' . $h->name . '</dt>';
if ($h->description) echo '<dd>' . $h->description . '</dd>';
echo "<br/>\n";
}
echo '</dl>';
}
}
class Hook {
public $name;
public $description;
private $_bindings = array();
public function __construct($name) {
$this->name = $name;
HookHandler::RegisterHook($this);
}
public function attach($function) {
$this->_bindings[] = array('call' => $function);
}
public function dispatch($args = null) {
foreach ($this->_bindings as $call) {
$result = call_user_func_array($call['call'], func_get_args());
if ($result === false) return false;
}
return true;
}
public function __toString() {
return $this->getName();
}
public function getName() {
return strtolower($this->name);
}
}
HookHandler::singleton();
HookHandler::RegisterNewHook('db_ready');
HookHandler::RegisterNewHook('libraries_loaded');
HookHandler::RegisterNewHook('libraries_ready');
HookHandler::RegisterNewHook('components_loaded');
HookHandler::RegisterNewHook('components_ready');
HookHandler::RegisterNewHook('session_ready');

$preincludes_time = microtime(true);
Debug::Write('Loading core system');
### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/core/InstallTask.class.php
class InstallTask {
public static function ParseNode(DomElement $node, $relativeDir) {
foreach ($node->getElementsByTagName('*') as $c) {
$result = HookHandler::DispatchHook('/install_task/' . $c->tagName, $c, $relativeDir);
if (!$result) return false;
}
return true;
}
public static function _ParseSetConfig($node, $relativeDir) {
$set   = $node->getAttribute('set');
$key   = $node->getAttribute('key');
$value = $node->nodeValue;
DB::Execute("UPDATE `" . DB_PREFIX . "configs` SET `value` = ? WHERE `config_set` = ? AND `key` = ? LIMIT 1", array($value, $set, $key));
}
public static function _ParseAddConfig($node, $relativeDir) {
$set         = $node->getAttribute('set');
$key         = $node->getAttribute('key');
$type        = @$node->getAttribute('type');
$valueType   = @$node->getAttribute('valuetype');
$default     = @$node->getAttribute('default');
$value       = @$node->getAttribute('value');
$options     = @$node->getAttribute('options');
$description = @$node->getAttribute('description');
if (!$type) $type = 'setting';
if (!$valueType) $valueType = 'string';
if (!$default) $default = $value;
if (!$value) $value = $default;
if (!$description) $description = "";
$options = null;
if ($valueType == 'enum') {
$options = '';
foreach ($node->getElementsByTagName('option') as $opt) {
$value = @$opt->getAttribute('value');
$title = $opt->nodeValue;
if (!$value) $value = $title;
$title = str_replace(array(':', ';'), array('/:', '/;'), $title);
$value = str_replace(array(':', ';'), array('/:', '/;'), $value);
$options .= (($options == '') ? '' : ";\n") . (($title == $value) ? '' : $value . ':') . $title;
}
}
$pkeys = array(
'config_set' => $set,
'key'        => $key
);
$keys  = array(
'type'          => $type,
'value_type'    => $valueType,
'default_value' => $default,
'value'         => $value,
'options'       => $options,
'description'   => $description
);
$q = DB::CreateSQLFromHash(DB_PREFIX . 'configs', 'auto', $keys, $pkeys);
DB::Execute($q);
if ($type == 'define') define($key, $value);
}
public static function _ParseAddResourceDir($node, $relativeDir) {
$dir = $node->getAttribute('dir');
if (is_dir(ROOT_PDIR . '/resources/' . $dir)) return true;
return mkdir(ROOT_PDIR . '/resources/' . $dir, 0777, true);
}
public static function _ParseSql($node, $relativeDir) {
$file = @$node->getAttribute('file');
if ($file) {
$file = $relativeDir . $file;
$sql  = file_get_contents($file);
}
else {
$sql = $node->nodeValue;
}
if (!$sql) return false;
$foe = @$node->getAttribute('failonerror');
switch ($foe) {
case null:
case 'true':
case 'yes':
case '1':
$foe = true;
break;
default:
$foe = false;
break;
}
$prefix = $node->getAttribute('prefix');
$sql    = str_replace($prefix, DB_PREFIX, $sql);
$sql = str_replace(array('\r\n', '\r'), '\n', $sql);
$sqls = preg_split('/;[ ]*\n/', $sql);
foreach ($sqls as $sqlline) {
if (trim($sqlline) == '') continue;
if ($foe) {
DB::Execute($sqlline);
}
else {
$saveErrHandlers = DB::GetConnection()->IgnoreErrors();
DB::Execute($sqlline);
DB::GetConnection()->IgnoreErrors($saveErrHandlers);
}
}
return true;
}
}
HookHandler::RegisterNewHook('/install_task/sql');
HookHandler::AttachToHook('/install_task/sql', 'InstallTask::_ParseSql');
HookHandler::RegisterNewHook('/install_task/addconfig');
HookHandler::AttachToHook('/install_task/addconfig', 'InstallTask::_ParseAddConfig');
HookHandler::RegisterNewHook('/install_task/setconfig');
HookHandler::AttachToHook('/install_task/setconfig', 'InstallTask::_ParseSetConfig');
HookHandler::RegisterNewHook('/install_task/addresourcedir');
HookHandler::AttachToHook('/install_task/addresourcedir', 'InstallTask::_ParseAddResourceDir');

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/core/Core.class.php
class Core implements ISingleton {
private static $instance;
private static $_LoadedComponents = false;
private $_components = null;
private $_libraries = array();
private $_classes = array();
private $_widgets = array();
private $_viewClasses = array();
private $_scriptlibraries = array();
private $_loaded = false;
private $_componentobj;
private $_profiletimes = array();
private $_permissions = array();
public function load() {
return;
if ($this->_loaded) return;
$XMLFilename = ROOT_PDIR . 'core/core.xml';
$this->setFilename($XMLFilename);
$this->setRootName('core');
if (!parent::load()) {
$this->error     = $this->error | Component::ERROR_INVALID;
$this->errstrs[] = $XMLFilename . ' parsing failed, not valid XML.';
$this->valid     = false;
return;
}
$this->version = $this->getRootDOM()->getAttribute("version");
$this->_loaded = true;
}
public function isLoadable() {
return $this->_isInstalled();
}
public function isValid() {
return $this->valid;
}
public function loadFiles() {
return true;
}
public function hasLibrary() {
return true;
}
public function hasModule() {
return true;
}
public function hasJSLibrary() {
return false;
}
public function getClassList() {
return array('Core'     => ROOT_PDIR . 'core/Core.class.php',
'CoreView' => ROOT_PDIR . 'core/CoreView.class.php');
}
public function getViewClassList() {
return array('CoreView' => ROOT_PDIR . 'core/CoreView.class.php');
}
public function getLibraryList() {
return array('Core' => $this->versionDB);
}
public function getViewSearchDirs() {
return array(ROOT_PDIR . 'core/view/');
}
public function getIncludePaths() {
return array();
}
public function install() {
if ($this->_isInstalled()) return;
if (!class_exists('DB')) return; // I need a database present before I can install.
InstallTask::ParseNode(
$this->getRootDOM()->getElementsByTagName('install')->item(0),
ROOT_PDIR . 'core/'
);
DB::Execute("REPLACE INTO `" . DB_PREFIX . "component` (`name`, `version`) VALUES (?, ?)", array('Core', $this->version));
$this->versionDB = $this->version;
}
public function upgrade() {
if (!$this->_isInstalled()) return false;
if (!class_exists('DB')) return; // I need a database present before I can install.
$canBeUpgraded = true;
while ($canBeUpgraded) {
$canBeUpgraded = false;
foreach ($this->getElements('upgrade') as $u) {
if (Core::GetComponent()->getVersionInstalled() == @$u->getAttribute('from')) {
$canBeUpgraded = true;
InstallTask::ParseNode($u, ROOT_PDIR . 'core/');
$this->versionDB = @$u->getAttribute('to');
DB::Execute("REPLACE INTO `" . DB_PREFIX . "component` (`name`, `version`) VALUES (?, ?)", array($this->name, $this->versionDB));
}
}
}
}
private function __construct() {
}
private function _addProfileTime($event, $microtime = null) {
if ($microtime === null) $microtime = microtime(true);
$time = (sizeof($this->_profiletimes)) ? ($microtime - $this->_profiletimes[0]['microtime']) : 0;
$this->_profiletimes[] = array(
'event'     => $event,
'microtime' => $microtime,
'timetotal' => $time
);
}
private function _isInstalled() {
return ($this->_componentobj->getVersionInstalled() === false) ? false : true;
}
private function _needsUpdated() {
return ($this->_componentobj->getVersionInstalled() != $this->_componentobj->getVersion());
}
private function _loadComponents() {
if ($this->_components) return null;
$this->_components = array();
$this->_libraries  = array();
if(DEVELOPMENT_MODE){
$enablecache = false;
}
else{
$enablecache = true;
}
if(!$enablecache || ($cachedcomponents = Cache::GetSystemCache()->get('core-components', (3600 * 24))) === false){
$this->_components['core'] = ComponentFactory::Load(ROOT_PDIR . 'core/component.xml');
$dh = opendir(ROOT_PDIR . 'components');
if (!$dh) throw new CoreException('Unable to open directory [' . ROOT_PDIR . 'components/] for reading.');
while (($file = readdir($dh)) !== false) {
if ($file{0} == '.') continue;
if (!is_dir(ROOT_PDIR . 'components/' . $file)) continue;
if (!is_readable(ROOT_PDIR . 'components/' . $file . '/component.xml')) continue;
$c = ComponentFactory::Load(ROOT_PDIR . 'components/' . $file . '/component.xml');
$file = strtolower($file);
if (!$c->isValid()) {
if (DEVELOPMENT_MODE) {
CAEUtils::AddMessage('Component ' . $c->getName() . ' appears to be invalid.');
}
continue;
}
$this->_components[$file] = $c;
unset($c);
}
closedir($dh);
foreach ($this->_components as $c) {
try {
$c->load();
$c->getClassList();
$c->getViewSearchDir();
$c->getSmartyPluginDirectory();
$c->getWidgetList();
}
catch (Exception $e) {
var_dump($e);
die();
}
}
if($enablecache){
Cache::GetSystemCache()->set('core-components', $this->_components);
}
}
else{
$this->_components = $cachedcomponents;
}
$list = $this->_components;
do {
$size = sizeof($list);
foreach ($list as $n => $c) {
if($c->isInstalled() && !$c->isEnabled()){
unset($list[$n]);
continue;
}
if ($c->isInstalled() && $c->isLoadable() && $c->loadFiles()) {
if ($c->needsUpdated()) {
$c->upgrade();
}
$this->_registerComponent($c);
unset($list[$n]);
continue;
}
if ($c->isInstalled() && $c->needsUpdated() && $c->isLoadable()) {
$c->upgrade();
$c->loadFiles();
$this->_registerComponent($c);
unset($list[$n]);
continue;
}
if (!$c->isInstalled() && DEVELOPMENT_MODE && $c->isLoadable()) {
$c->install();
$c->loadFiles();
$this->_registerComponent($c);
unset($list[$n]);
continue;
}
}
}
while ($size > 0 && ($size != sizeof($list)));
if (DEVELOPMENT_MODE) {
foreach ($list as $l) {
if ($l->error & Component::ERROR_WRONGEXECMODE) continue;
$msg = 'Could not load installed component ' . $l->getName() . ' due to requirement failed.' . "\n" . $l->getErrors();
error_log($msg);
}
}
}
public function _registerComponent($c) {
$name = strtolower($c->getName());
if ($c->hasLibrary()) {
$this->_libraries = array_merge($this->_libraries, $c->getLibraryList());
foreach ($c->getIncludePaths() as $path) {
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
}
}
$this->_scriptlibraries = array_merge($this->_scriptlibraries, $c->getScriptLibraryList());
if ($c->hasModule()) $this->_modules[$name] = $c->getVersionInstalled();
$this->_classes           = array_merge($this->_classes, $c->getClassList());
$this->_viewClasses       = array_merge($this->_viewClasses, $c->getViewClassList());
$this->_widgets           = array_merge($this->_widgets, $c->getWidgetList());
$this->_components[$name] = $c;
if($c instanceof Component_2_1){
$this->_permissions       = array_merge($this->_permissions, $c->getPermissions());
ksort($this->_permissions);
}
}
public static function CheckClass($classname) {
if (class_exists($classname)) return;
$classname = strtolower($classname);
if (!self::$_LoadedComponents) {
self::LoadComponents();
}
if (isset(Core::Singleton()->_classes[$classname])) {
require_once(Core::Singleton()->_classes[$classname]);
}
}
public static function LoadComponents() {
$self = self::Singleton();
$self->_loadComponents();
}
public static function DB() {
return \Core\DB();
}
public static function Cache() {
return Cache::GetSystemCache();
}
public static function FTP() {
return \Core\FTP();
}
public static function User() {
return \Core\user();
}
public static function File($filename = null) {
return \Core\file($filename);
}
public static function Directory($directory) {
return \Core\directory($directory);
}
public static function TranslateDimensionToPreviewSize($dimensions) {
$themesizes = array(
'sm'  => ConfigHandler::Get('/theme/filestore/preview-size-sm'),
'med' => ConfigHandler::Get('/theme/filestore/preview-size-med'),
'lg'  => ConfigHandler::Get('/theme/filestore/preview-size-lg'),
'xl'  => ConfigHandler::Get('/theme/filestore/preview-size-xl'),
);
if (sizeof(func_get_args()) == 2) {
$width  = (int)func_get_arg(0);
$height = (int)func_get_arg(1);
}
elseif (is_numeric($dimensions)) {
$width  = $dimensions;
$height = $dimensions;
}
elseif (stripos($dimensions, 'x') !== false) {
$ds     = explode('x', strtolower($dimensions));
$width  = trim($ds[0]);
$height = trim($ds[1]);
}
else {
return null;
}
$smaller = min($width, $height);
if ($smaller >= $themesizes['xl']) return 'xl';
elseif ($smaller >= $themesizes['lg']) return 'lg';
elseif ($smaller >= $themesizes['med']) return 'med';
else return 'sm';
}
public static function AddProfileTime($event, $microtime = null) {
self::Singleton()->_addProfileTime($event, $microtime);
}
public static function GetProfileTimeTotal() {
return (sizeof(self::Singleton()->_profiletimes)) ? (microtime(true) - self::Singleton()->_profiletimes[0]['microtime']) : 0;
}
public static function GetProfileTimes() {
return self::Singleton()->_profiletimes;
}
public static function FormatProfileTime($in) {
$in = round($in, 5) * 1000;
if ($in == 0) return '0000.00 ms';
$parts = explode('.', $in);
$whole = str_pad($parts[0], 4, 0, STR_PAD_LEFT);
$dec   = (isset($parts[1])) ? str_pad($parts[1], 2, 0, STR_PAD_RIGHT) : '00';
return $whole . '.' . $dec . ' ms';
}
public static function GetPermissions(){
return self::Singleton()->_permissions;
}
public static function GetComponent($name = 'core') {
return isset(self::Singleton()->_components[$name]) ? self::Singleton()->_components[$name] : null;
}
public static function GetComponents() {
return self::Singleton()->_components;
}
public static function GetComponentByController($controller) {
$controller = strtolower($controller);
$self = self::Singleton();
foreach ($self->_components as $c) {
$controllers = $c->getControllerList();
if (isset($controllers[$controller])) return $c;
}
return null;
}
public static function GetStandardHTTPHeaders($forcurl = false, $autoclose = false) {
$headers = array(
'User-Agent: Core Plus ' . self::GetComponent()->getVersion() . ' (http://corepl.us)',
'Servername: ' . SERVERNAME,
);
if ($autoclose) {
$headers[] = 'Connection: close';
}
if ($forcurl) {
return $headers;
}
else {
return implode("\r\n", $headers);
}
}
public static function Singleton() {
if(self::$instance === null){
self::$instance = new self();
}
return self::$instance;
}
public static function GetInstance() {
return self::Singleton();
}
public static function _LoadFromDatabase() {
if (!self::GetComponent()->load()) {
if (DEVELOPMENT_MODE) {
self::GetComponent()->install();
die('Installed core!  <a href="' . ROOT_WDIR . '">continue</a>');
}
else {
die('There was a server error, please notify the administrator of this.');
}
}
return;
}
public static function IsClassAvailable($classname) {
if(self::$instance == null){
self::Singleton();
}
return (isset(self::$instance->_classes[$classname]));
}
public static function IsLibraryAvailable($name, $version = false, $operation = 'ge') {
$ch   = self::Singleton();
$name = strtolower($name);
if (!isset($ch->_libraries[$name])) {
return false;
}
elseif ($version !== false) {
return Core::VersionCompare($ch->_libraries[$name], $version, $operation);
}
else return true;
}
public static function IsJSLibraryAvailable($name, $version = false, $operation = 'ge') {
$ch   = self::Singleton();
$name = strtolower($name);
if (!isset($ch->_jslibraries[$name])) return false;
elseif ($version) return version_compare(str_replace('~', '-', $ch->_jslibraries[$name]->version), $version, $operation);
else return true;
}
public static function GetJSLibrary($library) {
$library = strtolower($library);
return self::Singleton()->_jslibraries[$library];
}
public static function LoadScriptLibrary($library) {
$library = strtolower($library);
$obj     = self::Singleton();
if (isset($obj->_scriptlibraries[$library])) {
return call_user_func($obj->_scriptlibraries[$library]);
}
else {
return false;
}
}
public static function IsComponentAvailable($name, $version = false, $operation = 'ge') {
$self = self::Singleton();
$name = strtolower($name);
if (!isset($self->_components[$name])){
return false;
}
elseif (!$self->_components[$name]->isEnabled()){
return false;
}
elseif ($version){
return Core::VersionCompare($self->_components[$name]->getVersionInstalled(), $version, $operation);
}
else{
return true;
}
}
public static function IsInstalled() {
return Core::Singleton()->_isInstalled();
}
public static function NeedsUpdated() {
return Core::Singleton()->_needsUpdated();
}
public static function GetVersion() {
return Core::GetComponent()->getVersionInstalled();
}
public static function ResolveAsset($asset) {
if (strpos($asset, '://') !== false) return $asset;
if (strpos($asset, 'assets/') !== 0) $asset = 'assets/' . $asset;
$f = self::File($asset);
return $f->getURL();
$keyname    = 'asset-resolveurl';
$cachevalue = self::Cache()->get($keyname, (3600 * 24));
if (!$cachevalue) $cachevalue = array();
if (!isset($cachevalue[$asset])) {
$f = self::File($asset);
$cachevalue[$asset] = $f->getURL();
self::Cache()->set($keyname, $cachevalue, (3600 * 24));
}
return $cachevalue[$asset];
}
public static function ResolveLink($url) {
if ($url == '#') return $url;
if (strpos($url, '://') !== false) return $url;
$a = PageModel::SplitBaseURL($url);
return ROOT_URL . substr($a['rewriteurl'], 1);
$p = new PageModel($url);
return $p->getResolvedURL();
}
public static function ResolveFilenameTo($filename, $base = ROOT_URL) {
$file = preg_replace('/^(' . str_replace('/', '\\/', ROOT_PDIR . '|' . ROOT_URL) . ')/', '', $filename);
return $base . $file;
}
static public function Redirect($page) {
$page = self::ResolveLink($page);
if ($page == CUR_CALL) return false;
if (DEVELOPMENT_MODE) header('X-Content-Encoded-By: Core Plus ' . Core::GetComponent()->getVersion());
header("Location:" . $page);
HookHandler::DispatchHook('/core/page/postrender');
die("If your browser does not refresh, please <a href=\"{$page}\">Click Here</a>");
}
static public function Reload() {
if (DEVELOPMENT_MODE) header('X-Content-Encoded-By: Core Plus ' . Core::GetComponent()->getVersion());
header('Location:' . CUR_CALL);
HookHandler::DispatchHook('/core/page/postrender');
die("If your browser does not refresh, please <a href=\"" . CUR_CALL . "\">Click Here</a>");
}
static public function GoBack() {
CAEUtils::redirect(CAEUtils::GetNavigation());
}
static public function RequireSSL() {
if (!ENABLE_SSL) return;
if (!isset($_SERVER['HTTPS'])) {
$page = ViewClass::ResolveURL($_SERVER['REQUEST_URI'], true);
header("Location:" . $page);
HookHandler::DispatchHook('/core/page/postrender');
die("If your browser does not refresh, please <a href=\"{$page}\">Click Here</a>");
}
}
static public function GetNavigation($base) {
if (!isset($_SESSION['nav'])) return $base;
if (!isset($_SESSION['nav'][$base])) return $base;
$coreparams  = array();
$extraparams = array();
foreach ($_SESSION['nav'][$base]['parameters'] as $k => $v) {
if (is_numeric($k)) $coreparams[] = $v;
else $extraparams[] = $k . '=' . $v;
}
return $base .
(sizeof($coreparams) ? '/' . implode('/', $coreparams) : '') .
(sizeof($extraparams) ? '?' . implode('&', $extraparams) : '');
}
static public function RecordNavigation(PageModel $page) {
if (!isset($_SESSION['nav'])) $_SESSION['nav'] = array();
$c = $page->getControllerClass();
if (strpos($c, 'Controller') == strlen($c) - 10) $c = substr($c, 0, -10);
$base = '/' . $c . '/' . $page->getControllerMethod();
$_SESSION['nav'][$base] = array(
'parameters' => $page->getParameters(),
'time'       => Time::GetCurrent(),
);
}
static public function SetMessage($messageText, $messageType = 'info') {
if (trim($messageText) == '') return;
$messageType = strtolower($messageType);
if (EXEC_MODE == 'CLI') {
$messageText = preg_replace('/<br[^>]*>/i', "\n", $messageText);
echo "[" . $messageType . "] - " . $messageText . "\n";
}
else {
if (!isset($_SESSION['message_stack'])) $_SESSION['message_stack'] = array();
$_SESSION['message_stack'][] = array(
'mtext' => $messageText,
'mtype' => $messageType,
);
}
}
static public function AddMessage($messageText, $messageType = 'info') {
Core::SetMessage($messageText, $messageType);
}
static public function GetMessages($returnSorted = FALSE, $clearStack = TRUE) {
if (!isset($_SESSION['message_stack'])) return array();
$return = $_SESSION['message_stack'];
if ($returnSorted) $return = Core::SortByKey($return, 'mtype');
if ($clearStack) unset($_SESSION['message_stack']);
return $return;
}
static public function SortByKey($named_recs, $order_by, $rev = false, $flags = 0) {
$named_hash = array();
foreach ($named_recs as $key=> $fields) $named_hash["$key"] = $fields[$order_by];
if ($rev) arsort($named_hash, $flags);
else asort($named_hash, $flags);
$sorted_records = array();
foreach ($named_hash as $key=> $val) $sorted_records["$key"] = $named_recs[$key];
return $sorted_records;
}
static public function ImplodeKey($glue, &$array) {
$arrayKeys = array();
foreach ($array as $key => $value) {
$arrayKeys[] = $key;
}
return implode($glue, $arrayKeys);
}
static public function RandomHex($length = 1, $casesensitive = false) {
$output = '';
if ($casesensitive) {
$chars   = '0123456789ABCDEFabcdef';
$charlen = 21; // (needs to be -1 of the actual length)
}
else {
$chars   = '0123456789ABCDEF';
$charlen = 15; // (needs to be -1 of the actual length)
}
$output = '';
for ($i = 0; $i < $length; $i++) {
$pos = rand(0, $charlen);
$output .= $chars{$pos};
}
return $output;
}
public static function FormatSize($filesize, $round = 2) {
$suf = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
$c   = 0;
while ($filesize >= 1024) {
$c++;
$filesize = $filesize / 1024;
}
return (round($filesize, $round) . ' ' . $suf[$c]);
}
public static function GetExtensionFromString($str) {
if (strpos($str, '.') === false) return '';
return substr($str, strrpos($str, '.') + 1);
}
public static function CheckEmailValidity($email) {
$atIndex = strrpos($email, "@");
if (is_bool($atIndex) && !$atIndex) return false;
$domain    = substr($email, $atIndex + 1);
$local     = substr($email, 0, $atIndex);
$localLen  = strlen($local);
$domainLen = strlen($domain);
if ($localLen < 1 || $localLen > 64) {
return false;
}
if ($domainLen < 1 || $domainLen > 255) {
return false;
}
if ($local[0] == '.' || $local[$localLen - 1] == '.') {
return false;
}
if (preg_match('/\\.\\./', $local)) {
return false;
}
if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
return false;
}
if (preg_match('/\\.\\./', $domain)) {
return false;
}
if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\", "", $local))) {
if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\", "", $local))) {
return false;
}
}
if (ConfigHandler::Get('/core/email/verify_with_dns') && !(checkdnsrr($domain, "MX") || checkdnsrr($domain, "A"))) {
return false;
}
return true;
}
public static function _AttachCoreJavascript() {
$script = '<script type="text/javascript">
var Core = {
Version: "' . self::GetComponent()->getVersion() . '",
ROOT_WDIR: "' . ROOT_WDIR . '",
ROOT_URL: "' . ROOT_URL . '",
ROOT_URL_SSL: "' . ROOT_URL_SSL . '",
ROOT_URL_NOSSL: "' . ROOT_URL_NOSSL . '"
};
</script>';
View::AddScript($script, 'head');
View::AddScript('js/core.js', 'head');
View::AddScript('js/core-foot.js', 'foot');
}
public static function _AttachCoreStrings() {
View::AddScript('js/core.strings.js');
return true;
}
public static function VersionCompare($version1, $version2, $operation = null) {
if (!$version1) $version1 = 0;
if (!$version2) $version2 = 0;
$version1 = Core::VersionSplit($version1);
$version2 = Core::VersionSplit($version2);
$v1    = $version1['major'] . '.' . $version1['minor'] . '.' . $version1['point'];
$v2    = $version2['major'] . '.' . $version2['minor'] . '.' . $version2['point'];
$check = version_compare($v1, $v2);
if($check == 0 && $version1['user'] && $version2['user']){
$check = version_compare($version1['user'], $version2['user']);
}
if ($operation === null){
return $check;
}
elseif($check == -1){
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
public static function VersionSplit($version) {
$ret = array(
'major'     => 0,
'minor'     => 0,
'point'     => 0,
'user'      => 0,
'stability' => '',
);
$v = array();
$lengthall = strlen($version);
$pos       = 0;
$x         = 0;
while ($pos < $lengthall && $x < 10) {
$nextpos = strpos($version, '.', $pos) - $pos;
$part = ($nextpos > 0) ? substr($version, $pos, $nextpos) : substr($version, $pos);
if (($subpos = strpos($part, '-')) !== false) {
$subpart = strtolower(substr($part, $subpos + 1));
if ($subpart == 'a') {
$ret['stability'] = 'alpha';
}
elseif ($subpart == 'b') {
$ret['stability'] = 'beta';
}
else {
$ret['stability'] = $subpart;
}
$part = substr($part, 0, $subpos);
}
elseif(($subpos = strpos($part, '~')) !== false){
$subpart = strtolower(substr($part, $subpos + 1));
$ret['user'] = $subpart;
}
$v[] = (int)$part;
$pos = ($nextpos > 0) ? $pos + $nextpos + 1 : $lengthall;
$x++; // Just in case something really bad happens here...
}
for ($i = 0; $i < 3; $i++) {
if (!isset($v[$i])) $v[$i] = 0;
}
$ret['major'] = $v[0];
$ret['minor'] = $v[1];
$ret['point'] = $v[2];
return $ret;
}
}
class CoreException extends Exception {
}
spl_autoload_register('Core::CheckClass');

Debug::Write('Loading configs');
### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/core/ConfigHandler.class.php
### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/models/ConfigModel.class.php
class ConfigModel extends Model {
public static $Schema = array(
'key'           => array(
'type'      => Model::ATT_TYPE_STRING,
'maxlength' => 255,
'required'  => true,
'null'      => false,
),
'type'          => array(
'type'    => Model::ATT_TYPE_ENUM,
'options' => array('string', 'int', 'boolean', 'enum', 'set'),
'default' => 'string',
'null'    => false,
),
'default_value' => array(
'type'    => Model::ATT_TYPE_TEXT,
'default' => null,
'null'    => true,
),
'value'         => array(
'type'    => Model::ATT_TYPE_TEXT,
'default' => null,
'null'    => true,
),
'options'       => array(
'type'      => Model::ATT_TYPE_STRING,
'maxlength' => 511,
'default'   => null,
'null'      => true,
),
'description'   => array(
'type'    => Model::ATT_TYPE_TEXT,
'default' => null,
'null'    => true,
),
'mapto'         => array(
'type'      => Model::ATT_TYPE_STRING,
'maxlength' => 32,
'default'   => null,
'comment'   => 'The define constant to map the value to on system load.',
'null'      => true,
),
'created'       => array(
'type' => Model::ATT_TYPE_CREATED
),
'updated'       => array(
'type' => Model::ATT_TYPE_UPDATED
)
);
public static $Indexes = array(
'primary' => array('key'),
);
public function getValue() {
$v = $this->get('value');
if ($v === null) $v = $this->get('default');
switch ($this->get('type')) {
case 'int':
return (int)$v;
case 'boolean':
return ($v == '1' || $v == 'true') ? true : false;
case 'set':
return array_map('trim', explode('|', $v));
default:
return $v;
}
}
} // END class ConfigModel extends Model

class ConfigHandler implements ISingleton {
private static $instance = null;
public static $directory;
private static $CacheFromDB = array();
private function __construct() {
ConfigHandler::$directory = ROOT_PDIR . "config/";
if (!is_readable(ConfigHandler::$directory)) {
throw new Exception("Could not open config directory [" . ConfigHandler::$directory . "] for reading.");
}
}
public static function Singleton() {
if (is_null(self::$instance)) {
self::$instance = new self();
HookHandler::AttachToHook('db_ready', 'ConfigHandler::_DBReadyHook');
}
return self::$instance;
}
public static function getInstance() {
return self::singleton();
}
public static function LoadConfigFile($config) {
$return = array();
$file = ConfigHandler::$directory . $config . '.xml';
if (!file_exists($file)) {
trigger_error("Requested config file $config.xml not located within " . ConfigHandler::$directory, E_USER_NOTICE);
return false;
}
if (!is_readable($file)) {
trigger_error("Unable to read $file, please ensure it's permissions are set correctly", E_USER_NOTICE);
return false;
}
$xml = new DOMDocument();
$xml->load($file);
foreach ($xml->getElementsByTagName("define") as $xmlEl) {
$name  = $xmlEl->getAttribute("name");
$type  = $xmlEl->getAttribute("type");
$value = $xmlEl->getElementsByTagName("value")->item(0)->nodeValue;
switch (strtolower($type)) {
case 'int':
$value = (int)$value;
break;
case 'octal':
$value = octdec($value);
break;
case 'boolean':
$value = (($value == 'true' || $value == '1' || $value == 'yes') ? true : false);
break;
}
if (!defined($name))
define($name, $value);
} // foreach($xml->getElementsByTagName("define") as $xmlEl)
foreach ($xml->getElementsByTagName("return") as $xmlEl) {
$name  = $xmlEl->getAttribute("name");
$type  = $xmlEl->getAttribute("type");
$value = $xmlEl->getElementsByTagName("value")->item(0)->nodeValue;
switch (strtolower($type)) {
case 'int':
$value = (int)$value;
break;
case 'octal':
$value = octdec($value);
case 'boolean':
$value = (($value == 'true' || $value == '1' || $value == 'yes') ? true : false);
break;
}
$return[$name] = $value;
} // foreach($xml->getElementsByTagName("define") as $xmlEl)
return (!count($return) ? true : $return);
}
public static function GetValue($key) {
return self::Get($key);
return (isset(ConfigHandler::$CacheFromDB[$key])) ? ConfigHandler::$CacheFromDB[$key] : null;
}
public static function GetConfig($key) {
if (!isset(ConfigHandler::$CacheFromDB[$key])) {
ConfigHandler::$CacheFromDB[$key] = new ConfigModel($key);
}
return ConfigHandler::$CacheFromDB[$key];
}
public static function Get($key) {
if (isset(ConfigHandler::$CacheFromDB[$key])) return ConfigHandler::$CacheFromDB[$key]->getValue();
elseif (isset($_SESSION) && isset($_SESSION['configs']) && isset($_SESSION['configs'][$key])) return $_SESSION['configs'][$key];
else return null;
}
public static function Set($key, $value) {
if (!isset(ConfigHandler::$CacheFromDB[$key])) return false;
ConfigHandler::$CacheFromDB[$key]->set('value', $value);
ConfigHandler::$CacheFromDB[$key]->save();
return true;
}
public static function _Set(ConfigModel $config) {
ConfigHandler::$CacheFromDB[$config->get('key')] = $config;
}
public static function _DBReadyHook() {
ConfigHandler::$CacheFromDB = array();
$fac                        = ConfigModel::Find();
foreach ($fac as $model) {
ConfigHandler::$CacheFromDB[$model->get('key')] = $model;
if ($model->get('mapto') && !defined($model->get('mapto'))) define($model->get('mapto'), $model->getValue());
}
}
public static function _DBReadyHookLEGACY() {
$obj = new Dataset();
$obj->table('config');
$obj->select(array('key', 'value', 'type', 'mapto'));
$rs = $obj->execute();
if (!$rs) return false;
foreach ($rs as $row) {
switch ($row['type']) {
case 'int':
$row['value'] = (int)$row['value'];
break;
case 'boolean':
$row['value'] = ($row['value'] == '1' || $row['value'] == 'true') ? true : false;
break;
case 'set':
$row['value'] = array_map('trim', explode('|', $row['value']));
}
ConfigHandler::$cacheFromDB[$row['key']] = $row['value'];
if ($row['mapto'] && !defined($row['mapto'])) define($row['mapto'], $row['value']);
}
}
public static function var_dump_cache() {
var_dump(ConfigHandler::$cacheFromDB);
}
}

ConfigHandler::Singleton();
$core_settings = ConfigHandler::LoadConfigFile("configuration");
if (!$core_settings) {
if(EXEC_MODE == 'WEB'){
$newURL = 'install/';
header("Location:" . $newURL);
die("If your browser does not refresh, please <a href=\"{$newURL}\">Click Here</a>");
}
else{
die('Please install core plus through the web interface first!' . "\n");
}
}
if (!DEVELOPMENT_MODE) {
ini_set('display_errors', 0);
ini_set('html_errors', 0);
}
if (EXEC_MODE == 'CLI') {
$servername          = null;
$servernameSSL       = null;
$servernameNOSSL     = null;
$rooturl             = null;
$rooturlNOSSL        = null;
$rooturlSSL          = null;
$curcall             = null;
$relativerequestpath = null;
$ssl                 = false;
$tmpdir              = $core_settings['tmp_dir_cli'];
$host                = 'localhost';
if (isset($_SERVER['HOME']) && is_dir($_SERVER['HOME'] . '/.gnupg')) $gnupgdir = $_SERVER['HOME'] . '/.gnupg/';
else $gnupgdir = false;
}
else {
if (isset ($_SERVER ['HTTPS'])) $servername = "https://";
else $servername = "http://";
if ($core_settings['site_url'] != '') $servername .= $core_settings['site_url'];
else $servername .= $_SERVER ['HTTP_HOST'];
if ($core_settings['site_url'] != '' && $_SERVER['HTTP_HOST'] != $core_settings['site_url']) {
$newURL = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $core_settings['site_url'] . $_SERVER['REQUEST_URI'];
header("Location:" . $newURL);
die("If your browser does not refresh, please <a href=\"{$newURL}\">Click Here</a>");
}
$host = $_SERVER['HTTP_HOST'];
$servernameNOSSL = str_replace('https://', 'http://', $servername);
if (preg_match('/\:\d+$/', substr($servernameNOSSL, -6))) {
$servernameNOSSL = preg_replace('/\:\d+$/', ':' . PORT_NUMBER, $servernameNOSSL);
}
else {
$servernameNOSSL .= ':' . PORT_NUMBER;
}
if (PORT_NUMBER == 80) {
$servernameNOSSL = str_replace(':80', '', $servernameNOSSL);
}
if (ENABLE_SSL) {
$servernameSSL = str_replace('http://', 'https://', SERVERNAME);
if (preg_match('/\:\d+$/', substr($servernameSSL, -6))) {
$servernameSSL = preg_replace('/\:\d+$/', ':' . PORT_NUMBER_SSL, $servernameSSL);
}
else {
$servernameSSL .= ':' . PORT_NUMBER_SSL;
}
if (PORT_NUMBER_SSL == 443) {
$servernameSSL = str_replace(':443', '', $servernameSSL);
}
}
else {
$servernameSSL = $servernameNOSSL;
}
$rooturl             = $servername . ROOT_WDIR;
$rooturlNOSSL        = $servernameNOSSL . ROOT_WDIR;
$rooturlSSL          = $servername . ROOT_WDIR;
$curcall             = $servername . $_SERVER['REQUEST_URI'];
$relativerequestpath = strtolower('/' . substr($_SERVER['REQUEST_URI'], strlen(ROOT_WDIR)));
if (strpos($relativerequestpath, '?') !== false) $relativerequestpath = substr($relativerequestpath, 0, strpos($relativerequestpath, '?'));
$ssl = (isset($_SERVER['HTTPS']));
$tmpdir = $core_settings['tmp_dir_web'];
$gnupgdir = false;
}
define('SERVERNAME', $servername);
define('SERVERNAME_NOSSL', $servernameNOSSL);
define('SERVERNAME_SSL', $servernameSSL);
define('ROOT_URL', $rooturl);
define('ROOT_URL_NOSSL', $rooturlNOSSL);
define('ROOT_URL_SSL', $rooturlSSL);
define('CUR_CALL', $curcall);
define('REL_REQUEST_PATH', $relativerequestpath);
define('SSL', $ssl);
define('TMP_DIR', $tmpdir);
define('TMP_DIR_WEB', $core_settings['tmp_dir_web']);
define('TMP_DIR_CLI', $core_settings['tmp_dir_cli']);
define('HOST', $host);
if (!is_dir(TMP_DIR)) {
mkdir(TMP_DIR, 0777, true);
}
if (!defined('GPG_HOMEDIR')) {
define('GPG_HOMEDIR', ($gnupgdir) ? $gnupgdir : ROOT_PDIR . 'gnupg');
}
unset($servername, $servernameNOSSL, $servernameSSL, $rooturl, $rooturlNOSSL, $rooturlSSL, $curcall, $ssl);
$maindefines_time = microtime(true);
Core::AddProfileTime('application_start', $start_time);
Core::AddProfileTime('predefines_complete', $predefines_time);
Core::AddProfileTime('preincludes_complete', $preincludes_time);
Core::AddProfileTime('maindefines_complete', $maindefines_time);
### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/datamodel/DMI.class.php
define('__DMI_PDIR', ROOT_PDIR . 'core/libs/datamodel/');
### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/datamodel/DMI_Backend.interface.php
interface DMI_Backend {
public function connect($host, $user, $pass, $database);
public function execute(Dataset $dataset);
public function tableExists($tablename);
public function createTable($tablename, $schema);
public function readCount();
public function writeCount();
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/datamodel/Dataset.class.php
class Dataset implements Iterator{
const MODE_GET = 'get';
const MODE_INSERT = 'insert';
const MODE_UPDATE = 'update';
const MODE_INSERTUPDATE = 'insertupdate';
const MODE_DELETE = 'delete';
const MODE_COUNT = 'count';
public $_table;
public $_selects = array();
public $_where = array();
public $_wheregroups = array('AND');
public $_mode = Dataset::MODE_GET;
public $_sets = array();
public $_idcol = null;
public $_idval = null;
public $_limit = false;
public $_order = false;
public $_data = null;
public $num_rows = null;
public function __construct(){
}
public function select(){
$n = func_num_args();
if($n == 0) throw new DMI_Exception ('Invalid amount of parameters requested for Dataset::set()');
if($n == 1 && func_get_arg(0) === null){
$this->_selects = array();
return $this;
}
$this->_mode = Dataset::MODE_GET;
$args = func_get_args();
foreach($args as $a){
if(is_array($a)){
$this->_selects = array_merge($this->_selects, $a);
}
elseif(strpos($a, ',') !== false){
$parts = explode(',', $a);
foreach($parts as $p){
$this->_selects[] = trim($p);
}
}
else{
$this->_selects[] = $a;
}
}
$this->_selects = array_unique($this->_selects);
return $this;
}
public function insert(){
call_user_func_array(array($this, '_set'), func_get_args());
$this->_mode = Dataset::MODE_INSERT;
return $this;
}
public function update(){
call_user_func_array(array($this, '_set'), func_get_args());
$this->_mode = Dataset::MODE_UPDATE;
return $this;
}
public function set(){
call_user_func_array(array($this, '_set'), func_get_args());
$this->_mode = Dataset::MODE_INSERTUPDATE;
return $this;
}
public function delete(){
$this->_mode = Dataset::MODE_DELETE;
return $this;
}
public function count(){
$this->_mode = Dataset::MODE_COUNT;
return $this;
}
private function _set(){
$n = func_num_args();
if($n == 0 || $n > 2){
throw new DMI_Exception ('Invalid amount of parameters requested for Dataset::set(), ' . $n . ' provided, exactly 1 or 2 expected');
}
elseif($n == 1){
$a = func_get_arg(0);
if(!is_array($a)) throw new DMI_Exception ('Invalid parameter sent for Dataset::set()');
foreach($a as $k => $v){
$this->_sets[$k] = $v;
}
}
else{
$k = func_get_arg(0);
$v = func_get_arg(1);
$this->_sets[$k] = $v;
}
}
public function setID($key, $val = null){
$this->_idcol = $key;
$this->_idval = $val;
if($val) $this->where("$key = $val");
}
public function getID(){
return $this->_idval;
}
public function table($tablename){
if(DB_PREFIX && strpos($tablename, DB_PREFIX) === false) $tablename = DB_PREFIX . $tablename;
$this->_table = $tablename;
return $this;
}
public function where(){
$args = func_get_args();
if(sizeof($args) == 2 && !is_array($args[0]) && !is_array($args[1])){
$this->_parseWhere($args[0] . ' = ' . $args[1]);
return $this;
}
foreach($args as $a){
if(is_array($a)){
foreach($a as $k => $v){
if(is_numeric($k)) $this->_parseWhere($v);
else $this->_where[] = array('field' => $k, 'op' => '=', 'value' => $v, 'group' => 0);
}
}
else{
$this->_parseWhere($a);
}
}
return $this;
}
public function whereGroup($separator, $wheres){
$args = func_get_args();
$sep = array_shift($args);
$group = sizeof($this->_wheregroups);
$this->_wheregroups[] = $sep;
foreach($args as $a){
if(is_array($a)){
foreach($a as $k => $v){
if(is_numeric($k)) $this->_parseWhere($v, $group);
else $this->_where[] = array('field' => $k, 'op' => '=', 'value' => $v, 'group' => $group);
}
}
else{
$this->_parseWhere($a, $group);
}
}
return $this;
}
public function limit(){
$n = func_num_args();
if($n == 1) $this->_limit = func_get_arg(0);
elseif($n == 2) $this->_limit = func_get_arg(0) . ', ' . func_get_arg(1);
else throw new DMI_Exception('Invalid amount of parameters requested for Dataset::limit()');
return $this;
}
public function order(){
$n = func_num_args();
if($n == 1) $this->_order = func_get_arg(0);
elseif($n == 2) $this->_order = func_get_arg(0) . ', ' . func_get_arg(1);
else throw new DMI_Exception('Invalid amount of parameters requested for Dataset::order()');
return $this;
}
public function execute($interface = null){
if(!$interface) $interface = DMI::GetSystemDMI();
$interface->connection()->execute($this);
if($this->_data !== null) reset($this->_data);
return $this;
}
function rewind() {
if($this->_data !== null) reset($this->_data);
}
function current() {
if($this->_data === null) $this->execute();
return $this->_data[key($this->_data)];
}
function key() {
if($this->_data === null) $this->execute();
return key($this->_data);
}
function next() {
if($this->_data === null) $this->execute();
next($this->_data);
}
function valid() {
if($this->_data === null) $this->execute();
return isset($this->_data[key($this->_data)]);
}
private function _parseWhere($statement, $group = 0){
$valid = false;
$operations = array('!=', '<=', '>=', '=', '>', '<', 'LIKE ');
$k = preg_replace('/^([^ !=<>]*).*/', '$1', $statement);
$statement = trim(substr($statement, strlen($k)));
foreach($operations as $c){
if(($pos = strpos($statement, $c)) === 0){
$op = $c;
$statement = trim(substr($statement, strlen($op)));
$valid = true;
break;
}
}
if($valid){
$this->_where[] = array('field' => $k, 'op' => $op, 'value' => $statement, 'group' => $group);
}
}
public static function Init(){
return new self();
}
}

class DMI {
protected $_backend = null;
static protected $_Interface = null;
public function __construct($backend = null, $host = null, $user = null, $pass = null, $database = null){
if($backend) $this->setBackend($backend);
if($host) $this->connect($host, $user, $pass, $database);
}
public function setBackend($backend){
if($this->_backend) throw new DMI_Exception('Backend already set');
$class = 'DMI_' . $backend . '_backend';
$classfile = strtolower($backend);
if(!file_exists(__DMI_PDIR . 'backends/' . strtolower($classfile) . '.backend.php')){
throw new DMI_Exception('Could not locate backend file for ' . $class);
}
require_once(__DMI_PDIR . 'backends/' . strtolower($classfile) . '.backend.php');
$this->_backend = new $class();
}
public function connect($host, $user, $pass, $database){
$this->_backend->connect($host, $user, $pass, $database);
return $this->_backend;
}
public function connection(){
return $this->_backend;
}
public static function GetSystemDMI(){
if(self::$_Interface !== null) return self::$_Interface;
self::$_Interface = new DMI();
$cs = ConfigHandler::LoadConfigFile("configuration");
self::$_Interface->setBackend($cs['database_type']);
self::$_Interface->connect($cs['database_server'], $cs['database_user'], $cs['database_pass'], $cs['database_name']);
return self::$_Interface;
}
}
class DMI_Exception extends Exception{
const ERRNO_NODATASET = '42S02';
const ERRNO_UNKNOWN = '07000';
public $ansicode;
public function __construct($message, $code = null, $previous = null, $ansicode = null) {
parent::__construct($message, $code, $previous);
if($ansicode) $this->ansicode = $ansicode;
elseif($code) $this->ansicode = $code;
}
}
class DMI_Authentication_Exception extends DMI_Exception{
}
class DMI_ServerNotFound_Exception extends DMI_Exception{
}
class DMI_Query_Exception extends DMI_Exception{
public $query = null;
}

try {
$dbconn = DMI::GetSystemDMI();
HookHandler::DispatchHook('db_ready');
}
catch (Exception $e) {
error_log($e->getMessage());
if (DEVELOPMENT_MODE) {
header('Location: ' . ROOT_WDIR . 'install');
die();
}
else {
require(ROOT_PDIR . 'core/templates/halt_pages/fatal_error.inc.html');
die();
}
}
unset($start_time, $predefines_time, $preincludes_time, $maindefines_time);
Core::LoadComponents();
if (EXEC_MODE == 'WEB') {
try {
### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/core/Session.class.php
register_shutdown_function("session_write_close");
class Session implements ISingleton {
private $_model = null;
private static $_Instance = null;
public static function Singleton() {
if (self::$_Instance === null) {
self::$_Instance = new Session();
ini_set('session.hash_bits_per_character', 5);
ini_set('session.hash_function', 1);
session_start();
$m = self::$_Instance->_getModel();
$m->set('updated', Time::GetCurrentGMT());
HookHandler::DispatchHook('session_ready');
}
return Session::$_Instance;
}
public static function GetInstance() {
return Session::Singleton();
}
public static function Start($save_path, $session_name) {
self::Singleton();
}
public static function End() {
}
public static function Read($id) {
return self::Singleton()->_getModel()->get('data');
}
public static function Write($id, $data) {
$m = self::Singleton()->_getModel();
$m->set('data', $data);
$m->save();
return TRUE;
}
public static function Destroy($id = null) {
if ($id === null) {
self::Singleton()->_destroy();
session_destroy();
session_regenerate_id(true);
} else {
$dataset = new Dataset();
$dataset->table('session');
$dataset->where('session_id = ' . $id);
$dataset->delete();
$dataset->execute();
}
return TRUE;
}
public static function GC() {
$ttl = ConfigHandler::Get('/core/session/ttl');
$dataset = new Dataset();
$dataset->table('session');
$dataset->where('updated < ' . (Time::GetCurrentGMT() - $ttl));
$dataset->delete();
return true;
}
public static function SetUser(User $u) {
$m = self::Singleton()->_getModel();
$m->set('user_id', $u->get('id'));
$_SESSION['user'] = $u;
}
private function __construct() {
session_set_save_handler(
array('Session', "Start"),
array('Session', "End"),
array('Session', "Read"),
array('Session', "Write"),
array('Session', "Destroy"),
array('Session', "GC")
);
Session::GC();
}
private function _getModel() {
if ($this->_model === null) {
$this->_model = new SessionModel(session_id());
$this->_model->set('ip_addr', REMOTE_IP);
}
return $this->_model;
}
private function _destroy() {
if ($this->_model) {
$this->_model->delete();
$this->_model = null;
}
}
}

Session::Singleton();
}
catch (DMI_Exception $e) {
if (DEVELOPMENT_MODE) {
header('Location: ' . ROOT_WDIR . 'install');
die();
}
else {
require(ROOT_PDIR . 'core/libs/fatal_errors/database.php');
die();
}
}
}
HookHandler::DispatchHook('components_loaded');
HookHandler::DispatchHook('components_ready');
Core::AddProfileTime('components_load_complete');
