(function($){

	/* It will redirect if anyone clicked on link before ready */
	$(document).on( 'click', 'a[href*="wcf-next-step"]', function(e) {
		
		e.preventDefault();

		if( 'undefined' !== typeof cartflows.is_pb_preview && '1' == cartflows.is_pb_preview ) {
			e.stopPropagation();
			return;
		}

		window.location.href = cartflows.next_step; 

		return false;
	});

	/* Once the link is ready this will work to stop conditional propogation*/
	$(document).on( 'click', '.wcf-next-step-link', function(e) {

		if( 'undefined' !== typeof cartflows.is_pb_preview && '1' == cartflows.is_pb_preview ) {
			e.preventDefault();
			e.stopPropagation();
			return false;
		}
	});

	// Remove css when oceanwp theme is enabled.
	var remove_oceanwp_custom_style = function(){
		if( 'OceanWP' === cartflows.current_theme && 'default' !== cartflows.page_template){
			var style = document.getElementById("oceanwp-style-css");
			if( null != style ){
				style.remove();
			}
		}
	};

	var trigger_facebook_events = function () {
		if ('enable' === cartflows.fb_active['facebook_pixel_tracking']) {

			if (cartflows.fb_active['facebook_pixel_id'] != '') {

				var facebook_pixel = cartflows.fb_active['facebook_pixel_id'];
				var initial_checkout_event = cartflows.fb_active['facebook_pixel_initiate_checkout'];
				var purchase_event = cartflows.fb_active['facebook_pixel_purchase_complete'];
				var add_payment_info_event = cartflows.fb_active['facebook_pixel_add_payment_info'];
				var facebook_pixel_for_site = cartflows.fb_active['facebook_pixel_tracking_for_site'];
				var is_checkout_page = cartflows.is_checkout_page;

				fbq('init', facebook_pixel);
				fbq('track', 'PageView', {'plugin': 'CartFlows'});
				if ('enable' === initial_checkout_event) {
					if ('1' === is_checkout_page) {
						fbq('track', 'AddToCart', cartflows.params);
						fbq('track', 'InitiateCheckout', cartflows.params);
					}
				}

				if ('enable' === purchase_event) {
					var order_details = $.cookie('wcf_order_details');
					if (typeof order_details !== 'undefined') {
						fbq('track', 'Purchase', jQuery.parseJSON(order_details));
						$.removeCookie('wcf_order_details', {path: '/'});
					}
				}

				if ('enable' === add_payment_info_event) {
					jQuery("form.woocommerce-checkout").on('submit', function () {
						var params = cartflows.params;
						fbq('track', 'AddPaymentInfo', params);
					});
				}

			}
		}
	}

	var trigger_google_events = function(){

		if( cartflows.wcf_ga_active['enable_google_analytics'] === "enable" ){
			// Get all required Data
			var google_a_id = cartflows.wcf_ga_active['google_analytics_id'];
			var ga_for_other_page = cartflows.wcf_ga_active['enable_google_analytics_for_site'];
			var ga_begin_checkout = cartflows.wcf_ga_active['enable_begin_checkout'];
			var ga_add_payment_info = cartflows.wcf_ga_active['enable_add_payment_info'];
			var ga_purchase_event = cartflows.wcf_ga_active['enable_purchase_event'];
			var ga_add_to_cart = cartflows.wcf_ga_active['enable_add_to_cart'];
			var cookies = $.cookie('wcf_ga_trans_data');
			var is_checkout_page = cartflows.is_checkout_page;
			
			if( 'disable' === ga_for_other_page ){
				//Common page view event for cartflows pages.
				gtag('event', 'page_view', { send_to: google_a_id, non_interaction : true } );
			}
			if( "1" === is_checkout_page  ){
				var param = cartflows.ga_param;
					
					var event_data = { 
						 send_to: google_a_id,
						 event_category: "ecommerce",
						 items: param,
						 non_interaction : true
						}

				if ('enable' === ga_begin_checkout) {	
					gtag('event', 'begin_checkout', event_data );
				}

				if ( 'enable' === ga_add_to_cart ) {
					gtag('event', 'add_to_cart', event_data );
				}	

				if ( 'enable' === ga_add_payment_info ) {
					jQuery("form.woocommerce-checkout").on('submit', function () {
						gtag('event', 'add_payment_info', { send_to: google_a_id, non_interaction : true } );
					});
				}	
			}

			if( typeof cookies !== 'undefined' ){
				var ga_order_details = jQuery.parseJSON(cookies);

				var purchase_data = {
					send_to: google_a_id,
					event_category: "ecommerce",
					transaction_id: ga_order_details.transaction_id,
					value: ga_order_details.value,
					currency: ga_order_details.currency,
					shipping: ga_order_details.shipping,
					tax: ga_order_details.tax,
					items: ga_order_details.items,
					non_interaction: true
				}

				if ('enable' === ga_purchase_event) {
					gtag('event', 'purchase', purchase_data );
					$.removeCookie('wcf_ga_trans_data', {path: '/'});	
				}
			}
		}
	}

	$(document).ready(function($) {
		
		/* Assign the class & link to specific button */
		var next_links = $('a[href*="wcf-next-step"]');

		if ( next_links.length > 0 && 'undefined' !== typeof cartflows.next_step ) {
			next_links.addClass( 'wcf-next-step-link' );
			next_links.attr( 'href', cartflows.next_step );
		}
		remove_oceanwp_custom_style();
		if( '1' !== cartflows.is_pb_preview ) {
			trigger_facebook_events();
			trigger_google_events();
		}
	});
	
})(jQuery);