<script type="text/javascript">
	
	// This will contain the full data persistently. (required for continuous checking of requirements and provides).
	var components;
	
	// Get the latest updates.
	/*
	function version_compare(vers1, vers2, op){
		var vers1array, vers2array, i, ret;
		
		if(op == undefined){
			op = 'ge';
		}
		
		// split the versions into an array, splitting on a dot(.).
		vers1array = vers1.split('.');
		vers2array = vers2.split('.');
		
		// Make sure they have identical number of arguments.
		if(vers1array.length < vers2array.length){
			for(i in vers2array){
				if(typeof(vers1array[i]) == 'undefined') vers1array[i] = 0;
			}
		}
		else if(vers2array.length < vers1array.length){
			for(i in vers1array){
				if(typeof(vers2array[i]) == 'undefined') vers2array[i] = 0;
			}
		}
		
		ret = null;
		
		// vers1 and vers2 arrays are now of equal length.
		for(i in vers1array){
			if(ret != null){
				if(vers1)
			}
		}
	}
	*/
	$.ajax({
		url: Core.ROOT_WDIR + 'updater/getupdates.json',
		dataType: 'json',
		success: function(data){
			var i, v, html, last, d;
			
			for(i in data){
				// Remember the highest version for this component.
				// Useful for filters.
				last = null;
				
				for(v in data[i]){
					// Shorthand
					d = data[i][v];
					
					html = '<tr dat:version="' + d.version + '" dat:name="' + d.name + '" dat:latest="0">';
					html += '<td>' + d.title + '</td>';
					html += '<td>' + d.version + '</td>';
					html += '<td>' + d.status + '</td>';
					if(d.status == 'update'){
						html += '<td><a class="dryrun-check" href="' + Core.ROOT_WDIR + 'Updater/Install/' + d.name + '/' + d.version + '">Install Update</a></td>';
					}
					
					html += '</tr>';
					//console.log(data[i][v]);
					
					last = d.version;
					$('#output').append(html);
				}
				
				// Mark the last as the latest.
				$('tr[dat\\:name="' + i + '"][dat\\:version="' + last + '"]').attr('dat:latest', '1');
			}
			
			// And remember these!
			components = data;
		}
	});
	
	$('.dryrun-check').live('click', function(){
		var $this, orightml;
		
		$this = $(this);
		
		if($this.hasClass('dryrun-checking')){
			alert('Checking dependencies, please wait...');
			return false;
		}
		
		$this.addClass('dryrun-checking');
		orightml = $this.html();
		$this.html('Checking...');
		
		// Submit a 'dryrun' request first.
		$.ajax({
			url: $this.attr('href') + '.json?dryrun=1',
			dataType: 'json',
			success: function(dat){
				var msg, i;
				
				$this.removeClass('dryrun-checking').html(orightml);
				
				if(!dat.status){
					alert(dat.message);
					return false;
				}
				
				msg = dat.message + "\nSummary of Changes:";
				for(i in dat.data){
					msg += "\n" + 'To Install ' + dat.data[i].title + ' ' + dat.data[i].version;
				}
				
				if(confirm(msg)){
					window.location.href = $this.attr('href');
				}
			}
		});
		
		// Submission will be handled with javascript.
		return false;
	});
</script>

<table id="output">
	<tr>
		<th>Package</th>
		<th>Version</th>
		<th>Status</th>
		<th>&nbsp;</th>
	</tr>
</table>