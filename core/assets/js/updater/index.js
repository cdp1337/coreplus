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
	}

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
		var $table = $('#component-list'), i;

		// Since this table will contain only components that are actually updatable.. I can do this.
		for( i in packages.components){
			$table.find('tr[componentname="' + i + '"]').find('.update-link').show();
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
				name = ((type == 'components') ? $tr.attr('componentname') : $tr.attr('themename') ),
				i;

			// packages need to be downloaded first!
			if(packages === null){
				// How the eff did you even get here yet?  This button is supposed to be hidden!
				alert('Please wait for package information to download first.');
				return false;
			}

			if(!packages[type][name]){
				// How is this specific button enabled?!?  WTF's going on here?
				alert(type + ' ' + name + ' has no available updates.');
				return false;
			}

			$overlay = $('<div/>').appendTo('body');

			html = '<dl>';
			for(i in packages[type][name]){
				html += '<dt><a href="#" class="perform-update">' + packages[type][name][i].title + ' ' + i + '</a></dt>';
				html += '<dd title="' + packages[type][name][i].location + '">From: ' + packages[type][name][i].sourceurl + '</dd>';
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
	})

})(jQuery);
