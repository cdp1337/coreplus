<?php
/**
 * Class file for the controller ThemeEditorController
 *
 * @package theme-editor
 * @author Nick Hinsch <nicholas@eval.bz
 */
class ThemeEditorController extends Controller_2_1 {

	public function index(){

		$view = $this->getView();

		if(!$this->setAccess('g:admin')){
			return View::ERROR_ACCESSDENIED;
		}

		//figure out the current theme absolute path
		$theme = ROOT_PDIR . 'themes/' . ConfigHandler::Get('/theme/selected');

		$css = new Directory_local_backend('assets/css');
		$styles = $css->ls();

		$templates = new Directory_local_backend(ROOT_PDIR . '/themes/' . ConfigHandler::Get('/theme/selected') . '/skins/');
		$skins = $templates->ls();

		$imgDir = new Directory_local_backend('assets/images');
		$images = $imgDir->ls();

		$iconDir = new Directory_local_backend('assets/icons');
		$icons = $iconDir->ls();

		$fontDir = new Directory_local_backend('assets/fonts');
		$fonts = $fontDir->ls();

		$loadStyleItem = $_GET['css'];
		$loadTemplateItem = $_GET['tpl'];
		$loadImageItem = $_GET['img'];
		$loadIconItem = $_GET['icon'];
		$loadFontItem = $_GET['font'];

		if(empty($_GET)){
			//default to editing main stylesheet
			$content = $css->get('styles.css')->getContents();
			$filename = "styles.css";
		}
		elseif(!empty($loadStyleItem)){
			//load requested css resource
			$fh = new File_local_backend($loadStyleItem);
			$content = $fh->getContents();
			$view->assignVariable('activefile', 'style');
			$filename = $fh->getBasename($loadStyleItem);
		}
		elseif(!empty($loadTemplateItem)){
			//load requested smarty resource
			$fh = new File_local_backend($loadTemplateItem);
			$content = $fh->getContents();
			$view->assignVariable('activefile', 'template');
			$filename = $fh->getBasename($loadTemplateItem);
		}
		elseif(!empty($loadImageItem)){
			//load image file types
			$fh = new File_local_backend($loadImageItem);
			$image = $fh->getFilename();
			$view->assignVariable('activefile', 'image');
			$filename = $fh->getBasename($loadImageItem);
		}
		elseif(!empty($loadIconItem)){
			//load requested text resource
			$fh = new File_local_backend($loadIconItem);
			$image = $fh->getFilename();
			$view->assignVariable('activefile', 'icon');
			$filename = $fh->getBasename($loadIconItem);
		}
		elseif(!empty($loadFontItem)){
			//load requested font resource
			$fh = new File_local_backend($loadFontItem);
			$font = $fh->getFilename();
			$view->assignVariable('activefile', 'font');
			$filename = $fh->getBasename($loadFontItem);
		}


		$view->assignVariable('styles', $styles);
		$view->assignVariable('skins', $skins);
		$view->assignVariable('images', $images);
		$view->assignVariable('icons', $icons);
		$view->assignVariable('fonts', $fonts);

		$view->assignVariable('content', $content);
		$view->assignVariable('filename', $filename);
		$view->assignVariable('image', $image);
		$view->assignVariable('font', $font);

		$view->title = 'Theme Editor';

	}

	public static function update(){
		$file = $_GET['file'];
		//finish this :)
	}
}