#!/usr/bin/env php
<?php
/**
 * The purpose of this file is to archive up the core, components, and bundles.
 * and to set all the appropriate information.
 *
 * @package Core Plus\CLI Utilities
 * @since 2.1.5
 * @author Charlie Powell <charlie@eval.bz>
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

if(!isset($_SERVER['SHELL'])){
	die("Please run this script from the command line.");
}

define('ROOT_PDIR', realpath(dirname(__DIR__) . '/src/') . '/');
define('BASE_DIR', realpath(dirname(__DIR__)) . '/');

// Include the core bootstrap, this will get the system functional.
require_once(ROOT_PDIR . 'core/bootstrap.php');

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
$included_files = array();

function compile_file($filename, $recursivelevel = 0, CompilerNamespace $parentnamespace){

	global $included_files;

	if(in_array($filename, $included_files)){
		echo "Skipping " . $filename . ", already included.\n";
		return false;
	}

	echo "Scanning " . $filename . "...\n";
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
		if(strpos($line, 'require_once') === 0 && $recursivelevel > 0){
			$subfile = preg_replace('#require_once[ ]*\([ ]*([^\)]*)[ ]*\)[ ]*;#', '$1', $line);
			// The file probably has relative paths...
			$replaces = array(
				'__DIR__' => "'" . dirname($filename) . "'",
				'ROOT_PDIR' => "'" . ROOT_PDIR . "'"
			);
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

				$filecontents = compile_file($subfile, ($recursivelevel-1), $namespace);

				// Trim the root directory and put a location-independent one instead.
				$subfilerelative = substr($subfile, strlen(ROOT_PDIR));

				// If this file could not be minified, false will be returned.
				if($filecontents !== false){
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

	echo "Finished scanning $filename, found $codenumber lines\n";
	return $contents;
}

$globalnamespace = new CompilerNamespace();
// Start the namespace.
$contents = $globalnamespace->getOpeningTag();
$contents .= compile_file(ROOT_PDIR . 'core/bootstrap.php', 4, $globalnamespace);

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
 * @package Core Plus\Core
 * @since 2.1.5
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2009-2013  Charlie Powell
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

file_put_contents(ROOT_PDIR . 'core/bootstrap.compiled.php', '<?php' . "\n" . $header . $contents . $globalnamespace->getClosingTag());















// Can we compile SCSS files too?
if(exec('which sass') == ''){
	echo "Skipping compiling of SASS resources, you do not have the sass compiler installed!\n";
}
else{
	echo "Scanning for SASS/SCSS resources...\n";
	exec('find "' . ROOT_PDIR . '" -name "[a-z]*.scss"', $results);

	foreach($results as $file){
		echo "Compiling $file...\n";

		$cssfile = substr($file, 0, -4) . 'css';
		$minfile = substr($file, 0, -4) . 'min.css';

		exec('sass "' . $file . '":"' . $cssfile . '" -C -l -f -t expanded --unix-newlines', $null, $ret);
		if($ret == 0) echo "Compiled CSS file successfully!\n";
		else echo "Couldn't compile CSS file!\n";

		exec('sass "' . $file . '":"' . $minfile . '" -C -f -t compressed --unix-newlines', $null, $ret);
		if($ret == 0) echo "Compiled minified CSS file successfully!\n";
		else echo "Couldn't compile minified CSS file!\n";
	}

	// sass styles.scss:styles.css -l -f -t expanded --unix-newlines
	// sass styles.scss:styles.min.css --style compressed
}