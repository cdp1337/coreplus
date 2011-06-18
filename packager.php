#!/usr/bin/env php
<?php
/**
 * The purpose of this file is to archive up the core, components, and bundles.
 * and to set all the appropriate information.
 * 
 * @package Core
 * @since 2011.06
 * @author Charlie Powell <powellc@powelltechs.com>
 * @copyright Copyright 2011, Charlie Powell
 * @license GNU Lesser General Public License v3 <http://www.gnu.org/licenses/lgpl-3.0.html>
 * This system is licensed under the GNU LGPL, feel free to incorporate it into
 * custom applications, but keep all references of the original authors intact,
 * read the full license terms at <http://www.gnu.org/licenses/lgpl-3.0.html>, 
 * and please contribute back to the community :)
 */


if(!isset($_SERVER['SHELL'])){
	die("Please run this script from the command line.");
}


// Inlude the core bootstrap, this will get the system functional.
require_once('core/bootstrap.php');


// I need a valid editor.
CLI::RequireEditor();

// Some cache variables.
$_cversions = null;

/**
 * Copy file or folder from source to destination, it can do
 * recursive copy as well and is very smart
 * It recursively creates the dest file or directory path if there weren't exists
 * Situtaions :
 * - Src:/home/test/file.txt ,Dst:/home/test/b ,Result:/home/test/b -> If source was file copy file.txt name with b as name to destination
 * - Src:/home/test/file.txt ,Dst:/home/test/b/ ,Result:/home/test/b/file.txt -> If source was file Creates b directory if does not exsits and copy file.txt into it
 * - Src:/home/test ,Dst:/home/ ,Result:/home/test/** -> If source was directory copy test directory and all of its content into dest     
 * - Src:/home/test/ ,Dst:/home/ ,Result:/home/**-> if source was direcotry copy its content to dest
 * - Src:/home/test ,Dst:/home/test2 ,Result:/home/test2/** -> if source was directoy copy it and its content to dest with test2 as name
 * - Src:/home/test/ ,Dst:/home/test2 ,Result:->/home/test2/** if source was directoy copy it and its content to dest with test2 as name
 * @todo
 *     - Should have rollback technique so it can undo the copy when it wasn't successful
 *  - Auto destination technique should be possible to turn off
 *  - Supporting callback function
 *  - May prevent some issues on shared enviroments : http://us3.php.net/umask
 * @author http://sina.salek.ws/en/contact 
 * @param $source //file or folder
 * @param $dest ///file or folder
 * @param $options //folderPermission,filePermission
 * @return boolean
 */
function smartCopy($source, $dest, $options=array('folderPermission'=>0755,'filePermission'=>0755)){
    $result=false;
   
    if (is_file($source)) {
        if ($dest[strlen($dest)-1]=='/') {
            if (!file_exists($dest)) {
                cmfcDirectory::makeAll($dest,$options['folderPermission'],true);
            }
            $__dest=$dest."/".basename($source);
        } else {
            $__dest=$dest;
        }
        $result=copy($source, $__dest);
        chmod($__dest,$options['filePermission']);
       
    } elseif(is_dir($source)) {
        if ($dest[strlen($dest)-1]=='/') {
            if ($source[strlen($source)-1]=='/') {
                //Copy only contents
            } else {
                //Change parent itself and its contents
                $dest=$dest.basename($source);
                @mkdir($dest);
                chmod($dest,$options['filePermission']);
            }
        } else {
            if ($source[strlen($source)-1]=='/') {
                //Copy parent directory with new name and all its content
                @mkdir($dest,$options['folderPermission']);
                chmod($dest,$options['filePermission']);
            } else {
                //Copy parent directory with new name and all its content
                @mkdir($dest,$options['folderPermission']);
                chmod($dest,$options['filePermission']);
            }
        }

        $dirHandle=opendir($source);
        while($file=readdir($dirHandle))
        {
            if($file!="." && $file!="..")
            {
                 if(!is_dir($source."/".$file)) {
                    $__dest=$dest."/".$file;
                } else {
                    $__dest=$dest."/".$file;
                }
                //echo "$source/$file ||| $__dest<br />";
                $result=smartCopy($source."/".$file, $__dest, $options);
            }
        }
        closedir($dirHandle);
       
    } else {
        $result=false;
    }
    return $result;
}


/**
 * Create Unique Arrays using an md5 hash
 *
 * @param array $array
 * @return array
 */
function arrayUnique($array, $preserveKeys = false){
    // Unique Array for return
    $arrayRewrite = array();
    // Array with the md5 hashes
    $arrayHashes = array();
    foreach($array as $key => $item) {
        // Serialize the current element and create a md5 hash
        $hash = md5(serialize($item));
        // If the md5 didn't come up yet, add the element to
        // to arrayRewrite, otherwise drop it
        if (!isset($arrayHashes[$hash])) {
            // Save the current element hash
            $arrayHashes[$hash] = $hash;
            // Add element to the unique Array
            if ($preserveKeys) {
                $arrayRewrite[$key] = $item;
            } else {
                $arrayRewrite[] = $item;
            }
        }
    }
    return $arrayRewrite;
}
    

/**
 * Simple function to get any license from a file context.
 * 
 * @todo This may move to the CLI system if found useful enough...
 * @param string $file
 * @return array
 */
