<?php
class Snowdog_Payall_Model_Config {

  public function getNewPaymentUrl() {
    return 'https://pay.test.payall.com.gh/new-payment'; // TODO
  }

  public function getStoreId() {
    return '3b45c737a0d4d4f8589f3805fb1e4246'; // TODO
  }

  public function getSalt() {
    return '^!du1*be#2mjhelzpfnqo7^^%1dh';
  }

  public function getClientSalt() {
    return '236b27e71abf7d1606c4a6aface2a457b9f9a17915c9c91329b6aae0'; // TODO
  }

  public function getServerSalt() {
    return '5bbab3aeb736641454eb01db74aabbaadb9c6ffea3f8da239b87f857'; // TODO
  }

  public function getErrorLogFilename() {
    return Mage::getStoreConfig('payall/settings/error_log_filename');
  }

} // end class