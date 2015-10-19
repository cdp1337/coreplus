<?php
/**
 * File for class DefaultElement definition in the Agency-Portal project
 *
 * @package   HTMLToMD\Elements
 * @author    Charlie Powell <charlie@evalagency.com>
 * @date      20151017.1825
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

namespace HTMLToMD\Elements;
use HTMLToMD\Converter;


/**
 * A short teaser of what DefaultElement does.
 *
 * More lengthy description of what DefaultElement does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo    Write documentation for DefaultElement
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
 * @package HTMLToMD\Elements
 * @author  Charlie Powell <charlie@evalagency.com>
 *
 */
class TableElement extends DefaultElement implements ElementInterface {

	private $_columnWidths = [];

	private $_currentRow = 0;

	private $_currentCol = 0;

	private $_data = [];

	/**
	 * @return string
	 */
	public function convert() {
		$this->_parseInto($this->_node);
		$output = $this->_generateContent();

		return "\n" . $output . "\n";
	}

	/**
	 * Parse through a child node of this table, usually a tr, thead, or tbody.
	 *
	 * This is a recursive safe function.
	 *
	 * @param \DOMNode $node
	 */
	private function _parseInto(\DOMNode $node){
		if($node->hasChildNodes()){
			$nodes = $node->childNodes;
			for($i = 0; $i < $nodes->length; $i++){
				/** @var \DOMNode $node */
				$node = $nodes->item($i);
				$nodeType = $node->nodeName;

				switch($nodeType){
					case 'tr':
						// Increment to the next row on a TR tag!
						++$this->_currentRow;
						$this->_currentCol = 0;
						$this->_parseInto($node);
						break;
					case 'thead':
					case 'tbody':
						// These simply get parsed again for TR tags.
						$this->_parseInto($node);
						break;
					case 'td':
					case 'th':
						$this->_parseCell($node);
						break;
				}
			}
		}
	}

	private function _parseCell(\DOMNode $node){
		++$this->_currentCol;
		$isHeader     = $node->nodeName == 'th';
		$subElement   = $this->_parentConverter->_resolveNodeToElement($node);
		$content      = trim($subElement->convert());
		$colSpan      = $subElement->getAttribute('colspan', 1);
		$rowSpan      = $subElement->getAttribute('rowspan', 1);
		$contentWidth = strlen($content) - ($colSpan - 1) * 3; // Include merged-cell padding

		// Table cells in MD don't support newlines!
		if(strpos($content, "\n") !== false){
			$content = '';
			$contentWidth = 0;
		}

		// Set this row data
		$row = $this->_getCurrentRow();
		if($isHeader){
			$row->isHeader = true;
		}
		$row->setColumn($this->_currentCol, $content, $colSpan);

		if(!isset($this->_columnWidths[ $this->_currentCol ])){
			// Ensure that the column is set to at least something!
			$this->_columnWidths[ $this->_currentCol ] = $contentWidth;
		}

		if($colSpan == 1){
			// Simple max algorithm!
			$this->_columnWidths[ $this->_currentCol ] = max(
				$this->_columnWidths[ $this->_currentCol ],
				$contentWidth
			);
		}
		else{
			// This one requires a little more work.
			$eaWidth = floor($contentWidth / $colSpan);
			$eaTotal = 0;
			for($i = 0; $i < $colSpan; $i++){
				$x = $this->_currentCol + $i;
				$eaTotal += $eaWidth;

				// Bump the last column to include the full width!
				if($i == $colSpan - 1){
					$eaWidth += ($contentWidth - $eaTotal);
				}

				if(!isset($this->_columnWidths[ $x ])){
					// Ensure that the column is set to at least something!
					$this->_columnWidths[ $x ] = $eaWidth;
				}
				else{
					$this->_columnWidths[ $x ] = max( $this->_columnWidths[ $x ], $eaWidth );
				}
			}
		}

		if($colSpan > 1){
			// Jump to the next column in this record.
			$this->_currentCol += ($colSpan - 1);
		}
	}

	/**
	 * @return TableRow
	 */
	private function _getCurrentRow(){
		return $this->_getRow($this->_currentRow);
	}

	/**
	 * @return TableRow
	 */
	private function _getRow($rowID){
		if(!isset($this->_data[ $rowID ])){
			$r = new TableRow();
			$r->isHeader = false;
			$r->rowID = $rowID;

			$this->_data[ $rowID ] = $r;
		}

		return $this->_data[ $rowID ];
	}

	/**
	 * @return string
	 */
	private function _generateContent(){
		$output = '';
		$outerBoarder = '';
		// Now that the data has been parsed, I can build the output!
		foreach($this->_data as $row){
			/** @var TableRow $row */
			$cells = [];
			foreach($row->columns as $c){
				/** @var TableCell $c */
				$cells[] = $c->fetch($this->_columnWidths);
			}
			$output .= $outerBoarder . implode('|', $cells) . $outerBoarder . "\n";

			// Is this a header row?
			// If so, add the spacer between a TH and the columns below.
			if($row->isHeader){
				$cells = [];
				for($i = 1; $i <= sizeof($this->_columnWidths); $i++){
					$cells[] = ' ' . str_repeat('-', $this->_columnWidths[$i]) . ' ';
				}
				$output .= $outerBoarder . implode('|', $cells) . $outerBoarder . "\n";
			}
		}

		return $output;
	}
}

class TableRow{

	public $rowID = 0;
	public $isHeader = false;

	public $columns = [];

	public function setColumn($idx, $content, $cols){
		if(!isset($this->columns[$idx])){
			$c = new TableCell();
			$c->colID = $idx;
			$c->cols = (int)$cols;
			$this->columns[$idx] = $c;
		}

		$this->columns[$idx]->content = $content;
	}
}

class TableCell{
	public $colID = 0;
	public $content = '';
	public $cols = 1;

	public function fetch($widthmap){
		// Total width for this column
		$colWidth = 0;
		for($i = 0; $i < $this->cols; $i++){
			$colWidth += $widthmap[ $this->colID + $i ];
		}
		// Add in extra padding, +3 columns per extra cell.
		$colWidth += ($this->cols - 1) * 3;

		$sl = strlen($this->content);
		if($sl < $colWidth){
			$spacing = str_repeat(' ', $colWidth - $sl);
		}
		else{
			$spacing = '';
		}

		$padding = ' ';

		return $padding . $this->content . $spacing . $padding;
	}
}