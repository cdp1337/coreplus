<?php
/**
 * Main controller for the user system
 *
 * @package User
 * @since 2.0.4
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

class UserActivityController extends Controller_2_1 {
	public function __construct(){
		$this->accessstring = 'p:user_activity_list';
	}

	public function index(){
		$view = $this->getView();

		$view->title = 'User Activity';

		$view->addControl(
			[
				'title' => 'Detailed Activity',
				'icon' => 'list-alt',
				'link' => '/useractivity/details'
			]
		);
	}

	public function now(){
		$request = $this->getPageRequest();
		$view = $this->getView();

		if(!$request->isJSON()) return View::ERROR_BADREQUEST;
		$view->contenttype = View::CTYPE_JSON;
		$view->mode = View::MODE_AJAX;
		$view->record = false;

		$limit = $this->_getQueryLimit();
		$duration = 60;
		// Use FindRaw because it's faster than using full Models for everything, especially when dealing with 20k + records.
		$listings = UserActivityModel::FindRaw('datetime >= ' . (Time::GetCurrent() - $duration), $limit, 'datetime DESC');


		$data = array();

		// Performance reports
		$data['information'] = array(
			'duration' => $duration,
		);
		$data['performance'] = array('get' => 0, 'post' => 0);
		$data['requests'] = array('get' => 0, 'post' => 0);
		$users = array();
		$bots = array();

		foreach($listings as $log){
			if($log['type'] == 'GET'){
				$data['performance']['get'] += $log['processing_time'];
				$data['requests']['get']++;
			}
			elseif($log['type'] == 'POST'){
				$data['performance']['post'] += $log['processing_time'];
				$data['requests']['post']++;
			}

			$ua = new \Core\UserAgent($log['useragent']);

			// Bots have their own data, because, well... they're bots.
			// Damn bots!
			if($ua->isBot()){
				if(!isset($bots[ $log['ip_addr'] ])){
					$bots[ $log['ip_addr'] ] = array(
						'ip'        => $log['ip_addr'],
						'useragent' => $log['useragent'],
						'lastpage'  => $log['request'],
						'status'    => $log['status'],
						//'type'      => $ua->,
						'browser'   => $ua->browser,
						'count'     => 1,
					);
				}
				else{
					$bots[$log['ip_addr']]['count']++;
				}
			}
			// The user agent information I want to know on a per-user basis, not a per-click basis.
			else{
				if(!isset($users[ $log['session_id'] ])){
					$users[ $log['session_id'] ] = array(
						'session'   => $log['session_id'],
						'ip'        => $log['ip_addr'],
						'user_id'   => $log['user_id'],
						'username'  => User::Construct($log['user_id'])->getDisplayName(),
						'useragent' => $log['useragent'],
						'lastpage'  => $log['request'],
						//'type'      => $ua->type,
						'browser'   => $ua->browser . ' ' . $ua->version,
						'os'        => $ua->platform,
						'count'     => 1,
					);
				}
				else{
					$users[$log['session_id']]['count'] ++;
				}
			}
		}
//UserAgent::Test(); die();
		if($data['requests']['get'] > 0) $data['performance']['get'] = round($data['performance']['get'] /  $data['requests']['get'], 2);
		if($data['requests']['post'] > 0) $data['performance']['post'] = $data['performance']['post'] /  $data['requests']['post'];

		$data['users'] = array_values($users);
		$data['bots'] = array_values($bots);

		//var_dump($data, $users, $listings); die();
		$view->jsondata = $data;
	}

	public function historical(){
		$request = $this->getPageRequest();
		$view = $this->getView();

		if(!$request->isJSON()) return View::ERROR_BADREQUEST;
		$view->contenttype = View::CTYPE_JSON;
		$view->mode = View::MODE_AJAX;
		$view->record = false;

		$profiler = new \Core\Utilities\Profiler\Profiler('useractivity');

		$limit  = $this->_getQueryLimit();
		$dstart = $request->getParameter('dstart');
		$dend   = $request->getParameter('dend');

		if(!$dend){
			$dend = Time::GetCurrent();
		}
		else{
			$dend = strtotime($dend);
		}
		if(!$dstart){
			$dstart = $dend - (3600 * 24 * 30);
		}
		else{
			$dstart = strtotime($dstart);
		}
		// Use FindRaw because it's faster than using full Models for everything, especially when dealing with 20k + records.
		$listings = UserActivityModel::FindRaw(array('datetime >= ' . $dstart, 'datetime <= ' . $dend), $limit, 'datetime DESC');
		$profiler->record('Found raw models');

		$data = array(
			'performance' => array('get' => 0, 'post' => 0),
			'requests'    => array('get' => 0, 'post' => 0),
			'counts'      => array('bots' => 0, 'users' => 0, 'visitors' => 0),
			'browsers'    => array(),
			'referrers'   => array(),
			'ips'         => array(),
			'os'          => array(),
			'notfounds'   => array(),
			'pages'       => array(),
		);

		$sessions = array();
		$x = 0;

		foreach($listings as $log){
			++$x;

			if($log['type'] == 'GET'){
				$data['performance']['get'] += $log['processing_time'];
				$data['requests']['get']++;
			}
			elseif($log['type'] == 'POST'){
				$data['performance']['post'] += $log['processing_time'];
				$data['requests']['post']++;
			}

			$ua = \Core\UserAgent::Construct($log['useragent']);

			if($ua->isBot()){
				$data['counts']['bots']++;
			}
			else{
				$data['counts']['users']++;

				if(!isset($sessions[ $log['session_id'] ])) $sessions[ $log['session_id'] ] = true;
			}

			$browser = $ua->browser . ' ' . $ua->version;

			if(!isset($data['browsers'][ $browser ])) $data['browsers'][ $browser ] = 1;
			else $data['browsers'][ $browser ]++;

			if($log['referrer'] == ''){
				$referrer = 'none';
			}
			elseif(strpos($log['referrer'], 'http://' . HOST) === 0){
				$referrer = 'internal';
			}
			elseif(strpos($log['referrer'], 'https://' . HOST) === 0){
				$referrer = 'internal-ssl';
			}
			else{
				// I want to trim the referrer a bit so I don't end up with a bunch of one-offs.
				if(($qpos = strpos($log['referrer'], '?')) !== false) $referrer = substr($log['referrer'], 0, $qpos);
				else $referrer = $log['referrer'];
			}

			if(!isset($data['referrers'][ $referrer ])) $data['referrers'][ $referrer ] = 1;
			else $data['referrers'][ $referrer ]++;

			if(!isset($data['ips'][ $log['ip_addr'] ])) $data['ips'][ $log['ip_addr'] ] = 1;
			else $data['ips'][ $log['ip_addr'] ]++;

			if(!isset($data['os'][ $ua->platform ])) $data['os'][ $ua->platform ] = 1;
			else $data['os'][ $ua->platform ]++;

			if($log['status'] == 404){
				if(!isset($data['notfounds'][ $log['request'] ])) $data['notfounds'][ $log['request'] ] = 1;
				else $data['notfounds'][ $log['request'] ]++;
			}

			if($log['status'] == 200){
				if(!isset($data['pages'][ $log['baseurl'] ])) $data['pages'][ $log['baseurl'] ] = 1;
				else $data['pages'][ $log['baseurl'] ]++;
			}

			$profiler->record('Parsed record #' . $x);
		}
//UserAgent::Test(); die();
		if($data['requests']['get'] > 0) $data['performance']['get'] = round($data['performance']['get'] /  $data['requests']['get'], 2);
		if($data['requests']['post'] > 0) $data['performance']['post'] = $data['performance']['post'] /  $data['requests']['post'];

		$data['counts']['visitors'] = sizeof($sessions);

		// Do some sorting on a few of the arrays.
		arsort($data['browsers']);
		arsort($data['referrers']);
		arsort($data['ips']);
		arsort($data['os']);
		arsort($data['notfounds']);
		arsort($data['pages']);

		$profiler->record('Sorted all data');

		// DEBUG!
		//echo '<pre>';
		//echo $profiler->getEventTimesFormatted();
		//die();

		//var_dump($data, $users, $listings); die();
		$view->jsondata = $data;
	}

	/**
	 * See the details of a given search criteria, be it IP address, session, or user.
	 */
	public function details(){
		$view = $this->getView();
		$request = $this->getPageRequest();

		$filters = new FilterForm();
		$filters->haspagination = true;
		$filters->setLimit(100);
		$filters->setName('useractivity-details');

		$filters->addElement(
			'text',
			['name' => 'user_id', 'title' => 'User ID', 'link' => FilterForm::LINK_TYPE_STANDARD]
		);

		$filters->addElement(
			'text',
			['name' => 'ip_addr', 'title' => 'IP Address', 'link' => FilterForm::LINK_TYPE_STANDARD]
		);

		$filters->addElement(
			'text',
			['name' => 'session_id', 'title' => 'Session ID', 'link' => FilterForm::LINK_TYPE_STANDARD]
		);

		$filters->load($request);


		$factory = new ModelFactory('UserActivityModel');
		$factory->order('datetime DESC');

		$filters->applyToFactory($factory);

		$view->title = 'Detailed Activity';
		$view->assign('filters', $filters);
		$view->assign('listings', $factory->get());
	}

	/**
	 * Since the amount of data here may be rather large,
	 * limit it to ensure that it does not exceed php's memory limit size.
	 *
	 * Approximately 6.4k of data is required per record, (after all the overhead of the subsystems).
	 * As such, a memory limit of 64M can handle about 10k records, 128M can handle 20k, and so on.
	 *
	 * @return int
	 */
	private function _getQueryLimit(){
		// This is required to limit the number of results to get.
		$memory_limit = ini_get('memory_limit');
		if(stripos($memory_limit, 'k') !== false){
			$memory_limit = (str_ireplace('k', '', $memory_limit) * 1024);
		}
		elseif(stripos($memory_limit, 'm') !== false){
			$memory_limit = (str_ireplace('m', '', $memory_limit) * (1024*1024));
		}
		// 64m == 10k records,
		// 128m == 20k records
		// mem = 64 x 10^5
		// limit = 10 x 10^3
		// 640 = 1
		$limit = round($memory_limit / 6400);

		return $limit;
	}
}
