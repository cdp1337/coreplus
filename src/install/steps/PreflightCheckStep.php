<?php
/**
 * File for class PreflightCheckStep definition in the coreplus project
 * 
 * @author Charlie Powell <charlie@evalagency.com>
 * @date 20130320.0640
 * @package Core\Installer
 */

namespace Core\Installer;


/**
 * Class PreflightCheckStep description
 *
 * @package Core\Installer
 */
class PreflightCheckStep extends InstallerStep {
	public function execute() {
		// Some more preflight checks, such as htaccess presence and permissions.
		// See https://rm.eval.bz/issues/29 for more info.

		$tests = array(
			$this->testPHPVersion(),
			$this->testPHPMemoryLimit(),
			$this->testExtension(
				'bcadd',
				'BCMath',
				'bcmath',
				[
					'debian' => 'apt-get install php7.0-bcmath' . NL . 'systemctl restart apache2',
					'redhat' => 'yum install php7.0-bcmath' . NL . 'systemctl restart httpd',
					'suse' => 'zypper install php7.0-bcmath' . NL . 'systemctl restart apache2',
				]
			),
			$this->testExtension(
				'hash',
				'Cryptographic Hash',
				'php-hash',
				[
					'debian' => 'Recompile PHP without the "--disable-hash" flag!',
					'redhat' => 'Recompile PHP without the "--disable-hash" flag!',
					'suse' => 'Recompile PHP without the "--disable-hash" flag!',
				]
			),
			$this->testExtension(
				'curl_exec',
				'cURL',
				'php-curl',
				[
					'debian' => 'apt-get install php7.0-curl' . NL . 'systemctl restart apache2',
					'redhat' => 'yum install php7.0-curl' . NL . 'systemctl restart httpd',
					'suse' => 'zypper install php7.0-curl' . NL . 'systemctl restart apache2',
				]
			),
			$this->testExtension(
				'imagecreatefromgd',
				'Graphics Draw "GD"',
				'php-gd',
				[
					'debian' => 'apt-get install php7.0-gd' . NL . 'systemctl restart apache2',
					'redhat' => 'yum install php7.0-gd' . NL . 'systemctl restart httpd',
					'suse' => 'zypper install php7.0-gd' . NL . 'systemctl restart apache2',
				]
			),
			$this->testExtension(
				'mcrypt_encrypt',
				'MCrypt',
				'php-mcrypt',
				[
					'debian' => 'apt-get install php7.0-mcrypt' . NL . 'systemctl restart apache2',
					'redhat' => 'yum install php7.0-mcrypt' . NL . 'systemctl restart httpd',
					'suse' => 'zypper install php7.0-mcrypt' . NL . 'systemctl restart apache2',
				]
			),
			$this->testExtension(
				'mb_check_encoding',
				'MultiByte Strings',
				'php-mbstring',
				[
					'debian' => 'apt-get install php7.0-mbstring' . NL . 'systemctl restart apache2',
					'redhat' => 'yum install php7.0-mbstring' . NL . 'systemctl restart httpd',
					'suse' => 'zypper install php7.0-mbstring' . NL . 'systemctl restart apache2',
				]
			),
			$this->testExtension(
				'xml_parse',
				'XML',
				'php-xml',
				[
					'debian' => 'apt-get install php7.0-xml' . NL . 'systemctl restart apache2',
					'redhat' => 'yum install php7.0-xml' . NL . 'systemctl restart httpd',
					'suse' => 'zypper install php7.0-xml' . NL . 'systemctl restart apache2',
				]
			),
			$this->testExtension(
				'zlib_decode',
				'ZLib',
				'php-zlib',
				[
					'debian' => 'Recompile PHP with the "--with-zlib" flag!',
					'redhat' => 'Recompile PHP with the "--with-zlib" flag!',
					'suse' => 'Recompile PHP with the "--with-zlib" flag!',
				]
			),
			$this->testRewrite(),
			$this->testHTAccessFile(),
			$this->testConfigFile(),
			$this->testNDirectory('logs', 'Logs'),
			$this->testNDirectory('files', 'Files'),
			$this->testNDirectory('themes/custom', 'Custom Theming'),
		);

		// Run through all these checks and see if there were any errors.
		$good = true;
		foreach($tests as $test){
			if($test['status'] == 'error') $good = false;
		}

		if($good && $_SERVER['REQUEST_METHOD'] == 'POST'){
			// user clicked "next"
			// Mark this task as passed and proceed to the next.
			$this->setAsPassed();
			reload($this->stepCurrent + 1);
		}

		$tpl = $this->getTemplate();

		$this->title = 'Preflight Checks';
		$tpl->assign('tests', $tests);
		$tpl->assign('good', $good);
	}

