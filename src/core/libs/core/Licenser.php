<?php
/**
 * Created by PhpStorm.
 * User: charlie
 * Date: 4/8/16
 * Time: 8:31 AM
 */

namespace Core;


use ISingleton;

class Licenser implements \ISingleton {
	/** The singleton instance of this object. */
	private static $_Instance;

	/** @var array Array of licensed feature codes and the URL that they point to. */
	private $_features = [];
	
	private function _registerFeature($feature, $url, $component){
		if(!defined('SERVER_ID')){
			throw new \Exception('Unable to load the licenser without a valid server id set!');
		}
		
		// Lookup any matching feature and do not allow two components to register the same feature code!
		if(isset($this->_features[$feature]) && $this->_features[$feature]['component'] != $component){
			throw new \Exception('Feature ' . $feature . ' already defined in another component! (' . $this->_features[$feature]['component'] . ' vs ' . $component . ')');
		}
		
		// Lookup the cache for this licensed key value.
		$cached = \Core\Cache::Get(md5('LICENSER:' . SERVER_ID . $feature));

		$this->_features[$feature] = [
			'feature' => $feature,
			'url' => $url,
			'component' => $component,
			'value' => $cached,
		];
	}

	/**
	 * @return self
	 */
	public static function Singleton() {
		if(self::$_Instance === null){
			self::$_Instance = new self();
		}

		return self::$_Instance;
	}

	public static function Get($key){
		// This feature relies on a valid server id.
		if(!defined('SERVER_ID')){
			return null;
		}
		if(strlen(SERVER_ID) != 32){
			return null;
		}

		$features = self::Singleton()->_features;
		if(!isset($features[$key])){
			return null;
		}

		if($features[$key]['value'] === false) {
			// Not loaded yet
			return null;
		}

		return $features[$key]['value'];
		/*
			// It hasn't been checked yet!
			// Assemble all keys that are from this server and query them.
			// The results will be cached for at least one day.

			$r = new \Core\Filestore\Backends\FileRemote();
			$r->setRequestHeader('X-Core-Server-ID', SERVER_ID);
			$r->setFilename($licenser[$key]['url'] . '/licenser.json');
			
			$contents = $r->getContents();
			var_dump($r, $contents); die();
		}

		var_dump(); die();
		
		// Is there a valid cache for this key?
		$hash = md5('LICENSER:' . SERVER_ID . $key);
		
		$cache = \Core\Cache::Get($hash);
		if($cache === false){
			// There is no cache for this feature, look it up!
			
			
		}*/
	}

	/**
	 * Register a feature with a given URL and component,
	 * usually only performed from within Core.
	 * 
	 * @param string $feature
	 * @param string $url
	 * @param string $component
	 *
	 * @throws \Exception
	 */
	public static function RegisterFeature($feature, $url, $component){
		self::Singleton()->_registerFeature($feature, $url, $component);
	}

	/**
	 * Sync all features from the licensing servers and keep the values cached locally.
	 */
	public static function Sync(){
		// This feature relies on a valid server id.
		if(!defined('SERVER_ID')){
			return null;
		}
		if(strlen(SERVER_ID) != 32){
			return null;
		}
		
		$urls     = [];
		$features = self::Singleton()->_features;
		
		foreach($features as $d){
			if(!in_array($d['url'], $urls)){
				$urls[] = $d['url'];
			}
		}
		
		// URLs now is an array of all update servers to pull licensed features from.
		foreach($urls as $u){
			$r = new \Core\Filestore\Backends\FileRemote();
			$r->setRequestHeader('X-Core-Server-ID', SERVER_ID);
			$r->setFilename($u . '/licenser.json');

			$contents = $r->getContents();
			var_dump($r, $contents); die();
		}
	}

	/**
	 * Get the raw array of registered features, useful for debugging.
	 * 
	 * @return array
	 */
	public static function GetRaw(){
		return self::Singleton()->_features;
	}
}