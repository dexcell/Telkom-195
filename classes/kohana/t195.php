<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_T195 {
  
  protected $_config;
  protected $_data = array();
  
  public static function factory()
  {
    return new T195;
  }
  
  public function __construct()
  {
    $this->_config = Kohana::$config->load('t195');
  }
  
  public function set($key, $value)
  {
    $this->_data[$key] = $value;
   
    return $this;
  }
  
  public function values($values)
  {
    $this->_data = $values;
    
    return $this;
  }
  
  /*
   * Render
   * 
   *  $ipg = IPG::factory()
   *    ->values(array(
   *      'invoice' => 1,
   *      'amount' => 950000,
   *      'currency_code' => 'IDR',
   *      'language' => 'EN',
   *      'cust_id' => 1,
   *    ))
   *    ->render();
   * 
   */
  public function generate_code()
  {
    $values = array_filter(array(
      'merchant_id' => Arr::get($this->_data, 'merchant_id', $this->_config->merchant_id),
      'invoice' => Arr::get($this->_data, 'invoice'),
      'amount' => Arr::get($this->_data, 'amount'),
      'add_info1' => Arr::get($this->_data, 'add_info1'),
      'add_info2' => Arr::get($this->_data, 'add_info2'),
      'add_info3' => Arr::get($this->_data, 'add_info3'),
      'add_info4' => Arr::get($this->_data, 'add_info4'),
      'add_info5' => Arr::get($this->_data, 'add_info5'),
      'timeout' => Arr::get($this->_data, 'timeout', $this->_config->timeout),
      'return_url' => Arr::get($this->_data, 'return_url', $this->_config->return_url),
    ));
    
    // Create merchant signature
    $merchant_signature = strtoupper(hash('sha256', strtoupper(implode('%', $values)).'%'.$this->_config->merchant_password));
    
    // Add merchant signature
    Arr::unshift($values, 'mer_signature', $merchant_signature);
    
    // Request
    $request = Request::factory($this->_config->request_url)
      ->method(HTTP_Request::POST)
      ->post($values);
      
    $request->client()
      ->options(array(
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
      ));  
      
    $response = $request->execute();
    
    // 195 Payment Code
    $payment_code = '';
    
    // If success
    if ($response->body() == '00')
    {
      // Get the payment code from database
      $transaction = ORM::factory('t195_transaction')
        ->where('invoice', '=', Arr::get($values, 'invoice'))
        ->find();
      
      // If transaction found
      if ($transaction->loaded())
      {
        $payment_code = $transaction->payment_code;
      }
    }
    
    return $payment_code;
  }
  
}