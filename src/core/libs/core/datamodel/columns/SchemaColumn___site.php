<?php
/**
 * Created by PhpStorm.
 * User: charlie
 * Date: 4/12/16
 * Time: 3:26 PM
 */

namespace Core\Datamodel\Columns;


class SchemaColumn___site extends SchemaColumn {
	public function __construct(){
		// Defaults
		$this->type = \Model::ATT_TYPE_SITE;
		$this->maxlength = 15;
		$this->default = 0;
		$this->comment = 'The site id in multisite mode, (or -1 if global)';
		$this->formAttributes['type'] = 'system';
		// Sites have the option to pull from the multisite system when enabled.
		$this->formAttributes['source'] = 'MultiSiteModel::GetAllAsOptions';
	}

	/**
	 * Get the value appropriate for INSERT statements.
	 *
	 * @return string
	 */
	public function getInsertValue(){
		if($this->value === null || $this->value === false){
			if(\Core::IsComponentAvailable('multisite') && \MultiSiteHelper::IsEnabled()){
				$this->setValueFromApp(\MultiSiteHelper::GetCurrentSiteID());	
			}
			else{
				$this->setValueFromApp(0);
			}
		}

		return $this->value;
	}
}