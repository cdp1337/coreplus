Core.ListingTable = function($table, currentsortkey, currentsortdirection){
	var self = this,
		colidx = [],
		row = -1, col;

	this.currentsort = {
		key: currentsortkey,
		dir: currentsortdirection,
		other: (currentsortdirection == 'up' ? 'down' : 'up')
	};
	this.$table = $table;
	// DIV or TABLE, needed to support new CSS3 listing-tables.
	this.type = $table[0].nodeName;
	this.$tableheads = 
		this.type == 'DIV' ?
		this.$table.find('.listing-table-cell-header') :
		this.$table.find('th[data-sortkey]');
	this.storagename = 'core-listingtable-columns-' + $table.data('table-name');
	this.mode = 'view';

	this.refreshColumns = function(){
		var current = JSON.parse(localStorage.getItem(self.storagename)), i;

		if(!current){
			// The stylesheet takes care of default values,
			// just clear out any existing styles attached to the columns,
			// (useful for when the user resets the views on columns).
			self.$table.find('.column-visibility-hidden').removeClass('column-visibility-hidden');
			self.$table.find('.column-visibility-visible').removeClass('column-visibility-visible');
			
			return;
		}

		for(i in current){
			if(current[i]){
				self.$table.find('.' + i)
					.removeClass('column-visibility-hidden')
					.addClass('column-visibility-visible');
			}
			else{
				self.$table.find('.' + i)
					.addClass('column-visibility-hidden')
					.removeClass('column-visibility-visible');
			}
		}
	};



	// I can now copy these classes to every td in the table.
	// This is not required with Core 6.2 listing tables,
	// as they are dynamically generated and will have the classes attached already!
	if(this.type == 'TABLE'){
		self.$table.find('tr').each(function(){
			row++;
			if(row == 0){
				// Copy every <th> class in this row to the colidx.
				$(this).children('th').each(function(){
					colidx.push($(this).attr('class'));
				});
			}
			else{
				col = 0;
				$(this).children('td').each(function(){
					$(this).attr('class', colidx[col]);
					col++;
				});
			}
		});
	}

	self.$tableheads.each(function(){
		var $th = $(this),
			sortkey = $th.data('sortkey'),
			icons;
			
		if(!sortkey){
			// Skip columns that do not have a sort key.
			return true;
		}
		
		icons = '<div class="sort-icons">';
		
		if(self.currentsort.key === sortkey){
			// One of these is active!
			icons += '<i title="Sort ASC" data-key="' + sortkey + '" data-dir="up" class="icon sort-icon icon-sort-up icon-sort-' + (self.currentsort.dir === 'up' ? 'current' : 'other') + '"></i>';
			icons += '<i title="Sort DESC" data-key="' + sortkey + '" data-dir="down" class="icon sort-icon icon-sort-down icon-sort-' + (self.currentsort.dir === 'down' ? 'current' : 'other') + '"></i>';
		}
		else{
			// Both of these are inactive.
			icons += '<i title="Sort ASC" data-key="' + sortkey + '" data-dir="up" class="icon sort-icon icon-sort-up"></i>';
			icons += '<i title="Sort DESC" data-key="' + sortkey + '" data-dir="down" class="icon sort-icon icon-sort-down"></i>';
		}
		
		// Finish the div.
		icons += '</div>';
		
		$th.append(icons);
		
		// These icons have click events!
		$th.find('.sort-icon').click(function() {
			var $this = $(this), newkey = $this.data('key'), newdir = $this.data('dir'),
				req = 'sortkey=' + newkey + '&sortdir=' + newdir;

			window.location.search = '?' + req;
			return false;
		});
	});

	//console.log(self.$tableheads);
	self.$tableheads.find('.control-column-selection').click(function(){
		var html = '', current, $dialog;

		// What values are currently set as display/hidden?
		current = JSON.parse(localStorage.getItem(self.storagename));

		if(!current){
			current = {};
		}
		if(typeof current != 'object'){
			// Hmmm...
			current = {};
		}

		self.$tableheads.each(function(){
			var $th = $(this),
				vk = $th.data('viewkey');

			if(!vk){
				// Skip undefined view keys.
				return true;
			}

			// Just to make sure that any new columns show up.
			// This only really applies during development, but it's enough of an annoyance that it needs addressed.
			if(typeof current[vk] == 'undefined'){
				current[vk] = !$(this).hasClass('column-optional');
			}

			html += '<label><input type="checkbox" name="' + $th.data('viewkey') + '"';
			if(current[$th.data('viewkey')]){
				html += ' checked="checked"';
			}
			html += '>&nbsp;' + $th.data('viewtitle') + '</label>';
		});
		
		// Tack on a 'reset view' button so that the end user can reset the view back to defaults if they royally jack something up.
		// Since we're here, might as well add an 'OK' button too!
		html += '<div class="button-group">' +
			'<a href="#" class="button button-apply"><i class="icon icon-check"></i> <span>OK</span></a>' +
			'<a href="#" class="button button-reset"><i class="icon icon-history"></i> <span>Reset</span></a>' +
			'</div>';

		$dialog = $('<div class="listing-table-column-select">' + html + '</div>');

		$dialog.dialog({
			modal: true,
			title: 'Select Columns',
			width: '75%',
			resizable: false
		});
		
		// Attach the event for updating the checkbox items.
		$dialog.find('input').change(function(){
			var n = $(this).attr('name');
			// INVERSE!
			current[n] = !current[n];

			// Update the local storage.
			localStorage.setItem(self.storagename, JSON.stringify(current));

			self.refreshColumns();
		});
		
		// Attach the event for resetting the view.
		$dialog.find('a.button-reset').click(function(){
			current = [];
			
			// Clear the local storage.
			localStorage.removeItem(self.storagename);
			
			self.refreshColumns();
			
			// Close the dialog window because I'm lazy and don't feel like going back and 
			// resyncing the labels with the display logic.
			// Otherwise, the user resets the views and the table updates, but the labels to control what's displayed doesn't update.
			$dialog.dialog('close');
			
			// Prevent jumping.
			return false;
		});
		
		// Attach the event for accepting the view. ;)
		$dialog.find('a.button-apply').click(function(){
			// Just close!  The view updates in real time, so there's nothing to actually do here.
			$dialog.dialog('close');
			
			// Prevent jumping.
			return false;
		});

		return false;
	});

	self.$table.find('.control-edit-toggle').click(function(){
		var $this = $(this),
			$nearbyInputs = $this.closest('td').find(':input'),
			isrecord = $this.closest('tr').hasClass('edit-record-buttons'),
			$targetPointer;

		if($nearbyInputs.length > 0 && !isrecord){
			// It's near an input field.  Show only this specific record and highlight the nearby input.
			$targetPointer = $this.closest('tr');

			// First, hide the rest of the edit links on the table.
			$table.find('.edit').hide();
			$table.find('.view').show();

			// And show this specific record's edit links.
			$targetPointer.find('.view').hide();
			$targetPointer.find('.edit').show();
			self.mode = 'edit';

			// Not to mention, show the edit button records.
			$table.find('.edit-record-buttons').show();

			// Select that first input field.
			$nearbyInputs.first().select();
		}
		else{
			// Full table edit, behave as normal.
			if(self.mode == 'view'){
				$table.find('.view').hide();
				$table.find('.edit').show();
				self.mode = 'edit';
			}
			else{
				$table.find('.view').show();
				$table.find('.edit').hide();
				self.mode = 'view';
			}
		}
		return false;
	});


	// Lastly...
	self.refreshColumns();
};
