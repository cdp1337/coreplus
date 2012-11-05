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
 * @compiled Mon, 05 Nov 2012 12:40:06 -0500
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
if(strpos($path,  '/' . $this->getRootDOM()->tagName) === 0){
$path = '/' . $path;
}
else{
$path = '//' . $this->getRootDOM()->tagName . $path;
}
}
return $path;
}
public function createElement($path, $el = false, $forcecreate = 0) {
if (!$el){
$el = $this->getRootDOM();
$path = $this->_translatePath($path);
if(strpos($path, '//' . $this->getRootDOM()->nodeName) === 0){
$path = substr($path, strlen($this->getRootDOM()->nodeName) + 3);
}
}
else{
$path = $this->_translatePath($path);
if($el == $this->getRootDOM()){
if(strpos($path, '//' . $this->getRootDOM()->nodeName) === 0){
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
foreach ($patharray as $k => $s) {
if ($s == '') continue;
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
const ATT_TYPE_ISO_8601_DATETIME = 'ISO_8601_datetime';
const ATT_TYPE_MYSQL_TIMESTAMP = 'mysql_timestamp';
const ATT_TYPE_ISO_8601_DATE = 'ISO_8601_date';
const VALIDATION_NOTBLANK = "/^.+$/";
const VALIDATION_EMAIL = 'Core::CheckEmailValidity';
const VALIDATION_URL = '#^[a-zA-Z]+://.+$#';
const VALIDATION_URL_WEB = '#^[hH][tT][tT][pP][sS]{0,1}://.+$#';
const LINK_HASONE  = 'one';
const LINK_HASMANY = 'many';
const LINK_BELONGSTOONE = 'belongs_one';
const LINK_BELONGSTOMANY = 'belongs_many';
public $interface = null;
protected $_data = array();
protected $_datainit = array();
protected $_datadecrypted = null;
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
$this->_datainit = $this->_data;
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
if (!isset($v['type']))      $this->_schemacache[$k]['type']      = Model::ATT_TYPE_TEXT; // Default if not present.
if (!isset($v['maxlength'])) $this->_schemacache[$k]['maxlength'] = false;
if (!isset($v['null']))      $this->_schemacache[$k]['null']      = false;
if (!isset($v['comment']))   $this->_schemacache[$k]['comment']   = false;
if (!isset($v['default']))   $this->_schemacache[$k]['default']   = false;
if (!isset($v['encrypted'])) $this->_schemacache[$k]['encrypted'] = false;
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
$n = $this->_getTableName();
if (!isset($i['primary'])) $i['primary'] = array(); // No primary schema defined... just don't make the in_array bail out.
$dat = new Dataset();
$dat->table($n);
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
$n = $this->_getTableName();
if (!isset($i['primary'])) $i['primary'] = array();
$dat = new Dataset();
$dat->table($n);
$idcol = false;
foreach ($this->_data as $k => $v) {
if(!isset($s[$k])){
continue;
}
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
if (isset($this->_datainit[$k]) && $this->_datainit[$k] == $v) continue; // Skip non-changed columns
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
$n = $this->_getTableName();
$i = self::GetIndexes();
$dat = new Dataset();
$dat->table($n);
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
$keydat = $this->getKeySchema($k);
if($keydat['encrypted']){
$this->decryptData();
$this->_datadecrypted[$k] = $v;
$this->_data[$k] = $this->encryptValue($v);
}
else{
$this->_data[$k] = $v;
}
$this->_dirty    = true;
return true;
}
else {
$this->_dataother[$k] = $v;
return true;
}
}
protected function _setLinkKeyPropagation($key, $newval) {
$exists = $this->exists();
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
if($exists){
$links = $this->getLink($lk);
if (!is_array($links)) $links = array($links);
foreach ($links as $model) {
$model->set($key, $newval);
}
}
else{
if(!isset($this->_linked[$lk]['records'])) continue;
foreach($this->_linked[$lk]['records'] as $model){
$model->set($key, $newval);
}
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
public function setLink($linkname, Model $model) {
if (!isset($this->_linked[$linkname])) return; // @todo Error Handling
switch($this->_linked[$linkname]['link']){
case Model::LINK_HASONE:
case Model::LINK_BELONGSTOONE:
$this->_linked[$linkname]['records'] = $model;
break;
case Model::LINK_HASMANY:
case Model::LINK_BELONGSTOMANY:
if(!isset($this->_linked[$linkname]['records'])) $this->_linked[$linkname]['records'] = array();
$this->_linked[$linkname]['records'][] = $model;
break;
}
}
public function setFromArray($array) {
foreach ($array as $k => $v) {
$this->set($k, $v);
}
}
public function setFromForm(Form $form, $prefix = null){
$els = $form->getElements(true, false);
foreach ($els as $e) {
if ($prefix){
if(!preg_match('/^' . $prefix . '\[(.*?)\].*/', $e->get('name'), $matches)) continue;
$key = $matches[1];
}
else{
$key = $e->get('name');
}
$val    = $e->get('value');
$schema = $this->getKeySchema($key);
if(!$schema) continue;
if ($schema['type'] == Model::ATT_TYPE_BOOL) {
if (strtolower($val) == 'yes') $val = 1;
elseif (strtolower($val) == 'on') $val = 1;
elseif ($val == 1) $val = 1;
else $val = 0;
}
$this->set($key, $val);
}
}
public function get($k) {
if($this->_datadecrypted !== null && array_key_exists($k, $this->_datadecrypted)){
return $this->_datadecrypted[$k];
}
elseif (array_key_exists($k, $this->_data)) {
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
if($this->_datadecrypted !== null){
return array_merge($this->_data, $this->_dataother, $this->_datadecrypted);
}
else{
return array_merge($this->_data, $this->_dataother);
}
}
public function exists() {
return $this->_exists;
}
public function isnew() {
return !$this->_exists;
}
public function decryptData(){
if($this->_datadecrypted === null){
$this->_datadecrypted = array();
foreach($this->getKeySchemas() as $k => $v){
if($v['encrypted']){
$payload = $this->_data[$k];
if($payload === null || $payload === ''){
$this->_datadecrypted[$k] = null;
continue;
}
preg_match('/^\$([^$]*)\$([0-9]*)\$(.*)$/m', $payload, $matches);
$cipher = $matches[1];
$passes = $matches[2];
$size = openssl_cipher_iv_length($cipher);
$dec = substr($payload, strlen($cipher) + 5, 0-$size);
$iv = substr($payload, 0-$size);
for($i=0; $i<$passes; $i++){
$dec = openssl_decrypt($dec, $cipher, SECRET_ENCRYPTION_PASSPHRASE, true, $iv);
}
$this->_datadecrypted[$k] = $dec;
}
}
}
}
public function _getTableName(){
return self::GetTableName();
}
protected function encryptValue($value){
$cipher = 'AES-256-CBC';
$passes = 10;
$size = openssl_cipher_iv_length($cipher);
$iv = mcrypt_create_iv($size, MCRYPT_RAND);
$enc = $value;
for($i=0; $i<$passes; $i++){
$enc = openssl_encrypt($enc, $cipher, SECRET_ENCRYPTION_PASSPHRASE, true, $iv);
}
$payload = '$' . $cipher . '$' . str_pad($passes, 2, '0', STR_PAD_LEFT) . '$' . $enc . $iv;
return $payload;
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
'parenturl' => array(
'type' => Model::ATT_TYPE_STRING,
'maxlength' => 128,
'null' => true,
'formtype' => 'pageparentselect',
'formtitle' => 'Parent Page'
),
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
'title' => 'Page URL',
'type' => 'pagerewriteurl',
'description' => 'Starts with a "/", omit the root web dir.',
),
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
if(strpos($v, '#') !== false){
return 'Invalid Rewrite URL, cannot contain a pound sign (#).';
}
$controller = substr($v, 1, ( (strpos($v, '/', 1) !== false) ? strpos($v, '/', 1) : strlen($v)) );
if($controller && class_exists($controller . 'Controller')){
return 'Invalid Rewrite URL, "' . $controller . '" is a reserved system name!';
}
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
public function getBaseTemplateName(){
$t = 'pages/';
$c = $this->getControllerClass();
if (strlen($c) - strrpos($c, 'Controller') == 10) {
$c = substr($c, 0, -10);
}
$t .= $c . '/';
$t .= $this->getControllerMethod() . '.tpl';
return strtolower($t);
}
public function getTemplateName() {
$t = $this->getBaseTemplateName();
if (($override = $this->get('page_template'))){
$t = substr($t, 0, -4) . '/' . $override;
}
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
public function setFromForm(Form $form, $prefix = null){
parent::setFromForm($form, $prefix);
$meta = $form->getElementByName($prefix . '_meta');
$this->set('metas', $meta->get('value'));
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
$p = false;
if (!$this->exists()) {
self::_LookupUrl('/');
$url = strtolower($this->get('baseurl'));
do {
$url = substr($url, 0, strrpos($url, '/'));
if (isset(self::$_RewriteCache[$url])) {
$url = self::$_RewriteCache[$url];
}
$p = PageModel::Construct($url);
return array_merge($p->_getParentTree(--$antiinfiniteloopcounter), array($p));
}
while ($url);
}
if (!$this->get('parenturl') && $this->get('admin') && strtolower($this->get('baseurl')) != '/admin') {
$url = '/admin';
if (isset(self::$_RewriteCache[$url])) {
$p = PageModel::Construct($url);
}
return $p ? array($p) : array();
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
$baseurl = strtolower($p->get('baseurl'));
$t = '';
foreach ($p->getParentTree() as $subp) {
$t .= $subp->get('title') . ' &raquo; ';
}
$t .= $p->get('title');
$t .= ' ( ' . $p->get('rewriteurl') . ' )';
$opts[$baseurl] = $t;
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
protected $_enabled = false;
protected $_description;
protected $_updateSites = array();
protected $_authors = array();
protected $_iterator;
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
$this->_xmlloader->removeElements('//component/licenses');
$path = '//component/licenses/';
foreach ($licenses as $lic) {
$el = 'license' . ((isset($lic['url']) && $lic['url']) ? '[@url="' . $lic['url'] . '"]' : '');
$l  = $this->_xmlloader->createElement($path . $el, false, 1);
if ($lic['title']) $l->nodeValue = $lic['title'];
}
}
public function loadFiles() {
if(!$this->isInstalled()) return false;
if(!$this->isEnabled()) return false;
$dir = $this->getBaseDir();
foreach ($this->_xmlloader->getElements('/includes/include') as $f) {
require_once($dir . $f->getAttribute('filename'));
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
$dir = $this->getBaseDir();
if($this->_classlist === null){
$this->_classlist = array();
foreach ($this->_xmlloader->getElements('/files/file') as $f) {
$filename = $dir . $f->getAttribute('filename');
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
$dir = $this->getBaseDir();
if($this->_widgetlist === null){
$this->_widgetlist = array();
foreach ($this->_xmlloader->getElements('/files/file') as $f) {
$filename = $dir . $f->getAttribute('filename');
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
$dir = $this->getBaseDir();
if ($this->hasView()) {
foreach ($this->_xmlloader->getElementByTagName('view')->getElementsByTagName('tpl') as $t) {
$filename     = $dir . $t->getAttribute('filename');
$name         = $t->getAttribute('name');
$views[$name] = $filename;
}
}
return $views;
}
public function getControllerList() {
$classes = array();
$dir = $this->getBaseDir();
foreach ($this->_xmlloader->getElements('/files/file') as $f) {
$filename = $dir . $f->getAttribute('filename');
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
return ($this->_enabled === true);
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
$this->_enabled = ($c->get('enabled') == '1');
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
Core::Cache()->delete('core-components');
return true;
}
public function enable(){
if($this->isEnabled()) return false;
$c = new ComponentModel($this->_name);
$c->set('enabled', true);
$c->save();
$this->_enabled = true;
Core::Cache()->delete('core-components');
return true;
}
private function _performInstall() {
$changed = array();
$change = $this->_parseDBSchema();
if ($change !== false) $changed = array_merge($changed, $change);
$change = $this->_parseConfigs();
if ($change !== false) $changed = array_merge($changed, $change);
$change = $this->_parseUserConfigs();
if ($change !== false) $changed = array_merge($changed, $change);
$change = $this->_parsePages();
if ($change !== false) $changed = array_merge($changed, $change);
$change = $this->_parseWidgets();
if ($change !== false) $changed = array_merge($changed, $change);
$change = $this->_installAssets();
if ($change !== false) $changed = array_merge($changed, $change);
foreach($this->_xmlloader->getElements('install/dataset') as $datasetel){
$datachanges = $this->_parseDatasetNode($datasetel);
if($datachanges !== false) $changed = array_merge($changed, $datachanges);
}
Core::Cache()->delete('core-components');
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
private function _parseUserConfigs() {
$changes = array();
$node = $this->_xmlloader->getElement('userconfigs');
foreach ($node->getElementsByTagName('userconfig') as $confignode) {
$key        = $confignode->getAttribute('key');
$name       = $confignode->getAttribute('name');
$default    = $confignode->getAttribute('default');
$formtype   = $confignode->getAttribute('formtype');
$onreg      = $confignode->getAttribute('onregistration');
$onedit     = $confignode->getAttribute('onedit');
$options    = $confignode->getAttribute('options');
$validation = $confignode->getAttribute('validation');
if($onreg === null) $onreg = 1;
if($onedit === null) $onedit = 1;
$model = UserConfigModel::Construct($key);
$model->set('name', $name);
if($default)  $model->set('default_value', $default);
if($formtype) $model->set('formtype', $formtype);
$model->set('onregistration', $onreg);
$model->set('onedit', $onedit);
if($options)  $model->set('options', $options);
$model->set('validation', $validation);
if($model->save()) $changes[] = 'Set user config [' . $model->get('key') . '] as a [' . $model->get('formtype') . ' input]';
}
return (sizeof($changes)) ? $changes : false;
} // private function _parseUserConfigs
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
try{
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
catch(DMI_Query_Exception $e){
$e->query = $e->query . "\n<br/>(original table " . $tablename . ")";
echo '<pre>' . $e->getTraceAsString() . '</pre>';
throw $e;
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
public function getPreviewURL($dimensions = "300x300");
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
public function rename($newname);
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
case 'text/xml':
return new File_xml_contents($file);
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
public function getPreviewURL($dimensions = "300x300"){
return $this->getURL();
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
public function getDirectoryName(){
return dirname($this->_filename) . '/';
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
$ftp    = \Core\FTP();
if(!$ftp){
if (!@unlink($this->getFilename())) return false;
$this->_filename = null;
return true;
}
else{
if(!ftp_delete($ftp, $this->getFilename())) return false;
$this->_filename = null;
return true;
}
}
public function rename($newname){
if($newname{0} != '/'){
$newname = substr($this->getFilename(), 0, 0 - strlen($this->getBaseFilename())) . $newname;
}
if(self::_Rename($this->getFilename(), $newname)){
$this->_filename = $newname;
return true;
}
return false;
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
$prefix = $dir . '/' . $base;
$suffix = (($ext == '') ? '' : '.' . $ext);
$thathash = $src->getHash();
$f = $prefix . $suffix;
while(file_exists($f) && md5_file($f) != $thathash){
$f = $prefix . ' (' . ++$c . ')' . $suffix;
}
$this->_filename = $f;
}
return $this->putContents($src->getContents());
}
public function getContents() {
return file_get_contents($this->_filename);
}
public function putContents($data) {
self::_Mkdir(dirname($this->_filename), null, true);
if(!is_dir(dirname($this->_filename))){
throw new Exception("Unable to make directory " . dirname($this->_filename) . ", please check permissions.");
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
$mode = '';
}
elseif ($dimensions === null) {
$width  = 300;
$height = 300;
$mode = '';
}
elseif($dimensions === false){
$width = false;
$height = false;
$mode = '';
}
else {
if(strpos($dimensions, '^') !== false){
$mode = '^';
$dimensions = str_replace('^', '', $dimensions);
}
elseif(strpos($dimensions, '!') !== false){
$mode = '!';
$dimensions = str_replace('!', '', $dimensions);
}
elseif(strpos($dimensions, '>') !== false){
$mode = '>';
$dimensions = str_replace('>', '', $dimensions);
}
elseif(strpos($dimensions, '<') !== false){
$mode = '<';
$dimensions = str_replace('<', '', $dimensions);
}
else{
$mode = '';
}
$vals   = explode('x', strtolower($dimensions));
$width  = (int)$vals[0];
$height = (int)$vals[1];
}
$key = str_replace(' ', '-', $this->getBaseFilename(true)) . $this->getHash() . '-' . $width . 'x' . $height . $mode . '.png';
if (!$this->exists()) {
error_log('File not found [ ' . $this->_filename . ' ]', E_USER_NOTICE);
$file = Core::File('assets/mimetype_icons/notfound.png');
if($width === false) return $file->getURL();
elseif($file->exists()) return $file->getPreviewURL($dimensions);
else return null;
}
elseif ($this->isPreviewable()) {
if($width === false) return $this->getURL();
$file = Core::File('public/tmp/' . $key);
if (!$file->exists()) {
$img2 = $this->_getResizedImage($width, $height, $mode);
imagepng($img2, TMP_DIR . $key);
$file->putContents(file_get_contents(TMP_DIR . $key));
}
return $file->getURL();
}
else {
$filemime = str_replace('/', '-', $this->getMimetype());
$file = Core::File('assets/mimetype_icons/' . $filemime . '.png');
if(!$file->exists()){
$file = Core::File('assets/mimetype_icons/unknown.png');
}
if($width === false) return $file->getURL();
else return $file->getPreviewURL($dimensions);
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
if(is_dir($pathname)) return false;
else return mkdir($pathname, $mode, $recursive);
}
elseif (strpos($pathname, $tmpdir) === 0) {
if(is_dir($pathname)) return false;
else return mkdir($pathname, $mode, $recursive);
}
else {
if (strpos($pathname, ROOT_PDIR) === 0) $pathname = substr($pathname, strlen(ROOT_PDIR));
$paths = explode('/', $pathname);
foreach ($paths as $p) {
if(trim($p) == '') continue;
if (!@ftp_chdir($ftp, $p)) {
if (!ftp_mkdir($ftp, $p)) return false;
if (!ftp_chmod($ftp, $mode, $p)) return false;
ftp_chdir($ftp, $p);
}
}
return true;
}
}
public static function _Rename($oldpath, $newpath){
$ftp    = \Core\FTP();
if(!$ftp){
return rename($oldpath, $newpath);
}
else{
if (strpos($oldpath, ROOT_PDIR) === 0) $oldpath = substr($oldpath, strlen(ROOT_PDIR));
if (strpos($newpath, ROOT_PDIR) === 0) $newpath = substr($newpath, strlen(ROOT_PDIR));
return ftp_rename($ftp, $oldpath, $newpath);
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
private function _getResizedImage($width, $height, $mode = '') {
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
switch($mode){
case '':
case '<':
if ($nW > $width) {
$nH = $width * $sH / $sW;
$nW = $width;
}
if ($nH > $height) {
$nW = $height * $sW / $sH;
$nH = $height;
}
break;
case '>':
if ($nW < $width) {
$nH = $width * $sH / $sW;
$nW = $width;
}
if ($nH < $height) {
$nW = $height * $sW / $sH;
$nH = $height;
}
break;
case '!':
$nW = $width;
$nH = $height;
break;
case '^':
if(($width * $sH / $sW) > ($height * $sW / $sH)){
$nH = $width * $sH / $sW;
$nW = $width;
}
else{
$nH = $height;
$nW = $height * $sW / $sH;
}
break;
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
public function rename($newname) {
if($newname{0} != '/'){
$newname = substr($this->getPath(), 0, -1 - strlen($this->getBasename())) . $newname;
}
if(File_local_backend::_Rename($this->getPath(), $newname)){
$this->_path = $newname;
$this->_files = null;
return true;
}
else{
return false;
}
}
public function getPath() {
return $this->_path;
}
public function getBasename() {
$p = trim($this->_path, '/');
return substr($p, strrpos($p, '/') + 1);
}
public function remove() {
$ftp    = \Core\FTP();
if(!$ftp){
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
return true;
}
else{
foreach($this->ls() as $sub){
if($sub instanceof File_local_backend) $sub->delete();
else $sub->remove();
}
$path = $this->getPath();
if (strpos($path, ROOT_PDIR) === 0) $path = substr($path, strlen(ROOT_PDIR));
return ftp_rmdir($ftp, $path);
}
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
require_once(__CACHE_PDIR . 'backends/cachecore.class.php'); #SKIPCOMPILER
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
public function getBindingCount(){
return sizeof($this->_bindings);
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
$tempcomponents    = array();
if(DEVELOPMENT_MODE){
$enablecache = false;
}
else{
$enablecache = true;
}
if(!$enablecache || ($cachedcomponents = Cache::GetSystemCache()->get('core-components', (3600 * 24))) === false){
$tempcomponents['core'] = ComponentFactory::Load(ROOT_PDIR . 'core/component.xml');
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
$tempcomponents[$file] = $c;
unset($c);
}
closedir($dh);
foreach ($tempcomponents as $c) {
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
Cache::GetSystemCache()->set('core-components', $tempcomponents);
}
}
else{
$tempcomponents = $cachedcomponents;
}
$list = $tempcomponents;
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
$this->_components[$n] = $c;
$this->_registerComponent($c);
unset($list[$n]);
continue;
}
if ($c->isInstalled() && $c->needsUpdated() && $c->isLoadable()) {
$c->upgrade();
$c->loadFiles();
$this->_components[$n] = $c;
$this->_registerComponent($c);
unset($list[$n]);
continue;
}
if (!$c->isInstalled() && $c->isLoadable()) {
$c->install();
if(!DEVELOPMENT_MODE){
$c->disable();
}
else{
$c->enable();
$c->loadFiles();
$this->_components[$n] = $c;
$this->_registerComponent($c);
}
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
static public function GoBack($depth=2) {
if(!isset($_SESSION['nav'])){
Core::Redirect(ROOT_WDIR);
}
$s = sizeof($_SESSION['nav']);
if($depth > $s){
Core::Redirect(ROOT_WDIR);
}
if($depth <= 0){
Core::Redirect(ROOT_WDIR);
}
Core::Redirect($_SESSION['nav'][$s - $depth]['uri']);
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
static public function _RecordNavigation() {
$request = PageRequest::GetSystemRequest();
$view = $request->getView();
if(!$view->record) return;
if(!$request->isGet()) return;
if (!isset($_SESSION['nav'])) $_SESSION['nav'] = array();
$rel = substr($_SERVER['REQUEST_URI'], strlen(ROOT_WDIR));
if($rel === false) $rel = '';
$dat = array(
'uri' => ROOT_URL . $rel,
'title' => $view->title,
);
$s = sizeof($_SESSION['nav']);
if($s && $_SESSION['nav'][$s-1]['uri'] == $dat['uri']) return;
if($s >= 5){
array_shift($_SESSION['nav']);
$_SESSION['nav'] = array_values($_SESSION['nav']);
}
$_SESSION['nav'][] = $dat;
return;
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
ROOT_URL_NOSSL: "' . ROOT_URL_NOSSL . '",
SSL: ' . (SSL ? 'true' : 'false') . ',
SSL_MODE: "' . SSL_MODE . '",
User: {
id: ' . (\Core\user()->get('id') ? \Core\user()->get('id') : 0) . ',
authenticated: ' . (\Core\user()->exists() ? 'true' : 'false') . '
}
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
'NAME'          => array(
'type' => Model::ATT_TYPE_STRING,
'maxlength' => 50,
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
$sslmode             = 'disabled';
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
if(defined('ENABLE_SSL')){
if(ENABLE_SSL){
$sslmode = 'ondemand';
}
else{
$sslmode = 'disabled';
}
define('SSL_MODE', $sslmode);
}
elseif(defined('SSL_MODE')){
if(SSL_MODE == 'disabled') $enablessl = false;
else $enablessl = true;
define('ENABLE_SSL', $enablessl);
}
else{
define('SSL_MODE', 'disabled');
define('ENABLE_SSL', false);
}
if (ENABLE_SSL) {
$servernameSSL = str_replace('http://', 'https://', $servername);
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
$rooturlSSL          = $servernameSSL . ROOT_WDIR;
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
define('SSL_MODE_DISABLED', 'disabled');
define('SSL_MODE_ONDEMAND', 'ondemand');
define('SSL_MODE_ALLOWED',  'allowed');
define('SSL_MODE_REQUIRED', 'required');
define('TMP_DIR', $tmpdir);
define('TMP_DIR_WEB', $core_settings['tmp_dir_web']);
define('TMP_DIR_CLI', $core_settings['tmp_dir_cli']);
define('HOST', $host);
if (!is_dir(TMP_DIR)) {
mkdir(TMP_DIR, 0777, true);
}
if(SSL_MODE == SSL_MODE_REQUIRED && !SSL){
if(!DEVELOPMENT_MODE) header("HTTP/1.1 301 Moved Permanently");
header('Location: ' . ROOT_URL_SSL . substr(REL_REQUEST_PATH, 1));
die('This site requires SSL, if it does not redirect you automatically, please <a href="' . ROOT_URL_SSL . substr(REL_REQUEST_PATH, 1) . '">Click Here</a>.');
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
public $_where = null;
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
public function getWhereClause(){
if($this->_where === null){
$this->_where = new DatasetWhereClause('root');
}
return $this->_where;
}
public function where(){
$args = func_get_args();
if(sizeof($args) == 2 && is_string($args[0]) && is_string($args[1])){
$this->getWhereClause()->addWhere($args[0] . ' = ' . $args[1]);
}
else{
$this->getWhereClause()->addWhere($args);
}
return $this;
}
public function whereGroup($separator, $wheres){
$args = func_get_args();
$sep = array_shift($args);
$clause = new DatasetWhereClause();
$clause->setSeparator($sep);
$clause->addWhere($args);
$this->getWhereClause()->addWhere($clause);
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
public static function Init(){
return new self();
}
}
class DatasetWhereClause{
private $_separator = 'AND';
private $_statements = array();
private $_name;
public function __construct($name = '_unnamed_'){
$this->_name = $name;
}
public function addWhere($arguments){
if($arguments instanceof DatasetWhereClause){
$this->_statements[] = $arguments;
return true;
}
if(is_string($arguments)){
$this->_statements[] = new DatasetWhere($arguments);
return true;
}
foreach($arguments as $a){
if(is_array($a)){
foreach($a as $k => $v){
if(is_numeric($k)){
$this->_statements[] = new DatasetWhere($v);
}
else{
$this->_statements[] = new DatasetWhere($k . ' = ' . $v);
}
}
}
elseif($a instanceof DatasetWhereClause){
$this->_statements[] = $a;
}
elseif($a instanceof DatasetWhere){
$this->_statements[] = $a;
}
else{
$this->_statements[] = new DatasetWhere($a);
}
}
}
public function addWhereSub($sep, $arguments){
$subgroup = new DatasetWhereClause();
$subgroup->setSeparator($sep);
$subgroup->addWhere($arguments);
$this->addWhere($subgroup);
}
public function getStatements(){
return $this->_statements;
}
public function setSeparator($sep){
$sep = trim(strtoupper($sep));
switch($sep){
case 'AND':
case 'OR':
$this->_separator = $sep;
break;
default:
throw new DMI_Exception('Invalid separator, [' . $sep . ']');
}
}
public function getSeparator(){
return $this->_separator;
}
public function getAsArray(){
$children = array();
foreach($this->_statements as $s){
if($s instanceof DatasetWhereClause){
$children[] = $s->getAsArray();
}
elseif($s instanceof DatasetWhere){
$children[] = $s->field . ' ' . $s->op . ' ' . $s->value;
}
}
return array('sep' => $this->_separator, 'children' => $children);
}
}
class DatasetWhere{
public $field;
public $op;
public $value;
public function __construct($arguments){
$this->_parseWhere($arguments);
}
private function _parseWhere($statement){
$valid = false;
$operations = array('!=', '<=', '>=', '=', '>', '<', 'LIKE ', 'NOT LIKE');
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
$this->field = $k;
$this->op = $op;
$this->value = $statement;
}
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
$m = self::$_Instance->_getModel();
$m->set('updated', Time::GetCurrentGMT());
HookHandler::DispatchHook('session_ready');
}
return Session::$_Instance;
}
public static function GetInstance() {
return self::Singleton();
}
public static function Start($save_path, $session_name) {
self::Singleton();
}
public static function End() {
$m = self::Singleton()->_getModel();
$m->save();
}
public static function Read($id) {
$m = self::Singleton()->_getModel();
return $m->get('data');
}
public static function Write($id, $data) {
$m = self::Singleton()->_getModel();
$m->set('data', $data);
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
session_set_save_handler(
array('Session', "Start"),
array('Session', "End"),
array('Session', "Read"),
array('Session', "Write"),
array('Session', "Destroy"),
array('Session', "GC")
);
ini_set('session.hash_bits_per_character', 5);
ini_set('session.hash_function', 1);
session_start();

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
### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/bootstrap_postincludes.php
define('SMARTY_DIR', ROOT_PDIR . 'core/libs/smarty/');
### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/smarty/Smarty.class.php
if (!defined('DS')) {
define('DS', DIRECTORY_SEPARATOR);
}
if (!defined('SMARTY_DIR')) {
define('SMARTY_DIR', dirname(__FILE__) . DS);
}
if (!defined('SMARTY_SYSPLUGINS_DIR')) {
define('SMARTY_SYSPLUGINS_DIR', SMARTY_DIR . 'sysplugins' . DS);
}
if (!defined('SMARTY_PLUGINS_DIR')) {
define('SMARTY_PLUGINS_DIR', SMARTY_DIR . 'plugins' . DS);
}
if (!defined('SMARTY_MBSTRING')) {
define('SMARTY_MBSTRING', function_exists('mb_strlen'));
}
if (!defined('SMARTY_RESOURCE_CHAR_SET')) {
define('SMARTY_RESOURCE_CHAR_SET', SMARTY_MBSTRING ? 'UTF-8' : 'ISO-8859-1');
}
if (!defined('SMARTY_RESOURCE_DATE_FORMAT')) {
define('SMARTY_RESOURCE_DATE_FORMAT', '%b %e, %Y');
}
if (!defined('SMARTY_SPL_AUTOLOAD')) {
define('SMARTY_SPL_AUTOLOAD', 0);
}
if (SMARTY_SPL_AUTOLOAD && set_include_path(get_include_path() . PATH_SEPARATOR . SMARTY_SYSPLUGINS_DIR) !== false) {
$registeredAutoLoadFunctions = spl_autoload_functions();
if (!isset($registeredAutoLoadFunctions['spl_autoload'])) {
spl_autoload_register();
}
} else {
spl_autoload_register('smartyAutoload');
}
include_once SMARTY_SYSPLUGINS_DIR.'smarty_internal_data.php';
include_once SMARTY_SYSPLUGINS_DIR.'smarty_internal_templatebase.php';
include_once SMARTY_SYSPLUGINS_DIR.'smarty_internal_template.php';
include_once SMARTY_SYSPLUGINS_DIR.'smarty_resource.php';
include_once SMARTY_SYSPLUGINS_DIR.'smarty_internal_resource_file.php';
include_once SMARTY_SYSPLUGINS_DIR.'smarty_cacheresource.php';
include_once SMARTY_SYSPLUGINS_DIR.'smarty_internal_cacheresource_file.php';
class Smarty extends Smarty_Internal_TemplateBase {
const SMARTY_VERSION = 'Smarty-3.1.8';
const SCOPE_LOCAL = 0;
const SCOPE_PARENT = 1;
const SCOPE_ROOT = 2;
const SCOPE_GLOBAL = 3;
const CACHING_OFF = 0;
const CACHING_LIFETIME_CURRENT = 1;
const CACHING_LIFETIME_SAVED = 2;
const COMPILECHECK_OFF = 0;
const COMPILECHECK_ON = 1;
const COMPILECHECK_CACHEMISS = 2;
const PHP_PASSTHRU = 0; //-> print tags as plain text
const PHP_QUOTE = 1; //-> escape tags as entities
const PHP_REMOVE = 2; //-> escape tags as entities
const PHP_ALLOW = 3; //-> escape tags as entities
const FILTER_POST = 'post';
const FILTER_PRE = 'pre';
const FILTER_OUTPUT = 'output';
const FILTER_VARIABLE = 'variable';
const PLUGIN_FUNCTION = 'function';
const PLUGIN_BLOCK = 'block';
const PLUGIN_COMPILER = 'compiler';
const PLUGIN_MODIFIER = 'modifier';
const PLUGIN_MODIFIERCOMPILER = 'modifiercompiler';
public static $global_tpl_vars = array();
public static $_previous_error_handler = null;
public static $_muted_directories = array();
public static $_MBSTRING = SMARTY_MBSTRING;
public static $_CHARSET = SMARTY_RESOURCE_CHAR_SET;
public static $_DATE_FORMAT = SMARTY_RESOURCE_DATE_FORMAT;
public static $_UTF8_MODIFIER = 'u';
public $auto_literal = true;
public $error_unassigned = false;
public $use_include_path = false;
private $template_dir = array();
public $joined_template_dir = null;
public $joined_config_dir = null;
public $default_template_handler_func = null;
public $default_config_handler_func = null;
public $default_plugin_handler_func = null;
private $compile_dir = null;
private $plugins_dir = array();
private $cache_dir = null;
private $config_dir = array();
public $force_compile = false;
public $compile_check = true;
public $use_sub_dirs = false;
public $allow_ambiguous_resources = false;
public $caching = false;
public $merge_compiled_includes = false;
public $cache_lifetime = 3600;
public $force_cache = false;
public $cache_id = null;
public $compile_id = null;
public $left_delimiter = "{";
public $right_delimiter = "}";
public $security_class = 'Smarty_Security';
public $security_policy = null;
public $php_handling = self::PHP_PASSTHRU;
public $allow_php_templates = false;
public $direct_access_security = true;
public $debugging = false;
public $debugging_ctrl = 'NONE';
public $smarty_debug_id = 'SMARTY_DEBUG';
public $debug_tpl = null;
public $error_reporting = null;
public $get_used_tags = false;
public $config_overwrite = true;
public $config_booleanize = true;
public $config_read_hidden = false;
public $compile_locking = true;
public $cache_locking = false;
public $locking_timeout = 10;
public $template_functions = array();
public $default_resource_type = 'file';
public $caching_type = 'file';
public $properties = array();
public $default_config_type = 'file';
public $template_objects = array();
public $cache_modified_check = false;
public $registered_plugins = array();
public $plugin_search_order = array('function', 'block', 'compiler', 'class');
public $registered_objects = array();
public $registered_classes = array();
public $registered_filters = array();
public $registered_resources = array();
public $_resource_handlers = array();
public $registered_cache_resources = array();
public $_cacheresource_handlers = array();
public $autoload_filters = array();
public $default_modifiers = array();
public $escape_html = false;
public static $_smarty_vars = array();
public $start_time = 0;
public $_file_perms = 0644;
public $_dir_perms = 0771;
public $_tag_stack = array();
public $smarty;
public $_current_file = null;
public $_parserdebug = false;
public $merged_templates_func = array();
public function __construct()
{
$this->smarty = $this;
if (is_callable('mb_internal_encoding')) {
mb_internal_encoding(Smarty::$_CHARSET);
}
$this->start_time = microtime(true);
$this->setTemplateDir('.' . DS . 'templates' . DS)
->setCompileDir('.' . DS . 'templates_c' . DS)
->setPluginsDir(SMARTY_PLUGINS_DIR)
->setCacheDir('.' . DS . 'cache' . DS)
->setConfigDir('.' . DS . 'configs' . DS);
$this->debug_tpl = 'file:' . dirname(__FILE__) . '/debug.tpl';
if (isset($_SERVER['SCRIPT_NAME'])) {
$this->assignGlobal('SCRIPT_NAME', $_SERVER['SCRIPT_NAME']);
}
}
public function __destruct()
{
}
public function __clone()
{
$this->smarty = $this;
}
public function __get($name)
{
$allowed = array(
'template_dir' => 'getTemplateDir',
'config_dir' => 'getConfigDir',
'plugins_dir' => 'getPluginsDir',
'compile_dir' => 'getCompileDir',
'cache_dir' => 'getCacheDir',
);
if (isset($allowed[$name])) {
return $this->{$allowed[$name]}();
} else {
trigger_error('Undefined property: '. get_class($this) .'::$'. $name, E_USER_NOTICE);
}
}
public function __set($name, $value)
{
$allowed = array(
'template_dir' => 'setTemplateDir',
'config_dir' => 'setConfigDir',
'plugins_dir' => 'setPluginsDir',
'compile_dir' => 'setCompileDir',
'cache_dir' => 'setCacheDir',
);
if (isset($allowed[$name])) {
$this->{$allowed[$name]}($value);
} else {
trigger_error('Undefined property: ' . get_class($this) . '::$' . $name, E_USER_NOTICE);
}
}
public function templateExists($resource_name)
{
$save = $this->template_objects;
$tpl = new $this->template_class($resource_name, $this);
$result = $tpl->source->exists;
$this->template_objects = $save;
return $result;
}
public function getGlobal($varname = null)
{
if (isset($varname)) {
if (isset(self::$global_tpl_vars[$varname])) {
return self::$global_tpl_vars[$varname]->value;
} else {
return '';
}
} else {
$_result = array();
foreach (self::$global_tpl_vars AS $key => $var) {
$_result[$key] = $var->value;
}
return $_result;
}
}
function clearAllCache($exp_time = null, $type = null)
{
$_cache_resource = Smarty_CacheResource::load($this, $type);
Smarty_CacheResource::invalidLoadedCache($this);
return $_cache_resource->clearAll($this, $exp_time);
}
public function clearCache($template_name, $cache_id = null, $compile_id = null, $exp_time = null, $type = null)
{
$_cache_resource = Smarty_CacheResource::load($this, $type);
Smarty_CacheResource::invalidLoadedCache($this);
return $_cache_resource->clear($this, $template_name, $cache_id, $compile_id, $exp_time);
}
public function enableSecurity($security_class = null)
{
if ($security_class instanceof Smarty_Security) {
$this->security_policy = $security_class;
return $this;
} elseif (is_object($security_class)) {
throw new SmartyException("Class '" . get_class($security_class) . "' must extend Smarty_Security.");
}
if ($security_class == null) {
$security_class = $this->security_class;
}
if (!class_exists($security_class)) {
throw new SmartyException("Security class '$security_class' is not defined");
} elseif ($security_class !== 'Smarty_Security' && !is_subclass_of($security_class, 'Smarty_Security')) {
throw new SmartyException("Class '$security_class' must extend Smarty_Security.");
} else {
$this->security_policy = new $security_class($this);
}
return $this;
}
public function disableSecurity()
{
$this->security_policy = null;
return $this;
}
public function setTemplateDir($template_dir)
{
$this->template_dir = array();
foreach ((array) $template_dir as $k => $v) {
$this->template_dir[$k] = rtrim($v, '/\\') . DS;
}
$this->joined_template_dir = join(DIRECTORY_SEPARATOR, $this->template_dir);
return $this;
}
public function addTemplateDir($template_dir, $key=null)
{
$this->template_dir = (array) $this->template_dir;
if (is_array($template_dir)) {
foreach ($template_dir as $k => $v) {
if (is_int($k)) {
$this->template_dir[] = rtrim($v, '/\\') . DS;
} else {
$this->template_dir[$k] = rtrim($v, '/\\') . DS;
}
}
} elseif ($key !== null) {
$this->template_dir[$key] = rtrim($template_dir, '/\\') . DS;
} else {
$this->template_dir[] = rtrim($template_dir, '/\\') . DS;
}
$this->joined_template_dir = join(DIRECTORY_SEPARATOR, $this->template_dir);
return $this;
}
public function getTemplateDir($index=null)
{
if ($index !== null) {
return isset($this->template_dir[$index]) ? $this->template_dir[$index] : null;
}
return (array)$this->template_dir;
}
public function setConfigDir($config_dir)
{
$this->config_dir = array();
foreach ((array) $config_dir as $k => $v) {
$this->config_dir[$k] = rtrim($v, '/\\') . DS;
}
$this->joined_config_dir = join(DIRECTORY_SEPARATOR, $this->config_dir);
return $this;
}
public function addConfigDir($config_dir, $key=null)
{
$this->config_dir = (array) $this->config_dir;
if (is_array($config_dir)) {
foreach ($config_dir as $k => $v) {
if (is_int($k)) {
$this->config_dir[] = rtrim($v, '/\\') . DS;
} else {
$this->config_dir[$k] = rtrim($v, '/\\') . DS;
}
}
} elseif( $key !== null ) {
$this->config_dir[$key] = rtrim($config_dir, '/\\') . DS;
} else {
$this->config_dir[] = rtrim($config_dir, '/\\') . DS;
}
$this->joined_config_dir = join(DIRECTORY_SEPARATOR, $this->config_dir);
return $this;
}
public function getConfigDir($index=null)
{
if ($index !== null) {
return isset($this->config_dir[$index]) ? $this->config_dir[$index] : null;
}
return (array)$this->config_dir;
}
public function setPluginsDir($plugins_dir)
{
$this->plugins_dir = array();
foreach ((array)$plugins_dir as $k => $v) {
$this->plugins_dir[$k] = rtrim($v, '/\\') . DS;
}
return $this;
}
public function addPluginsDir($plugins_dir)
{
$this->plugins_dir = (array) $this->plugins_dir;
if (is_array($plugins_dir)) {
foreach ($plugins_dir as $k => $v) {
if (is_int($k)) {
$this->plugins_dir[] = rtrim($v, '/\\') . DS;
} else {
$this->plugins_dir[$k] = rtrim($v, '/\\') . DS;
}
}
} else {
$this->plugins_dir[] = rtrim($plugins_dir, '/\\') . DS;
}
$this->plugins_dir = array_unique($this->plugins_dir);
return $this;
}
public function getPluginsDir()
{
return (array)$this->plugins_dir;
}
public function setCompileDir($compile_dir)
{
$this->compile_dir = rtrim($compile_dir, '/\\') . DS;
if (!isset(Smarty::$_muted_directories[$this->compile_dir])) {
Smarty::$_muted_directories[$this->compile_dir] = null;
}
return $this;
}
public function getCompileDir()
{
return $this->compile_dir;
}
public function setCacheDir($cache_dir)
{
$this->cache_dir = rtrim($cache_dir, '/\\') . DS;
if (!isset(Smarty::$_muted_directories[$this->cache_dir])) {
Smarty::$_muted_directories[$this->cache_dir] = null;
}
return $this;
}
public function getCacheDir()
{
return $this->cache_dir;
}
public function setDefaultModifiers($modifiers)
{
$this->default_modifiers = (array) $modifiers;
return $this;
}
public function addDefaultModifiers($modifiers)
{
if (is_array($modifiers)) {
$this->default_modifiers = array_merge($this->default_modifiers, $modifiers);
} else {
$this->default_modifiers[] = $modifiers;
}
return $this;
}
public function getDefaultModifiers()
{
return $this->default_modifiers;
}
public function setAutoloadFilters($filters, $type=null)
{
if ($type !== null) {
$this->autoload_filters[$type] = (array) $filters;
} else {
$this->autoload_filters = (array) $filters;
}
return $this;
}
public function addAutoloadFilters($filters, $type=null)
{
if ($type !== null) {
if (!empty($this->autoload_filters[$type])) {
$this->autoload_filters[$type] = array_merge($this->autoload_filters[$type], (array) $filters);
} else {
$this->autoload_filters[$type] = (array) $filters;
}
} else {
foreach ((array) $filters as $key => $value) {
if (!empty($this->autoload_filters[$key])) {
$this->autoload_filters[$key] = array_merge($this->autoload_filters[$key], (array) $value);
} else {
$this->autoload_filters[$key] = (array) $value;
}
}
}
return $this;
}
public function getAutoloadFilters($type=null)
{
if ($type !== null) {
return isset($this->autoload_filters[$type]) ? $this->autoload_filters[$type] : array();
}
return $this->autoload_filters;
}
public function getDebugTemplate()
{
return $this->debug_tpl;
}
public function setDebugTemplate($tpl_name)
{
if (!is_readable($tpl_name)) {
throw new SmartyException("Unknown file '{$tpl_name}'");
}
$this->debug_tpl = $tpl_name;
return $this;
}
public function createTemplate($template, $cache_id = null, $compile_id = null, $parent = null, $do_clone = true)
{
if (!empty($cache_id) && (is_object($cache_id) || is_array($cache_id))) {
$parent = $cache_id;
$cache_id = null;
}
if (!empty($parent) && is_array($parent)) {
$data = $parent;
$parent = null;
} else {
$data = null;
}
$cache_id = $cache_id === null ? $this->cache_id : $cache_id;
$compile_id = $compile_id === null ? $this->compile_id : $compile_id;
if ($this->allow_ambiguous_resources) {
$_templateId = Smarty_Resource::getUniqueTemplateName($this, $template) . $cache_id . $compile_id;
} else {
$_templateId = $this->joined_template_dir . '#' . $template . $cache_id . $compile_id;
}
if (isset($_templateId[150])) {
$_templateId = sha1($_templateId);
}
if ($do_clone) {
if (isset($this->template_objects[$_templateId])) {
$tpl = clone $this->template_objects[$_templateId];
$tpl->smarty = clone $tpl->smarty;
$tpl->parent = $parent;
$tpl->tpl_vars = array();
$tpl->config_vars = array();
} else {
$tpl = new $this->template_class($template, clone $this, $parent, $cache_id, $compile_id);
}
} else {
if (isset($this->template_objects[$_templateId])) {
$tpl = $this->template_objects[$_templateId];
$tpl->parent = $parent;
$tpl->tpl_vars = array();
$tpl->config_vars = array();
} else {
$tpl = new $this->template_class($template, $this, $parent, $cache_id, $compile_id);
}
}
if (!empty($data) && is_array($data)) {
foreach ($data as $_key => $_val) {
$tpl->tpl_vars[$_key] = new Smarty_variable($_val);
}
}
return $tpl;
}
public function loadPlugin($plugin_name, $check = true)
{
if ($check && (is_callable($plugin_name) || class_exists($plugin_name, false))) {
return true;
}
$_name_parts = explode('_', $plugin_name, 3);
if (!isset($_name_parts[2]) || strtolower($_name_parts[0]) !== 'smarty') {
throw new SmartyException("plugin {$plugin_name} is not a valid name format");
return false;
}
if (strtolower($_name_parts[1]) == 'internal') {
$file = SMARTY_SYSPLUGINS_DIR . strtolower($plugin_name) . '.php';
if (file_exists($file)) {
require_once($file);
return $file;
} else {
return false;
}
}
$_plugin_filename = "{$_name_parts[1]}.{$_name_parts[2]}.php";
$_stream_resolve_include_path = function_exists('stream_resolve_include_path');
foreach($this->getPluginsDir() as $_plugin_dir) {
$names = array(
$_plugin_dir . $_plugin_filename,
$_plugin_dir . strtolower($_plugin_filename),
);
foreach ($names as $file) {
if (file_exists($file)) {
require_once($file);
return $file;
}
if ($this->use_include_path && !preg_match('/^([\/\\\\]|[a-zA-Z]:[\/\\\\])/', $_plugin_dir)) {
if ($_stream_resolve_include_path) {
$file = stream_resolve_include_path($file);
} else {
$file = Smarty_Internal_Get_Include_Path::getIncludePath($file);
}
if ($file !== false) {
require_once($file);
return $file;
}
}
}
}
return false;
}
public function compileAllTemplates($extention = '.tpl', $force_compile = false, $time_limit = 0, $max_errors = null)
{
return Smarty_Internal_Utility::compileAllTemplates($extention, $force_compile, $time_limit, $max_errors, $this);
}
public function compileAllConfig($extention = '.conf', $force_compile = false, $time_limit = 0, $max_errors = null)
{
return Smarty_Internal_Utility::compileAllConfig($extention, $force_compile, $time_limit, $max_errors, $this);
}
public function clearCompiledTemplate($resource_name = null, $compile_id = null, $exp_time = null)
{
return Smarty_Internal_Utility::clearCompiledTemplate($resource_name, $compile_id, $exp_time, $this);
}
public function getTags(Smarty_Internal_Template $template)
{
return Smarty_Internal_Utility::getTags($template);
}
public function testInstall(&$errors=null)
{
return Smarty_Internal_Utility::testInstall($this, $errors);
}
public static function mutingErrorHandler($errno, $errstr, $errfile, $errline, $errcontext)
{
$_is_muted_directory = false;
if (!isset(Smarty::$_muted_directories[SMARTY_DIR])) {
$smarty_dir = realpath(SMARTY_DIR);
Smarty::$_muted_directories[SMARTY_DIR] = array(
'file' => $smarty_dir,
'length' => strlen($smarty_dir),
);
}
foreach (Smarty::$_muted_directories as $key => &$dir) {
if (!$dir) {
$file = realpath($key);
$dir = array(
'file' => $file,
'length' => strlen($file),
);
}
if (!strncmp($errfile, $dir['file'], $dir['length'])) {
$_is_muted_directory = true;
break;
}
}
if (!$_is_muted_directory || ($errno && $errno & error_reporting())) {
if (Smarty::$_previous_error_handler) {
return call_user_func(Smarty::$_previous_error_handler, $errno, $errstr, $errfile, $errline, $errcontext);
} else {
return false;
}
}
}
public static function muteExpectedErrors()
{
$error_handler = array('Smarty', 'mutingErrorHandler');
$previous = set_error_handler($error_handler);
if ($previous !== $error_handler) {
Smarty::$_previous_error_handler = $previous;
}
}
public static function unmuteExpectedErrors()
{
restore_error_handler();
}
}
if (Smarty::$_CHARSET !== 'UTF-8') {
Smarty::$_UTF8_MODIFIER = '';
}
class SmartyException extends Exception {
}
class SmartyCompilerException extends SmartyException  {
}
function smartyAutoload($class)
{
$_class = strtolower($class);
$_classes = array(
'smarty_config_source' => true,
'smarty_config_compiled' => true,
'smarty_security' => true,
'smarty_cacheresource' => true,
'smarty_cacheresource_custom' => true,
'smarty_cacheresource_keyvaluestore' => true,
'smarty_resource' => true,
'smarty_resource_custom' => true,
'smarty_resource_uncompiled' => true,
'smarty_resource_recompiled' => true,
);
if (!strncmp($_class, 'smarty_internal_', 16) || isset($_classes[$_class])) {
include SMARTY_SYSPLUGINS_DIR . $_class . '.php';
}
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/core/CurrentPage.class.php
class CurrentPage {
private static $_instance = null;
private $_headscripts = array();
private $_footscripts = array();
private $_headstylesheets = array();
private $_prebody = array();
private $_postbody = array();
private $_htmlattributes = array('xmlns' => "http://www.w3.org/1999/xhtml");
private $_page;
private function __construct() {
$uri = $_SERVER['REQUEST_URI'];
if (!$uri) $uri = ROOT_WDIR;
$uri = substr($uri, strlen(ROOT_WDIR));
if (($_qpos = strpos($uri, '?')) !== false) $uri = substr($uri, 0, $_qpos);
if ($uri{0} != '/') $uri = '/' . $uri;
if (preg_match('/\.[a-z]{3,4}$/i', $uri)) {
$ctype = strtolower(preg_replace('/^.*\.([a-z]{3,4})$/i', '\1', $uri));
$uri   = substr($uri, 0, -1 - strlen($ctype));
}
else {
$ctype = 'html';
}
$p = PageModel::Find(
array('rewriteurl' => $uri,
'fuzzy'      => 0), 1
);
$pagedat = PageModel::SplitBaseURL($uri);
if ($p) {
$this->_page = $p;
}
elseif ($pagedat) {
$p = new PageModel();
$p->set('baseurl', $uri);
$p->set('rewriteurl', $uri);
$this->_page = $p;
}
else {
return false;
}
if ($pagedat && $pagedat['parameters']) {
foreach ($pagedat['parameters'] as $k => $v) {
$this->_page->setParameter($k, $v);
}
}
if (is_array($_GET)) {
foreach ($_GET as $k => $v) {
if (is_numeric($k)) continue;
$this->_page->setParameter($k, $v);
}
}
switch ($ctype) {
case 'xml':
$ctype = View::CTYPE_XML;
break;
case 'json':
$ctype = View::CTYPE_JSON;
break;
default:
$ctype = View::CTYPE_HTML;
break;
}
$view                          = $this->_page->getView();
$view->request['contenttype']  = $ctype;
$view->response['contenttype'] = $ctype; // By default, this can be the same.
$view->request['method']       = $_SERVER['REQUEST_METHOD'];
$view->request['useragent']    = $_SERVER['HTTP_USER_AGENT'];
$view->request['uri']          = $_SERVER['REQUEST_URI'];
$view->request['uriresolved']  = $uri;
$view->request['protocol']     = $_SERVER['SERVER_PROTOCOL'];
}
public static function Singleton() {
if (EXEC_MODE != 'WEB') return null;
if (!self::$_instance) {
self::$_instance = new self();
}
return self::$_instance;
}
public static function Render() {
return self::Singleton()->_render();
}
public static function AddScript($script, $location = 'head') {
View::AddScript($script, $location);
}
public static function GetScripts() {
$obj = self::Singleton();
$s   = array_merge($obj->_headscripts, $obj->footscripts);
return $s;
}
public static function AddBodyContent($content, $location = 'pre') {
$obj = self::Singleton();
if (in_array($content, $obj->_prebody)) return;
if (in_array($content, $obj->_postbody)) return;
if ($location == 'pre') $obj->_prebody[] = $content;
else $obj->_postbody[] = $content;
}
public static function AddStylesheet($link, $media = "all") {
View::AddStylesheet($link, $media);
}
public static function AddStyle($style) {
View::AddStyle($style);
}
public static function SetHTMLAttribute($attribute, $value) {
View::SetHTMLAttribute($attribute, $value);
}
public static function GetHead() {
return View::GetHead();
}
public static function GetFoot() {
return View::GetFoot();
}
public static function GetBodyPre() {
return trim(implode("\n", self::Singleton()->_prebody));
}
public static function GetBodyPost() {
return trim(implode("\n", self::Singleton()->_postbody));
}
public static function GetHTMLAttributes($asarray = false) {
return View::GetHTMLAttributes($asarray);
}
private function _render() {
if ($this->_page) {
$view = $this->_page->execute();
Core::RecordNavigation($this->_page);
}
else {
$view        = new View();
$view->error = View::ERROR_NOTFOUND;
}
if ($view->error == View::ERROR_ACCESSDENIED || $view->error == View::ERROR_NOTFOUND) {
HookHandler::DispatchHook('/core/page/error-' . $view->error, $view);
}
if ($view->error != View::ERROR_NOERROR) {
$view->baseurl = '/Error/Error' . $view->error;
$view->setParameters(array());
$view->templatename   = '/pages/error/error' . $view->error . '.tpl';
$view->mastertemplate = ConfigHandler::Get('/theme/default_template');
}
try {
$data = $view->fetch();
}
catch (Exception $e) {
$view->error   = View::ERROR_SERVERERROR;
$view->baseurl = '/Error/Error' . $view->error;
$view->setParameters(array());
$view->templatename   = '/pages/error/error' . $view->error . '.tpl';
$view->mastertemplate = ConfigHandler::Get('/theme/default_template');
$view->assignVariable('exception', $e);
$data = $view->fetch();
}
switch ($view->error) {
case View::ERROR_NOERROR:
header('Status: 200 OK', true, $view->error);
break;
case View::ERROR_ACCESSDENIED:
header('Status: 403 Forbidden', true, $view->error);
break;
case View::ERROR_NOTFOUND:
header('Status: 404 Not Found', true, $view->error);
break;
case View::ERROR_SERVERERROR:
header('Status: 500 Internal Server Error', true, $view->error);
break;
default:
header('Status: 500 Internal Server Error', true, $view->error);
break; // I don't know WTF happened...
}
if ($view->response['contenttype']) header('Content-Type: ' . $view->response['contenttype']);
if (DEVELOPMENT_MODE) header('X-Content-Encoded-By: Core Plus ' . Core::GetComponent()->getVersion());
echo $data;
if (DEVELOPMENT_MODE && $view->mode == View::MODE_PAGE && $view->response['contenttype'] == View::CTYPE_HTML) {
echo '<pre class="xdebug-var-dump">';
echo "Database Reads: " . Core::DB()->readCount() . "\n";
echo "Database Writes: " . Core::DB()->writeCount() . "\n";
echo "Amount of memory used by PHP: " . Core::FormatSize(memory_get_usage()) . "\n";
echo "Total processing time: " . round(Core::GetProfileTimeTotal(), 4) * 1000 . ' ms' . "\n";
if (FULL_DEBUG) {
foreach (Core::GetProfileTimes() as $t) {
echo "[" . Core::FormatProfileTime($t['timetotal']) . "] - " . $t['event'] . "\n";
}
}
echo '<b>Available Components</b>' . "\n";
foreach (Core::GetComponents() as $l => $v) {
echo $v->getName() . ' ' . $v->getVersion() . "\n";
}
echo '<b>Query Log</b>' . "\n";
var_dump(Core::DB()->queryLog());
echo '</pre>';
}
}
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/core/TemplateException.php
class TemplateException extends Exception{
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/core/TemplateInterface.php
interface TemplateInterface {
public function fetch($template);
public function render($template);
public function getTemplateVars($varname = null);
public function assign($tpl_var, $value = null);
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/core/Template.class.php
class Template extends Smarty implements TemplateInterface {
private $_baseurl;
public function  __construct() {
parent::__construct();
$this->addTemplateDir(ROOT_PDIR . 'themes/' . ConfigHandler::Get('/theme/selected') . '/');
foreach (Core::GetComponents() as $c) {
$d = $c->getViewSearchDir();
if ($d) $this->addTemplateDir($d);
$plugindir = $c->getSmartyPluginDirectory();
if ($plugindir) $this->addPluginsDir($plugindir);
}
$this->compile_dir = TMP_DIR . 'smarty_templates_c';
$this->cache_dir   = TMP_DIR . 'smarty_cache';
}
public function setBaseURL($url) {
$this->_baseurl = $url;
}
public function getBaseURL() {
return $this->_baseurl;
}
public function fetch($template) {
$cache_id = null;
$compile_id = null;
$parent = null;
$display = false;
$merge_tpl_vars = true;
$no_output_filter = false;
if (strpos($template, ROOT_PDIR) !== 0 && $template{0} == '/') $template = substr($template, 1);
try{
return parent::fetch($template, $cache_id, $compile_id, $parent, $display, $merge_tpl_vars, $no_output_filter);
}
catch(SmartyException $e){
throw new TemplateException($e->getMessage(), $e->getCode(), $e->getPrevious());
}
}
public function render($template){
$cache_id = null;
$compile_id = null;
$parent = null;
$display = true;
$merge_tpl_vars = true;
$no_output_filter = false;
if (strpos($template, ROOT_PDIR) !== 0 && $template{0} == '/') $template = substr($template, 1);
try{
return parent::fetch($template, $cache_id, $compile_id, $parent, $display, $merge_tpl_vars, $no_output_filter);
}
catch(SmartyException $e){
throw new TemplateException($e->getMessage(), $e->getCode(), $e->getPrevious());
}
}
public static function ResolveFile($filename) {
$t = new Template();
$dirs = $t->getTemplateDir();
if ($filename{0} == '/') $filename = substr($filename, 1);
foreach ($dirs as $d) {
if (file_exists($d . $filename)) return $d . $filename;
}
return null;
}
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/core/UserAgent.php
class UserAgent {
private static $updateInterval =   604800; // 1 week
private static $_ini_url    =   'http://user-agent-string.info/rpc/get_data.php?key=free&format=ini';
private static $_ver_url    =   'http://user-agent-string.info/rpc/get_data.php?key=free&format=ini&ver=y';
private static $_md5_url    =   'http://user-agent-string.info/rpc/get_data.php?format=ini&md5=y';
private static $_info_url   =   'http://user-agent-string.info';
private static $_Cache = array();
private static $_Data = null;
public $type             = 'unknown';
public $ua_family        = 'unknown';
public $ua_name          = 'unknown';
public $ua_version       = 'unknown';
public $ua_url           = 'unknown';
public $ua_company       = 'unknown';
public $ua_company_url   = 'unknown';
public $ua_icon          = 'unknown.png';
public $ua_info_url      = 'unknown';
public $os_family        = 'unknown';
public $os_name          = 'unknown';
public $os_url           = 'unknown';
public $os_company       = 'unknown';
public $os_company_url   = 'unknown';
public $os_icon          = 'unknown.png';
public static function Test(){
var_dump(self::$_Cache);
}
public function __construct($useragent = null) {
if($useragent === null) $useragent = $_SERVER['HTTP_USER_AGENT'];
$_data = $this->_loadData();
if(!$_data || !isset($useragent)) {
return;
}
if(isset(self::$_Cache[$useragent])){
$this->fromArray(self::$_Cache[$useragent]);
return;
}
$os_id = false;
$browser_id = false;
foreach ($_data['robots'] as $test) {
if ($test[0] == $useragent) {
$this->type                            = 'Robot';
if ($test[1]) $this->ua_family        = $test[1];
if ($test[2]) $this->ua_name          = $test[2];
if ($test[3]) $this->ua_url           = $test[3];
if ($test[4]) $this->ua_company       = $test[4];
if ($test[5]) $this->ua_company_url   = $test[5];
if ($test[6]) $this->ua_icon          = $test[6];
if ($test[7]) { // OS set
$os_data = $_data['os'][$test[7]];
if ($os_data[0]) $this->os_family       =   $os_data[0];
if ($os_data[1]) $this->os_name         =   $os_data[1];
if ($os_data[2]) $this->os_url          =   $os_data[2];
if ($os_data[3]) $this->os_company      =   $os_data[3];
if ($os_data[4]) $this->os_company_url  =   $os_data[4];
if ($os_data[5]) $this->os_icon         =   $os_data[5];
}
if ($test[8]) $this->ua_info_url      = self::$_info_url.$test[8];
self::$_Cache[$useragent] = $this->asArray();
return;
}
}
foreach ($_data['browser_reg'] as $test) {
if (@preg_match($test[0],$useragent,$info)) { // $info may contain version
$browser_id = $test[1];
break;
}
}
if ($browser_id) { // browser detail
$browser_data = $_data['browser'][$browser_id];
if ($_data['browser_type'][$browser_data[0]][0]) $this->type    = $_data['browser_type'][$browser_data[0]][0];
if (isset($info[1]))    $this->ua_version     = $info[1];
if ($browser_data[1])   $this->ua_family      = $browser_data[1];
if ($browser_data[1])   $this->ua_name        = $browser_data[1].(isset($info[1]) ? ' '.$info[1] : '');
if ($browser_data[2])   $this->ua_url         = $browser_data[2];
if ($browser_data[3])   $this->ua_company     = $browser_data[3];
if ($browser_data[4])   $this->ua_company_url = $browser_data[4];
if ($browser_data[5])   $this->ua_icon        = $browser_data[5];
if ($browser_data[6])   $this->ua_info_url    = self::$_info_url.$browser_data[6];
}
if (isset($_data['browser_os'][$browser_id])) { // os detail
$os_id = $_data['browser_os'][$browser_id][0]; // Get the os id
$os_data = $_data['os'][$os_id];
if ($os_data[0])    $this->os_family      = $os_data[0];
if ($os_data[1])    $this->os_name        = $os_data[1];
if ($os_data[2])    $this->os_url         = $os_data[2];
if ($os_data[3])    $this->os_company     = $os_data[3];
if ($os_data[4])    $this->os_company_url = $os_data[4];
if ($os_data[5])    $this->os_icon        = $os_data[5];
self::$_Cache[$useragent] = $this->asArray();
return;
}
foreach ($_data['os_reg'] as $test) {
if (@preg_match($test[0],$useragent)) {
$os_id = $test[1];
break;
}
}
if ($os_id) { // os detail
$os_data = $_data['os'][$os_id];
if ($os_data[0]) $this->os_family       = $os_data[0];
if ($os_data[1]) $this->os_name         = $os_data[1];
if ($os_data[2]) $this->os_url          = $os_data[2];
if ($os_data[3]) $this->os_company      = $os_data[3];
if ($os_data[4]) $this->os_company_url  = $os_data[4];
if ($os_data[5]) $this->os_icon         = $os_data[5];
}
self::$_Cache[$useragent] = $this->asArray();
}
public function isBot(){
switch($this->type){
case 'Robot':
case 'Offline Browser':
case 'Other':
return true;
default:
return false;
}
}
public function asArray(){
return array(
'type' => $this->type,
'ua_family'      =>	$this->ua_family,
'ua_name'        =>	$this->ua_name,
'ua_version'     =>	$this->ua_version,
'ua_url'         =>	$this->ua_url,
'ua_company'     =>	$this->ua_company,
'ua_company_url' =>	$this->ua_company_url,
'ua_icon'        =>	$this->ua_icon,
'ua_info_url'    =>	$this->ua_info_url,
'os_family'      =>	$this->os_family,
'os_name'        =>	$this->os_name,
'os_url'         =>	$this->os_url,
'os_company'     =>	$this->os_company,
'os_company_url' =>	$this->os_company_url,
'os_icon'        =>	$this->os_icon,
);
return array(
$this->type,
$this->ua_family,
$this->ua_name,
$this->ua_version,
$this->ua_url,
$this->ua_company,
$this->ua_company_url,
$this->ua_icon,
$this->ua_info_url,
$this->os_family,
$this->os_name,
$this->os_url,
$this->os_company,
$this->os_company_url,
$this->os_icon,
);
}
private function fromArray($dat){
$this->type           = $dat['type'];
$this->ua_family      = $dat['ua_family'];
$this->ua_name        = $dat['ua_name'];
$this->ua_version     = $dat['ua_version'];
$this->ua_url         = $dat['ua_url'];
$this->ua_company     = $dat['ua_company'];
$this->ua_company_url = $dat['ua_company_url'];
$this->ua_icon        = $dat['ua_icon'];
$this->ua_info_url    = $dat['ua_info_url'];
$this->os_family      = $dat['os_family'];
$this->os_name        = $dat['os_name'];
$this->os_url         = $dat['os_url'];
$this->os_company     = $dat['os_company'];
$this->os_company_url = $dat['os_company_url'];
$this->os_icon        = $dat['os_icon'];
}
private static function _LoadData() {
if(self::$_Data === null){
self::$_Data = Cache::GetSystemCache()->get('useragent-cache-ini', 3600);
if(self::$_Data){
return self::$_Data;
}
else{
$file = Core::File('tmp/useragent.cache.ini');
if(!$file->exists()){
$remote = Core::File(self::$_ini_url);
$remote->copyTo($file);
}
if($file->getMTime() < (Time::GetCurrent() - self::$updateInterval)){
$remote = Core::File(self::$_ini_url);
$remote->copyTo($file);
}
self::$_Data = parse_ini_file($file->getFilename(), true);
Cache::GetSystemCache()->set('useragent-cache-ini', self::$_Data);
return self::$_Data;
}
}
return self::$_Data;
}
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/core/View.class.php
class View {
const ERROR_OTHER        = 1;
const ERROR_NOERROR      = 200;
const ERROR_BADREQUEST   = 400;
const ERROR_ACCESSDENIED = 403;
const ERROR_NOTFOUND     = 404;
const ERROR_SERVERERROR  = 500;
const MODE_PAGE = 'page';
const MODE_WIDGET = 'widget';
const MODE_NOOUTPUT = 'nooutput';
const MODE_AJAX = 'ajax';
const MODE_PAGEORAJAX = 'pageorajax';
const METHOD_GET = 'GET';
const METHOD_POST = 'POST';
const METHOD_PUT = 'PUT';
const METHOD_HEAD = 'HEAD';
const METHOD_DELETE = 'DELETE';
const CTYPE_HTML  = 'text/html';
const CTYPE_PLAIN = 'text/plain';
const CTYPE_JSON  = 'application/json';
const CTYPE_XML   = 'application/xml';
const CTYPE_ICS   = 'text/calendar';
public $error;
private $_template;
private $_params;
public $baseurl;
public $title;
public $access;
public $templatename;
public $contenttype = View::CTYPE_HTML;
public $mastertemplate;
public $breadcrumbs = array();
public $controls = array();
public $mode;
public $jsondata = null;
public $updated = null;
public $head = array();
public $meta = array();
public $scripts = array('head' => array(), 'foot' => array());
public $stylesheets = array();
public $canonicalurl = null;
public $allowerrors = false;
public $ssl = false;
public $record = true;
public static $MetaData = array();
public static $HeadScripts = array();
public static $FootScripts = array();
public static $Stylesheets = array();
public static $HTMLAttributes = array();
public static $HeadData = array();
public function __construct() {
$this->error = View::ERROR_NOERROR;
$this->mode  = View::MODE_PAGE;
}
public function setParameters($params) {
$this->_params = $params;
}
public function getParameters() {
if (!$this->_params) {
$this->_params = array();
}
return $this->_params;
}
public function getParameter($key) {
$p = $this->getParameters();
return (array_key_exists($key, $p)) ? $p[$key] : null;
}
public function getTemplate() {
if (!$this->_template) {
$this->_template = new Template();
$this->_template->setBaseURL($this->baseurl);
}
return $this->_template;
}
public function overrideTemplate($template){
if(!is_a($template, 'TemplateInterface')){
return false;
}
if($template == $this->_template){
return false;
}
if($this->_template !== null){
foreach($this->_template->getTemplateVars() as $k => $v){
$template->assign($k, $v);
}
}
$this->_template = $template;
}
public function assign($key, $val) {
$this->getTemplate()->assign($key, $val);
}
public function assignVariable($key, $val) {
$this->assign($key, $val);
}
public function getVariable($key) {
$v = $this->getTemplate()->getVariable($key);
return ($v) ? $v->value : null;
}
public function fetchBody() {
if ($this->mode == View::MODE_NOOUTPUT) {
return null;
}
if ($this->error != View::ERROR_NOERROR && !$this->allowerrors) {
$tmpl = '/pages/error/error' . $this->error . '.tpl';
}
else {
$tmpl = $this->templatename;
}
switch ($this->contenttype) {
case View::CTYPE_XML:
if (strpos($tmpl, ROOT_PDIR) === 0 && strpos($tmpl, '.xml.tpl') !== false) {
$this->mastertemplate = false;
}
else {
$ctemp = Template::ResolveFile(preg_replace('/tpl$/i', 'xml.tpl', $tmpl));
if ($ctemp) {
$tmpl                 = $ctemp;
$this->mastertemplate = false;
}
else {
$this->contenttype = View::CTYPE_HTML;
}
}
break;
case View::CTYPE_ICS:
if(strpos($tmpl, ROOT_PDIR) === 0 && strpos($tmpl, '.ics.tpl') !== false){
$this->mastertemplate = false;
}
else{
$ctemp = Template::ResolveFile(preg_replace('/tpl$/i', 'ics.tpl', $tmpl));
if($ctemp){
$tmpl = $ctemp;
$this->mastertemplate = false;
}
else{
$this->contenttype = View::CTYPE_HTML;
}
}
break;
case View::CTYPE_JSON:
if ($this->jsondata !== null) {
$this->mastertemplate = false;
$tmpl                 = false;
return json_encode($this->jsondata);
}
$ctemp = Template::ResolveFile(preg_replace('/tpl$/i', 'json.tpl', $tmpl));
if ($ctemp) {
$tmpl                 = $ctemp;
$this->mastertemplate = false;
}
else {
$this->contenttype = View::CTYPE_HTML;
}
break;
}
if (!$tmpl && $this->templatename == '') {
throw new Exception('Please set the variable "templatename" on the page view.');
}
if(false && $this->error == View::ERROR_NOERROR && !\Core\user()->exists() && $this->updated){
$cacheable = true;
$key = 'page-body' . str_replace('/', '-', $this->baseurl);
$cache = Cache::GetSystemCache()->get($key, (60*30));
if($cache){
if($this->updated == $cache['updated']){
return $cache['html'];
}
}
}
else{
$cacheable = false;
}
switch ($this->mode) {
case View::MODE_PAGE:
case View::MODE_AJAX:
case View::MODE_PAGEORAJAX:
$t = $this->getTemplate();
$html = $t->fetch($tmpl);
break;
case View::MODE_WIDGET:
$tn = Template::ResolveFile(preg_replace(':^[/]{0,1}pages/:', '/widgets/', $tmpl));
if (!$tn) $tn = $tmpl;
$t = $this->getTemplate();
$html = $t->fetch($tn);
break;
}
if($cacheable){
Cache::GetSystemCache()->set($key, array('updated' => $this->updated, 'html' => $html), (60 * 30));
}
return $html;
}
public function fetch() {
$body = $this->fetchBody();
if ($this->mastertemplate === false) {
return $body;
}
elseif ($this->mastertemplate === null) {
$this->mastertemplate = ConfigHandler::Get('/theme/default_template');
}
if ($this->contenttype == View::CTYPE_JSON) {
$mastertpl = false;
}
else {
switch ($this->mode) {
case View::MODE_PAGEORAJAX:
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){
$mastertpl = false;
$this->mode = View::MODE_AJAX;
}
else{
$mastertpl = ROOT_PDIR . 'themes/' . ConfigHandler::Get('/theme/selected') . '/skins/' . $this->mastertemplate;
$this->mode = View::MODE_PAGE;
}
break;
case View::MODE_NOOUTPUT:
case View::MODE_AJAX:
$mastertpl = false;
break;
case View::MODE_PAGE:
$mastertpl = ROOT_PDIR . 'themes/' . ConfigHandler::Get('/theme/selected') . '/skins/' . $this->mastertemplate;
break;
case View::MODE_WIDGET:
$mastertpl = Template::ResolveFile('widgetcontainers/' . $this->mastertemplate);
break;
}
}
if (!$mastertpl) return $body;
$template = new Template();
$template->setBaseURL('/');
if ($this->mode == View::MODE_PAGE) {
$template->assign('breadcrumbs', $this->getBreadcrumbs());
$template->assign('controls', $this->controls);
$template->assign('messages', Core::GetMessages());
}
if(isset($this->meta['title']) && $this->meta['title']){
$template->assign('title', $this->meta['title']);
}
else{
$template->assign('title', $this->title);
}
$template->assign('body', $body);
try{
$data = $template->fetch($mastertpl);
}
catch(SmartyException $e){
$this->error = View::ERROR_SERVERERROR;
error_log($e->getMessage());
require(ROOT_PDIR . 'core/templates/halt_pages/fatal_error.inc.html');
die();
}
if ($this->mode == View::MODE_PAGE && $this->contenttype == View::CTYPE_HTML) {
HookHandler::DispatchHook('/core/page/rendering', $this);
$data = str_replace('</head>', $this->getHeadContent() . "\n" . '</head>', $data);
$data = str_replace('</body>', $this->getFootContent() . "\n" . '</body>', $data);
$data = str_replace('<html', '<html ' . self::GetHTMLAttributes(), $data);
$url  = strtolower(trim(preg_replace('/[^a-z0-9\-]*/i', '', str_replace('/', '-', $this->baseurl)), '-'));
$bodyclass = 'page-' . $url;
if(preg_match('/<body[^>]*>/', $data, $matches)){
$fullbody = $matches[0];
if($fullbody == '<body>'){
$data = str_replace($fullbody, '<body class="' . $bodyclass . '">', $data);
}
elseif(strpos($fullbody, 'class=') === false){
$data = str_replace($fullbody, substr($fullbody, 0, -1) . ' class="' . $bodyclass . '">', $data);
}
else{
$node = new SimpleXMLElement($fullbody . '</body>');
$newnode = '<body';
foreach($node->attributes() as $k => $v){
if($k == 'class'){
$newnode .= ' ' . $k . '="' . $bodyclass . ' ' . $v . '"';
}
else{
$newnode .= ' ' . $k . '="' . $v . '"';
}
}
$newnode .= '>';
$data = str_replace($fullbody, $newnode, $data);
}
}
if (DEVELOPMENT_MODE) {
$debug = '';
$debug .= '<pre class="xdebug-var-dump screen">';
$debug .= '<b>Template Information</b>' . "\n";
$debug .= 'Base URL: ' . $this->baseurl . "\n";
$debug .= 'Template Used: ' . $this->templatename . "\n";
$debug .= "\n" . '<b>Performance Information</b>' . "\n";
$debug .= "Database Reads: " . Core::DB()->readCount() . "\n";
$debug .= "Database Writes: " . Core::DB()->writeCount() . "\n";
$debug .= "Amount of memory used by PHP: " . Core::FormatSize(memory_get_usage()) . "\n";
$debug .= "Total processing time: " . round(Core::GetProfileTimeTotal(), 4) * 1000 . ' ms' . "\n";
if (FULL_DEBUG) {
foreach (Core::GetProfileTimes() as $t) {
$debug .= "[" . Core::FormatProfileTime($t['timetotal']) . "] - " . $t['event'] . "\n";
}
}
$debug .= "\n" . '<b>Available Components</b>' . "\n";
foreach (Core::GetComponents() as $l => $v) {
$debug .= ($v->isEnabled() ? '[<span style="color:green;">Enabled</span>]' : '[<span style="color:red;">Disabled</span>]').
$v->getName() . ' ' . $v->getVersion() . "\n";
}
$debug .= "\n" . '<b>Registered Hooks</b>' . "\n";
foreach(HookHandler::GetAllHooks() as $hook){
$debug .= $hook->name;
if($hook->description) $debug .= ' <i> - ' . $hook->description . '</i>';
$debug .= "\n" . '<span style="color:#999;">Attached by ' . $hook->getBindingCount() . ' binding(s).</span>' . "\n\n";
}
$debug .= "\n" . '<b>Included Files</b>' . "\n";
$debug .= 'Number: ' . sizeof(get_included_files()) . "\n";
$debug .= implode("\n", get_included_files()) . "\n";
$debug .= "\n" . '<b>Query Log</b>' . "\n";
$debug .= print_r(Core::DB()->queryLog(), true);
$debug .= '</pre>';
$data = str_replace('</body>', $debug . "\n" . '</body>', $data);
}
}
return $data;
}
public function render() {
if ($this->contenttype && $this->contenttype == View::CTYPE_HTML) {
View::AddMeta('http-equiv="Content-Type" content="text/html;charset=UTF-8"');
}
$data = $this->fetch();
if ($this->mode == View::MODE_PAGE || $this->mode == View::MODE_PAGEORAJAX || $this->mode == View::MODE_AJAX) {
switch ($this->error) {
case View::ERROR_NOERROR:
header('Status: 200 OK', true, $this->error);
break;
case View::ERROR_ACCESSDENIED:
header('Status: 403 Forbidden', true, $this->error);
break;
case View::ERROR_NOTFOUND:
header('Status: 404 Not Found', true, $this->error);
break;
case View::ERROR_SERVERERROR:
header('Status: 500 Internal Server Error', true, $this->error);
break;
default:
header('Status: 500 Internal Server Error', true, $this->error);
break; // I don't know WTF happened...
}
if ($this->contenttype) {
if ($this->contenttype == View::CTYPE_HTML) header('Content-Type: text/html; charset=UTF-8');
else header('Content-Type: ' . $this->contenttype);
}
header('X-Content-Encoded-By: Core Plus ' . (DEVELOPMENT_MODE ? Core::GetComponent()->getVersion() : ''));
}
if(SSL_MODE != SSL_MODE_DISABLED){
if($this->ssl && !SSL){
header('Location: ' . ROOT_URL_SSL . substr(REL_REQUEST_PATH, 1));
die('This page requires SSL, if it does not redirect you automatically, please <a href="' . ROOT_URL_SSL . substr(REL_REQUEST_PATH, 1) . '">Click Here</a>.');
}
elseif(!$this->ssl && SSL && SSL_MODE == SSL_MODE_ONDEMAND){
header('Location: ' . ROOT_URL_NOSSL . substr(REL_REQUEST_PATH, 1));
die('This page does not require SSL, if it does not redirect you automatically, please <a href="' . ROOT_URL_NOSSL . substr(REL_REQUEST_PATH, 1) . '">Click Here</a>.');
}
}
echo $data;
}
public function addBreadcrumb($title, $link = null) {
if ($link !== null && strpos($link, '://') === false) $link = Core::ResolveLink($link);
$this->breadcrumbs[] = array('title' => $title,
'link'  => $link);
}
public function setBreadcrumbs($array) {
$this->breadcrumbs = array();
if (!$array) return;
foreach ($array as $k => $v) {
if ($v instanceof PageModel) $this->addBreadcrumb($v->get('title'), $v->getResolvedURL());
else $this->addBreadcrumb($v, $k);
}
}
public function getBreadcrumbs() {
$crumbs = $this->breadcrumbs;
if ($this->title) $crumbs[] = array('title' => $this->title,
'link'  => null);
return $crumbs;
}
public function addControl($title, $link = null, $class = 'edit') {
$control = new ViewControl();
if(func_num_args() == 1 && is_array($title)){
foreach($title as $k => $v){
$control->set($k, $v);
}
}
else{
if(is_array($class)){
foreach($class as $k => $v){
$control->set($k, $v);
}
}
else{
$control->class = $class;
}
$control->title = $title;
$control->link = Core::ResolveLink($link);
}
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
$this->controls[] = $control;
}
public function setAccess($accessstring) {
$this->access = $accessstring;
return $this->checkAccess();
}
public function checkAccess() {
$u = Core::User();
if ($u->checkAccess($this->access)) {
return true;
}
else {
$this->error = View::ERROR_ACCESSDENIED;
return false;
}
}
public function getHeadContent(){
$data = array_merge($this->head, $this->scripts['head'], $this->stylesheets);
if($this->error == View::ERROR_NOERROR){
if($this->updated !== null){
$this->meta['article:modified_time'] = Time::FormatGMT($this->updated, Time::TIMEZONE_GMT, Time::FORMAT_ISO8601);
}
$data[] = '<meta name="generator" content="Core Plus ' . Core::GetComponent()->getVersion() . '"/>';
if(!isset($this->meta['og:title'])){
$this->meta['og:title'] = $this->title;
}
if($this->canonicalurl === null){
$this->canonicalurl = Core::ResolveLink($this->baseurl);
}
if($this->canonicalurl !== false){
$data[] = '<link rel="canonical" href="' . $this->canonicalurl . '" />';
$this->meta['og:url'] = $this->canonicalurl;
}
$this->meta['og:site_name'] = SITENAME;
}
foreach($this->meta as $k => $v){
if(!$v) continue; // Skip blank values.
switch($k){
case 'name':
case 'keywords':
case 'description':
$key = 'name';
break;
default:
$key = 'property';
}
if(is_array($v)){
foreach($v as $sv){
$data[] = '<meta ' . $key . '="' . $k . '" content="' . str_replace('"', '\\"', $sv) . '"/>';
}
}
else{
$data[] = '<meta ' . $key . '="' . $k . '" content="' . str_replace('"', '\\"', $v) . '"/>';
}
}
if (ConfigHandler::Get('/core/markup/minified')) {
$out = implode('', $data);
}
else {
$out = implode("\n", $data);
}
return trim($out);
}
public function getFootContent(){
$data = $this->scripts['foot'];
if (ConfigHandler::Get('/core/markup/minified')) {
$out = implode('', $data);
}
else {
$out = implode("\n", $data);
}
return trim($out);
}
public static function AddScript($script, $location = 'head') {
if (strpos($script, '<script') === false) {
$script = '<script type="text/javascript" src="' . Core::ResolveAsset($script) . '"></script>';
}
$scripts =& PageRequest::GetSystemRequest()->getView()->scripts;
if (in_array($script, $scripts['head'])) return;
if (in_array($script, $scripts['foot'])) return;
if ($location == 'head') $scripts['head'][] = $script;
else $scripts['foot'][] = $script;
}
public static function AppendBodyContent($content){
$scripts =& PageRequest::GetSystemRequest()->getView()->scripts;
if (in_array($content, $scripts['foot'])) return;
$scripts['foot'][] = $content;
}
public static function AddStylesheet($link, $media = "all") {
if (strpos($link, '<link') === false) {
$link = '<link type="text/css" href="' . Core::ResolveAsset($link) . '" media="' . $media . '" rel="stylesheet"/>';
}
$styles =& PageRequest::GetSystemRequest()->getView()->stylesheets;
if (!in_array($link, $styles)) $styles[] = $link;
}
public static function AddStyle($style) {
if (strpos($style, '<style') === false) {
$style = '<style>' . $style . '</style>';
}
$styles =& PageRequest::GetSystemRequest()->getView()->stylesheets;
if (!in_array($style, $styles)) $styles[] = $style;
}
public static function SetHTMLAttribute($attribute, $value) {
self::$HTMLAttributes[$attribute] = $value;
}
public static function GetHTMLAttributes($asarray = false) {
$atts = self::$HTMLAttributes;
if ($asarray) {
return $atts;
}
else {
$str = '';
foreach ($atts as $k => $v) $str .= " $k=\"" . str_replace('"', '\"', $v) . "\"";
return trim($str);
}
}
public static function GetHead() {
return PageRequest::GetSystemRequest()->getView()->getHeadContent();
}
public static function GetFoot() {
return PageRequest::GetSystemRequest()->getView()->getFootContent();
}
public static function AddMetaName($key, $value) {
PageRequest::GetSystemRequest()->getView()->meta[$key] = $value;
}
public static function AddMeta($string) {
if (strpos($string, '<meta') === false) $string = '<meta ' . $string . '/>';
PageRequest::GetSystemRequest()->getView()->head[] = $string;
}
public static function AddHead($string){
PageRequest::GetSystemRequest()->getView()->head[] = $string;
}
}
class ViewException extends Exception {
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/core/ViewControl.class.php
class ViewControl implements ArrayAccess {
public $link = '#';
public $title = '';
public $class = '';
public $icon = '';
public $confirm = '';
public $otherattributes = array();
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
if($this->link){
$html .= '</a>';
}
$html .= '</li>';
return $html;
}
private function _fetchA(){
if(!$this->link) return null;
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
public function offsetExists($offset) {
return(property_exists($this, $offset));
}
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
public function offsetSet($offset, $value) {
$this->set($offset, $value);
}
public function offsetUnset($offset) {
return void;
}
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/core/Widget_2_1.class.php
class Widget_2_1 {
private $_view = null;
private $_request = null;
public $_model = null;
public $_params = null;
public function getView() {
if ($this->_view === null) {
$this->_view              = new View();
$this->_view->contenttype = View::CTYPE_HTML;
$this->_view->mode        = View::MODE_WIDGET;
if ($this->getWidgetModel()) {
$this->_view->baseurl = $this->getWidgetModel()->get('baseurl');
}
else {
$back = debug_backtrace();
$cls  = $back[1]['class'];
if (strpos($cls, 'Widget') !== false) $cls = substr($cls, 0, -6);
$mth                  = $back[1]['function'];
$this->_view->baseurl = $cls . '/' . $mth;
}
}
return $this->_view;
}
public function getRequest(){
if($this->_request === null){
$this->_request = new WidgetRequest();
}
return $this->_request;
}
public function getWidgetModel() {
return $this->_model;
}
protected function setAccess($accessstring) {
$this->getWidgetModel()->set('access', $accessstring);
return (\Core\user()->checkAccess($accessstring));
}
protected function setTemplate($template) {
$this->getView()->templatename = $template;
}
protected function getParameter($param) {
if($this->_params !== null){
$parameters = $this->_params;
}
else{
$dat = $this->getWidgetModel()->splitParts();
$parameters = $dat['parameters'];
}
return (isset($parameters[$param])) ? $parameters[$param] : null;
}
public static function Factory($name) {
return new $name();
}
}
class WidgetRequest{
public $parameters = array();
public function getParameters() {
return $this->parameters;
}
public function getParameter($key) {
return (array_key_exists($key, $this->parameters)) ? $this->parameters[$key] : null;
}
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/core/Form.class.php
class FormGroup {
protected $_elements;
protected $_attributes;
protected $_validattributes = array();
public $requiresupload = false;
public function __construct($atts = null) {
$this->_attributes = array();
$this->_elements   = array();
if ($atts) $this->setFromArray($atts);
}
public function set($key, $value) {
$this->_attributes[strtolower($key)] = $value;
}
public function get($key) {
$key = strtolower($key);
return (isset($this->_attributes[$key])) ? $this->_attributes[$key] : null;
}
public function setFromArray($array) {
foreach ($array as $k => $v) {
$this->set($k, $v);
}
}
public function hasError() {
foreach ($this->_elements as $e) {
if ($e->hasError()) return true;
}
return false;
}
public function getErrors() {
$err = array();
foreach ($this->_elements as $e) {
if ($e instanceof FormGroup) $err = array_merge($err, $e->getErrors());
elseif ($e->hasError()) $err[] = $e->getError();
}
return $err;
}
public function addElement($element, $atts = null) {
if ($element instanceof FormElement || is_a($element, 'FormElement')) {
if ($atts) $element->setFromArray($atts);
$this->_elements[] = $element;
}
elseif ($element instanceof FormGroup) {
if ($atts) $element->setFromArray($atts);
$this->_elements[] = $element;
}
else {
if (!isset(Form::$Mappings[$element])) $element = 'text'; // Default.
$this->_elements[] = new Form::$Mappings[$element]($atts);
}
}
public function switchElement(FormElement $oldelement, FormElement $newelement) {
foreach ($this->_elements as $k => $el) {
if ($el == $oldelement) {
$this->_elements[$k] = $newelement;
return true;
}
if ($el instanceof FormGroup) {
if ($el->switchElement($oldelement, $newelement)) return true;
}
}
return false;
}
public function removeElement($name){
foreach ($this->_elements as $k => $el) {
if($el->get('name') == $name){
unset($this->_elements[$k]);
return true;
}
if ($el instanceof FormGroup) {
if ($el->removeElement($name)) return true;
}
}
return false;
}
public function getTemplateName() {
return 'forms/groups/default.tpl';
}
public function render() {
$out = '';
foreach ($this->_elements as $e) {
$out .= $e->render();
}
$file = $this->getTemplateName();
if (!$file) return $out;
$tpl = new Template();
$tpl->assign('group', $this);
$tpl->assign('elements', $out);
return $tpl->fetch($file);
}
public function getClass() {
$c = $this->get('class');
$r = $this->get('required');
$e = $this->hasError();
return $c . (($r) ? ' formrequired' : '') . (($e) ? ' formerror' : '');
}
public function getGroupAttributes() {
$out = '';
foreach ($this->_validattributes as $k) {
if (($v = $this->get($k))) $out .= " $k=\"" . str_replace('"', '\\"', $v) . "\"";
}
return $out;
}
public function getElements($recursively = true, $includegroups = false) {
$els = array();
foreach ($this->_elements as $e) {
if (
$e instanceof FormElement ||
($e instanceof FormGroup && ($includegroups || !$recursively))
) {
$els[] = $e;
}
if ($recursively && $e instanceof FormGroup) $els = array_merge($els, $e->getElements($recursively));
}
return $els;
}
public function getElement($name) {
return $this->getElementByName($name);
}
public function getElementByName($name) {
$els = $this->getElements(true, true);
foreach ($els as $el) {
if ($el->get('name') == $name) return $el;
}
return false;
}
}
class FormElement {
protected $_attributes = array();
protected $_error;
protected $_validattributes = array();
public $requiresupload = false;
public $validation = null;
public $validationmessage = null;
public function __construct($atts = null) {
if ($atts) $this->setFromArray($atts);
}
public function set($key, $value) {
$key = strtolower($key);
switch ($key) {
case 'value': // Drop into special logic.
$this->setValue($value);
break;
case 'label': // This is an alias for title.
$this->_attributes['title'] = $value;
case 'options':
if (!is_array($value)) {
$this->_attributes[$key] = $value;
}
elseif(\Core\is_numeric_array($value)) {
$o = array();
foreach ($value as $v) {
$o[$v] = $v;
}
$this->_attributes[$key] = $o;
}
else{
$this->_attributes[$key] = $value;
}
break;
case 'autocomplete':
if(!$value){
$this->_attributes[$key] = 'off';
}
else{
$this->_attributes[$key] = 'on';
}
break;
default:
$this->_attributes[$key] = $value;
break;
}
}
public function get($key) {
$key = strtolower($key);
switch ($key) {
case 'label': // Special case, returns either title or name, whichever is set.
if (!empty($this->_attributes['title'])) return $this->_attributes['title'];
else return $this->get('name');
break;
case 'id': // ID is also a special case, it casn use the name if not defined otherwise.
return $this->getID();
break;
default:
return (isset($this->_attributes[$key])) ? $this->_attributes[$key] : null;
}
}
public function getAsArray() {
$ret            = array();
$ret['__class'] = get_class($this);
foreach ($this->_attributes as $k => $v) {
$ret[$k] = (isset($this->_attributes[$k])) ? $this->_attributes[$k] : null;
}
return $ret;
}
public function setFromArray($array) {
foreach ($array as $k => $v) {
$this->set($k, $v);
}
}
public function setValue($value) {
if ($this->get('required') && !$value) {
$this->_error = $this->get('label') . ' is required.';
return false;
}
if ($value && $this->validation) {
$vmesg = $this->validationmessage ? $this->validationmessage : $this->get('label') . ' does not validate correctly, please double check it.';
$v     = $this->validation;
if (strpos($v, '::') !== false && ($out = call_user_func($v, $value)) !== true) {
if ($out !== false) $vmesg = $out;
$this->_error = $vmesg;
return false;
}
elseif (
($v{0} == '/' && !preg_match($v, $value)) ||
($v{0} == '#' && !preg_match($v, $value))
) {
if (DEVELOPMENT_MODE) $vmesg .= ' validation used: ' . $v;
$this->_error = $vmesg;
return false;
}
}
$this->_attributes['value'] = $value;
return true;
}
public function hasError() {
return ($this->_error);
}
public function getError() {
return $this->_error;
}
public function setError($err, $displayMessage = true) {
$this->_error = $err;
if ($err && $displayMessage) Core::SetMessage($err, 'error');
}
public function clearError() {
$this->setError(false);
}
public function getTemplateName() {
return 'forms/elements/' . strtolower(get_class($this)) . '.tpl';
}
public function render() {
if ($this->get('multiple') && !preg_match('/.*\[.*\]/', $this->get('name'))) $this->_attributes['name'] .= '[]';
$file = $this->getTemplateName();
$tpl = new Template();
$tpl->assign('element', $this);
return $tpl->fetch($file);
}
public function getClass() {
$c = $this->get('class');
$r = $this->get('required');
$e = $this->hasError();
return $c . (($r) ? ' formrequired' : '') . (($e) ? ' formerror' : '');
}
public function getID(){
if (!empty($this->_attributes['id'])){
return $this->_attributes['id'];
}
else{
$n = $this->get('name');
$c = strtolower(get_class($this));
$id = $c . '-' . $n;
$id = str_replace('[]', '', $id);
$id = preg_replace('/\[([^\]]*)\]/', '-$1', $id);
return $id;
}
}
public function getInputAttributes() {
$out = '';
foreach ($this->_validattributes as $k) {
if ($k == 'required' && !$this->get($k)) continue;
if($k == 'checked' && !$this->get($k)) continue;
if (($v = $this->get($k)) !== null) $out .= " $k=\"" . str_replace('"', '\\"', $v) . "\"";
}
return $out;
}
public function lookupValueFrom(&$src) {
$n = $this->get('name');
if (strpos($n, '[') !== false) {
$base = substr($n, 0, strpos($n, '['));
if (!isset($src[$base])) return null;
$t = $src[$base];
preg_match_all('/\[(.+?)\]/', $n, $m);
foreach ($m[1] as $k) {
if (!isset($t[$k])) return null;
$t = $t[$k];
}
return $t;
}
else {
if (!isset($src[$n])) return null;
else return $src[$n];
}
}
public static function Factory($type, $attributes = array()) {
if (!isset(Form::$Mappings[$type])) $type = 'text'; // Default.
return new Form::$Mappings[$type]($attributes);
}
}
class Form extends FormGroup {
public static $Mappings = array(
'checkbox'         => 'FormCheckboxInput',
'checkboxes'       => 'FormCheckboxesInput',
'file'             => 'FormFileInput',
'hidden'           => 'FormHiddenInput',
'pageinsertables'  => 'FormPageInsertables',
'pagemeta'         => 'FormPageMeta',
'pagemetas'        => 'FormPageMetasInput',
'pageparentselect' => 'FormPageParentSelectInput',
'pagerewriteurl'   => 'FormPageRewriteURLInput',
'pagethemeselect'  => 'FormPageThemeSelectInput',
'password'         => 'FormPasswordInput',
'radio'            => 'FormRadioInput',
'reset'            => 'FormResetInput',
'select'           => 'FormSelectInput',
'state'            => 'FormStateInput',
'submit'           => 'FormSubmitInput',
'system'           => 'FormSystemInput',
'text'             => 'FormTextInput',
'textarea'         => 'FormTextareaInput',
'time'             => 'FormTimeInput',
'wysiwyg'          => 'FormTextareaInput',
);
public function  __construct($atts = null) {
parent::__construct($atts);
$this->_validattributes = array('accept', 'accept-charset', 'action', 'enctype', 'id', 'method', 'name', 'target', 'style');
$this->_attributes['method'] = 'POST';
}
public function getTemplateName() {
return 'forms/form.tpl';
}
public function  render($part = null) {
foreach ($this->getElements() as $e) {
if ($e->requiresupload) {
$this->set('enctype', 'multipart/form-data');
break;
}
}
$ignoreerrors = false;
if (($part === null || $part == 'body') && $this->get('callsmethod')) {
$e               = new FormHiddenInput(array('name'  => '___formid',
'value' => $this->get('uniqueid')));
$this->_elements = array_merge(array($e), $this->_elements);
if (!$this->get('uniqueid')) {
$hash = '';
if ($this->get('___modelpks')) {
foreach ($this->get('___modelpks') as $k => $v) {
$hash .= $k . ':' . $v . ';';
}
}
foreach ($this->getElements() as $el) {
if($el instanceof FormSystemInput){
$hash .= get_class($el) . ':' . $el->get('name') . ':' . $el->get('value') . ';';
}
else{
$hash .= get_class($el) . ':' . $el->get('name') . ';';
}
}
$hash = md5($hash);
$this->set('uniqueid', $hash);
$this->getElementByName('___formid')->set('value', $hash);
}
if (isset($_SESSION['FormData'][$this->get('uniqueid')])) {
if (($savedform = unserialize($_SESSION['FormData'][$this->get('uniqueid')]))) {
$this->_elements = $savedform->_elements;
}
else {
$ignoreerrors = true;
}
}
else {
$ignoreerrors = true;
}
}
if ($ignoreerrors) {
foreach ($this->getElements(true) as $el) {
$el->setError(false);
}
}
$tpl = new Template();
$tpl->assign('group', $this);
if ($part === null || $part == 'body') {
$els = '';
foreach ($this->_elements as $e) {
$els .= $e->render();
}
$tpl->assign('elements', $els);
}
switch ($part) {
case null:
$out = $tpl->fetch('forms/form.tpl');
break;
case 'head':
$out = $tpl->fetch('forms/form.head.tpl');
break;
case 'body':
$out = $tpl->fetch('forms/form.body.tpl');
break;
case 'foot':
$out = $tpl->fetch('forms/form.foot.tpl');
break;
}
if (($part === null || $part == 'foot') && $this->get('callsmethod')) {
$this->saveToSession();
}
return $out;
}
public function getModel() {
$m = $this->get('___modelname');
if (!$m) return null; // A model needs to be defined first of all...
$model = new $m();
if (!$model instanceof Model) return null; // It needs to be a model... :/
if($model instanceof PageModel){
foreach($this->getElements(false, false) as $el){
if($el instanceof FormPageMeta){
return $el->getModel();
}
}
}
if (is_array($this->get('___modelpks'))) {
foreach ($this->get('___modelpks') as $k => $v) {
$model->set($k, $v);
}
$model->load();
}
$model->setFromForm($this, 'model');
return $model;
if ($model->get('baseurl') && $model->getLink('Page') instanceof PageModel && $this->getElementByName('page')) {
$page = $model->getLink('Page');
if ($model->get('title') !== null) $page->set('title', $model->get('title'));
if ($model->get('access') !== null) $page->set('access', $model->get('access'));
$this->getElementByName('page')->getModel($page);
}
if ($model->get('baseurl') && $model->getLink('Widget') instanceof WidgetModel) {
$widget = $model->getLink('Widget');
if ($model->get('title') !== null) $widget->set('title', $model->get('title'));
if ($model->get('access') !== null) $widget->set('access', $model->get('access'));
}
return $model;
}
public function loadFrom($src) {
$els = $this->getElements(true, false);
foreach ($els as $e) {
$e->clearError();
$e->set('value', $e->lookupValueFrom($src));
if ($e->hasError()) Core::SetMessage($e->getError(), 'error');
}
}
public function switchElementType($elementname, $newtype) {
$el = $this->getElement($elementname);
if (!$el) return false;
if (!isset(self::$Mappings[$newtype])) $newtype = 'text';
$cls = self::$Mappings[$newtype];
if (get_class($el) == $cls) return false;
$atts = $el->getAsArray();
unset($atts['__class']);
$newel = new $cls();
$newel->setFromArray($atts);
$this->switchElement($el, $newel);
return true;
}
private function saveToSession() {
if (!$this->get('callsmethod')) return; // Don't save anything if there's no method to call.
$this->set('expires', Time::GetCurrent() + 1800); // 30 minutes
$_SESSION['FormData'][$this->get('uniqueid')] = serialize($this);
}
public static function CheckSavedSessionData() {
if (!(isset($_SESSION['FormData']) && is_array($_SESSION['FormData']))) return;
$formid = (isset($_REQUEST['___formid'])) ? $_REQUEST['___formid'] : false;
$form   = false;
foreach ($_SESSION['FormData'] as $k => $v) {
if (!($el = unserialize($v))) {
unset($_SESSION['FormData'][$k]);
continue;
}
if ($el->get('expires') <= Time::GetCurrent()) {
unset($_SESSION['FormData'][$k]);
continue;
}
if ($k == $formid) {
$form = $el;
}
}
if (!$form) return;
if (strtoupper($form->get('method')) != $_SERVER['REQUEST_METHOD']) {
Core::SetMessage('Form submission type does not match', 'error');
return;
}
if (strtoupper($form->get('method')) == 'POST') $src =& $_POST;
else $src =& $_GET;
$form->loadFrom($src);
try{
$form->getModel();
if (!$form->hasError()) $status = call_user_func($form->get('callsmethod'), $form);
else $status = false;
}
catch(ModelValidationException $e){
Core::SetMessage($e->getMessage(), 'error');
$status = false;
}
$_SESSION['FormData'][$formid] = serialize($form);
if ($status === false) return;
if ($status === null) return;
unset($_SESSION['FormData'][$formid]);
if ($status === 'die') exit;
elseif ($status === true) Core::Reload();
else Core::Redirect($status);
}
public static function BuildFromModel(Model $model) {
$f = new Form();
$groups = array();
$f->set('___modelname', get_class($model));
$s = $model->getKeySchemas();
$i = $model->GetIndexes();
if (!isset($i['primary'])) $i['primary'] = array();
$new = $model->isnew();
if (!$new) {
$pks = array();
foreach ($i['primary'] as $k => $v) {
$pks[$v] = $model->get($v);
}
$f->set('___modelpks', $pks);
}
foreach ($s as $k => $v) {
if ($new && $v['type'] == Model::ATT_TYPE_ID) continue;
if (!$new && in_array($k, $i['primary'])) continue;
$formatts = array(
'type' => null,
'title' => ucwords($k),
'description' => null,
'required' => false,
'value' => $model->get($k),
'name' => 'model[' . $k . ']',
);
if(!$formatts['value'] && isset($v['default'])) $formatts['value'] = $v['default'];
if(isset($v['form'])){
$formatts = array_merge($formatts, $v['form']);
}
if(isset($v['formtype']))        $formatts['type'] = $v['formtype'];
if(isset($v['formtitle']))       $formatts['title'] = $v['formtitle'];
if(isset($v['formdescription'])) $formatts['description'] = $v['formdescription'];
if(isset($v['required']))        $formatts['required'] = $v['required'];
if($formatts['type'] == 'disabled'){
continue;
}
elseif ($formatts['type'] !== null) {
$el = FormElement::Factory($formatts['type']);
}
elseif ($v['type'] == Model::ATT_TYPE_BOOL) {
$el = FormElement::Factory('radio');
$el->set('options', array('Yes', 'No'));
if ($formatts['value']) $formatts['value'] = 'Yes';
elseif ($formatts['value'] === null && $v['default']) $formatts['value'] = 'Yes';
elseif ($formatts['value'] === null && !$v['default']) $formatts['value'] = 'No';
else $formatts['value'] = 'No';
}
elseif ($v['type'] == Model::ATT_TYPE_STRING) {
$el = FormElement::Factory('text');
}
elseif ($v['type'] == Model::ATT_TYPE_INT) {
$el = FormElement::Factory('text');
}
elseif ($v['type'] == Model::ATT_TYPE_FLOAT) {
$el = FormElement::Factory('text');
}
elseif ($v['type'] == Model::ATT_TYPE_TEXT) {
$el = FormElement::Factory('textarea');
}
elseif ($v['type'] == Model::ATT_TYPE_CREATED) {
continue;
}
elseif ($v['type'] == Model::ATT_TYPE_UPDATED) {
continue;
}
elseif ($v['type'] == Model::ATT_TYPE_ENUM) {
$el   = FormElement::Factory('select');
$opts = $v['options'];
if ($v['null']) $opts = array_merge(array('' => '-Select One-'), $opts);
$el->set('options', $opts);
if ($v['default']) $el->set('value', $v['default']);
}
else {
die('Unsupported model attribute type for Form Builder [' . $v['type'] . ']');
}
unset($formatts['type']);
if(isset($formatts['group'])){
$groupname = $formatts['group'];
if(!isset($groups[$groupname])){
$groups[$groupname] = new FormGroup(array('title' => $groupname));
$f->addElement($groups[$groupname]);
}
unset($formatts['group']);
$el->setFromArray($formatts);
$groups[$groupname]->addElement($el);
}
else{
$el->setFromArray($formatts);
$f->addElement($el);
}
}
return $f;
}
}
class FormPageInsertables extends FormGroup {
public function  __construct($atts = null) {
parent::__construct($atts);
if (!$this->get('title')) $this->set('title', 'Page Content');
if (!$this->get('baseurl')) return null;
$p = new PageModel($this->get('baseurl'));
$tpl = $p->getTemplateName();
if (!$tpl) return null;
$tpl = Template::ResolveFile($tpl);
if (!$tpl) return null;
$tplcontents = file_get_contents($tpl);
preg_match_all('/\{insertable(.*)\}(.*)\{\/insertable\}/isU', $tplcontents, $matches);
if (!sizeof($matches[0])) return null;
foreach ($matches[0] as $k => $v) {
$tag     = trim($matches[1][$k]);
$content = trim($matches[2][$k]);
$default = $content;
$name  = preg_replace('/.*name=["\'](.*?)["\'].*/i', '$1', $tag);
$title = preg_replace('/.*title=["\'](.*?)["\'].*/i', '$1', $tag);
if($title == $tag) $title = $name;
$i = new InsertableModel($this->get('baseurl'), $name);
if ($i->get('value') !== null) $content = $i->get('value');
if (strpos($default, "\n") === false && strpos($default, "<") === false) {
$this->addElement('text', array('name'  => "insertable[$name]",
'title' => $title,
'value' => $content)
);
}
elseif (preg_match('/<img(.*?)>/i', $default)) {
$this->addElement(
'file',
array(
'name' => 'insertable[' . $name . ']',
'title' => $title,
'accept' => 'image/*',
'basedir' => 'public/insertable',
)
);
}
else {
$this->addElement('wysiwyg', array('name'  => "insertable[$name]",
'title' => $title,
'value' => $content)
);
}
}
}
public function save() {
$baseurl = $this->get('baseurl');
$els     = $this->getElements(true, false);
foreach ($els as $e) {
if (!preg_match('/^insertable\[(.*?)\].*/', $e->get('name'), $matches)) continue;
$i = new InsertableModel($baseurl, $matches[1]);
$i->set('value', $e->get('value'));
$i->save();
}
}
} // class FormPageInsertables
class FormPageMeta extends FormGroup {
public function  __construct($atts = null) {
$this->_attributes['name']    = 'page';
if ($atts instanceof PageModel) {
parent::__construct(array('name' => 'page'));
$page = $atts;
}
else {
if(isset($atts['model']) && $atts['model'] instanceof PageModel){
$page = $atts['model'];
unset($atts['model']);
parent::__construct($atts);
}
else{
parent::__construct($atts);
$page = new PageModel($this->get('baseurl'));
}
}
$this->_attributes['baseurl'] = $page->get('baseurl');
$name = $this->_attributes['name'];
$f = new ModelFactory('PageModel');
if ($this->get('baseurl')) $f->where('baseurl != ' . $this->get('baseurl'));
$opts = PageModel::GetPagesAsOptions($f, '-- No Parent Page --');
$this->addElement(
'pageparentselect',
array(
'name'    => $name . "[parenturl]",
'title'   => 'Parent Page',
'value'   => strtolower($page->get('parenturl')),
'options' => $opts
)
);
$this->addElement(
'text', array(
'name'        => $name . "[title]",
'title'       => 'Title',
'value'       => $page->get('title'),
'description' => 'Every page needs a title to accompany it, this should be short but meaningful.',
'required'    => true
)
);
$this->addElement(
'pagerewriteurl', array(
'name'        => $name . "[rewriteurl]",
'title'       => 'Page URL',
'value'       => $page->get('rewriteurl'),
'description' => 'Starts with a "/", omit ' . ROOT_URL,
'required'    => true
)
);
$this->addElement(
'access', array(
'name'  => $name . "[access]",
'title' => 'Access Permissions',
'value' => $page->get('access')
)
);
$this->addElement(
'pagemetas',
array(
'value' => $page->getMetas(),
'name' => $name . '_meta',
)
);
$skins = array('' => '-- Site Default Skin --');
foreach(ThemeHandler::GetTheme(null)->getSkins() as $s){
$n = ($s['title']) ? $s['title'] : $s['file'];
if($s['default']) $n .= ' (default)';
$skins[$s['file']] = $n;
}
if(sizeof($skins) > 2){
$this->addElement(
'select', array(
'name'    => $name . "[theme_template]",
'title'   => 'Theme Skin',
'value'   => $page->get('theme_template'),
'options' => $skins
)
);
}
$tmpname = substr($page->getBaseTemplateName(), 0, -4) . '/';
$matches = array();
$t = new Template();
foreach($t->getTemplateDir() as $d){
if(is_dir($d . $tmpname)){
$dir = new Directory_local_backend($d . $tmpname);
foreach($dir->ls() as $file){
if($file->getExtension() != 'tpl') continue;
$matches[] = $file->getBaseFilename();
}
}
}
if(sizeof($matches)){
$pages = array('' => '-- Default Page Template --');
foreach($matches as $m){
$pages[$m] = ucwords(str_replace('-', ' ', substr($m, 0, -4))) . ' Template';
}
$this->addElement(
'select',
array(
'name'    => $name . '[page_template]',
'title'   => 'Page Template',
'value'   => $page->get('page_template'),
'options' => $pages,
'class' => 'page-template-selector',
)
);
}
}
public function save() {
$page = $this->getModel();
return $page->save();
$els = $this->getElements();
foreach ($els as $e) {
if (!preg_match('/^insertable\[(.*?)\].*/', $e->get('name'), $matches)) continue;
$e->set('baseurl', $this->get('baseurl'));
}
$i = $this->getElementByName('insertables');
$i->save();
return true;
}
public function getModel($page = null) {
if (!$page) $page = new PageModel($this->get('baseurl'));
$name = $this->_attributes['name'];
$els = $this->getElements(true, false);
foreach ($els as $e) {
if (!preg_match('/^[a-z_]*\[(.*?)\].*/', $e->get('name'), $matches)) continue;
$key = $matches[1];
$val = $e->get('value');
if(strpos($e->get('name'), $name . '_meta') === 0){
$page->setMeta($key, $val);
}
elseif(strpos($e->get('name'), $name) === 0){
$page->set($key, $val);
}
else{
continue;
}
}
return $page;
}
public function getTemplateName() {
return null;
}
} // class FormPageInsertables

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/core/PageRequest.class.php
class PageRequest {
const METHOD_HEAD   = 'HEAD';
const METHOD_GET    = 'GET';
const METHOD_POST   = 'POST';
const METHOD_PUT    = 'PUT';
const METHOD_PUSH   = 'PUSH';
const METHOD_DELETE = 'DELETE';
public $contentTypes = array();
public $method = null;
public $useragent = null;
public $uri = null;
public $uriresolved = null;
public $protocol = null;
public $parameters = array();
public $ctype = View::CTYPE_HTML;
private $_pagemodel = null;
private $_pageview = null;
public function __construct($uri = '') {
$this->uri = $uri;
if (!$uri) $uri = ROOT_WDIR;
$uri = substr($uri, strlen(ROOT_WDIR));
if (($_qpos = strpos($uri, '?')) !== false) $uri = substr($uri, 0, $_qpos);
if ($uri{0} != '/') $uri = '/' . $uri;
if (preg_match('/\.[a-z]{3,4}$/i', $uri)) {
$ctype = strtolower(preg_replace('/^.*\.([a-z]{3,4})$/i', '\1', $uri));
$uri   = substr($uri, 0, -1 - strlen($ctype));
}
else {
$ctype = 'html';
}
$this->uriresolved = $uri;
$this->protocol    = $_SERVER['SERVER_PROTOCOL'];
switch ($ctype) {
case 'xml':
$this->ctype = View::CTYPE_XML;
break;
case 'json':
$this->ctype = View::CTYPE_JSON;
break;
case 'ics':
$this->ctype = View::CTYPE_ICS;
break;
default:
$this->ctype = View::CTYPE_HTML;
break;
}
$this->_resolveMethod();
$this->_resolveAcceptHeader();
$this->_resolveUAHeader();
if (is_array($_GET)) {
foreach ($_GET as $k => $v) {
if (is_numeric($k)) continue;
$this->parameters[$k] = $v;
}
}
return;
$p = PageModel::Find(
array('rewriteurl' => $uri,
'fuzzy'      => 0), 1
);
$pagedat = PageModel::SplitBaseURL($uri);
var_dump($pagedat, $_GET);
die();
if ($p) {
$this->pagemodel = $p;
}
elseif ($pagedat) {
$p = new PageModel();
$p->set('baseurl', $uri);
$p->set('rewriteurl', $uri);
$this->pagemodel = $p;
}
else {
return false;
}
if ($pagedat && $pagedat['parameters']) {
foreach ($pagedat['parameters'] as $k => $v) {
$this->pagemodel->setParameter($k, $v);
}
}
if (is_array($_GET)) {
foreach ($_GET as $k => $v) {
if (is_numeric($k)) continue;
$this->pagemodel->setParameter($k, $v);
}
}
switch ($ctype) {
case 'xml':
$ctype = View::CTYPE_XML;
break;
case 'json':
$ctype = View::CTYPE_JSON;
break;
default:
$ctype = View::CTYPE_HTML;
break;
}
}
public function prefersContentType($type) {
$current     = 0;
$currentmain = substr($this->ctype, 0, strpos($this->ctype, '/'));
foreach ($this->contentTypes as $t) {
if ($t['type'] == $this->ctype || ($t['type'] == $t['group'] . '/*' && $t['group'] == $currentmain)) {
$current = max($current, $t['weight']);
}
}
$typeweight = 0;
$typemain   = substr($type, 0, strpos($type, '/'));
foreach ($this->contentTypes as $t) {
if ($t['type'] == $type || ($t['type'] == $t['group'] . '/*' && $t['group'] == $typemain)) {
$typeweight = max($typeweight, $t['weight']);
}
}
return ($typeweight > $current);
}
public function splitParts() {
$ret = PageModel::SplitBaseURL($this->uriresolved);
if (!$ret) {
$ret = array(
'controller' => null,
'method'     => null,
'parameters' => null,
'baseurl'    => null,
'rewriteurl' => null
);
}
if ($ret['parameters'] === null) $ret['parameters'] = array();
$ret['parameters'] = array_merge($ret['parameters'], $this->parameters);
return $ret;
}
public function getBaseURL() {
$parts = $this->splitParts();
return $parts['baseurl'];
}
public function getView(){
if($this->_pageview === null){
$this->_pageview = new View();
}
return $this->_pageview;
}
public function execute() {
$pagedat = $this->splitParts();
$view = $this->getView();
if (!$pagedat['controller']) {
$view->error = View::ERROR_NOTFOUND;
return $view;
}
if ($pagedat['method']{0} == '_') {
$view->error = View::ERROR_NOTFOUND;
return $view;
}
if (!method_exists($pagedat['controller'], $pagedat['method'])) {
$view->error = View::ERROR_NOTFOUND;
return $view;
}
$c = Controller_2_1::Factory($pagedat['controller']);
$view->baseurl = $this->getBaseURL();
$c->setView($view);
$page = $this->getPageModel();
if ($c->accessstring !== null) {
$page->set('access', $c->accessstring);
if (!\Core\user()->checkAccess($c->accessstring)) {
$view->error = View::ERROR_ACCESSDENIED;
return $view;
}
}
$return = call_user_func(array($c, $pagedat['method']));
if (is_int($return)) {
$view->error = $return;
return $view;
}
elseif ($return === null) {
$return = $c->getView();
}
elseif(!is_a($return, 'View')){
if(DEVELOPMENT_MODE){
var_dump('Controller method returned', $return);
die('Sorry, but this controller did not return a valid object.  Please ensure that your method returns either an integer, null, or a View object!');
}
else{
$view->error = View::ERROR_SERVERERROR;
return $view;
}
}
if ($page->exists()) {
$defaultpage = $page;
} else {
$defaultpage = null;
$url         = $view->baseurl;
while ($url != '') {
$url = substr($url, 0, strrpos($url, '/'));
$p   = PageModel::Find(array('baseurl' => $url, 'fuzzy' => 1), 1);
if ($p === null) continue;
if ($p->exists()) {
$defaultpage = $p;
break;
}
}
if ($defaultpage === null) {
$defaultpage = $page;
}
}
foreach ($defaultpage->getMetas() as $key => $val) {
if ($val && !isset($return->meta[$key])) {
$return->meta[$key] = $val;
}
}
if ($return->title === null){
$return->title = $defaultpage->get('title');
}
$parents = array();
foreach ($page->getParentTree() as $parent) {
$parents[] = array(
'title' => $parent->get('title'),
'link'  => $parent->getResolvedURL()
);
}
$return->breadcrumbs = array_merge($parents, $return->breadcrumbs);
if ($return->error == View::ERROR_NOERROR && $return->contenttype == View::CTYPE_HTML && $return->templatename === null) {
$cnameshort           = (strpos($pagedat['controller'], 'Controller') == strlen($pagedat['controller']) - 10) ? substr($pagedat['controller'], 0, -10) : $pagedat['controller'];
$return->templatename = strtolower('/pages/' . $cnameshort . '/' . $pagedat['method'] . '.tpl');
}
elseif ($return->error == View::ERROR_NOERROR && $return->contenttype == View::CTYPE_XML && $return->templatename === null) {
$cnameshort           = (strpos($pagedat['controller'], 'Controller') == strlen($pagedat['controller']) - 10) ? substr($pagedat['controller'], 0, -10) : $pagedat['controller'];
$return->templatename = Template::ResolveFile(strtolower('pages/' . $cnameshort . '/' . $pagedat['method'] . '.xml.tpl'));
}
if($defaultpage->get('page_template')){
$return->templatename = substr($return->templatename, 0, -4) . '/' . $defaultpage->get('page_template');
}
if ($defaultpage->get('theme_template')) {
$return->mastertemplate = $defaultpage->get('theme_template');
}
if ($page->exists() && $return->error == View::ERROR_NOERROR) {
$page->save();
}
return $return;
}
public function setParameters($params) {
$this->parameters = $params;
}
public function getParameters() {
$data = $this->splitParts();
return $data['parameters'];
}
public function getParameter($key) {
$data = $this->splitParts();
return (array_key_exists($key, $data['parameters'])) ? $data['parameters'][$key] : null;
}
public function getPost($key){
return (isset($_POST[$key])) ? $_POST[$key] : null;
}
public function getPageModel() {
if ($this->_pagemodel === null) {
$uri = $this->uriresolved;
$p = PageModel::Find(
array('rewriteurl' => $uri,
'fuzzy'      => 0), 1
);
$pagedat = $this->splitParts();
if ($p) {
$this->_pagemodel = $p;
}
elseif ($pagedat) {
$p = new PageModel($pagedat['baseurl']);
if(!$p->exists()){
$p->set('rewriteurl', $pagedat['rewriteurl']);
}
$this->_pagemodel = $p;
}
else {
return false;
}
if ($pagedat && $pagedat['parameters']) {
foreach ($pagedat['parameters'] as $k => $v) {
$this->_pagemodel->setParameter($k, $v);
}
}
if (is_array($_GET)) {
foreach ($_GET as $k => $v) {
if (is_numeric($k)) continue;
$this->_pagemodel->setParameter($k, $v);
}
}
}
return $this->_pagemodel;
}
public function isPost() {
return ($this->method == PageRequest::METHOD_POST);
}
public function isGet() {
return ($this->method == PageRequest::METHOD_GET);
}
public function isJSON(){
return ($this->ctype == View::CTYPE_JSON);
}
public function isAjax(){
return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
}
public function getUserAgent(){
return new UserAgent($this->useragent);
}
private function _resolveMethod() {
switch ($_SERVER['REQUEST_METHOD']) {
case self::METHOD_DELETE:
case self::METHOD_GET:
case self::METHOD_HEAD:
case self::METHOD_POST:
case self::METHOD_PUSH:
case self::METHOD_PUT:
$this->method = $_SERVER['REQUEST_METHOD'];
break;
default:
$this->method = self::METHOD_GET;
}
}
private function _resolveAcceptHeader() {
$header = (isset($_SERVER['HTTP_ACCEPT'])) ? $_SERVER['HTTP_ACCEPT'] : 'text/html';
$header = explode(',', $header);
$this->contentTypes = array();
if ($this->ctype == View::CTYPE_JSON) {
if (ALLOW_NONXHR_JSON || $this->isAjax()) {
$this->contentTypes[] = array(
'type'   => View::CTYPE_JSON,
'weight' => 1.0
);
}
else {
$this->ctype = View::CTYPE_HTML;
}
}
foreach ($header as $h) {
if (strpos($h, ';') === false) {
$weight  = 1.0; // Do 1.0 to ensure it's parsed as a float and not an int.
$content = $h;
}
else {
list($content, $weight) = explode(';', $h);
$weight = floatval(substr($weight, 3));
}
$this->contentTypes[] = array(
'type'   => $content,
'weight' => $weight
);
}
foreach ($this->contentTypes as $k => $v) {
$this->contentTypes[$k]['group'] = substr($v['type'], 0, strpos($v['type'], '/'));
}
}
private function _resolveUAHeader() {
$ua              = (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : '';
$this->useragent = $ua;
}
public static function GetSystemRequest() {
static $instance = null;
if ($instance === null) {
$instance = new PageRequest($_SERVER['REQUEST_URI']);
}
return $instance;
}
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/libs/core/Controller_2_1.class.php
class Controller_2_1 {
private $_request = null;
private $_model = null;
private $_view = null;
public $accessstring = null;
protected function getPageRequest() {
if ($this->_request === null) {
$this->_request = PageRequest::GetSystemRequest();
}
return $this->_request;
}
public function setView(View $view){
$this->_view = $view;
}
public function getView() {
if ($this->_view === null) {
$this->_view          = new View();
$this->_view->baseurl = $this->getPageRequest()->getBaseURL();
}
return $this->_view;
}
protected function overwriteView($newview) {
$newview->error = View::ERROR_NOERROR;
$this->_view = $newview;
}
public function getPageModel() {
return $this->getPageRequest()->getPageModel();
}
protected function setAccess($accessstring) {
$this->getPageModel()->set('access', $accessstring);
return (\Core\user()->checkAccess($accessstring));
}
protected function setContentType($ctype) {
$this->getView()->contenttype = $ctype;
}
protected function setTemplate($template) {
$this->getView()->templatename = $template;
}
public static function Factory($name) {
return new $name();
}
}

### REQUIRE_ONCE FROM /home/powellc/Projects/CorePlus/site/core/models/WidgetModel.class.php
class WidgetModel extends Model {
public static $Schema = array(
'baseurl' => array(
'type'      => Model::ATT_TYPE_STRING,
'maxlength' => 128,
'required'  => true,
'null'      => false,
),
'title'   => array(
'type'      => Model::ATT_TYPE_STRING,
'maxlength' => 128,
'default'   => null,
'comment'   => '[Cached] Title of the page',
'null'      => true,
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
);
public static function SplitBaseURL($base) {
if (!$base) return null;
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
if (class_exists($controller . 'Widget')) {
switch (true) {
case is_subclass_of($controller . 'Widget', 'Widget_2_1'):
case is_subclass_of($controller . 'Widget', 'Widget'):
$controller = $controller . 'Widget';
break;
default:
return null;
}
}
elseif (class_exists($controller)) {
switch (true) {
case is_subclass_of($controller, 'Widget_2_1'):
case is_subclass_of($controller, 'Widget'):
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
$method = 'index';
}
if (!method_exists($controller, $method)) {
return null;
}
if ($method{0} == '_') return null;
$params = ($base !== false) ? explode('/', $base) : null;
$baseurl = '/' . ((strpos($controller, 'Widget') == strlen($controller) - 6) ? substr($controller, 0, -6) : $controller);
if (!($method == 'index' && !$params)) $baseurl .= '/' . str_replace('_', '/', $method);
$baseurl .= ($params) ? '/' . implode('/', $params) : '';
if($args){
$params = ($params) ? array_merge($params, $args) : $args;
}
return array('controller' => $controller,
'method'     => $method,
'parameters' => $params,
'baseurl'    => $baseurl);
}
}


