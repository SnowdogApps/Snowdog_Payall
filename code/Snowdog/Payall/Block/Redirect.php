<?php
class Snowdog_Payall_Block_Redirect extends Mage_Core_Block_Abstract {

  protected $_order = null;

  protected function _toHtml() {
    $payment      = Mage::getModel('payall/payment');
    $form         = new Varien_Data_Form();
    $redirectData = $payment->newPaymentData($this->_order);
      
    $form->setAction($redirectData['url'])
      ->setId('payall_checkout')
      ->setName('payall_checkout')
      ->setMethod('POST')
      ->setUseContainer(true);

    $form->addField('store-id', 'hidden', array('name' => 'store-id', 'value' => $redirectData['store_id']));
    $form->addField('order-id', 'hidden', array('name' => 'order-id', 'value' => $redirectData['order_id']));
    $form->addField('amount', 'hidden', array('name' => 'amount', 'value' => $redirectData['amount']));
    $form->addField('currency', 'hidden', array('name' => 'currency', 'value' => $redirectData['currency']));
    $form->addField('title', 'hidden', array('name' => 'title', 'value' => $redirectData['title']));
    $form->addField('checksum', 'hidden', array('name' => 'checksum', 'value' => $redirectData['checksum']));
    $form->addField('salt', 'hidden', array('name' => 'salt', 'value' => $redirectData['salt']));

    $html = '<html><body>';
    $html.= $form->toHtml();
    // $html.= '<script type="text/javascript">document.getElementById("payall_checkout").submit();</script>';
    $html.= '</body></html>';

    return $html;
  }

  public function setOrder($order) {
    $this->_order = $order;
    return $this;
  }

} // end class
