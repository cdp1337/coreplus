<?php
/**
 * File for class SpamCheck definition in the coreplus project
 * 
 * @package SecuritySuite\SpamCan
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20140411.1017
 * @copyright Copyright (C) 2009-2014  Charlie Powell
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

namespace SecuritySuite\SpamCan;
use Core\Datamodel\Dataset;


/**
 * A short teaser of what SpamCheck does.
 *
 * More lengthy description of what SpamCheck does and why it's fantastic.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for SpamCheck
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
 * @package SecuritySuite\SpamCan
 * @author Charlie Powell <charlie@eval.bz>
 *
 */
class SpamCheck {
	/** @var string The source IP address of this spam test */
	public $ip;

	/** @var string The full textual contents of this spam test */
	public $content;

	public $results = [];

	public function __construct($content){
		$this->content = $content;
	}

	/**
	 * Run this test and get the results.
	 *
	 * @return float The total score on "spammyness" of this content.
	 */
	public function check(){
		$this->results = [];
		// has HTML tags, +1 spam
		// <a tags are +3 spam.
		// each keyword is +1 spam.
		// If I can do server geolocation, then +1 for not the same country.

		$this->_checkHTML();
		$this->_checkLinks();
		$this->_checkKeywords();

		$result = 0;
		foreach($this->results as $r){
			/** @var SpamCheckResult $r */
			$result += $r->score;
		}
		return $result;
	}

	/**
	 * Extract all possible keywords from the content along with their current scores.
	 *
	 * @return array
	 */
	public function getKeywords(){
		$s = strtolower($this->content);

		$s = strip_tags($s);

		$s = preg_replace('/[^a-z0-9 ]/', '', $s);

		$stopwords = \Core\get_stop_words();

		// Add on a few more custom stop words that don't necessarily belong in upstream.
		$stopwords[] = 'id';
		$stopwords[] = 'like';

		$exploded = explode(' ', $s);
		$nt = [];
		foreach($exploded as $i => $w){
			if($w == ''){
				continue;
			}
			if(in_array($w, $stopwords)){
				continue;
			}

			$nt[] = $w;

			if(isset($exploded[$i+1])){
				$nt[] = $w . ' ' . $exploded[$i+1];
			}
			if(isset($exploded[$i+2])){
				$nt[] = $w . ' ' . $exploded[$i+1] . ' ' . $exploded[$i+2];
			}

		}
		$nt = array_unique($nt);

		sort($nt);

		$all = Dataset::Init()->select('*')->table('spam_ham_keyword')->execute();
		// Convert this to something I can quickly check through.
		$keywords = [];
		foreach($all as $row){
			$keywords[ $row['keyword'] ] = $row['score'];
		}

		$ret = [];

		foreach($nt as $w){
			if(isset($keywords[ $w ])){
				$score = $keywords[ $w ];
			}
			else{
				$score = 0;
			}

			$ret[] = [
				'keyword' => $w,
				'score' => $score,
			];
		}

		return $ret;
	}


	/*
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head>
<style type="text/css">#page_wrapper {	margin:0 10px 10px; } #apply-form {	width:100%; } </style>
<title>:/</title>
</head>
<body><h1>:/</h1><table><tr><td><div id="page_wrapper"><form id="apply-form" action="#" method="post"><div style="display:none"><input type="text" name="test"/></div></div></td><td width="1"></td></tr></table><p>Request Blocked</p></body>
</html>
	 */

	/**
	 * Check if the content has HTML content.
	 */
	private function _checkHTML(){
		if(strpos($this->content, '<') !== false && strpos($this->content, '>') !== false){
			$this->_addResult('Has HTML Content', 1);
		}
	}

	private function _checkLinks(){
		// Since all links require :// in them, (the protocol), we'll use that as the basis of the check.
		$score = preg_match_all('#://#', $this->content);

		if($score == 1){
			$this->_addResult('Has ' . $score . ' link', $score * $score);
		}
		elseif($score > 1){
			$this->_addResult('Has ' . $score . ' links', $score * $score);
		}
	}

	private function _checkKeywords(){
		$keywords = $this->getKeywords();

		foreach($keywords as $dat){
			if($dat['score'] != 0){
				$this->_addResult('Contains the word or phrase "' . $dat['keyword'] . '"', $dat['score']);
			}
		}
	}

	private function _addResult($comment, $score){
		$r = new SpamCheckResult();
		$r->score = (float)$score;
		$r->comment = $comment;
		$this->results[] = $r;
	}
}

class SpamCheckResult {
	public $score;
	public $comment;
}