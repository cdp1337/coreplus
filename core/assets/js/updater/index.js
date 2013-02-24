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
		performinstall,
		packages = null,
		// Set testupdater to true to test out the update procedure.
		testupdater = false
		;

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
				if(d || testupdater){
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
	Updater.GetPackages = function($target){
		if(!$target) $target = $('#updates');

		$target.html('Loading Packages...');

		$.ajax({
			url: Core.ROOT_WDIR + 'updater/getupdates.json',
			type: 'get',
			dataType: 'json',
			success: function(d){

				// Remember this next time.
				packages = d;

				drawpackages();
				$target.html('');
			},
			error: function(){
				// :/ Whatever, I didn't care about updates anyway.
				$target.html('Unable to retrieve list of packages');
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

	/**
	 * Show the updates that are currently available for the list of components
	 * Used on the landing page.
	 *
	 * @requires Updater.PerformCheck()
	 * @private
	 */
	showupdates = function(){
		var $ctable = $('#component-list'),
			$ttable = $('#theme-list'),
			$coretable = $('#core-list'),
			i, $l;

		// packages must be defined beforehand.
		if(!packages) return;

		// Clear the existing updates, if any
		$('.update-link').hide();

		if(testupdater){
			$l = $coretable.find('.update-link');
			$l.html('Update to 99.1337 (TEST)').show();
			$l.attr('version', '99.1337~(TEST)').attr('type', 'core').attr('name', 'core');
		}

		// Since this table will contain only components that are actually updatable.. I can do this.
		for( i in packages.components){
			// Find the update link in the tr
			$l = $ctable.find('tr[componentname="' + i + '"]').find('.update-link');
			// Set the text and show it
			$l.html('Update to ' + packages.components[i].version).show();
			// Set the version number so the called script knows what version to get
			$l.attr('version', packages.components[i].version).attr('type', 'components').attr('name', packages.components[i].name);
		}

		for( i in packages.themes){
			// Find the update link in the tr
			$l = $ttable.find('tr[themename="' + i + '"]').find('.update-link');
			// Set the text and show it
			$l.html('Update to ' + packages.themes[i].version).show();
			// Set the version number so the called script knows what version to get
			$l.attr('version', packages.themes[i].version).attr('type', 'themes').attr('name', packages.themes[i].name);
		}

		if(typeof packages.core != 'undefined' && packages.core){
			// Find the update link in the tr
			$l = $coretable.find('.update-link');
			// Set the text and show it
			$l.html('Update to ' + packages.core.version).show();
			// Set the version number so the called script knows what version to get
			$l.attr('version', packages.core.version).attr('type', 'core').attr('name', 'core');
		}
	};

	/**
	 * Construct and write the listing of components and themes that are available for install.
	 * Used on the browse page.
	 *
	 * @requires Updater.GetPackages()
	 * @private
	 */
	drawpackages = function(){
		var $componentstable = $('#component-list'),
			$themestable = $('#theme-list'),
			$coretable = $('#core-list'),
			name, version, cur, html, counter;

		counter = 0;
		for(name in packages.components){

			// alias it so it's quicker.
			cur = packages.components[name];
			version = cur.version;

			// Skip components that are not updated.
			if(cur.status == 'downgrade') continue;

			html = '<tr><td>' + cur.title + '</td><td>' + version + '</td>';
			if(cur.status == 'installed'){
				// Don't bother displaying installed packages.. that's what the updater is for!
				continue;
				html += '<td>Installed</td>';
			}
			else if(cur.status == 'new'){
				html += '<td><a href="#" class="perform-update" type="components" name="' + name + '" version="' + version + '">Install</a></td>';
			}
			else if(cur.status == 'update'){
				// Don't bother displaying installed packages.. that's what the updater is for!
				continue;
				html += '<td><a href="#" class="perform-update" type="components" name="' + name + '" version="' + version + '">Update</a></td>';
			}
			else{
				html += '<td>' + cur.status + '</td>';
			}

			html += '</tr>';
			$componentstable.append(html);
			++counter;
		}
		if(counter == 0){
			$componentstable.append('<tr><td colspan="3">No new components available</td></tr>');
		}


		counter = 0;
		for(name in packages.themes){

			// alias it so it's quicker.
			cur = packages.themes[name];
			version = cur.version;

			// Skip components that are not updated.
			if(cur.status == 'downgrade') continue;

			html = '<tr><td>' + cur.title + '</td><td>' + version + '</td>';
			if(cur.status == 'installed'){
				// Don't bother displaying installed packages.. that's what the updater is for!
				continue;
				html += '<td>Installed</td>';
			}
			else if(cur.status == 'new'){
				html += '<td><a href="#" class="perform-update" type="themes" name="' + name + '" version="' + version + '">Install</a></td>';
			}
			else if(cur.status == 'update'){
				// Don't bother displaying installed packages.. that's what the updater is for!
				continue;
				html += '<td><a href="#" class="perform-update" type="themes" name="' + name + '" version="' + version + '">Update</a></td>';
			}
			else{
				html += '<td>' + cur.status + '</td>';
			}

			html += '</tr>';
			$themestable.append(html);
			++counter;
		}
		if(counter == 0){
			$themestable.append('<tr><td colspan="3">No new themes available</td></tr>');
		}

		/*
		// alias it so it's quicker.
		cur = packages.core;

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
		*/
	};

	/**
	 * Perform the actual installation of a new component or upgrade.
	 *
	 * @param type
	 * @param name
	 * @param version
	 * @return {Boolean}
	 */
	performinstall = function(type, name, version){
		var url,
			$terminaldiv = $('#update-terminal'),
			$terminal,
			$form;

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

		if($terminaldiv.find('iframe').length){
			// Use the existing elements
			$terminal = $('#terminal');
			$form = $terminaldiv.find('form');

			$form.attr('action', url + '?verbose=1');
		}
		else{
			// Create an iframe to contain the progress.
			$terminal = $('<iframe name="terminal" id="terminal"></iframe>');
			$form = $('<form action="' + url + '?verbose=1" method="POST" target="terminal"></form>');

			$terminal.load(function(){
				var $body = $(this).contents().find('body'),
					$results = $body.find('#results');

				if($results.attr('status') == '1'){
					// successful run, call the reinstallation page now.
					$body.append('Executing Core installer...<br/>\n');

					$.ajax({
						url: Core.ROOT_URL + 'admin/reinstallall',
						success: function(){
							// call it again damnit!
							$body.append('Re-executing Core installer (yes, I am calling it twice)...<br/>\n');
							$.ajax({
								url: Core.ROOT_URL + 'admin/reinstallall',
								success: function(){
									$body.append('Installation successful!');
									lookupupdates();
									//Core.Reload();
								},
								error: function(){
									$body.append('Installation probably successful...');
									lookupupdates();
									//Core.Reload();
								}
							});
						},
						error: function(){
							// call it again damnit!
							$body.append('Re-executing Core installer (yes, I am calling it twice)...<br/>\n');
							$.ajax({
								url: Core.ROOT_URL + 'admin/reinstallall',
								success: function(){
									$body.append('Installation successful!');
									lookupupdates();
									//Core.Reload();
								},
								error: function(){
									$body.append('Installation probably successful...');
									lookupupdates();
									//Core.Reload();
								}
							});
						}
					});
				}
				else{
					$body.append('Seems that an error occurred.  Aborting installation!<br/>\n');
					alert($results.html());
				}
			});

			$terminaldiv.append($terminal).append($form);
		}

		// Show some helpful loading text.
		$terminal.html('Starting installation...');

		// Enable the div
		$terminaldiv.show();

		// And submit the form, kickstarting the install process.
		$form.submit();

		return false;
	}


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

			// Give some useful feedback to the user.
			if(!$this.attr('original-text')){
				$this.attr('original-text', $this.text());
			}
			$this.html($('#loading-replacement-text').html());

			// Do a dry run
			xhr = $.ajax({
				url: url + '?dryrun=1',
				type: 'POST',
				dataType: 'json',
				success: function(r){
					var msg = '';

					// This system returns a "status" field.
					if(!r.status){
						// and a message.
						alert(r.message);
						return;
					}

					// Get confirmation from the user before proceeding.
					if(r.changes && r.changes.length > 1){
						msg = 'The following additional packages will be installed or updated: \n' +
							r.changes.join('\n') +
							'\n\n' +
							'Click ok to proceed with the upgrade/install.';
					}
					else{
						msg = 'Everything looks good, click ok to proceed with the upgrade/install.';
					}



					$this.html($this.attr('original-text'));
					$this.removeAttr('original-text');

					if(confirm(msg)){
						performinstall(type, name, version);
					}
				},
				error: function(jqxhr, data, error){
					$this.html($this.attr('original-text'));
					$this.removeAttr('original-text');

					alert(error);
				}
			});
			return false;
		});
	});

})(jQuery);
