<?php
/**
 * File for class MarkdownProcessor definition in the Agency-Portal project
 *
 * @package   Core
 * @author    Charlie Powell <charlie@eval.bz>
 * @date      20150820.1027
 * @copyright Copyright (C) 2009-2015  Charlie Powell
 * @license   GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
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
use Michelf\Markdown;
use Michelf\MarkdownExtra;


/**
 * A short teaser of what MarkdownProcessor does.
 *
 * More lengthy description of what MarkdownProcessor does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo    Write documentation for MarkdownProcessor
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
 * @author  Charlie Powell <charlie@eval.bz>
 *
 */
class MarkdownProcessor extends MarkdownExtra {
	private $_headerCount = 0;

	public function __construct(){
		parent::__construct();

		// Add a few features to the default Markdown formatter.

		// This will use Core to generate a unique ID for header fields.
		$this->header_id_func = [$this, 'generateHeaderID'];

		// This will add the [TOC] tag to MD.
		$this->document_gamut += ['doTOC' => 55];
	}

	/**
	 * Generate an ID from the text contents
	 *
	 * @param $text string
	 *
	 * @return string
	 */
	public function generateHeaderID($text){
		++$this->_headerCount;

		$id = 'md' . $this->_headerCount . '_' . \Core\str_to_url($text);
		return $id;
	}

	/**
	 * Adds TOC support by including the following on a single line:
	 *
	 * [TOC]
	 *
	 * TOC Requirements:
	 * * Only headings 2-6
	 * * Headings must have an ID
	 * * Builds TOC with headings _after_ the [TOC] tag
	 *
	 * @param $text
	 *
	 * @return string
	 */
	public function doTOC($text){
		$toc = '';
		if (preg_match ('/\[TOC\]/m', $text, $i, PREG_OFFSET_CAPTURE)) {
			preg_match_all ('/<h([2-6]) id="([0-9a-z_-]+)">(.*?)<\/h\1>/i', $text, $h, PREG_SET_ORDER, $i[0][1]);
			foreach ($h as &$m){
				$toc .= str_repeat ("\t", (int) $m[1]-2)."*\t [${m[3]}](#${m[2]})\n";
			}
			$text = preg_replace('/\[TOC\]/m', '<aside class="markdown-toc">' . Markdown::defaultTransform($toc) . '</aside>', $text);
			//$text = preg_replace ('/\[TOC\]/m', Markdown($toc), $text);
		}
		return trim ($text, "\n");
	}
}