function get_file_licenses($file){
	$ret = array();
	
	$fh = fopen($file, 'r');
	// ** sigh... counldn't open the file... oh well, skip to the next.
	if(!$fh) return $ret;
	// This will make filetype be the extension of the file... useful for expanding to JS, HTML and CSS files.
	$filetype = strtolower(substr($file, strrpos($file, '.') + 1));
	
	$counter = 0;
	$inphpdoc = false;
	
	while(!feof($fh)){
		$counter++;
		$line = trim(fgets($fh, 1024));
		switch($filetype){
			case 'php':
				// Skip line 1... should be <?php
				if($counter == 1) continue;
				// start of a phpDoc comment.
				if($line == '/**'){
					$inphpdoc = true;
					continue;
				}
				// end of a phpDoc comment.  This indicates the end of the reading of the file...
				// Valid license tags must be in the FIRST phpDoc of the page, immediately after the <?php.
				if($file == '*/'){
					break(2);
				}
				// At line 5 and no phpDoc yet?!?  wtf?
				if($counter == 5 && !$inphpdoc){
					break(2);
				} 
				// Recognize PHPDoc syntax... basically just [space]*[space]@license...
				if($inphpdoc && stripos($line, '@license') !== false){
					$lic = preg_replace('/\*[ ]*@license[ ]*/i', '', $line);
					if(substr_count($lic, ' ') == 0 && strpos($lic, '://') !== false){
						// lic is similar to @license http://www.gnu.org/licenses/agpl-3.0.txt
						// Take the entire string as both URL and title.
						$ret[] = array('title' => $lic, 'url' => $lic);
					}
					elseif(strpos($lic, '://') === false){
						// lic is similar to @license GNU Affero General Public License v3
						// There's no url at all... just take the entire string as a title, blank URL.
						$ret[] = array('title' => $lic, 'url' => null);
					}
					elseif(strpos($lic, '<') !== false && strpos($lic, '>') !== false){
						// lic is similar to @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
						// String has both.
						$title = preg_replace('/[ ]*<[^>]*>/', '', $lic);
						$url = preg_replace('/.*<([^>]*)>.*/', '$1', $lic);
						$ret[] = array('title' => $title, 'url' => $url);
					}
				}
				break; // type: 'php'
		}
	}
	fclose($fh);
	return $ret;
}


/**
 * Slightly more advanced function to parse for specific information from file headers.
 *
 * @todo Support additional filetypes other than just PHP.
 * 
 * Will return an array containing any author, license
 * 
 * @todo This may move to the CLI system if found useful enough...
 * @param string $file
 * @return array
 */
