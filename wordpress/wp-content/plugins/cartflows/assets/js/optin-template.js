(function($){

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



	$(document).ready(function($) {

		wcf_persistent_data();
		
		wcf_custom_field_validation();
	});

})(jQuery);