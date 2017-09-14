<?php

require_once(dirname(__FILE__) . "/vendor/autoload.php");

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

 /**
  * Plugin Name: PayCertify Gateway
  * Plugin URI:  https://paycertify.com/
  * Description: Paycertify Payment Gateway Plugin for WooCommerce.
  * Version: 	 0.2.1
  * Author: 	 PayCertify
  * Author URI:  https://paycertify.com/
  * License: 	 GPLv2
  *
  * Text Domain: paycertify
  *
  * @class       WC_Paycertify
  * @version     0.2.1
  * @package     WooCommerce/Classes/Payment
  * @author      Paycertify
  */

class WC_Paycertify {

  // Gateway
  protected $gateway;

  /**
   * Constructor
   */
  public function __construct() {

    define( 'Paycertify_Plugin_Url', plugin_dir_path( __FILE__ ) );

    define( 'Log_Path', plugin_dir_path( __FILE__ ) . 'log' );

    add_action( 'plugins_loaded', array( $this, 'init' ), 0 );
    add_action( 'wp_enqueue_scripts', array( $this, 'paycertify_assets' ) );
  }


  /**
   * Loading assets
   */
  public function paycertify_assets() {

    wp_enqueue_style( 'style-name',  plugins_url('assets/css/style.css', __FILE__), array()  );
    wp_enqueue_script( 'script', plugins_url('assets/js/script.js', __FILE__) ,array('jquery'),false,true);
    wp_enqueue_script( 'creditcardvalidator', plugins_url('assets/js/jquery.payment.min.js', __FILE__),array('jquery'),false,false);

  }

  /**
   * Init function
   */
  public function init() {

    if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
      add_action( 'admin_notices', array( $this, 'woocommerce_gw_fallback_notice_paycertify') );
      return;
    }

    // Includes
    include_once( 'include/class-woocommerce-paycertify-gateway.php' );
    include_once( 'include/class-woocommerce-paycertify-api.php' );
    include_once( 'include/class-woocommerce-paycertify-three-ds.php' );
    include_once( 'include/class-woocommerce-paycertify-three-ds-callback.php' );

    if ( class_exists( 'WC_Subscriptions_Order' ) || class_exists( 'WC_Pre_Orders_Order' ) )
      include_once( 'include/class-woocommerce-paycertify-subscription.php' );

    // Gateway
    $this->gateway = new WC_Paycertify_Gateway();

    // Call configure 3DS method
    $this->configure_three_ds();

    // Add Paycertify Gateway
    add_filter( 'woocommerce_payment_gateways', array( $this, 'add_paycertify_gateway' ) );
  }

  /**
   *  Add paycertify_gateway to exitsting woocommerce gateway
   */
  public function add_paycertify_gateway( $gateways ) {

    if ( class_exists( 'WC_Subscriptions_Order' ) || class_exists( 'WC_Pre_Orders_Order' ) ) {
      $gateways[] = 'Wc_Paycertify_Gateway_Subscription';
    } else {
      $gateways[] = 'WC_Paycertify_Gateway';
    }

      return $gateways;

  }

  /**
   * Fallback_notice_paycertify
   */
  public function woocommerce_gw_fallback_notice_paycertify() {

    echo '<div class="error"><p>' . sprintf( __( 'WooCommerce PayCertify Gateway depends on the last version of %s to work!', 'wcPG' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a>' ) . '</p></div>';

  }

  /**
   * Configure 3DS
   */
  private function configure_three_ds() {
    $gateway = new WC_Paycertify_Gateway();

    if( $gateway->is_three_ds_enabled() ) {
      PayCertify\ThreeDS::$api_key    = $gateway->get_option('3ds_api_key');
      PayCertify\ThreeDS::$api_secret = $gateway->get_option('3ds_api_secret');
      PayCertify\ThreeDS::$mode       = 'live'; // Can be live or test
    }
  }

}
new WC_Paycertify();

/*
* Write log
*/
if( !function_exists('writeLog') ) {
  function writeLog( $msg, $logTime = true, $source = true ) {

    $filename = 'log.txt';

    if ((count($msg) == 2)&&is_array($msg)&&is_string($msg[0]))
    $msg = $msg[0]."\n". (is_string($msg[1]) ? $msg[1] : var_export($msg[1], 1))."\n";

    $file = Log_Path.'/'.$filename;

    $d = debug_backtrace();
    $line = $d[0]['line'];

    $f = fopen($file, 'a+');
    list($usec, $sec) = explode(' ', microtime());
    if ($logTime) {
      $date = date('d.m.Y H:i:s ', $sec) . $usec;
      fwrite($f, $date.": ");
    }
    if ($source && ($source != "FATAL")) {
      fwrite($f, "\t{$filename}:{$line}\t");
    }
    fwrite($f, $msg."\n");
    fclose($f);
  }
}

if( !function_exists('pc_overridePageTemplate') ) {
  add_action( 'template_redirect', 'pc_overridePageTemplate' );

  function pc_overridePageTemplate( $page_template )
  {
    global $wp;

    $current_url = home_url(add_query_arg(array(),$wp->request));

    if (preg_match("/paycertify\/callback/", $current_url)) {
      require_once(dirname( __FILE__ ) . '/actions/3ds_callback.php');
    } else {
      return;
    }
  }
}

function register_session(){
    if( !session_id() )
        session_start();
}
add_action('init','register_session');
