/**
 * Helper function to help with the file upload logic.  There is a small amount of javascript for "prettifying"
 * the upload inputs.  This helps with the selection between
 * @param idprefix
 * @param current
 */
Core.fileupload = function(idprefix){
	var $selectgrp = $('#' + idprefix + '-selector'),
		$actiongrp = $('#' + idprefix + '-actions'),
		type       = $selectgrp.find(':checked').attr('selectortype'),
		$selects   = {
			current: $selectgrp.find('.fileinput-selector[selectortype=current]'),
			upload:  $selectgrp.find('.fileinput-selector[selectortype=upload]'),
			link:    $selectgrp.find('.fileinput-selector[selectortype=link]'),
			browse:  $selectgrp.find('.fileinput-selector[selectortype=browse]'),
			none:    $selectgrp.find('.fileinput-selector[selectortype=none]')
		},
		$actions   = {
			current: $actiongrp.find('.fileinput-action[selectortype=current]'),
			upload:  $actiongrp.find('.fileinput-action[selectortype=upload]'),
			link:    $actiongrp.find('.fileinput-action[selectortype=link]'),
			browse:  $actiongrp.find('.fileinput-action[selectortype=browse]'),
			none:    $actiongrp.find('.fileinput-action[selectortype=none]')
		},
		browseloaded = false,
		browsemode   = (($('#' + idprefix).attr('accept')).indexOf('image/') === 0 ? 'image' : 'index');

	if($selectgrp.find('.fileinput-selector').length > 1){
		// Only show the selector if there is more than 1 label to select...
		$selectgrp.show();
	}

	$actiongrp.find('.fileinput-action').hide();
	$actiongrp.show();
	$actions[type].show();
	if(type == 'upload'){
		$actions.upload.find('input').removeAttr('disabled');
	}

	$selectgrp.find('.fileinput-selector').click(function(){
		type = $(this).attr('selectortype');

		$actiongrp.children('.fileinput-action').hide();
		$actions[type].show();

		// Sometimes, there are custom actions that must be taken for each action.
		switch(type){
			case 'upload':
				$actions.upload.find('input').removeAttr('disabled');
				break;
			case 'browse':
				if(!browseloaded){
					load_browser(Core.ROOT_URL + 'mediamanagernavigator/' + browsemode + '?mode=list&ajax=1&controls=0&uploader=0');
					browseloaded = true;
				}
				$actions.upload.find('input').attr('disabled', 'disabled');
				break;
			default:
				$actions.upload.find('input').attr('disabled', 'disabled');
				break;
		}
	});

	$actions.link.find('input').change(function(){
		$selects.link.val('_link_://' + this.value);
	});

	// Redundant sectional department of redundancy and duplication ;)
	load_browser = function(url){
		$actions.browse.load(
			url,
			function(){
				Navigator.Setup();

				// Hijack standard "a" clicks so they don't open in the parent window!
				$actions.browse.find('a').click(function(){
					var href = $(this).attr('href');
					if(href != '#'){
						load_browser($(this).attr('href'));
						return false;
					}
				});

				// Hijack the select-file function!
				Navigator.SelectFile = function($target){
					$selects.browse.val('_browse_://' + $target.attr('corename'));
					$actions.browse.find('.file.selected').removeClass('selected');
					$target.closest('.file').addClass('selected');
					return false;
				};
			}
		);
	};
};
