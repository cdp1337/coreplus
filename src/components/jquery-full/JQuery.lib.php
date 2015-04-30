<?php
/**
 * jQuery library file, just includes the various jquery javascript assets.
 * 
 * @package JQuery
 * @since 0.1
 * @author Charlie Powell <charlie@eval.bz>
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

abstract class JQuery {
	
	public static function IncludeJQuery(){
		$use2x = \ConfigHandler::Get('/jquery/use_2_x');

		if($use2x === 'auto'){
			// Determine if it should be used based on the UA.
			// Remember, only IE <= 8.0 requires this.
			$ua = \Core\UserAgent::Construct();
			if($ua->browser == 'IE' && $ua->version <= 8){
				$use2x = '0';
			}
			else{
				$use2x = '1';
			}
		}

		if($use2x == '1'){
			// The site is setup to use 2.x, (default as of Core 4.0).
			\Core\view()->addScript ('js/jquery/jquery-2.1.3.js');
		}
		else{
			// The admin requested not to use the new version of jQuery.  Stick with 1.11 then.
			\Core\view()->addScript ('js/jquery/jquery-1.11.0.js');
		}

		
		// IMPORTANT!  Tells the script that the include succeeded!
		return true;
	}
	
	public static function IncludeJQueryUI(){
		self::IncludeJQuery();
		\Core\view()->addScript ('js/jquery/jquery-ui-1.11.4.js');
		\Core\view()->addScript ('js/jquery/jquery.ui.touch-punch.js');
		\Core\view()->addStylesheet('css/jquery-ui-1.11.4.css');
		
		// IMPORTANT!  Tells the script that the include succeeded!
		return true;
	}
	
	public static function Include_nestedSortable(){
		// I need jquery ui first.
		self::IncludeJQueryUI();
		
		\Core\view()->addScript ('js/jquery/jquery.ui.nestedSortable.js');
		
		// IMPORTANT!  Tells the script that the include succeeded!
		return true;
	}

	public static function Include_tmpl(){
		// I need jquery ui first.
		self::IncludeJQueryUI();

		\Core\view()->addScript ('js/jquery/tmpl.js');

		// IMPORTANT!  Tells the script that the include succeeded!
		return true;
	}
	
	public static function Include_readonly(){
		// I need jquery ui first.
		self::IncludeJQueryUI();
		
		\Core\view()->addStylesheet('css/jquery.readonly.css');
		\Core\view()->addScript ('js/jquery/jquery.ui.readonly.js');
		
		// IMPORTANT!  Tells the script that the include succeeded!
		return true;
	}
	
	public static function Include_json(){
		// I need jquery first.
		self::IncludeJQuery();
		
		\Core\view()->addScript ('js/jquery/jquery.json-2.4.js');
		
		// IMPORTANT!  Tells the script that the include succeeded!
		return true;
	}

	public static function Include_cookie(){
		// I need jquery first.
		self::IncludeJQuery();

		\Core\view()->addScript ('js/jquery/jquery.cookie.js');

		// IMPORTANT!  Tells the script that the include succeeded!
		return true;
	}

	public static function Include_masonry(){
		// I need jquery first.
		self::IncludeJQuery();
		\Core\view()->addScript('js/jquery/jquery.masonry.js');

		// IMPORTANT!  Tells the script that the include succeeded!
		return true;
	}

	public static function Include_form(){
		// I need jquery first.
		self::IncludeJQuery();
		\Core\view()->addScript('js/jquery/jquery.form.js');

		// IMPORTANT!  Tells the script that the include succeeded!
		return true;
	}

	public static function Include_timepicker(){
		// I need jquery ui first.
		self::IncludeJQueryUI();
		\Core\view()->addScript('js/jquery/jqueryui.timepicker.js');

		// IMPORTANT!  Tells the script that the include succeeded!
		return true;
	}

	public static function Include_waypoints(){
		// I need jquery first.
		self::IncludeJQuery();
		\Core\view()->addScript ('js/jquery/waypoints.js');

		// IMPORTANT!  Tells the script that the include succeeded!
		return true;
	}

	public static function Include_Smoothscroll(){
		// I need jquery first.
		self::IncludeJQuery();

		\Core\view()->addScript('js/jquery/jquery.smooth-scroll.min.js');

		// IMPORTANT!  Tells the script that the include succeeded!
		return true;
	}

	public static function Include_Minimalect(){
		self::IncludeJQuery();

		\Core\view()->addScript('js/jquery/jquery.minimalect.js');

		// IMPORTANT!  Tells the script that the include succeeded!
		return true;
	}

	public static function Include_Icheck(){
		self::IncludeJQuery();

		\Core\view()->addStylesheet('css/jquery.icheck.css');
		\Core\view()->addScript('js/jquery/jquery.icheck.js');

		// IMPORTANT!  Tells the script that the include succeeded!
		return true;
	}
}
