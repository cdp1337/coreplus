<?php
/**
 * Model for the gallery images themselves.
 *
 * @package Gallery
 * @author Charlie Powell <charlie@evalagency.com>
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
				'basedir' => 'public/galleryalbum', // Defaults, but may be changed in the controller.
				'accept' => 'image/*', // Defaults, but may be changed in the controller.
			),
		),
		'title' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'formtype' => 'disabled',
		),
		'location' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => '64',
			'form' => array(
				'title' => 'Location taken',
				'description' => 'Where was this taken?',
				'type' => 'disabled', // Candidate for removal?
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
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => '64',
			'form' => array(
				'title' => 'Date taken',
				'description' => 'When was this taken?',
				'type' => 'disabled', // Candidate for removal?
			)
		),
		'previewfile' => array(
			'type' => Model::ATT_TYPE_STRING,
			'maxlength' => 128,
			'required' => false,
			'form' => array(
				'title' => 'Preview Image',
				'type' => 'hidden', // Hidden by default, (enabled for non-image uploads)
				'basedir' => 'public/galleryalbum', // Defaults, but may be changed in the controller.
				'accept' => 'image/*',
				'description' => 'Upload an image to use as the preview version of this file.',
			),
			'comment' => 'For videos and non-images, this is the preview image',
		),
		'previewsize' => array(
			'type' => Model::ATT_TYPE_ENUM,
			'options' => array('xs', 'sm', 'med', 'lg', 'xl'),
			'default' => 'med',
			'form' => array(
				'title' => 'Preview Size',
				'description' => 'The preview size of this item on the listing page',
			)
		),
		/*'keywords' => array(
			'type' => Model::ATT_TYPE_TEXT,
		),*/
		/*'description' => array(
			'type' => Model::ATT_TYPE_TEXT,
		),*/
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

	/**
	 * Cache of the file object, so consecutive calls to getFile will not re-request the entire file.
	 *
	 * @var null|\Core\Filestore\File
	 */
	private $_file = null;


	public function __construct($key = null) {
		$this->_linked = array(
			'Page' => array(
				'link' => Model::LINK_HASONE,
				'on' => 'baseurl',
			),
			'GalleryAlbum' => array(
				'link' => Model::LINK_BELONGSTOONE,
				'on' => array('id' => 'albumid'),
			)
		);

		parent::__construct($key);
	}

	public function set($k, $v){
		switch($k){
			case 'file':
				$ret = parent::set($k, $v);
				// Clear out the cache
				$this->_file = null;
				// File was updated... load the exif data too!
				// Note, only do this if it was an image!
				$file = $this->getOriginalFile();
				if($file->isImage() && ($file->getExtension() == 'jpg')){
					$this->_data['exifdata'] = json_encode( exif_read_data($file->getFilename()) );
				}
				else{
					$this->_data['exifdata'] = null;
				}

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
	 * Get the preview file associated to this image/item.
	 *
	 * This is required because albeit images are their own previews, videos, music and other files do not have a useful preview version.
	 * As such, if there was one uploaded, use that image instead of the mimetype image.
	 *
	 * @return \Core\Filestore\File
	 */
	public function getPreviewFile(){
		if($this->getOriginalFile()->isImage()){
			return $this->getFile();
		}
		elseif($this->get('previewfile')){
			$file = \Core\Filestore\Factory::File($this->get('previewfile'));
			return $file;
		}
		else{
			return $this->getFile();
		}
	}

	/**
	 * Get the file associated to this image.
	 *
	 * This has an interesting function; it will return the original filename or the rotated version.
	 * This rotated version is stored in a different directory to prevent name conflicts.
	 *
	 * @return \Core\Filestore\File
	 */
	public function getFile(){
		if($this->_file !== null) return $this->_file;

		if($this->get('rotation') == 0){
			// Simple enough :p
			$this->_file = $this->getOriginalFile();
		}
		else{
			$filename = $this->get('file');
			$ext = substr($filename, strrpos($filename, '.'));
			$base = substr($filename, 0, 0 -strlen($ext));
			$rotatedfilename = $base . '-deg' . $this->get('rotation') . $ext;

			// Since rotated files are all temporary, (but need to be publicly visible)...
			$tmpfile = \Core\Filestore\Factory::File('public/tmp/galleryalbum/' . $rotatedfilename);

			if(!($tmpfile->exists() && $tmpfile->getFilesize() > 0)){

				// I need physical write access, /tmp provides that!
				$tmptmp = \Core\Filestore\Factory::File('tmp/galleryalbum/' . $rotatedfilename);

				// Write a small amount of data to the file so the subsystem will create the directories and everything.
				// Otherwise, if /tmp/galleryalbum doesn't exist, the image operations won't be able to write to it.
				$tmptmp->putContents('');

				// Rotate it!
				$originallocal = $this->getOriginalFile()->getLocalFilename();

				switch(strtolower($ext)){
					case '.jpg':
					case '.jpeg':
						$imagedat   = imagecreatefromjpeg($originallocal);
						$rotateddat = imagerotate($imagedat, $this->get('rotation'), 0);
						imagejpeg($rotateddat, $tmptmp->getFilename());
						$tmptmp->copyTo($tmpfile, true);
						$tmptmp->delete();
						break;
					case '.png':
						$imagedat   = imagecreatefrompng($originallocal);
						$rotateddat = imagerotate($imagedat, $this->get('rotation'), 0);
						imagepng($rotateddat, $tmptmp->getFilename());
						$tmptmp->copyTo($tmpfile, true);
						$tmptmp->delete();
						break;
					case '.gif':
						$imagedat   = imagecreatefromgif($originallocal);
						$rotateddat = imagerotate($imagedat, $this->get('rotation'), 0);
						imagegif($rotateddat, $tmptmp->getFilename());
						$tmptmp->copyTo($tmpfile, true);
						$tmptmp->delete();
						break;
					default:
						return $this->getOriginalFile();
				}
			}

			$this->_file = $tmpfile;
		}

		return $this->_file;
	}

	/**
	 * Get the original file associated to this image.
	 *
	 * This is critical because the getFile may return the *rotated* image!
	 *
	 * @return \Core\Filestore\File
	 */
	public function getOriginalFile(){
		return \Core\Filestore\Factory::File($this->get('file'));
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

		// If there are no sections found, what's the point?
		if($exif['SectionsFound'] == '') return false;

		// If there was only a comment found... I don't care either.
		if($exif['SectionsFound'] == 'COMMENT') return false;

		$dat = array(
			'FileSize' => null, 'FileSizeFormatted' => null, 'Height' => null, 'Width' => null, 'Dimensions' => null,
			'DateTimeOriginal' => null,
			'FNumber' => null,
			'Make' => null, 'Model' => null, 'Orientation' => null, 'Software' => null, 'DateTime' => null, 'ExposureTime' => null,
			// 	33437 (hex 0x829D)
			'FNumber' => null,
			// 34850 (hex 0x8822)
			// The class of the program used by the camera to set exposure when the picture is taken.
			'ExposureProgram' => null, 'ExposureProgramDesc' => null,
			'ISOSpeedRatings' => null,
			// the Shutter Speed
			// The unit is the APEX (Additive System of Photographic Exposure) setting.
			'ShutterSpeedValue' => null,
			// The lens aperture.
			// The unit is the APEX (Additive System of Photographic Exposure) setting.
			'ApertureValue' => null,
			// The exposure bias.
			// Ordinarily it is given in the range of -99.99 to 99.99.
			'ExposureBiasValue' => null,
			// The smallest F number of the lens.
			// Ordinarily it is given in the range of 00.00 to 99.99, but it is not limited to this range.
			'MaxApertureValue' => null, 'MeteringMode' => null, 'MeteringModeDesc' => null,
			'LightSource' => null, 'LightSourceDesc' => null,
			'Flash' => null, 'FlashDesc' => '',
			'FocalLength' => null,
			'XResolution' => null, 'YResolution' => null,
			'ResolutionUnit' => null, 'ResolutionUnitDesc' => null,
			'Artist' => null, 'Copyright' => null,
			'GPS' => false,
		);

		// Try to load the data as-is, (most keys will map one-to-one)
		foreach($dat as $k => $v){
			if(isset($exif[$k])) $dat[$k] = $exif[$k];
		}

		// These are pulled from the COMPUTED section.
		if(isset($exif['COMPUTED'])){
			if(isset($exif['COMPUTED']['Height'])) $dat['Height'] = $exif['COMPUTED']['Height'];
			if(isset($exif['COMPUTED']['Width'])) $dat['Width'] = $exif['COMPUTED']['Width'];
		}

		// Filesize supports formatting.
		if($dat['FileSize']) $dat['FileSizeFormatted'] = Core::FormatSize($dat['FileSize'], 2);

		// Don't know why some of them are in fractions... but they are.
		foreach(array('FNumber', 'ShutterSpeedValue', 'ApertureValue', 'FocalLength', 'MaxApertureValue', 'XResolution', 'YResolution') as $k){
			if($dat[$k] && strpos($dat[$k], '/')){
				//$dat[$k] = eval('return (' . preg_replace('#[^0-9/]#', '', $dat[$k]) . ');');
				$dat[$k] = preg_replace('#[^0-9/]#', '', $dat[$k]);
			}
		}

		// This one is just all sorts of difficult...
		if(!$dat['ExposureTime']){
			// Well, it's not set.. not much I can do.
		}
		elseif(preg_match('#/1$#', $dat['ExposureTime'])){
			// This is a whole number.... simple enough!
			$dat['ExposureTime'] = substr($dat['ExposureTime'], 0, strpos($dat['ExposureTime'], '/'));
		}
		elseif(strpos($dat['ExposureTime'], '1/') === false){
			// Well, it should have damnit!  It's in a weird format like 833/10000 or 3/25....
			$n = substr($dat['ExposureTime'], 0, strpos($dat['ExposureTime'], '/'));
			$d = substr($dat['ExposureTime'], strpos($dat['ExposureTime'], '/') + 1);
			$dat['ExposureTime'] = '1/' . round($d / $n);
		}


		$dat['Dimensions'] = $dat['Width'] . ' x ' . $dat['Height'];

		switch($dat['ExposureProgram']){
			case 0: $dat['ExposureProgramDesc'] = "Not defined"; break;
			case 1: $dat['ExposureProgramDesc'] = "Manual"; break;
			case 2: $dat['ExposureProgramDesc'] = "Normal program"; break;
			case 3: $dat['ExposureProgramDesc'] = "Aperture priority"; break;
			case 4: $dat['ExposureProgramDesc'] = "Shutter priority"; break;
			case 5: $dat['ExposureProgramDesc'] = "Creative program (biased toward depth of field)"; break;
			case 6: $dat['ExposureProgramDesc'] = "Action program (biased toward fast shutter speed)"; break;
			case 7: $dat['ExposureProgramDesc'] = "Portrait mode (for closeup photos with the background out of focus)"; break;
			case 8: $dat['ExposureProgramDesc'] = "Landscape mode (for landscape photos with the background in focus)"; break;
		}

		switch($dat['MeteringMode']){
			case 0:   $dat['MeteringModeDesc'] = "Unknown"; break;
			case 1:   $dat['MeteringModeDesc'] = "Average"; break;
			case 2:   $dat['MeteringModeDesc'] = "Center-Weighted Average"; break;
			case 3:   $dat['MeteringModeDesc'] = "Spot"; break;
			case 4:   $dat['MeteringModeDesc'] = "MultiSpot"; break;
			case 5:   $dat['MeteringModeDesc'] = "Pattern"; break;
			case 6:   $dat['MeteringModeDesc'] = "Partial"; break;
			case 255: $dat['MeteringModeDesc'] = "other"; break;
		}

		switch($dat['ResolutionUnit']){
			case 2: $dat['ResolutionUnitDesc'] = 'in'; break;
			case 3: $dat['ResolutionUnitDesc'] = 'cm'; break;
		}

		switch($dat['LightSource']){
			case 0:   $dat['LightSourceDesc'] = "Unknown"; break;
			case 1:   $dat['LightSourceDesc'] = "Daylight"; break;
			case 2:   $dat['LightSourceDesc'] = "Fluorescent"; break;
			case 3:   $dat['LightSourceDesc'] = "Tungsten (incandescent light)"; break;
			case 4:   $dat['LightSourceDesc'] = "Flash"; break;
			case 9:   $dat['LightSourceDesc'] = "Fine weather"; break;
			case 10:  $dat['LightSourceDesc'] = "Cloudy weather"; break;
			case 11:  $dat['LightSourceDesc'] = "Shade"; break;
			case 12:  $dat['LightSourceDesc'] = "Daylight fluorescent (D 5700 - 7100K)"; break;
			case 13:  $dat['LightSourceDesc'] = "Day white fluorescent (N 4600 - 5400K)"; break;
			case 14:  $dat['LightSourceDesc'] = "Cool white fluorescent (W 3900 - 4500K)"; break;
			case 15:  $dat['LightSourceDesc'] = "White fluorescent (WW 3200 - 3700K)"; break;
			case 17:  $dat['LightSourceDesc'] = "Standard light A"; break;
			case 18:  $dat['LightSourceDesc'] = "Standard light B"; break;
			case 19:  $dat['LightSourceDesc'] = "Standard light C"; break;
			case 20:  $dat['LightSourceDesc'] = "D55"; break;
			case 21:  $dat['LightSourceDesc'] = "D65"; break;
			case 22:  $dat['LightSourceDesc'] = "D75"; break;
			case 23:  $dat['LightSourceDesc'] = "D50"; break;
			case 24:  $dat['LightSourceDesc'] = "ISO studio tungsten"; break;
			case 255: $dat['LightSourceDesc'] = "Other light source"; break;
		}

		/*
1 = The 0th row is at the visual top of the image, and the 0th column is the visual left-hand side.
2 = The 0th row is at the visual top of the image, and the 0th column is the visual right-hand side.
3 = The 0th row is at the visual bottom of the image, and the 0th column is the visual right-hand side.
4 = The 0th row is at the visual bottom of the image, and the 0th column is the visual left-hand side.
5 = The 0th row is the visual left-hand side of the image, and the 0th column is the visual top.
6 = The 0th row is the visual right-hand side of the image, and the 0th column is the visual top.
7 = The 0th row is the visual right-hand side of the image, and the 0th column is the visual bottom.
8 = The 0th row is the visual left-hand side of the image, and the 0th column is the visual bottom.
		 */

		/*
		Flash:

		bits: 7   6   5   4   3   2   1   0
		dec:  128,64, 32, 16, 8,  4,  2,  1

		 */
		$flashes = array();

		/*
		Values for bit 0 indicating whether the flash fired.
		0 = Flash did not fire
		1 = Flash fired
		 */
		if(($dat['Flash'] & 1) == 0){
			$flashes[] = 'Flash did not fire';
		}
		else{
			$flashes[] = 'Flash fired';
		}

		/*
		Values for bits 1 and 2 indicating the status of returned light.
		00 = No strobe return detection function
		01 = reserved
		10 = Strobe return light not detected
		11 = Strobe return light detected
		 */
		switch(($dat['Flash'] & 6)){
			case 4:
				$flashes[] = 'Strobe return light not detected';
				break;
			case 6:
				$flashes[] = 'Strobe return light detected';
				break;
		}

		/*
		Values for bits 3 and 4 indicating the camera's flash mode.
		00 = unknown
		01 = Compulsory flash firing
		10 = Compulsory flash suppression
		11 = Auto mode
		 */
		switch(($dat['Flash'] & 24)){
			case 8:
				$flashes[] = 'Compulsory flash mode';
				break;
			case 16:
				$flashes[] = 'Compulsory flash suppression';
				break;
			case 24:
				$flashes[] = 'Auto mode';
				break;
		}

		/*
		Values for bit 5 indicating the presence of a flash function.
		0 = Flash function present
		1 = No flash function
		 */
		if(($dat['Flash'] & 32) == 32){
			// No flash supported on this device, wipe out all previous information!
			$flashes = array('No flash supported');
		}

		/*
		Values for bit 6 indicating the camera's red-eye mode.
		0 = No red-eye reduction mode or unknown
		1 = Red-eye reduction supported
		 */
		if(($dat['Flash'] & 64) == 64){
			$flashes[] = 'Red eye reduction mode';
		}

		// I can now merge them back together.
		$dat['FlashDesc'] = implode(', ', $flashes);


		/*
		Indicates the latitude. The latitude is expressed as three RATIONAL values giving the degrees, minutes, and
seconds, respectively. If latitude is expressed as degrees, minutes and seconds, a typical format would be
dd/1,mm/1,ss/1. When degrees and minutes are used and, for example, fractions of minutes are given up to two
decimal places, the format would be dd/1,mmmm/100,0/1.
		 */
		if(isset($exif['GPSVersion']) && isset($exif['GPSLatitude'])){
			$dat['GPS'] = array('lat' => null, 'lng' => null, 'alt' => null);
			$dat['GPS']['lat'] = '(' . $exif['GPSLatitude'][0] . ') + ( ((' . $exif['GPSLatitude'][1] . ') * 60) + (' . $exif['GPSLatitude'][2] . ') ) / 3600';
			$dat['GPS']['lng'] = '(' . $exif['GPSLongitude'][0] . ') + ( ((' . $exif['GPSLongitude'][1] . ') * 60) + (' . $exif['GPSLongitude'][2] . ') ) / 3600';
			$dat['GPS']['alt'] = $exif['GPSAltitude'];

			foreach(array('lat', 'lng', 'alt') as $k){
				if($dat['GPS'][$k]){
					//$dat['GPS'][$k] = eval('return (' . preg_replace('#[^0-9/\+ \*\(\)]#', '', $dat['GPS'][$k]) . ');');
					$dat['GPS'][$k] = preg_replace('#[^0-9/\+ \*\(\)]#', '', $dat['GPS'][$k]);
				}
			}

			if($exif['GPSLatitudeRef'] == 'S') $dat['GPS']['lat'] = (0 - $dat['GPS']['lat']);
			if($exif['GPSLongitudeRef'] == 'W') $dat['GPS']['lng'] = (0 - $dat['GPS']['lng']);
		}

		return $dat;
	}

	/**
	 * Get an english description for the type of this file.
	 *
	 * @return string image|video|audio|file
	 */
	public function getFileType(){
		$m = $this->getOriginalFile()->getMimetype();
//var_dump($m, $this->getOriginalFile()); die();
		// I just want the beginning part
		$prefix = substr($m, 0, strpos($m, '/'));

		// There are a few exceptions.
		if($m == 'application/ogg') return 'audio';

		switch($prefix){
			case 'audio':
			case 'image':
			case 'video':
				return $prefix;
			default:
				return 'file';
		}
	}

	public function getFileExtension(){
		return $this->getOriginalFile()->getExtension();
	}
}
