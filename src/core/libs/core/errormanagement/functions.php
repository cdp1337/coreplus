<?php
/**
 * The file that contains all the error management functions
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20131018.0206
 * @package Core\ErrorManagement
 */

namespace Core\ErrorManagement;
use Core\Utilities\Logger;
use Core\Utilities\Logger\LogFile;

/**
 * Handle an exception and report it to the Core system log.
 *
 * If $fatal is set to true, then the fatal error page will be loaded and sent to the browser if in WEB mode,
 * and will exit the script gracefully automatically.
 *
 * @param \Exception $e     The Exception to render out.
 * @param boolean    $fatal Set to true if this exception is fatal.
 */
function exception_handler(\Exception $e, $fatal = false){
	$type  = 'error';
	$class = $fatal ? 'error' : 'warning';
	$code  = get_class($e);

	$errstr  = $e->getMessage();
	$errfile = $e->getFile();
	$errline = $e->getLine();

	// All errors/warnings/notices get logged!
	if($errfile && strpos($errfile, ROOT_PDIR) === 0){
		$details = '[src: ' . '/' . substr($errfile, strlen(ROOT_PDIR)) . ':' . $errline . '] ';
	}
	elseif($errfile){
		$details = '[src: ' . $errfile . ':' . $errline . '] ';
	}
	else{
		$details = '';
	}

	if($e instanceof \DMI_Query_Exception){
		// Tack on the original query into the error log; this can be valuable information to the developer for debugging the issue.
		$details .= '[query: ' . $e->query . '] ';
	}

	try{
		if(!\Core::GetComponent()){
			// If Core isn't loaded, there's not much we can do.
			return;
		}
		
		// Allow external systems to hook into this event.
		\HookHandler::DispatchHook('/core/exception_handler', $e);

		$log = \SystemLogModel::Factory();
		$log->setFromArray([
				'type'    => $type,
				'code'    => $code,
				'message' => $errstr,
				'details' => $details,
			]);
		$log->save();
	}
	catch(\Exception $e){
		// meh, try a traditional log.
		try{
			if(class_exists('Core\\Utilities\\Logger\\LogFile')){
				$log = new LogFile($type);
				$log->write($details . $errstr, $code);
			}
			else{
				error_log($details . $errstr);
			}
		}
		catch(\Exception $e){
			// Really meh now!
		}
	}

	// Display all errors when in development mode.
	if(DEVELOPMENT_MODE){
		// The correct way to handle output is via EXEC_MODE.
		// HOWEVER, since the unit tests emulate a WEB mode so that the scripts behave as they would in the web browser,
		// this is not a reliable test here.
		if(isset($_SERVER['TERM']) || isset($_SERVER['SHELL'])){
			print_error_as_text($class, $code, $e);
		}
		elseif(EXEC_MODE == 'WEB'){
			print_error_as_html($class, $code, $e);
		}
		else{
			print_error_as_text($class, $code, $e);
		}
	}

	// If it's a fatal error and it's not in development mode, simply display a friendly error page instead.
	if($fatal){
		if(EXEC_MODE == 'WEB'){
			require(ROOT_PDIR . 'core/templates/halt_pages/fatal_error.inc.html');
		}
		exit();
	}
}

/**
 * Handle an error and report it to the Core system log.
 *
 * @param      $errno
 * @param      $errstr
 * @param      $errfile
 * @param      $errline
 * @param null $errcontext
 */
