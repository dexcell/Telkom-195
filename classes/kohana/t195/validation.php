<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_T195_Validation {
  
  protected $_config;
  protected $_error;
  protected $_rules = array();
  protected $_values = array();
  
  public static function factory(array $values)
  {
    return new T195_Validation($values);
  }
  
  public function __construct($values)
  {
    $this->_config = Kohana::$config->load('t195');
    $this->_values = $values;
  }
  
  public function rule($key, $value)
  {
    $this->_rules[$key] = $value;
   
    return $this;
  }
  
  public function rules($values)
  {
    $this->_rules = $values;
    
    return $this;
  }
  
  public function error()
  {
    return $this->_error;
  }
  
  /*
   * Validate Telkom 195 response
   * 
   *  $validation = T195_Validation::factory($this->request->post())
   *    ->rule('invoice', 1)
   *    ->rule('amount', 950000)
   *    ->rule('currency_code', 'IDR');
   * 
   *  if ($validation->check())
   *  {
   *    // Do something
   *  }
   *  else
   *  {
   *    echo $validation->error();
   *  }
   * 
   * return boolean
   * 
   */
  public function check()
  {
    $result = FALSE;
    
    try
    {
      if (Arr::get($this->_values, 'result_code') != '00')
        throw new Kohana_Exception(Arr::get($this->_values, 'result_desc'));
      
      if (Arr::get($this->_values, 'merchant_id') != $this->_config->merchant_id)
        throw new Kohana_Exception('Invalid merchant ID');
      
      foreach ($this->_rules as $key => $rule)
      {
        if ($value = Arr::get($this->_values, $key) != $rule)
          throw new Kohana_Exception("$key invalid");
      }
      
      // Build values to be checked
      $values = array(
        'merchant_id' => Arr::get($this->_values, 'merchant_id'),
        'payment_code' => Arr::get($this->_values, 'payment_code'),
        'return_url' => $this->_config->return_url,
      );
      
      // Create merchant signature
      $merchant_signature = strtoupper(hash('sha256', strtoupper(implode('%', $values)).'%'.$this->_config->merchant_password));

      // Add merchant signature
      Arr::unshift($values, 'mer_signature', $merchant_signature);
      
      // Check if transaction is really exist
      $request = Request::factory($this->_config->check_status_url)
        ->method(HTTP_Request::POST)
        ->post($values);
      
      $request->client()
        ->options(array(
          CURLOPT_SSL_VERIFYHOST => 0,
          CURLOPT_SSL_VERIFYPEER => 0,
        ));
      
      $response = $request->execute();
      
      $response_code = $response->body();
    
      if ($response_code != '00')
        throw new Kohana_Exception("Invalid Transaction. Code: $response_code");
      
      $result = TRUE;
    }
    catch (Kohana_Exception $e)
    {
      $this->_error = $e->getMessage();
      $result = FALSE;
    }
    
    return $result;
  }
  
}