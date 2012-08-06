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

// This is required to establish the root path of the system, (since it's always one directory up from "here"
$dir = realpath(dirname($_SERVER['PWD'] . '/' . $_SERVER['SCRIPT_FILENAME']) . '/..') . '/';

// Include the core bootstrap, this will get the system functional.
require_once($dir . 'core/bootstrap.php');

// Open the bootstrap.php file and read in the sourcecode of the requested files.

/**
 * Will be a list of files that have already been included.
 * Since it is require_once... it'll check first.
 */
global $included_files;
$included_files = array();

function compile_file($filename, $recursivelevel = 0){

	global $included_files;

	if(in_array($filename, $included_files)){
		echo "Skipping " . $filename . ", already included.\n";
		return false;
	}

	echo "Scanning " . $filename . "...\n";
	$fh = fopen($filename, 'r');
	if(!$fh) die('Unable to open [' .$filename . '] for reading.');
	$contents = '';
	$linenumber = 0;
	$codenumber = 0;
	$incomment = false;
	while(!feof($fh)){
		$line = trim(fgets($fh, 1024));
		++$linenumber;

		// Skip blank lines.
		if($line == '') continue;

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

		// Namespaces invalidate the entire file.
		if(strpos($line, 'namespace ') === 0) return false;

		// If recursion is enabled, we will recurse into REQUIRE_ONCE statements.
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
			else{
				// I'm using eval here because the line otherwise is a valid PHP string, just split into parts.
				// it's the easiest way to take a string such as '/somewhere/blah' . '/' . 'foo mep.php'
				// and combine them.

				eval("\$subfile = $subfile;");

				$filecontents = compile_file($subfile, ($recursivelevel-1));

				// If this file could not be minified, false will be returned.
				if($filecontents !== false){
					$line = '### REQUIRE_ONCE FROM ' . $subfile . "\n" . $filecontents;
				}
			}
		}

		++$codenumber;
		if($codenumber % 10 == 0) $line = '## ' . basename($filename) . ':' . $codenumber . "\n" . $line;

		$contents .= $line . "\n";
	}
	return $contents;
}

$contents = compile_file(ROOT_PDIR . 'core/bootstrap.php', 2);

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
 * @copyright Copyright (C) 2009-2012  Charlie Powell
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
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

EOD;


file_put_contents(ROOT_PDIR . 'core/bootstrap.compiled.php', '<?php' . "\n" . $header . $contents);