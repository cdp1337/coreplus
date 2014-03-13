$(function(){

	// http://bugs.jqueryui.com/ticket/9315
	// The draggables have a regression bug in jQuery 1.10.x..... joy
	// This means that if a page is scrolled down a little, the offset of the UI element is off.

	var counter = 0,
		changed = false,
		$dragsources = $('.widget-dragsource'),
		$droptargets = $('.widget-droptarget');

	// Widget sources are draggable, but only to the droppable widgetareas.
	$dragsources.draggable({
		helper: 'clone',
		connectToSortable: '.widget-droptarget',
		opacity: 0.5,
		revert: 'invalid',
		appendTo: 'body',
		start: function(e, ui){
			$droptargets.addClass('highlightdroptarget');
		},
		stop: function (e, ui){
			$droptargets.removeClass('highlightdroptarget');
		}
	});

	$droptargets.sortable({
		stop: function(e, ui){
			var $el = ui.item,
				$parent,
				parent,
				instanceid;
			
			// No instance ID, this widget needs to be setup!
			if(!$el.attr('data-instanceid')){
				instanceid = 'new-' + (++counter);
				$parent = $el.closest('.widgetarea');
				parent = $parent.attr('data-area');
				
				$el.find('input.baseurl').attr('name', 'widgetarea[' + instanceid + '][baseurl]');
				$el.find('input.widgetaccess').attr('name', 'widgetarea[' + instanceid + '][widgetaccess]');
				// Update the widget's widgetarea.  This is critical to know where it should be rendered at.
				$el.find('input.widgetarea').attr('name', 'widgetarea[' + instanceid + '][widgetarea]').val(parent);

				$el.attr('data-instanceid', instanceid);
			}
			
			// Update the classes anyhow.
			$el.removeClass('widget-dragsource').addClass('widget-dragdropped');

			// SOMETHING CHANGED!
			changed = true;
		},
		helper: 'original',
		revert: true
	});
	
	// All deletes over here need to do something.
	$('.widget-bucket-destination').delegate('a.control-delete', 'click', function(){
		var $this = $(this),
			$el = $this.closest('div.widget-dragdropped'),
			instance = $el.attr('data-instanceid');
			
		console.log(instance);
		// It didn't exist in the first place, feel free to delete it.
		if(instance.indexOf('new-') === 0){
			$el.remove();
		}
		else{
			$el.attr('data-instanceid', 'del-' + instance).hide();
			$el.find(':input').each(function(){
				var $this = $(this),
					n = $this.attr('name');
				$this.attr('name', n.replace('widgetarea[' + instance + ']', 'widgetarea[del-' + instance + ']'));
			});
		}

		// SOMETHING CHANGED!
		changed = true;

		return false;
	});

	$('.widget-bucket-destination').delegate('a.control-edit', 'click', function(){
		var $this = $(this),
			$par = $this.closest('.widget-dragdropped'),
			$widgetaccess = $par.find('.widgetaccess'),
			accessstring = $widgetaccess.val(),
			out = '',
			$dialog = null;

		out = Core.User.AccessStrings.render({
			value: accessstring
		});

		// Append a SAVE button.
		out += '<div><a class="button ok saveaccessstring">Set Access Settings</a></div>';

		// Now that I have the data... show it in a popover!
		$dialog = $('<div/>');
		$dialog.appendTo('body');
		$dialog.html(out);

		$dialog.show().dialog({
			modal: true,
			autoOpen: false,
			title: 'Access Permissions',
			width: '500px',
			close: function(e, ui){
				$(this).remove();
			}
		}).dialog('open');

		$dialog.find('.saveaccessstring').click(function(){
			$widgetaccess.val(Core.User.AccessStrings.parsedom($dialog));
			$dialog.dialog('close');

			return false;
		});

		// SOMETHING CHANGED!
		changed = true;

		return false;
	});

	$('#skin-selection-select').change(function(){
		if(changed){
			if(!confirm('You have unsaved changed to the widgets, click Cancel if you want to save the widgets first.')){
				return false;
			}
		}
		$(this).closest('form').submit();
	});

	// Required here.
	Core.User.init();

});