<?php
class Snowdog_Payall_Model_Payment extends Mage_Payment_Model_Method_Abstract {

  protected $_code    = 'payall';
  protected $_config  = null;
  protected $_order   = null;
  protected $_result  = null;

  const PAYMENT_STATUS_CASHPAID   = 1;
  const PAYMENT_STATUS_SENT       = 2;
  const PAYMENT_STATUS_COMPLETED  = 3;
  // const PAYMENT_STATUS_CANCELED  = 4; // TODO

  public function getOrderPlaceRedirectUrl() {
    return Mage::getUrl('payall/payment/new', array('_secure' => true));
  }

  public function newPaymentData(Mage_Sales_Model_Order $order) {
    $this->_order   = $order;
    $address        = Mage::getModel('sales/order_address')->load($this->_order->getBillingAddressId());
    $orderId        = $this->_order->getRealOrderId();
    $salt           = md5($this->_getConfig()->getSalt() . $orderId . rand() . rand() . microtime() . rand() . rand());

    return array(
      'url'       => $this->_getConfig()->getNewPaymentUrl(),
      'store_id'  => $this->_getConfig()->getStoreId(),
      'order_id'  => $orderId,
      'amount'    => $this->_amountFormat($this->_order->getGrandTotal()),
      'currency'  => $this->_order->getOrderCurrencyCode(),
      'title'     => Mage::helper('payall')->__('Order no. %s', $orderId),
      'salt'      => $salt,
      'checksum'  => $this->_generateChecksum($salt, $this->_getConfig()->getClientSalt()),
      'user'      => array(
        'firstname'   => $this->_order->getCustomerFirstname(),
        'middlename'  => $this->_order->getCustomerMiddlename(),
        'lastname'    => $this->_order->getCustomerLastname(),
        'email'       => $this->_order->getCustomerEmail(),
        'phone'       => $address->getTelephone(),
        'region'      => $address->getRegion(),
        'postcode'    => $address->getPostcode(),
        'street'      => $address->getStreet(),
        'city'        => $address->getCity(),
        'country'     => $address->getCountry()
      )
    );
  }

  public function orderNotifyRequest($result) {
    $this->_result  = $result;
    $this->_order   = Mage::getModel('sales/order')->loadByIncrementId($this->_result['order-id']);

    if ($this->_verifyChecksum($this->_result['checksum'], $this->_result['salt'])) {
      $this->_updatePaymentStatus($this->_result['status']);
    } else {
      throw new Exception(Mage::helper('payall')->__('Checksum incorrect'));
    }
  }

  public function getOrder() {
    return $this->_order;
  }

  protected function _updatePaymentStatus($status) {
    $this->_order->getPayment()->setTransactionId($this->_result['txn-id']);
    if (isset($this->_result['parent-txn-id'])) {
      $this->_order->getPayment()->setParentTransactionId($this->_result['parent-txn-id']);
    }
    switch ($status) {
      case self::PAYMENT_STATUS_CASHPAID:
        $this->_updatePaymentStatusCashpaid();
        break;
      case self::PAYMENT_STATUS_SENT:
        $this->_updatePaymentStatusSent();
        break;
      case self::PAYMENT_STATUS_COMPLETED:
        $this->_updatePaymentStatusCompleted();
        break;
      default:
        throw new Exception(Mage::helper('payall')->__('Cannot handle payment status \'%s\'.', $status));
        break;
    }
  }

  protected function _updatePaymentStatusCashpaid() {
    $this->_order->getPayment()
        ->setPreparedMessage(Mage::helper('payall')->__('Customer paid cash.'))
        ->setIsTransactionClosed(false)
        ->registerVoidNotification();
    $this->_order->save();
  }

  protected function _updatePaymentStatusSent() {
    $this->_order->getPayment()
        ->setPreparedMessage(Mage::helper('payall')->__('Transfer was sent.'))
        ->setIsTransactionClosed(false)
        ->registerVoidNotification();
    $this->_order->save();
  }

  protected function _updatePaymentStatusCompleted() {
    $payment = $this->_order->getPayment();

    $payment->setPreparedMessage('')
        ->setShouldCloseParentTransaction(true)
        ->setIsTransactionClosed(false)
        ->registerCaptureNotification($this->_order->getGrandTotal());

    Mage::dispatchEvent('payall_payment_completed', array('payment_method' => $this));

    $this->_order->save();

    // notify customer
    $invoice = $payment->getCreatedInvoice();
    if ($invoice && !$this->_order->getEmailSent()) {
      $this->_order->sendNewOrderEmail()->addStatusHistoryComment(
        Mage::helper('payall')->__('Notified customer about invoice #%s.', $invoice->getIncrementId())
      )
      ->setIsCustomerNotified(true)
      ->save();
    }
  }

  protected function _generateChecksum($publicSalt, $privateSalt) {
    if ( ! $this->_order) {
      throw new Exception('No order');
    }

    return hash('sha224', implode(array(
      $this->_order->getRealOrderId(),
      $this->_amountFormat($this->_order->getGrandTotal()),
      $this->_order->getOrderCurrencyCode(),
      Mage::helper('payall')->__('Order no. %s', $this->_order->getRealOrderId()),
      $publicSalt,
      $privateSalt
    )));
  }

  protected function _verifyChecksum($checksum, $salt) {
    if ( ! $this->_order) {
      return false;
    }

    return ($checksum == $this->_generateChecksum($salt, $this->_getConfig()->getServerSalt()));
  }

  protected function _getConfig() {
    if (is_null($this->_config)) {
      $this->_config = Mage::getModel('payall/config');
    }
    return $this->_config;
  }

  protected function _amountFormat($amount) {
    return number_format(round($amount, 2, PHP_ROUND_HALF_UP), 2);
  }

} // end class
