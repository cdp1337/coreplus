<?php
/**
 * Enter a meaningful file description here!
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130313.1611
 * @package Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Class description here
 *
 * @package Core
 */
class Bug253Test extends TestCase {
	public function testBug(){
		$testcomponent = new Component_2_1(ROOT_PDIR . 'core/tests/testcomponent.xml');
		$this->assertInstanceOf('Component_2_1', $testcomponent);

		// Loading the component will read in the contents and get it setup.
		$testcomponent->load();
		$this->assertEquals('1.0.0', $testcomponent->getVersion());

		if($testcomponent->isInstalled()){
			// It's already installed?.... ok!
			$testcomponent->enable();
			$this->assertTrue($testcomponent->isEnabled());
		}
		else{
			// "Installing" a component should make it immediately enabled.
			$testcomponent->install();
			$this->assertTrue($testcomponent->isEnabled());
		}

		// So let's disable it!
		$testcomponent->disable();
		$this->assertFalse($testcomponent->isEnabled());

		// Now I can load it into core.
		// I couldn't do this before because the issue was that disabled components were being ignored completely.
		// This way when Core hits the component, it'll already be disabled.
		Core::Singleton()->_registerComponent($testcomponent);

		// And load up the page to make sure it's visible, (and enableable)
		// Update the current user so it has admin access.
		\Core\user()->set('admin', true);

		$request = new PageRequest('/updater');
		$request->execute();
		$view = $request->getView();
		$this->assertEquals(200, $view->error);
		// Obviously if the title gets changed, change it here to keep the bug from breaking!
		$this->assertEquals('t:STRING_SYSTEM_UPDATER', $view->title);

		// Get the body of this page and make sure that it's there.
		$html = $view->fetchBody();
		$matchtitle = 'Test Component';
		$matchmarkup = 'data-name="test-component" data-type="components"';


		$this->assertContains($matchtitle, $html, 'Failed to find the string "' . $matchtitle . '" on the updater page!');
		$this->assertContains($matchmarkup, $html, 'Failed to find the string "' . $matchmarkup . '" on the updater page!');


		// Lastly, cleanup this component!
		// Oh yeah.... uninstalling a component is needed!
		//$testcomponent->
		$this->markTestIncomplete('@todo Component uninstalling is not possible currently!');
	}
}
