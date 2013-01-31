<?php
class Snowdog_Payall_Model_System_Environment {
  const PRODUCTION	= 'secure';
  const SANDBOX		  = 'sandbox';

  public function toOptionArray() {
    return array(
      array(
        'value' => PayU_Account_Model_Environment::SANDBOX,
        'label' => Mage::helper('payall')->__('Yes')
      ),
      array(
        'value' => PayU_Account_Model_Environment::PRODUCTION,
        'label' => Mage::helper('payall')->__('No')
      )
    );
  }

} // end class

