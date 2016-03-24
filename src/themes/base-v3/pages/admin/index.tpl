{script library="Core.AjaxLinks"}{/script}
{script library="jqueryui"}{/script}
{script library="core.strings"}{/script}

<style>
	.admin-dashboard-top .col,
	.admin-dashboard-top .seperator {
		display:inline-block;
	}

	.admin-dashboard-top .col {
		vertical-align: top;
	}

	.admin-dashboard-top .col h4 {
		margin: 0.6em 0 0.2em 0;
	}

	.admin-dashboard-top,
	.admin-dashboard-bottom-left,
	.admin-dashboard-bottom-right {
		background:#f9f9f9;
		padding:1.5em 2em;
		outline: 1px solid #ccc;
		margin-top:2em;
		min-height: 10em;
	}

	.admin-dashboard-top .seperator {
		padding:0;
		margin:0 2em;
		width:1px;
		background:#ccc;
		height:8.4em;
	}

	.admin-dashboard-bottom-left {
		width: 33%;
		float:left;
		margin-bottom: 2em;
		min-height: 39em;
	}

	.admin-dashboard-bottom-right {
		width: 64%;
		float:right;
		margin-bottom: 2em;
		min-height: 39em;
	}

	.admin-dashboard-bottom-left h4,
	.admin-dashboard-bottom-right h4 {
		margin: 0 0 0.2em 0;
	}
</style>

<div class="admin-dashboard-top">
	<div class="col">
		<h1>Welcome to Core Plus</h1>
		<h6>Check out these handy links to get started</h6>
		<br/>
		<p>
			{a class='button' href='/theme'}Customize your theme{/a}
		</p>

	</div>

	<div class="seperator"></div>

	<div class="col">
		<h4>What's Next?</h4>
		<ul>
			<li>{a href='/content/create'}Add a content page{/a}</li>
			<li>{a href='/blog/create'}Create a blog{/a}</li>
			<li>{a href='/gallery/create'}Create a gallery{/a}</li>
		</ul>
	</div>

	<div class="seperator"></div>

	<div class="col">
		<h4>Navigation Menus &amp; Widgets</h4>
		<ul>
			<li>{a href='/admin/widget/create?class=ContentWidget'}Create a content area widget{/a}</li>
			<li>{a href='/gallerywidget/create'}Create a gallery widget{/a}</li>
			<li>{a href='/navigation/create'}Create a navigation menu{/a}</li>
		</ul>
	</div>

</div>

<div class="admin-dashboard-bottom-left">
	{widgetarea name="Admin Dashboard Bottom Left" installable="/admin"}

	<h4>Quick Stats</h4>

	<ul>
		<li>{$pages|@count} {a href='/admin/pages?filter[component]=content&filter[page_types]=no_admin'}Content Pages{/a}</li>
		<li>{$blogs|@count} {a href='/blog/editindex'} {if $blogs|@count == 1}Blog{else}Blogs{/if} {/a}</li>
		<li>{$galleries|@count} {a href='/gallery/admin'} {if $galleries|@count == 1}Gallery{else}Galleries{/if} {/a}</li>
	</ul>

	<p></p><em>Core Version <span class="version"></span></em></p>

</div>

<div class="admin-dashboard-bottom-right">
	<h4>Site Activity</h4>

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
</div>

{script location="foot"}<script>
	$(function(){
		var xhr, intv,$now_speed_get, $now_speed_post, $now_connections, $connection_duration,
			$now_sessions, $now_bots, timer, $now_userlist, usertemplate, userheader,
			$now_botlist, bottemplate, botheader, $lastrefresh, $now_play, $now_pause,
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

		update_now();
		$('.version').html(Core.Version);

	});
</script>{/script}