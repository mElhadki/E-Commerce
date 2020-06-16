(function ($) {

    var timer;
    var wcf_cart_abandonment = {

        init: function () {

            if ( CartFlowsProCAVars._show_gdpr_message && ! $("#wcf_cf_gdpr_message_block").length ) {
                $("#billing_email").after("<span id='wcf_cf_gdpr_message_block'> <span style='font-size: xx-small'> "+ CartFlowsProCAVars._gdpr_message +" <a style='cursor: pointer' id='wcf_ca_gdpr_no_thanks'> "+ CartFlowsProCAVars._gdpr_nothanks_msg +" </a></span></span>");
            }

            $(document).on(
                'keyup keypress change',
                '#billing_email, #billing_phone, input.input-text, textarea.input-text, select',
                this._getCheckoutData
            );

            $("#wcf_ca_gdpr_no_thanks").click(  function () {
                wcf_cart_abandonment._set_cookie();
            } );

            $( document.body ).on( 'updated_checkout', function(){
                wcf_cart_abandonment._getCheckoutData();
            });

            $(document).on('ready', function(e) {
                setTimeout(function() {
                    wcf_cart_abandonment._getCheckoutData();
                }, 800);
            });
        },

        _set_cookie: function() {


            var data = {
                'wcf_ca_skip_track_data': true,
                'action': 'cartflows_skip_cart_tracking_gdpr',
                'security': CartFlowsProCAVars._gdpr_nonce,
            };

            jQuery.post(
                CartFlowsProCAVars.ajaxurl,data,
                function (response) {

                    if(response.success) {
                        $("#wcf_cf_gdpr_message_block").empty().append("<span style='font-size: xx-small'>" + CartFlowsProCAVars._gdpr_after_no_thanks_msg + "</span>").delay(5000).fadeOut();
                    }

                }
            );

        },

        _validate_email: function (value) {
            var valid = true;
            if (value.indexOf('@') == -1) {
                valid = false;
            } else {
                var parts = value.split('@');
                var domain = parts[1];
                if (domain.indexOf('.') == -1) {
                    valid = false;
                } else {
                    var domainParts = domain.split('.');
                    var ext = domainParts[1];
                    if (ext.length > 14 || ext.length < 2) {
                        valid = false;
                    }
                }
            }
            return valid;
        },

        _getCheckoutData: function () {

            var wcf_phone = jQuery("#billing_phone").val();
            var wcf_email = jQuery("#billing_email").val();

            if( typeof wcf_email === 'undefined' ){
                return ;
            }

            var atposition = wcf_email.indexOf("@");
            var dotposition = wcf_email.lastIndexOf(".");
           

            if (typeof wcf_phone === 'undefined' || wcf_phone === null) { //If phone number field does not exist on the Checkout form
                wcf_phone = '';
            }

            clearTimeout(timer);

            if (!(atposition < 1 || dotposition < atposition + 2 || dotposition + 2 >= wcf_email.length) || wcf_phone.length >= 1) { //Checking if the email field is valid or phone number is longer than 1 digit
                //If Email or Phone valid
                var wcf_name = jQuery("#billing_first_name").val();
                var wcf_surname = jQuery("#billing_last_name").val();
                var wcf_phone = jQuery("#billing_phone").val();
                var wcf_country = jQuery("#billing_country").val();
                var wcf_city = jQuery("#billing_city").val();

                //Other fields used for "Remember user input" function
                var wcf_billing_company = jQuery("#billing_company").val();
                var wcf_billing_address_1 = jQuery("#billing_address_1").val();
                var wcf_billing_address_2 = jQuery("#billing_address_2").val();
                var wcf_billing_state = jQuery("#billing_state").val();
                var wcf_billing_postcode = jQuery("#billing_postcode").val();
                var wcf_shipping_first_name = jQuery("#shipping_first_name").val();
                var wcf_shipping_last_name = jQuery("#shipping_last_name").val();
                var wcf_shipping_company = jQuery("#shipping_company").val();
                var wcf_shipping_country = jQuery("#shipping_country").val();
                var wcf_shipping_address_1 = jQuery("#shipping_address_1").val();
                var wcf_shipping_address_2 = jQuery("#shipping_address_2").val();
                var wcf_shipping_city = jQuery("#shipping_city").val();
                var wcf_shipping_state = jQuery("#shipping_state").val();
                var wcf_shipping_postcode = jQuery("#shipping_postcode").val();
                var wcf_order_comments = jQuery("#order_comments").val();

                var data = {
                    action: "cartflows_save_cart_abandonment_data",
                    wcf_email: wcf_email,
                    wcf_name: wcf_name,
                    wcf_surname: wcf_surname,
                    wcf_phone: wcf_phone,
                    wcf_country: wcf_country,
                    wcf_city: wcf_city,
                    wcf_billing_company: wcf_billing_company,
                    wcf_billing_address_1: wcf_billing_address_1,
                    wcf_billing_address_2: wcf_billing_address_2,
                    wcf_billing_state: wcf_billing_state,
                    wcf_billing_postcode: wcf_billing_postcode,
                    wcf_shipping_first_name: wcf_shipping_first_name,
                    wcf_shipping_last_name: wcf_shipping_last_name,
                    wcf_shipping_company: wcf_shipping_company,
                    wcf_shipping_country: wcf_shipping_country,
                    wcf_shipping_address_1: wcf_shipping_address_1,
                    wcf_shipping_address_2: wcf_shipping_address_2,
                    wcf_shipping_city: wcf_shipping_city,
                    wcf_shipping_state: wcf_shipping_state,
                    wcf_shipping_postcode: wcf_shipping_postcode,
                    wcf_order_comments: wcf_order_comments,
                    security: CartFlowsProCAVars._nonce,
                    wcf_post_id: CartFlowsProCAVars._post_id,
                }

                timer = setTimeout(
                    function () {
                        if (wcf_cart_abandonment._validate_email(data.wcf_email)) {
                            jQuery.post(
                                CartFlowsProCAVars.ajaxurl, data, //Ajaxurl coming from localized script and contains the link to wp-admin/admin-ajax.php file that handles AJAX requests on Wordpress
                                function (response) {
                                    // success response
                                }
                            );
                        }
                    }, 500
                );
            } else {
                //console.log("Not a valid e-mail or phone address");
            }
        }

    }

    wcf_cart_abandonment.init();

})(jQuery);