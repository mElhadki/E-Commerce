/**
 * AJAX Request Queue
 *
 * - add()
 * - remove()
 * - run()
 * - stop()
 *
 * @since 1.0.0
 */

var CartFlowsAjaxQueue = (function() {

	var requests = [];

	return {

		/**
		 * Add AJAX request
		 *
		 * @since 1.0.0
		 */
		add:  function(opt) {
		    requests.push(opt);
		},

		/**
		 * Remove AJAX request
		 *
		 * @since 1.0.0
		 */
		remove:  function(opt) {
		    if( jQuery.inArray(opt, requests) > -1 )
		        requests.splice($.inArray(opt, requests), 1);
		},

		/**
		 * Run / Process AJAX request
		 *
		 * @since 1.0.0
		 */
		run: function() {
		    var self = this,
		        oriSuc;

		    if( requests.length ) {
		        oriSuc = requests[0].complete;

		        requests[0].complete = function() {
		             if( typeof(oriSuc) === 'function' ) oriSuc();
		             requests.shift();
		             self.run.apply(self, []);
		        };

		        jQuery.ajax(requests[0]);

		    } else {

		      self.tid = setTimeout(function() {
		         self.run.apply(self, []);
		      }, 1000);
		    }
		},

		/**
		 * Stop AJAX request
		 *
		 * @since 1.0.0
		 */
		stop:  function() {

		    requests = [];
		    clearTimeout(this.tid);
		}
	};

}());


