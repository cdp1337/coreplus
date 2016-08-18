<?php
/**
 * Class file for the controller ZabbixAgentController
 *
 * @package Zabbix Agent
 * @author Charlie Powell <charlie@evalagency.com>
 */
class ZabbixAgentController extends Controller_2_1 {
	
	public function test(){
		if(!DEVELOPMENT_MODE){
			return View::ERROR_ACCESSDENIED;
		}
		
		$view = $this->getView();
		$view->mode = View::MODE_NOOUTPUT;
		$view->contenttype = View::CTYPE_PLAIN;
		$view->render();
		
		echo "Sending component discovery...\n";
		self::_DiscoverComponents();
		echo "\n\n";
		
		echo "Sending status...\n";
		self::_SendStatus();
	}
	
	public static function _DiscoverComponents(){
		$server = ConfigHandler::Get('/zabbixagent/server');
		$port = ConfigHandler::Get('/zabbixagent/port');
		$host = ConfigHandler::Get('/zabbixagent/host');
		
		if(!$host){
			// Default to the server's hostname.
			$host = HOST;
		}

		if(!$server){
			echo "Zabbix monitoring server is not configured!\nPlease set that in order to enable active monitoring.";
			return true;
		}
		
		// Pull the versions installed
		$data = [];
		$components = Core::GetComponents();
		foreach($components as $c){
			/** @var Component_2_1 $c */
			$data[] = [
				'{#KEY}' => $c->getKeyName(),
				'{#NAME}' => $c->getName(),
			];
		}

		$cmd = "/usr/bin/zabbix_sender -vv -s " . escapeshellarg($host) . " -z $server -p $port -k coreplus.discovery[components] -o ";
		$cmd .= escapeshellarg(json_encode(['data' => $data]));

		$outputPipes = [];
		$descriptorspec = [
			0 => array("pipe", "r"),  // stdin
			1 => array("pipe", "w"),  // stdout
			2 => array("pipe", "w"),  // stderr
		];
		$process = proc_open($cmd, $descriptorspec, $outputPipes);

		$stdout = stream_get_contents($outputPipes[1]);
		fclose($outputPipes[1]);

		$stderr = stream_get_contents($outputPipes[2]);
		fclose($outputPipes[2]);

		// Close the connection and read the output result.
		$result = proc_close($process);
		
		// Print the status returned by Zabbix to the cron log.
		echo $stderr;
		return ($result == 0);
	}
	
