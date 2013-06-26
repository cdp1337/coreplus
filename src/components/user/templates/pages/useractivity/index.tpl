<fieldset>
	<legend> Right Now </legend>

	<div style="float:right;" class="control-panel">
		Last Refresh: <span id="last-refresh-date"></span>
		<a href="#" id="now-pause" class="button"><i class="icon-pause"></i></a>
		<a href="#" id="now-play" class="button" disabled="disabled"><i class="icon-play"></i></a>
	</div>

	Average Speed (GET) : <span id="now-speed-get"></span><br/>
	Average Speed (POST): <span id="now-speed-post"></span><br/>

	Total Page Views: <span id="now-connections"></span><br/>

	Number of Unique Visitors: <span id="now-sessions"></span><br/>
	Number of Bots: <span id="now-bots"></span><br/>

	<i>Averaged from information in the last <span id="connection-duration"></span></i><br/><br/>

	<b>Users</b>
	<table id="now-userlist" class="listing"></table><br/>

	<b>Bots</b>
	<table id="now-botlist" class="listing"></table>
</fieldset>

<fieldset>
	<legend> Historical </legend>

	<div style="float:right;" class="control-panel">
		From <input type="text" id="hist-dstart" class="update-historical datepicker" size="9"/>
		To <input type="text" id="hist-dend" class="update-historical datepicker" size="9"/>
	</div>

	Average Speed (GET) : <span id="hist-speed-get"></span><br/>
	Average Speed (POST): <span id="hist-speed-post"></span><br/>

	Total Page Views: <span id="hist-connections"></span><br/>

	Number of Unique Visitors: <span id="hist-sessions"></span><br/>
	Number of Bots: <span id="hist-bots"></span><br/>

	<div style="width:49%; float:left;">
		<b>Browsers (Top <select id="hist-browsers-count" class="update-historical"><option value="10">10</option><option value="25">25</option><option value="999">all</option></select>)</b>
		<table id="hist-browsers" class="listing"></table><br/>

		<b>Referrers (Top <select id="hist-referrers-count" class="update-historical"><option value="10">10</option><option value="25">25</option><option value="999">all</option></select>)</b>
		<table id="hist-referrers" class="listing"></table><br/>

		<b>IP Addresses (Top <select id="hist-ips-count" class="update-historical"><option value="10">10</option><option value="25">25</option><option value="999">all</option></select>)</b>
		<table id="hist-ips" class="listing"></table><br/>
	</div>

	<div style="width:49%; float:right;">
		<b>Operating Systems (Top <select id="hist-os-count" class="update-historical"><option value="10">10</option><option value="25">25</option><option value="999">all</option></select>)</b>
		<table id="hist-os" class="listing"></table><br/>

		<b>Not Found Pages (Top <select id="hist-notfound-count" class="update-historical"><option value="10">10</option><option value="25">25</option><option value="999">all</option></select>)</b>
		<table id="hist-notfounds" class="listing"></table><br/>

		<b>Pages (Top <select id="hist-pages-count" class="update-historical"><option value="10">10</option><option value="25">25</option><option value="999">all</option></select>)</b>
		<table id="hist-pages" class="listing"></table><br/>
	</div>

</fieldset>

