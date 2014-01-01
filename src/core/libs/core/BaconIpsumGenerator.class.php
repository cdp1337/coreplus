<?php
/**
 * File for the BaconIpsumGenerator class.
 */

/**
 * Bacon Ipsum Generator.
 *
 * <h3>Usage</h3>
 * <code>
 * // Create a new object
 * $bacon = new BaconIpsumGenerator();
 *
 * // Do we want filler or just meat?
 * $bacon->includeFiller = true;
 * // $bacon->includeFiller = false;
 *
 * // Give me.... 4 sentences!
 * echo $bacon->getParagraph(4);
 *
 * // How 'bout... a bunch of paragraphs!  I need to fill up a blog article with BACON!
 * echo $bacon->getParagraphsAsMarkup(25);
 *
 * </code>
 *
 * @author Pete Nelson (@GunGeekATX)
 * @author Charlie Powell <charlie@eval.bz>
 * @link https://github.com/petenelson/bacon-ipsum Original code
 *
 * @version 2.1.3~cdp1337-1
 */
class BaconIpsumGenerator {

	/**
	 * @var bool Set to false to return only meat.
	 */
	public $includeFiller = true;

	private $meat = array(
		'beef',
		'chicken',
		'pork',
		'bacon',
		'chuck',
		'short loin',
		'sirloin',
		'shank',
		'flank',
		'sausage',
		'pork belly',
		'shoulder',
		'cow',
		'pig',
		'ground round',
		'hamburger',
		'meatball',
		'tenderloin',
		'strip steak',
		't-bone',
		'ribeye',
		'shankle',
		'tongue',
		'tail',
		'pork chop',
		'pastrami',
		'corned beef',
		'jerky',
		'ham',
		'fatback',
		'ham hock',
		'pancetta',
		'pork loin',
		'short ribs',
		'spare ribs',
		'beef ribs',
		'drumstick',
		'tri-tip',
		'ball tip',
		'venison',
		'turkey',
		'biltong',
		'rump',
		'jowl',
		'salami',
		'bresaola',
		'meatloaf',
		'brisket',
		'boudin',
		'andouille',
		'capicola',
		'swine',
		'kielbasa',
		'frankfurter',
		'prosciutto',
		'filet mignon',
		'leberkas',
		'turducken',
		'doner'
	);

	private $filler = array(
		'consectetur',
		'adipisicing',
		'elit',
		'sed',
		'do',
		'eiusmod',
		'tempor',
		'incididunt',
		'ut',
		'labore',
		'et',
		'dolore',
		'magna',
		'aliqua',
		'ut',
		'enim',
		'ad',
		'minim',
		'veniam',
		'quis',
		'nostrud',
		'exercitation',
		'ullamco',
		'laboris',
		'nisi',
		'ut',
		'aliquip',
		'ex',
		'ea',
		'commodo',
		'consequat',
		'duis',
		'aute',
		'irure',
		'dolor',
		'in',
		'reprehenderit',
		'in',
		'voluptate',
		'velit',
		'esse',
		'cillum',
		'dolore',
		'eu',
		'fugiat',
		'nulla',
		'pariatur',
		'excepteur',
		'sint',
		'occaecat',
		'cupidatat',
		'non',
		'proident',
		'sunt',
		'in',
		'culpa',
		'qui',
		'officia',
		'deserunt',
		'mollit',
		'anim',
		'id',
		'est',
		'laborum'
	);


	/**
	 * Get a complete sentence.
	 *
	 * @return string
	 */
	public function getSentence()	{
		// A sentence should be between 4 and 15 words.
		$sentence = '';
		$length = rand(4, 15);

		// Add a little more randomness to commas, about 2/3rds of the time
		$includeComma = ($length >= 7 && rand(0,2) > 0);

		$words = $this->_getWords();

		if (count($words) < 1) {
			// What?
			return '';
		}

		// Capitalize the first word.
		$words[0] =  ucfirst($words[0]);

		for ($i = 0; $i < $length; $i++) {
			if ($i > 0) {
				if ($i >= 3 && $i != $length - 1 && $includeComma) {
					if (rand(0,1) == 1) {
						$sentence = rtrim($sentence) . ', ';
						$includeComma = false;
					}
					else{
						$sentence .= ' ';
					}
				}
				else{
					$sentence .= ' ';
				}
			}
			$sentence .= $words[$i];
		}

		$sentence = rtrim($sentence) . '. ';

		return $sentence;
	}

