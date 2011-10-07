<?php
/**
 * @license GNU Affero General Public License v3 <http://www.gnu.org/licenses/agpl-3.0.txt>
 *
 * Copyright (C) 2010  Charlie Powell <powellc@powelltechs.com>
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
 *
 * @package [packagename]
 * @author Charlie Powell <powellc@powelltechs.com>
 * @date [date]
 */

class WidgetController extends Controller{
	public static function Index(View $page){
		// Get every registered widget on the system.
		$f = WidgetModel::Find(null);
		$page->assignVariable('widgets', $f);
		
		// This page can be ajaxie too!
		$page->mode = View::MODE_PAGEORAJAX;
	}
	
	public static function View(View $page){
		// This page can be ajaxie too!
		$page->mode = View::MODE_PAGEORAJAX;
		
		if(!$page->getParameter('widget')) return View::ERROR_NOTFOUND;
		
		// Lookup the widget requested.
		$widgetbaseurl = $page->getParameter('widget');
		$w = new WidgetModel($widgetbaseurl);
		// GO!
		$p =  $w->execute();
		$page->assignVariable('widget', $p);
		return;
	}
	
	public static function Edit(View $page){
		
	}
	
	public static function SaveOrder(View $page){
		// This page is exclusive ajaxie.
		$page->contenttype = View::CTYPE_JSON;
		$page->mode = View::MODE_NOOUTPUT;
		
		// The widget area should have been passed in, as well as an array of each element
		$data = json_decode($page->getParameter('jsondata'), true);
		
		// The area to do everything will be $data['widgetarea'].
		
		$ret = array();
		$x = 0;
		
		foreach($data['widgets'] as $w){
			if($w['instanceid'] == 'NEW') $wobj = new WidgetInstanceModel();
			else $wobj = new WidgetInstanceModel($w['instanceid']);
			
			$wobj->set('baseurl', $w['baseurl']);
			$wobj->set('weight', ++$x);
			$wobj->set('area', $data['widgetarea']);
			$wobj->set('theme', ConfigHandler::Get('/core/theme'));
			$wobj->save();
			
			$ret[] = array('instanceid' => $w['instanceid'], 'newid' => $wobj->get('id'), 'weight' => $wobj->get('weight'));
		}
		
		echo json_encode(array('widgetarea' => $data['widgetarea'], 'widgets' => $ret));
	}
	
	/*public static function InstallTo(View $page){
		// This page can be ajaxie too!
		$page->mode = View::MODE_PAGEORAJAX;
		
		if(!$page->getParameter('widget')) return View::ERROR_NOTFOUND;
		
		// Lookup the widget requested.
		$widgetbaseurl = $page->getParameter('widget');
		
		// Also need where it's being installed to.
	}*/
	
	
}
