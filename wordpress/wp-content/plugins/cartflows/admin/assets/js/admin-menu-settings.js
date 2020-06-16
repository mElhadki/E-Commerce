(function ($) {

    /* Disable/Enable Facebook Pixel Field section*/
    var wcf_toggle_fields_facebook_pixel = function () {
        var fb_pixel_fields = ".wcf-fb-pixel-wrapper";
        jQuery(fb_pixel_fields).toggle(jQuery("#wcf_wcf_facebook_pixel_tracking").is(":checked"));
        jQuery("#wcf_wcf_facebook_pixel_tracking").click(function () {
            jQuery(fb_pixel_fields).toggle(jQuery("#wcf_wcf_facebook_pixel_tracking").is(":checked"));
        });
    }
    /* Disable/Enable Facebook Pixel Field section*/

    /* Disable/Enable Google Analytics Field section */
    var wcf_toggle_fields_google_analytics = function (){
        var google_analytics_fields = ".wcf-google-analytics-wrapper";
            
            jQuery(google_analytics_fields).toggle(jQuery("#wcf_enable_google-analytics-id").is(":checked"));
            
            jQuery("#wcf_enable_google-analytics-id").click(function () {
                jQuery(google_analytics_fields).toggle(jQuery("#wcf_enable_google-analytics-id").is(":checked"));
            });   
    }
    /* Disable/Enable Google Analytics Field section */

    $(document).ready(function () {
        wcf_toggle_fields_facebook_pixel();
        wcf_toggle_fields_google_analytics();
    });

})(jQuery);