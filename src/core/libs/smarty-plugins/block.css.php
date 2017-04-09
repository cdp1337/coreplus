<?php
/**
 * File for the smarty css block function
 *
 * @package Core\Templates\Smarty
 * @since 1.9
 * @author Charlie Powell <charlie@evalagency.com>
 * @copyright Copyright (C) 2009-2017  Charlie Powell
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

/**
 * Inject a CSS file or snippet into the head of a page.
 *
 * This is the recommended way to inject stylesheets into your application.
 *
 * Any inline styles or links to stylesheets added via the `{css}` smarty block are automatically moved into the head of the document.
 * Redundant file includes and inline styles are omitted automatically.
 *
 * CSS files have their minified version sent automatically when the Core config option is set to do so.
 *
 * #### Smarty Parameters
 *
 *  * media
 *    * (string) The media attribute, defaults to "all".
 *  * href
 *    * The source of the linked CSS asset.
 *    * Can be fully resolved or a Core asset/* path.
 *  * link
 *    * alias of href.
 *  * src
 *    * alias of href.
 *  * optional
 *    * Set to "1" if this is an optional stylesheet where the admin can toggle on/off its inclusion.
 *    * Currently only supported in theme skins.
 *  * default
 *    * If optional="1", this is if the file is included by default or not.
 *  * title
 *    * If optional="1", this is an optional title displayed for the admin.
 *
 * #### Example Usage
 *
 * Include an asset file located in css/ called styles.css.
 * <pre>
 * {css src="css/styles.css"}{/css}
 * </pre>
 *
 * (For a theme skin), provide the admin with the option to include this stylesheet
 * <pre>
 * {css src="css/opt/full-width.css" optional="1" default="0" title="Set the page to be full width"}{/css}
 * </pre>
 *
 * Inject these styles into the page
 * <pre>
 * {css}
 *     &lt;style&gt;
 *         .blah {
 *             width: auto;
 *         }
 *     &lt;/style&gt;
 * {/css}
 * </pre>
 *
 * @param array       $params  Associative (and/or indexed) array of smarty parameters passed in from the template
 * @param string|null $content Null on opening pass, rendered source of the contents inside the block on closing pass
 * @param Smarty      $smarty  Parent Smarty template object
 * @param boolean     $repeat  True at the first call of the block-function (the opening tag) and
 * false on all subsequent calls to the block function (the block's closing tag).
 * Each time the function implementation returns with $repeat being TRUE,
 * the contents between {func}...{/func} are evaluated and the function implementation
 * is called again with the new block contents in the parameter $content.
 */
function smarty_block_css($params, $content, $smarty, &$repeat){
	// This only needs to be called once.
	if($repeat) return;

	// media type is the first parameter to check for.
	$media  = (isset($params['media'])) ? $params['media'] : 'all';
	$inline = isset($params['inline']) && $params['inline'] == '1' ? true : false;
	$tmpl   = $smarty->getTemplateVars('__core_template');
	$view   = ($tmpl instanceof \Core\Templates\TemplateInterface) ? $tmpl->getView() : \Core\view();

	// See if there's a "href" set.  If so, that's probably an asset.
	// I have a tendency of calling this different things, since things in the head all have
	// different names for this crap!
	// as such, support a bunch of different properties....
	$href = null;
	if(isset($params['href'])) $href = $params['href'];
	elseif(isset($params['link'])) $href = $params['link'];
	elseif(isset($params['src'])) $href = $params['src'];

	// Standard include from an asset.
	if($href !== null){

		// If optional is set, then look up the data to see if it's set.
		if(isset($params['optional']) && $params['optional']){
			$file = $smarty->template_resource;
			// Trim off the base directory.
			$paths = \Core\Templates\Template::GetPaths();
			foreach($paths as $p){
				if(strpos($file, $p) === 0){
					$file = substr($file, strlen($p));
					break;
				}
			}

			// Look up and see if this css is requested to be loaded by the user.
			$model = TemplateCssModel::Construct($file, $href);
			$enabled = $model->exists() ? $model->get('enabled') : (isset($params['default']) ? $params['default'] : 0);

			if(!$enabled) return;
		}

		if($inline){
			// Allow stylesheets to be rendered "in-line" in the code.
			// This is only really useful for emails and other HTML fragments.

			$file = Core\Filestore\resolve_asset_file($href);

			if(\ConfigHandler::Get('/core/javascript/minified')){
				// Remove the extension from the filename, (makes the logic cleaner).
				$dir      = $file->getDirectoryName();
				$filename = $file->getBaseFilename(true);
				$ext      = $file->getExtension();

				// Core is set to use minified css and javascript assets, try to locate those!
				// I need to do the check based on the base $filename, because 'assets/css/reset.css' may reside in one
				// of many locations, and not all of them may have a minified version.

				// Try to load the minified version instead.
				$minified = $filename . '.min.' . $ext;
				$minfile = \Core\Filestore\Factory::File($dir . $minified);
				if($minfile->exists()){
					// Overwrite the $file variable so it's returned instead.
					$file = $minfile;
				}
			}

			$view->addStyle('<style media="' . $media . '">' . $file->getContents() . '</style>');
		}
		else{
			$view->addStylesheet($href, $media);
		}
	}
	// Styles defined inline, fine as well.  The styles will be displayed in the head.
	elseif($content){
		$view->addStyle($content);
	}
}
