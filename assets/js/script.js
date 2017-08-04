jQuery(document).ajaxComplete(function(event,response,xhr){
    jQuery('.threeds_loading').hide();
  try {
      jQuery('#paycertify_3ds_iframe').remove();
    jQuery.parseJSON( response.responseText );
  }
  catch (err) {
    var div = jQuery('<div id="paycertify_3ds_iframe" />').append(response.responseText);
    jQuery('.woocommerce-error').remove();
    jQuery('div.payment_method_paycertify').append(div);

    jQuery('.threeds_loading').show();
  }
});

