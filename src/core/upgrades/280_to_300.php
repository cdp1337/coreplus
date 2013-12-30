<?php
/**
 * Upgrade file for 2.8.0 to 3.0.0
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20131229.2033
 */

// Handle conversion of the security entries to system log entries.
// These are now contained in that table.

$secfac = new ModelFactory('SecurityLogModel');
$stream = new \Core\Datamodel\DatasetStream($secfac->getDataset());
while($row = $stream->getRecord()){
	$model = SystemLogModel::Construct($row['id']);

	// Standard keys
	$model->setFromArray(
		[
			'datetime'         => $row['datetime'],
			'session_id'       => $row['session_id'],
			'user_id'          => $row['user_id'],
			'ip_addr'          => $row['ip_addr'],
			'useragent'        => $row['useragent'],
			'code'             => $row['action'],
			'affected_user_id' => $row['affected_user_id'],
			'message'          => $row['details'],
		]
	);

	$model->set('type', ($row['status'] == 'fail') ? 'security' : 'info');
	$model->save();
}