	/**
	 * Test that the PHP version is new enough.
	 *
	 * @return array
	 */
	private function testPHPVersion() {
		$version = phpversion();

		if(version_compare($version, '7.0.0', '<')){
			return [
				'title' => 'PHP Version',
				'status' => 'error',
				'message' => 'php is too old',
				'description' => 'Your version of PHP is ' . $version . '.  The bare minimum required is 7.0.0!  Please upgrade PHP before proceeding.'
			];
		}
/*
		if(version_compare($version, '5.5.0', '<')){
			return [
				'title' => 'PHP Version',
				'status' => 'warning',
				'message' => 'php might be too old',
				'description' => 'Your version of PHP is ' . $version . '.  It is probably a good idea to upgrade to newest version, ya know, for security and all.',
			];
		}
*/
		return [
			'title' => 'PHP Version',
			'status' => 'passed',
			'message' => 'PHP version is ' . $version . '.',
			'description' => '',
		];
	}


	/**
	 * Test that the PHP memory_limit is large enough.
	 *
	 * @return array
	 */
	private function testPHPMemoryLimit() {
		$mlimit = ini_get('memory_limit');
		$munits = substr($mlimit, -1);
		$msize  = substr($mlimit, 0, -1);

		if($munits == 'G'){
			$msize = $msize * 1024 * 1024 * 1024;
		}
		else if($munits == 'M'){
			$msize = $msize * 1024 * 1024;
		}
		else if($munits == 'K'){
			$msize = $msize * 1024;
		}


		if($msize < 128 * 1024 * 1024){
			return [
				'title' => 'PHP Memory Limit',
				'status' => 'error',
				'message' => 'Core Plus requires at least 128Mb of memory in the memory_limit directive.',
			];
		}

		return [
			'title' => 'PHP Memory Limit',
			'status' => 'passed',
			'message' => 'PHP memory limit is ' . $mlimit,
			'description' => '',
		];
	}


	/**
	 * Test that (N) is available.
	 *
	 * @return array
	 */
	private function testExtension($function, $title, $key, $fixes) {

		if( !function_exists($function) ){
			$fix = isset($fixes[SERVER_FAMILY]) ? $fixes[SERVER_FAMILY] : null;
			
			return [
				'title' => $title,
				'status' => 'error',
				'message' => $key . ' is missing!',
				'fix' => $fix,
			];
		}

		return [
			'title' => $title,
			'status' => 'passed',
			'message' => $key . ' is available!',
		];
	}

