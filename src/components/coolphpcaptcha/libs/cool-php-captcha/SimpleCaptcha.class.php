<?php
/**
 * SimpleCaptcha class
 *
 * @author  Jose Rodriguez <jose.rodriguez@exec.cl>
 * @author  Charlie Powell <charlie@eval.bz>
 * @link    http://code.google.com/p/cool-php-captcha
 * @package CoolPHPCaptcha
 * @copyright Copyright (C) 2009-2013  Charlie Powell
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
 * Provides the logic to the SimpleCaptcha system.
 *
 */
class SimpleCaptcha {

	/**
	 * @var int Width (in pixels) of the image
	 */
	public $width  = 150;

	/**
	 * @var int Height (in pixels) of the image
	 */
	public $height = 60;

	/**
	 * Path for resource files (fonts, words, etc.)
	 *
	 * "resources" by default. For security reasons, is better move this
	 * directory to another location outise the web server
	 *
	 */
	public $resourcesPath = 'resources';

	/**
	 * @var int Minimum word length
	 */
	public $minWordLength = 5;

	/**
	 * @var int Maximum word length
	 */
	public $maxWordLength = 8;

	/**
	 * @var string Session name to store the original text
	 */
	public $sessionVar = 'captcha';

	/**
	 * @var array Background color in RGB-array
	 */
	public $backgroundColor = array(255, 255, 255);

	/**
	 * @var array Foreground colors in RGB-array
	 */
	public $colors = array(
	    array(27,78,181), // blue
	   // array(22,163,35), // green
	    array(214,36,7),  // red
	);

	/**
	 * @var array|null Shadow color in RGB-array or null to disable
	 */
	public $shadowColor = null; //array(0, 0, 0);

	/**
	 * @var int Horizontal line through the text
	 */
	public $lineWidth = 0;

	/**
	 * Font configuration
	 *
	 * - font: TTF file
	 * - spacing: relative pixel space between character
	 * - minSize: min font size
	 * - maxSize: max font size
	 */
	public $fonts = array(
	    'Antykwa'  => array('spacing' => -3, 'minSize' => 27, 'maxSize' => 30, 'font' => 'AntykwaBold.ttf'),
	    'Candice'  => array('spacing' =>-1.5,'minSize' => 28, 'maxSize' => 31, 'font' => 'Candice.ttf'),
	    'DingDong' => array('spacing' => -2, 'minSize' => 24, 'maxSize' => 30, 'font' => 'Ding-DongDaddyO.ttf'),
	    'Duality'  => array('spacing' => -2, 'minSize' => 30, 'maxSize' => 38, 'font' => 'Duality.ttf'),
	    'Heineken' => array('spacing' => -2, 'minSize' => 24, 'maxSize' => 34, 'font' => 'Heineken.ttf'),
	    'Jura'     => array('spacing' => -2, 'minSize' => 28, 'maxSize' => 32, 'font' => 'Jura.ttf'),
	    'StayPuft' => array('spacing' =>-1.5,'minSize' => 28, 'maxSize' => 32, 'font' => 'StayPuft.ttf'),
	    'Times'    => array('spacing' => -2, 'minSize' => 28, 'maxSize' => 34, 'font' => 'TimesNewRomanBold.ttf'),
	    'VeraSans' => array('spacing' => -1, 'minSize' => 20, 'maxSize' => 28, 'font' => 'VeraSansBold.ttf'),
	);


	/** Wave configuracion in X and Y axes */
	public $Yperiod    = 12;
	public $Yamplitude = 14;
	public $Xperiod    = 11;
	public $Xamplitude = 2;

	/** letter rotation clockwise */
	public $maxRotation = 4;

	/**
	 * Internal image size factor (for better image quality)
	 * 1: low, 2: medium, 3: high
	 */
	public $scale = 3;

	/**
	 * Blur effect for better image quality (but slower image processing).
	 * Better image results with scale=3
	 */
	public $blur = false;

	/** Debug? */
	public $debug = false;

	/**
	 * @var string Image format: jpeg or png
	 */
	public $imageFormat = 'png';


	/** GD image */
	public $im;

	/**
	 * @var int The GD color for the text, set internally.
	 */
	private $gdFgColor;