function parse_for_documentation($file){
	$ret = array(
		'authors' => array(),
		'licenses' => array()
	);
	
	$fh = fopen($file, 'r');
	// ** sigh... counldn't open the file... oh well, skip to the next.
	if(!$fh) return $ret;
	// This will make filetype be the extension of the file... useful for expanding to JS, HTML and CSS files.
	$filetype = strtolower(substr($file, strrpos($file, '.') + 1));
	
	// This is the counter for non valid doc lines.
	$counter = 0;
	$inphpdoc = false;
	$incomment = false;
	
	while(!feof($fh) && $counter <= 10){
		// I want to limit the number of lines read so this doesn't continue on reading the entire file.
		
		// Remove any extra whitespace.
		$line = trim(fgets($fh, 1024));
		switch($filetype){
			case 'php':
				// This only support multi-line phpdocs.
				// start of a phpDoc comment.
				if($line == '/**'){
					$inphpdoc = true;
					break;
				}
				// end of a phpDoc comment.  This indicates the end of the reading of the file...
				if($line == '*/'){
					$inphpdoc = false;
					break;
				}
				// Not in phpdoc... ok
				if(!$inphpdoc){
					$counter++;
					break;
				}
				
				// Recognize PHPDoc syntax... basically just [space]*[space]@license...
				if($inphpdoc){
					// Is this an @license line?
					if(stripos($line, '@license') !== false){
						$lic = preg_replace('/\*[ ]*@license[ ]*/i', '', $line);
						if(substr_count($lic, ' ') == 0 && strpos($lic, '://') !== false){
							// lic is similar to @license http://www.gnu.org/licenses/agpl-3.0.txt
							// Take the entire string as both URL and title.
							$ret['licenses'][] = array('title' => $lic, 'url' => $lic);
						}
						elseif(strpos($lic, '://') === false){
							// lic is similar to @license GNU Affero General Public License v3
							// There's no url at all... just take the entire string as a title, blank URL.
							$ret['licenses'][] = array('title' => $lic, 'url' => null);
						}
						elseif(strpos($lic, '<') !== false && strpos($lic, '>') !== false){
							// lic is similar to @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
							// String has both.
							$title = preg_replace('/[ ]*<[^>]*>/', '', $lic);
							$url = preg_replace('/.*<([^>]*)>.*/', '$1', $lic);
							$ret['licenses'][] = array('title' => $title, 'url' => $url);
						}
					}
					// Is this an @author line?
					if(stripos($line, '@author') !== false){
						$aut = preg_replace('/\*[ ]*@author[ ]*/i', '', $line);
						$autdata = array();
						if(strpos($aut, '<') !== false && strpos($aut, '>') !== false && preg_match('/<[^>]*(@| at )[^>]*>/i', $aut)){
							// Resembles: @author user foo <email@domain.com>
							// or         @author user foo <email at domain dot com>
							preg_match('/(.*) <([^>]*)>/', $aut, $matches);
							$autdata = array('name' => $matches[1], 'email' => $matches[2]);
						}
						elseif(strpos($aut, '(') !== false && strpos($aut, ')') !== false && preg_match('/\([^\)]*(@| at )[^\)]*\)/i', $aut)){
							// Resembles: @author user foo (email@domain.com)
							// of         @author user foo (email at domain dot com)
							preg_match('/(.*) \(([^\)]*)\)/', $aut, $matches);
							$autdata = array('name' => $matches[1], 'email' => $matches[2]);
						}
						else{
							// Eh, must be something else...
							$autdata = array('name' => $aut, 'email' => null);
						}

						// Sometimes the @author line may consist of:
						// @author credit to someone <someone@somewhere.com>
						$autdata['name'] = preg_replace('/^credit[s]* to/i', '', $autdata['name']);
						$autdata['name'] = preg_replace('/^contribution[s]* from/i', '', $autdata['name']);
						$autdata['name'] = trim($autdata['name']);
						$ret['authors'][] = $autdata;
					}
				}
				break; // type: 'php'
			case 'js':
				// This only support multi-line phpdocs.
				// start of a multiline comment.
				if($line == '/*' || $line == '/*!' || $line == '/**'){
					$incomment = true;
					break;
				}
				// end of a phpDoc comment.  This indicates the end of the reading of the file...
				if($line == '*/'){
					$incomment = false;
					break;
				}
				// Not in phpdoc... ok
				if(!$incomment){
					$counter++;
					break;
				}
				
				// Recognize "* Author: Person Blah" syntax... basically just [space]*[space]license...
				if($incomment){
					// Is this line Author: ?
					if(stripos($line, 'author:') !== false){
						$aut = preg_replace('/\*[ ]*author:[ ]*/i', '', $line);
						$autdata = array();
						if(strpos($aut, '<') !== false && strpos($aut, '>') !== false && preg_match('/<[^>]*(@| at )[^>]*>/i', $aut)){
							// Resembles: @author user foo <email@domain.com>
							// or         @author user foo <email at domain dot com>
							preg_match('/(.*) <([^>]*)>/', $aut, $matches);
							$autdata = array('name' => $matches[1], 'email' => $matches[2]);
						}
						elseif(strpos($aut, '(') !== false && strpos($aut, ')') !== false && preg_match('/\([^\)]*(@| at )[^\)]*\)/i', $aut)){
							// Resembles: @author user foo (email@domain.com)
							// of         @author user foo (email at domain dot com)
							preg_match('/(.*) \(([^\)]*)\)/', $aut, $matches);
							$autdata = array('name' => $matches[1], 'email' => $matches[2]);
						}
						else{
							// Eh, must be something else...
							$autdata = array('name' => $aut, 'email' => null);
						}

						// Sometimes the @author line may consist of:
						// @author credit to someone <someone@somewhere.com>
						$autdata['name'] = preg_replace('/^credit[s]* to/i', '', $autdata['name']);
						$autdata['name'] = preg_replace('/^contribution[s]* from/i', '', $autdata['name']);
						$autdata['name'] = trim($autdata['name']);
						$ret['authors'][] = $autdata;
					}
				}
				break; // type: 'js'
			default:
				break(2);
		}
	}
	fclose($fh);
	
	// I don't want 5 million duplicates... so remove all the duplicate results.
	// I need to use arrayUnique because the arrays are multi-dimensional.
	$ret['licenses'] = arrayUnique($ret['licenses']);
	$ret['authors'] = arrayUnique($ret['authors']);
	return $ret;
}

/**
 * Simple function to intelligently "up" the version number.
 * Supports Ubuntu-style versioning for non-original maintainers (~extraversionnum)
 * 
 * Will try to utilize the versioning names, ie: dev to alpha to beta, etc.
 * 
 * @param string $version
 * @param boolean $original
 * @return string 
 */
function _increment_version($version, $original){
	if($original){
		
		// It's an official package, increment the regular number and drop anything after the ~...
		if(strpos($version, '~') !== false){
			$version = substr($version, 0, strpos($version, '~'));
		}
		
		// if there's a -dev, -b, -rc[0-9], -beta, -a, -alpha, etc... just step up to the next one.
		// dev < alpha = a < beta = b < RC = rc < # <  pl = p
		if(preg_match('/\-(dev|a|alpha|b|beta|rc[0-9]|p|pl)$/i', $version, $match)){
			// Step the development stage up instead of the version number.
			$basev = substr($version, 0, -1-strlen($match[1]));
			switch(strtolower($match[1])){
				case 'dev':
					return $basev . '-alpha';
				case 'a':
				case 'alpha':
					return $basev . '-beta';
				case 'b':
				case 'beta':
					return $basev . '-rc1';
				case 'p':
				case 'pl':
					return $basev;
			}
			// still here, might be 'rc#'.
			if(preg_match('/rc([0-9]*)/i', $match[1], $rcnum)){
				return $basev . '-rc' . ($rcnum[1] + 1);
			}
			
			// still no?  I give up...
			$version = $basev;
		}
		
		// Increment the version number by 0.0.1.
		@list($vmaj, $vmin, $vrev) = explode('.', $version);
		// They need to at least be 0....
		if(is_null($vmaj)) $vmaj = 1;
		if(is_null($vmin)) $vmin = 0;
		if(is_null($vrev)) $vrev = 0;
	
		$vrev++;
		$version = "$vmaj.$vmin.$vrev";
	}
	else{
		// This is a release, but not the original packager.
		// Therefore, all versions should be after the ~ to signify this.
		if(strpos($version, '~') === false){
			$version .= '~1';
		}
		else{
			preg_match('/([^~]*)~([^0-9]*)([0-9]*)/', $version, $matches);
			$version = $matches[1];
			$vname = $matches[2];
			$vnum = $matches[3];
			$vnum++;
			$version .= '~' . $vname . $vnum;
		}
	}
	
	return $version;
}

