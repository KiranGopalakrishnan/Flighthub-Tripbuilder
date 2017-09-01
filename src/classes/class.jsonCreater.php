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
    * @return Array/false
    **/
    public function setStatus($status,$message = null){
      $this->response["status"] = $status;
      if($message !== null){
        $this->response["message"] = $message;
      }
    }
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
    }
    public function createResponse(){
      return $this->response;
    }

  }
?>
