<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

/*
* @class 	WC_Paycertify_ThreeDS
* @extends  WC_Payment_ThreeDS
* @auther 	Percertify
* @version  0.1.1
*/

class WC_Paycertify_ThreeDS {

  // Card Data
  protected $cardNumber = '';
  protected $cardExpirationMonth = '';
  protected $cardExpirationYear = '';

  // Order Data
  protected $orderAmount = '';
  protected $orderId = '';

  // ThreeDS
  protected $threeDS;

  /**
   * Constructor
   */
  public function __construct($params, $order) {
    $this->threeDS = new PayCertify\ThreeDS();
    $this->cardNumber = str_replace(' ', '', $params['cardnum']);
    $this->cardExpirationMonth = substr($params['exp_date'],0,2);
    $this->cardExpirationYear = substr($params['exp_date'],2,4);
    $this->orderAmount = $order->total;
    $this->orderId = $order->id;
  }

  public function start($order_id) {
    $this->threeDS->setType('frictionless');

    $this->threeDS->setCardNumber($this->cardNumber);
      $this->threeDS->setExpirationMonth($this->cardExpirationMonth);
      $this->threeDS->setExpirationYear($this->cardExpirationYear);

      $this->threeDS->setAmount($this->orderAmount);
      $this->threeDS->setTransactionId($this->orderId);
      $this->threeDS->setMessageId($this->orderId);

      $this->threeDS->setReturnUrl("http://localhost/wordpress/3ds-callback");
      $this->threeDS->isCardEnrolled();

      if($this->threeDS->isCardEnrolled()) {
        $_SESSION['3ds'] = $this->threeDS->getSettings();
        $_SESSION['3ds']['order_id'] = $order_id;
        // Start the authentication process!
        $this->threeDS->start();
        if($this->threeDS->getClient()->hasError()) {
          // Something went wrong, render JSON for debugging
          var_dump($this->threeDS->getClient()->getResponse());
          var_dump(json_encode($this->threeDS->getClient()->getResponse(), JSON_UNESCAPED_SLASHES));

          die('DEU RUIM');
        } else {
          // All good, render the view
          // var_dump($this->threeDS->render());
          echo $this->threeDS->render();
          die();
        }
    } else {
        // If the card is not enrolled, you can't do 3DS. Do some action here:
        // you can either block the transaction from happenning or just move forward without 3DS.
          die('NAO ROLOU');
    }

    die('LOL');
  }
}
