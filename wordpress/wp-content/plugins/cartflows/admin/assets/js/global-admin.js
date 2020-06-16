( function( $ ) {

	/**
	 * Add Shortcode next to metabox heading.
	 *
	 * @since 1.0.0
	 */

	/* Toogle fields logic for all fields */
	var wcf_toggle_fields_general_logic = function() {
		
		var fields = $('[toggle]');

		if ( fields.length < 1 ) {
			return;
		} 

		fields.each(function( i ) {
			console.log( $(this) );
		});

		// if( 'custom' == default_value ){
		// 	$('.field-wcf-field-tb-padding').show();
		// 	$('.field-wcf-field-lr-padding').show();
		// }else{
		// 	$('.field-wcf-field-tb-padding .wcf-field-row-content input[type="number"]').val('');
		// 	$('.field-wcf-field-lr-padding .wcf-field-row-content input[type="number"]').val('');

		// 	$('.field-wcf-field-tb-padding').hide();
		// 	$('.field-wcf-field-lr-padding').hide();
		// }

		$('.wcf-field-row input[type=checkbox]').on('change', function(e) {

			e.preventDefault();

			var $this 		= $(this),
				toggle_data = $this.attr('toggle');

			console.log( toggle_data );
			console.log( $(this).is(':checked') );
			
			// if(selected_value == 'custom' ){
			// 	$('.field-wcf-field-tb-padding').show();
			// 	$('.field-wcf-field-lr-padding').show();
			// }else{
			// 	$('.field-wcf-field-tb-padding').hide();
			// 	$('.field-wcf-field-lr-padding').hide();
			// }
		});

		var hide_show_fields = function() {
			// body...
		}
	};

	/* Show Hide Custom Options for input types */
	var wcf_checkout_show_field_custom_options = function() {
		var default_value = $('.field-wcf-input-field-size select').val();

		if( 'custom' == default_value ){
			$('.field-wcf-field-tb-padding').show();
			$('.field-wcf-field-lr-padding').show();
		}else{
			$('.field-wcf-field-tb-padding .wcf-field-row-content input[type="number"]').val('');
			$('.field-wcf-field-lr-padding .wcf-field-row-content input[type="number"]').val('');

			$('.field-wcf-field-tb-padding').hide();
			$('.field-wcf-field-lr-padding').hide();
		}

		$('.field-wcf-input-field-size select').on('change', function(e) {

			e.preventDefault();

			var $this 	= $(this),
				selected_value = $this.val();
			
			if(selected_value == 'custom' ){
				$('.field-wcf-field-tb-padding').show();
				$('.field-wcf-field-lr-padding').show();
			}else{
				$('.field-wcf-field-tb-padding').hide();
				$('.field-wcf-field-lr-padding').hide();
			}
		});
	};

	/* Show Hide Custom Options for Buttons */
	var wcf_checkout_show_button_custom_options = function() {
		
		var wrapper	 	  = $('.wcf-checkout-table');

		if ( wrapper.length < 1 ) {
			return;
		}

		var default_value = wrapper.find('.field-wcf-input-button-size select').val();

		if( 'custom' == default_value ){
			wrapper.find('.field-wcf-submit-tb-padding').show();
			wrapper.find('.field-wcf-submit-lr-padding').show();
		}else{
			wrapper.find('.field-wcf-submit-tb-padding').hide();
			wrapper.find('.field-wcf-submit-lr-padding').hide();
		}

		
		
		wrapper.find('.field-wcf-input-button-size select').on('change', function(e) {

			e.preventDefault();

			var $this 	= $(this),
				selected_value = $this.val();
			
			if(selected_value == 'custom' ){
				wrapper.find('.field-wcf-submit-tb-padding').show();
				wrapper.find('.field-wcf-submit-lr-padding').show();
			}else{
				wrapper.find('.field-wcf-submit-tb-padding').hide();
				wrapper.find('.field-wcf-submit-lr-padding').hide();
			}
		});
	};

	/* Show Hide Custom Options for Buttons */
	var wcf_optin_submit_button_custom_options = function() {
		
		var wrapper	 	  = $('.wcf-optin-table');

		if ( wrapper.length < 1 ) {
			return;
		}

		var default_value = wrapper.find('.field-wcf-submit-button-size select').val();
		
		if( 'custom' == default_value ){
			wrapper.find('.field-wcf-submit-tb-padding').show();
			wrapper.find('.field-wcf-submit-lr-padding').show();
			wrapper.find('.field-wcf-submit-button-position').show();
		}else{
			wrapper.find('.field-wcf-submit-tb-padding .wcf-field-row-content input[type="number"]').val('');
			wrapper.find('.field-wcf-submit-lr-padding .wcf-field-row-content input[type="number"]').val('');
			
			wrapper.find('.field-wcf-submit-tb-padding').hide();
			wrapper.find('.field-wcf-submit-lr-padding').hide();
			wrapper.find('.field-wcf-submit-button-position').hide();
		}

		wrapper.find('.field-wcf-submit-button-size select').on('change', function(e) {

			e.preventDefault();

			var $this 	= $(this),
				selected_value = $this.val();
			
			if(selected_value == 'custom' ){
				wrapper.find('.field-wcf-submit-tb-padding').show();
				wrapper.find('.field-wcf-submit-lr-padding').show();
				wrapper.find('.field-wcf-submit-button-position').show();
			}else{
				wrapper.find('.field-wcf-submit-tb-padding').hide();
				wrapper.find('.field-wcf-submit-lr-padding').hide();
				wrapper.find('.field-wcf-submit-button-position').hide();
			}
		});
	};

	var wcf_checkout_prevent_toggle_for_shortcode = function() {
		// Prevent inputs in meta box headings opening/closing contents.
		$( '#wcf-checkout-settings' ).find( '.hndle' ).unbind( 'click.postboxes' );

		$( '#wcf-checkout-settings' ).on( 'click', '.hndle', function( event ) {

			// If the user clicks on some form input inside the h3 the box should not be toggled.
			if ( $( event.target ).filter( 'input, option, label, select' ).length ) {
				return;
			}

			$( '#wcf-checkout-settings' ).toggleClass( 'closed' );
		});
	};

	var wcf_add_tool_tip_msg = function(){
		var tooltip = false;

		$( '.postbox' ).on('click', '.wcf-field-heading-help', function(){
			var tip_wrap = $(this).closest('.wcf-field-row');
	        	closest_tooltip = tip_wrap.find('.wcf-tooltip-text');
	        	
	        closest_tooltip.toggleClass('display_tool_tip');
	    });
	};


	// Check for the highlight area and add the class.
	var wcf_highlight_the_metabox = function(){

		if( ( 'undefined' !== typeof cartflows_admin ) && ( cartflows_admin.wcf_edit_test_mode ) ){

			$('#wcf-sandbox-settings').addClass("wcf-highlight");

			// Remove the class automatically after 6 seconds.
			setTimeout(function(){
				wcfDeactivateHighlight()
			}, 6000);

			// Click outside the higlight element and remove the class
			$(document).on('click', function (e) {
				wcfDeactivateHighlight();
			});
		}
	};

	// Function to remove the highlighted class
	var wcfDeactivateHighlight = function() {
		$('#wcf-sandbox-settings').removeClass('wcf-highlight');
	};

	var wcf_toggle_post_update = function() {

		if ( 'undefined' === typeof cartflows_woo ) {
			return;
		}

		if( ! cartflows_woo.show_update_post ) {
			$("#submitdiv").hide();
		}
	};

	var wcf_create_woo_product_from_iframe = function() {
	
		// Function to create an HTML elements
		function _create_html_element( args, appent_to ){

			window.htmlElement 				= document.createElement( args['element'] );
			window.htmlElement.id 			= args['id'];
	        window.htmlElement.className 	= args['class'];

	        if( 'body' === appent_to ){

	        	document.getElementsByTagName('body')[0].appendChild(window.htmlElement);
	        }else{
	        	document.getElementById( appent_to ).appendChild(window.htmlElement);
	        }
		}

		// Function to create Iframe
		function _create_iframe_element( args, appent_to ){

			window.iFrameElement 					= document.createElement( args['element'] );
			window.iFrameElement.id 				= args['id'];
	        window.iFrameElement.className 			= args['class'];
	        window.iFrameElement.frameborder 		= args['border'];
	        window.iFrameElement.allowtransparency 	= args['transparency'];
	        window.iFrameElement.src 				= args['src'];

	        window.iFrameElement.setAttribute('style', 'opacity: 0; visibility:hidden;');
	        
	        var created = document.getElementById( appent_to ).appendChild(window.iFrameElement);

	        $('#' + args['id'] ).on( 'load', function() {
				
			    $('#wcf-create-woo-product-iframe').contents().find("body").addClass("wcf-in-iframe");
			    
				/* Create Close Button */
				var args = {
					'element'		: 'a',
					'id'			: 'wcf-close-create-woo-product',
					'class'			: 'wcf-close-create-woo-product close-icon',
				};
				_create_html_element( args, 'wcf-create-woo-product-wrap' );

				// Display Iframe
				window.iFrameElement.setAttribute('style', 'opacity: 1; visibility:visible;');
				$( '.wcf-create-woo-product-wrap' ).addClass( 'product-loaded' );
				
			} );
		}
		
		// Function to destroy the Iframe & close the popup.
		function _destroy_create_woo_product_iframe(){
			window.iFrameElement.setAttribute('style', 'opacity: 0; visibility:hidden;');
			$('body').removeClass('wcf-create-woo-iframe-opened');
			$('#wcf-create-woo-product-overlay').removeClass('open');
			$('.wcf-create-woo-product').removeClass('updating-message');
			$( '.wcf-create-woo-product-wrap' ).removeClass( 'product-loaded' );
			$("#wcf-create-woo-product-overlay").remove();

		}

		function wcf_open_create_woo_product_popup(){
			$( '.wcf-create-woo-product' ).on( 'click', function( event ) {

				event.preventDefault();
				
				var create_btn = $('.wcf-create-woo-product');

				/* Display Loading */
				create_btn.addClass('updating-message');
				
				// Create wrapper div.
				var args = {
					'element': 'div',
					'id': 'wcf-create-woo-product-overlay',
					'class': 'wcf-create-woo-product-overlay'
				};

				// Create wrapper div.
				var args = {
					'element': 'div',
					'id': 'wcf-create-woo-product-overlay',
					'class': 'wcf-create-woo-product-overlay'
				};

				_create_html_element( args, 'body' );


	            /* Create frame wrap */
	            var args = {
					'element': 'div',
					'id': 'wcf-create-woo-product-wrap',
					'class': 'wcf-create-woo-product-wrap'
				};

				_create_html_element( args, 'wcf-create-woo-product-overlay' );

				$( '#wcf-create-woo-product-overlay' ).addClass( 'open' );

				/* Create Iframe */
				var args = {
					'element'		: 'iframe',
					'id'			: 'wcf-create-woo-product-iframe',
					'class'			: 'wcf-woo-product-iframe',
					'border'		: 0,
					'transparency'	: 'true',
					'src'			: cartflows_admin.create_product_src,
				};

				_create_iframe_element( args, 'wcf-create-woo-product-wrap' );

				$('body').addClass('wcf-create-woo-iframe-opened');
			});
		}

		// Close iframe events function.
		function wcf_close_create_woo_product_popup() {
			$( document.body ).on( 'click', '#wcf-close-create-woo-product', function(){
				if( $(this).hasClass('close-icon') && $('#wcf-create-woo-product-overlay').hasClass('open') ){
					
					_destroy_create_woo_product_iframe();
				}
			} );

			$( document.body ).on( 'click', '#wcf-create-woo-product-overlay', function(){
				if( $('#wcf-create-woo-product-overlay').hasClass('open') ){
					
					_destroy_create_woo_product_iframe();
				}
			} );
		}

		wcf_open_create_woo_product_popup();

		wcf_close_create_woo_product_popup();
	};
	
	var wcf_show_upgrade_to_pro_popup = function(){

		$('.wcf-custom-add-new-button').click(function(){
			$('#cartflows-upgrade-notice-popup').show();
			$('#cartflows-upgrade-notice-overlay').show();
		});
		$('.cartflows-close-popup-button').click(function(){
			$('#cartflows-upgrade-notice-popup').hide();
			$('#cartflows-upgrade-notice-overlay').hide();
		});
		$('#cartflows-upgrade-notice-overlay').click(function(){
  			$('#cartflows-upgrade-notice-popup').hide();
			$('#cartflows-upgrade-notice-overlay').hide();
		});
	};

	/* Optin - hide/show login */
	var wcf_optin_hide_show_init = function(){

		var wrapper = $('.wcf-optin-table');

		if ( wrapper.length < 1 ) {
			return;
		}

		var field 			= $('input[type=checkbox]#wcf-optin-pass-fields');
		var toggle_fields 	= [ '.field-wcf-optin-pass-specific-fields', '.wcf-optin-pass-fields-doc' ];

		if ( field.is(":checked") ) {
			$.each( toggle_fields, function(i, val) {
				wrapper.find( val ).show();
			})
		} else {
			$.each( toggle_fields, function(i, val) {
				wrapper.find( val ).hide();
			});
		}

		field.on('change', function(e) {
			
			if ( field.is(":checked") ) {
				$.each( toggle_fields, function(i, val) {
					wrapper.find( val ).show();
				})
			} else {
				$.each( toggle_fields, function(i, val) {
					wrapper.find( val ).hide();
				});
			}
		});
	};

	/* Gutenberg compatibility and events */
	var wcf_gutenberg_compatibility_and_events = function() {
		
		if( $('body .block-editor #editor').length < 1 ) {
			return;
		}

		var wcf_trigger_update_button_click = function(){
			
			$( '.wcf-column-right-footer #wcf-save' ).on( 'click', function( event ) {

				event.preventDefault();

				var wcf_update_button = $(this),
					wp_update_button  = $('.edit-post-header__settings button.editor-post-publish-button');

				if ( wcf_update_button.hasClass('is-busy') ) {
					return;
				}

				wcf_update_button.addClass('is-busy').val('Updating...');
				wp_update_button.trigger('click', 'update');

				var reloader = setInterval(function() {
	                
	                success = wp.data.select('core/editor').didPostSaveRequestSucceed();
	                
	                if ( success && ! wp_update_button.hasClass('is-busy') ) { 
	                	
		                clearInterval(reloader);

		                wcf_update_button.removeClass('is-busy').val('Update');
	                }
	            }, 300);
			});
		};

		var wcf_back_flow_button = function(){
			
			if ( 'cartflows_step' === typenow ) {

				var flow_back_button  = $('#wcf-gutenberg-back-flow-button').html();

				if( flow_back_button.length > 0 ){

					$('body #editor').find('.edit-post-header-toolbar').append(flow_back_button);
				}
			}
		};

		/* Trigger update button click */
		wcf_trigger_update_button_click();

		setTimeout(function () {
			wcf_back_flow_button();
		}, 300);
	};

	$( document ).ready(function() {
		
		/* Checkout */
		wcf_checkout_show_field_custom_options();

		wcf_checkout_show_button_custom_options();

		wcf_checkout_prevent_toggle_for_shortcode();

		/* Optin */
		wcf_optin_submit_button_custom_options();
		wcf_optin_hide_show_init();

		/* Other */
		wcf_add_tool_tip_msg();

		wcf_highlight_the_metabox();

		wcf_toggle_post_update();
		
		wcf_show_upgrade_to_pro_popup();
		
		/* Create woo product from iframe */
		wcf_create_woo_product_from_iframe();

		/* Gutenberg compatibility and events */
		wcf_gutenberg_compatibility_and_events();
	});
})( jQuery ); 