(function($){

	CartFlowsImport = {

		doc     : $( document ),
		wrap    : $( '.wcf-flow-steps-data-wrap' ),
		inner   : $( '.wcf-flow-steps-data-wrap-importer' ),
		post_id : $( '#post_ID' ).val(),

		_ref			: null,

		_api_step_type : {},
		_api_params		: {},

		all_steps       : 0,
		remaining_steps : 0,
		remaining_install_plugins : 0,
		remaining_active_plugins : 0,
		woo_required_steps: [ 'checkout', 'upsell', 'downsell', 'thankyou', 'optin' ],
		step_order: [ 'landing', 'checkout', 'upsell', 'downsell', 'thankyou', 'optin' ],
		new_step_names: {
			'landing': 'Landing',
			'checkout': 'Checkout (Woo)',
			'upsell': 'Upsell (Woo)',
			'downsell': 'Downsell (Woo)',
			'thankyou': 'Thank You (Woo)',
			'optin' : 'Optin (Woo)'
		},

		/**
		 * Init
		 */
		init: function()
		{	

			this._bind();

			if( 'other' !== CartFlowsImportVars.default_page_builder ) {
				if( $('.post-type-cartflows_flow').hasClass('edit-php') ) {
					this._process_cache_remote_flows();
				}
				if( $('.post-type-cartflows_flow').hasClass('post-php') ) {
					this._process_cache_remote_steps();
				}
			}

			if( $('.post-type-cartflows_flow').hasClass('edit-php') && null !== this._getParamFromURL('add-new-flow') ) {
				this._render_remote_flows();
			}
			if( $('.post-type-cartflows_flow').hasClass('post-php') && null !== this._getParamFromURL('add-new-step') ) {
				this._render_remote_steps();
			}

	        if( this._getParamFromURL('highlight-step-id') ) {
	        	var selector = $('.wcf-step-wrap[data-id="'+this._getParamFromURL('highlight-step-id')+'"]');
	        	if( selector.length ) {
        		    $('html, body').animate({
				        scrollTop: selector.offset().top
				    }, 1500);
	        	}
	        }

	        if( $('.post-type-cartflows_flow').hasClass('edit-php') ) {
				var $product_screen = $( '.edit-php.post-type-cartflows_flow' ),
					$title_action   = $product_screen.find( '.page-title-action:first' );

				$title_action.after('<a href="'+CartFlowsImportVars.export_url+'" class="page-title-action">Export</a>' );
				$title_action.after('<a href="'+CartFlowsImportVars.import_url+'" class="page-title-action">Import</a>');
			}

		},

		/**
		 * Get URL param.
		 */
		_getParamFromURL: function(name, url)
		{
		    if (!url) url = window.location.href;
		    name = name.replace(/[\[\]]/g, "\\$&");
		    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
		        results = regex.exec(url);
		    if (!results) return null;
		    if (!results[2]) return '';
		    return decodeURIComponent(results[2].replace(/\+/g, " "));
		},

		/**
		 * Binds events
		 */
		_bind: function()
		{
			var self = CartFlowsImport;

			self.doc.on('click', '.wcf-install-plugin',	self._install_plugin);

			self.doc.on( 'cartflows-api-request-fail', self._api_request_failed );
			self.doc.on('click', '#wcf-get-started-steps a, .wcf-create-from-scratch-link',	self._toggle_ready_templates);
			self.doc.on('click', '.cartflows-flow-import-blank',	self._create_default_flow);
			self.doc.on('click', '#wcf-remote-flow-importer .wcf-page-builder-links a',	self._filterFlowPageBuilderClick);
			self.doc.on('click', '#wcf-remote-step-importer #wcf-categories .step-type-filter-links a', self._filterBlankStepCategoryClick );
			self.doc.on('change', '#wcf-remote-step-importer #wcf-scratch-steps-categories .step-type-filter-links', self._filterBlankStepCategoryChange );
			self.doc.on('click', '#wcf-get-started-steps', self._filterBlankStepCategoryChange );
			self.doc.on('click', '#wcf-remote-step-importer .wcf-page-builder-links a', self._filterStepPageBuilderClick );

			self.doc.on('click'                 , '.cartflows-step-import-blank:not(.get-pro)',	self._create_blank_step);
			self.doc.on('click'                 , '#wcf-remote-step-importer .cartflows-step-import',	self._process_import_step);
			self.doc.on('click'                 , '#wcf-remote-flow-importer .cartflows-step-import',	self._process_import_flow);

			self.doc.on('click'                 , '.cartflows-preview-flow-step',	self._preview_individual);

			self.doc.on( 'add_template_to_page-fail', self._add_template_to_page_fail);

			$( 'body' ).on('thickbox:iframe:loaded', 					self._previewLoaded );

			// Event's for API request.
			$( document ).on('keyup input', '#wcf-remote-step-importer .wcf-flow-search-input', 	self._remote_step_search );

			$( document ).on('click', '.actions a', 					self._previewResponsive );

			$( document ).on( 'click', '.page-title-action:first', self._render_remote_flows );
			$( document ).on( 'click', '.wcf-trigger-popup', self._render_remote_steps );

			$( document ).on( 'click', '.wcf-templates-popup-overlay', self._close_template_popup );
			$( document ).on( 'click', '.wcf-popup-close-wrap .close-icon', self._close_template_popup );

			$( document ).on('wp-plugin-install-success' , self._installSuccess);

			$( document ).on( 'click', '.wcf-activate-wc', self._installWc );
		},

		_install_plugin: function( event ) {
			event.preventDefault();

			var btn = $( this );

			if( btn.hasClass('updating-message') ) {
				return;
			}

			$('#wcf-remote-flow-importer').addClass('request-process');
			$('#wcf-remote-step-importer').addClass('request-process');

			btn.addClass('updating-message button');

			var page_builder_plugins = CartFlowsImportVars.required_plugins[ CartFlowsImportVars.default_page_builder ]['plugins'];
			
			$.each( page_builder_plugins, function( index, plugin ) {
				if( 'install' === plugin.status ) {
					CartFlowsImport.remaining_install_plugins++;
				}
				if( 'activate' === plugin.status ) {
					CartFlowsImport.remaining_active_plugins++;
				}
			});

			// Have any plugin for install?
			if( CartFlowsImport.remaining_install_plugins ) {
				CartFlowsImport._install_all_plugins();
			} else if( CartFlowsImport.remaining_active_plugins ) {
				CartFlowsImport._activate_all_plugins();
			} else {
				if( $('#wcf-remote-flow-importer').length ) {
					CartFlowsImport._cache_remote_flows();
				} else if( $('#wcf-remote-step-importer').length ) {
					CartFlowsImport._cache_remote_steps();
				}
			}
		},

		_install_all_plugins: function() {

			var page_builder_plugins = CartFlowsImportVars.required_plugins[ CartFlowsImportVars.default_page_builder ]['plugins'];

			$.each( page_builder_plugins, function( index, plugin ) {
				if( 'install' === plugin.status ) {
					// Add each plugin activate request in Ajax queue.
					// @see wp-admin/js/updates.js
					wp.updates.queue.push( {
						action: 'install-plugin', // Required action.
						data:   {
							slug: plugin.slug
						}
					} );
				}
			});

			// Required to set queue.
			wp.updates.queueChecker();
		},

		_activate_all_plugins: function() {

			if( ! CartFlowsImport.remaining_active_plugins && ! CartFlowsImport.remaining_install_plugins ) {
				if( $('#wcf-remote-flow-importer').length ) {
					CartFlowsImport._cache_remote_flows();
				} else if( $('#wcf-remote-step-importer').length ) {
					CartFlowsImport._cache_remote_steps();
				}
			} else {
				var page_builder_plugins = CartFlowsImportVars.required_plugins[ CartFlowsImportVars.default_page_builder ]['plugins'];

				// Activate ALl Plugins.
				CartFlowsAjaxQueue.stop();
				CartFlowsAjaxQueue.run();

				$.each( page_builder_plugins, function( index, plugin ) {
					if( 'activate' === plugin.status ) {

						CartFlowsAjaxQueue.add({
							url: CartFlowsImportVars.ajaxurl,
							type: 'POST',
							data: {
								action      : 'cartflows_activate_plugin',
								plugin_init : plugin.init,
								security    : CartFlowsImportVars.cartflows_activate_plugin_nonce
							},
							success: function( result ) {

								CartFlowsImport.remaining_active_plugins--;

								if( ! CartFlowsImport.remaining_active_plugins && ! CartFlowsImport.remaining_install_plugins ) {
									if( $('#wcf-remote-flow-importer').length ) {
										CartFlowsImport._cache_remote_flows();
									} else if( $('#wcf-remote-step-importer').length ) {
										CartFlowsImport._cache_remote_steps();
									}
								}

							}
						});
					}
				});
			}

		},

		_installSuccess: function( event, response ) {

			event.preventDefault();

			if( 'no' === CartFlowsImportVars.is_wc_activated ) {
				CartFlowsImport._activateWc();
			}


			var page_builder_plugins = CartFlowsImportVars.required_plugins[ CartFlowsImportVars.default_page_builder ]['plugins'];

			$.each( page_builder_plugins, function( index, plugin ) {
				if( 'install' === plugin.status && response.slug === plugin.slug ) {

					$.ajax({
						url  : ajaxurl,
						type : 'POST',
						data : {
							action      : 'cartflows_activate_plugin',
							plugin_init : plugin.init,
							security    : CartFlowsImportVars.cartflows_activate_plugin_nonce
						},
					})
					.done(function( request, status, XHR )
					{
						CartFlowsImport.remaining_install_plugins--;

						if( ! CartFlowsImport.remaining_install_plugins ) {
							CartFlowsImport._activate_all_plugins();
						}
					});
				}
            });

		},

		_api_request_failed: function( event, data, jqXHR, textStatus ) {
			if( 'error' == textStatus ) {
				if( ! $('#wcf-remote-content-failed').length ) {
					$('#wcf-ready-templates').html( wp.template('cartflows-website-unreachable') );
				}
			}
		},

		_toggle_ready_templates: function( event ) {
			event.preventDefault();

			var slug = $(this).data('slug') || '';

			$('#wcf-get-started-steps').find('a').removeClass('current');
			$('#wcf-get-started-steps').find('a[data-slug="'+slug+'"]').addClass('current');

			if( 'canvas' == slug ) {
				$('#wcf-ready-templates').hide();
				$('#wcf-start-from-scratch').show();
			} else {
				$('#wcf-ready-templates').show();
				$('#wcf-start-from-scratch').hide();
			}

			if( ! $('.wcf-page-builder-notice').length && $('#wcf-remote-step-importer').length ) {
				CartFlowsImport._showSteps();
			}
		},

		_switch_step_tab: function( event ) {

			event.preventDefault();//stop browser to take action for clicked anchor
						
			//get displaying tab content jQuery selector
			var active_tab_selector = $('.wcf-tab > li.active > a').attr('href');					
						
			//find actived navigation and remove 'active' css
			var actived_nav = $('.wcf-tab > li.active');
			actived_nav.removeClass('active');
						
			//add 'active' css into clicked navigation
			$(this).parents('li').addClass('active');
						
			//hide displaying tab content
			$(active_tab_selector).removeClass('active');
			$(active_tab_selector).addClass('hide');
						
			//show target tab content
			var target_tab_selector = $(this).attr('href');
			$(target_tab_selector).removeClass('hide');
			$(target_tab_selector).addClass('active');
	    },

		/**
		 * Search Site.
		 *
		 * Prepare Before API Request:
		 * - Remove Inline Height
		 * - Added 'hide-me' class to hide the 'No more sites!' string.
		 * - Added 'loading-content' for body.
		 * - Show spinner.
		 */
		_remote_step_search: function( event ) {

			event.preventDefault();//stop browser to take action for clicked anchor

			// Remove all filter classes.
	        $('.step-type-filter-links').find('option').removeClass('current');
	        $('.step-type-filter-links').find('option:first-child').addClass('current');

			window.clearTimeout(CartFlowsImport._ref);
			CartFlowsImport._ref = window.setTimeout(function () {
				CartFlowsImport._ref = null;
				CartFlowsImport._showSteps();
	        }, 500);

		},

		/**
		 * Responsive On Click.
		 */
		_previewResponsive: function( event ) {

			event.preventDefault();

			var icon = $(this).find('.dashicons');

			var viewClass = icon.attr('data-view') || '';

			$('#TB_window').removeClass( 'desktop tablet mobile' );
			$('#TB_window').addClass( viewClass );

			$('.actions .dashicons').removeClass('active');
			icon.addClass('active');

			$('#TB_iframeContent').removeClass();
			$('#TB_iframeContent').addClass( viewClass );

		},

		/**
		 * On Filter Clicked
		 */
		_filterStepPageBuilderClick: function( event ) {
			event.preventDefault();

			$(this).parents('ul').find('a').removeClass('current');
			$(this).addClass('current');

			var step_type = $('.step-type-filter-links .current').data('slug') || '';

			if ( 'upsell' === step_type || 'downsell' === step_type ) {
				$( '.wcf-template-notice' ).show();

			}else{
				$( '.wcf-template-notice' ).hide();
			}

			$('.wcf-page-builder-notice').html( '' );

			$('#wcf-remote-step-list').html( '<span class="spinner is-active"></span>' );

			CartFlowsImport._showSteps();
		},

		_filterBlankStepCategoryClick: function( event ) {
			event.preventDefault();

			$('.wcf-page-builder-notice').html( '' );

			var val = $(this).data('group') || '';
			if( val ) {
				$('#wcf-scratch-steps-categories .step-type-filter-links').val( val );
				$('#wcf-scratch-steps-categories .step-type-filter-links option').removeClass('current');
				$('#wcf-scratch-steps-categories .step-type-filter-links option[data-group="'+val+'"]').addClass('current');
			}

			$('.step-type-filter-links').find('a').removeClass('current');
			$(this).addClass('current');

			$step_type = $(this).data('slug');

			if ( 'upsell' === $step_type || 'downsell' === $step_type ) {
				$( '.wcf-template-notice' ).show();

			}else{
				$( '.wcf-template-notice' ).hide();
			}

			if( '' == CartFlowsImportVars._is_pro_active && ( 'upsell' == $step_type || 'downsell' == $step_type ) ) {
				$('.cartflows-step-import-blank').text( 'Get Pro' );
				$('.cartflows-step-import-blank').attr( 'href', CartFlowsImportVars.domain_url );
				$('.cartflows-step-import-blank').attr( 'target', '_blank' );
				$('.cartflows-step-import-blank').addClass('get-pro');
				// 	// $('#wcf-remote-step-list').find('.cartflows-step-import-blank').attr( 'class', 'button button-primary' );
			} else {
				$('.cartflows-step-import-blank').text( 'Create Step' );
				$('.cartflows-step-import-blank').removeClass( 'get-pro' );
				$('.cartflows-step-import-blank').removeAttr( 'target' );
			}

			$('#wcf-remote-step-list').html( '<span class="spinner is-active"></span>' );
			CartFlowsImport._showSteps();
		},

		_filterBlankStepCategoryChange: function( event ) {

			event.preventDefault();
			$(".wcf-notice-wrap").remove();
			$('.cartflows-step-import-blank').css('pointer-events', 'auto').removeClass('disabled');

			var val = $('.step-type-filter-links').find('option:selected').val() || '';
			if( val ) {
				$('.step-type-filter-links').val( val );
				$('.step-type-filter-links').find('a').removeClass('current');
				$('.step-type-filter-links').find('a[data-group="'+val+'"]').addClass('current');
			}

			$('.step-type-filter-links').find('option').removeClass('current');
			$('.step-type-filter-links').find('option:selected').addClass('current');

			$step_type = $('.step-type-filter-links').find('option:selected').data('slug');

			if ( ( 'no' === CartFlowsImportVars.is_wc_installed || 'no' === CartFlowsImportVars.is_wc_activated ) && ( 'upsell' === $step_type || 'downsell' === $step_type || 'checkout' === $step_type || 'thankyou' === $step_type)) {
				$(".cartflows-step-import-blank").after("<p class='wcf-notice-wrap' style='text-align: center'>You need WooCommerce plugin installed and actived to use this product flow. <br/><br/> <a href='#' class='wcf-activate-wc button-secondary'> Click here to install and activate WooCommerce </a> </p>");
				$('.cartflows-step-import-blank').addClass('disabled').css('pointer-events', 'none');
				return;
			}


			if ( 'upsell' === $step_type || 'downsell' === $step_type ) {
				$( '.wcf-template-notice' ).show();
			}else{
				$( '.wcf-template-notice' ).hide();
			}

			if( '' == CartFlowsImportVars._is_pro_active && ( 'upsell' == $step_type || 'downsell' == $step_type ) ) {
				$('.cartflows-step-import-blank').text( 'Get Pro' );
				$('.cartflows-step-import-blank').attr( 'href', CartFlowsImportVars.domain_url );
				$('.cartflows-step-import-blank').attr( 'target', '_blank' );
				$('.cartflows-step-import-blank').addClass('get-pro');
				// 	// $('#wcf-remote-step-list').find('.cartflows-step-import-blank').attr( 'class', 'button button-primary' );
			} else {
				$('.cartflows-step-import-blank').text( 'Create Step' );
				$('.cartflows-step-import-blank').removeClass( 'get-pro' );
				$('.cartflows-step-import-blank').removeAttr( 'target' );
			}

			if( ! $('.wcf-page-builder-notice').length && $('#wcf-remote-step-importer').length ) {
				$('#wcf-remote-step-list').html( '<span class="spinner is-active"></span>' );
				CartFlowsImport._showSteps();
			}
		},

		_showSteps: function() {

			// Add Params for API request.
			var api_params = {
				licence_args : CartFlowsImportVars.licence_args,
				per_page     : 100,
				_fields      : CartFlowsImportVars.step_fields.toString(),
			};

			var type 		= $('#wcf-categories .step-type-filter-links').find('.current').data('group') || '';
			var step_type 	= $('#wcf-categories .step-type-filter-links').find('.current').data('slug') || '';

			if( '' !== type && 'all' !== type ) {
				api_params[ CartFlowsImportVars.step_type ] = type;
			}

			// Page Builder.
			var type      = $('#wcf-page-builders .wcf-page-builder-links').find('.current').data('group') || '';
			var step_type = $('#wcf-page-builders .wcf-page-builder-links').find('.current').data('slug') || '';
			var title     = $('#wcf-page-builders .wcf-page-builder-links').find('.current').data('title') || 'Page Builder';
			if( '' !== type && 'all' !== type ) {
				api_params[ CartFlowsImportVars.step_page_builder ] = type;
			}

			// API Request.
			var api_post = {
				remote_slug : CartFlowsImportVars.step,
				slug        : CartFlowsImportVars.step + '?' + decodeURIComponent( $.param( api_params ) ),
			};

			CartFlowsAPI._api_request( api_post, function( data ) {

				data.current_step_type = step_type;

				if ('yes' !== CartFlowsImportVars.is_wc_activated) {
					$.each(data.items, function (index, value) {
						data.items[index].woo_required = false;
						if ($.inArray(value.step_type.slug, CartFlowsImport.woo_required_steps) >= 0) {
							data.items[index].woo_required = true;
							return;
						}
					});
				}

				var template = wp.template('cartflows-steps-list');
				if( parseInt( data.items_count ) ) {
					$('#wcf-remote-step-list').html( template( data ) );
				} else {
					$('#wcf-remote-step-list').html( wp.template('cartflows-no-steps') );
					$('.cartflows-no-steps').find( '.description' ).html( 'We are working on ready templates designed with '+title+'.<br/>Meanwhile you can <a href="#" data-slug="canvas" class="wcf-create-from-scratch-link">create your own designs</a> easily.' );
				}

				$('.wcf-page-builder-notice').remove();
				$('#wcf-remote-step-importer').removeClass('request-process');
			} );
		},

		_apiAddParam_per_page: function() {
			CartFlowsImport._api_params['per_page'] = 100;
		},

		// Add Params for API request.
		_apiAddParam_licence_args: function() {
			CartFlowsImport._api_params['licence_args'] = CartFlowsImportVars.licence_args;
		},

		// Add 'search'
		_apiAddParam_search: function() {
			var search_val = $('.wcf-flow-search-input').val() || '';
			if( '' !== search_val ) {
				CartFlowsImport._api_params['search'] = search_val;
			}
		},

		_close_popup: function() {
			$('#cartflows-steps').fadeOut();
			$('body').removeClass('cartflows-popup-is-open');
		},

		_post_auto_save: function() {
			var post_title       = $( '#title' );
			var post_prompt_text = $( '#title-prompt-text' );
			var self = CartFlowsImport;

			if ( ! post_title.val() ) {
				post_title.val( 'CartFlows #' + self.post_id );
				if( post_prompt_text.length ) {
					post_prompt_text.remove();
				}
			}

			if ( wp.autosave ) {
				wp.autosave.server.triggerSave();
			}
		},

		_process_cache_remote_flows: function() {

			var page_builder_plugins = CartFlowsImportVars.required_plugins[ CartFlowsImportVars.default_page_builder ]['plugins'];

			var anyInactive = true;

			$.each( page_builder_plugins, function( index, plugin ) {

				if( anyInactive ) {
					if( 'install' === plugin.status || 'activate' === plugin.status ) {
						anyInactive = false;
					}
				}
			});

			if( false === anyInactive ) {
				$('.wcf-page-builder-notice').html( wp.template('cartflows-page-builder-notice') );
				$('#wcf-remote-flow-list').find( '.spinner' ).remove();
			} else {
				CartFlowsImport._cache_remote_flows();
			}
		},

		_cache_remote_flows: function() {

			var self = CartFlowsImport;

			// Add Params for API request.
			var api_params = {
				search       : CartFlowsImportVars.default_page_builder,
				licence_args : CartFlowsImportVars.licence_args,
				hide_empty   : false,
				_fields      : CartFlowsImportVars.flow_page_builder_fields.toString(),
			};

			// API Request.
			var api_post = {
				remote_slug   : CartFlowsImportVars.flow_page_builder,
				slug          : CartFlowsImportVars.flow_page_builder + '?'+decodeURIComponent( $.param( api_params ) ),
				wrapper_class : 'wcf-page-builder-links filter-links',
				show_all      : false,
			};

			CartFlowsAPI._api_request( api_post, function( data ) {

				var template = wp.template('cartflows-term-filters');
				$('#wcf-page-builders').html(template( data ));
				$('#wcf-page-builders').find('li:first a').addClass('current');

				// Step 2: Categories.
				// Add Params for API request.
				var api_params = {
					licence_args : CartFlowsImportVars.licence_args,
					hide_empty   : true,
					_fields      : CartFlowsImportVars.flow_type_fields.toString(),
				};

				// API Request.
				var api_post = {
					remote_slug   : CartFlowsImportVars.flow_type,
					slug          : CartFlowsImportVars.flow_type + '?'+decodeURIComponent( $.param( api_params ) ),
					wrapper_class : 'flow-type-filter-links filter-links',
					show_all      : false,
				};

				CartFlowsAPI._api_request( api_post, function( data ) {
					var template = wp.template('cartflows-term-filters');
					$('#wcf-categories').html(template( data ));
					$('#wcf-categories').find('li:first a').addClass('current');

					// Step 3: Flows.
					CartFlowsImport._showFlows();
				} );
			} );
		},

		_render_remote_flows: function( event ) {

			if( event ) {
				event.preventDefault();
			}

			$("#wcf-remote-flow-importer").addClass('open');

			$("html").addClass('wcf-popup-open');

		},

		_process_cache_remote_steps: function() {

			var page_builder_plugins = CartFlowsImportVars.required_plugins[ CartFlowsImportVars.default_page_builder ]['plugins'];
			

			var anyInactive = true;

			$.each( page_builder_plugins, function( index, plugin ) {

				if( anyInactive ) {
					if( 'install' === plugin.status || 'activate' === plugin.status ) {
						anyInactive = false;
					}
				}
			});

			if( false === anyInactive ) {
				$('.wcf-page-builder-notice').html( wp.template('cartflows-page-builder-notice') );
				$('#wcf-remote-step-list').find( '.spinner' ).remove();
			} else {
				CartFlowsImport._cache_remote_steps();
			}

		},

		mapOrder: function (array, order, key) {

			array.sort(function (a, b) {
				var A = a[key], B = b[key];

				if ( order.indexOf(A) === -1 ) {
					return 0;
				}

				if (order.indexOf(A) > order.indexOf(B)) {
					return 1;
				} else {
					return -1;
				}

			});
			return array;
		},

		_cache_remote_steps: function() {

			var self = CartFlowsImport;

			// Disable the button until caching the data.
			$( 'html' ).addClass('wcf-steps-loading');

			// Add Params for API request.
			var api_params = {
				search       : CartFlowsImportVars.default_page_builder,
				licence_args : CartFlowsImportVars.licence_args,
				hide_empty   : false,
				_fields      : CartFlowsImportVars.step_page_builder_fields.toString(),
			};

			// API Request.
			var api_post = {
				remote_slug   : CartFlowsImportVars.step_page_builder,
				slug          : CartFlowsImportVars.step_page_builder + '?'+decodeURIComponent( $.param( api_params ) ),
				wrapper_class : 'wcf-page-builder-links filter-links',
				show_all      : false,
			};

			CartFlowsAPI._api_request( api_post, function( data ) {
				var template = wp.template('cartflows-term-filters');
				$('#wcf-page-builders').html(template( data ));
				$('#wcf-page-builders').find('li:first a').addClass('current');
			} );

			// Add Params for API request.
			var api_params = {
				licence_args : CartFlowsImportVars.licence_args,
				_fields      : CartFlowsImportVars.step_type_fields.toString(),
			};

			// API Request.
			var api_post = {
				remote_slug   : CartFlowsImportVars.step_type,
				slug          : CartFlowsImportVars.step_type + '?'+decodeURIComponent( $.param( api_params ) ),
				wrapper_class : 'step-type-filter-links filter-links',
				show_all      : false,
			};

			CartFlowsAPI._api_request( api_post, function( data ) {

				data.items = CartFlowsImport.mapOrder( data.items, CartFlowsImport.step_order, 'slug' );
				var step_type_response_data = data;
				var step_types_count        = data.items_count;
				
				// Send other request for JS caching.
				if( data.items ) {
					for ( key in data.items ) {

						// Rename Step names on UI.
						data.items[ key ].name = CartFlowsImport.new_step_names[ data.items[ key ].slug ];

						// Add Params for API request.
						var api_params = {
							licence_args : CartFlowsImportVars.licence_args,
							per_page     : 100,
							_fields      : CartFlowsImportVars.step_fields.toString(),
						};

						api_params[ CartFlowsImportVars.step_type ] = data.items[ key ].id;

						// API Request.
						var api_post = {
							remote_slug : CartFlowsImportVars.step,
							slug        : CartFlowsImportVars.step + '?' + decodeURIComponent( $.param( api_params ) ),
						};

						CartFlowsAPI._api_request( api_post, function( data ) {
							var template = wp.template('cartflows-steps-list');
							if( parseInt( data.items_count ) ) {
								$('#wcf-remote-step-list').html( template( data ) );
							} else {
								$('#wcf-remote-step-list').html( wp.template('cartflows-no-steps') );
							}

							step_types_count--;

							if( 0 == step_types_count ) {

								var template_dropdown = wp.template('cartflows-term-filters-dropdown');
								var template          = wp.template('cartflows-term-filters');
								$('#wcf-categories').html(template( step_type_response_data ));
								$('#wcf-scratch-steps-categories').html(template_dropdown( step_type_response_data ));
								$('#wcf-scratch-steps-categories').find('option:first').addClass('current');
								$('#wcf-categories').find('li a[data-slug=landing]').addClass('current');

								$('.wcf-page-builder-notice').remove();
								$('#wcf-remote-content').find( '.spinner' ).remove();

								CartFlowsImport._showSteps();

								$( 'html' ).removeClass('wcf-steps-loading');
							}
						});
					}
				}
			} );
		},

		_render_remote_steps: function( event ) {
			if( event ) {
				event.preventDefault();
			}

			$("#wcf-remote-step-importer").addClass('open');

			$("html").addClass('wcf-popup-open');
		},

		_categorize_data: function( items ) {
				
			var categorised_data = [];

			$.each( items, function( index, value ) {

				var step_type = value.step_type.slug;

				if( 'undefined' == typeof categorised_data[step_type] ) {
					categorised_data[step_type] = [];
				}
					
				categorised_data[step_type].push( value );

			});

			return categorised_data;

		},
		
		_close_template_popup: function( event ) {
			if ( $( event.target ).hasClass('wcf-templates-popup-overlay') 
			 || $( event.target ).hasClass('close-icon') ) {

				// New step creation/importing is in process..
			 	if( ! $( '.wcf-templates-popup-overlay' ).hasClass('request-process') ) {
					$("html").removeClass('wcf-popup-open');
					$(".wcf-templates-popup-overlay").removeClass( 'open' );
			 	}
			}
		},

		_ajax: function( data, callback, trigger ) {

			var self = CartFlowsImport;

			$.ajax({
				url  : ajaxurl,
				type : 'POST',
				data : data,
			})
			.done(function( request, status, XHR )
			{
				var data = {
					request : request,
					status  : status,
					XHR     : XHR,
				};

				if( callback && typeof callback == "function"){
					callback( data );
				}

				self.doc.trigger( trigger + '-done', [request, status, XHR] );
			})
			.fail(function( jqXHR, textStatus )
			{
				self.doc.trigger( trigger + '-fail', [jqXHR, textStatus] );
			})
			.always(function()
			{
				self.doc.trigger( trigger + '-always' );
			});

		},

		_empty: function(data)
		{
		  if(typeof(data) == 'number' || typeof(data) == 'boolean')
		  { 
		    return false; 
		  }
		  if(typeof(data) == 'undefined' || data === null)
		  {
		    return true; 
		  }
		  if(typeof(data.length) != 'undefined')
		  {
		    return data.length == 0;
		  }
		  var count = 0;
		  for(var i in data)
		  {
		    if(data.hasOwnProperty(i))
		    {
		      count ++;
		    }
		  }
		  return count == 0;
		},

		_preview_individual: function()
		{
			var id    = $(this).data('id') || '';
			var href  = $(this).data('href') || '';

			// Active.
			$('.cartflows-preview-flow-step').removeClass('active');
			$('.cartflows-preview-flow-step[data-id="'+id+'"]').addClass('active');

			$('#TB_window').addClass('cartflows-thickbox-loading');

			$('#TB_iframeContent').removeAttr('onload');
			$('#TB_iframeContent').removeAttr('src');
			$('#TB_iframeContent').attr('src', href );
			$('#TB_iframeContent').attr('onload', 'CartFlowsImport.showIframe()' )
		},
		showIframe: function() {
			$("#TB_load").remove();
			$("#TB_window").css({'visibility':'visible'});
			// $("#TB_window").css({'visibility':'visible'}).trigger( 'thickbox:iframe:loaded' );
			$('#TB_window').removeClass('cartflows-thickbox-loading');
		},

		/**
		 * Remove thickbox loading class
		 * 
		 * @param  object event Event object.
		 * @return void.
		 */
		_previewLoaded: function( event ) {
			event.preventDefault();
			$('#TB_window').removeClass('cartflows-thickbox-loading');
		},

		_filterFlowPageBuilderClick: function( event ) {
			event.preventDefault();

			$(this).parents('ul').find('a').removeClass('current');
			$(this).addClass('current');

			$('.wcf-page-builder-notice').html( '' );

			$('#wcf-remote-flow-list').html( '<span class="spinner is-active"></span>' );

			CartFlowsImport._showFlows();
		},

		_showFlows: function() {
			// Add Params for API request.
			var api_params = {
				licence_args : CartFlowsImportVars.licence_args,
				_fields      : CartFlowsImportVars.flow_fields.toString(),
				per_page    : 100
			};

			// Page Builder.
			var type      = $('#wcf-page-builders .wcf-page-builder-links').find('.current').data('group') || '';
			var step_type = $('#wcf-page-builders .wcf-page-builder-links').find('.current').data('slug') || '';
			var title     = $('#wcf-page-builders .wcf-page-builder-links').find('.current').data('title') || 'Page Builder';
			if( '' !== type && 'all' !== type ) {
				api_params[ CartFlowsImportVars.flow_page_builder ] = type;
			}

			// API Request.
			var api_post = {
				remote_slug : CartFlowsImportVars.flow,
				slug        : CartFlowsImportVars.flow + '?' + decodeURIComponent( $.param( api_params ) ),
			};

			CartFlowsAPI._api_request( api_post, function( data ) {

				if ('yes' !== CartFlowsImportVars.is_wc_activated) {
					$.each(data.items, function (index, value) {
						data.items[index].woo_required = false;
						$.each(value.flow_steps, function (key, value) {
							if ($.inArray(value.type, CartFlowsImport.woo_required_steps) >= 0) {
								data.items[index].woo_required = true;
								return;
							}
						});
					});
				}

				var template = wp.template('cartflows-flows-list');
				if( parseInt( data.items_count ) ) {
					$('#wcf-remote-flow-list').html( template( data ) );
				} else {
					$('#wcf-remote-flow-list').html( wp.template('cartflows-no-flows') );
					// $('.cartflows-no-flows').find( '.description' ).html( 'We are working on ready templates designed with '+title+'.<br/>Meanwhile you can <a href="#" data-slug="canvas" class="wcf-create-from-scratch-link">create your own designs</a> easily.' );
				}

				$('.wcf-page-builder-notice').remove();
				$('#wcf-remote-flow-importer').removeClass('request-process');
			} );
		},

		_create_default_flow: function( event )
		{
			event.preventDefault();
			var self = CartFlowsImport;
			var btn = $( this );

			if( btn.hasClass('updating-message') ) {
				return;
			}

			$('#wcf-remote-flow-importer').addClass('request-process');

			btn.addClass('updating-message').text( 'Creating Flow..' );
			btn.parents('.template').addClass('importing');

			var data = {
				action : 'cartflows_default_flow',
				security: cartflows_admin.cf_default_flow_nonce
			};

			// Import Template AJAX.
			self._ajax( data, function( data ) {

				if( data.request.success ) {

					var flow_id = data.request.data;

					// Created.
					setTimeout(function() {
						btn.removeClass('updating-message').text('Flow Created! Redirecting..');
						window.location = CartFlowsImportVars.admin_url + 'post.php?post='+flow_id+'&action=edit';
					}, 3000);

				}
			});

		},

		_activate_plugin: function( plugin_init ) {

			var self = CartFlowsImport;

			var data = {
				action      : 'cartflows_activate_plugin',
				plugin_init : plugin_init,
				security    : CartFlowsImportVars.cartflows_activate_plugin_nonce
			};

			self._ajax( data, function( data ) {
			});
		},

		_process_import_flow: function( event )
		{
			event.preventDefault();

			var btn = $( this );

			if( btn.hasClass('updating-message') ) {
				return;
			}

			$('#wcf-remote-flow-importer').addClass('request-process');

			var self = CartFlowsImport;

			btn.text( 'Creating Flow..' );

			btn.addClass('updating-message');
			btn.parents('.template').addClass('importing');

			var flow_steps_string = btn.data('flow-steps') || '';
			var flow_steps = ( '' !== flow_steps_string ) ? JSON.parse("[" + flow_steps_string + "]") : [];

			var data = {
				action : 'cartflows_create_flow',
				security: cartflows_admin.cf_create_flow_nonce
			};

			// Import Template AJAX.
			self._ajax( data, function( data ) {

				if( data.request.success ) {

					var flow_id = data.request.data;

					if( flow_steps ) {

						// Activate ALl Plugins.
						CartFlowsAjaxQueue.stop();
						CartFlowsAjaxQueue.run();

						CartFlowsImport.all_steps = flow_steps.length;

						// Importing.
						btn.addClass('updating-message').text('Importing Step 1 of ' + CartFlowsImport.all_steps );

						$.each(flow_steps, function(index, template_id) {

							CartFlowsAjaxQueue.add({
								url: CartFlowsImportVars.ajaxurl,
								type: 'POST',
								data: {
									action      : 'cartflows_import_flow_step',
									flow_id     : flow_id,
									template_id : template_id,
									security    : cartflows_admin.cf_import_flow_step_nonce
								},
								success: function( result ) {
									CartFlowsImport.remaining_steps = CartFlowsImport.remaining_steps + 1;

									if( CartFlowsImport.remaining_steps === CartFlowsImport.all_steps ) {

										// Imported.
										btn.addClass('updating-message').text('Imported Step ' + CartFlowsImport.remaining_steps  + ' of ' + CartFlowsImport.all_steps );

										setTimeout(function() {
											btn.removeClass('updating-message').text('All Imported! Redirecting..');
											window.location = CartFlowsImportVars.admin_url + 'post.php?post='+flow_id+'&action=edit';
										}, 3000);

									} else {
										// Importing.
										btn.addClass('updating-message').text('Importing Step ' + CartFlowsImport.remaining_steps  + ' of ' + CartFlowsImport.all_steps );
									}
								},
								error: function( result ) {
									CartFlowsImport.remaining_steps = CartFlowsImport.remaining_steps + 1;

									template = btn.parents('.template.importing');
									template.find('.preview')
										.addClass('notice notice-warning')
										.removeClass('preview')
										.text( result.statusText );

									if( CartFlowsImport.remaining_steps === CartFlowsImport.all_steps ) {

										// Imported.
										btn.addClass('updating-message').text('Failed ' + CartFlowsImport.remaining_steps  + ' of ' + CartFlowsImport.all_steps );

										setTimeout(function() {
											btn.removeClass('updating-message button-primary').addClass('disabled');
											// location.reload();
										}, 3000);

									} else {
										// Importing.
										btn.addClass('updating-message').text('Failed ' + CartFlowsImport.remaining_steps  + ' of ' + CartFlowsImport.all_steps );
									}
								},
								fail: function( result ) {
									
									CartFlowsImport.remaining_steps = CartFlowsImport.remaining_steps + 1;

									if( CartFlowsImport.remaining_steps === CartFlowsImport.all_steps ) {

										// Imported.
										btn.addClass('updating-message').text('Imported ' + CartFlowsImport.remaining_steps  + ' of ' + CartFlowsImport.all_steps );

										setTimeout(function() {
											btn.removeClass('updating-message').text('All Step Imported! Reloading..');
											location.reload();
										}, 3000);

									} else {
										// Importing.
										btn.addClass('updating-message').text('Importing ' + CartFlowsImport.remaining_steps  + ' of ' + CartFlowsImport.all_steps );
									}
								}
							});
						});
					}
				}
			});
		},

		_handle_error: function( response ) 
		{
			var btn = $( '.cartflows-step-import.updating-message' );

			btn.addClass('updating-message').text( response.errorMessage );
		},

		_create_blank_step: function( event )
		{
			event.preventDefault();

			var btn    	   		  = $(this),
				self              = CartFlowsImport,
				flow_id           = $("#post_ID").val(),
				step_slug         = $('#wcf-scratch-steps-categories .step-type-filter-links .current').data('slug') || '',
				step_title        = $('#wcf-scratch-steps-categories .step-type-filter-links .current').data('title') || '',
				step_type         = step_slug,
				step_count        = $('.wcf-step-wrap[data-term-slug="'+step_slug+'"]').length || 1,
				step_custom_title = step_title + ' ' + ( parseInt( step_count ) + 1 );
				all_step_type = [ 'landing', 'checkout', 'thankyou' ];

			$('#wcf-start-from-scratch .wcf-notice-wrap ').remove();

			if( '' === step_type ) {
				$('#wcf-start-from-scratch .inner').append( '<div class="wcf-notice-wrap"><div class="notice notice-info"><p>Please select the step type.</p></div></div>' );
				return;
			}

			if ( ! CartFlowsImportVars._is_pro_active && ( 'upsell' === step_type || 'downsell' === step_type )) {
				return;
			}

			btn.parents('.template').addClass('importing');
			
			if ( ! CartFlowsImportVars._is_pro_active ) {
				
				if ( jQuery.inArray( step_type, all_step_type ) != '-1' ) {
					var current_steps = $( '.wcf-step-wrap[data-term-slug="'+step_type+'"]');

					if ( 0 < current_steps.length ) {

						var parent_template = btn.parents('.template.importing');

						btn.removeClass('importing updating-message')
							.text('Import Failed!');

						$('#wcf-start-from-scratch .inner').append( '<div class="wcf-notice-wrap"><div class="notice notice-warning"><p>Upgrade to Pro for adding more than one '+step_type.charAt(0).toUpperCase()+step_type.slice(1)+' step.</p></div></div>' );

						return;
					}
				}
			}

			$('#wcf-remote-step-importer').addClass('request-process');

			$('.cartflows-step-import').addClass('disabled');
			btn.addClass('importing updating-message').text('Creating..');

			// Process Import Page.
			if( $( 'body' ).hasClass( 'post-type-cartflows_flow' ) ) {
				var data = {
					action     : 'cartflows_step_create_blank',
					flow_id    : flow_id,
					step_type  : step_type,
					step_title : step_custom_title,
					security   : cartflows_admin.cf_step_create_blank_nonce
				};

				// Import Template AJAX.
				self._ajax( data, function( data ) {
					
					var self     = CartFlowsImport;
					var template = btn.parents('.template.importing');

					if( data.request.success ) {
						btn.text('Created. Reloading..');
						setTimeout(function() {
							window.location.href = window.location.href + '&highlight-step-id=' + data.request.data;
						}, 3000);

					} else {

						btn.removeClass('importing updating-message')
							.text('Creating Failed!');

						$('#wcf-remote-step-importer').removeClass('request-process');

						template.find('.cartflows-step-preview').append( "<div class='preview'></div>" );

						template.find('.preview')
							.addClass('notice notice-warning')
							.removeClass('preview')
							.text( data.request.data );
					}
				} );
			}
		},

		_process_import_step: function( event ) {

			event.preventDefault();

			var btn               = $( this ),
				step_slug         = btn.data('slug') || '',
				step_count        = $('.wcf-step-wrap[data-term-slug="'+step_slug+'"]').length || 1;
				step_title        = btn.data('title') || '',
				step_custom_title = step_title + ' ' + ( parseInt( step_count ) + 1 ),
				self 		      = CartFlowsImport;
				all_step_slug = [ 'landing', 'checkout', 'thankyou' ];

			if ( ! CartFlowsImportVars._is_pro_active ) {
				
				if ( jQuery.inArray( step_slug, all_step_slug ) != '-1' ) {

					var current_step_slug = $( '.wcf-step-wrap[data-term-slug="'+step_slug+'"]');

					if ( 0 < current_step_slug.length ) {

						var parent_template = btn.parents('.template');

						btn.removeClass('importing updating-message')
							.text('Import Failed!');

						parent_template.find('.preview').hide();
						var notice_wrap = parent_template.find('#wcf_create_notice')
							.show();

						notice_wrap.find('a')
							.addClass('notice notice-warning ')
							.text ( 'Upgrade to Pro for adding more than one '+step_slug.charAt(0).toUpperCase()+step_slug.slice(1)+' step' );

						$('#wcf-remote-step-importer').removeClass('request-process');
						return;
					}
				}
			}

			if( btn.hasClass('updating-message') ) {
				return;
			}

			$('#wcf-remote-step-importer').addClass('request-process');

			var self = CartFlowsImport;

			btn.addClass('updating-message');
			btn.parents('.template').addClass('importing');
			
			// var plugin_slug = '';
			// plugin = response.slug;

			// switch ( plugin ) {
			// 	case 'elementor':
			// 		plugin_slug = 'elementor/elementor.php';
			// 	break;
			// 	case 'beaver-builder-lite-version':
			// 		plugin_slug = 'beaver-builder-lite-version/fl-builder.php';
			// 	break;
			// }

			// self._activate_plugin( plugin_slug );

			var template_id = btn.data('template-id') || '';
			var flow_id     = $("#post_ID").val();
			var step_type   = step_slug; 
			var self        = CartFlowsImport;

			$('.cartflows-step-import-blank').addClass('disabled');
			$('.cartflows-step-import').addClass('disabled');
			btn.addClass('importing updating-message').text('Importing..');

			// Process Import Page.
			if( $( 'body' ).hasClass( 'post-type-cartflows_flow' ) )
			{
				var data = {
					action      : 'cartflows_step_import',
					flow_id     : flow_id,
					template_id : template_id,
					step_title  : step_custom_title,
					step_type   : step_type,
					security    : cartflows_admin.cf_step_import_nonce   
				};

				// Import Template AJAX.
				self._ajax( data, function( data ) {


					var self     = CartFlowsImport;
					var template = btn.parents('.template.importing');

					if( data.request.success ) {
						btn.text('Imported. Reloading..');
						setTimeout(function() {
							window.location.href = window.location.href + '&highlight-step-id=' + data.request.data;

						}, 3000);

					} else {

						$( '.wcf-templates-popup-overlay' ).removeClass('request-process');

						btn.removeClass('importing updating-message')
							.text('Import Failed!');

						template.find('.preview')
							.addClass('notice notice-warning')
							.removeClass('preview')
							.text( data.request.data );
					}

				}, 'add_template_to_page' );
			}
		},

		_add_template_to_page_fail: function( event, jqXHR, textStatus ) {
			event.preventDefault();
			var self = CartFlowsImport,
				template = $('.wcf-flow-steps-data-wrap-importer .template.importing');

			template.find('.cartflows-step-import')
				.removeClass('importing updating-message')
				.text('Import Failed!');


			template.find('.preview')
				.addClass('notice notice-warning')
				.removeClass('preview')
				.text( jqXHR.responseText );
		},

		_activateWc: function( event ) {

			$.ajax({
				url  : ajaxurl,
				type : 'POST',
				data : {
					action      : 'cartflows_activate_plugin',
					plugin_init : 'woocommerce/woocommerce.php',
					security    : CartFlowsImportVars.cartflows_activate_plugin_nonce
				},
			})
				.done(function( request, status, XHR )
				{
					$(".wcf-notice-wrap").addClass('wcf-hidden');
					$(".cartflows-template-selector").removeAttr( 'disabled' );
					CartFlowsImport.wc_installed = true;
					CartFlowsImportVars.is_wc_installed = 'yes';
					CartFlowsImportVars.is_wc_activated = 'yes';
					location.reload(true);
					window.location.search += '&add-new-flow';
				});

		},


		_installWc: function( event ) {

			var plugin_slug = 'woocommerce';

			$(this).addClass('updating-message button');
			$(this).text( cartflows_admin.wc_activating_message );

			if( false == cartflows_admin.wc_status.installed ) {

				if ( wp.updates.shouldRequestFilesystemCredentials && ! wp.updates.ajaxLocked ) {
					wp.updates.requestFilesystemCredentials( event );

					$document.on( 'credential-modal-cancel', function() {
						var $message = $( '.install-now.updating-message' );

						$message
							.removeClass( 'updating-message' )
							.text( wp.updates.l10n.installNow );

						wp.a11y.speak( wp.updates.l10n.updateCancel, 'polite' );
					} );
				}

				wp.updates.installPlugin( {
					slug: plugin_slug
				} );
			} else {
				CartFlowsImport._activateWc();
			}
		}


	};

	/**
	 * Initialization
	 */
	$(function(){
		CartFlowsImport.init();
	});

})(jQuery);	