	/**
	 * @var int The GD color for the background, set internally.
	 */
	private $gdBgColor;

	/**
	 * @var int The GD color for the text shadow, set internally.
	 */
	private $gdShadowColor;

	/**
	 * @var String The final text to display on the image, set internally.
	 */
	private $textFinalX;


	public function __construct() {
		// Load in the settings from Core.

		$this->width         = ConfigHandler::Get('/captcha/width');
		$this->height        = ConfigHandler::Get('/captcha/height');
		$this->minWordLength = ConfigHandler::Get('/captcha/minlength');
		$this->maxWordLength = ConfigHandler::Get('/captcha/maxlength');
		$this->lineWidth     = ConfigHandler::Get('/captcha/linethrough');
		$this->Yperiod       = ConfigHandler::Get('/captcha/yperiod');
		$this->Yamplitude    = ConfigHandler::Get('/captcha/yamplitude');
		$this->Xperiod       = ConfigHandler::Get('/captcha/xperiod');
		$this->Xamplitude    = ConfigHandler::Get('/captcha/xamplitude');
		$this->maxRotation   = ConfigHandler::Get('/captcha/maxrotation');
		$this->blur          = ConfigHandler::Get('/captcha/blur');

		// Ensure it knows where to look for the "resources"...
		$this->resourcesPath = Core::GetComponent('coolphpcaptcha')->getBaseDir() . "libs/cool-php-captcha/resources";
	}


	public function createImage() {
		$ini = microtime(true);

		/** Initialization */
		$this->imageAllocate();

		/** Text insertion */
		$text = $this->getCaptchaText();
		$fontcfg  = $this->fonts[array_rand($this->fonts)];
		$this->writeText($text, $fontcfg);

		$_SESSION[$this->sessionVar] = $text;

		/** Transformations */
		if (!empty($this->lineWidth)) {
			$this->writeLine();
		}
		$this->waveImage();
		if ($this->blur && function_exists('imagefilter')) {
			imagefilter($this->im, IMG_FILTER_GAUSSIAN_BLUR);
		}
		$this->reduceImage();


		if ($this->debug) {
			imagestring(
				$this->im, 1, 1, $this->height-8,
				"$text {$fontcfg['font']} ".round((microtime(true)-$ini)*1000)."ms",
				$this->gdFgColor
			);
		}


		/** Output */
		$this->writeImage();
		$this->cleanup();
	}


	/**
	 * Creates the image resources
	 */
	protected function imageAllocate() {
		// Cleanup
		if (!empty($this->im)) {
			imagedestroy($this->im);
		}

		$this->im = imagecreatetruecolor($this->width*$this->scale, $this->height*$this->scale);

		// Background color
		$this->gdBgColor = imagecolorallocate(
			$this->im,
			$this->backgroundColor[0],
			$this->backgroundColor[1],
			$this->backgroundColor[2]
		);
		imagefilledrectangle($this->im, 0, 0, $this->width*$this->scale, $this->height*$this->scale, $this->gdBgColor);

		// Foreground color
		$color           = $this->colors[mt_rand(0, sizeof($this->colors)-1)];
		$this->gdFgColor = imagecolorallocate($this->im, $color[0], $color[1], $color[2]);

		// Shadow color
		if (!empty($this->shadowColor) && is_array($this->shadowColor) && sizeof($this->shadowColor) >= 3) {
			$this->gdShadowColor = imagecolorallocate(
				$this->im,
				$this->shadowColor[0],
				$this->shadowColor[1],
				$this->shadowColor[2]
			);
		}
	}


	/**
	 * Text generation
	 *
	 * @return string Text
	 */
	protected function getCaptchaText() {
		return $this->getRandomCaptchaText();
	}


	/**
	 * Random text generation
	 *
	 * @param $length int
	 * @return string Text
	 */
	protected function getRandomCaptchaText($length = null) {
		if (empty($length)) {
			$length = rand($this->minWordLength, $this->maxWordLength);
		}

		$words  = "abcdefghijlmnopqrstvwyz";
		$vocals = "aeiou";

		$text  = "";
		for ($i=0; $i<$length; $i++) {
			$vocal = rand(0, 1);

			if ($vocal) {
				$text .= substr($vocals, mt_rand(0, 4), 1);
			} else {
				$text .= substr($words, mt_rand(0, 22), 1);
			}
		}
		return $text;
	}



