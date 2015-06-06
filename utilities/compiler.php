#!/usr/bin/env php
<?php
/**
 * The purpose of this file is to archive up the core, components, and bundles.
 * and to set all the appropriate information.
 *
 * @package Core\CLI Utilities
 * @since 2.1.5
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2014  Charlie Powell
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

if(!isset($_SERVER['SHELL'])){
	die("Please run this script from the command line.");
}

define('ROOT_PDIR', realpath(dirname(__DIR__) . '/src/') . '/');
define('BASE_DIR', realpath(dirname(__DIR__)) . '/');

define('MAX_RECURSE_LEVEL', 4);

// Include the core bootstrap, this will get the system functional.
require_once(ROOT_PDIR . 'core/bootstrap.php');

require_once(ROOT_PDIR . 'core/libs/core/cli/Arguments.php');
require_once(ROOT_PDIR . 'core/libs/core/cli/Argument.php');


$arguments = new \Core\CLI\Arguments([
	'help' => [
		'description' => 'Display help and exit.',
		'value' => false,
		'shorthand' => ['?', 'h'],
	],
	'php' => [
		'description' => 'Compile PHP files into the compiled bootstrap.',
		'value' => false,
		'shorthand' => [],
	],
	'scss' => [
		'description' => 'Compile SASS/SCSS files into corresponding css.',
		'value' => false,
		'shorthand' => [],
	],
	'sass' => [
		'description' => 'Alias of --scss',
		'value' => false,
		'shorthand' => [],
	],
	'js' => [
		'description' => 'Minify javascript assets',
		'value' => false,
		'shorthand' => [],
	],
	'javascript' => [
		'description' => 'Alias of --js',
		'value' => false,
		'shorthand' => [],
	],
	'core' => [
		'description' => 'Minify Core assets.  (Overrides --component and --theme)',
		'value' => false,
		'shorthand' => [],
	],
	'component' => [
		'description' => 'Minify only a requested component. (Overrides --theme)',
		'value' => true,
		'shorthand' => ['c'],
	],
	'theme' => [
		'description' => 'Minify only a requested theme.',
		'value' => true,
		'shorthand' => ['t'],
	],
]);
$arguments->usageHeader = 'This utility will compile all resources into minified versions.

Standard Usage:
  Simply run it without any arguments to compile/minify everything.

Advanced Options:';
$arguments->processArguments();


// Allow for inline arguments.
$opts = [
	'compile_php'    => null,
	'minify_scss'    => null,
	'minify_js'      => null,
	'only_core'      => null,
    'only_component' => null,
    'only_theme'     => null,
];


// Process and validate those arguments now.
if($arguments->getArgumentValue('help')){
	$arguments->printUsage();
	exit;
}

$set = false;
if($arguments->getArgumentValue('php')){
	$opts['compile_php'] = true;
	$set = true;
}
if($arguments->getArgumentValue('scss')){
	$opts['minify_scss'] = true;
	$set = true;
}
if($arguments->getArgumentValue('sass')){
	$opts['minify_scss'] = true;
	$set = true;
}
if($arguments->getArgumentValue('js')){
	$opts['minify_js'] = true;
	$set = true;
}
if($arguments->getArgumentValue('javascript')){
	$opts['minify_js'] = true;
	$set = true;
}

if($set){
	if($opts['compile_php'] === null){
		$opts['compile_php'] = false;
	}
	if($opts['minify_scss'] === null){
		$opts['minify_scss'] = false;
	}
	if($opts['minify_js'] === null){
		$opts['minify_js'] = false;
	}
}
else{
	// Default, no arguments provided.
	$opts['compile_php'] = true;
	$opts['minify_scss'] = true;
	$opts['minify_js'] = true;
}
unset($set);


if($arguments->getArgumentValue('core')){
	$opts['only_core'] = true;
}
elseif($arguments->getArgumentValue('component')){
	$opts['only_component'] = $arguments->getArgumentValue('component');
	$opts['compile_php'] = false;
}
elseif($arguments->getArgumentValue('theme')){
	$opts['only_theme'] = $arguments->getArgumentValue('theme');
	$opts['compile_php'] = false;
}


// Open the bootstrap.php file and read in the sourcecode of the requested files.

class CompilerNamespace {
	public $name = null;
	public $isopen = false;

	public function __construct($name = null){
		$this->name = $name;
	}

	public function getOpeningTag(){
		if($this->isopen) return '';

		$this->isopen = true;
		return 'namespace ' . $this->name . ' {' . "\n";
	}

	public function getClosingTag(){
		if(!$this->isopen) return '';

		$this->isopen = false;
		$name = ($this->name) ? 'NAMESPACE ' . $this->name : 'GLOBAL NAMESPACE';
		return '} // ENDING ' . $name . "\n";
	}
}

/**
 * Will be a list of files that have already been included.
 * Since it is require_once... it'll check first.
 */