/**
 * Try to intelligently merge duplicate authors, matching a variety of input names.
 *
 * @param array <<string>> $authors
 * @return array
 */
function get_unique_authors($authors){
	// This clusterfuck of a section will basicaly match up the name to its email,
	// use the email as a unique key for name grouping,
	// then try to figure out the canonical name of the author.
	$ea = array();
	foreach($authors as $a){
		// Remove any whitespace.
		$a['email'] = trim($a['email']);
		$a['name'] = trim($a['name']);

		// Group the names under the emails attached.
		if(!isset($ea[$a['email']])) $ea[$a['email']] = array($a['name']);
		else $ea[$a['email']][] = $a['name'];
	}
	// I now have a cross reference list of emails to names.

	// Handle the unset emails first.
	if(isset($ea[''])){
		array_unique($ea['']);
		foreach($ea[''] as $nk => $n){
			// Look up this name in the list of names that have emails to them.
			foreach($ea as $k => $v){
				if($k == '') continue;
				if(in_array($n, $v)){
					// This name is also under an email address... opt to use the email address one instead.
					unset($ea[''][$nk]);
					continue 2;
				}
			}
		}

		// If there are no more unset names, no need to keep this array laying about.
		if(!sizeof($ea[''])) unset($ea['']);
	}
	

	$authors = array();
	// Now handle every email.
	foreach($ea as $e => $na){
		$na = array_unique($na);
		if($e == ''){
			foreach($na as $name) $authors[] = array('name' => $name);
			continue;
		}
		
		
		// Match differences such as Tomas V.V.Cox and Tomas V. V. Cox
		$simsearch = array();
		foreach($na as $k => $name){
			$key = preg_replace('/[^a-z]/i', '', $name);
			if(in_array($key, $simsearch)) unset($na[$k]);
			else $simsearch[] = $key;
		}
		

		// There may be a pattern in the names, ie: Charlie Powell == cpowell == powellc == charlie.powell
		$aliases = array();
		// Try to get the first and last name.
		$ln = $fn = $funame = '';
		foreach($na as $name){
			if(preg_match('/([a-z]*)[ ]+([a-z]*)/i', $name, $matches)){
				$funame = $matches[1] . ' ' . $matches[2];
				$fn = strtolower($matches[1]);
				$ln = strtolower($matches[2]);
				break;
			}
		}
		if($ln && $fn){
			foreach($na as $name){
				switch(strtolower($name)){
					case $fn . ' ' . $ln:
					case $ln . $fn{0}:
					case $fn . $ln{0}:
					case $fn . '.' . $ln:
						// It matches the pattern, it'll just use the fullname.
						continue 2;
						break;
					default:
						$authors[] = array('email' => $e, 'name' => $name);
				}
			}
			$authors[] = array('email' => $e, 'name' => $funame);
		}
		else{
			foreach($na as $name){
				$authors[] = array('email' => $e, 'name' => $name);
			}
		}
	}

	return $authors;
}

function get_unique_licenses($licenses){
	// This behaves much similar to the unique_authors system above, but much simplier.
	$lics = array();
	foreach($licenses as $k => $v){
		$v['title'] = trim($v['title']);
		$v['url'] = trim($v['url']);

		if(!isset($lics[$v['title']])){
			$lics[$v['title']] = array($v['url']);
		}
		elseif(!in_array($v['url'], $lics[$v['title']])){
			$lics[$v['email']][] = $v['url'];
		}
	}
	// $lics should be unique-ified now.
	$licenses = array();
	foreach($lics as $l => $urls){
		foreach($urls as $url) $licenses[] = array('title' => $l, 'url' => $url);
	}
	
	return $licenses;
}



