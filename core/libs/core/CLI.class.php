<?php
/**
 * [PAGE DESCRIPTION HERE]
 *
 * @package Core Plus\Core
 * @author Charlie Powell <powellc@powelltechs.com>
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

/**
 * This is a basic class to provide some basic CLI functionality.
 * 
 * @author powellc
 */
class CLI{
	/**
	 * Prompt the user a question and return the result.
	 * 
	 * @param $question string The question to prompt to the user.
	 * @param $answers array | string What answers to provide to the user.
	 *                 array			- Will prompt the user with the value of each pair, returning the key.
	 *                 "boolean"		- Will ask for a yes/no response and return true/false.
	 *                 "text"			- Open-ended text input, user can type in anything and that input is returned.
	 *                 "text-required"	- Open-ended text input, user can type in anything (non-blank), and that value is returned.
	 * @param $default string The default answer if the user simply presses "enter". [optional]
	 */
	public static function PromptUser($question, $answers, $default = false){
		$isanswered = false;
		while(!$isanswered){
			echo NL . $question . NL;
			$hasexit = false;
			$hassave = false;
			
			if(is_array($answers)){
				$answerhash = array();
				$x=0;
				foreach($answers as $a => $q){
					if(($a === 'exit')){// && ($x+1 == sizeof($answers))){
						// This is a 'special' action, so it gets a special key.
						$answerhash['x'] = $a;
						echo TAB . " x - $q" . NL;
						$hasexit = true;
					}
					elseif($a === 'save'){
						$answerhash['s'] = $a;
						echo TAB . " s - $q" . NL;
						$hassave = true;
					}
					else{
						$x++;
						$answerhash[$x] = $a;
						$indent = ($x < 10)? ' ' : '';
						if($default !== false && $default == $a){
							echo TAB . $indent . "$x*- $q (default)" . NL;
						}
						else{
							echo TAB . $indent . "$x - $q" . NL;
						}
					}
				}
				
				// Print the "enter a number 1-10..." text.
				if($x == 1) echo NL . '(Enter 1 to continue';
				else echo NL . "(Enter a number, 1-$x";
				
				if($hassave) echo " or 's'";
				if($hasexit) echo " or 'x'";
				
				echo ") ";
				
				// Read the response.
				$line = strtolower(trim(fgets(STDIN)));
				echo NL;
				
				// Maybe there's a default.
				if($line == '' && $default !== false){
					return $default;
				}
				
				if(!isset($answerhash[$line])){
					echo "Invalid Response!" . NL . NL;
					sleep(1.5);
					continue; 
				}
				
				return $answerhash[$line];
			}
			else{
				switch(strtolower($answers)){
					case 'boolean':
						echo "(enter y for yes, n for no.) ";
						$line = strtolower(trim(fgets(STDIN)));
						echo NL;
						// Maybe there's a default.
						if($line == '' && $default !== false){
							return $default;
						}
						elseif($line == 'y' || $line == 'yes'){
							return true;
						}
						elseif($line == 'n' || $line == 'no'){
							return false;
						}
						else{
							echo "Invalid Response!" . NL . NL;
							sleep(1.5);
							continue;
						}
						break;
					case 'text':
						if($default !== false){
							echo "Press enter for [" . $default . "]" . NL;
						}
						$line = trim(fgets(STDIN));
						echo NL;
						
						if($line == '' && $default !== false){
							return $default;
						}
						else{
							return $line;
						}
						break;
					case 'text-required':
						if($default !== false){
							echo "Press enter for [" . $default . "]" . NL;
						}
						$line = trim(fgets(STDIN));
						echo NL;
						
						if($line == '' && $default !== false){
							return $default;
						}
						elseif($line != ''){
							return $line;
						}
						else{
							echo "Invalid Response!" . NL . NL;
							sleep(1.5);
							continue;
						}
						break;
					case 'textarea':
						// This requires a bit of app-trickery.  
						// I need to pass any "default" data into a text file in /tmp, then edit that and read the file afterwards.
						$file = "/tmp/cae2-cli-textarea-" . Core::RandomHex(4) . '.tmp';
						file_put_contents($file, $default);
						//echo "(Press enter to open the editor.)";
						//fgets(STDIN);
						system($_SERVER['EDITOR'] . " " . $file . " > `tty`");
						// And read back in that file.
						$data = file_get_contents($file);
						// Remove the file from the filesystem, no need for clutter.
						unlink($file);
						return $data;
				}
			}
	
		}
	}
	
