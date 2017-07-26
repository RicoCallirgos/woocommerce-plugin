<?php 

$threeDSCallback = new WCPaycertifyThreeDSCallback();
$result = json_decode($threeDSCallback->execute(), JSON_UNESCAPED_SLASHES);

$gateway = new WC_Paycertify_Gateway();

$payment = $gateway->finishPayment($_SESSION['3ds'], $result);

?>

<script>
	window.top.location.href = "<?php echo $payment['redirect']; ?>"; 
</script>