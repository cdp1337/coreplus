$('.admin-bar-toggle').click(function(){
    var $this = $(this),
        $bar = $this.closest('#admin-bar');
    if($bar.hasClass('admin-bar-collapsed')){
        $bar.removeClass('admin-bar-collapsed').addClass('admin-bar-expanded');
        $.cookie('admin-bar-status', 'expanded');
    }
    else{
        $bar.addClass('admin-bar-collapsed').removeClass('admin-bar-expanded');
        $.cookie('admin-bar-status', 'collapsed');
    }

    return false;
});

if($.cookie('admin-bar-status') == 'expanded'){
    $('#admin-bar').removeClass('admin-bar-collapsed').addClass('admin-bar-expanded');
}