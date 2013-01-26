<?php
class Snowdog_Payall_PaymentController extends Mage_Core_Controller_Front_Action {

  protected $_session = null;
  protected $_order   = null;

  public function newAction() {
    $this->setSession();
    $this->setOrder();
    $this->forceNewOrderStatus();

    $body = $this->getLayout()->createBlock('payall/redirect')
      ->setOrder($this->_order)
      ->toHtml();

    $this->getResponse()->setBody($body);
  }

  private function setSession() {
    $this->_session = Mage::getSingleton('checkout/session');
  }

  private function setOrder() {
    $id = $this->_session->getLastRealOrderId();
    $this->_order = Mage::getModel('sales/order')->loadByIncrementId($id);
  }

  private function isNewOrder() {
    return ($this->_session->getLastRealOrderId() == $this->_order->getRealOrderId());
  }

  private function forceNewOrderStatus() {
    if ($this->isNewOrder()) {
      $status = $this->_order->getStatus();
      $state  = $this->_order->getState();
      
      if ($state == Mage_Sales_Model_Order::STATE_NEW && $status != Mage::getStoreConfig('payment/payall/order_status')) {
        $this->_order->setState(Mage::getStoreConfig('payment/payall/order_status'), true)->save();
      }
    }
  }

} // end class