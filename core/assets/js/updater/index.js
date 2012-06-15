/**
 * Created with JetBrains PhpStorm.
 * Author: Charlie Powell <charlie@eval.bz>
 * Date: 5/31/12
 * Time: 1:53 AM
 */


/**
 * The main updater object, will contain public functions useful for external scripts to tie into
 * (well, scripts that are on the updater/index page...)
 *
 * @type {Object}
 */
Updater = {};

(function($){
	var xhr = null,
		lookupupdates,
		showupdates,
		packages = null;

	/**
	 * Perform a check on if there are updates available.
	 * This will make a request to the server and get a response of true or false.
	 *
	 * Optionally, update a $target element with the response.
	 *
	 * @param $target
	 * @constructor
	 */
	Updater.PerformCheck = function($target){
		if($target) $target.html('Checking for updates...');

		$.ajax({
			url: Core.ROOT_WDIR + 'updater/check.json',
			type: 'get',
			dataType: 'json',
			success: function(d){
				if(d){
					if($target) $target.html('Updates Available!');
					lookupupdates();
				}
				else{
					if($target) $target.html('No updates available');
				}
			},
			error: function(){
				if($target) $target.html('An error occurred while checking for updates.');
			}
		});
	};

	/**
	 * Get all packages from the server.
	 *
	 * @constructor
	 */
	Updater.GetPackages = function(){
		var $update = $('#updates');
		$update.html('Loading Packages...');

		$.ajax({
			url: Core.ROOT_WDIR + 'updater/getupdates.json',
			type: 'get',
			dataType: 'json',
			success: function(d){

				// Remember this next time.
				packages = d;

				drawpackages();
				$update.html('');
			},
			error: function(){
				// :/ Whatever, I didn't care about updates anyway.
				$update.html('Unable to retrieve list of packages');
			}
		});
	};

	/**
	 * Internal function to lookup more information about available updates and toggle the
	 * necessary "update" buttons on the records.
	 *
	 * @private
	 */
	lookupupdates = function(){
		$.ajax({
			url: Core.ROOT_WDIR + 'updater/getupdates.json?onlyupdates=1',
			type: 'get',
			dataType: 'json',
			success: function(d){
				// Remember this next time.
				packages = d;

				// And show the updates.
				showupdates();
			},
			error: function(){
				// :/ Whatever, I didn't care about updates anyway.
			}
		});
	};

	showupdates = function(){
		var $ctable = $('#component-list'), $ttable = $('#theme-list'), $coretable = $('#core-list'), i;

		// Since this table will contain only components that are actually updatable.. I can do this.
		for( i in packages.components){
			$ctable.find('tr[componentname="' + i + '"]').find('.update-link').show();
		}

		for( i in packages.themes){
			$ttable.find('tr[themename="' + i + '"]').find('.update-link').show();
		}

		for( i in packages.core){
			$coretable.find('.update-link').show();
			break; // I only need to run this once.
		}
	};

	drawpackages = function(){
		var $componentstable = $('#component-list'),
			$themestable = $('#theme-list'),
			$coretable = $('#core-list'),
			name, version, cur, html;

		for(name in packages.components){

			for(version in packages.components[name]){
				// alias it so it's quicker.
				cur = packages.components[name][version];

				// Skip components that are not updated.
				if(cur.status == 'downgrade') continue;

				html = '<tr><td>' + cur.title + '</td><td>' + version + '</td>';
				if(cur.status == 'installed'){
					html += '<td>Installed</td>';
				}
				else if(cur.status == 'new'){
					html += '<td><a href="#" class="perform-update-components" name="' + name + '" version="' + version + '">Install</a></td>';
				}
				else if(cur.status == 'update'){
					html += '<td><a href="#" class="perform-update-components" name="' + name + '" version="' + version + '">Update</a></td>';
				}
				else{
					html += '<td>' + cur.status + '</td>';
				}

				html += '</tr>';
				$componentstable.append(html);
			}
		}

		for(name in packages.themes){

			for(version in packages.themes[name]){
				// alias it so it's quicker.
				cur = packages.themes[name][version];

				// Skip components that are not updated.
				if(cur.status == 'downgrade') continue;

				html = '<tr><td>' + cur.title + '</td><td>' + version + '</td>';
				if(cur.status == 'installed'){
					html += '<td>Installed</td>';
				}
				else if(cur.status == 'new'){
					html += '<td><a href="#" class="perform-update" type="themes" name="' + name + '" version="' + version + '">Install</a></td>';
				}
				else if(cur.status == 'update'){
					html += '<td><a href="#" class="perform-update" type="themes" name="' + name + '" version="' + version + '">Update</a></td>';
				}
				else{
					html += '<td>' + cur.status + '</td>';
				}

				html += '</tr>';
				$themestable.append(html);
			}
		}

		for(version in packages.core){
			// alias it so it's quicker.
			cur = packages.core[version];

			// Skip components that are not updated.
			if(cur.status == 'downgrade') continue;

			html = '<tr><td>' + cur.title + '</td><td>' + version + '</td>';
			if(cur.status == 'installed'){
				html += '<td>Installed</td>';
			}
			else if(cur.status == 'new'){
				html += '<td><a href="#" class="perform-update" type="core" name="core" version="' + version + '">Install</a></td>';
			}
			else if(cur.status == 'update'){
				html += '<td><a href="#" class="perform-update" type="core" name="core" version="' + version + '">Update</a></td>';
			}
			else{
				html += '<td>' + cur.status + '</td>';
			}

			html += '</tr>';
			$coretable.append(html);
		}
	};


	// These are all events that kick in after the DOM is loaded.
	$(function(){
		// This function is the exact same for enable or disable, just the verbiage is changed slightly.
		$('.disable-link, .enable-link').click(function(){
			var $this = $(this),
				$tr = $this.closest('tr'),
				name = $tr.attr('componentname'),
				action = ($this.text() == 'Enable') ? 'enable' : 'disable';

			// Cancel the last request.
			if(xhr !== null) xhr.abort();

			// Do a dry run
			xhr = $.ajax({
				url: Core.ROOT_WDIR + 'updater/component/' + action + '/' + name + '?dryrun=1',
				type: 'POST',
				dataType: 'json',
				success: function(r){
					// If there was an error, "message" will be populated.
					if(r.message){
						alert(r.message);
						return;
					}

					// If the length is more than one and the user accepts that more than one component will be disabled,
					// or if there's only one.
					if(
						(r.changes.length > 1 && confirm('The following components will be ' + action + 'd: \n' + r.changes.join('\n')) ) ||
							(r.changes.length == 1)
						){
						xhr = $.ajax({
							url: Core.ROOT_WDIR + 'updater/component/' + action + '/' + name + '?dryrun=0',
							type: 'POST',
							dataType: 'json',
							success: function(r){
								// Done, just reload the page!
								Core.Reload();
							},
							error: function(jqxhr, data, error){
								alert(error);
							}
						});
					}
				},
				error: function(jqxhr, data, error){
					alert(error);
				}
			});
			return false;
		});

		$('.update-link').click(function(){
			var $tr = $(this).closest('tr'),
				$overlay, html,
				type = $tr.attr('type'),
				name = null,
				i, package;

			// packages need to be downloaded first!
			if(packages === null){
				// How the eff did you even get here yet?  This button is supposed to be hidden!
				alert('Please wait for package information to download first.');
				return false;
			}

			switch(type){
				case 'components':
					name = $tr.attr('componentname');
					package = packages[type][name];
					break;
				case 'themes':
					name = $tr.attr('themename');
					package = packages[type][name];
					break;
				case 'core':
					name = 'core';
					package = packages[type];
					break;
				default:
					alert('Invalid type, ' + type);
					return false;
			}

			if(!package){
				// How is this specific button enabled?!?  WTF's going on here?
				alert(type + ' ' + name + ' has no available updates.');
				return false;
			}

			$overlay = $('<div/>').appendTo('body');

			html = '<dl>';
			for(i in package){
				html += '<dt>' +
					'<a href="#" class="perform-update" type="' + type + '" name="' + name + '" version="' + i + '">' +
					package[i].title + ' ' + i +
					'</a>' +
					'</dt>';
				html += '<dd title="' + package[i].location + '">From: ' + package[i].sourceurl + '</dd>';
			}
			html += '</dl>';
			$overlay.html(html).dialog({
				title: 'Updates for ' + name,
				autoOpen: false,
				modal: true,
				minWidth: 450,
				close: function(){ $(this).dialog('destroy').remove(); }
			}).dialog('open');

			return false;
		});

		$('body').delegate('.perform-update', 'click', function(){
			var $this   = $(this),
				name    = $this.attr('name'),
				version = $this.attr('version'),
				type    = $this.attr('type'),
				url     = null;

			// Cancel the last request.
			if(xhr !== null) xhr.abort();

			switch(type){
				case 'components':
					url = Core.ROOT_WDIR + 'updater/component/install/' + name + '/' + version;
					break;
				case 'themes':
					url = Core.ROOT_WDIR + 'updater/theme/install/' + name + '/' + version;
					break;
				case 'core':
					url = Core.ROOT_WDIR + 'updater/core/install/' + version;
					break;
				default:
					alert('Invalid type, ' + type);
					return false;
			}

			// Do a dry run
			xhr = $.ajax({
				url: url + '?dryrun=1',
				type: 'POST',
				dataType: 'json',
				success: function(r){

					// This system returns a "status" field.
					if(!r.status){
						// and a message.
						alert(r.message);
						return;
					}

					// If the length is more than one and the user accepts that more than one component will be disabled,
					// or if there's only one.
					if(
						(r.changes.length > 1 && confirm('The following ' + type + ' will be installed or updated: \n' + r.changes.join('\n')) ) ||
							(r.changes.length == 1)
						){
						xhr = $.ajax({
							url: url + '?dryrun=0',
							type: 'POST',
							dataType: 'json',
							success: function(r){
								// Done, just reload the page!
								Core.Reload();
							},
							error: function(jqxhr, data, error){
								alert(error);
							}
						});
					}
				},
				error: function(jqxhr, data, error){
					alert(error);
				}
			});
			return false;
		});
	});

})(jQuery);