function error_handler($errno, $errstr, $errfile, $errline, $errcontext = null){
	$type       = null;
	$fatal      = false;
	$code       = null;
	$class      = '';
	// The exception to this is when error_reporting is explictly set to 0.
	// This happens when a function is called with the "@" error suppressor.
	// In this event, I still want to log the error, but simply do not display it on the screen.
	// Damn fucking "@" operator.....
	$suppressed = (error_reporting() === 0);

	switch($errno){
		case E_ERROR:
		case E_USER_ERROR:
			$fatal = true;
			$type  = 'error';
			$class = 'error';
			$code  = 'PHP Error';
			break;
		case E_WARNING:
		case E_USER_WARNING:
			$type = 'error';
			$class = 'warning';
			$code = 'PHP Warning';
			break;
		case E_NOTICE:
		case E_USER_NOTICE:
			$type = 'info';
			$class = 'info';
			$code = 'PHP Notice';
			break;
		case E_DEPRECATED:
		case E_USER_DEPRECATED:
			$type = 'info';
			$class = 'deprecated';
			$code = 'PHP Deprecated Notice';
			break;
		case E_STRICT:
			$type = 'info';
			$class = 'warning';
			$code = 'PHP Strict Warning';
			$suppressed = true;
			break;
		default:
			$type = 'info';
			$class = 'unknown';
			$code = 'Unknown PHP Error [' . $errno . ']';
			break;
	}

	if($suppressed){
		// Ignore suppressed errors when on production.
		// This is required because PHP < 7.0 has some functions that can only be called with the '@' operator.
		// Such as LDAP binding or many things in Smarty.
		if(!DEVELOPMENT_MODE){
			return;	
		}
		$code .= ' @SUPPRESSED';
	}

	// All errors/warnings/notices get logged!
	if($errfile && strpos($errfile, ROOT_PDIR) === 0){
		$details = '[src: ' . '/' . substr($errfile, strlen(ROOT_PDIR)) . ':' . $errline . '] ';
	}
	elseif($errfile){
		$details = '[src: ' . $errfile . ':' . $errline . '] ';
	}
	else{
		$details = '';
	}

	try{
		if(!\Core::GetComponent()){
			// SQUAK!  Core isn't even loaded yet!
			return;
		}

		// Allow external systems to hook into this event.
		\HookHandler::DispatchHook('/core/error_handler', $code, $errstr);

		$log = \SystemLogModel::Factory();
		$log->setFromArray([
			'type'    => $type,
			'code'    => $code,
			'message' => $details . $errstr
		]);
		$log->save();
	}
	catch(\Exception $e){
		// meh, try a traditional log.
		try{
			if(class_exists('Core\\Utilities\\Logger\\LogFile')){
				$log = new LogFile($type);
				$log->write($details . $errstr, $code);
			}
			else{
				error_log($details . $errstr);
			}
		}
		catch(\Exception $e){
			// Really meh now!
		}
	}

	// Display all errors when in development mode.
	if(DEVELOPMENT_MODE && !$suppressed){
		// The correct way to handle output is via EXEC_MODE.
		// HOWEVER, since the unit tests emulate a WEB mode so that the scripts behave as they would in the web browser,
		// this is not a reliable test here.
		if(isset($_SERVER['TERM']) || isset($_SERVER['SHELL'])){
			print_error_as_text($class, $code, $errstr);
		}
		elseif(EXEC_MODE == 'WEB'){
			print_error_as_html($class, $code, $errstr);
		}
		else{
			print_error_as_text($class, $code, $errstr);
		}
	}

	// If it's a fatal error and it's not in development mode, simply display a friendly error page instead.
	if($fatal){
		if(EXEC_MODE == 'WEB'){
			require(ROOT_PDIR . 'core/templates/halt_pages/fatal_error.inc.html');
		}
		exit();
	}
}


/**
 * Checks for a fatal error, work around for set_error_handler not working on fatal errors.
 */
function check_for_fatal() {
	$error = error_get_last();
	if ( $error["type"] == E_ERROR ){
		$file = $error['file'];
		if(strpos($file, ROOT_PDIR) === 0) $file = '/' . substr($file, strlen(ROOT_PDIR));

		if(file_exists(TMP_DIR . 'lock.message')){
			// If the upgrade had a fatal error, remove the lock file.
			unlink(TMP_DIR . 'lock.message');
		}

		error_handler($error["type"], $error["message"] . ' in ' . $file . ':' . $error['line'], null, null);
	}
}

/**
 * Print an error or exception as HTML.
 *
 * @param string            $class
 * @param string            $code
 * @param string|\Exception $errstr
 */
