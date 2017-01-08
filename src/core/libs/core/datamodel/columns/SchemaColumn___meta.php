<?php
/**
 * Created by PhpStorm.
 * User: charlie
 * Date: 4/12/16
 * Time: 3:26 PM
 */

namespace Core\Datamodel\Columns;


class SchemaColumn___meta extends SchemaColumn {
	public function __construct(){
		// Defaults
		$this->type = \Model::ATT_TYPE_META;
		$this->formAttributes['type']     = 'disabled';
	}

	/**
	 * Get the value appropriate for INSERT statements.
	 *
	 * @return string
	 */
	public function getInsertValue(){
		return null;
	}
	
	public function getDiff(SchemaColumn $col){
		return null;
	}
	
	/**
	 * Meta columns are never stored, so are always identical to something else :p
	 *
	 * @param SchemaColumn $col
	 *
	 * @return bool
	 */
	public function isIdenticalTo(SchemaColumn $col){
		return true;
	}
}