	/**
	 * Check that mod_rewrite is available.
	 *
	 * @return array
	 */
	private function testRewrite() {
		// Check if mod_rewrite is available
		// This will only be the case if php is running in native mode.
		if(function_exists('apache_get_modules')){
			if(!in_array('mod_rewrite', apache_get_modules())){
				// @todo Write blog post about mod_rewrite and how to enable it on different servers.
				return [
					'title' => 'Mod Rewrite',
					'status' => 'error',
					'message' => 'mod_rewrite is not available',
					'description' => 'In order to use Core Plus, the apache module mod_rewrite must be installed and enabled!'
				];
			}
			else{
				return [
					'title' => 'Mod Rewrite',
					'status' => 'passed',
					'message' => 'mod_rewrite is available!',
					'description' => 'The module "mod_rewrite" was located as a native apache module.',
				];
			}
		}
		else{
			// This is not working again.... gah
			// PHP is running as CGI.... guess I have to do this the long way :/
			$fp = fsockopen((isset($_SERVER['HTTPS']) ? 'ssl://' : '') . $_SERVER['SERVER_NAME'], $_SERVER['SERVER_PORT']);
			if($fp) {
				fwrite($fp, "GET " . ROOT_WDIR . "install/test_rewrite/ HTTP/1.0\r\n\r\n");
				stream_set_timeout($fp, 2);
				$line = trim(fgets($fp, 512));
				if(strpos($line, '300 Multiple Choices') === false){
					return [
						'title' => 'Mod Rewrite',
						'status' => 'warning',
						'message' => 'mod_rewrite may not available',
						'description' => 'Preliminary tests show that url rewriting may not be available.  If this is the case, you will not be able to fully use Core Plus.  Proceed with caution.'
					];
				}
				else{
					return [
						'title' => 'Mod Rewrite',
						'status' => 'passed',
						'message' => 'mod_rewrite is available!',
						'description' => 'The native module could not located, but work-around tests confirmed that it is indeed functioning.',
					];
				}
			}
		}
	}

	

	/**
	 * Configuration.xml file checks.
	 * This includes being able to write to the file and making sure that it's not world readable.
	 *
	 * @return array
	 */
	private function testConfigFile() {
		// The configuration file needs to be modified!
		if(!file_exists(ROOT_PDIR . 'config/configuration.xml') && !is_writable(ROOT_PDIR . 'config')){
			return [
				'title' => 'configuration.xml',
				'status' => 'warning',
				'message' => ROOT_PDIR . 'config/ is not writable',
				'fix' => 'chown ' . exec('whoami') . ' "'. ROOT_PDIR . 'config"',
			];
		}

		// The configuration file needs to be modified!
		if(file_exists(ROOT_PDIR . 'config/configuration.xml') && !is_writable(ROOT_PDIR . 'config/configuration.xml')){
			return [
				'title' => 'configuration.xml',
				'status' => 'warning',
				'message' => ROOT_PDIR . 'config/configuration.xml is not writable',
				'fix' => 'chown ' . exec('whoami') . ' -R "'. ROOT_PDIR . 'config"',
			];
		}

		return [
			'title' => 'configuration.xml',
			'status' => 'passed',
			'message' => ROOT_PDIR . 'config/configuration.xml can be written',
			'description' => ''
		];
	}

	/**
	 * Check that the core .htaccess file can be written.
	 *
	 * @return array
	 */
	private function testHTAccessFile(){
		if(!is_writable(ROOT_PDIR)){
			return [
				'title' => '.htaccess',
				'status' => 'warning',
				'message' => ROOT_PDIR . ' is not writable',
				'fix' => 'chown ' . exec('whoami') . ' "'. ROOT_PDIR . '"',
			];
		}

		return [
			'title' => '.htaccess',
			'status' => 'passed',
			'message' => ROOT_PDIR . ' is writable',
			'description' => ''
		];
	}

	/**
	 * Test that the (N)/ directory exists and is writable.
	 *
	 * @return array
	 */
	private function testNDirectory($dir, $title){
		$dir = ROOT_PDIR . $dir . '/';

		if(is_dir($dir) && is_writable($dir)){
			// Yay, everything is good here!
			return [
				'title' => $title . ' Directory',
				'status' => 'passed',
				'message' => $dir . ' is writable!',
			];
		}
		elseif(is_dir($dir)){
			return [
				'title' => $title . ' Directory',
				'status' => 'error',
				'message' => $dir . ' is not writable!',
				'fix' => 'chown ' . exec('whoami') . ' "' . $dir . '"',
			];
		}
		else{
			return [
				'title' => $title . ' Directory',
				'status' => 'error',
				'message' => $dir . ' does not exist!',
				'fix' => 'mkdir "' . $dir . '"' . NL . 'chown ' . exec('whoami') . ' "' . $dir . '"',
			];
		}
	}
}
