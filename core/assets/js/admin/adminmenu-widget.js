//admin-bar 100% width if cookie = expanded
if($.cookie('admin-bar-status') == 'expanded'){
	$('#admin-bar').css('width', '100%')
		.removeClass('admin-bar-collapsed')
		.addClass('admin-bar-expanded');
}

//calculate initial width and height for #admin-bar
$adminBarW = $('.admin-bar-toggle').width() + 4;

$adminBarH = $('.admin-bar-toggle').height();

if($adminBarW !== 26){
	$('#admin-bar').width($adminBarW).height($adminBarH);
}

$('.admin-bar-toggle').click(function(){
	var $this = $(this),
		$bar = $this.closest('#admin-bar');
	if($bar.hasClass('admin-bar-collapsed')) {
		$bar.animate({width: "100%"},350, 'linear', function(){
			$bar.removeClass('admin-bar-collapsed')
				.addClass('admin-bar-expanded');
		});

		$.cookie('admin-bar-status', 'expanded');
	}
	else{
		$bar.animate({width: $adminBarW},250, 'linear', function(){
			$bar.addClass('admin-bar-collapsed')
				.removeClass('admin-bar-expanded');
		});

		$.cookie('admin-bar-status', 'collapsed');

	}

	return false;
});