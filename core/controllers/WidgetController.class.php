<?php
// @todo 2012.05.11 cpowell - Can I kill this file?  It doesn't seem to be doing anything.

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
		$page->response['contenttype'] = View::CTYPE_JSON;
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
			$wobj->set('theme', ConfigHandler::Get('/theme/selected'));
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
