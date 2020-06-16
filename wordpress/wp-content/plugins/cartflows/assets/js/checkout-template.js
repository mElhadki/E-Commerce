(function($){

	var wcf_reload_checkout_on_return = function() {
		
		var vis = (function(){
		    var stateKey, eventKey, keys = {
		        hidden: "visibilitychange",
		        webkitHidden: "webkitvisibilitychange",
		        mozHidden: "mozvisibilitychange",
		        msHidden: "msvisibilitychange"
		    };
		    for (stateKey in keys) {
		        if (stateKey in document) {
		            eventKey = keys[stateKey];
		            break;
		        }
		    }
		    return function(c) {
		        if (c) document.addEventListener(eventKey, c);
		        return !document[stateKey];
		    }
		})();

		var visible = vis(); // gives current state
		
		vis(function(){
			
			if ( vis() ) {
				location.reload();
			}
		});
	}
	

	var wcf_show_loader = function() {
		$('.wcf-loader-bg').addClass('show');
		
	}

	var wcf_hide_loader = function() {
		$('.wcf-loader-bg').removeClass('show');
		
	}

	/**
	* Checkout Custom Field Validations
	* This will collect all the present fields in the woocommerce form and adds an class if the field
	* is blank
	*/
	var wcf_custom_field_validation = function(){

		var custom_field_add_class = function (field_value, field_row, field_wrap, field_type){

			if (  field_value == '' || 'select' == field_type && field_value == ' ') {
		    	if( field_row.hasClass('validate-required') ){

		    		field_wrap.addClass('field-required');
		    	}
		    } else {
		    	field_wrap.removeClass('field-required');
		    }

		}

		var fields_wrapper = $('form.woocommerce-checkout #customer_details'),
			$all_fields    = fields_wrapper.find('input, textarea'),
			$selects	   = fields_wrapper.find('select');
			
		$all_fields.blur(function(){
		    var $this 		= $(this),
		    	field_type	= $this.attr('type'),
		    	field_row   = $this.closest('p.form-row'),
		 	 	field_value = $this.val();

		   	custom_field_add_class(field_value, field_row, $this, field_type);

		});


		$selects.blur(function(){
		    var $this 		= $(this),
		    	field_row   = $this.closest('p.form-row'),
		    	field_type  = 'select',
		    	field_wrap	= field_row.find('.select2-container--default'),
		 	 	field_value = field_row.find('select').val();
		    	
		    custom_field_add_class(field_value, field_row, field_wrap, field_type);

		});

	}

	/**
	* Billing and shipping field add class form-row-first and form-row-last
	* To add those classes to change the layout of the field
	*/
	var add_custom_class_address_field = function(){
		// For Billing address fields.
		var $get_checkout_style_layout = $('.cartflows-container').find('.wcf-embed-checkout-form-two-column'),
			$get_bill_addr_field_one = $('.wcf-embed-checkout-form .woocommerce-checkout').find('#billing_address_1_field'),
			$get_bill_addr_field_two = $('.wcf-embed-checkout-form .woocommerce-checkout').find('#billing_address_2_field');
			
			if( $get_bill_addr_field_one.hasClass('form-row-wide') ){
				$get_bill_addr_field_one.removeClass('form-row-wide');
				$get_bill_addr_field_one.addClass('form-row-first');
			}

			if( $get_bill_addr_field_two.hasClass('form-row-wide') ){
				$get_bill_addr_field_two.removeClass('form-row-wide');
				$get_bill_addr_field_two.addClass('form-row-last');

				if( $get_bill_addr_field_two.find('label').hasClass('screen-reader-text') ){

					$get_bill_addr_field_two.addClass('mt20');
				}else{
					$get_bill_addr_field_two.removeClass('mt20');
				}
			}

		// For Shipping address fields.
		var $get_ship_addr_field_one = $('.wcf-embed-checkout-form .woocommerce-checkout').find('#shipping_address_1_field');
		var $get_ship_addr_field_two = $('.wcf-embed-checkout-form .woocommerce-checkout').find('#shipping_address_2_field');
			if( $get_ship_addr_field_one.hasClass('form-row-wide') ){
				$get_ship_addr_field_one.removeClass('form-row-wide');
				$get_ship_addr_field_one.addClass('form-row-first');
			}

			if( $get_ship_addr_field_two.hasClass('form-row-wide') ){
				$get_ship_addr_field_two.removeClass('form-row-wide');
				$get_ship_addr_field_two.addClass('form-row-last');

				if( $get_ship_addr_field_two.find('label').hasClass('screen-reader-text') ){

					$get_ship_addr_field_two.addClass('mt20');
				}else{
					$get_ship_addr_field_two.removeClass('mt20');
				}
			}

		function address_fields_management( type ) {
			
			var wrapper = $('.woocommerce-' + type + '-fields' );

			setTimeout(function() {
				var column_three = wrapper.find('.wcf-column-33');
				column_three.css( 'clear', '' );
				column_three.first().css( 'clear', 'left' );
				// column_three.first().css( 'margin-right', '10px' );
				// column_three.last().css( 'margin-left', '10px' );
			}, 100);

			setTimeout(function() {
				var column_fifty = wrapper.find('.wcf-column-50');
				column_fifty.css( 'clear', '' );
				column_fifty.last().css( 'clear', 'left' );
			}, 100);
		}

		// address_fields_management( 'billing' );
		// address_fields_management( 'shipping' );
		
		var billing_country = $('.wcf-embed-checkout-form .woocommerce-checkout').find('#billing_country');
		
		billing_country.on( 'change', function(e) {
			
			// address_fields_management( 'billing' );
		} );

		var shipping_country = $('.wcf-embed-checkout-form .woocommerce-checkout').find('#shipping_country');
		
		shipping_country.on( 'change', function(e) {
			
			// address_fields_management( 'shipping' );
			
		} );
	}

	var wcf_check_is_local_storage = function(){ 
		var test = 'test';
		try {
			localStorage.setItem(test, test);
			localStorage.removeItem(test);
			return true;
		} catch(e) {
			return false;
		}
	}

	var wcf_persistent_data = function(){
		
		if( 'yes' != cartflows.allow_persistance ){
			return;
		}

		if ( false === wcf_check_is_local_storage() ) {
			return;
		}

		var checkout_cust_form 	= 'form.woocommerce-checkout #customer_details';
		
		var wcf_form_data = {
			set : function (){
				
				var checkout_data 	= [];
				var checkout_form 	= $('form.woocommerce-checkout #customer_details');
				
				localStorage.removeItem('cartflows_checkout_form');

				checkout_form.find('input[type=text], select, input[type=email], input[type=tel]').each(function(){
					checkout_data.push({ name: this.name, value: this.value});
				});

				cartflows_checkout_form = JSON.stringify(checkout_data);
				localStorage.setItem('cartflows_checkout_form', cartflows_checkout_form);
			},
			get : function (){
				
		
				if( localStorage.getItem('cartflows_checkout_form') != null ){
					
					checkout_data = JSON.parse( localStorage.getItem('cartflows_checkout_form') );
					
					for (var i = 0; i < checkout_data.length; i++) {
						if($('form.woocommerce-checkout [name='+checkout_data[i].name+']').hasClass('select2-hidden-accessible'))
						{
							$('form.woocommerce-checkout [name='+checkout_data[i].name+']').selectWoo("val", [checkout_data[i].value]);
						}else{
							$('form.woocommerce-checkout [name='+checkout_data[i].name+']').val(checkout_data[i].value);
						}
						
					}
				}
			}
		}
		
		wcf_form_data.get();
		
		$( checkout_cust_form + " input, " + checkout_cust_form + " select").change( function() {
			wcf_form_data.set();
		});
	}


	$(window).load(function(){
		// $( 'body' ).trigger( 'update_checkout' );
	});

	var wcf_checkout_coupons = {

		init: function() {
			
			$( document.body ).on( 'click', '.wcf-submit-coupon', this.submit_coupon );
			$( document.body ).on( 'click', '.wcf-remove-coupon', this.remove_coupon );

		},

		submit_coupon: function( e ) {

			e.preventDefault();
			var coupon_wrapper = $('.wcf-custom-coupon-field'),
				coupon_field   = coupon_wrapper.find('.wcf-coupon-code-input'),
				coupon_value   = coupon_field.val();

			if( '' == coupon_value){
				coupon_field.addClass('field-required');
				return false;
			}else{
				coupon_field.removeClass('field-required');
			}

			var data = {
				coupon_code : coupon_value,
				action      : 'wcf_woo_apply_coupon',
				security    : cartflows.wcf_validate_coupon_nonce   
			};

			$.ajax({
				type:		'POST',
				url:		cartflows.ajax_url,
				data:		data,

				success:	function( code ) {
					
					var coupon_message = $(".wcf-custom-coupon-field"); 
					coupon_message.find( '.woocommerce-error, .woocommerce-message' ).remove();
					
					var data = JSON.parse(code);
					
					if( data.status == true ) {
												
						$( document.body ).trigger( 'update_checkout', { update_shipping_method: false } );		
				        coupon_message.prepend(data.msg);
					}else{
						coupon_message.prepend(data.msg);
					}
				},

			});
		},

		remove_coupon: function( e ) {

			e.preventDefault();
			var data = {
				coupon_code	: 	$(this).attr("data-coupon"),
				action		:   'wcf_woo_remove_coupon',
				security	: 	cartflows.wcf_validate_remove_coupon_nonce   
			};

			$.ajax({
				type:		'POST',
				url:		cartflows.ajax_url,
				data:		data,

				success:	function( code ) {
					
					var coupon_message = $(".wcf-custom-coupon-field"); 
					coupon_message.find( '.woocommerce-error, .woocommerce-message' ).hide();	
				
					if( code ) {
						$( document.body ).trigger( 'update_checkout', { update_shipping_method: false } );		
				        coupon_message.prepend(code);
	            	
					}
				}
			});
		},		
	}

	var wcf_remove_cart_products = function(){
		
			$( document.body ).on( 'click', '#wcf-embed-checkout-form .remove', function(e){
				e.preventDefault();
				var p_id = $(this).attr("data-id");
				var data = {
					p_key	: 	$(this).attr("data-item-key"),
					p_id	: 	p_id,
					action		:   'wcf_woo_remove_cart_product',
					security	: 	cartflows.wcf_validate_remove_cart_product_nonce   
				};
	
				$.ajax({
					type:		'POST',
					url:		cartflows.ajax_url,
					data:		data,
	
					success:	function( response ) {
						var data = JSON.parse(response);
				
						if( data.need_shipping == false ) {
							// $('#wcf-embed-checkout-form').find('#ship-to-different-address-checkbox').hide();
							$('#wcf-embed-checkout-form').find('#ship-to-different-address-checkbox').attr("checked",false);
						}
						$('#wcf-embed-checkout-form').find('.woocommerce-notices-wrapper').first().html(data.msg);
						$(document).trigger('cartflows_remove_product',[p_id]);
						$('#wcf-embed-checkout-form').trigger('update_checkout');
					}
				});
			});
		
		}

	$(document).ready(function($) {

		wcf_persistent_data();
		
		//wcf_reload_checkout_on_return();

		wcf_custom_field_validation();
		
		add_custom_class_address_field();

		wcf_remove_cart_products();

		wcf_checkout_coupons.init();

	});

})(jQuery);