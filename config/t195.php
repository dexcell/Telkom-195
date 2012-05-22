<?php defined('SYSPATH') or die('No direct access allowed.');

return array(
  'merchant_id' => '', // Merchant ID
  'merchant_password' => '', // Merchant Password
  'timeout' => 20, // In Minutes
  'request_url' => 'http://demos.finnet-indonesia.com/195/response-insert.php',
  'check_status_url' => 'http://demos.finnet-indonesia.com/195/check-status.php',
  'return_url' => 'http://yourwebsite.com/return/t195', // Return and notification URL after transaction
);