function process_component($component, $forcerelease = false){
	global $packagername, $packageremail;
	
	// Get that component, should be available via the component handler.
	$c = ComponentHandler::GetComponent($component);
	
	$ans = false;
	
	// If just updating a current release, no need to ask for a version number.
	if($forcerelease){
		// if it's a force release... don't bother asking the user what they want to do.
		$reltype = 'release';
	}
	else{
		$reltype = CLI::PromptUser('Are you releasing a new release or just updating an existing component?', array('update' => 'Update to Existing Version', 'release' => 'New Release'));
	}


	if($reltype == 'release'){

		// Try to determine if it's an official package based on the author email.
		$original = false;
		foreach($c->getAuthors() as $aut){
			if($aut['email'] == $packageremail) $original = true;
		}
		
		// Try to explode the version by a ~ sign, this signifies not the original packager/source.
		// ie: ForeignComponent 3.2.4 may be versioned 3.2.4~thisproject5
		// if it's the 5th revision of the upstream version 3.2.4 for 'thisproject'.
		$version = _increment_version($c->getVersion(), $original);
		
		$version = CLI::PromptUser('Please set the version of the new release', 'text', $version);
		$c->setVersion($version);
	}
	else{
		$version = $c->getVersion();
	}
	
	
	// Set the packager information on this release.
	$c->setPackageMaintainer($packagername, $packageremail);
	
	// Grep through the files and pull out the documentation... this will populate the licenses and authors.
	$licenses = $c->getLicenses();
	$authors = $c->getAuthors();
	//$it = new DirectoryCAEIterator($c->getBaseDir());
	$it = $c->getDirectoryIterator();
	foreach($it as $file){
		$docelements = parse_for_documentation($file->filename);
		$licenses = array_merge($licenses, $docelements['licenses']);
		// @todo Should I skip the authors?
		$authors = array_merge($authors, $docelements['authors']);
	}
	
	$c->setAuthors(get_unique_authors($authors));


	$c->setLicenses(get_unique_licenses($licenses));
	
	while($ans != 'finish'){
		$opts = array(
			'editvers' => 'Edit Version Number',
			'editdesc' => 'Edit Description',
			'editchange' => 'Edit Changelog',
			//'dbtables' => 'Manage DB Tables',
			//'printdebug' => 'DEBUG - Print the XML',
			'finish' => 'Finish Editing, Save it!',
			'exit' => 'Abort and exit without saving changes',
		);
		$ans = CLI::PromptUser('What do you want to edit for component ' . $c->getName() . ' ' . $version, $opts);
		
		switch($ans){
			case 'editvers':
				// Do not need to increment it, just provide the user with the ability to change it.
				// This could be useful if the developer accidently entered the wrong version before.
				//$version = _increment_version($c->getVersion(), $original);
				$version = CLI::PromptUser('Please set the new version or', 'text', $version);
				$c->setVersion($version);
				break;
			case 'editdesc':
				$c->setDescription(CLI::PromptUser('Enter a description.', 'textarea', $c->getDescription()));
				break;
			case 'editchange':
				$c->setChangelog(CLI::PromptUser('Enter the changelog.', 'textarea', $c->getChangelog()));
				break;
			case 'dbtables':
				$c->setDBSchemaTableNames(explode("\n", CLI::PromptUser('Enter the tables that are included in this component', 'textarea', implode("\n", $c->getDBSchemaTableNames()))));
				break;
			case 'printdebug':
				echo $c->getRawXML() . NL;
				break;
			case 'exit':
				echo "Aborting build" . NL;
				exit;
				break;
		}
	}
	
	// User must have selected 'finish'...
	$c->save();
	echo "Saved!" . NL;
	
	if($reltype == 'release'){
		if($forcerelease){
			// if force release, don't give the user an option... just do it.
			$bundleyn = true;
		}
		else{
			$bundleyn = CLI::PromptUser('Package saved, do you want to bundle the changes into a package?', 'boolean');
		}
		if($bundleyn){
			// Create a temp directory to contain all these
			// @todo Bundle up the component, add a META-INF.xml file and (ideally), sign the package.
			$dir = '/tmp/packager-' . $c->getName() . '/';
			
			// The destination depends on the type.
			switch($component){
				case 'core':
					$tgz = ROOT_PDIR . 'exports/core/' . $c->getName() . '-' . $c->getVersion() . '.tgz';
					break;
				default:
					$tgz = ROOT_PDIR . 'exports/components/' . $c->getName() . '-' . $c->getVersion() . '.tgz';
					break;
			}
		
			// Ensure the export directory exists.
			if(!is_dir(dirname($tgz))) exec('mkdir -p "' . dirname($tgz) . '"');
			//mkdir(dirname($tgz));
		
			if(!is_dir($dir)) mkdir($dir);
			if(!is_dir($dir . 'data/')) mkdir($dir . 'data/');
			if(!is_dir($dir . 'META-INF/')) mkdir($dir . 'META-INF/');
			
			//smartCopy(ROOT_PDIR . '/components/' . $c->getName(), $dir . '/data');
			//smartCopy($c->getBaseDir(), $dir . 'data/');
			foreach($c->getAllFilenames() as $f){
				$file = new File($c->getBaseDir() . $f['file']);
				$file->copyTo($dir . 'data/' . $f['file']);
			}
			// Don't forget the metafile....
			$file = new File($c->getXMLFilename());
			$file->copyTo($dir . 'data/' . $c->getXMLFilename(''));

			$packager = 'CAE2 ' . ComponentHandler::GetComponent('core')->getVersion();
			$packagename = $c->getName();
			
			// Different component types require a different bundle type.
			$bundletype = ($component == 'core')? 'core' : 'component';
		
			// This is the data that will be added to the manifest file.
			// That file tells the installer what the archive is, ie: component, template, etc.
			$meta = <<<EOD
Manifest-Version: 1.0
Created-By: $packager
Bundle-ContactAddress: $packageremail
Bundle-Name: $packagename
Bundle-Version: $version
Bundle-Type: $bundletype
EOD;
			file_put_contents($dir . 'META-INF/MANIFEST.MF', $meta);
			exec('tar -czf ' . $tgz . ' -C ' . $dir . ' --exclude=.svn --exclude=*~ --exclude=._* .');
			$bundle = $tgz;
		
			if(CLI::PromptUser('Package created, do you want to sign it?', 'boolean', true)){
				exec('gpg -u "' . $packageremail . '" -a --sign "' . $tgz . '"');
				$bundle .= '.asc';
			}
		
			// And remove the tmp directory.
			exec('rm -fr "' . $dir . '"');
		
			echo "Created package of " . $c->getName() . ' ' . $c->getVersion() . NL . " as " . $bundle . NL;
		}
	}
} // function process_component($component)


