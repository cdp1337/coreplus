#!/usr/bin/env php
<?php
/**
 * Script to import city population and geo data from the geonames.org database.
 * 
 * Usage: Download the requested country NN.zip file somewhere.
 * Then, just run this script with that file as the one argument.
 * 
 * Browse to http://download.geonames.org/export/dump/ to download new records.
 */


use Core\CLI\CLI;

if(!isset($_SERVER['SHELL'])){
	die("Please run this script from the command line.");
}

define('ROOT_PDIR', realpath(dirname(__DIR__) . '/../../') . '/');

// Include the core bootstrap, this will get the system functional.
require_once(ROOT_PDIR . 'core/bootstrap.php');

if($argc < 2){
	CLI::PrintError('Please specify the country NN.zip file as the one and only argument here.');
	die();
}

$filename = $argv[1];

CLI::PrintLine('Starting process of requested file ' . $filename);

$file = \Core\Filestore\Factory::File($filename);
if(!$file->exists()){
	CLI::PrintError('File does not seem to exist!');
	die();
}

if(!$file->isReadable()){
	CLI::PrintError('File is not readable.');
	die();
}

if(!$file->isLocal()){
	CLI::PrintError('File is not local ?!?');
	die();
}

if($file->getExtension() != 'zip'){
	CLI::PrintError('Please specify a ZIP file as the one argument, no other file is supported!');
	die();
}

// Geonames has incorrect province data for everything except the US :/
// As such, this map is to fix that until they correct their output to ISO standard!
$provinceMap = [
	'CA' => [
		'01' => 'AB',
		'02' => 'BC',
		'03' => 'MB',
		'04' => 'NB',
		'05' => 'NL',
		'07' => 'NS',
		'08' => 'ON',
		'09' => 'PE',
		'10' => 'QC',
		'11' => 'SK',
		'12' => 'YT',
		'13' => 'NT',
		'14' => 'NU',
	],
];



// Extract a list of files contained to ensure that it has a country file.
exec('unzip -l ' . escapeshellarg($file->getFilename()), $out);
if(sizeof($out) < 1){
	CLI::PrintError('ZIP list did not return a list of files.');
	die();
}

foreach($out as $line){
	if(preg_match('/[A-Z][A-Z]\.txt$/', $line, $m)){
		$countryFile = $m[0];
		CLI::PrintLine('Found ' . $countryFile . '!');
		$country = substr($countryFile, 0, 2);
		break;
	}
}

// Extract it to this file.
$tmpFile = Core\Filestore\Factory::File('tmp/' . $countryFile);
CLI::PrintLine('Extracting...');
exec('unzip ' . escapeshellarg($file->getFilename()) . ' ' . escapeshellarg($countryFile) . ' -d ' . escapeshellarg($tmpFile->getDirectoryName()));
CLI::PrintLine('Extracted with file size of ' . \Core\Filestore\format_size($tmpFile->getFilesize()));

CLI::PrintLine('Processing Data..');

// Start creating the output file for this result.
$outFilename = ROOT_PDIR . 'components/geographic-codes/datafiles/cities_bulk_' . strtolower($country) . '.sql';
$outFile = \Core\Filestore\Factory::File($outFilename);
// Clear out this file to start!
$outFile->putContents('');

// Write the header
$date = \Core\Date\DateTime::Now(\Core\Date\DateTime::FULLDATETIME);
$header = <<<EOD
-- Cities Geo table created dynamically by the generate_city_sql.php script
-- on $date
-- courtesy of data provided by http://geonames.org
-- This work is licensed under a Creative Commons Attribution 3.0 License,
-- see http://creativecommons.org/licenses/by/3.0/
-- The Data is provided "as is" without warranty or any representation of accuracy, timeliness or completeness.

-- Clear out the records ONLY for this country to start, (if any).
-- This is because there may be additional countries listed here, each in their own file.
DELETE FROM `geo_city` WHERE `country` = '$country';

INSERT INTO `geo_city` (`country`, `province`, `name`, `lat`, `lng`, `population`, `timezone`) VALUES

EOD;

$fh = fopen($outFilename, 'w');
fputs($fh, $header);

