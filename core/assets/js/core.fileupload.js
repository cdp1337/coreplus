/**
 * Helper function to help with the file upload logic.  There is a small amount of javascript for "prettifying"
 * the upload inputs.  This helps with the selection between
 * @param idprefix
 * @param current
 */
Core.fileupload = function(idprefix, current){
	// Because "this" gets overwritten frequently by events and calls...
	var self = this;

	// The respective elements for this system are:
	// -action-upload, -action-current, -action-browse, -action-none

	// jquery doesn't like things having brackets in the name.
	idprefix = idprefix.replace('[', '\\[').replace(']', '\\]');

	self.$selector = $('#' + idprefix + '-selector');

	self.hasUpload = ($('#' + idprefix + '-selector-upload').length > 0);
	self.hasCurrent = ($('#' + idprefix + '-selector-current').length > 0);
	self.hasBrowse = ($('#' + idprefix + '-selector-browse').length > 0);
	self.hasNone = ($('#' + idprefix + '-selector-none').length > 0);

	// Only show the selector if there is more than 1 label to select...
	if(self.$selector.find('label').length > 1){
		self.$selector.show();
		$('#' + idprefix + '-actions').children('div').hide();
		$('#' + idprefix + '-actions').show();
	}
	else{
		// Check the only available option.
		$('#' + idprefix + '-selector').find('input').attr('checked', true);
		// And show the action for it.
		$('#' + idprefix + '-actions').show();
	}

	if(self.hasUpload){
		$('#' + idprefix + '-selector-upload').change(function(){
			$('#' + idprefix + '-actions').children('div').hide();
			$('#' + idprefix + '-action-upload').show();
		});
	}

	if(self.hasCurrent){
		$('#' + idprefix + '-selector-current').change(function(){
			$('#' + idprefix + '-actions').children('div').hide();
			$('#' + idprefix + '-action-current').show();
		});
	}

	if(self.hasNone){
		$('#' + idprefix + '-selector-none').change(function(){
			$('#' + idprefix + '-actions').children('div').hide();
			$('#' + idprefix + '-action-none').show();
		});
	}

	if(self.hasBrowse){
		$('#' + idprefix + '-selector-browse').change(function(){
			$('#' + idprefix + '-actions').children('div').hide();
			$('#' + idprefix + '-action-browse').show();
		});
	}

	if(current){
		// There is currently an image selected, enable that option.
		$('#' + idprefix + '-selector-current').attr('checked', 'true').change();
	}
}
