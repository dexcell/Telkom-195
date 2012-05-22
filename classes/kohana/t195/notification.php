<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_T195_Notification {
  
	const GENERATE_CODE = 1;
	const PAYMENT = 2;
	const EXPIRED = 3;
  const CANCELED = 4;
  const STATUS = 5;
  
  protected $_config;
  protected $_values = array();
  
  public static function factory(array $values)
  {
    return new T195_Notification($values);
  }
  
  public function __construct($values)
  {
    $this->_config = Kohana::$config->load('t195');
    $this->_values = $values;
  }
  
  /*
   * Read 195 Notification post
   * Use it in return URL specified in T195 config
   * 
   *  $notification = T195_Notification::factory($this->request->post())
   *    ->read();
   * 
   */
  public function read()
  {
    $result = FALSE;
    
    if (Arr::get($this->_values, 'trax_type') == '195Code')
    {
      // Add to transaction table so we can get it from t195->generate_code()
      ORM::factory('t195_transaction')
        ->values($this->_values, array(
          'invoice',
          'payment_code',
        ))
        ->create();
      
      $result = T195_Notification::GENERATE_CODE;
    }
    elseif (Arr::get($this->_values, 'trax_type') == 'Payment' AND Arr::get($this->_values, 'result_code') == '00')
    {
      // Find the transaction
      $transaction = ORM::factory('t195_transaction')
        ->where('payment_code', '=', Arr::get($this->_values, 'payment_code'))
        ->find();
      
      if ($transaction->loaded())
      {
        // We need remove record from database because payment is already finished
        $transaction->delete();
        // Set the result as payment
        $result = T195_Notification::PAYMENT;
      }
    }
    elseif (Arr::get($this->_values, 'trax_type') == 'Payment' AND Arr::get($this->_values, 'result_code') == '05') 
    {
      // Find the transaction
      $transaction = ORM::factory('t195_transaction')
        ->where('payment_code', '=', Arr::get($this->_values, 'payment_code'))
        ->find();
      
      if ($transaction->loaded())
      {
        // We need remove record from database because payment is already finished
        $transaction->delete();
        // Set the result as expired
        $result = T195_Notification::EXPIRED;
      }
    }
    elseif (Arr::get($this->_values, 'trax_type') == 'Cancel')
    {
      // Find the transaction
      $transaction = ORM::factory('t195_transaction')
        ->where('payment_code', '=', Arr::get($this->_values, 'payment_code'))
        ->find();
      
      if ($transaction->loaded())
      {
        // We need remove record from database because payment is already finished
        $transaction->delete();
        // Set the result as cancel
        $result = T195_Notification::CANCELED;
      }
    }
    elseif (Arr::get($this->_values, 'trax_type') == '195Status')
    {
      $result = T195_Notification::STATUS;
    }
    
    return $result;
  }
  
}