	/**
	 * Set the 'EDITOR' variable to be set.
	 * This is a linux-specific thing that svn shares also.
	 * 
	 * The user will usually set their preferred EDITOR, be it vi/vim, emacs or nano.
	 * If they didn't, ask the user for their choice.
	 */
	public static function RequireEditor(){
		global $previous_editor;

		// First, check the editor in the "session" file.
		CLI::LoadSettingsFile('editor');
		if(isset($previous_editor)) $_SERVER['EDITOR'] = $previous_editor;


		if(!isset($_SERVER['EDITOR']) || $_SERVER['EDITOR'] == ''){
			// I need to assemble a list of editors currently on the system.
			$opts = array();
			$default = false;
			if(($loc = trim(`which vi`))) $opts[$loc] = $loc;
			if(($loc = trim(`which vim`))) $opts[$loc] = $loc;
			if(($loc = trim(`which emacs`))) $opts[$loc] = $loc;
			if(($loc = trim(`which nano`))){
				$opts[$loc] = $loc;
				$default = $loc;
			}
			
			$_SERVER['EDITOR'] = CLI::PromptUser(
				'Which editor do you want to use for editing text files?  If you are unsure, simply press enter if there is a default option.',
				$opts,
				$default
			);

			// And remember this option.
			$previous_editor = $_SERVER['EDITOR'];
			CLI::SaveSettingsFile('editor', array('previous_editor'));
		}
	}
	
	
	/**
	 * This can be used to load a saved session from the user's home directory.
	 * It's useful for saving common per-user data across different executions.
	 * 
	 * Since CLI scripts are per-user and localhost only, these can, and should be, saved locally.
	 * 
	 * Note, no error is generated if file doesn't exist, but false is returned instead of true.
	 * 
	 * @param string $filebasename The basename of the settings file, the .php is added automatically.
	 * @return boolean Status of the load attempt.
	 */
	public static function LoadSettingsFile($filebasename){
		// @todo Is there a better way of getting the home directory of the user?
		$home = $_SERVER['HOME'];
		$dir = $home . '/.cae2/';
		$file = $dir . $filebasename . '.php';
		
		if(!is_dir($dir)) return false;
		if(!is_readable($file)) return false;
		
		// w00t, try to load this file.
		include_once($file);
		return true;
	}
	
	/**
	 * Save the user settings back to the settings file.
	 * Any parameter given after the first is written to the settings file.
	 * 
	 * @param string $filebasename The basename of the settings file, the .php is added automatically.
	 * @param ... Any variables to save.
	 */
	public static function SaveSettingsFile($filebasename, $variables){
		// @todo Is there a better way of getting the home directory of the user?
		$home = $_SERVER['HOME'];
		$dir = $home . '/.cae2/';
		$file = $dir . $filebasename . '.php';
		
		$out = '<?php' . NL;
		
		foreach($variables as $name){
			// I need to get the variable in the first place...
			global ${$name};
			
			// The script will need to global it as well.
			$out .= NL . 'global $' . $name . ';' . NL;
			
			// The common line-beginnings of all variable types.
			$out .= '$' . $name . ' = ';
			
			if(is_null(${$name})) $out .= 'NULL';
			if(is_numeric(${$name})) $out .= ${$name};
			if(is_string(${$name})) $out .= '"' . str_replace('"', '\\"', ${$name}) . '"';
			if(${$name} === true) $out .= 'TRUE';
			if(${$name} === false) $out .= 'FALSE';
			if(is_array(${$name}) || is_object(${$name})) $out .= 'unserialize("' . str_replace('"', '\\"', serialize(${$name})) . '")';
			
			// And the common line-endings for all variable types.
			$out .= ';' . NL;
		}
		
		// This output will be saved to the file.
		if(!is_dir($dir)) mkdir($dir);
		file_put_contents($file, $out);
	}

}