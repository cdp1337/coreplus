<?php
/**
 * File for class ModelResult definition in the tenant-visitor project
 * 
 * @package Core\Search
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20131122.1942
 * @copyright Copyright (C) 2009-2016  Charlie Powell
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

namespace Core\Search;
use Core\Templates\Template;


/**
 * A search result from a model.
 *
 * Primary difference is that it uses a template to render the Model instead of just the basic title.
 *
 * <h3>Usage Examples</h3>
 *
 *
 * @todo Write documentation for ModelResult
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
 * @package Core\Search
 * @author Charlie Powell <charlie@evalagency.com>
 *
 */
class ModelResult extends Result {
	/** @var  \Model The model record associated to this search result. */
	public $_model;

	/**
	 * @param string $query The original query, used to calculate the relevancy.
	 * @param \Model $model The located model.
	 */
	public function __construct($query, \Model $model){
		$this->_model = $model;
		$this->query = $query;

		$this->_calculateRelevancy();
	}

	/**
	 * Get this result entry as rendered HTML.
	 *
	 * @return string
	 */
	public function fetch(){
		$modelname = get_class($this->_model);
		// Trim off the "Model" part
		$modelname = substr($modelname, 0, -5);
		// And lowercase it.
		$modelname = strtolower($modelname);

		if(!Template::ResolveFile('search/model/' . $modelname . '.tpl')){
			$out = parent::fetch();
			if(DEVELOPMENT_MODE){
				$out .= '<p class="message-error">Unable to find template [/search/model/' . $modelname . '.tpl] !</p>';
			}
			return $out;
		}

		$tpl = Template::Factory('search/model/' . $modelname . '.tpl');
		$tpl->assign('result', $this);
		$tpl->assign('model', $this->_model);
		return $tpl->fetch();
	}

	private function _calculateRelevancy(){
		// Lowercase it.
		$query       = strtolower($this->query);
		// Convert this string to latin.
		$query       = \Core\str_to_latin($query);
		// Skip punctuation.
		$query       = preg_replace('/[^a-z0-9 ]/', '', $query);
		// Split out words.
		$parts       = explode(' ', $query);

		$ignore = Helper::GetSkipWords();
		foreach($parts as $k => $word){
			// Skip blank words.
			if(!$word){
				unset($parts[$k]);
				continue;
			}
			// Unset any to-be-skipped word.
			if(in_array($word, $ignore)){
				unset($parts[$k]);
				continue;
			}
		}

		// All query words in the result mean 100% relevancy.
		$size = sizeof($parts);
		if(!$size){
			$this->relevancy = 0;
			return;
		}

		$wordweight = 100 / $size;
		// And each word has 3 parts to it.
		$wordweight /= 3;

		$rel = 0.0;

		$str = explode(' ', $this->_model->get('search_index_str'));
		$pri = explode(' ', $this->_model->get('search_index_pri'));
		$sec = explode(' ', $this->_model->get('search_index_sec'));

		foreach($parts as $word){
			$it = new DoubleMetaPhone($word);

			if(in_array($word, $str)){
				// Exact matches here get an automatic boost!
				$rel += ($wordweight * 3);
			}
			else{
				foreach($str as $w){
					if(strpos($w, $word) !== false){
						// If a partial match is located, add a fraction of the word weight, (since it wasn't a complete match).
						$rel += $wordweight * (strlen($word) / strlen($w));
						break;
					}
				}
			}

			if(in_array($it->primary, $pri)) $rel += $wordweight;
			if(in_array($it->secondary, $sec)) $rel += $wordweight;
		}

		$this->relevancy = min($rel, 100);
	}
}