global $included_files;
$included_files = [];

function compile_file($filename, $recursivelevel = 0, CompilerNamespace $parentnamespace){

	global $included_files;

	$haschildren = 0;

	$filedisplay = '/' . substr($filename, strlen(ROOT_PDIR));

	echo "\n";
	$lineprefix = '';

	if($recursivelevel > 0){
		$lineprefix .= str_repeat(' |   ', $recursivelevel-1) .  ' |>- ';
	}

	if(in_array($filename, $included_files)){
		echo $lineprefix;
		echo "Skipping " . $filedisplay . ", already included!\n";
		return false;
	}


	//echo "[$recursivelevel] ";

	$flen = strlen($lineprefix . $filedisplay) + 3;
	echo $lineprefix;
	echo "Scanning $filedisplay..." . str_repeat(' ', max(80 - $flen, 1));

	//echo "Scanning " . $filedisplay . "... ";
	$fh = fopen($filename, 'r');
	if(!$fh) die('Unable to open [' .$filename . '] for reading.');

	$contents       = '';
	$linenumber     = 0;
	$codenumber     = 0;
	$incomment      = false;
	$lastwasending  = false;
	// Will get reset to an internal one if there is one defined.
	$namespace      = $parentnamespace;
	$namespacename  = null;
	$namespacewrote = false;

	while(!feof($fh)){
		$line = trim(fgets($fh, 1024));
		++$linenumber;

		// Skip blank lines.
		if($line == '') continue;

		// If the last line was an ending block and it's continuing on... then the script actually does need it.
		if($lastwasending){
			++$codenumber;
			$contents .= '?>' . "\n";
			$lastwasending = false;
		}

		// Skip the opening <?php line.
		if($line == '<?php' && $codenumber == 0) continue;

		// Single-line comment?
		if(preg_match('#^/[\*]+.*[\*]+/$#', $line)) continue;

		// Another single line comment?
		if(strpos($line, '//') === 0) continue;

		// Opening of a comment?
		if(!$incomment && preg_match('#^/[\*]+#', $line)){
			$incomment = true;
			continue;
		}

		// End of a comment?
		if($incomment && preg_match('#[\*]+/$#', $line)){
			$incomment = false;
			continue;
		}

		// In a multiline comment?
		if($incomment) continue;

		// Namespaces must be first in the code!
		// If the parent namespace was defined and is identical,
		// then don't worry about anything, as the parent will take care of opening and closing it.
		if(strpos($line, 'namespace ') === 0){
			$namespacename = preg_replace('#[^a-zA-Z\\\\]#', '', substr($line, 10));
			// Was this the same namespace as the parent?  If so, I don't need to do anything.
			if($namespacename != $parentnamespace->name){
				// Close the previous namespace before I open a new one!
				$contents .= $parentnamespace->getClosingTag();

				// And start a new one.
				$namespace = new CompilerNamespace($namespacename);
				$contents .= $namespace->getOpeningTag();
				++$codenumber;
			}
			continue;
		}

		// If recursion is enabled, we will recurse into REQUIRE_ONCE statements.
		// And if the namespace is global....
		if(strpos($line, 'require_once') === 0 && $recursivelevel <= MAX_RECURSE_LEVEL){
			$subfile = preg_replace('#require_once[ ]*\([ ]*([^\)]*)[ ]*\)[ ]*;#', '$1', $line);
			// The file probably has relative paths...
			$replaces = [
				'__DIR__' => "'" . dirname($filename) . "'",
				'ROOT_PDIR' => "'" . ROOT_PDIR . "'"
			];
			$subfile = str_replace(array_keys($replaces), array_values($replaces), $subfile);

			// If this looks like a variable or something, skip it!
			if(strpos($subfile, '$') !== false){
				// Skip
			}
			elseif(strpos($subfile, '::') !== false){
				// Skip
			}
			elseif(strpos($subfile, '#SKIPCOMPILER') !== false){
				// Skip
			}
			else{
				// I'm using eval here because the line otherwise is a valid PHP string, just split into parts.
				// it's the easiest way to take a string such as '/somewhere/blah' . '/' . 'foo mep.php'
				// and combine them.

				eval("\$subfile = $subfile;");

				$filecontents = compile_file($subfile, ($recursivelevel+1), $namespace);

				// Trim the root directory and put a location-independent one instead.
				$subfilerelative = substr($subfile, strlen(ROOT_PDIR));

				// If this file could not be minified, false will be returned.
				if($filecontents !== false){
					$haschildren++;
					$line = '### REQUIRE_ONCE FROM ' . $subfilerelative . "\n" . $filecontents . "\n";
					// Do I need to re-open my namespace?
					// This doesn't actually have any effect if it's already open :)
					$line .= $namespace->getOpeningTag();
				}
			}
		}

		// Is this line an ending block?
		if($line == '?>'){
			$lastwasending = true;
			continue;
		}

		++$codenumber;
		//if($codenumber % 10 == 0) $line = '## ' . basename($filename) . ':' . $linenumber . "\n" . $line;

		// I need to make sure that a namespace was written already.
		// This is because the namespace must be the first line in the document.
		/*
		if(!$namespacewrote){
			// Was this the same namespace as the parent?  If so, I don't need to do anything.
			if($namespace != $parentnamespace){
				// Close the previous namespace before I open a new one!
				$contents .= "} // ENDING NAMESPACE " . $parentnamespace . "\n";
				$contents .= "namespace {\n";
				++$codenumber;
				$contents .= $line . "\n";
				$namespacewrote = true;
			}
			$namespacewrote = true;
		}
		*/
		$contents .= $line . "\n";
	}

	// Before I return, I need to check and see if the parent had a different namespace.
	// If it did... I should probably close mine so the parent can reopen its namespace.
	if($namespace->name != $parentnamespace->name){
		$contents .= $namespace->getClosingTag();
	}

	if($haschildren){
		echo "\n";
		$lineprefix = '';
		if($recursivelevel > 0){
			$lineprefix .= str_repeat(' |   ', $recursivelevel-1) .  ' |>- ';
		}

		$flen = strlen($lineprefix . $filedisplay) + 3;
		echo $lineprefix . $filedisplay . "..." . str_repeat(' ', max(89 - $flen, 1)) . "Found $codenumber lines and $haschildren children files";
	}
	else{
		echo "Found $codenumber lines!";
	}

	return $contents;
}

