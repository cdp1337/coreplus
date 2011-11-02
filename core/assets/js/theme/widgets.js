$(function(){

	// Widget sources are draggable, but only to the droppable widgetareas.
	$('.widget-dragsource').draggable({
		helper: 'clone',
		connectToSortable: '.widgetarea',
		opacity: 0.5,
		revert: 'invalid',
		appendTo: 'body',
		start: function(e, ui){
			$('.widget-dragtarget').addClass('highlightdroptarget');
		},
		stop: function (e, ui){
			$('.widget-dragtarget').removeClass('highlightdroptarget');
		}
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

});