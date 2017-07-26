jQuery(document.body).on('update_checkout', function(e){
    //e.preventDefault();
    //e.stopPropagation();
    e.stopImmediatePropagation();
    ALERT('ENTROU AQUI');
});

jQuery(document).on('ready', function(e){
    // alert('CARREGOU')
});

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