if($opts['compile_php']){
	$globalnamespace = new CompilerNamespace();
	// Start the namespace.
	$contents = $globalnamespace->getOpeningTag();
	$contents .= compile_file(ROOT_PDIR . 'core/bootstrap.php', 0, $globalnamespace);

	// The compiled file will have a header stating some useful information.
	$date = Time::GetCurrent(Time::TIMEZONE_DEFAULT, Time::FORMAT_RFC2822);
	$header = <<<EOD
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
 * @package Core\Core
 * @since 2.1.5
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2015  Charlie Powell
 * @license     GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 *
 * @compiled $date
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


//===========================================================================\\\\
//                                       _____________________________       \\\\
//                                      |                             |      \\\\
//                                      |  You're in the wrong file!  |      \\\\
//                                      |_____    ____________________|      \\\\
//                                            \  /                           \\\\
//                                             \/                            \\\\
//                                                                           \\\\
//                                           _/|__                           \\\\
//                       _,-------,        _/ -|  \_     /~>.                \\\\
//                    _-~ __--~~/\ |      (  \   /  )   | / |                \\\\
//                 _-~__--    //   \\\\      \ *   * /   / | ||                \\\\
//              _-~_--       //     ||      \     /   | /  /|                \\\\
//             ~ ~~~~-_     //       \\\\     |( " )|  / | || /                \\\\
//                     \\   //         ||    | VWV | | /  ///                 \\\\
//               |\\     | //           \\\\ _/      |/ | ./|                   \\\\
//               | |    |// __         _-~         \// |  /                  \\\\
//              /  /   //_-~  ~~--_ _-~  /          |\// /                   \\\\
//             |  |   /-~        _-~    (     /   |/ / /                     \\\\
//            /   /           _-~  __    |   |____|/                         \\\\
//           |   |__         / _-~  ~-_  (_______  `\                        \\\\
//           |      ~~--__--~ /  _     \        __\)))                       \\\\
//            \               _-~       |     ./  \                          \\\\
//             ~~--__        /         /    _/     |                         \\\\
//                   ~~--___/       _-_____/      /                          \\\\
//                    _____/     _-_____/      _-~                           \\\\
//                 /^<  ___       -____         -____                        \\\\
//                    ~~   ~~--__      ``\--__       ``\                     \\\\
//                               ~~--\)\)\)   ~~--\)\)\)                     \\\\
//                                                                           \\\\
//===========================================================================\\\\


EOD;

	file_put_contents(
		ROOT_PDIR . 'core/bootstrap.compiled.php',
		'<?php' . "\n" . $header . $contents . $globalnamespace->getClosingTag()
	);

	echo "\n\n";
}


