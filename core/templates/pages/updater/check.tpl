{script library="jquery"}{/script}

<script type="text/javascript">
$(function(){

	
	// This will contain the full data persistently. (required for continuous checking of requirements and provides).
	var components;
	
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