function process_theme($theme, $forcerelease = false){
	global $packagername, $packageremail;
	
	$t = new Theme($theme);
	
	$ans = false;
	
	// If just updating a current release, no need to ask for a version number.
	if($forcerelease){
		// if it's a force release... don't bother asking the user what they want to do.
		$reltype = 'release';
	}
	else{
		$reltype = CLI::PromptUser('Are you releasing a new release or just updating an existing theme?', array('update' => 'Update to Existing Version', 'release' => 'New Release'));
	}


	if($reltype == 'release'){
		// Try to determine if it's an official package based on the author email.
		$original = false;
		foreach($t->getAuthors() as $aut){
			if($aut['email'] == $packageremail) $original = true;
		}
		
		// Try to explode the version by a ~ sign, this signifies not the original packager/source.
		// ie: ForeignComponent 3.2.4 may be versioned 3.2.4~thisproject5
		// if it's the 5th revision of the upstream version 3.2.4 for 'thisproject'.
		$version = _increment_version($t->getVersion(), $original);
		
		$version = CLI::PromptUser('Please set the version of the new release', 'text', $version);
		$c->setVersion($version);
	}
	else{
		$version = $t->getVersion();
	}
	
	
	// Set the packager information on this release.
	$t->setPackageMaintainer($packagername, $packageremail);
	
	// Grep through the files and pull out the documentation... this will populate the licenses and authors.
	$licenses = $t->getLicenses();
	$authors = $t->getAuthors();
	//$it = new DirectoryCAEIterator($c->getBaseDir());
	$it = $t->getDirectoryIterator();
	foreach($it as $file){
		$docelements = parse_for_documentation($file->filename);
		$licenses = array_merge($licenses, $docelements['licenses']);
		// @todo Should I skip the authors?
		$authors = array_merge($authors, $docelements['authors']);
	}
	
	$t->setAuthors(get_unique_authors($authors));


	$t->setLicenses(get_unique_licenses($licenses));
	
	while($ans != 'finish'){
		$opts = array(
			'editdesc' => 'Edit Description',
			'editchange' => 'Edit Changelog',
			'finish' => 'Finish Editing, Save it!',
		);
		$ans = CLI::PromptUser('What do you want to edit for theme ' . $t->getName() . ' ' . $version, $opts);
		
		switch($ans){
			case 'editdesc':
				$t->setDescription(CLI::PromptUser('Enter a description.', 'textarea', $c->getDescription()));
				break;
			case 'editchange':
				$t->setChangelog(CLI::PromptUser('Enter the changelog.', 'textarea', $c->getChangelog()));
				break;
		}
	}
	
	
	// @todo Add SVN integration using libraries from the websvn project.
	
	
	// User must have selected 'finish'...
	$t->save();
	echo "Saved!" . NL;
	
	if($reltype == 'release'){
		if($forcerelease){
			// if force release, don't give the user an option... just do it.
			$bundleyn = true;
		}
		else{
			$bundleyn = CLI::PromptUser('Package saved, do you want to bundle the changes into a package?', 'boolean');
		}
		
		
		if($bundleyn){
			// Create a temp directory to contain all these
			// @todo Bundle up the component, add a META-INF.xml file and (ideally), sign the package.
			$dir = '/tmp/packager-' . $t->getName() . '/';
			$tgz = ROOT_PDIR . 'exports/themes/' . $t->getName() . '-' . $t->getVersion() . '.tgz';
		
			// Ensure the export directory exists.
			if(!is_dir(dirname($tgz))) exec('mkdir -p "' . dirname($tgz) . '"');
			//mkdir(dirname($tgz));
		
			if(!is_dir($dir)) mkdir($dir);
			if(!is_dir($dir . 'data/')) mkdir($dir . 'data/');
			if(!is_dir($dir . 'META-INF/')) mkdir($dir . 'META-INF/');
			
			//smartCopy(ROOT_PDIR . '/components/' . $c->getName(), $dir . '/data');
			//smartCopy($c->getBaseDir(), $dir . 'data/');
			foreach($t->getAllFilenames() as $f){
				$file = new File($t->getBaseDir() . $f['file']);
				$file->copyTo($dir . 'data/' . $f['file']);
			}
			// Don't forget the metafile....
			$file = new File($t->getXMLFilename());
			$file->copyTo($dir . 'data/' . $t->getXMLFilename(''));

			$packager = 'CAE2 ' . ComponentHandler::GetComponent('core')->getVersion();
			$packagename = $t->getName();
			
			// Different component types require a different bundle type.
			$bundletype = 'theme';
		
			// This is the data that will be added to the manifest file.
			// That file tells the installer what the archive is, ie: component, template, etc.
			$meta = <<<EOD
Manifest-Version: 1.0
Created-By: $packager
Bundle-ContactAddress: $packageremail
Bundle-Name: $packagename
Bundle-Version: $version
Bundle-Type: $bundletype
EOD;
			file_put_contents($dir . 'META-INF/MANIFEST.MF', $meta);
			exec('tar -czf ' . $tgz . ' -C ' . $dir . ' --exclude=.svn --exclude=*~ --exclude=._* .');
			$bundle = $tgz;
		
			if(CLI::PromptUser('Package created, do you want to sign it?', 'boolean', true)){
				exec('gpg -u "' . $packageremail . '" -a --sign "' . $tgz . '"');
				$bundle .= '.asc';
			}
		
			// And remove the tmp directory.
			exec('rm -fr "' . $dir . '"');
		
			echo "Created package of " . $t->getName() . ' ' . $t->getVersion() . NL . " as " . $bundle . NL;
		}
	}
} // function process_theme($theme)


