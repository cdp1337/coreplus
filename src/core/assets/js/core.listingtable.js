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
	this.$tableheads = this.$table.find('th[data-sortkey]');
	this.storagename = 'core-listingtable-columns-' + $table.data('table-name');
	this.mode = 'view';

	this.refreshColumns = function(){
		var current = JSON.parse(localStorage.getItem(self.storagename)), i;

		if(!current){
			// The stylesheet takes care of default values.
			return;
		}

		for(i in current){
			if(current[i]){
				$('.' + i).show();
			}
			else{
				$('.' + i).hide();
			}
		}
	}



	// I can now copy these classes to every td in the table.
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

	self.$tableheads.each(function(){
		var $th = $(this),
			sortkey = $th.data('sortkey');

		if(sortkey == self.currentsort.key){
			$th.append('<i class="sort-icon icon-sort-' + self.currentsort.dir + ' current"></i>');
			$th.append('<i class="sort-icon icon-sort-' + self.currentsort.other + ' other"></i>');
		}
		else{
			$th.append('<i class="sort-icon icon-sort other"></i>');
		}
	});

	self.$tableheads.click(function(){
		var $th = $(this), newkey, newdir, req,
			sortkey = $th.data('sortkey');

		if(sortkey == self.currentsort.key){
			// Set the dir
			newkey = self.currentsort.key;

			if(self.currentsort.dir == 'up') newdir = 'down';
			else newdir = 'up';
		}
		else{
			newkey = sortkey;
			newdir = self.currentsort.dir;
		}

		req = 'sortkey=' + newkey + '&sortdir=' + newdir;

		window.location.search = '?' + req;
	});

	self.$table.find('.control-column-selection').click(function(){
		var html = '', current, $dialog;

		// What values are currently set as display/hidden?
		current = JSON.parse(localStorage.getItem(self.storagename))

		if(!current){
			current = {};
		}
		if(typeof current != 'object'){
			// Hmmm...
			current = {};
		}

		self.$table.find('th').each(function(){
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
			html += '>&nbsp;' + $th.data('viewtitle') + '</label><br/>';
		});

		$dialog = $('<div>' + html + '</div>');

		$dialog.dialog({
			modal: true,
			title: 'Select Columns'
		}).find('input').change(function(){
			var n = $(this).attr('name');
			// INVERSE!
			current[n] = !current[n];

			// Update the local storage.
			localStorage.setItem(self.storagename, JSON.stringify(current));

			self.refreshColumns();
		});

		return false;
	});

	self.$table.find('.control-edit-toggle').click(function(){
		var $this = $(this);

		if(self.mode == 'view'){
			$table.find('.view').hide();
			$table.find('.edit').show();
			self.mode = 'edit';

			// See if there's an input somewhere nearby.
			if($this.closest('td').find(':input').length > 0){
				$this.closest('td').find(':input').first().select();
			}
		}
		else{
			$table.find('.view').show();
			$table.find('.edit').hide();
			self.mode = 'view';
		}

		return false;
	});


	// Lastly...
	self.refreshColumns();
};
