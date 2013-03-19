//admin-bar 100% width if cookie = expanded
if($.cookie('admin-bar-status') == 'expanded'){
	$('#admin-bar').css('width', '100%')
		.removeClass('admin-bar-collapsed')
		.addClass('admin-bar-expanded');
}

//calculate initial width and height for #admin-bar
//$adminBarW = $('.admin-bar-toggle').width() + 4;
$adminBarW = $('#admin-bar').width();

$adminBarH = $('.admin-bar-toggle').height();


$('.admin-bar-toggle').click(function(){
	var $this = $(this),
		$bar = $this.closest('#admin-bar');
	if($bar.hasClass('admin-bar-collapsed')) {
		$bar.animate({ width: '100%'}, 1400);

		$bar.find('ul').animate({ left: '+='+$adminBarW }, 1800, function(){

			$bar.removeClass('admin-bar-collapsed')
				.addClass('admin-bar-expanded');
		});

		$.cookie('admin-bar-status', 'expanded');
	}
	else{
		$bar.find('ul').animate({ left: '-='+$adminBarW }, 500, function(){
			$bar.addClass('admin-bar-collapsed')
				.removeClass('admin-bar-expanded');
		});

		$bar.delay(400).animate({ width: '30'}, 1000);

		$.cookie('admin-bar-status', 'collapsed');

	}

	return false;
});