// Can we compile SCSS files too?
if($opts['minify_scss'] && exec('which sass') == ''){
	echo "Skipping compiling of SASS resources, you do not have the sass compiler installed!\n";
}
elseif($opts['minify_scss']){
	$sassversion = exec("sass --version | sed 's:^Sass \\([0-9\\.]*\\).*:\\1:'");
	echo "Using SASS version " . $sassversion . "\n";

	echo "Compiling SASS/SCSS resources...\n\n";
	// [Filename] [CSS] [MIN]
	echo 'FILENAME                                                                          DEV    MIN' . "\n";
	echo '---------------------------------------------------------------------------------------------' . "\n";

	$compresults  = [];
	$coreresults  = [];
	$themeresults = [];
	// Allow specifying core, a component, or a theme.
	if($opts['only_core'] === true){
		exec('find "' . ROOT_PDIR . 'core/" -name "[a-z]*.scss"', $coreresults);
	}
	elseif($opts['only_component'] !== null){
		exec('find "' . ROOT_PDIR . 'components/' . $opts['only_component'] . '/" -name "[a-z]*.scss"', $compresults);
	}
	elseif($opts['only_theme'] !== null){
		exec('find "' . ROOT_PDIR . 'themes/' . $opts['only_theme'] . '/" -name "[a-z]*.scss"', $themeresults);
	}
	else{
		exec('find "' . ROOT_PDIR . 'core/" -name "[a-z]*.scss"', $coreresults);
		exec('find "' . ROOT_PDIR . 'components/" -name "[a-z]*.scss"', $compresults);
		exec('find "' . ROOT_PDIR . 'themes/" -name "[a-z]*.scss"', $themeresults);
	}

	$results = array_merge($compresults, $themeresults, $coreresults);

	foreach($results as $file){

		$outfilename = basename($file);
		$outdirname  = dirname($file) . '/';

		// If the out directory name ends with "dev/assets/scss/", remap that to "assets/css/"
		// This allows for development SCSS/SASS files to be located in the dev codebase, but not exported along with
		// production packages.
		if(preg_match('#/dev/assets/scss/#', $outdirname)){
			$outdirname = preg_replace('#/dev/assets/scss/#', '/assets/css/', $outdirname);
		}
		elseif(preg_match('#/dev/assets/sass/#', $outdirname)){
			$outdirname = preg_replace('#/dev/assets/sass/#', '/assets/css/', $outdirname);
		}
		elseif(preg_match('#/assets/scss/#', $outdirname)){
			$outdirname = preg_replace('#/assets/scss/#', '/assets/css/', $outdirname);
		}
		elseif(preg_match('#/assets/sass/#', $outdirname)){
			$outdirname = preg_replace('#/assets/sass/#', '/assets/css/', $outdirname);
		}
		elseif(preg_match('#/dev/#', $outdirname)){
			// Skip
			continue;
		}

		$dfile = substr($file, strlen(ROOT_PDIR));
		$flen = strlen($dfile) + 3;
		echo "$dfile..." . str_repeat(' ', max(80 - $flen, 1));

		if(!is_dir($outdirname)){
			mkdir($outdirname);
		}


		$cssfile = $outdirname . substr($outfilename, 0, -4) . 'css';
		$minfile = $outdirname . substr($outfilename, 0, -4) . 'min.css';

		if(version_compare($sassversion, '3.4.4', '>=')){
			// Version 3.4.4 of Sass changed some of the arguments for sourcemap,
			// namely they reversed them.  So now the compressed version requires sourcemap instead of the standard one.

			exec('sass "' . $file . '":"' . $cssfile . '" -C -l -f -t expanded --unix-newlines', $null, $ret);
			if($ret == 0) echo "[ OK ] ";
			else echo "[ !! ]";

			exec('sass "' . $file . '":"' . $minfile . '" -C -f -t compressed --unix-newlines --sourcemap=none', $null, $ret);
			if($ret == 0) echo "[ OK ] ";
			else echo "[ !! ]";
		}
		else{
			// Provide backwards compatibility for developers using an older version of SASS.
			exec('sass "' . $file . '":"' . $cssfile . '" -C -l -f -t expanded --unix-newlines --sourcemap', $null, $ret);
			if($ret == 0) echo "[ OK ] ";
			else echo "[ !! ]";

			exec('sass "' . $file . '":"' . $minfile . '" -C -f -t compressed --unix-newlines', $null, $ret);
			if($ret == 0) echo "[ OK ] ";
			else echo "[ !! ]";
		}


		echo "\n";
	}

	// sass styles.scss:styles.css -l -f -t expanded --unix-newlines
	// sass styles.scss:styles.min.css --style compressed
}


