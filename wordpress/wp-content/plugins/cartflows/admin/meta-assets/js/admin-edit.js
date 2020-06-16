(function($){

	CartFlowsAdminEdit = {

		/**
		 * Init
		 */
		init: function()
		{
			this._bind();
			this._set_font_weigths();
		},
		
		/**
		 * Binds events
		 */
		_bind: function()
		{
			$( document ).on('change', '.wcf-field-font-family', CartFlowsAdminEdit._set_font_weight_select_options );
			$( document ).on('change', '.wcf-field-font-weight', CartFlowsAdminEdit._set_font_weight_val );
		},

		_set_font_weight_val: function(event) {
			event.preventDefault();
			$(this).attr( 'data-selected', $(this).val() );

			CartFlowsAdminEdit._set_google_url();
		},

		_set_font_weigths: function() {

			if ( 'function' !== typeof $('.wcf-field-font-family').select2 ){
				return;
			}

			$('.wcf-field-font-family').select2();

			var google_url = '';
			var google_font_families = {};

			$('.wcf-field-font-family').each(function(index, el) {
				var font_family = $(el).val();
				var id    = $(el).data('for');
				
				var temp = font_family.match("'(.*)'");

				if( temp && temp[1] ) {
					font_family = temp[1];
				}

				var new_font_weights = {};
				if( wcf.google_fonts[ font_family ] ) {

					var variants = wcf.google_fonts[ font_family ][0];

					$.each( variants, function(index, weight) {
						if( ! weight.includes( 'italic' ) ) {
							new_font_weights[ weight ] = wcf.font_weights[ weight ];
						}
					});
		
					var weight = $( '.wcf-field-font-weight[data-for="'+id+'"]' );
					if( weight.length ) {

						weight.empty(); // remove old options
						var current_selected = weight.attr('data-selected');
						var selected = "";

						$.each(new_font_weights, function(key,value) {

							if( key == current_selected ) {
								var selected = "selected='selected'";
							}

							weight.append($("<option "+selected+"></option>").attr("value", key).text(value));
						});
					}

					temp_font_family = font_family.replace(' ', '+');
					google_font_families[ temp_font_family ] = new_font_weights;

				} else if( wcf.system_fonts[ font_family ] ) {

					var variants = wcf.system_fonts[ font_family ]['variants'];

					$.each( variants, function(index, weight) {
						if( ! weight.includes( 'italic' ) ) {
							new_font_weights[ weight ] = wcf.font_weights[ weight ];
						}
					});

					var weight = $( '.wcf-field-font-weight[data-for="'+id+'"]' );

					if( weight.length ) {
						var current_selected = weight.attr('data-selected');

						weight.empty(); // remove old options
						var selected = "";
						$.each(new_font_weights, function(key,value) {

							if( key == current_selected ) {
								var selected = "selected='selected'";
							} else {
							}
							weight.append($("<option "+selected+"></option>").attr("value", key).text(value));
						});
					}
				}
			});

			CartFlowsAdminEdit._set_google_url();
		},

		_set_google_url: function() {
			var google_url = '';
			$('.wcf-field-font-family').each(function(index, el) {

				var font_family = $(el).val();
				var id    = $(el).data('for');

				var temp = font_family.match("'(.*)'");

				if( temp && temp[1] ) {
					font_family = temp[1];
				}

				if( ( 'inherit' != font_family ) && ( 'Helvetica' !== font_family ) && ( 'Verdana' !== font_family ) && ( 'Arial' !== font_family ) && ( 'Times' !== font_family ) && ( 'Georgia' !== font_family ) && ( 'Courier' !== font_family ) ) {
					font_family = font_family.replace(' ', '+');

					var weight      = $( '.wcf-field-font-weight[data-for="'+id+'"]' );
					var font_weight = weight.val();
					
					if( typeof  font_weight == 'undefined' && id == 'wcf-base' ){
						font_weight = '';
					}
					var bar   = '',
						colon = '';

					if( google_url ) {
						if( font_weight != ''){
							bar   = '|';
							colon = ':';
						}
						google_url = google_url + bar + font_family + colon + font_weight;
					} else {
						google_url = font_family;
					}
				}

			});

			$('#wcf-field-google-font-url').val( '//fonts.googleapis.com/css?family=' + google_url );
		},

		_set_font_weight_select_options: function(event) {
			event.preventDefault();

			CartFlowsAdminEdit._set_font_weigths();
		}
	};

	/**
	 * Initialization
	 */
	$(function(){
		CartFlowsAdminEdit.init();
	});

})(jQuery);