function print_error_as_html($class, $code, $errstr){
	echo '<div class="message-' . $class . '">' . "\n";

	if($errstr instanceof \Exception){
		$exception = $errstr;
		$errstr = $exception->getMessage();
		$back = $exception->getTrace();
	}
	else{
		$back = debug_backtrace();
	}

	// The header
	echo '<strong>' . $code . ':</strong> ' . $errstr . "\n<br/>\n<br/>";

	// And the stack trace
	echo '<em>Stack Trace</em>' . "\n<br/>" . '<table class="stacktrace">';
	echo '<tr><th>Function/Method</th><th>File Location:Line Number</th></tr>';

	foreach($back as $entry){

		// Parent?  Skip!
		if(
			!isset($entry['file']) &&
			!isset($entry['line']) &&
			isset($entry['function']) &&
			$entry['function'] == 'Core\ErrorManagement\error_handler'
		){
			continue;
		}

		// Self?  Also skip!
		if(isset($entry['function']) && $entry['function'] == 'Core\ErrorManagement\print_error_as_html'){
			continue;
		}

		// The fatal error function?  Skip!
		if(isset($entry['function']) && $entry['function'] == 'Core\ErrorManagement\check_for_fatal'){
			continue;
		}

		// Cleanup the file location
		$file = (isset($entry['file']) ? $entry['file'] : 'unknown');
		if(strpos($file, ROOT_PDIR) === 0){
			// Trim off the prefix ROOT_PDIR, I don't need that!
			$file = '/' . substr($file, strlen(ROOT_PDIR));
		}

		// Cleanup the file line number
		$line = isset($entry['line']) ? $entry['line'] : 'N/A';

		if(isset($entry['class'])){
			$linecode = $entry['class'] . $entry['type'] . $entry['function'] . '()';
		}
		elseif(isset($entry['function']) && $entry['function'] == 'Core\ErrorManagement\error_handler'){
			$linecode = '****';
		}
		elseif(isset($entry['function'])){
			$linecode = $entry['function'] . '()';
		}
		else{
			$linecode = 'Unknown!?!';
		}

		echo '<tr><td>' . $linecode . '</td><td>' . $file . ':' . $line . '</td></tr>';
	}
	echo '</table>';
	echo '</div>';
}

/**
 * Print an error or exception as text.
 *
 * @param string            $class
 * @param string            $code
 * @param string|\Exception $errstr
 */
function print_error_as_text($class, $code, $errstr){
	echo get_error_as_text($code, $errstr);

	// And to the stderr
	$stderr = fopen('php://stderr', 'w');
	fwrite($stderr, '[' . $code . '] ' . $errstr . "\n");
	fclose($stderr);
}

/**
 * Internal function to calculate the backtrace for errors,
 * (omitting the things higher than the error itself, in theory).
 * 
 * This is only needed for errors, so it will be unecessary on PHP 7.
 * 
 * @return array
 */
function get_backtrace(){
	$back = debug_backtrace();
	$bfound = false;

	// This requires a bit of work to figure out where the error started,
	// as this script will have been called by several others
	// and errors do not provide the same level of analytics as Exceptions to.
	foreach($back as $k => $dat){
		// Run through each backtrace level starting with the latest one until I find this file.
		// Everything else above that stack, (in theory at least), did not cause the error.
		// The caveat to this of course if if there's an error in this file!
		// When found, record that position and then splice the returning output array with the latest instance of functions.php.
		if(isset($dat['file']) && $dat['file'] == ROOT_PDIR . 'core/libs/core/errormanagement/functions.php'){
			$bfound = $k;
		}
		// Or this one.
		if(isset($dat['function']) && $dat['function'] == 'Core\ErrorManagement\error_handler'){
			$bfound = $k;
		}
	}

	if($bfound !== false){
		$back = array_splice($back, $bfound + 1);
	}
	
	return $back;
}

