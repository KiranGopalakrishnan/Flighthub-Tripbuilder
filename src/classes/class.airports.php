<?php
require_once("class.database.php");
require_once("class.jsonCreater.php");

/**
*Contains all functions related to airports
*/
class airports
{
  private $db;
  private $jsonCreater;
  //Dependancy injection via Constructor
  public function __construct($db,$jsonCreater)
  {
    $this->db = $db;
    $this->jsonCreater = $jsonCreater;
  }

  /**
  * Retrieves all airports in alphabetical order from positions startLimit to endLimit
  **@param startLimit - starting position of results to fetch
  **@param endLimit - ending position of results to fetch
  **@return Array
  **/

  public function getAirports($startLimit,$endLimit){
    $responseStatus = 200;
    $message = null;
    $data = null;
    $result = null;
    $status =1; //Selection value for the tatus field in db table

    //Creating a database connection
    $connection = $this->db->connect();
    if(!$connection){
      $responseStatus = 500;
      $message = "An internal error occured";
      //connection error
    }
    $parameterCheck = is_numeric($startLimit)&&is_numeric($endLimit)&&($startLimit<$endLimit);
    if(!$parameterCheck){
      $responseStatus = 400;
      $message = "Invalid arguments";
      //connection error
    }
    if($responseStatus==200){
      $startLimit = (int)$startLimit;
      $endLimit = (int)$endLimit;
      //Parameters as name => paramName,value => paramValue ,type => PDO::PARAM_TYPE
      $params = array();
      $params[0] = $this->db->prepData(":status",$status,PDO::PARAM_INT);
      $params[1] = $this->db->prepData(":startLimit",$startLimit,PDO::PARAM_INT);
      $params[2] = $this->db->prepData(":endLimit",$endLimit,PDO::PARAM_INT);
      $sql = "SELECT * FROM airports WHERE status = :status ORDER BY name LIMIT :startLimit,:endLimit";
      $stmt = $this->db->buildQuery($sql,$params);

      //Executing The prepared statement and fetching results
      $stmt->execute();
      $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    //Json Response Creation
    $this->jsonCreater->setStatus($responseStatus,$message);
    $this->jsonCreater->setData($data);
    $result = $this->jsonCreater->createResponse();

    return $result;
  }

  /**
  * Getts the airport data based on a airportId
  **@param airportId
  **@return array/false
  **/
  public function getAirportById($airportId){
    $result = false;
    $status =1;

    //Creating a database connection
    $connection = $this->db->connect();
    if($connection){
      //connected  //Parameters as name => paramName,value => paramValue ,type => PDO::PARAM_TYPE
      $params = array();
      $params[0] = $this->db->prepData(":airportId",$airportId,PDO::PARAM_INT);
      $params[1] = $this->db->prepData(":status",$status,PDO::PARAM_INT);
      $sql = "SELECT airportId,name,IATA_Code,city,country FROM airports WHERE airportId = :airportId AND status = :status";
      $stmt = $this->db->buildQuery($sql,$params);

      //Executing The prepared statement and fetching results
      $stmt->execute();
      $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $result = $data;
    }
    return $result;

  }
}

?>
