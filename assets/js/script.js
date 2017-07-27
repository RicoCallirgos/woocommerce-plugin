jQuery(document).ajaxComplete(function(event,response,xhr){
	try {
	  jQuery.parseJSON( response.responseText );
	  jQuery('#paycertify_3ds_iframe').remove();
	}
	catch (err) {
	  var div = jQuery('<div id="paycertify_3ds_iframe" />').append(response.responseText);
	  jQuery('.woocommerce-error').remove();
	  jQuery('div.payment_method_paycertify').append(div);
	}
})

