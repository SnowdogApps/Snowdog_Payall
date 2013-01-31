<?php
class Snowdog_Payall_Model_Config {

  protected function _isSandbox($field) {
    if ($this->getEnvironment() == Snowdog_Payall_Model_System_Environment::SANDBOX) {
      return 'sandbox_' . $field;
    }
    return $field;
  }

  public function getEnvironment() {
    return Mage::getStoreConfig('payment/payall/environment');
  }

  public function getNewPaymentUrl() {
    return $this->_configData('new_payment_url', 'payall/settings');
  }

  public function getStoreId() {
    return $this->_configData('store_id');
  }

  public function getClientSalt() {
    return $this->_configData('client_salt');
  }

  public function getServerSalt() {
    return $this->_configData('server_salt');
  }

  public function getSalt() {
    return '^!du1*be#2mjhelzpfnqo7^^%1dh';
  }

  public function getErrorLogFilename() {
    return Mage::getStoreConfig('payall/settings/error_log_filename');
  }

  protected function _configData($field, $group = 'payment/payall') {
    return Mage::getStoreConfig($group . '/'. $this->_isSandbox($field));
  }

} // end class