	/**
	 * Get a complete paragraph
	 *
	 * @param  int $length Number of sentences to include, set to 0 for random.
	 * @return string
	 */
	public function getParagraph($length = 0) {
		$para = '';

		// A paragraph should be between 4 and 7 sentences if not otherwise set.
		if($length == 0) $length = rand(4, 7);

		for ($i = 0; $i < $length; $i++){
			$para .= $this->getSentence() . ' ';
		}

		return rtrim($para);
	}

	/**
	 * Get a set of complete paragraphs as an array
	 *
	 * @param int    $number Number of paragraphs to return
	 * @param string $prefix Prefix string, (or blank), to start it with.
	 * @return array
	 */
	public function getParagraphs($number = 5, $prefix = 'Bacon ipsum dolor sit amet '){
		$paragraphs = array();

		// I need at least 1 paragraph.
		if($number < 1) $number = 1;
		// but not too many
		if($number > 99) $number = 99;

		if($prefix){
			// If a prefix was requested, make sure that the first paragraph is lowercase.
			$paragraphs[] = $prefix . strtolower($this->getSentence()) . ' ' . $this->getParagraph();
		}
		else{
			// Doesn't matter.
			$paragraphs[] = $this->getParagraph();
		}

		for ($i = 1; $i < $number; $i++) {
			$paragraphs[] = $this->getParagraph();
		}

		return $paragraphs;
	}

	/**
	 * Get a set of complete paragraphs as an HTML encoded string
	 *
	 * @param int    $number Number of paragraphs to return
	 * @param string $prefix Prefix string, (or blank), to start it with.
	 * @return string
	 */
	public function getParagraphsAsMarkup($number = 5, $prefix = 'Bacon ipsum dolor sit amet '){
		$paragraphs = $this->getParagraphs($number, $prefix);

		$out = '';
		foreach($paragraphs as $p){
			$out .= '<p>' . $p . '</p>' . "\n";
		}
		return $out;
	}


	### COMPATIBILITY LAYER ####

	public static function GetWords($type = 'meat-and-filler'){
		$bacon = self::_CompatFactory($type);
		return $bacon->_getWords();
	}

	public static function Make_a_Sentence($type = 'meat-and-filler') {
		$bacon = self::_CompatFactory($type);
		return $bacon->getSentence();
	}

	public static function Make_a_Paragraph($type = 'meat-and-filler') {
		$bacon = self::_CompatFactory($type);
		return $bacon->getParagraph();
	}

	public static function Make_Some_Meaty_Filler(
		$type = 'meat-and-filler',
		$number_of_paragraphs = 5,
		$start_with_lorem = true,
		$number_of_sentences = 0
	) {
		$bacon = self::_CompatFactory($type);
		$prefix = ($start_with_lorem) ? 'Bacon ipsum dolor sit amet ' : '';

		// Were sentences requested?
		if($number_of_sentences > 0){
			$out = '';

			// First sentence, (prefix with the $prefix if requested).
			if($prefix){
				$out .= $prefix . strtolower($bacon->getSentence()) . ' ';
			}
			else{
				$out .= $bacon->getSentence() . ' ';
			}

			// And the rest.
			$out .= $bacon->getParagraph($number_of_sentences - 1);

			return $out;
		}
		else{
			return $bacon->getParagraphs($number_of_paragraphs, $prefix);
		}
	}

	/**
	 * Create a new Bacon based on a legacy type.  Useful for the compatibility methods.
	 *
	 * @param string $type
	 * @return BaconIpsumGenerator
	 */
	private static function _CompatFactory($type){
		$bacon = new BaconIpsumGenerator();

		if($type == 'meat-and-filler'){
			$bacon->includeFiller = true;
		}
		else{
			$bacon->includeFiller = false;
		}

		return $bacon;
	}



	/**
	 * Get a shuffled set of words from the dictionary set.
	 *
	 * @return array The array of words, shuffled and ready for use.
	 */
	private function _getWords() {
		if($this->includeFiller){
			$words = array_merge($this->meat, $this->filler);
		}
		else{
			$words = $this->meat;
		}

		shuffle($words);

		return $words;
	}
}
