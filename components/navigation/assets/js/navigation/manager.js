var NavigationManager = null;

(function () {
	var addcounter = 0,
		options = {

		},
		dialogs = {
			'int': null,
			'ext': null,
			'none':null
		},
		extractpagename,
		$renderarea,
		Dialog,
		sorttimeout,
		controlshtml;

	extractpagename = function ($select) {
		// Find the active option and retrieve the pagename from its label.
		// Required because pagenames may be in the format of
		// Home Page &raquo; Core+ &raquo; Why Choose Core+ Framework? ( /why-choose-core-plus )

		var $opt = $select.find('option:selected'),
			s = Core.Strings.trim($opt.html());

		return s.replace(/ \(.*\)$/, '').replace(/.*» (.*)$/, '$1')
	};

	controlshtml = '<ul class="controls" style="float:right;">' +
		'<li>' +
		'<a href="#" class="edit-entry-link control control-edit"><i class="icon-edit"></i><span>edit entry</span></a>' +
		'</li>' +
		'<li>' +
		'<a href="#" class="delete-entry-link control control-delete"><i class="icon-remove"></i><span>delete entry</span></a>' +
		'</li>' +
		'</ul>';

	Dialog = function (type, typetitle) {
		var self = this;

		//// PUBLIC PROPERTIES
		this.$dialog = null;

		//// PUBLIC METHODS

		/**
		 * Extend this to do any sanity checks before saving.
		 * Return false to invalidate the save.
		 */
		this.beforesave = function () {
			return true;
		}

		this.open = function ($el) {
			var id, title, url, target, $div, windowtitle;

			if (typeof $el == 'undefined') {
				// It's a new window, not too many options here.
				windowtitle = 'New ' + typetitle;
			}
			else {
				$div = $el.closest('div.entry');
				id = $div.closest('li').attr('entryid');

				title = $div.find('.entry-title').val();
				url = $div.find('.entry-url').val();
				target = $div.find('.entry-target').val();

				windowtitle = 'Edit ' + title;
			}

			// Set the data
			this.$dialog.find('input[name=id]').val(id);
			this.$dialog.find('input[name=title]').val(title);
			this.$dialog.find(':input[name=url]').val(url);
			this.$dialog.find(':input[name=target]').val(target);

			// Open the dialog
			this.$dialog.show().dialog({
				modal:   true,
				autoOpen:false,
				title:   windowtitle,
				width:   '500px'
			}).dialog('open');
		};

		this.save = function () {
			var dat = {
				id:    this.$dialog.find(':input[name=id]').val(),
				type:  type,
				url:   this.$dialog.find(':input[name=url]').val(),
				target:this.$dialog.find(':input[name=target]').val(),
				title: this.$dialog.find('input[name=title]').val()
			};

			// Sanity checks.
			if (!dat.title) {
				alert('You must include a title.');
				return false;
			}

			// User-definable sanity check.
			if (!this.beforesave(dat)) return false;

			// New link
			if (!dat.id) {
				NavigationManager.addEntry(dat);
			}
			// Existing link
			else {
				NavigationManager.editEntry(dat);
			}

			this.$dialog.dialog('close');
		};

		//// CONSTRUCTOR LOGIC

		this.$dialog = $('.add-entry-options-' + type);

		// This is the trigger for opening this dialog.
		$('.add-entry-' + type + '-btn').click(function () {
			self.open();
		});

		// Save button of course
		this.$dialog.find('.submit-btn').click(function () {
			self.save();

			return false;
		});
	};

	NavigationManager = {
		init:     function () {

			// Setup some private variables real quick.
			$renderarea = $('#entry-listings');
			dialogs['int'] = new Dialog('int', 'Internal Link');
			dialogs['ext'] = new Dialog('ext', 'External Link');
			dialogs['none'] = new Dialog('none', 'Text Label');

			// By default, set the title of the link to the title of the page, for convenience.
			dialogs['int'].$dialog.find('select[name=url]').change(function () {
				dialogs['int'].$dialog.find('input[name=title]').val(extractpagename($(this)));
			});

			// External links have additional sanity checks.
			dialogs['ext'].beforesave = function (dat) {
				// external links should be resolved fully!
				if (dat.url.indexOf('://') == -1) {
					dat.url = 'http://' + dat.url;
				}

				return true;
			};

			// Do the actual sortable logic
			$renderarea.nestedSortable({
				disableNesting:      'no-nest',
				forcePlaceholderSize:true,
				handle:              'div.entry',
				helper:              'clone',
				items:               'li.entry',
				opacity:             .6,
				placeholder:         'placeholder',
				tabSize:             25,
				tolerance:           'pointer',
				toleranceElement:    '> div'
			});

			// Edit links, should edit!
			$renderarea.delegate('a.edit-entry-link', 'click', function () {
				var $li = $(this).closest('li[entryid]'),
					type = $li.find('.entry-type').val();

				dialogs[type].open($(this));

				return false;
			});

			// And delete links should delete.
			$renderarea.delegate('a.delete-entry-link', 'click', function () {
				var $li = $(this).closest('li[entryid]'),
					id = $li.attr('entryid'),
					type = $li.find('.entry-type').val(),
					title = $li.find('.entry-title').val();

				if (confirm('Delete ' + type + ' link ' + title + '?')) {
					if (id.match(/^new-/) == null) {
						// Existing record, I actually have to record the deletion.
						$li.find(':input').remove();
						$li.append('<input type="hidden" name="entries[del-' + id + '][name]" value="does not matter ;)"/>');
						$li.hide();
					}
					else {
						// New record, I can just delete it! ^_^
						$li.remove();
					}
				}

				return false;
			});

			// Capture the form submission, I need to scan through and update the parentids as necessary.
			$renderarea.closest('form').submit(function () {
				$renderarea.find('div.entry').each(function () {
					var $this = $(this),
						$parent = $this.parent().parent().parent(),
						isli = $parent.is('li[entryid]'),
						parentid = (isli) ? $parent.attr('entryid') : 0;

					$this.find('.entry-parent').val(parentid);
				});
			});
		},

		// Add an entry to the page, with all the information setup appropriately.
		addEntry: function (data) {
			var
				id = data.id || 0,
				type = data.type,
				url = data.url,
				target = data.target,
				title = data.title,
				parent = data.parent || 0,
				payload,
				$target;

			// Blank ID?  (probably the case actually)
			if (!id) id = 'new-' + (++addcounter);

			// Create the payload first, this is just basic HTML.
			payload = '<li class="entry" id="entry-' + id + '" entryid="' + id + '">'
				+ '<div class="entry">'
				+ '<input type="hidden" class="entry-type" name="entries[' + id + '][type]" value="' + type + '"/>'
				+ '<input type="hidden" class="entry-url" name="entries[' + id + '][url]" value="' + url + '"/>'
				+ '<input type="hidden" class="entry-target" name="entries[' + id + '][target]" value="' + target + '"/>'
				+ '<input type="hidden" class="entry-title" name="entries[' + id + '][title]" value="' + title + '"/>'
				+ '<input type="hidden" class="entry-parent" name="entries[' + id + '][parent]" value="' + parent + '"/>'
				+ title
				+ controlshtml
				+ '</div>'
				+ '</li>';

			// Should this payload be attached onto the root node, or the parent node?
			if (parent) {
				// Look up that parent first.
				$target = $renderarea.find('li[entryid=' + parent + ']');
				// No target? Reset to the parent.
				if (!$target.length) {
					$target = $renderarea;
				}
				else {
					// Find the child OL for this target, create one if it doesn't exist.
					if (!$target.children('ol').length) $target.append('<ol/>');
					$target = $target.children('ol');
				}
			}
			else {
				// Easy enough here.
				$target = $renderarea;
			}

			$target.append(payload);
		},

		// Add an entry to the page, with all the information setup appropriately.
		editEntry:function (data) {
			var
				id = data.id,
				type = data.type,
				url = data.url,
				target = data.target,
				title = data.title,
				parent = data.parent || null,
				payload,
				$target = $renderarea.find('li[entryid=' + id + ']').find('div.entry');

			// Lookup the current parent.
			if (parent === null) parent = $target.find(':input[name="entries[' + id + '][parent]').val();

			// Create the payload first, this is just basic HTML.
			// This is because it's quicker than updating the inputs and trying to find the specific title.
			payload = '<input type="hidden" class="entry-type" name="entries[' + id + '][type]" value="' + type + '"/>'
				+ '<input type="hidden" class="entry-url" name="entries[' + id + '][url]" value="' + url + '"/>'
				+ '<input type="hidden" class="entry-target" name="entries[' + id + '][target]" value="' + target + '"/>'
				+ '<input type="hidden" class="entry-title" name="entries[' + id + '][title]" value="' + title + '"/>'
				+ '<input type="hidden" class="entry-parent" name="entries[' + id + '][parent]" value="' + parent + '"/>'
				+ title
				+ controlshtml;

			$target.html(payload);
		}
	};
})();

$(NavigationManager.init);