function process_bundle(){
	global $_cversions;
	// Create a default list of components to bundle.
	$components = array('CoreIcons', 'Email', 'File', 'HTML', 'jquery', 'Menu', 'Page', 'PEAR', 'Session', 'Template', 'Time', 'tinyMCE', 'User');
	$required = array('File', 'Time', 'PEAR');
	
	// I need a list of components, libraries and themes that need bundled together to create an installation package.
	$files = array();
	$dir = ROOT_PDIR . 'components';
	$dh = opendir($dir);
	while(($file = readdir($dh)) !== false){
		if($file{0} == '.') continue;
		if(!is_dir($dir . '/' . $file)) continue;
		if(!is_readable($dir . '/' . $file . '/' . 'component.xml')) continue;
		
		$files[] = $file;
	}
	closedir($dh);
	
	// They should be in alphabetical order...
	sort($files);
	
	// Ask the user what components should be added/removed to the array.
	$q = "[+] Denotes a package set to be added, enter its number to remove it." . NL;
	$q .= "[-] Denotes a package set to be ingored, enter its number to add it." . NL;
	$q .= "[*] Denotes a package that is required by the core.";
	$ans = null;
	while($ans != 'exit'){
		// Add or remove the selected component.
		if($ans && in_array($ans, $required)){
			echo "\033[1;31mDENIED! " . $ans . " is required by the core.\033[0m" . NL;
		}
		elseif($ans && in_array($ans, $components)){
			unset($components[array_search($ans, $components)]);
			echo "\033[1;34mRemoved " . $ans . "\033[0m" . NL;
		}
		elseif($ans){
			$components[] = $ans;
			echo "\033[1;34mAdded " . $ans . "\033[0m" . NL;
		}
		
		$a = array();
		foreach($files as $f){
			if(in_array($f, $required)) $a[$f] = '[*] ' . $f;
			elseif(in_array($f, $components)) $a[$f] = '[+] ' . $f;
			else $a[$f] = '[-] ' . $f;
		}
		
		// Tack on the 'next/quit loop' option.
		$a['exit'] = 'Next, Select Versions';
		$ans = CLI::PromptUser($q, $a);
	}
	
	// Tack on the core.
	$components[] = 'core';
	
	// I have a list of components to bundle, $components.  Ask the user what versions to do.
	//CLI::PromptUser('Press enter to select, (or create), the package versions.', 'text');
	
	$versions = array();
	$ans = null;
	while($ans != 'exit'){
		// Move these to a new array so I can keep track of the version number requested.
		//foreach($components as $c){
		//	$versions[$c] = get_exported_component($c);
		//}
		
		$q = 'Select the component to edit its version, or continue to the next step.';
		$a = array();
		foreach($components as $c){
			$vers = get_exported_component($c);
			if(!$vers['version']){
				// Version doesn't exist... ask the user to create one.
				CLI::PromptUser('Component ' . $c . ' does not have a package, press enter to create one.', 'text');
				process_component($c, true);
				// Reset the cache.
				$_cversions = null;
				continue 2;
			}
			else{
				$a[$c] = $c . ' ' . $vers['version'];
			}
		}
		// Tack on the exit/next option.
		$a['exit'] = 'Next, something else';
		
		$ans = CLI::PromptUser($q, $a);
		if($ans != 'exit'){
			// Reset the cache.
			$_cversions = null;
		}
	}
	
	// @todo Handle the libraries.
	
	// @todo Handle the themes.
	
	
	// Set a temp directory to export everything to.
	$dir = '/tmp/packager-bundle/';
	if(!is_dir($dir)) mkdir($dir);
	if(!is_dir($dir . 'dropins/')) mkdir($dir . 'dropins/');
	if(!is_dir($dir . 'install/')) mkdir($dir . 'install/');
	
	if(!is_dir(ROOT_PDIR . 'exports/bundles')) mkdir(ROOT_PDIR . 'exports/bundles');
	
	// Copy the components to the new directory.
	foreach($components as $c){
		$vers = get_exported_component($c);
		copy($vers['filename'], $dir . 'dropins/' . basename($vers['filename']));
	}
	// @todo Copy libraries
	
	// @todo Copy themes
	
	// Copy the bare files required for installation.
	copy(ROOT_PDIR . 'index.php', $dir . 'index.php');
	//copy(ROOT_PDIR . '.htaccess', $dir . '.htaccess');
	copy(ROOT_PDIR . 'install/index.php', $dir . 'install/index.php');
	//copy(ROOT_PDIR . 'bootstrap.php', $dir . 'bootstrap.php');
	copy(ROOT_PDIR . 'license.txt', $dir . 'license.txt');
	copy(ROOT_PDIR . 'core/InstallArchive.class.php', $dir . 'install/InstallArchive.class.php');
	copy(ROOT_PDIR . 'components/File/File.class.php', $dir . 'install/File.class.php');
	
	// @todo Which compression algorithm should be used?
	// Create the bundle version based on the current core version.
	$coreversion = get_exported_component('core');
	$bundleversion = $coreversion['version'] . '-' . Time::GetCurrent(Time::TIMEZONE_SERVER, 'Y-m-d');
	$tgz = ROOT_PDIR . 'exports/bundles/bundle-' . $bundleversion . '.tgz';
	exec('tar -czf ' . $tgz . ' -C ' . $dir . ' --exclude=.svn --exclude=*~ --exclude=._* .');
	$bundle = $tgz;
	
	echo "Created bundle " . $bundleversion . NL . " as " . $bundle . NL;
	
	// And remove the tmp directory.
	exec('rm -fr "' . $dir . '"');
	
} // function process_bundle


/**
 * Will return an array with the newest exported versions of each component.
 */
