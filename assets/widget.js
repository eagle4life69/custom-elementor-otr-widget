jQuery(function($){
    $('.otr-tab-button').on('click', function(){
        var tab = $(this).data('tab');
        $('.otr-tab-button').removeClass('active');
        $(this).addClass('active');
        $('.otr-tab-content').hide();
        $('#' + tab).show();
    });
    $('.play-preview').on('click', function(e){
        e.preventDefault();
        var modal = $('#' + $(this).data('modal'));
        modal.show();
    });
    $('.otr-close').on('click', function(){
        $(this).closest('.otr-modal').hide();
    });
});