{script library="jquery"}{/script}
{script library="jqueryui"}{/script}
{script library="core.strings"}{/script}
{script location="foot"}<script>
$(function(){
	var xhr, intv,$now_speed_get, $now_speed_post, $now_connections, $connection_duration,
		$now_sessions, $now_bots, timer, $now_userlist, usertemplate, userheader,
		$now_botlist, bottemplate, botheader, $lastrefresh, $now_play, $now_pause,
		$hist_speed_get, $hist_speed_post, $hist_connections, $hist_sessions, $hist_bots,
		$hist_browsers, $hist_referrers, $hist_ips, $hist_os, $hist_notfounds, $hist_pages,
		hist_generic_header, hist_generic_template, histxhr,
		$hist_browsers_count, $hist_referrers_count, $hist_ips_count, $hist_os_count, $hist_notfounds_count, $hist_pages_count,
		$hist_dstart, $hist_dend, datenow, datemonth, dateday;

	$now_speed_get = $('#now-speed-get');
	$now_speed_post = $('#now-speed-post');
	$now_connections = $('#now-connections');
	$connection_duration = $('#connection-duration');
	$now_sessions = $('#now-sessions');
	$now_bots = $('#now-bots');
	$now_userlist = $('#now-userlist');
	$now_botlist = $('#now-botlist');
	$lastrefresh = $('#last-refresh-date');
	$now_play = $('#now-play');
	$now_pause = $('#now-pause');
	timer = 5000;

	$hist_speed_get = $('#hist-speed-get');
	$hist_speed_post = $('#hist-speed-post');
	$hist_connections = $('#hist-connections');
	$hist_sessions = $('#hist-sessions');
	$hist_bots = $('#hist-bots');

	$hist_browsers = $('#hist-browsers');
	$hist_referrers = $('#hist-referrers');
	$hist_ips = $('#hist-ips');
	$hist_os = $('#hist-os');
	$hist_notfounds = $('#hist-notfounds');
	$hist_pages = $('#hist-pages');

	$hist_browsers_count = $('#hist-browsers-count');
	$hist_referrers_count = $('#hist-referrers-count');
	$hist_ips_count = $('#hist-ips-count');
	$hist_os_count = $('#hist-os-count');
	$hist_notfounds_count = $('#hist-notfounds-count');
	$hist_pages_count = $('#hist-pages-count');

	$hist_dstart = $('#hist-dstart');
	$hist_dend = $('#hist-dend');

	userheader = '<tr><th>User Name</th><th>IP Address</th><th>Browser</th><th>Operating System</th><th>Count</th><th>Last Page</th></tr>';
	usertemplate = '<tr>' +
			'<td>[%%username%%]</td>' +
			'<td>[%%ip%%]</td>' +
			'<td>[%%browser%%]</td>' +
			'<td>[%%os%%]</td>' +
			'<td>[%%count%%]</td>' +
			'<td>[%%lastpage%%]</td>' +
			'</tr>';

	botheader = '<tr><th>Bot Name</th><th>IP Address</th><th>Count</th><th>Last Page</th></tr>';
	bottemplate = '<tr>' +
			'<td>[%%browser%%]</td>' +
			'<td>[%%ip%%]</td>' +
			'<td>[%%count%%]</td>' +
			'<td>[%%lastpage%%]</td>' +
			'</tr>';

	hist_generic_header = '<tr><th>&nbsp;</th><th>Count</th></tr>';
	hist_generic_template = '<tr><td>[%%1%%]</td><td>[%%2%%]</td></tr>';

	datenow = new Date();
	datemonth = datenow.getMonth() + 1;
	if(datemonth < 10) datemonth = '0' + datemonth.toString();
	dateday = datenow.getDate();
	if(dateday < 10) dateday = '0' + dateday.toString();
	$hist_dend.val(datenow.getFullYear() + '-' + datemonth + '-' + dateday);

	datenow.setDate(datenow.getDate() - 31);
	datemonth = datenow.getMonth() + 1;
	if(datemonth < 10) datemonth = '0' + datemonth.toString();
	dateday = datenow.getDate();
	if(dateday < 10) dateday = '0' + dateday.toString();
	$hist_dstart.val(datenow.getFullYear() + '-' + datemonth + '-' + dateday);

	function update_now(){
		// Cancel the previous request, if it's still pending.
		if(xhr){
			//console.log('Killing previous request');
			xhr.abort();
			// xhr.abort will have killed the interval on the next run.  Restart it with a higher timeout.
			timer += 2000;
			intv = setInterval(update_now, timer);
		}

		if($now_play.attr('disabled') != 'disabled'){
			return;
		}

		xhr = $.ajax({
			url: Core.ROOT_URL + 'useractivity/now.json',
			dataType: 'json',
			success: function(d){
				var userdata = userheader, botdata = botheader, i, dt;

				if($now_play.attr('disabled') != 'disabled'){
					return;
				}

				//console.log('yayz');
				// It's returned... clear out the previous request!
				xhr = null;

				dt = new Date();

				$now_speed_get.html(Math.round(d.performance.get) + ' ms');
				$now_speed_post.html(Math.round(d.performance.post) + ' ms');
				$now_connections.html(d.requests.get + d.requests.post);
				$connection_duration.html(d.information.duration + ' seconds');
				$now_sessions.html(d.users.length);
				$now_bots.html(d.bots.length);
				$lastrefresh.html(dt.toTimeString());

				if(d.users.length == 0){
					// I know it's literally impossible for there to be no users... but leave me alone, this is more of a
					// sanity check than anything!  Why can there not be any active users?  Because the sheer act of
					// observing active users changes the active state!  meow ;)
					userdata += '<tr><td colspan="6">No Active Users</td></tr>';
				}
				for(i in d.users){
					userdata += usertemplate.template(d.users[i]);
				}
				$now_userlist.html(userdata);

				if(d.bots.length == 0){
					// Get the meow reference above yet, or haven't you had enough of quantum fun yet?
					botdata += '<tr><td colspan="4">No Active Bots :)</td></tr>';
				}
				for(i in d.bots){
					botdata += bottemplate.template(d.bots[i]);
				}
				$now_botlist.html(botdata);

				if(timer > 2000){
					timer -= 250;
					clearInterval(intv);
					intv = setInterval(update_now, timer);
				}

			},
			error: function(){
				clearInterval(intv);
				//console.log('Error on XHR');
			}
		});
	}

	function update_historical(){
		// Cancel the previous request, if it's still pending.
		if(histxhr){
			//console.log('Killing previous request');
			histxhr.abort();
		}

		histxhr = $.ajax({
			url: Core.ROOT_URL + 'useractivity/historical.json',
			type: 'get',
			dataType: 'json',
			data: {
				dstart: $hist_dstart.val(),
				dend: $hist_dend.val()
			},
			success: function(d){
				var browserdata  = hist_generic_header,
					referrerdata = hist_generic_header,
					ipdata       = hist_generic_header,
					osdata       = hist_generic_header,
					notfounddata = hist_generic_header,
					pagedata     = hist_generic_header,
					i, c;

				//console.log('yayz');
				// It's returned... clear out the previous request!
				histxhr = null;

				$hist_speed_get.html(Math.round(d.performance.get) + ' ms');
				$hist_speed_post.html(Math.round(d.performance.post) + ' ms');
				$hist_connections.html(d.requests.get + d.requests.post);
				$hist_sessions.html(d.counts.visitors);
				$hist_bots.html(d.counts.bots);


				if(d.browsers.length == 0){
					browserdata += '<tr><td colspan="2">No Data</td></tr>';
				}
				c = 0;
				for(i in d.browsers){
					c++; if(c > $hist_browsers_count.val()) break;
					browserdata += hist_generic_template.template(i, d.browsers[i]);
				}
				$hist_browsers.html(browserdata);

				if(d.referrers.length == 0){
					referrerdata += '<tr><td colspan="2">No Data</td></tr>';
				}
				c = 0;
				for(i in d.referrers){
					c++; if(c > $hist_referrers_count.val()) break;
					referrerdata += hist_generic_template.template(i, d.referrers[i]);
				}
				$hist_referrers.html(referrerdata);

				if(d.ips.length == 0){
					ipdata += '<tr><td colspan="2">No Data</td></tr>';
				}
				c = 0;
				for(i in d.ips){
					c++; if(c > $hist_ips_count.val()) break;
					ipdata += hist_generic_template.template(i, d.ips[i]);
				}
				$hist_ips.html(ipdata);

				if(d.os.length == 0){
					osdata += '<tr><td colspan="2">No Data</td></tr>';
				}
				c = 0;
				for(i in d.os){
					c++; if(c > $hist_os_count.val()) break;
					osdata += hist_generic_template.template(i, d.os[i]);
				}
				$hist_os.html(osdata);

				if(d.notfounds.length == 0){
					notfounddata += '<tr><td colspan="2">No Data</td></tr>';
				}
				c = 0;
				for(i in d.notfounds){
					c++; if(c > $hist_notfounds_count.val()) break;
					notfounddata += hist_generic_template.template(i, d.notfounds[i]);
				}
				$hist_notfounds.html(notfounddata);

				if(d.pages.length == 0){
					pagedata += '<tr><td colspan="2">No Data</td></tr>';
				}
				c = 0;
				for(i in d.pages){
					c++; if(c > $hist_pages_count.val()) break;
					pagedata += hist_generic_template.template(i, d.pages[i]);
				}
				$hist_pages.html(pagedata);

			},
			error: function(){
				//clearInterval(intv);
				//console.log('Error on XHR');
			}
		});
	}

	intv = setInterval(update_now, timer);

	$now_pause.click(function(){
		$now_pause.attr('disabled', 'disabled');
		$now_play.removeAttr('disabled');
		return false;
	});

	$now_play.click(function(){
		$now_play.attr('disabled', 'disabled');
		$now_pause.removeAttr('disabled');
		return false;
	});

	$('.update-historical').change(update_historical);

	$('.datepicker').datepicker({
		dateFormat: 'yy-mm-dd'
	});

	update_now();
	update_historical();
});
</script>{/script}