function standardize_backtrace($array){
	$out = [];
	foreach($array as $dat){
		$line = [
			'file' => null,
		    'line' => null,
		    'call' => null,
		    'args' => [],
		];
		
		if(isset($dat['file']) && strpos($dat['file'], ROOT_PDIR) === 0){
			// Cleanup the file location by
			// trimming off the prefix ROOT_PDIR, I don't need that!
			$line['file'] = '/' . substr($dat['file'], strlen(ROOT_PDIR));
		}
		elseif(isset($dat['file'])){
			// it's present, but doesn't contain the ROOT directory, just copy as-is.
			$line['file'] = $dat['file'];
		}
		else{
			$line['file'] = 'unknown';
		}
		
		$line['line'] = isset($dat['line']) ? $dat['line'] : 'N/A';

		if(isset($dat['class'])){
			$line['call'] = $dat['class'] . $dat['type'] . $dat['function'];
		}
		elseif(isset($dat['function'])){
			$line['call'] = $dat['function'];
		}
		else{
			$line['call'] = 'unknown';
		}
		
		if(isset($dat['args'])){
			// Convert the arguments to something flat.
			foreach($dat['args'] as $k => $a){
				if(is_string($a)){
					$line['args'][] = "'" . $a . "'";
				}
				elseif($a === true){
					$line['args'][] = 'TRUE';
				}
				elseif($a === false){
					$line['args'][] = 'FALSE';
				}
				elseif($a === null){
					$line['args'][] = 'NULL';
				}
				elseif(is_array($a)){
					$line['args'][] = 'Array {' . sizeof($a) . '}';
				}
				elseif(is_object($a)){
					$line['args'][] = 'Object {' . get_class($a) . '}';
				}
				else{
					// ???
					$line['args'][] = $a;
				}
			}
		}
		
		// Append to stack
		$out[] = $line;
	}
	
	return $out;
}

/**
 * Format an error to a plain text string.
 * 
 * @param $code
 * @param $errstr
 * 
 * @return string
 */
function get_error_as_text($code, $errstr){

	if($errstr instanceof \Exception){
		$exception = $errstr;
		$errstr = $exception->getMessage();
		$back = $exception->getTrace();
	}
	else{
		$back = get_backtrace();
	}
	
	// Cleanup these back traces.
	$back = standardize_backtrace($back);
	
	$out = '';

	// The header
	$out .= '### [' . $code . '] ' . $errstr . "\n\n";

	// And the stack trace
	// I need to render the data to a "buffer" so I know the positions of everything.

	$lines = [];
	$maxlength1 = $maxlength2 = 0;
	foreach($back as $entry){
		$linecode = $entry['call'] . '(' . implode(', ', $entry['args']) . ')';
		$file = $entry['file'] . ':' . $entry['line'];

		$lines[] = [
			'code' => $linecode,
			'file' => $file,
		];

		$maxlength1 = max($maxlength1, strlen($linecode));
		$maxlength2 = max($maxlength2, strlen($file));
	}

	// Now I know the sizes of the table.
	// This jumble of code will create a table in the shell using ASCII characters.
	$borderheader = '+' . str_repeat('-', $maxlength1 + $maxlength2) . '+';
	$borderinner = str_repeat('-', $maxlength1+1) . '+' . str_repeat('-', $maxlength2+1);
	//$out .= $borderheader . "\n";
	//$out .= '| ' . str_pad('STACK TRACE', $maxlength1 + $maxlength2-1, ' ', STR_PAD_BOTH) . '|' . "\n";
	//$out .= $borderheader . "\n";

	$padding1 = max(0, $maxlength1-15);
	$padding2 = max(0, $maxlength2-13);
	$out .= 'Function/Method' . str_repeat(' ', $padding1) . ' | File Location' . str_repeat(' ', $padding2) . "\n";
	$out .= $borderinner . "\n";

	foreach($lines as $entry){
		$padding1 = max(0, $maxlength1-strlen($entry['code']));
		$padding2 = max(0, $maxlength2-strlen($entry['file']));

		$out .= $entry['code'] . str_repeat(' ', $padding1) . ' | ' . $entry['file'] . str_repeat(' ', $padding2) . "\n";
	}
	//$out .= $borderinner . "\n";
	
	return $out;
}