<?php
/**
 * This is a basic class to provide some basic CLI functionality.
 *
 * @package Core
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2017  Charlie Powell
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

namespace Core\CLI;

class CLI {
	
	/**
	 * Prompt the user a question and return the result.
	 *
	 * @param string       $question The question to prompt to the user.
	 * @param array|string $answers  What answers to provide to the user.
	 *                               array           - Will prompt the user with the value of each pair, returning the key.
	 *                               "boolean"       - Will ask for a yes/no response and return true/false.
	 *                               "text"          - Open-ended text input, user can type in anything and that input is returned.
	 *                               "text-required" - Open-ended text input, user can type in anything (non-blank), and that value is returned.
	 * @param string|bool  $default  string The default answer if the user simply presses "enter". [optional]
	 *
	 * @throws \Exception
	 * @return bool|string
	 */
	public static function PromptUser($question, $answers, $default = false) {
		$isanswered = false;
		while (!$isanswered) {
			echo NL . $question . NL;
			$extras = [];

			if (is_array($answers)) {
				$answerhash = array();
				$x          = 0;
				foreach ($answers as $a => $q) {
					if (($a === 'exit')) { // && ($x+1 == sizeof($answers))){
						// This is a 'special' action, so it gets a special key.
						$answerhash['x'] = $a;
						echo TAB . " x - $q" . NL;
						$extras[] = 'x';
					}
					elseif($a === 'quit'){
						$answerhash['q'] = $a;
						echo TAB . " q - $q" . NL;
						$extras[] = 'q';
					}
					elseif($a === 'menu'){
						$answerhash['m'] = $a;
						echo TAB . " m - $q" . NL;
						$extras[] = 'm';
					}
					elseif ($a === 'save') {
						$answerhash['s'] = $a;
						echo TAB . " s - $q" . NL;
						$extras[] = 's';
					}
					else {
						$x++;
						$answerhash[$x] = $a;
						$indent         = ($x < 10) ? ' ' : '';
						if ($default !== false && $default == $a) {
							echo TAB . $indent . "$x*- $q (default)" . NL;
						}
						else {
							echo TAB . $indent . "$x - $q" . NL;
						}
					}
				}

				// Print the "enter a number 1-10..." text.
				if ($x == 1 && !sizeof($extras)){
					echo NL . '(Enter 1 to continue) ';
				}
				elseif($x > 1 && !sizeof($extras)){
					echo NL . "(Enter a number, 1-$x) ";
				}
				else{
					$extras = array_merge(['1-' . $x], $extras);
					echo NL . '(Enter ';
					$last = null;
					while(($e = array_shift($extras))){
						if($last){
							echo $last . ', ';
						}
						$last = $e;
					}
					echo 'or ' . $last . ') ';
				}

				// Read the response.
				$line = strtolower(trim(fgets(STDIN)));
				echo NL;

				// Maybe there's a default.
				if ($line == '' && $default !== false) {
					return $default;
				}

				if (!isset($answerhash[$line])) {
					echo "Invalid Response!" . NL . NL;
					sleep(1.5);
					continue;
				}

				return $answerhash[$line];
			}
			else {
				switch (strtolower($answers)) {
					case 'boolean':
					case 'bool':
						echo "(enter y for yes, n for no.) ";
						$line = strtolower(trim(fgets(STDIN)));
						echo NL;
						// Maybe there's a default.
						if ($line == '' && $default !== false) {
							return $default;
						}
						elseif ($line == 'y' || $line == 'yes') {
							return true;
						}
						elseif ($line == 'n' || $line == 'no') {
							return false;
						}
						else {
							echo "Invalid Response!" . NL . NL;
							sleep(1.5);
							continue;
						}
						break;
					case 'text':
						if ($default !== false) {
							echo "Press enter for [" . $default . "]" . NL;
						}
						$line = trim(fgets(STDIN));
						echo NL;

						if ($line == '' && $default !== false) {
							return $default;
						}
						else {
							return $line;
						}
						break;
					case 'text-required':
						if ($default !== false) {
							echo "Press enter for [" . $default . "]" . NL;
						}
						$line = trim(fgets(STDIN));
						echo NL;

						if ($line == '' && $default !== false) {
							return $default;
						}
						elseif ($line != '') {
							return $line;
						}
						else {
							echo "Invalid Response!" . NL . NL;
							sleep(1.5);
							continue;
						}
						break;
					case 'textarea':
						// This requires a bit of app-trickery.  
						// I need to pass any "default" data into a text file in /tmp, then edit that and read the file afterwards.
						echo '(Press enter to open with ' . basename($_SERVER['EDITOR']) . '.  Save and close when done.)';
						fgets(STDIN);

						$file = "/tmp/cae2-cli-textarea-" . \Core::RandomHex(4) . '.tmp';
						file_put_contents($file, $default);
						system($_SERVER['EDITOR'] . " " . $file . " > `tty`");
						// And read back in that file.
						$data = file_get_contents($file);
						// Remove the file from the filesystem, no need for clutter.
						unlink($file);
						return $data;
					default:
						throw new \Exception('Unsupported answer choice [' . strtolower($answers) . '], please ensure it is either an array of options, "boolean", "text", "text-required", or "textarea"!');
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
	public static function RequireEditor() {
		global $previous_editor;

		// First, check the editor in the "session" file.
		\Core\CLI\CLI::LoadSettingsFile('editor');
		if (isset($previous_editor)) $_SERVER['EDITOR'] = $previous_editor;


		if (!isset($_SERVER['EDITOR']) || $_SERVER['EDITOR'] == '') {
			// I need to assemble a list of editors currently on the system.
			$opts    = array();
			$default = false;
			if (($loc = trim(`which vi`))) $opts[$loc] = $loc;
			if (($loc = trim(`which vim`))) $opts[$loc] = $loc;
			if (($loc = trim(`which emacs`))) $opts[$loc] = $loc;
			if (($loc = trim(`which nano`))) {
				$opts[$loc] = $loc;
				$default    = $loc;
			}

			$_SERVER['EDITOR'] = \Core\CLI\CLI::PromptUser(
				'Which editor do you want to use for editing text files?  If you are unsure, simply press enter if there is a default option.',
				$opts,
				$default
			);

			// And remember this option.
			$previous_editor = $_SERVER['EDITOR'];
			\Core\CLI\CLI::SaveSettingsFile('editor', array('previous_editor'));
		}
	}

	/**
	 * Print a stylized header to stdout
	 * 
	 * @param string $line   The header string to output
	 * @param int    $maxlen Maximum length of the line
	 */
	public static function PrintHeader($line, $maxlen = 90) {
		$nl = (EXEC_MODE == 'WEB') ? NL . '<br/>' : NL;
		
		echo COLOR_LINE;
		echo "| " . $nl;
		echo "+" . str_repeat('=', $maxlen-1) . $nl;
		echo "| " . COLOR_RESET . COLOR_HEADER;

		// Make this text centered.
		if(strlen($line) < $maxlen){
			echo str_repeat(NBSP, ($maxlen - strlen($line)) / 2);
		}

		echo $line . $nl . COLOR_RESET . COLOR_LINE;
		echo "+" . str_repeat('=', $maxlen-1);
		echo COLOR_RESET . $nl;

		if(EXEC_MODE == 'WEB'){
			echo '<!--CLI-DATA:HEADER=' . $line . '-->';
		}
		
		if(self::_FlushRequired()){
			ob_flush();
			flush();
		}
	}

	/**
	 * Print a single line or multiple lines of text to the screen or console.
	 *
	 * @param string|array $line  Line (or array of lines) to output
	 * @param string       $color Colour to render the output with
	 */
	public static function PrintLine($line, $color = COLOR_NORMAL) {
		if(is_array($line)){
			foreach($line as $l){
				self::PrintLine($l, $color);
			}
		}
		elseif(strpos($line, "\n") !== false){
			// This is a multi-line string, re-run this script on each line for proper formatting.
			$lines = explode("\n", $line);
			foreach($lines as $l){
				self::PrintLine($l, $color);
			}
		}
		else{
			$nl = (EXEC_MODE == 'WEB') ? NL . '<br/>' : NL;

			echo COLOR_LINE . '| ' . COLOR_RESET . $color . $line . COLOR_RESET . $nl;
			
			if(EXEC_MODE == 'WEB'){
				switch($color){
					case COLOR_DEBUG:
						echo '<!--CLI-DATA:LINE=' . $line . ';TYPE=debug-->';
						break;
					case COLOR_ERROR:
						echo '<!--CLI-DATA:LINE=' . $line . ';TYPE=error-->';
						break;
					case COLOR_WARNING:
						echo '<!--CLI-DATA:LINE=' . $line . ';TYPE=warning-->';
						break;
					case COLOR_SUCCESS:
						echo '<!--CLI-DATA:LINE=' . $line . ';TYPE=success-->';
						break;
					default:
						echo '<!--CLI-DATA:LINE=' . $line . '-->';
						break;
				}
			}
			
			if(self::_FlushRequired()){
				ob_flush();
				flush();
			}
		}
	}

	/**
	 * Print an error to stdout
	 * 
	 * @param string $line
	 */
	public static function PrintError($line) {
		self::PrintLine($line, COLOR_ERROR);
	}

	/**
	 * Print a success message to stdout
	 * 
	 * @param string $line
	 */
	public static function PrintSuccess($line) {
		self::PrintLine($line, COLOR_SUCCESS);
	}

	/**
	 * Print a warning to stdout
	 * 
	 * @param string $line
	 */
	public static function PrintWarning($line) {
		self::PrintLine($line, COLOR_WARNING);
	}

	/**
	 * Print a debug message to stdout
	 * 
	 * @param string $line
	 */
	public static function PrintDebug($line) {
		self::PrintLine($line, COLOR_DEBUG);
	}

	/**
	 * Print an action start line
	 * 
	 * This is usually rendered in the format of 
	 * 
	 * ```
	 * Performing Some Action ...           [ OK ]
	 * ```
	 * 
	 * @param        $line
	 * @param int    $maxlen
	 * @param string $suffix
	 */
	public static function PrintActionStart($line, $maxlen = 90, $suffix = '...'){
		$flen = strlen($line) + strlen($suffix) + 8;
		echo "$line..." . str_repeat(NBSP, max($maxlen - $flen, 1));
		
		if(EXEC_MODE == 'WEB'){
			echo '<!--CLI-DATA:LINE=' . $line . '-->';	
		}

		if(self::_FlushRequired()){
			ob_flush();
			flush();
		}
	}

	/**
	 * Print the result of a previous "ActionStart" command.
	 * 
	 * If the param is TRUE, 1, or 'OK', then '[  OK  ]' is rendered.
	 * If the param is FALSE, 0, or 'fail', then '[  !!  ]' is rendered.
	 * If the param is 'skip', then '[ SKIP ]' is rendered.
	 * 
	 * @param string|int|bool $status
	 */
	public static function PrintActionStatus($status){
		$nl = (EXEC_MODE == 'WEB') ? NL . '<br/>' : NL;

		if($status === true){
			$status = 'ok';
		}
		elseif($status === false){
			$status = 'fail';
		}

		switch($status){
			case 1:
			case 'OK':
			case 'ok':
				echo COLOR_SUCCESS . "[  OK  ]" . COLOR_RESET;
				break;

			case 'skip':
			case 'SKIP':
				echo '[ SKIP ]';
				break;

			case 0:
			case 'fail':
				echo COLOR_ERROR . "[  !!  ]" . COLOR_RESET;
				break;

			default:
				echo "[  ??  ]";
		}

		echo $nl;

		if(self::_FlushRequired()){
			ob_flush();
			flush();
		}
	}

	/**
	 * Print a progress bar and/or a status update on an existing progress bar.
	 * 
	 * If an absolute value is set, then the bar is jumped to that new value.
	 * Any absolute value less than the current will reset the progress bar to a new line.
	 * 
	 * A relative percentage can be set via the prefix '+'.
	 * For example, '+1' will bump the progress bar up by 1%.
	 * 
	 * @param $percent
	 */
	public static function PrintProgressBar($percent) {
		static $last = 0;
		static $pos  = -1;
		
		// Allow "+1" to be sent, to indicate 1 percent more.
		if(strpos($percent, '+') === 0){
			$percent = substr($percent, 1);
			$percent += $last;
		}

		// This progress bar displays up to 90 characters.
		// (Which taken into account the 3 characters before, that makes it 87 in length.)
		$newPos = ceil($percent / 100 * 87);

		// Allow the bar to be reset on a new pass too!
		if($percent < $last){
			$percent = 0;
			$pos = -1;
		}

		if($pos === -1){
			if(EXEC_MODE == 'WEB'){
				echo '<!--CLI-DATA:PROGRESSBAR=0-->';
			}
			else{
				echo COLOR_LINE . '| ' . COLOR_RESET . COLOR_SUCCESS . '>' . COLOR_RESET;	
			}
			
			$pos++;
		}
		
		// Echo the change in position!
		if($newPos > $pos && $newPos <= 87){
			if(EXEC_MODE == 'WEB'){
				echo '<!--CLI-DATA:PROGRESSBAR=' . (int)$percent . '-->';
			}
			else{
				echo COLOR_SUCCESS . str_repeat('=', $newPos - $pos) . COLOR_RESET;	
			}
		}

		if($newPos == 87 && $pos != $newPos){
			// FIN!
			if(EXEC_MODE != 'WEB'){
				echo NL;	
			}
		}
		
		// Record this new position for the next run.
		$pos = $newPos;
		$last = $percent;

		if(self::_FlushRequired()){
			ob_flush();
			flush();
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
	 *
	 * @return boolean Status of the load attempt.
	 */
	public static function LoadSettingsFile($filebasename) {
		// @todo Is there a better way of getting the home directory of the user?
		$home = $_SERVER['HOME'];
		$dir  = $home . '/.cae2/';
		$file = $dir . $filebasename . '.php';

		if (!is_dir($dir)) return false;
		if (!is_readable($file)) return false;

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
	public static function SaveSettingsFile($filebasename, $variables) {
		// @todo Is there a better way of getting the home directory of the user?
		$home = $_SERVER['HOME'];
		$dir  = $home . '/.cae2/';
		$file = $dir . $filebasename . '.php';

		$out = '<?php' . NL;

		foreach ($variables as $name) {
			// I need to get the variable in the first place...
			global ${$name};

			// The script will need to global it as well.
			$out .= NL . 'global $' . $name . ';' . NL;

			// The common line-beginnings of all variable types.
			$out .= '$' . $name . ' = ';

			if (is_null(${$name})) $out .= 'NULL';
			if (is_numeric(${$name})) $out .= ${$name};
			if (is_string(${$name})) $out .= '"' . str_replace('"', '\\"', ${$name}) . '"';
			if (${$name} === true) $out .= 'TRUE';
			if (${$name} === false) $out .= 'FALSE';
			if (is_array(${$name}) || is_object(${$name})) $out .= 'unserialize("' . str_replace('"', '\\"', serialize(${$name})) . '")';

			// And the common line-endings for all variable types.
			$out .= ';' . NL;
		}

		// This output will be saved to the file.
		if (!is_dir($dir)) mkdir($dir);
		file_put_contents($file, $out);
	}

	/**
	 * Get if an obflush is required to send output to the end browser.
	 * 
	 * If another output buffer is active other than the primary one, do not flush anything!
	 */
	private static function _FlushRequired(){
		if(EXEC_MODE != 'WEB'){
			// ob_flush is only required when in web.
			return false;
		}
		
		$l = ob_get_level();
		if($l == 0){
			// No output buffers present!  YAY!
			return true;
		}
		
		if($l == 1 && ini_get('output_buffering')){
			// 1 is also allowed if output buffering is turned on.
			return true;
		}
		
		// Otherwise?
		return false;
	}
}