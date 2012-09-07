<?php
/**
 * Model for the gallery images themselves.
 *
 * @package Gallery
 * @author Charlie Powell <charlie@eval.bz>
 * @copyright Copyright (C) 2012  Charlie Powell
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
class GalleryImageModel extends Model {
	public static $Schema = array(
		'id' => array(
			'type' => Model::ATT_TYPE_ID,
			'required' => true,
			'null' => false,
		),
		'albumid' => array(
			'type' => Model::ATT_TYPE_INT,
			'required' => true,
			'null' => false,
			'formtype' => 'system'
		),
		'uploaderid' => array(
			'type' => Model::ATT_TYPE_INT,
			'formtype' => 'disabled',
			'comment' => 'User id of the uploading user',
		),
		'weight' => array(
			'type' => Model::ATT_TYPE_INT,
			'formtype' => 'hidden',
		),
		'file' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'required' => true,
			'form' => array(
				'type' => 'file',
				'basedir' => 'public/galleryalbum',
				'accept' => 'image/*',
			),
		),
		'title' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128
		),
		'location' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => '64',
			'form' => array(
				'title' => 'Location taken',
				'description' => 'Where was this taken?'
			)
		),
		'latitude' => array(
			'type' => Model::ATT_TYPE_FLOAT,
			'formtype' => 'hidden'
		),
		'longitude' => array(
			'type' => Model::ATT_TYPE_FLOAT,
			'formtype' => 'hidden'
		),
		'datetaken' => array(
			'type' => Model::ATT_TYPE_INT,
			'form' => array(
				'title' => 'Date taken',
				'description' => 'When was this taken?'
			)
		),
		'keywords' => array(
			'type' => Model::ATT_TYPE_TEXT,
		),
		'description' => array(
			'type' => Model::ATT_TYPE_TEXT,
		),
		'rotation' => array(
			'type' => Model::ATT_TYPE_INT,
			'formtype' => 'hidden',
			'default' => 0
		),
		'exifdata' => array(
			'type' => Model::ATT_TYPE_TEXT,
			'formtype' => 'disabled',
			'comment' => 'Exif data from the photo, retrieved automatically'
		),
		'created' => array(
			'type' => Model::ATT_TYPE_CREATED,
			'null' => false,
		),
		'updated' => array(
			'type' => Model::ATT_TYPE_UPDATED,
			'null' => false,
		),
	);

	public static $Indexes = array(
		'primary' => array('id'),
		'unique:albumid_file' => array('albumid', 'file'), // Each image should be in each album at most once.
	);

	public function __construct($key = null) {
		$this->_linked = array(
			'Page' => array(
				'link' => Model::LINK_HASONE,
				'on' => 'baseurl',
			),
			'GalleryAlbum' => array(
				'link' => Model::LINK_HASONE,
				'on' => array('id' => 'albumid'),
			)
		);

		parent::__construct($key);
	}

	public function set($k, $v){
		switch($k){
			case 'file':
				$ret = parent::set($k, $v);
				// File was updated... load the exif data too!
				$file = $this->getOriginalFile()->getFilename();
				$this->_data['exifdata'] = json_encode( exif_read_data($file) );

				// Also if the file is new and it didn't exist... set the uploader id.
				if(!$this->_exists) $this->_data['uploaderid'] = \Core\user()->get('id');
				return $ret;
			case 'exifdata':
				// exif data cannot be changed externally!
				return false;
			default:
				return parent::set($k, $v);
		}
	}

	public function get($k){
		switch($k){
			case 'baseurl':
			case 'rewriteurl':
				return $this->getRewriteURL();
			case 'exifdata':
				return json_decode($this->_data['exifdata'], true);
			default:
				return parent::get($k);
		}
	}

	/**
	 * Get the file associated to this image.
	 *
	 * This has an interesting function; it will return the original filename or the rotated version.
	 * This rotated version is stored in a different directory to prevent name conflicts.
	 *
	 * @return File_Backend
	 */
	public function getFile(){
		if($this->get('rotation') == 0){
			// Simple enough :p
			return $this->getOriginalFile();
		}
		else{
			$filename = $this->get('file');
			$ext = substr($filename, strrpos($filename, '.'));
			$base = substr($filename, 0, 0 -strlen($ext));
			$rotatedfilename = $base . '-deg' . $this->get('rotation') . $ext;

			// Since rotated files are all temporary...
			$tmpfile = Core::File('tmp/galleryalbum/' . $rotatedfilename);

			if(!$tmpfile->exists()){
				$tmpfile->putContents('');
				// Rotate it!
				$originallocal = $this->getOriginalFile()->getLocalFilename();

				switch(strtolower($ext)){
					case '.jpg':
					case '.jpeg':
						$imagedat   = imagecreatefromjpeg($originallocal);
						$rotateddat = imagerotate($imagedat, $this->get('rotation'), 0);
						imagejpeg($rotateddat, $tmpfile->getFilename());
						break;
					case '.png':
						$imagedat   = imagecreatefrompng($originallocal);
						$rotateddat = imagerotate($imagedat, $this->get('rotation'), 0);
						imagepng($rotateddat, $tmpfile->getFilename());
						break;
					case '.gif':
						$imagedat   = imagecreatefromgif($originallocal);
						$rotateddat = imagerotate($imagedat, $this->get('rotation'), 0);
						imagegif($rotateddat, $tmpfile->getFilename());
						break;
					default:
						return $this->getOriginalFile();
				}
			}

			return $tmpfile;
		}
	}

	/**
	 * Get the original file associated to this image.
	 *
	 * This is critical because the getFile may return the *rotated* image!
	 *
	 * @return File_Backend
	 */
	public function getOriginalFile(){
		return Core::File('public/galleryalbum/' . $this->get('file'));
	}

	public function getRewriteURL(){
		$link = $this->getLink('GalleryAlbum')->get('rewriteurl') . '/';


		$link .= $this->get('id');
		if($this->get('title')) $link .= '-' . \Core\str_to_url($this->get('title'));

		return $link;
	}

	/**
	 * Get the exif data of this image as an associative array.
	 *
	 * @return array|false
	 */
	public function getExif(){
		$exif = $this->get('exifdata');
		if(!$exif) return false;

		$standardmappings = array(
			'Make', 'Model', 'Orientation', 'Software', 'DateTime', 'ExposureTime',
			// 	33437 (hex 0x829D)
			'FNumber',
			// 34850 (hex 0x8822)
			// The class of the program used by the camera to set exposure when the picture is taken.
			'ExposureProgram',
			'ISOSpeedRatings',
			// the Shutter Speed
			// The unit is the APEX (Additive System of Photographic Exposure) setting.
			'ShutterSpeedValue',
			// The lens aperture.
			// The unit is the APEX (Additive System of Photographic Exposure) setting.
			'ApertureValue',
			// The exposure bias.
			// Ordinarily it is given in the range of -99.99 to 99.99.
			'ExposureBiasValue',
			// The smallest F number of the lens.
			// Ordinarily it is given in the range of 00.00 to 99.99, but it is not limited to this range.
			'MaxApertureValue', 'MeteringMode', 'LightSource', 'Flash', 'FocalLength'
		);

		$dat = array(
			'FileSize' => Core::FormatSize($exif['FileSize']),
			'Height' => $exif['COMPUTED']['Height'],
			'Width' => $exif['COMPUTED']['Width'],
			'dimensions' => null,
			'ApertureFNumber' => $exif['COMPUTED']['ApertureFNumber'],
		);

		foreach($standardmappings as $k){
			$dat[$k] = (isset($exif[$k])) ? $exif[$k] : null;
		}

		$dat['dimensions'] = $dat['Width'] . ' x ' . $dat['Height'];
		
		switch($dat['ExposureProgram']){
			case 0: $dat['ExposureProgram'] = "Not defined"; break;
			case 1: $dat['ExposureProgram'] = "Manual"; break;
			case 2: $dat['ExposureProgram'] = "Normal program"; break;
			case 3: $dat['ExposureProgram'] = "Aperture priority"; break;
			case 4: $dat['ExposureProgram'] = "Shutter priority"; break;
			case 5: $dat['ExposureProgram'] = "Creative program (biased toward depth of field)"; break;
			case 6: $dat['ExposureProgram'] = "Action program (biased toward fast shutter speed)"; break;
			case 7: $dat['ExposureProgram'] = "Portrait mode (for closeup photos with the background out of focus)"; break;
			case 8: $dat['ExposureProgram'] = "Landscape mode (for landscape photos with the background in focus)"; break;
		}
		
		switch($dat['MeteringMode']){
			case 0:   $dat['MeteringMode'] = "Unknown"; break;
			case 1:   $dat['MeteringMode'] = "Average"; break;
			case 2:   $dat['MeteringMode'] = "CenterWeightedAverage"; break;
			case 3:   $dat['MeteringMode'] = "Spot"; break;
			case 4:   $dat['MeteringMode'] = "MultiSpot"; break;
			case 5:   $dat['MeteringMode'] = "Pattern"; break;
			case 6:   $dat['MeteringMode'] = "Partial"; break;
			case 255: $dat['MeteringMode'] = "other"; break;
		}

		switch($dat['LightSource']){
			case 0:   $dat['LightSource'] = "Unknown"; break;
			case 1:   $dat['LightSource'] = "Daylight"; break;
			case 2:   $dat['LightSource'] = "Fluorescent"; break;
			case 3:   $dat['LightSource'] = "Tungsten (incandescent light)"; break;
			case 4:   $dat['LightSource'] = "Flash"; break;
			case 9:   $dat['LightSource'] = "Fine weather"; break;
			case 10:  $dat['LightSource'] = "Cloudy weather"; break;
			case 11:  $dat['LightSource'] = "Shade"; break;
			case 12:  $dat['LightSource'] = "Daylight fluorescent (D 5700 - 7100K)"; break;
			case 13:  $dat['LightSource'] = "Day white fluorescent (N 4600 - 5400K)"; break;
			case 14:  $dat['LightSource'] = "Cool white fluorescent (W 3900 - 4500K)"; break;
			case 15:  $dat['LightSource'] = "White fluorescent (WW 3200 - 3700K)"; break;
			case 17:  $dat['LightSource'] = "Standard light A"; break;
			case 18:  $dat['LightSource'] = "Standard light B"; break;
			case 19:  $dat['LightSource'] = "Standard light C"; break;
			case 20:  $dat['LightSource'] = "D55"; break;
			case 21:  $dat['LightSource'] = "D65"; break;
			case 22:  $dat['LightSource'] = "D75"; break;
			case 23:  $dat['LightSource'] = "D50"; break;
			case 24:  $dat['LightSource'] = "ISO studio tungsten"; break;
			case 255: $dat['LightSource'] = "Other light source"; break;
		}
		/*
		Values for bit 0 indicating whether the flash fired.
		0 = Flash did not fire
		1 = Flash fired

		Values for bits 1 and 2 indicating the status of returned light.
		00 = No strobe return detection function
		01 = reserved
		10 = Strobe return light not detected
		11 = Strobe return light detected

		Values for bits 3 and 4 indicating the camera's flash mode.
		00 = unknown
		01 = Compulsory flash firing
		10 = Compulsory flash suppression
		11 = Auto mode

		Values for bit 5 indicating the presence of a flash function.
		0 = Flash function present
		1 = No flash function

		Values for bit 6 indicating the camera's red-eye mode.
		0 = No red-eye reduction mode or unknown
		1 = Red-eye reduction supported
		*/

		/*
		'ColorSpace' => int 1
		'ExifImageWidth' => int 2816
		'ExifImageLength' => int 2112
		'InteroperabilityOffset' => int 1204
		'SensingMethod' => int 2
		'FileSource' => string '' (length=1)
		'SceneType' => string '' (length=1)
		'ExposureMode' => int 0
		'WhiteBalance' => int 0
		'DigitalZoomRatio' => string '100/100' (length=7)
		'FocalLengthIn35mmFilm' => int 31
		'SceneCaptureType' => int 0
		'Sharpness' => int 0
		'InterOperabilityIndex' => string 'R98' (length=3)
		'InterOperabilityVersion' => string '0100' (length=4)
		*/

		return $dat;
	}
}
