<?php
/**
 * Core general formatter for various common formats that are used by applications.
 *
 * @package Core
 * @author Charlie Powell <charlie@evalagency.com>
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

namespace Core\Formatter;

/**
 * Class for standard formatters in Core such as Date, IP Addresses, and Boolean values.
 *
 * @author charlie
 */
class GeneralFormatter {
	/**
	 * Shortcut function to format a string in a requested output format.
	 * 
	 * This will ALWAYS use the user's default timezone for formatting!
	 * 
	 * Meant as a helper method for Model formatting.
	 * 
	 * @param type $datetime
	 * @param type $format
	 */
	public static function DateStringSD($datetime, $outputFormat){
		if($datetime == 0){
			return '';
		}
		$d = new \Core\Date\DateTime($datetime);
		return $d->format('SD', \Core\Date\Timezone::TIMEZONE_USER);
	}
	
	/**
	 * Shortcut function to format a string in a requested output format.
	 * 
	 * This will ALWAYS use the user's default timezone for formatting!
	 * 
	 * Meant as a helper method for Model formatting.
	 * 
	 * @param type $datetime
	 * @param type $format
	 */
	public static function DateStringSDT($datetime, $outputFormat){
		if($datetime == 0){
			return '';
		}
		$d = new \Core\Date\DateTime($datetime);
		return $d->format('SDT', \Core\Date\Timezone::TIMEZONE_USER);
	}
	
	/**
	 * Shortcut function to format a string in a requested output format.
	 * 
	 * This will ALWAYS use the user's default timezone for formatting!
	 * 
	 * Meant as a helper method for Model formatting.
	 * 
	 * @param type $datetime
	 * @param type $format
	 */
	public static function DateStringFD($datetime, $outputFormat){
		if($datetime == 0){
			return '';
		}
		$d = new \Core\Date\DateTime($datetime);
		return $d->format('FD', \Core\Date\Timezone::TIMEZONE_USER);
	}
	
	/**
	 * Shortcut function to format a string in a requested output format.
	 * 
	 * This will ALWAYS use the user's default timezone for formatting!
	 * 
	 * Meant as a helper method for Model formatting.
	 * 
	 * @param type $datetime
	 * @param type $format
	 */
	public static function DateStringFDT($datetime, $outputFormat){
		if($datetime == 0){
			return '';
		}
		$d = new \Core\Date\DateTime($datetime);
		return $d->format('FDT', \Core\Date\Timezone::TIMEZONE_USER);
	}
	
	/**
	 * Shortcut method to return 'Enabled' or 'Disabled' for 1/0.
	 * 
	 * @param type $value
	 * @param type $outputFormat
	 */
	public static function BoolEnabledDisabled($value, $outputFormat){
		if($value === '1' || $value === 1 || $value === true){
			if($outputFormat == \View::CTYPE_HTML){
				return '<span class="pos">' . t('STRING_ENABLED') . '</span>';
			}
			else{
				return t('STRING_ENABLED');
			}
		}
		elseif($value === '0' || $value === 0 || $value === false){
			if($outputFormat == \View::CTYPE_HTML){
				return '<span class="neg">' . t('STRING_DISABLED') . '</span>';
			}
			else{
				return t('STRING_DISABLED');
			}
		}
		else{
			return $value;
		}
	}
	
	/**
	 * Shortcut method to return 'Yes' or 'No' for 1/0.
	 * 
	 * @param type $value
	 * @param type $outputFormat
	 */
	public static function BoolYesNo($value, $outputFormat){
		if($value === '1' || $value === 1 || $value === true){
			if($outputFormat == \View::CTYPE_HTML){
				return '<span class="pos">' . t('STRING_YES') . '</span>';
			}
			else{
				return t('STRING_YES');
			}
		}
		elseif($value === '0' || $value === 0 || $value === false){
			if($outputFormat == \View::CTYPE_HTML){
				return '<span class="neg">' . t('STRING_NO') . '</span>';
			}
			else{
				return t('STRING_NO');
			}
		}
		else{
			return $value;
		}
	}
	
	/**
	 * Helper method to easily render an IP address to something useful other than just a string of numbers.
	 * 
	 * @param type $value
	 * @param type $format
	 */
	public static function IPAddress($value, $format = \View::CTYPE_HTML){
		if($value == ''){
			return '';
		}
		
		if(!\Core::IsComponentAvailable('geographic-codes')){
			// The component responsible for this is not available, simply return the value.
			return $value;
		}
		
		if($format == \View::CTYPE_HTML){
			$ip = new \geocode\IPLookup($value);
			return $ip->getAsHTML(true) . ' ' . $value;
		}
		else{
			return $value;
		}
	}
	
	public static function User($value, $format = \View::CTYPE_HTML){
		if($value == '' || $value === null){
			return '';
		}
		elseif($value == 0){
			return \ConfigHandler::Get('/user/displayname/anonymous');
		}
		
		$user = \UserModel::Construct($value);
		if(!$user->exists()){
			return 'Non-existent User (' . $value . ')';
		}
		
		if($format == \View::CTYPE_HTML){
			return '<a href="' . \Core\resolve_link('/user/view/' . $user->get('id')) . '">' . $user->getLabel() . '</a>';
		}
		else{
			return $user->getLabel();
		}
	}
	
	public static function UserAgent($value, $format = \View::CTYPE_HTML){
		if($value == '' || $value === null){
			return '';
		}
		
		if($format == \View::CTYPE_HTML){
			$ua = new \Core\UserAgent($value);
			return $ua->getAsHTML();
		}
		else{
			return $value;
		}
	}
	
	public static function Filesize($value, $format = \View::CTYPE_HTML){
		if($value === '' || $value === null){
			return '';
		}
		// Use the filestore for processing this!
		return \Core\Filestore\format_size($value);
	}
	
	/**
	 * Format an amount of time into human-readable version.
	 * 
	 * @param int $value
	 * @param string $format
	 * 
	 * @return string
	 */
	public static function TimeDuration($value, $format = \View::CTYPE_HTML){
		if($value === '' || $value === null){
			return '';
		}
		
		return \Core\time_duration_format($value);
	}
	
	/**
	 * Format an amount of time (as milliseconds) into human-readable version.
	 * 
	 * @param int $value
	 * @param string $format
	 * 
	 * @return string
	 */
	public static function TimeMSDuration($value, $format = \View::CTYPE_HTML){
		if($value === '' || $value === null){
			return '';
		}
		
		// Convert the milliseconds over to seconds, 
		// (the underlying formatter is expecting that).
		$value /= 1000;
		
		return \Core\time_duration_format($value, 0);
	}
	
	public static function TimeDurationSinceNow($value, $format = \View::CTYPE_HTML){
		if($value === '' || $value === null){
			return '';
		}
		
		$n = Core\Date\DateTime::NowGMT();
		// Use absolute here to allow this method to be used for times in the future too.
		$value = abs($n - $value);
		
		return \Core\time_duration_format($value);
	}
}