function get_exported_components(){
	$c = array();
	$dir = ROOT_PDIR . 'exports/components';
	$dh = opendir($dir);
	while(($file = readdir($dh)) !== false){
		if($file{0} == '.') continue;
		if(is_dir($dir . '/' . $file)) continue;
		// Get the extension type.
		
		if(preg_match('/\.tgz$/', $file)){
			$signed = false;
			$fbase = substr($file, 0, -4);
		}
		elseif(preg_match('/\.tgz\.asc$/', $file)){
			$signed = true;
			$fbase = substr($file, 0, -8);
		}
		else{
			continue;
		}
		
		// Split up the name and the version.
		preg_match('/([^-]*)\-(.*)/', $fbase, $matches);
		$n = $matches[1];
		$v = $matches[2];
		
		// Tack it on.
		if(!isset($c[$n])){
			$c[$n] = array('version' => $v, 'signed' => $signed, 'filename' => $dir . '/' . $file);
		}
		else{
			switch(version_compare($c[$n]['version'], $v)){
				case -1:
					// Existing older, overwrite!
					$c[$n] = array('version' => $v, 'signed' => $signed, 'filename' => $dir . '/' . $file);
					break;
				case 0:
					// Same, check the signed status.
					if($signed) $c[$n] = array('version' => $v, 'signed' => $signed, 'filename' => $dir . '/' . $file);
					break;
				default:
					// Do nothing, current is at a higher version.
			}
		}
	}
	closedir($dh);
	
	return $c;
} // function get_exported_components()

/**
 * Will return an array with the newest exported version of the core.
 */
function get_exported_core(){
	$c = array();
	$dir = ROOT_PDIR . 'exports/core';
	$dh = opendir($dir);
	while(($file = readdir($dh)) !== false){
		if($file{0} == '.') continue;
		if(is_dir($dir . '/' . $file)) continue;
		// Get the extension type.
		
		if(preg_match('/\.tgz$/', $file)){
			$signed = false;
			$fbase = substr($file, 0, -4);
		}
		elseif(preg_match('/\.tgz\.asc$/', $file)){
			$signed = true;
			$fbase = substr($file, 0, -8);
		}
		else{
			continue;
		}
		
		// Split up the name and the version.
		preg_match('/([^-]*)\-(.*)/', $fbase, $matches);
		$n = $matches[1];
		$v = $matches[2];
		
		// Tack it on.
		if(!isset($c[$n])){
			$c[$n] = array('version' => $v, 'signed' => $signed, 'filename' => $dir . '/' . $file);
		}
		else{
			switch(version_compare($c[$n]['version'], $v)){
				case -1:
					// Existing older, overwrite!
					$c[$n] = array('version' => $v, 'signed' => $signed, 'filename' => $dir . '/' . $file);
					break;
				case 0:
					// Same, check the signed status.
					if($signed) $c[$n] = array('version' => $v, 'signed' => $signed, 'filename' => $dir . '/' . $file);
					break;
				default:
					// Do nothing, current is at a higher version.
			}
		}
	}
	closedir($dh);
	
	if(!isset($c['core'])) $c['core'] = array('version' => null, 'signed' => false);
	
	return $c['core'];
} // function get_exported_core()


function get_exported_component($component){
	global $_cversions;
	if(is_null($_cversions)){
		$_cversions = get_exported_components();
		// Tack on the core.
		$_cversions['core'] = get_exported_core();
	}
	
	if(!isset($_cversions[$component])) return array('version' => null, 'signed' => false);
	else return $_cversions[$component];
}


// I need a few variables first about the user...
$packagername = '';
$packageremail = '';

CLI::LoadSettingsFile('packager');

if(!$packagername){
	$packagername = CLI::PromptUser('Please provide your name you wish to use for packaging', 'text-required');
}
if(!$packageremail){
	$packageremail = CLI::Promptuser('Please provide your email you wish to use for packaging.', 'text-required');
}

CLI::SaveSettingsFile('packager', array('packagername', 'packageremail'));


$ans = CLI::PromptUser(
	"What operation do you want to do?", 
	array(
		'component' => 'Manage a Component',
		'theme' => 'Manage a Theme',
		'bundle' => 'Installation Bundle',
		'exit' => 'Exit the script',
	),
	'component'
);

switch($ans){
	case 'component':
		// Open the "component" directory and look for anything with a valid component.xml file.
		$files = array();
		// Tack on the core component.
		$files[] = 'core';
		$dir = ROOT_PDIR . 'components';
		$dh = opendir($dir);
		while(($file = readdir($dh)) !== false){
			if($file{0} == '.') continue;
			if(!is_dir($dir . '/' . $file)) continue;
			if(!is_readable($dir . '/' . $file . '/' . 'component.xml')) continue;
			
			$files[] = $file;
		}
		closedir($dh);
		// They should be in alphabetical order...
		sort($files);
		$ans = CLI::PromptUser("Which component do you want to package/manage?", $files);
		process_component($files[$ans]);
		break; // case 'component'
	case 'theme':
		// Open the "themes" directory and look for anything with a valid theme.xml file.
		$files = array();
		$dir = ROOT_PDIR . 'themes';
		$dh = opendir($dir);
		while(($file = readdir($dh)) !== false){
			if($file{0} == '.') continue;
			if(!is_dir($dir . '/' . $file)) continue;
			if(!is_readable($dir . '/' . $file . '/' . 'theme.xml')) continue;
			
			$files[] = $file;
		}
		closedir($dh);
		// They should be in alphabetical order...
		sort($files);
		$ans = CLI::PromptUser("Which theme do you want to package/manage?", $files);
		process_theme($files[$ans]);
		break; // case 'component'
	case 'bundle':
		// Process a release bundle.
		process_bundle();
	case 'exit':
		echo 'Bye bye' . NL;
		break;
	default:
		echo "Unknown option..." . NL;
		break;
}


