/**
 * Just a simple script to spruce up the message-* blocks with a bit of flair.
 */

$(function(){
	var types = [
			{ class: 'error',    icon: 'exclamation-sign' },
			{ class: 'info',     icon: 'info-sign' },
			{ class: 'note',     icon: 'asterisk' },
			{ class: 'success',  icon: 'ok-sign' },
			{ class: 'tutorial', icon: 'question-sign' }
		],
		i;

	for(i in types){
		$('.message-' + types[i].class).each(function(){
			$(this).prepend('<span class="message-background-icon"><i class="icon-' + types[i].icon + '"></i></span>');
		});
	}
});