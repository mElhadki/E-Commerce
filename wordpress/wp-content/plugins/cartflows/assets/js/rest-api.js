
(function($){

	var CartFlowsAPILocalCache = {

	    /**
	     * timeout for cache in millis
	     * @type {number}
	     */
	    timeout: 300000,

	    /** 
	     * @type {{_: number, data: {}}}
	     **/
	    data: {},

	    /**
	     * Remove cache
	     * 
	     * @param  {[type]} url [description]
	     * @return {[type]}     [description]
	     */
	    remove: function (url) {
	        delete CartFlowsAPILocalCache.data[url];
	    },

	    /**
	     * Check cache
	     * 
	     * @param  {[type]} url [description]
	     * @return {[type]}     [description]
	     */
	    exist: function (url) {
	        return !! CartFlowsAPILocalCache.data[url] && ((new Date().getTime() - CartFlowsAPILocalCache.data[url]._) < CartFlowsAPILocalCache.timeout);
	    },

	    /**
	     * Get cache
	     * 
	     * @param  {[type]} url [description]
	     * @return {[type]}     [description]
	     */
	    get: function (url) {
	        return CartFlowsAPILocalCache.data[url].data;
	    },

	    /**
	     * Set cache
	     * 
	     * @param {[type]}   url        [description]
	     * @param {[type]}   cachedData [description]
	     * @param {Function} callback   [description]
	     */
	    set: function (url, cachedData, callback) {

	        CartFlowsAPILocalCache.remove(url);

	        CartFlowsAPILocalCache.data[url] = {
				_    : new Date().getTime(),
				data : cachedData
	        };

	        if( callback && typeof callback == "function"){
				callback( cachedData );
		    }
	    }
	};

	$.ajaxPrefilter(function (options, originalOptions, jqXHR) {

		if( 'brainstormforce' !== options.author ) {
			return;
		}

        var complete = originalOptions.complete || $.noop,
            url = originalOptions.url;
        
        options.beforeSend = function () {

            if (CartFlowsAPILocalCache.exist(url)) {

	        	var data = CartFlowsAPILocalCache.get(url);

	        	// Load from cache.
                CartFlowsAPI._api_cached_request( data );
                return false;
            }
            return true;
        };
        options.complete = function (XHR, status) {

        	var data = {
				args        : options.args,
				items       : ( XHR.responseText ) ? JSON.parse( XHR.responseText ) : '',
				items_count : XHR.getResponseHeader('x-wp-total') || 0,
				callback     : options.callback,
				
				// AJAX response.
				status : status,
				XHR    : XHR,
			};

            CartFlowsAPILocalCache.set(url, data, complete);
        };
	});

	CartFlowsAPI = {

		/**
		 * Debugging.
		 * 
		 * @param  {mixed} data Mixed data.
		 */
		_log: function( data, format ) {
			
			if( CartFlowsImportVars.debug ) {

				if( 'table' === format ) {
					console.table( data );
				} else {
					var date = new Date();
					var time = date.toLocaleTimeString();

					if (typeof data == 'object') { 
						console.log('%c ' + JSON.stringify( data ) + ' ' + time, 'background: #ededed; color: #444');
					} else {
						console.log('%c ' + data + ' ' + time, 'background: #ededed; color: #444');
					}
				}
			}
		},

		_api_url  : CartFlowsImportVars.server_rest_url,

		_api_cached_request: function( data ) {

			CartFlowsAPI._log( CartFlowsAPILocalCache.data, 'table' );
			CartFlowsAPI._log( 'Current time ' + new Date().getTime() );
			CartFlowsAPI._log( 'Cache expired in ' + parseInt( CartFlowsAPILocalCache.timeout ) / 1000 + ' seconds.' );

			if( undefined !== data.args.trigger && '' !== data.args.trigger ) {
				$(document).trigger( data.args.trigger, [data] );
			}

			if( data.callback && typeof data.callback == "function"){
				data.callback( data );
		    }
		},

		_api_request: function( args, callback ) {

			// Set API Request Data.
			var data = {
				url     : CartFlowsAPI._api_url + args.slug,
				args    : args,
				callback : callback,

				author  : 'brainstormforce',
			};

			// Set headers.
			data.headers = CartFlowsImportVars.headers;

			$.ajax( data )
			.done(function( items, status, XHR ) {

				if( 'success' === status && XHR.getResponseHeader('x-wp-total') ) {

					var data = {
						args        : args,
						items       : items,
						items_count : XHR.getResponseHeader('x-wp-total') || 0,
						callback     : callback,
						
						// AJAX response.
						status : status,
						XHR    : XHR,
					};

					if( undefined !== args.trigger && '' !== args.trigger ) {
						$(document).trigger( args.trigger, [data] );
					}

				} else {
					$(document).trigger( 'cartflows-api-request-error' );
				}

				if( callback && typeof callback == "function"){
					callback( data );
			    }
			})
			.fail(function( jqXHR, textStatus ) {

				$(document).trigger( 'cartflows-api-request-fail', [data, jqXHR, textStatus] );

			})
			.always(function() {

				$(document).trigger( 'cartflows-api-request-always' );

			});

		},

	};

})(jQuery);