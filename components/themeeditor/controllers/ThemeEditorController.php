<?php
/**
 * Class file for the controller ThemeEditorController
 *
 * @package theme-editor
 * @author Nick Hinsch <nicholas@eval.bz
 */
class ThemeEditorController extends Controller_2_1 {

	static public function _Lookfor($fileext, &$mergearray, Directory_local_backend $directory, $assetdir){
		foreach($directory->ls() as $file){
			if($file instanceof Directory_local_backend){
				self::_Lookfor($fileext, $mergearray, $file, $assetdir);
			}
			elseif($file instanceof File_local_backend){

				// Only add if matches the fileext.
				if($file->getExtension() == $fileext){
					$filename = $file->getFilename();
					$shortStr = substr($filename, strlen($assetdir)+1);
					$mergearray[$shortStr] = $file;
				}
			}
		}
	}

	public function index(){

		$view = $this->getView();
		$request = $this->getPageRequest();

		if(!$this->setAccess('g:admin')){
			return View::ERROR_ACCESSDENIED;
		}

		$components = Core::GetComponents();
		$styles = array();
		$skins = array();

		$assetdir = ROOT_PDIR . 'themes/' . ConfigHandler::Get('/theme/selected') . '/assets';
		$css = new Directory_local_backend($assetdir);
		$this->_lookfor('css', $styles, $css, $assetdir);

		$templatedir = ROOT_PDIR . 'themes/' . ConfigHandler::Get('/theme/selected') . '/skins';
		$templates = new Directory_local_backend($templatedir);
		$this->_lookfor('tpl', $skins, $templates, $templatedir);

		foreach($components as $c) {
			/** @var $c Component_2_1 */
			if($c->getAssetDir()){
				$dir = $c->getAssetDir();
				$dh = new Directory_local_backend($c->getAssetDir());
				$this->_lookfor('css', $styles, $dh, $dir);
				$this->_lookfor('tpl', $skins, $dh, $dir);
			}
		}

		ksort($styles);
		ksort($skins);

		if($request->getParameter('css')){
			$file = $request->getParameter('css');
			$activefile = 'style';
		}
		elseif($request->getParameter('tpl')) {
			$file = $request->getParameter('tpl');
			$activefile = 'template';
		}
		else {
			//no special gets...
			$file = false; // I'm already defining it all here!
			$fh = $css->get('css/styles.css');
			$content= $fh->getContents();
			$filename = $fh->getBasename();
			$activefile = 'style';
		}

		if($file) {
			$fh = new File_local_backend($file);
			$content = $fh->getContents();
			$filename = $fh->getBasename();
		}

		$m = new ThemeEditorItemModel();
		$m->set('content', $content);
		$m->set('filename', $fh->getFilename());

		$form = Form::BuildFromModel($m);
		$form->set('callsmethod', 'ThemeEditorController::_SaveHandler');
		$form->addElement('submit', array('value' => 'Update'));

		$revisions = ThemeEditorItemModel::Find( array('filename' => $fh->getFilename()), 5, 'updated DESC');

		$view->assignVariable('activefile', $activefile);
		$view->assignVariable('form', $form);
		$view->assignVariable('styles', $styles);
		$view->assignVariable('skins', $skins);
		$view->assignVariable('content', $content);
		$view->assignVariable('filename', $filename);
		$view->assignVariable('revisions', $revisions);

		$view->title = 'Theme Editor';

	}

	public static function _SaveHandler(Form $form) {
		$model = $form->getModel();
		$model->set('updated', Time::GetCurrent());
		$model->save();
	}
}