$(function(){
	$('fieldset.collapsible.collapsed').children(':not(legend)').hide();
	
	$('fieldset.collapsible legend').css('cursor', 'pointer').click(function(){
		var $this, $fieldset;
		
		$this = $(this);
		$fieldset = $this.closest('fieldset');
		
		$fieldset.toggleClass('collapsed').children(':not(legend)').toggle('fast');
	});
});