	/**
	 * Horizontal line insertion
	 */
	protected function writeLine() {
		$x1 = $this->width*$this->scale*.15;
		$x2 = $this->textFinalX;
		$y1 = rand($this->height*$this->scale*.40, $this->height*$this->scale*.65);
		$y2 = rand($this->height*$this->scale*.40, $this->height*$this->scale*.65);
		$width = $this->lineWidth/2*$this->scale;

		for ($i = $width*-1; $i <= $width; $i++) {
			imageline($this->im, $x1, $y1+$i, $x2, $y2+$i, $this->gdFgColor);
		}
	}



	/**
	 * Text insertion
	 */
	protected function writeText($text, $fontcfg = array()) {
		if (empty($fontcfg)) {
			// Select the font configuration
			$fontcfg  = $this->fonts[array_rand($this->fonts)];
		}

		// Full path of font file
		$fontfile = $this->resourcesPath.'/fonts/'.$fontcfg['font'];


		/** Increase font-size for shortest words: 9% for each glyp missing */
		$lettersMissing = $this->maxWordLength-strlen($text);
		$fontSizefactor = 1+($lettersMissing*0.09);

		// Text generation (char by char)
		$x      = 20*$this->scale;
		$y      = round(($this->height*27/40)*$this->scale);
		$length = strlen($text);
		for ($i=0; $i<$length; $i++) {
			$degree   = rand($this->maxRotation*-1, $this->maxRotation);
			$fontsize = rand($fontcfg['minSize'], $fontcfg['maxSize'])*$this->scale*$fontSizefactor;
			$letter   = substr($text, $i, 1);

			if ($this->shadowColor) {
				$coords = imagettftext(
					$this->im, $fontsize, $degree,
					$x+$this->scale, $y+$this->scale,
					$this->gdShadowColor, $fontfile, $letter
				);
			}
			$coords = imagettftext(
				$this->im, $fontsize, $degree,
				$x, $y,
				$this->gdFgColor, $fontfile, $letter
			);
			$x += ($coords[2]-$x) + ($fontcfg['spacing']*$this->scale);
		}

		$this->textFinalX = $x;
	}


	/**
	 * Wave filter
	 */
	protected function waveImage() {
		// X-axis wave generation
		$xp = $this->scale*$this->Xperiod*rand(1,3);
		$k = rand(0, 100);
		for ($i = 0; $i < ($this->width*$this->scale); $i++) {
			imagecopy(
				$this->im,
				$this->im,
				$i-1,
				sin($k+$i/$xp) * ($this->scale*$this->Xamplitude),
				$i,
				0,
				1,
				$this->height*$this->scale
			);
		}

		// Y-axis wave generation
		$k = rand(0, 100);
		$yp = $this->scale*$this->Yperiod*rand(1,2);
		for ($i = 0; $i < ($this->height*$this->scale); $i++) {
			imagecopy(
				$this->im,
				$this->im,
				sin($k+$i/$yp) * ($this->scale*$this->Yamplitude),
				$i-1,
				0,
				$i,
				$this->width*$this->scale,
				1
			);
		}
	}


	/**
	 * Reduce the image to the final size
	 */
	protected function reduceImage() {
		// Reduce the size of the image
		$imResampled = imagecreatetruecolor($this->width, $this->height);
		imagecopyresampled($imResampled, $this->im,
			0, 0, 0, 0,
			$this->width, $this->height,
			$this->width*$this->scale, $this->height*$this->scale
		);
		imagedestroy($this->im);
		$this->im = $imResampled;
	}



	/**
	 * File generation
	 */
	protected function writeImage() {
		if ($this->imageFormat == 'png' && function_exists('imagepng')) {
			header("Content-type: image/png");
			imagepng($this->im);
		} else {
			header("Content-type: image/jpeg");
			imagejpeg($this->im, null, 80);
		}
	}


	/**
	 * Cleanup
	 */
	protected function cleanup() {
		imagedestroy($this->im);
	}
}