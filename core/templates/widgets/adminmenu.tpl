{*
{script library="jqueryui"}{/script}
{script library="jquery.json"}{/script}
{script library="jqueryui.readonly"}{/script}
*}
<ul>
	{foreach from=$pages item=page}
		<li>
			{a href=$page->get('baseurl')}{$page->get('title')}{/a}
		</li>
	{/foreach}
	<!--<li><a href="#" class="admin-manage-widgets">Manage Widgets</a></li>-->
</ul>
{*
{script location="foot"}
<script type="text/javascript">
	$('.admin-manage-widgets').click(function(){
		// Load the page with the widgets listed.
		
		var dialog = $('<div>Loading...</div>').appendTo('body').dialog({
			title: 'Available Site Widgets',
			width: '500px',
			close: function(){
				// Unhighlight all the widget areas
				$('.widgetarea').removeClass('highlightdroptarget');
				// Destroy this element...
				$(this).dialog('destroy').remove();
				// And disable the sortables
				$('.widgetarea').sortable('destroy');
				$('.widget-source').draggable('destroy');
			}
		});
		// load remote content
		dialog.load(
			"{link href='/Widget'}",
			{},
			function (responseText, textStatus, XMLHttpRequest) {
				// Highlight all the widgetareas
				$('.widgetarea').addClass('highlightdroptarget');
				
				// Widget sources are draggable, but only to the droppable widgetareas.
				$('.widget-source').draggable({
					helper: 'clone',
					connectToSortable: '.widgetarea',
					opacity: 0.5,
					revert: 'invalid',
					appendTo: 'body'
				});
				
				$('.widgetarea').sortable({
					stop: function(e, ui){
						// Does this item already exist as an instanced widget?
						// If no, retrieve its contents and replace with that, then save the addition.
						$el = $(ui.item);
						baseurl = $el.attr('baseurl');
						instanceid = $el.attr('instanceid');
						
						if(baseurl && !instanceid){
							$el.html('<div>Loading...</div>').attr('instanceid', 'NEW');
							$.ajax({
								url: "{link href='/Widget/View'}?widget=" + baseurl,
								dateType: 'text',
								success: function(html){
									$html = $(html);
									$el.replaceWith($html);
									$html.addClass('ui-draggable').attr('instanceid', 'NEW').removeClass('widget-source');
									//$el.html(html);
									//console.log(html);
								}
							});
						}
						console.log('STOP');
						// Also, update the order of them.
						dat = [];
						$widgetarea = $el.closest('.widgetarea');
						widgets = $widgetarea.find('.widget,.widget-source');
						for(i = 0; i < widgets.length; i++){
							$w = $(widgets[i]);
							dat.push({ baseurl: $w.attr('baseurl'), instanceid: $w.attr('instanceid') });
						}
						
						// Submit this data back to the server
						$.ajax({
							url: "{link href='/Widget/SaveOrder'}",
							data: 'jsondata=' + $.toJSON({ widgetarea: $widgetarea.attr('widgetarea'), widgets: dat }),
							success: function (dat){
								$target = $('.widgetarea[widgetarea="' + dat.widgetarea + '"]');
								for(var i in dat.widgets){
									// Find the current widget and update its information.
									$target.find('.widget[instanceid=' + dat.widgets[i].instanceid + ']')
										.attr('instanceid', dat.widgets[i].newid)
										.attr('weight', dat.widgets[i].weight);
								}
							}
						});
						jsondat = $.toJSON(dat);
					},
					//update: function (e, ui){
					//	console.log("UPDATE");
					//	$el = $(ui.item);
					//	// Run though this widgetarea and save the order!
					//	data = new Array();
					//	widgets = $el.closest('.widgetarea').find('.widget');
					//	console.log(widgets);
					//	for(i = 0; i < widgets.length; i++){
					//		console.log(widgets[i]);
					//	}
					//		//widgets.push($this.attr('instanceid'));
					//	//console.log(widgets);
					//},
					helper: 'original',
					//helper: function(e, $el){
					//	return $('<div class="widget-proxy" style="width:' + $el.width() + 'px; height:' + $el.height() + 'px;"></div>');
					//},
					revert: true
				});
			}
		);
		
		// Toggle all widgets without an instance id to be disabled... these are hardcoded in the template.
		//$('.widget').readonly(true);
		return false;
	});
</script>
{/script}
*}