// Simple function to clean sql without an active database connection.
function mres($value)
{
	$search = array("\\",  "\x00", "\n",  "\r",  "'", "\x1a");
	$replace = array("\\\\","\\0","\\n", "\\r", "''", "\\Z");

	return str_replace($search, $replace, $value);
}

// Now, write the actual data.
$c = 0;
$first = true;
$fin = fopen($tmpFile->getFilename(), 'r');
if(!$fin){
	die('Unable to open file for reading?..');
}
while(($line = fgets($fin)) !== false){
	/*var_dump($line);
	fclose($fin);
	$tmpFile->delete();
	die();*/
	
	$parts = explode("\t", $line);
/*
The main 'geoname' table has the following fields :
---------------------------------------------------
00: geonameid         : integer id of record in geonames database
01: name              : name of geographical point (utf8) varchar(200)
02: asciiname         : name of geographical point in plain ascii characters, varchar(200)
03: alternatenames    : alternatenames, comma separated, ascii names automatically transliterated, convenience attribute from alternatename table, varchar(10000)
04: latitude          : latitude in decimal degrees (wgs84)
05: longitude         : longitude in decimal degrees (wgs84)
06: feature class     : see http://www.geonames.org/export/codes.html, char(1)
07: feature code      : see http://www.geonames.org/export/codes.html, varchar(10)
08: country code      : ISO-3166 2-letter country code, 2 characters
09: cc2               : alternate country codes, comma separated, ISO-3166 2-letter country code, 200 characters
10: admin1 code       : fipscode (subject to change to iso code), see exceptions below, see file admin1Codes.txt for display names of this code; varchar(20)
11: admin2 code       : code for the second administrative division, a county in the US, see file admin2Codes.txt; varchar(80) 
12: admin3 code       : code for third level administrative division, varchar(20)
13: admin4 code       : code for fourth level administrative division, varchar(20)
14: population        : bigint (8 byte int) 
15: elevation         : in meters, integer
16: dem               : digital elevation model, srtm3 or gtopo30, average elevation of 3''x3'' (ca 90mx90m) or 30''x30'' (ca 900mx900m) area in meters, integer. srtm processed by cgiar/ciat.
17: timezone          : the timezone id (see file timeZone.txt) varchar(40)
18: modification date : date of last modification in yyyy-MM-dd format
*/
	
	// Skip entries that are not of the required length.
	if(sizeof($parts) < 8){
		continue;
	}
	
	// I'm only concerned about cities, towns, etc.  These are feature code "P".
	if($parts[6] != 'P'){
		continue;
	}
	
	// Skip entries with no province, name, or other required field.
	if(
		$parts[1] == '' ||
		$parts[8] == '' ||
		$parts[10] == ''
	){
		//echo 's';
		continue;
	}
	
	// Skip any result with 50 or less people?...  These are usually civic POI's that snuck in.
	if($parts[14] < 50){
		//echo '2';
		continue;
	}
	
	$nam = mres($parts[1]);
	$lat = preg_replace('/[^0-9\.-]/', '', $parts[4]);
	$lng = preg_replace('/[^0-9\.-]/', '', $parts[5]);
	$cou = preg_replace('/[^A-Z0-9]/', '', $parts[8]);
	$pro = preg_replace('/[^A-Z0-9]/', '', $parts[10]);
	$pop = preg_replace('/[^0-9]/', '', $parts[14]);
	$tim = preg_replace('/[^A-Za-z\/_]/', '', $parts[17]);
	
	// Override/Correction?
	if(isset($provinceMap[$country]) && isset($provinceMap[$country][$pro])){
		$pro = $provinceMap[$country][$pro];
	}
	
	// (`country`, `province`, `name`, `lat`, `lng`, `population`, `timezone`)
	
	$out = $first ? '' : ",\n";
	$out .= "('$cou', '$pro', '$nam', $lat, $lng, $pop, '$tim')";
	fputs($fh, $out);
	++$c;
	$first = false;
	//echo '.';
}

fputs($fh, ';');
fclose($fh);
fclose($fin);
$tmpFile->delete();
CLI::PrintLine('Wrote ' . $c . ' entries to ' . $outFilename);