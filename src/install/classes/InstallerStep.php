<?php
/**
 * The scaffolding class for installer steps.
 * 
 * @author Charlie Powell <charlie@eval.bz>
 * @date 20130320.0620
 * @package Core\Installer
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 * @since 2013.03.20
 */

namespace Core\Installer;

use Core\Templates;

/**
 * The scaffolding class for installer steps.
 */
abstract class InstallerStep {

	private $_template;

	public $title = '';

	public function execute(){

	}

	public function render() {
		$body = $this->getTemplate()->fetch();

		$title = 'Core Plus Installation';
		if($this->title) $title .= ' [' . $this->title . ']';

		// This of course gets wrapped in the skin template.
		$skin = Templates\Template::Factory(ROOT_PDIR . 'install/templates/skin.phtml');
		$skin->assign('title', $title);
		$skin->assign('body', $body);
		$skin->render(ROOT_PDIR . 'install/templates/skin.phtml');
	}

	/**
	 * Get the template for this page's skin.
	 *
	 * @return \TemplatePHTML
	 */
	protected function getTemplate() {
		if($this->_template === null){

			$c = get_called_class();
			$template = str_replace('Core\\Installer\\', '', $c);
			$template = strtolower($template);
			$this->_template = Templates\Template::Factory(ROOT_PDIR . 'install/templates/' . $template . '.phtml');
		}

		return $this->_template;
	}

	/**
	 * Check if this step has passed.
	 *
	 * @return bool
	 */
	public function hasPassed(){
		// Check in the session and see if this class has been flagged as good.
		$c = get_called_class();
		if(!isset($_SESSION['passes'])) return false;
		if(!isset($_SESSION['passes'][$c])) return false;
		if($_SESSION['passes'][$c]) return true;

		return false;
	}

	/**
	 * Set this step as passed (or failed).
	 *
	 * @param bool $passed [true] Pass status, defaults to true
	 */
	protected function setAsPassed($passed = true) {
		if(!isset($_SESSION['passes'])) $_SESSION['passes'] = array();

		$c = get_called_class();
		$_SESSION['passes'][$c] = $passed;
	}
}
