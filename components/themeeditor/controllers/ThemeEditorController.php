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

		if(!$this->setAccess('p:content_manage')){
			return View::ERROR_ACCESSDENIED;
		}

		//$theme = ( Theme::getName() == null ? 'default' : Theme::getName() );
		//is get name not working as expected? it returns null for the default theme... :/

		//figure out the current theme absolute path
		$theme = ROOT_PDIR . 'themes/' . ConfigHandler::Get('/theme/selected');

		//grab all the relevant resources from the theme folder
		$styles = self::dirToArray($theme . '/assets/css', true, false, true);
		$skins  = self::dirToArray($theme . '/skins', true, false, true);



		//there's gotta be a better way
		$images = self::dirToArray($theme . '/assets/images', true, false, true);

		foreach($images as $img){
			$resultImg[] = str_replace($theme . "/", "", $img);
		}
		$images = $resultImg;



		//there's gotta be a better way
		$icons  = self::dirToArray($theme . '/assets/icons', true, false, true);

		foreach($icons as $icon){
			$resultIcon[] = str_replace($theme . "/", "", $icon);
		}
		$icons = $resultIcon;



		$fonts  = self::dirToArray($theme . '/assets/fonts', true, false, true);

		//see if we should load a different file instead of the default stylesheet
		$loadTextItem = $_GET['txt'];
		$loadImageItem = $_GET['img'];

		//set the content to display in the textarea
		//$content = (empty($loadItem) ? self::getFileContents($styles[0]) : self::getFileContents($loadItem) );

		if(empty($loadTextItem) && empty($loadImageItem)){
			//default to editing main stylesheet
			$content = self::getFileContents($styles[0]);
		}
		elseif(!empty($loadTextItem)){
			//load requested text resource
			$content = self::getFileContents($loadTextItem);
		}
		elseif(!empty($loadImageItem)){
			//load image file types
			$image = $loadImageItem;
		}


		$view->assignVariable('styles', $styles);
		$view->assignVariable('skins', $skins);
		$view->assignVariable('images', $images);
		$view->assignVariable('icons', $icons);
		$view->assignVariable('fonts', $fonts);

		$view->assignVariable('content', $content);
		$view->assignVariable('image', $image);
		$view->assignVariable('font', $font);

		$view->title = 'Theme Editor';

	}


	public static function dirToArray($directory, $recursive = true, $listDirs = false, $listFiles = true, $exclude = '') {
		$arrayItems = array();
		$skipByExclude = false;
		$handle = opendir($directory);
		if ($handle) {
			while (false !== ($file = readdir($handle))) {
				preg_match("/(^(([\.]){1,2})$|(\.(svn|git|md))|(Thumbs\.db|\.DS_STORE))$/iu", $file, $skip);
				if($exclude){
					preg_match($exclude, $file, $skipByExclude);
				}
				if (!$skip && !$skipByExclude) {
					if (is_dir($directory. DIRECTORY_SEPARATOR . $file)) {
						if($recursive) {
							$arrayItems = array_merge($arrayItems, self::dirToArray($directory. DIRECTORY_SEPARATOR . $file, $recursive, $listDirs, $listFiles, $exclude));
						}
						if($listDirs){
							$file = $directory . DIRECTORY_SEPARATOR . $file;
							$arrayItems[] = $file;
						}
					} else {
						if($listFiles){
							$file = $directory . DIRECTORY_SEPARATOR . $file;
							$arrayItems[] = $file;
						}
					}
				}
			}
			closedir($handle);
		}
		return $arrayItems;
	}

	public static function getFileContents($file){
		//made this it's own function so i can debug why xdebug keeps injecting itself in the smarty templates
		return file_get_contents($file);
	}

	public static function update(){
		$file = $_GET['file'];
	}
}