	public static function _SendStatus(){
		
		$server = ConfigHandler::Get('/zabbixagent/server');
		$port = ConfigHandler::Get('/zabbixagent/port');
		$host = ConfigHandler::Get('/zabbixagent/host');

		if(!$host){
			// Default to the server's hostname.
			$host = HOST;
		}
		
		if(!$server){
			echo "Zabbix monitoring server is not configured!\nPlease set that in order to enable active monitoring.";
			return true;
		}
		
		$keys = [
			'coreplus.status' => '1', // This doesn't change, but is used to support triggers. (0 views does not mean bad).
			'coreplus.views.ok' => 0,
			'coreplus.views.bad' => 0,
			'coreplus.views.notfound' => 0,
			'coreplus.db.reads' => 0,
			'coreplus.db.writes' => 0,
			'coreplus.render.time[min]' => null,
			'coreplus.render.time[avg]' => null,
			'coreplus.render.time[max]' => null,
			'coreplus.render.time[mean,25]' => null,
			'coreplus.render.time[mean,75]' => null,
			'coreplus.logs.info' => 0,
			'coreplus.logs.error' => 0,
			'coreplus.logs.security' => 0,
		];
		$rawTimes = [];
		
		$date = new Core\Date\DateTime();
		$date->modify('-1 minute');
		$minutely = $date->format('U');
		
		// Pull the user activity logs for the last minute
		$raw = UserActivityModel::FindRaw(['datetime >= ' . $minutely]);
		
		foreach($raw as $rec){
			switch($rec['status']){
				case View::ERROR_NOERROR:  // Request OK
					$keys['coreplus.views.ok']++;
					break;
				case View::ERROR_OTHER:
				case View::ERROR_BADREQUEST:                  // Section 10.4.1: Bad Request
				case View::ERROR_UNAUTHORIZED:                // Section 10.4.2: Unauthorized
				case View::ERROR_PAYMENTREQUIRED:             // Section 10.4.3: Payment Required
				case View::ERROR_ACCESSDENIED:                // Section 10.4.4: Forbidden
				case View::ERROR_METHODNOTALLOWED:            // Section 10.4.6: Method Not Allowed
				case View::ERROR_NOTACCEPTABLE:               // Section 10.4.7: Not Acceptable
				case View::ERROR_PROXYAUTHENTICATIONREQUIRED: // Section 10.4.8: Proxy Authentication Required
				case View::ERROR_REQUESTTIMEOUT:              // Section 10.4.9: Request Time-out
				case View::ERROR_CONFLICT:                    // Section 10.4.10: Conflict
				case View::ERROR_GONE:                        // Section 10.4.11: Gone
				case View::ERROR_LENGTHREQUIRED:              // Section 10.4.12: Length Required
				case View::ERROR_PRECONDITIONFAILED:          // Section 10.4.13: Precondition Failed
				case View::ERROR_ENTITYTOOLARGE:              // Section 10.4.14: Request Entity Too Large
				case View::ERROR_URITOOLARGE:                 // Section 10.4.15: Request-URI Too Large
				case View::ERROR_UNSUPPORTEDMEDIATYPE:        // Section 10.4.16: Unsupported Media Type
				case View::ERROR_RANGENOTSATISFIABLE:         // Section 10.4.17: Requested range not satisfiable
				case View::ERROR_EXPECTATIONFAILED:           // Section 10.4.18: Expectation Failed
				case View::ERROR_SERVERERROR:                 // Generic server error
					$keys['coreplus.views.bad']++;
					break;
				case View::ERROR_NOTFOUND:                    // Section 10.4.5: Not Found
					$keys['coreplus.views.notfound']++;
					break;
			}
			
			if($keys['coreplus.render.time[min]'] === null || $keys['coreplus.render.time[min]'] > $rec['processing_time']){
				$keys['coreplus.render.time[min]'] = $rec['processing_time'];
			}
			if($keys['coreplus.render.time[max]'] === null || $keys['coreplus.render.time[max]'] < $rec['processing_time']){
				$keys['coreplus.render.time[max]'] = $rec['processing_time'];
			}
			
			$rawTimes[] = $rec['processing_time'];
			
			$keys['coreplus.db.reads'] += $rec['db_reads'];
			$keys['coreplus.db.writes'] += $rec['db_writes'];
		}
		
		sort($rawTimes);
		
		if(sizeof($rawTimes) > 0){
			$keys['coreplus.render.time[avg]'] = round(array_sum($rawTimes) / sizeof($rawTimes), 0);
			$keys['coreplus.render.time[mean,25]'] = \Core\mean($rawTimes, 25);
			$keys['coreplus.render.time[mean,75]'] = \Core\mean($rawTimes, 75);	
		}
		else{
			$keys['coreplus.render.time[avg]'] = 0;
			$keys['coreplus.render.time[mean,25]'] = 0;
			$keys['coreplus.render.time[mean,75]'] = 0;
		}
		
		// Pull the log messages from the system log for this timeframe and record them.
		$raw = SystemLogModel::FindRaw(['datetime >= ' . $minutely]);
		foreach($raw as $rec){
			switch($rec['type']){
				case 'info':
					$keys['coreplus.logs.info']++;
					break;
				case 'security':
					$keys['coreplus.logs.security']++;
					break;
				case 'error':
					$keys['coreplus.logs.error']++;
					break;
			}
		}
		
		// Pull the versions installed
		$components = Core::GetComponents();
		foreach($components as $c){
			/** @var Component_2_1 $c */
			$keys['coreplus.components[' . $c->getKeyName() . ']'] = $c->getVersion();
		}
		
		// Flatten the keys to a multi-line string that can be sent to the zabbix-sender script.
		$data = [];
		foreach($keys as $k => $v){
			if($v !== null && $v !== ''){
				$data[] = $host . ' ' . $k . ' ' . $v;	
			}
		}

		$cmd = "/usr/bin/zabbix_sender -vv -i - -z $server -p $port";

		$outputPipes = [];
		$descriptorspec = [
			0 => array("pipe", "r"),  // stdin
			1 => array("pipe", "w"),  // stdout
			2 => array("pipe", "w"),  // stderr
		];
		$process = proc_open($cmd, $descriptorspec, $outputPipes);

		fwrite($outputPipes[0], implode("\n", $data));
		fclose($outputPipes[0]);

		$stdout = stream_get_contents($outputPipes[1]);
		fclose($outputPipes[1]);

		$stderr = stream_get_contents($outputPipes[2]);
		fclose($outputPipes[2]);

		// Close the connection and read the output result.
		$result = proc_close($process);

		// Print the status returned by Zabbix to the cron log.
		echo $stderr;
		return ($result == 0);
	}
}