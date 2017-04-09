<?php
/**
 * File for class ModelImportLogger definition in the Agency-Portal project
 * 
 * @package Core
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20150120.1122
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

namespace Core;
use Core\CLI\CLI;
use Core\Utilities\Profiler\Profiler;


/**
 * A short teaser of what ModelImportLogger does.
 *
 * More lengthy description of what ModelImportLogger does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for ModelImportLogger
 * <h4>Example 1</h4>
 * <p>Description 1</p>
 * <code>
 * // Some code for example 1
 * $a = $b;
 * </code>
 *
 *
 * <h4>Example 2</h4>
 * <p>Description 2</p>
 * <code>
 * // Some code for example 2
 * $b = $a;
 * </code>
 *
 * 
 * @package Core
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class ModelImportLogger {
	public $errors = [];

	public $log = [];

	public $success   = 0;
	public $error     = 0;
	public $skipped   = 0;
	public $duplicate = 0;

	private $_title;
	private $realtime = false;
	private $_actionMessage = null;
	private $_profiler;

	public function __construct($title, $output_realtime){
		$this->realtime = $output_realtime;
		$this->_title = $title;

		$this->_profiler = new Profiler($this->_title);
		$this->header($this->_title);
	}

	public function header($message){
		if($this->realtime){
			CLI::PrintHeader($message);
		}
		$this->log[] = '** ' . $message;
	}

	public function log($message){
		if($this->realtime){
			CLI::PrintLine($message);
		}

		$this->_profiler->record($message);
		$this->log[] = $message;
	}

	public function error($message){
		if($this->realtime){
			CLI::PrintError($message);
		}

		$this->_profiler->record($message);
		$this->errors[] = $message;
		++$this->error;
	}

	public function success($message){
		if($this->realtime){
			CLI::PrintLine($message);
		}

		$this->_profiler->record($message);
		$this->log[] = $message;
		++$this->success;
	}

	public function skip($message){
		if($this->realtime){
			CLI::PrintLine($message);
		}

		$this->_profiler->record($message);
		$this->log[] = $message;
		++$this->skipped;
	}

	public function duplicate($message){
		if($this->realtime){
			CLI::PrintLine($message);
		}

		$this->_profiler->record($message);
		$this->log[] = $message;
		++$this->duplicate;
	}

	public function actionStart($message){
		if($this->realtime){
			CLI::PrintActionStart($message);
		}

		if($this->_actionMessage){
			$this->_profiler->record('[ABORTED]');
			$this->log[] = $this->_actionMessage . ' [ABORTED]';
		}

		$this->_profiler->record($message);
		$this->_actionMessage = $message;
	}

	public function actionSuccess(){
		if($this->realtime){
			CLI::PrintActionStatus('ok');
		}

		$this->_profiler->record('[OK]');
		$this->log[] = $this->_actionMessage . ' [OK]';
		$this->_actionMessage = null;
	}
	public function actionSkipped(){
		if($this->realtime){
			CLI::PrintActionStatus('skip');
		}

		$this->_profiler->record('[SKIP]');
		$this->log[] = $this->_actionMessage . ' [SKIP]';
		$this->_actionMessage = null;
	}

	public function finalize(){
		$subject = 'Import completed in ' . $this->_profiler->getTimeFormatted();
		$bits = [];
		if($this->success > 0){
			$bits[] = 'Success: ' . $this->success;
		}
		if($this->skipped > 0){
			$bits[] = 'Skipped: ' . $this->skipped;
		}
		if($this->error > 0){
			$bits[] = 'Errors: ' . $this->error;
		}
		if($this->duplicate > 0){
			$bits[] = 'Duplicates: ' . $this->duplicate;
		}
		$subject .= ' ' . implode(', ', $bits);

		\SystemLogModel::LogInfoEvent($this->_title, $subject, $this->_profiler->getEventTimesFormatted());
	}
}