if($opts['minify_js']) {
	echo "Scanning for JS resources...\n";

	$compresults  = [];
	$coreresults  = [];
	$themeresults = [];

	// Allow specifying core, a component, or a theme.
	if($opts['only_core'] === true){
		exec('find "' . ROOT_PDIR . 'core/" -name "[a-z][a-z0-9_-]*.js"', $coreresults);
	}
	elseif($opts['only_component'] !== null){
		exec('find "' . ROOT_PDIR . 'components/' . $opts['only_component'] . '/" -name "[a-z][a-z0-9_-]*.js"', $compresults);
	}
	elseif($opts['only_theme'] !== null){
		exec('find "' . ROOT_PDIR . 'themes/' . $opts['only_theme'] . '/" -name "[a-z][a-z0-9_-]*.js"', $themeresults);
	}
	else{
		exec('find "' . ROOT_PDIR . 'core/" -name "[a-z][a-z0-9_-]*.js"', $coreresults);
		exec('find "' . ROOT_PDIR . 'components/" -name "[a-z][a-z0-9_-]*.js"', $compresults);
		exec('find "' . ROOT_PDIR . 'themes/" -name "[a-z][a-z0-9_-]*.js"', $themeresults);
	}

	$results = array_merge($compresults, $coreresults, $themeresults);

	foreach($results as $file) {

		// Is this already a minified file?
		if(strpos($file, 'min.js') !== false) {
			continue;
		}

		// Only compress files that are located within an "assets" directory.
		// This is because if any script is used for server-side tasks or test-related tasks,
		// there is no reason to need the minified version!
		if(strpos($file, '/assets/') === false) {
			continue;
		}

		echo "Compiling $file...\n";


		$cmd = escapeshellarg(BASE_DIR . 'utilities/minify.js.sh');
		$cmd .= ' ' . escapeshellarg($file);

		exec($cmd);
	}
	echo "\n";
}