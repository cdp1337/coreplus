<?php
/**
 * Created by JetBrains PhpStorm.
 * User: powellc
 * Date: 12/15/12
 * Time: 1:49 PM
 * Helper class to work with the stopforumspam.com system.
 */
class StopForumSpam {
	const URL_DAILY = 'http://www.stopforumspam.com/downloads/listed_ip_1_all.zip';

	/**
	 * Method that can be called on the /cron hooks to download the newest version of sfs's blacklist.
	 *
	 * This is designed to grab the daily version, so /cron/daily is the best location.
	 *
	 * @return bool
	 */
	public static function HookDaily(){
		// LIVE
		//$remotefile = new File_remote_backend(StopForumSpam::URL_DAILY);
		$remotefile = StopForumSpam::URL_DAILY;
		// TESTING
		//$remotefile = new File_local_backend(ROOT_PDIR . 'components/security-suite/listed_ip_1_all.zip');
		//$remotefile = ROOT_PDIR . 'components/security-suite/listed_ip_1_all.zip'

		return self::ImportList($remotefile);
	}

	public static function ImportList($filename){
		// Download the latest blacklist from sfs and import it into the system.
		// Each record will be in a format such as
		// "5.144.176.232","7","2012-12-11 00:08:10"
		// IP, number of submissions, date

		$remotefile = \Core\file($filename);

		// Does it exist?
		if(!$remotefile->exists()){
			echo 'Unable to read file ' . $remotefile->getFilename() . ', it does not appear to exist!';
			return false;
		}

		$contents = $remotefile->getContentsObject();

		// Make sure it's a zip file.  If it's not, then we'll have problems.
		if(!$contents instanceof File_zip_contents){
			echo 'File ' . $remotefile->getFilename() . ' does not appear to be a zip file, aborting extraction!';
			return false;
		}

		$dest = 'tmp/sfs-blacklist/';
		/** @var $extracted Directory_local_backend */
		$extracted = $contents->extract($dest);
		/** @var $file File_local_backend */
		$file = $extracted->get('listed_ip_1_all.txt');

		// Do this line by line instead of reading the entire contents into memory.
		$fh = fopen($file->getFilename(), 'r');
		if(!$fh){
			echo 'Unable to open file ' . $file->getFilename() . ' for reading, aborting extraction!';
			return false;
		}
		$recordscount = 0;
		$newcount     = 0;
		$updatedcount = 0;
		$skippedcount = 0;
		while(!feof($fh)){
			$line = fgetcsv($fh);
			// Don't know why.....
			if(!$line[0]) continue;

			++$recordscount;

			// If the record count is too low to even care about... just skip it.
			if($line[1] <= 2){
				++$skippedcount;
				continue;
			}

			$record = sfsBlacklistModel::Construct($line[0]);
			$record->setFromArray(
				array(
					'submissions' => $line[1],
					'lastseen' => $line[2],
				)
			);
			if(!$record->exists()){
				++$newcount;
				$record->save();
			}
			elseif($record->save()){
				++$updatedcount;
			}
			else{
				++$skippedcount;
			}
		}
		fclose($fh);

		echo 'Processed ' . $recordscount . ' records from ' . $remotefile->getFilename() . ' successfully!' . "\n";
		echo 'New Records: ' . $newcount . "\n";
		echo 'Updated Records: ' . $updatedcount . "\n";
		echo 'Skipped Records: ' . $skippedcount . "\n";
		return true;
	}

	/**
	 * Check the user's IP in the blacklist and see if it's found.
	 *
	 * If it is and has a high enough submission rate, (in a 24 hour period), then block the user completely and immediately.
	 */
	public static function CheckIP(){
		$record = sfsBlacklistModel::Construct(REMOTE_IP);
		// It's not in there, YAY!
		if(!$record->exists()) return;

		// Is the submission score high enough?
		$highscore = 100;
		if($record->get('submissions') > $highscore){
			// YOU can haz good party tiem nau

			SecurityLogModel::Log('/security/blocked', null, null, 'Blocking IP due to over ' . $highscore . ' submissions to sfs in a 24 hour period.');

			die('IP Blocked due to high spam score');
		}

		// Submissions listed, but not exceedingly high?
		$warnlevel = 5;
		if($record->get('submissions') > $warnlevel){
			if(isset($_SESSION['security_antispam_allowed'])){
				// Ok, they're allowed in.
			}
			else{
				$html = '<html><body>';
				$html .= '<!-- You smell of spam.... are you sure you didn\'t come from a can?-->';
				if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['happyfuntime']) && isset($_SESSION['happyfuntimecheck'])){
					// It's an attempt!
					if($_POST['happyfuntime'] == $_SESSION['happyfuntimecheck']){
						SecurityLogModel::Log('/security/unblocked', null, null, 'User successfully answered an anti-bot math question, unblocking.');
						$_SESSION['security_antispam_allowed'] = true;
					}
					else{
						SecurityLogModel::Log('/security/captchafailed', null, null, 'User attempted, but failed in answering an anti-bot math question.');
						$html .= '<b>NOPE!</b>';
					}
				}

				SecurityLogModel::Log('/security/blocked', null, null, 'Blocking IP due to over ' . $warnlevel . ' submissions to sfs in a 24 hour period.');
				$random1 = (rand(4, 6) * 2);
				$random2 = (rand(1, 3) * 2);
				$random3 = rand(1, 2);

				switch($random3){
					case 1:
						$result = $random1 / $random2;
						$operation = 'divided by';
						break;
					case 2:
						$result = $random1 * $random2;
						$operation = 'multiplied by';
						break;
				}

				$_SESSION['happyfuntimecheck'] = $result;
				switch($random2){
					case 1: $random2 = 'oNe'; break;
					case 2: $random2 = 'Tw0'; break;
					case 3: $random2 = 'ThRe'; break;
					case 4: $random2 = 'Foor'; break;
					case 5: $random2 = 'fIve'; break;
					case 6: $random2 = 'Siix'; break;
				}

				$html .= '<form method="POST"><p>What is ' . $random1 . ' ' . $operation . ' ' . $random2 . '?</p><input type="text" name="happyfuntime" size="3"/><input type="submit" value="GO"/></form></body></html>';
				die($html);
			}
		}
	}
}
