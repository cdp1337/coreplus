<?php

/*
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

/**
 * Description of PersistentJobModel
 *
 * @author charlie
 */
class CronPersistentJobModel extends Model {
	public static $Schema = [
		'id' => [
			'type' => Model::ATT_TYPE_UUID,
		],
		'key' => [
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 64,
		],
		'call' => [
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
		],
		'parameters' => [
			'type' => Model::ATT_TYPE_DATA,
			'encoding' => Model::ATT_ENCODING_JSON,
		],
		'state' => [
			'type' => Model::ATT_TYPE_ENUM,
			'options' => ['pending', 'starting', 'running', 'paused', 'stopped', 'zombie'],
			'default' => 'pending',
		],
		'server' => [
			'type' => Model::ATT_TYPE_STRING,
			'comment' => 'Server this job is running on',
		],
		'pid' => [
			'type' => Model::ATT_TYPE_INT,
			'comment' => 'Process ID of the job',
		],
		'memory' => [
			'type' => Model::ATT_TYPE_INT,
			'comment' => 'Memory usage at the last check',
		],
		'timestamp_checked' => [
			'type' => Model::ATT_TYPE_INT,
			'comment' => 'Timestamp of the last check',
		],
	];
	
	public static $Indexes = [
		'primary' => 'id',
	];
	
	public function getProcessInformation(){
		if($this->get('state') == 'pending'){
			return 'pending' ;
		}
		if(defined('SERVER_ID') && $this->get('server') != SERVER_ID){
			return 'notmanaged' ;
		}
		
		if(!$this->get('pid')){
			return 'stopped' ;
		}
		
		$out = [];
		exec('ps -p ' . $this->get('pid') . ' -o state,size', $out);
		
		if(sizeof($out) == 1){
			$this->set('state', 'stopped');
			$this->set('memory', 0);
			$this->set('pid', 0);
			$this->set('timestsamp_checked', Core\Date\DateTime::NowGMT());
			
			return 'stopped';
		}
		else{
			list($s, $m) = explode(' ', $out[1]);
			if($s == 'Z'){
				$this->set('state', 'zombie');
				$this->set('memory', 0);
				$this->set('timestamp_checked', Core\Date\DateTime::NowGMT());
				return 'zombie';
			}
			else{
				$this->set('state', 'running');
				$this->set('memory', $m);
				$this->set('timestamp_checked', Core\Date\DateTime::NowGMT());
				
				return 'running';
			}
		}
	}
	
	/**
	 * Stop a process that is currently running and mark is as paused.
	 */
	public function stopProcess(){
		// Get the latest process information.
		$this->getProcessInformation();
		
		if($this->get('state') == 'running'){
			// Send the instruction to kill this process.
			exec('kill ' . $this->get('pid'));
		}
		
		$this->set('state', 'paused');
		$this->save();
	}
	
	/**
	 * Queue a process to start, will do nothing if already running.
	 * 
	 * This will NOT start the process, but simply set it as queued to start by the next available server.
	 */
	public function startProcess(){
		if($this->get('state') == 'running'){
			return;
		}
		
		$this->set('state', 'pending');
		$this->save();
	}
	
	/**
	 * Register a persistent job to be background executed on this server, (or another server in the cluster)
	 * 
	 * @param type $call
	 * @param type $params
	 * @return boolean
	 */
	public static function RegisterJob($call, $params){
		
		// To easily keep track of this, (and not to allow duplicate jobs to be runnig),
		// Create a hash of the call and parameters.
		$hash = $call;
		if(is_array($params)){
			foreach($params as $k => $v){
				if(is_scalar($v)){
					$hash .= ';' . $k . ':' . $v;
				}
				elseif(is_object($v)){
					$hash .= ';' . $k . ':' . get_class($v);
				}
			}
		}
		
		$key = md5($hash);
		
		$model = CronPersistentJobModel::Find(['key = ' . $key], 1);
		if($model !== null){
			// Job already exists!
			// Check to see if it's stopped or something.
			if($model->get('state') == 'stopped' || $model->get('state') == 'zombie'){
				$model->delete();
			}
			else{
				// Stop/start the old script instance.
				$model->stopProcess();
				$model->startProcess();
				return true;
			}
		}
		
		$model = new CronPersistentJobModel();
		$model->setFromArray([
			'key' => $key,
			'call' => $call,
			'parameters' => $params,
			'state' => 'pending',
		]);
		$model->save();
		return true;
	}
	
	/**
	 * Method to check all active/pending jobs and start them if necessary.
	 * 
	 */
	public static function CheckJobs(){
		
		if(!defined('SERVER_ID')){
			echo "Unable to support persistent jobs without a valid SERVER ID set!";
			return false;
		}
		
		$zombieTime = new Core\Date\DateTime();
		$zombieTime->modify('-10 minutes');
		$zombieTime = $zombieTime->format('U');
		$models = CronPersistentJobModel::Find();
		foreach($models as $m){
			echo 'Found job ' . $m->get('call') . '(' . $m->get('id') . ') ';
			if($m->get('state') == 'paused'){
				// Skip any job that has been administratively paused.
				echo "Skipping, administratively paused!\n";
				continue;
			}
			
			
			if($m->get('state') == 'running' && $m->get('server') != SERVER_ID){
				// If this job is managed by another server and it still seems to be active, let it be also.
				if($m->get('timestamp_checked') < $zombieTime){
					echo "Process seems to be abandonded, adopting here!\n";
					$m->set('server', '');
					$m->set('state', 'pending');
					self::_RunJob($m);
				}
				else{
					echo "Skipping, handled by another server.\n";
					continue;
				}
			}
			
			// This server is eligible for monitoring this job!
			self::_RunJob($m);
		}
		
		return true;
	}
	
	private static function _RunJob($job){
		$state = $job->getProcessInformation();
		
		if($state == 'stopped'){
			echo "PID stopped, restarting!\n";
		}
		
		if($state == 'pending' || $state == 'stopped'){
			$job->set('state', 'starting');
			$job->save();
			
			// New job!
			$descriptor = [
			];
			$pipes = [];
			$process = proc_open('php ' . ROOT_PDIR . 'index.php ' . '/cron/persistent/' . $job->get('id') . ' &', $descriptor, $pipes);
			
			// It should be running now and have a PID.
			$status = proc_get_status($process);
			
			// Close immediately; it's meant to be a background process!
			proc_close($process);
			
			// Record this information.
			$job->setFromArray([
				'state' => 'running',
				'pid' => $status['pid'] + 1,
				'server' => SERVER_ID,
			]);
			$job->save();
			
			echo "Started job as PID " . ($status['pid'] + 1) . "\n";
		}
		elseif($state == 'starting'){
			echo "Job already marked as starting!\n";
		}
		elseif($state == 'zombie'){
			echo "WARNING!!! PID " . $job->get('pid') . " is a zombie!\n";
		}
		elseif($state == 'running'){
			echo "Running as PID " . $job->get('pid') . ", using " . \Core\filestore\format_size($job->get('memory')) . " of memory\n";
			$job->save();
		}
	}
}
