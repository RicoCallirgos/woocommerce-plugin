<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

/*
* @class 	WC_Paycertify_Gateway
* @extends  WC_Payment_Gateway
* @auther 	Percertify
* @version  0.1.3
*/

class WC_Paycertify_Gateway extends WC_Payment_Gateway {

  /**
   * Constructor
   */
  public function __construct() {

    $this->id                 = 'paycertify';
    $this->icon               = apply_filters( 'woocommerce_cod_icon', '' );
    $this->method_title       = __( 'PayCertify', 'paycertify' );
    $this->method_description = __( 'Demo PayCertify.', 'paycertify' );
    $this->has_fields 		  = true;

    // Load the settings
    $this->init_form_fields();
    $this->init_settings();

    // Get settings
    $this->title              = $this->get_option( 'title' );
    $this->description        = $this->get_option( 'description' );
    $this->instructions       = $this->get_option( 'instructions', $this->description );
    $this->enable_for_methods = $this->get_option( 'enable_for_methods', array() );

    // Define the supported features
    $this->supports = array(
      'products',
      'subscriptions',
      'subscription_cancellation',
      'subscription_suspension',
      'subscription_reactivation',
      'subscription_amount_changes',
      'subscription_date_changes',
      'subscription_payment_method_change',
      'subscription_payment_method_change_customer',
      'subscription_payment_method_change_admin',
      'multiple_subscriptions',
      'pre-orders',
    );


    // Save settings
    if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) )
      add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options' ) );
    else
      add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options' ) );
  }

  /**
  * Initialize Gateway Settings Form Fields
  */
  public function init_form_fields() {
    $shipping_methods = array();

    if ( is_admin() ) {
      foreach ( WC()->shipping()->load_shipping_methods() as $method ) {
        $shipping_methods[ $method->id ] = $method->get_method_title();
      }
    }

    $this->form_fields = array(
      'enabled' => array(
        'title'       => __( 'Enable/Disable', 'paycertify' ),
        'label'       => __( 'Enable PayCertify', 'paycertify' ),
        'type'        => 'checkbox',
        'description' => '',
        'default'     => 'no'
      ),
      'title' => array(
        'title'       => __( 'Title', 'paycertify' ),
        'type'        => 'text',
        'description' => __( 'Payment method description that the customer will see on your checkout.', 'paycertify' ),
        'default'     => __( 'PayCertify Payment Gateway', 'paycertify' ),
        'desc_tip'    => true,
      ),
      'description' => array(
        'title'       => __( 'Description', 'paycertify' ),
        'type'        => 'textarea',
        'description' => __( 'Payment method description that the customer will see on your website.', 'paycertify' ),
        'default'     => __( 'Pay with paycertify payment gateway.', 'paycertify' ),
        'desc_tip'    => true,
      ),
      'instructions' => array(
        'title'       => __( 'Instructions', 'paycertify' ),
        'type'        => 'textarea',
        'description' => __( 'Instructions that will be added to the thank you page.', 'paycertify' ),
        'default'     => __( 'Pay with paycertify payment gateway.', 'paycertify' ),
        'desc_tip'    => true,
      ),
      'api_endpoint' => array(
        'title'       => __( 'Gateway Endpoint', 'paycertify' ),
        'type'        => 'text',
        'default'     => __( 'https://demo.paycertify.net', 'paycertify' ),
      ),
      'api_token' => array(
        'title'       => __( 'Gateway ApiToken', 'paycertify' ),
        'type'        => 'text',
        'default'     => __( '7E35FC46-C951-2D2F-FB42-7795F3D24C60', 'paycertify' ),
      ),
      'paycertify_trans' => array(
        'title' 	  => __( 'PayCertify Transaction', 'paycertify' ),
        'type'        => 'checkbox',
        'label'       => __( 'Enable PayCertify Transaction for integration ', 'paycertify' ),
        'description' => __( 'We recommend you use payCertify transaction.', 'paycertify' ),
        'default'     => 'yes',
        'desc_tip'    => true
      ),
      'enable_testmode' => array(
        'title' 	  => __( 'Test Mode', 'paycertify' ),
        'type'        => 'checkbox',
        'label'       => __( 'Enable Test Mode for integration ', 'paycertify' ),
        'description' => __( 'We recommend you use test mode until you are ready to go live.', 'paycertify' ),
        'default'     => 'yes',
        'desc_tip'    => true
      ),
      'enable_3ds' => array(
        'title' 	  => __( '3DS', 'paycertify' ),
        'type'        => 'checkbox',
        'label'       => __( 'Enable 3DS ', 'paycertify' ),
        'description' => __( 'Enable it if you want to use 3DS.', 'paycertify' ),
        'default'     => 'no',
        'desc_tip'    => true
      ),
      '3ds_api_key' => array(
        'title' 	  => __( '3DS API Key', 'paycertify' ),
        'type'        => 'text',
        'label'       => __( 'Enable 3DS ', 'paycertify' ),
        'description' => __( 'Add your 3DS API Key here.', 'paycertify' ),
        'default'     => '',
        'desc_tip'    => true
      ),
      '3ds_api_secret' => array(
        'title'     => __( '3DS API Secret', 'paycertify' ),
        'type'        => 'text',
        'label'       => __( 'Enable 3DS ', 'paycertify' ),
        'description' => __( 'Add your 3DS API Secret here.', 'paycertify' ),
        'default'     => '',
        'desc_tip'    => true
      ),
      '3ds_decline_transactions' => array(
        'title'     => __( 'Auto Decline Transaction?', 'paycertify' ),
        'type'        => 'checkbox',
        'label'       => __( 'Decline transaction if 3D Secure fails.', 'paycertify' ),
        'description' => __( 'Decline transaction if 3D Secure fails.', 'paycertify' ),
        'default'     => 'yes',
        'desc_tip'    => true
      ),
      '3ds_frictionless' => array(
        'title'     => __( 'Use Frictionless Mode?', 'paycertify' ),
        'type'        => 'checkbox',
        'label'       => __( 'Enable this option to use 3DS in background, without a user input.', 'paycertify' ),
        'description' => __( 'Enable this option to use 3DS in background, without a user input.', 'paycertify' ),
        'default'     => 'yes',
        'desc_tip'    => true
      ),
      '3ds_fallback_regular' => array(
        'title'     => __( 'Fallback to Regular Mode?', 'paycertify' ),
        'type'        => 'checkbox',
        'label'       => __( 'If Frictionless mode fails, check it to fallback to regular mode.', 'paycertify' ),
        'description' => __( 'It only works when using Frictionless mode.', 'paycertify' ),
        'default'     => 'no',
        'desc_tip'    => true
      ),
    );

  }

  /**
  * Validate fields
  */
  public function validate_fields() {
    $Ret = true;

    if(isset($_POST['cardholder_name']) && $_POST['cardholder_name'] == '') {
      $Ret = false;
      wc_add_notice( __('', 'paycertify') . '<strong>NameOnCard</strong> is a required field.', 'error' );
    }
    if(isset($_POST['cardnum']) && $_POST['cardnum'] == '') {
      $Ret = false;
      wc_add_notice( __('', 'paycertify') . '<strong>Card Number</strong> is a required field.', 'error' );
    }
    if(isset($_POST['exp_date']) && $_POST['exp_date'] == '') {
      $Ret = false;
      wc_add_notice( __('', 'paycertify') . '<strong>Exp Date</strong> is a required field.', 'error' );
    }
    if(isset($_POST['cvv']) && $_POST['cvv'] == '') {
      $Ret = false;
      wc_add_notice( __('', 'paycertify') . '<strong>CVV</strong> is a required field.', 'error' );
    }

    return $Ret;
  }

  public function admin_options(){
    echo '<h3>'.__('Paycertify Payment Gateway Settings', 'paycertify').'</h3>';
    echo '<p>'.__('Paycertify Payment Gateway').'</p>';
    echo '<table class="form-table">';
    // Generate the HTML For the settings form.
    $this -> generate_settings_html();
    echo '</table>';
  }

  /**
  *  payment fields.
  */
  public function payment_fields(){
    $html = '';
    if($this -> description) echo wpautop(wptexturize($this -> description));
    $html.="<div class=\"threeds_loading\"><div class=\"message\"><p>Securing your purchase. <br /><small>It may take a few seconds.</small></p></div></div>";
    $html.='<input type="hidden" name="3ds_type" id="3ds_type" value="' . $this->threeDSType() . '">';   
    $html.='<input type="hidden" name="3ds_fallback" id="3ds_fallback" value="' . $this->isThreeDSFallbackEnabled() . '">';   
    $html.= '<table>';
      $html.= '<tbody>';
        $html.= '<tr>';
          $html.= '<td colspan="2">';
            $html.='<input type="text" placeholder="NameOnCard" name="cardholder_name" id="cardholder_name" class="form-control required">';
          $html.= '</td>';
        $html.= '</tr>';
        $html.= '<tr>';
          $html.= '<td colspan="2">';
            $html.='<input type="text" placeholder="Card Number" name="cardnum" id="cardnum" class="form-control required">';
          $html.= '</td>';
        $html.= '</tr>';
        $html.= '<tr>';
          $html.= '<td>';
            $html.='<input type="text" maxlength="4" placeholder="MMYY" name="exp_date" id="exp_date" class="form-control required">';
          $html.= '</td>';
          $html.= '<td>';
            $html.='<input type="text" maxlength="4" placeholder="CVV" name="cvv" id="cvv" class="form-control required">';
          $html.= '</td>';
        $html.= '</tr>';
      $html.= '</tbody>';
    $html.= '</table>';
    $html.= "<script type='text/javascript'>jQuery('#cardnum').payment('formatCardNumber');</script>";

    echo $html;
  }

  /**
  * process payment
  */
  public function process_payment( $order_id ) {
    global $woocommerce;

    require_once plugin_dir_path( __FILE__ ) . 'class-woocommerce-paycertify-api.php';

    if ( $woocommerce->cart->get_cart_contents_count() == 0 ) {
      wc_add_notice( __('Cart Error : ', 'paycertify') . '<strong>Cart</strong> is empty.', 'error' );
      return;
    }
    $order = wc_get_order( $order_id );

    // FAZER O 3DS
    if ($this->is_three_ds_enabled()) {
        $_SESSION['payment'] = $_POST;
        $threeDS = new WC_Paycertify_ThreeDS($_POST, $order);
        return $threeDS->start($order_id);
    } else {
      return $this->finishPayment($order_id);
    }
  }

  /**
   * Finish the order
   */
  public function finishPayment( $order_id, $threeDSResult ) {
    global $woocommerce;
    $error = '';
    // $_SESSION['3ds'] = null;

    require_once plugin_dir_path( __FILE__ ) . 'class-woocommerce-paycertify-api.php';

    if ( $woocommerce->cart->get_cart_contents_count() == 0 ) {
      return wc_add_notice( __('Cart Error : ', 'paycertify') . '<strong>Cart</strong> is empty.', 'error' );
    }
    $order = wc_get_order( $order_id );

    $Paycertify_Process = new Paycertify_API( $order,  $this->settings );
    $Ret = $Paycertify_Process->do_transaction( $_SESSION['payment'],  $threeDSResult );

    // PNRef number
    $PNRef =  $Ret['data']['PNRef'] ? $Ret['data']['PNRef'] : '';
    update_post_meta( $order_id, 'PNRef', $PNRef  );

    if( isset( $Ret['success'] ) && $Ret['success'] == 1 ) {
      $order->payment_complete();
      $order->add_order_note( __('PNRef:'.$Ret['data']['PNRef'].' payment completed', 'paycertify') );
      // Remove cart
      $woocommerce->cart->empty_cart();

      // Return thankyou redirect
      return array(
        'result'    => 'success',
        'redirect'  => $this->get_return_url( $order )
      );
    }
    else {
      $i = 1;
      foreach($Ret['error'] as $k=>$v) {
        if(count($Ret['error']) == $i )
          $join = "";
        else
          $join = ", <br>";

        $error.= $v.$join;
        $i++;
      }

      // var_dump($error);
      // die('ERROR');

      // Mark as on-hold (we're awaiting the payment)
      $order->update_status( 'on-hold', __( 'Awaiting offline payment', 'paycertify' ) );
      return wc_add_notice( __('Payment Error : ', 'paycertify') . $error , 'error' );
    }
  }

  /**
   * process_refund function.
   */
  public function process_refund( $order_id, $amount = NULL, $reason = '' ) {
    $order = wc_get_order( $order_id );

    $Paycertify_Process = new Paycertify_API( $order,  $this->settings );
    $Ret = $Paycertify_Process->refund();

    if( isset( $Ret['success'] ) && $Ret['success'] == 1 ) {
      $order->add_order_note( __('Paycertify Refund PNRef:'.$Ret['data']['PNRef'].' payment refund completed', 'paycertify') );
      return true;
    }
    else {
      $error = '';
      $i = 1;
      foreach($Ret['error'] as $k=>$v) {
        if(count($Ret['error']) == $i )
          $join = "";
        else
          $join = ", <br>";

        $error.= $v.$join;
        $i++;
      }
      return new WP_Error( 'refund_error', __('Payment Refund error: ', 'paycertify' ) . $error );
    }

  }

  public function is_three_ds_enabled() {
    return ($this->get_option('enable_3ds') == 'yes' && strlen($this->get_option('3ds_api_key')) > 0 && strlen($this->get_option('3ds_api_secret')) > 0);
  }

  public function threeDSType(){
    if ($this->get_option('3ds_frictionless') == 'yes'){
      return 'frictionless';
    } else {
      return 'strict';
    }
  }

  public function isThreeDSFallbackEnabled() {
    return ($this->get_option('3ds_fallback_regular') == 'yes') ? "1" : "0";
  }

} // end \WC_Paycertify_Gateway
