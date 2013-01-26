<?php
class Snowdog_Payall_Model_Payment extends Mage_Payment_Model_Method_Abstract {

  protected $_code          = 'payall';
  protected $_config        = null;

  public function getOrderPlaceRedirectUrl() {
    return Mage::getUrl('payall/payment/new', array('_secure' => true));
  }

  public function newPaymentData(Mage_Sales_Model_Order $order) {
    $this->_order   = $order;
    $this->_config  = $this->_getConfig();

    $url      = $this->_config->getNewPaymentUrl();
    $storeId  = $this->_config->getStoreId();
    $orderId  = $this->_order->getRealOrderId();
    $amount   = $this->_order->getGrandTotal();
    $currency = $this->_order->getOrderCurrencyCode();
    $title    = Mage::helper('payall')->__('Order no. %s', $orderId);
    $salt     = md5($this->_config->getSalt() . rand() . rand() . microtime() . rand() . rand());
    $checksum = hash('sha224', implode(array($orderId, $amount, $currency, $title, $salt, $this->_config->getClientSalt())));

    return array(
      'url'       => $url,
      'store_id'  => $storeId,
      'order_id'  => $orderId,
      'amount'    => $amount,
      'currency'  => $currency,
      'title'     => $title,
      'salt'      => $salt,
      'checksum'  => $checksum
    );
  }

  protected function _getConfig() {
    return Mage::getModel('payall/config');
  }

} // end class
