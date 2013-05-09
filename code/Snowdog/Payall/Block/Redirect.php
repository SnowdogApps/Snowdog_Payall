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
    $form->addField('user-firstname', 'hidden',
      array('name' => 'user-firstname', 'value' => $redirectData['user']['firstname']));
    $form->addField('user-middlename', 'hidden',
      array('name' => 'user-middlename', 'value' => $redirectData['user']['middlename']));
    $form->addField('user-lastname', 'hidden',
      array('name' => 'user-lastname', 'value' => $redirectData['user']['lastname']));
    $form->addField('user-email', 'hidden',
      array('name' => 'user-email', 'value' => $redirectData['user']['email']));
    $form->addField('user-phone', 'hidden',
      array('name' => 'user-phone', 'value' => $redirectData['user']['phone']));
    $form->addField('user-region', 'hidden',
      array('name' => 'user-region', 'value' => $redirectData['user']['region']));
    $form->addField('user-postcode', 'hidden',
      array('name' => 'user-postcode', 'value' => $redirectData['user']['postcode']));
    $form->addField('user-street', 'hidden',
      array('name' => 'user-street', 'value' => implode(', ', $redirectData['user']['street'])));
    $form->addField('user-city', 'hidden',
      array('name' => 'user-city', 'value' => $redirectData['user']['city']));
    $form->addField('user-country', 'hidden',
      array('name' => 'user-country', 'value' => $redirectData['user']['country']));

    $html = '<html><body>';
    $html.= $form->toHtml();
    $html.= '<script type="text/javascript">document.getElementById("payall_checkout").submit();</script>';
    $html.= '</body></html>';

    return $html;
  }

  public function setOrder($order) {
    $this->_order = $order;
    return $this;
  }

} // end class
