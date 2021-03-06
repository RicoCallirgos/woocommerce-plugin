<?php 
global $woocommerce;

if(isset($_SESSION['3ds'])) {
	$threeDSCallback = new WCPaycertifyThreeDSCallback();

	$result = json_decode($threeDSCallback->execute(), JSON_UNESCAPED_SLASHES);

	$gateway = new WC_Paycertify_Gateway();

	$payment = $gateway->finishPayment($_SESSION['3ds']['order_id'], $result);
}

function pc_redirectTo($location) {
	$redirect = "<script>" .
		"window.top.location.href = '" . $location . "';" .
		"</script>";
	echo $redirect;
}

if ($payment['redirect'] !== null) {
	pc_redirectTo( $payment['redirect'] );
} else {
	pc_redirectTo( $woocommerce->cart->get_checkout_url() );
}
