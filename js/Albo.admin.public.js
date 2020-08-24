jQuery.noConflict();
(function($) {
   $(document).delegate('.albo-notice-dismis .notice-dismiss', 'click', function(e){
    	jQuery.ajax({url: ajaxurl,data: {action: 'dismiss_alboonline_notice',security:myajaxsec}});
    });
})(jQuery);