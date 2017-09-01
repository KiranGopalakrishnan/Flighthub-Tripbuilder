<?php
  class jsonCreater{


    private $response;
    public function __construct(){
      $response = array();
      $this->response["timestamp"] = $_SERVER['REQUEST_TIME'];
    }
    /**
    * Response json status is set to the response
    * @param status
    * @param message - optional
    * @return void
    **/
    public function setStatus($status,$message = null){
      $this->response["status"] = $status;
      if($message !== null){
        $this->response["message"] = $message;
      }
    }/**
    * Additional data is set to the response
    * @param name - name of the data to set
    * @param value - value of the data to set
    * @return void
    **/
    public function setAdditionalData($name,$value){
      if($name !== null && $value !== null){
        $this->response[$name] = $value;
      }
    }
    /**
    * Sets the result into the JSON response format
    **@param $data
    **@param $name (optional) defaults to 'results'
    **@return none
    **/

    public function setData($data,$name="results"){
      if(isset($data)&&$data !== null){
      $this->response[$name] = $data;
    }
    }/**
    * Returns the response array
    **@param none
    **@return array
    **/
    public function createResponse(){
      return $this->response;
    }

  }
?>
