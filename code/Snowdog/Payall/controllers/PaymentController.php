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

  public function orderNotifyRequestAction() {
    try {
      $result = Zend_Json::decode($this->getRequest()->getRawBody());
      Mage::getModel('payall/payment')->orderNotifyRequest($result);
      $responseBody = Zend_Json::encode(array('result' => 'OK'));
    } catch (Zend_Json_Exception $e) {
      Mage::log(array(
          'message' => $e->getMessage(),
          'request' => $this->getRequest()->getRawBody()
        ), Zend_Log::DEBUG, Mage::getModel('payall/config')->getErrorLogFilename()
      );
      $responseBody = Zend_Json::encode(array('result' => 'Failed'));
    } catch (Exception $e) {
      Mage::log(array(
          'message' => $e->getMessage(),
          'request' => $result
        ), Zend_Log::ERR, Mage::getModel('payall/config')->getErrorLogFilename()
      );
      $responseBody = Zend_Json::encode(array('result' => 'Failed'));
    }

    $this->getResponse()
      ->setHeader('Content-type', 'application/json; charset=UTF-8')
      ->setBody($responseBody);
    $this->loadLayout(false);
    $this->renderLayout();
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