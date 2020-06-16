(function ($) {
    
    CartAbandonmentSettings = {
        
        init: function () {

            $("#wcf_ca_custom_filter_from").datepicker({
                dateFormat: 'yy-mm-dd',
                maxDate: '0',
                onClose: function( selectedDate ) {
                    jQuery( "#wcf_ca_custom_filter_to" ).datepicker( "option", "minDate", selectedDate );
                }
            }).attr('readonly','readonly').css('background', 'white');

            $("#wcf_ca_custom_filter_to").datepicker({
                dateFormat: 'yy-mm-dd',
                maxDate: '0',
                onClose: function( selectedDate ) {
                    jQuery( "#wcf_ca_custom_filter_from" ).datepicker( "option", "maxDate", selectedDate );
                }
            }).attr('readonly','readonly').css('background', 'white');

            $("#wcf_ca_custom_filter").click(function () {
                var from = $("#wcf_ca_custom_filter_from").val().trim();
                var to = $("#wcf_ca_custom_filter_to").val().trim();
                var url = window.location.search;
                url = url + "&from_date=" + from + "&to_date=" + to + "&filter=custom";
                window.location.href = url;

            });

            $("#wcf_search_id_submit").click( function () {
                var search = $("#wcf_search_id_search_input").val().trim();
                window.location.href = window.location.search + "&search_term=" + search;
            } );

            // Hide initially.
            $("#wcf_ca_discount_type, #wcf_ca_coupon_amount, #wcf_ca_coupon_expiry, #wcf_ca_zapier_cart_abandoned_webhook, #wcf_ca_coupon_code_status, #wcf_ca_gdpr_message").closest('tr').hide();


            if( $("#wcf_ca_gdpr_status:checked").length ) {
                $("#wcf_ca_gdpr_message").closest('tr').show();
            }

            if ($("#wcf_ca_zapier_tracking_status:checked").length) {
                $("#wcf_ca_zapier_cart_abandoned_webhook, #wcf_ca_coupon_code_status").closest('tr').show();
            }

            if ( $("#wcf_ca_coupon_code_status:checked").length && $("#wcf_ca_zapier_tracking_status:checked").length ) {
                $("#wcf_ca_discount_type, #wcf_ca_coupon_amount, #wcf_ca_coupon_expiry").closest('tr').show();
            }
            
            $("#wcf_ca_coupon_code_status").click(
                function () {
                    if (!$("#wcf_ca_coupon_code_status:checked").length) {
                           $("#wcf_ca_discount_type, #wcf_ca_coupon_amount, #wcf_ca_coupon_expiry").closest('tr').fadeOut();
                    } else {
                        $("#wcf_ca_discount_type, #wcf_ca_coupon_amount, #wcf_ca_coupon_expiry").closest('tr').fadeIn();
                    }
                }
            );

            $("#wcf_ca_gdpr_status").click(
                function () {
                    if (!$("#wcf_ca_gdpr_status:checked").length) {
                           $("#wcf_ca_gdpr_message").closest('tr').fadeOut();
                    } else {
                        $("#wcf_ca_gdpr_message").closest('tr').fadeIn();
                    }
                }
            );
	
	        $("#wcf_ca_zapier_tracking_status").click(
		        function () {
			        if (!$("#wcf_ca_zapier_tracking_status:checked").length) {
				        $("#wcf_ca_zapier_cart_abandoned_webhook, #wcf_ca_coupon_code_status").closest('tr').fadeOut();
			        } else {
				        $("#wcf_ca_zapier_cart_abandoned_webhook, #wcf_ca_coupon_code_status").closest('tr').fadeIn();
			        }

                    if ($("#wcf_ca_coupon_code_status:checked").length && $("#wcf_ca_zapier_tracking_status:checked").length) {
                        $("#wcf_ca_discount_type, #wcf_ca_coupon_amount, #wcf_ca_coupon_expiry").closest('tr').fadeIn();
                    } else {
                        $("#wcf_ca_discount_type, #wcf_ca_coupon_amount, #wcf_ca_coupon_expiry").closest('tr').fadeOut();
                    }
		        }
	        );
         
        }
    }
        

    EmailTemplatesAdmin = {


        init: function () {

            $(document).on('click', '#wcf_preview_email', EmailTemplatesAdmin.send_test_email);
            $(document).on('click', '.wcf-ca-switch.wcf-toggle-template-status', EmailTemplatesAdmin.toggle_activate_template);
            $(document).on('click', '#wcf_ca_delete_coupons', EmailTemplatesAdmin.delete_coupons);
            $(document).on('click', '#wcf_ca_export_orders', EmailTemplatesAdmin.export_orders);
            $(document).on('click', '.wcar-switch-grid', EmailTemplatesAdmin.toggle_activate_template_on_grid);
            var coupon_child_fields = "#wcf_email_discount_type, #wcf_email_discount_amount, #wcf_email_coupon_expiry_date, #wcf_free_shipping_coupon, #wcf_auto_coupon_apply, #wcf_individual_use_only";
            $(coupon_child_fields).closest('tr').toggle($("#wcf_override_global_coupon").is(":checked"));
            $(document).on('click', '#wcf_override_global_coupon', function () {
                $(coupon_child_fields).closest('tr').fadeToggle($("#wcf_override_global_coupon").is(":checked"));
            });
        },
	    
        send_test_email: function () {

            var email_body = '';
            if (jQuery("#wp-wcf_email_body-wrap").hasClass("tmce-active")) {
                email_body = tinyMCE.get('wcf_email_body').getContent();
            } else {
                email_body = jQuery('#wcf_email_body').val();
            }

            var email_subject = $('#wcf_email_subject').val();
            var email_send_to = $('#wcf_send_test_email').val();
            var wp_nonce = $("#_wpnonce").val();

            $(this).next('div.error').remove();

            if (!$.trim(email_body)) {
                $(this).after('<div class="error-message wcf-ca-error-msg"> Email body is required! </div>');
            } else if (!$.trim(email_subject)) {
                $(this).after('<div class="error-message wcf-ca-error-msg"> Email subject is required! </div>');
            } else if (!$.trim(email_send_to)) {
                $(this).after('<div class="error-message wcf-ca-error-msg"> You must add your email id! </div>');
            }
            else {

                var data = {
                    email_subject: email_subject,
                    email_body: email_body,
                    email_send_to: email_send_to,
                    action: 'wcf_ca_preview_email_send',
                    security: wp_nonce,
                };
                $("#wcf_preview_email").css('cursor', 'wait').attr("disabled", true);

                // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                $.post(
                    ajaxurl, data, function (response) {
                        $("#mail_response_msg").empty().fadeIn();;

                        if (response.success) {
                            var htmlString = "<strong> Email has been sent successfully! </strong>";
                            $("#mail_response_msg").css('color','green').html(htmlString).delay(3000).fadeOut();

                        } else {
                            var htmlString = "<strong> Email sending failed! Please check your SMTP settings!  </a></strong>"
                            $("#mail_response_msg").css('color','red').html(htmlString).delay(3000).fadeOut();;
                        }
                        $("#wcf_preview_email").css('cursor', '').attr("disabled", false);

                    }
                );
            }

            $(".wcf-ca-error-msg").delay(2000).fadeOut();
        },

        delete_coupons: function () {
                if (confirm(wcf_ca_localized_vars._confirm_msg)) {
                    var data = {
                        action: 'wcf_ca_delete_garbage_coupons',
                        security: wcf_ca_localized_vars._delete_coupon_nonce
                    };
                    $('.wcf-ca-spinner').show();

                    $('.wcf-ca-spinner').addClass("is-active");
                    $("#wcf_ca_delete_coupons").css('cursor', 'wait').attr("disabled", true);
                     $.post(
                        ajaxurl, data, function (response) {
                            $(".wcf-ca-response-msg").empty().fadeIn();;
                             if (response.success) {
                                $('.wcf-ca-spinner').hide();
                                $(".wcf-ca-response-msg").css('color','green').html(response.data).delay(5000).fadeOut();
                            }

                            $("#wcf_ca_delete_coupons").css('cursor', '').attr("disabled", false);
                        }
                    );
                }
        },
        export_orders: function () {
            if( confirm( wcf_ca_localized_vars._confirm_msg_export ) ) {
                window.location.href = window.location.search + "&export_data=true";
            }
        },
        toggle_activate_template_on_grid: function () {
            var $switch, state, new_state;
            $switch = $(this);
            state = $switch.attr('wcf-ca-template-switch');
            var css = (state === 'on') ? 'green' : 'red';

            $.post(
                ajaxurl, {
                    action: 'activate_email_templates',
                    id: $(this).attr('id'),
                    state: state,
                    security: wcf_ca_details.email_toggle_button_nonce
                }, function (response) {

                    $("#wcf_activate_email_template").val(new_state == 'on' ? 1 : 0);

                    $(".wcar_tmpl_response_msg").remove();

                    $("<span class='wcar_tmpl_response_msg'> " + response.data + " </span>").insertAfter($switch).delay(2000).fadeOut().css('color', css);

                }
            );
        },


        toggle_activate_template: function () {
            var $switch, state, new_state;
            $switch = $(this);
            state = $switch.attr('wcf-ca-template-switch');
            new_state = state === 'on' ? 'off' : 'on';
            $("#wcf_activate_email_template").val(new_state == 'on' ? 1 : 0);
            $switch.attr('wcf-ca-template-switch', new_state);
        }
    }
	
	ZapierSettings = {
    	init: function () {

		    $(document).delegate("#wcf_ca_trigger_web_hook_abandoned_btn", "click",
			    { 'order_status': 'abandoned' },
			    ZapierSettings.zapier_trigger_sample);
	    },
		zapier_trigger_sample: function( event ) {
   
			var zapier_webhook_url =  $("#wcf_ca_zapier_cart_"+ event.data.order_status +"_webhook").val().trim();
			
			if ( ! zapier_webhook_url.length ) {
				$("#wcf_ca_"+ event.data.order_status +"_btn_message").text("Webhook URL is required.").fadeIn().css('color', '#dc3232').delay(2000).fadeOut();
				return;
			}

			$("#wcf_ca_"+ event.data.order_status +"_btn_message").text("Triggering...").fadeIn();
			
			var now = new Date();
			var datetime = now.getFullYear()+'/'+(now.getMonth()+1)+'/'+now.getDate();
			datetime += ' '+now.getHours()+':'+now.getMinutes()+':'+now.getSeconds();
			if ($.trim(zapier_webhook_url) !== "") {
				var sample_data = {
					"first_name": wcf_ca_details.name,
					"last_name": wcf_ca_details.surname,
					"email": wcf_ca_details.email,
					"order_status": event.data.order_status,
					"checkout_url": window.location.origin + "/checkout/?wcf_ac_token=something",
					"coupon_code": "abcgefgh",
                    "product_names": "Product1, Product2 & Product3",
                    "cart_total": wcf_ca_details.woo_currency_symbol + "20",
                    "product_table": '<table align= left; cellpadding="10" cellspacing="0" style="float: none; border: 1px solid #e5e5e5;"> <tr align="center"> <th style="color: #636363; border: 1px solid #e5e5e5;">Item</th> <th style="color: #636363; border: 1px solid #e5e5e5;">Name</th> <th style="color: #636363; border: 1px solid #e5e5e5;">Quantity</th> <th style="color: #636363; border: 1px solid #e5e5e5;">Price</th> <th style="color: #636363; border: 1px solid #e5e5e5;">Line Subtotal</th> </tr> <tr style=color: #636363; border: 1px solid #e5e5e5; align="center"> <td style="color: #636363; border: 1px solid #e5e5e5;"><img class="demo_img" style="height: 42px; width: 42px;" src="http://localhost/wp-local/wp-content/uploads/2019/11/wGdyS_Zgz1A.jpg"></td> <td style="color: #636363; border: 1px solid #e5e5e5;">Product1</td> <td style="color: #636363; border: 1px solid #e5e5e5;"> 1 </td> <td style="color: #636363; border: 1px solid #e5e5e5;">&pound;85.00</td> <td style="color: #636363; border: 1px solid #e5e5e5;" >&pound;85.00</td> </tr> </table>'
				};
				$.ajax({
					url: zapier_webhook_url,
					type: 'POST',
					data: sample_data,
					success: function(data) {
						if (data.status == "success") {
							$("#wcf_ca_"+ event.data.order_status +"_btn_message").text("Trigger Success!").css('color', '#46b450');
						} else {
							$("#wcf_ca_"+ event.data.order_status +"_btn_message").text("Trigger Failed!").css('color', '#dc3232');
						}
                        $("#wcf_ca_"+ event.data.order_status +"_btn_message").fadeIn().delay(2000).fadeOut();
					},
					error: function() {
						$("#wcf_ca_"+ event.data.order_status +"_btn_message").text("Trigger Failed!").css('color', '#dc3232');
					}
				});
			} else {
				$("wcf_ca"+ event.data.order_status +"_btn_message").text("Please verify webhook URL.").fadeIn().delay(2000).fadeOut();;
			}
		},
	}

    ToolTipHover = {
        init: function () {

            $(".wcf-ca-report-table-row .wcf-ca-icon-row").hover(function(){
                $(this).find('.wcf-ca-tooltip-text').toggleClass("display_tool_tip");
             });
        },   
    }

    $(document).ready(
        function () {
            EmailTemplatesAdmin.init();
            CartAbandonmentSettings.init();
            ZapierSettings.init();

            ToolTipHover.init();
        }
    );


})(jQuery);