(function($){

	if ( typeof getEnhancedSelectFormatString == "undefined" ) {
		function getEnhancedSelectFormatString() {
			var formatString = {
				noResults: function() {
					return wc_enhanced_select_params.i18n_no_matches;
				},
				errorLoading: function() {
					return wc_enhanced_select_params.i18n_ajax_error;
				},
				inputTooShort: function( args ) {
					var remainingChars = args.minimum - args.input.length;

					if ( 1 === remainingChars ) {
						return wc_enhanced_select_params.i18n_input_too_short_1;
					}

					return wc_enhanced_select_params.i18n_input_too_short_n.replace( '%qty%', remainingChars );
				},
				inputTooLong: function( args ) {
					var overChars = args.input.length - args.maximum;

					if ( 1 === overChars ) {
						return wc_enhanced_select_params.i18n_input_too_long_1;
					}

					return wc_enhanced_select_params.i18n_input_too_long_n.replace( '%qty%', overChars );
				},
				maximumSelected: function( args ) {
					if ( args.maximum === 1 ) {
						return wc_enhanced_select_params.i18n_selection_too_long_1;
					}

					return wc_enhanced_select_params.i18n_selection_too_long_n.replace( '%qty%', args.maximum );
				},
				loadingMore: function() {
					return wc_enhanced_select_params.i18n_load_more;
				},
				searching: function() {
					return wc_enhanced_select_params.i18n_searching;
				}
			};

			var language = { 'language' : formatString };

			return language;
		}
	}
	
	var wcf_init_color_fields = function() {
		
		// Call color picker
    	$('.wcf-color-picker').wpColorPicker();
	};
	var wcf_woo_product_search_init = function() {
		
		var $product_search = $('.wcf-product-search:not(.wc-product-search)');

		if( $product_search.length > 0 ) {
			
			$product_search.addClass('wc-product-search');

			$(document.body).trigger('wc-enhanced-select-init');
		}
	};

	var wcf_woo_coupon_search_init = function() {

		$( ':input.wc-coupon-search' ).filter( ':not(.enhanced)' ).each( function() {
			var select2_args = {
				allowClear:  $( this ).data( 'allow_clear' ) ? true : false,
				placeholder: $( this ).data( 'placeholder' ),
				minimumInputLength: $( this ).data( 'minimum_input_length' ) ? $( this ).data( 'minimum_input_length' ) : '3',
				escapeMarkup: function( m ) {
					return m;
				},
				ajax: {
					url:         wc_enhanced_select_params.ajax_url,
					dataType:    'json',
					quietMillis: 250,
					data: function( params, page ) {
						return {
							term:     params.term,
							action:   $( this ).data( 'action' ) || 'wcf_json_search_coupons',
							security: cartflows_admin.wcf_json_search_coupons_nonce
						};
					},
					processResults: function( data, page ) {
						var terms = [];
						if ( data ) {
							$.each( data, function( id, text ) {
								terms.push( { id: id, text: text } );
							});
						}
						return { results: terms };
					},
					cache: true
				}
			};

			select2_args = $.extend( select2_args, getEnhancedSelectFormatString() );

			$( this ).select2( select2_args ).addClass( 'enhanced' );
		});
	};
	var wcf_pages_search_init = function() {


		$( 'select.wcf-search-pages' ).each( function() {
			
			var select2_args = {
				allowClear:  $( this ).data( 'allow_clear' ) ? true : false,
				placeholder: $( this ).data( 'placeholder' ) ? $( this ).data( 'placeholder' ): '',
				minimumInputLength: $( this ).data( 'minimum_input_length' ) ? $( this ).data( 'minimum_input_length' ) : '3',
				escapeMarkup: function( m ) {
					return m;
				},
				ajax: {
					url:         wc_enhanced_select_params.ajax_url,
					dataType:    'json',
					quietMillis: 250,
					data: function( params, page ) {
						return {
							term:     params.term,
							action:   $( this ).data( 'action' ) || 'wcf_json_search_pages',
							security: cartflows_admin.wcf_json_search_pages_nonce
						};
					},
					processResults: function( data, page ) {
						
						return { results: data };
					},
					cache: true
				}
			};

			select2_args = $.extend( select2_args, getEnhancedSelectFormatString() );

			$( this ).select2( select2_args ).addClass( 'enhanced' );
		});
	};

	var wcf_hide_discount_value_field= function(){
		
		var show_hide_discount_field = function($this,value){
			
			if( '' === value ){
				$this.closest('.wcf-repeatable-row-advance-fields').find('.wcf-repeatable-row-discount-field').hide();
			}else{
				$this.closest('.wcf-repeatable-row-advance-fields').find('.wcf-repeatable-row-discount-field').show();
			}
		};

		wrap = $('.wcf-repeatable-row-advance-fields');
		
		wrap.find('.wcf-repeatable-discount-type-field select').each(function(e){
			$this = $(this);
			var value = $this.val();
			show_hide_discount_field($this,value);
		});	

		/* On changes */
		$(document).on( 'change', '.wcf-repeatable-discount-type-field select', function(e) {
			$this = $(this);
			var value = $this.val();
			show_hide_discount_field($this,value);
		});

		/* Quanity field non zero value */
		$('.wcf-repeatable-row-advance-fields input[type=number]').on('keyup', function(e) {

			var value = $(this).val();
			var number = parseInt( value );

			if( value !== '' && ( number < 1 || isNaN( number ) ) ) {
				$(this).val(1);
			}
		});
	};

	var wcf_toggle_view_repeatable_fields = function() {
		
		$(document.body).on('click','.wcf-repeater-fields-head-wrap', function (e) {
			
			var e_target = $(e.target);
			
			if( ! e_target.hasClass('wcf-repeatable-row-standard-fields') && ! e_target.hasClass('wcf-repeater-fields-head-wrap') ) {
				return;
			}

			var $this          = $(this),
				fields_wrapper = $this.closest('.wcf-repeatable-row');

			fields_wrapper.toggleClass("active");
			$this.find('i').toggleClass('dashicons-arrow-up  dashicons-arrow-down');
		});
	};

	var wcf_add_repeatable_product = function() {
		
		$('.wcf-add-repeatable').on('click', function(e) {

			var $this 	   = $(this),
				field_name = $this.data('name'),
				wrap 	   = $this.closest('.wcf-field-row-content').find('.wcf-repeatables-wrap'),
				template   = $('#tmpl-wcf-product-repeater').html(),
				highest    = 0,
				new_key    = 0,
				unique_id  = Math.random().toString(36).substring(2, 10);

			wrap.find('.wcf-repeatable-row').each(function(er) {
				
				var r_row 	= $(this),
					key 	= r_row.data('key');
				
				if ( key > highest ) {
					highest = key;
				}
			});
			
			new_key = highest + 1;
			
			template = template.replace( /{{id}}/g, new_key );
			template = template.replace( /{{unique_id}}/g, unique_id );
			
			$( template ).insertBefore( ".wcf-add-fields" );

			/* Woo Product Search */
			wcf_woo_product_search_init();

			e.preventDefault();
		});
	};

	var wcf_remove_repeatable_product = function() {
		$(document).on( 'click', '.wcf-repeatable-remove', function(e) {
			
			var $this = $(this),
				deletable_row = $this.closest('.wcf-repeatable-row'),
				wrap = $this.closest('.wcf-repeatables-wrap');

			var all_rows = wrap.find('.wcf-repeatable-row');

			if ( all_rows.length === 1 ) {
				alert("You cannot remove this product.");
			}else{
				deletable_row.remove();
			}
		} );
	};

	/* Simple Quantity */
	var wcf_set_variation_mode_option = function() {
		
		$('.wcf-variation-mode select').each(function(e) {
			var $this 			= $(this),
				variation_mode 	= $this.val(),
				wrap 			= $this.closest('.wcf-repeatable-row-standard-fields'),
				quantity_data 	= wrap.find('.wcf-quantity-data');

			if ( 'simple-quantity' === variation_mode ) {
				quantity_data.show();
			}else{
				quantity_data.hide();
			}
		});

		$(document).on( 'change', '.wcf-variation-mode select', function(e) {
			var $this 			= $(this),
				variation_mode 	= $this.val(),
				wrap 			= $this.closest('.wcf-repeatable-row-standard-fields'),
				quantity_data 	= wrap.find('.wcf-quantity-data');

			if ( 'simple-quantity' === variation_mode ) {
				quantity_data.show();
			}else{
				quantity_data.hide();
			}
		});
	};

	/* Custom Fields Hide / Show */
	var wcf_custom_fields_events = function() {

		/* Ready */
		wcf_custom_fields();

		/* Change Custom Field*/
		$('.wcf-column-right .wcf-checkout-custom-fields .wcf-cc-fields .wcf-cc-checkbox-field input:checkbox').on('change', function(e) {
			wcf_custom_fields();
		});
	};

	/* Disable/Enable Custom Field section*/
	var wcf_custom_fields = function() {

		var wrap 			= $('.wcf-checkout-table'),
			custom_fields 	= wrap.find('.wcf-column-right .wcf-checkout-custom-fields .wcf-cc-fields .wcf-cc-checkbox-field .field-wcf-custom-checkout-fields input:checkbox');

		var field_names = [
			'.wcf-custom-field-box',
			'.wcf-cb-fields',
			'.wcf-sb-fields',
		];

		if ( custom_fields.is(":checked") ) {
			$.each( field_names, function(i, val) {
				wrap.find( val ).show();
			})
		} else {
			$.each( field_names, function(i, val) {
				wrap.find( val ).hide();
			});
		}
	};

	/* Advance Style Fields Hide / Show */
	var wcf_advance_style_fields_events = function() {

		/* Ready */
		wcf_advance_style_fields();
		wcf_thankyou_advance_style_fields();
		wcf_thankyou_settings_fields();

		/* Change Advance Style Field*/
		$('.wcf-column-right .wcf-checkout-style .wcf-cs-fields .wcf-cs-checkbox-field input:checkbox').on('change', function(e) {
			wcf_advance_style_fields();
		});

		/* Change Advance Style Field*/
		$('.wcf-thankyou-table [name="wcf-tq-advance-options-fields"]').on('change', function(e) {
			wcf_thankyou_advance_style_fields();
		});

		$('.wcf-thankyou-table [name="wcf-show-tq-redirect-section"]').on('change', function(e) {
			wcf_thankyou_settings_fields();
		});


	};

	var wcf_thankyou_advance_style_fields = function() {
		var wrap 			= $('.wcf-thankyou-table'),
			checkbox_field  = $('.wcf-thankyou-table [name="wcf-tq-advance-options-fields"]');

		var field_names = [
			'.field-wcf-tq-container-width',
			'.field-wcf-tq-section-bg-color'
		];

		if ( checkbox_field.is(":checked") ) {
			$.each( field_names, function(i, val) {
				wrap.find( val ).show();
			})
		} else {
			$.each( field_names, function(i, val) {
				wrap.find( val ).hide();
			});
		}
	};

	var wcf_thankyou_settings_fields = function() {
		var wrap 			= $('.wcf-thankyou-table'),
			checkbox_field  = $('.wcf-thankyou-table [name="wcf-show-tq-redirect-section"]');

		var field_names = [
			'.field-wcf-tq-redirect-link'
		];

		if ( checkbox_field.is(":checked") ) {
			$.each( field_names, function(i, val) {
				wrap.find( val ).show();
			})
		} else {
			$.each( field_names, function(i, val) {
				wrap.find( val ).hide();
			});
		}
	};
	
	/* Disable/Enable Advance Style Field section*/
	var wcf_advance_style_fields = function() {

		var wrap 			= $('.wcf-checkout-table'),
			custom_fields 	= wrap.find('.wcf-column-right .wcf-checkout-style .wcf-cs-fields .wcf-cs-checkbox-field input:checkbox');

		var field_names = [
			'.wcf-cs-fields-options',
			'.wcf-cs-button-options',
			'.wcf-cs-section-options',
		];

		// console.log(custom_fields);

		if ( custom_fields.is(":checked") ) {
			$.each( field_names, function(i, val) {
				wrap.find( val ).show();
			})
		} else {
			$.each( field_names, function(i, val) {
				wrap.find( val ).hide();
			});
		}
	};

	var wcf_settings_tab = function() {

		if( $('.wcf-tab.active').length ) {
			$active_tab = $('.wcf-tab.active');

			$active_tab_markup = '.' + $active_tab.data('tab');

			if( $( $active_tab_markup ).length ) {
				$( $active_tab_markup ).siblings().removeClass('active');
				$( $active_tab_markup ).addClass('active');
			}
		}

		$('.wcf-tab').on('click', function(e) {
			e.preventDefault();

			$this 		= $(this),
			tab_class 	= $this.data('tab');

			$('#wcf-active-tab').val( tab_class );

			$this.siblings().removeClass('wp-ui-text-highlight active');
			$this.addClass('wp-ui-text-highlight active');
			
			if( $( '.' + tab_class ).length ) {
				$( '.' + tab_class ).siblings().removeClass('active');
				$( '.' + tab_class ).addClass('active');
			}
		});
	};

	var wcf_products_sortable = function(){
		$('.wcf-checkout-general .wcf-repeatables-wrap').sortable({
			forcePlaceholderSize: true,
			placeholder: "sortable-placeholder",
		});
	};

	var wcf_input_file_init = function() {

		var file_frame;
		window.inputWrapper = '';

		$( document.body ).on('click', '.wcf-select-image', function(e) {

			e.preventDefault();

			var button = $(this);
			window.inputWrapper = $(this).closest('.wcf-field-row');
			if ( file_frame ) {
				file_frame.open();
				return;
			}

			// Create the media frame.
			file_frame = wp.media( {
				multiple: false
			} );

			// When an image is selected, run a callback.
			file_frame.on( 'select', function() {

				var attachment = file_frame.state().get( 'selection' ).first().toJSON();
				// place first attachment in field
				window.inputWrapper.find( '#wcf-image-preview' ).show();
				
				window.inputWrapper.find( '#wcf-image-preview' ).children('.saved-image').remove();

				window.inputWrapper.find( '#wcf-image-preview' ).append('<img src="' + attachment.url + '" width="150" class="saved-image" style="margin-bottom:12px;" />');

				window.inputWrapper.find( '.wcf-image' ).val( attachment.url );
 
				//image obj.
				var size = Object.keys(attachment).length;

				if(size > 0){
					window.inputWrapper.find( '.wcf-image-obj' ).val( JSON.stringify(attachment) );
				}
				window.inputWrapper.find('.wcf-remove-image').show();
			});

			// Finally, open the modal
			file_frame.open();
		});

		$( '.wcf-remove-image' ).on( 'click', function( e ) {
			e.preventDefault();

			var button   = $(this),
			    closeRow = $(this).closest('.wcf-field-row');

			    closeRow.find( '#wcf-image-preview img' ).hide();
			    closeRow.find( '.wcf-image-id' ).val('');
				closeRow.find( '.wcf-image' ).val('');
				closeRow.find('.wcf-image-obj').val('');
			    button.hide();
			
		});
	};

	$(document).ready(function($) {

		wcf_settings_tab();

		wcf_init_color_fields();

		/* Woo Product Search */
		wcf_woo_product_search_init();

		/* Woo Coupon Search */
		wcf_woo_coupon_search_init();

		/* Pages Search */
		wcf_pages_search_init();

		/* Select Image Field */
		wcf_input_file_init();

		/* Set Variation Mode Data */
		wcf_set_variation_mode_option();

		/* Repeateble Product */
		wcf_add_repeatable_product();

		/* Repeateble Product */
		wcf_toggle_view_repeatable_fields();

		wcf_hide_discount_value_field();

		/* Custom Fields Show Hide */
		wcf_custom_fields_events();
		
		/* Remove Repeatable Product */
		wcf_remove_repeatable_product();
		
		/* Advance Style Fields Show Hide */
		wcf_advance_style_fields_events();

		/*sortable products*/
		wcf_products_sortable();
	});
})(jQuery);
