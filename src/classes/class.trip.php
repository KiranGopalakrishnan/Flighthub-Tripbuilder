<?php
require_once ("class.database.php");
require_once ("class.jsonCreater.php");
require_once ("class.constraints.php");
require_once ("class.airports.php");
/**
* The purpose of this class is to contain all functions which can be perfomed on a trip
* Constructor recieves an optional tripId parameter
**@param tripId - optional
**/

class trip{

  private $db;
  private $jsonCreater;
  private $airports;
  //Dependancy injection via Constructor
  public function __construct($db,$jsonCreater,$airports){
    $this->db = $db;
    $this->jsonCreater = $jsonCreater;
    $this->airports = $airports;
  }

  /**
  * Retrieves flights for a trip
  *@param tripId
  **@return Array
  **/

  public function getFlights($fromAirport,$toAirport){

    $result = null;
    $responseStatus = 200; //Initial status
    $message = null;
    $data = null; //Will contain the fetched results

    //constraints check
    $constraints = new constraints();
    $parameterCheck = is_numeric($fromAirport)&&is_numeric($toAirport);

    if(!$parameterCheck){
      //Constraint checks failed
      $responseStatus = 400;
      $message = "Invalid Arguments";
    }

    $connection = $this->db->connect();
    if(!$connection){
      //connecction failed
      $responseStatus = 500;
      $message = "An Error Occured";
    }
    if($responseStatus==200){
      //Parameters as name => paramName,value => paramValue ,type => PDO::PARAM_TYPE
      $params = array();
      $params[0] = $this->db->prepData(":toAirport",$toAirport,PDO::PARAM_STR);
      $params[1] = $this->db->prepData(":fromAirport",$fromAirport,PDO::PARAM_STR);
      $params[2] = $this->db->prepData(":status",1,PDO::PARAM_INT);
      $sql = "SELECT flightId,flightName,fromAirport,toAirport FROM flights WHERE fromAirport = :fromAirport AND toAirport = :toAirport AND status = :status";
      $stmt = $this->db->buildQuery($sql,$params);

      //Executing The prepared statement and fetching results
      $stmt->execute();
      $flights = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if(count($flights)<1){
        //Empty results
        $responseStatus = 404;
        $message = "No results found";
      }
      foreach($flights as $singleRow){
        //Getting the data for fromAirport and toAirport from airports
        $fromAirportData = $this->airports->getAirportById($singleRow["fromAirport"]);
        $toAirportData = $this->airports->getAirportById($singleRow["toAirport"]);
        if(count($fromAirportData)>0&&count($toAirportData)>0){
          $singleRow["fromAirport"] = $fromAirportData;
          $singleRow["toAirport"] = $toAirportData;
        }
        $data[count($data)]= $singleRow;
      }

    }

    //Json Response Creation
    $this->jsonCreater->setStatus($responseStatus,$message);
    $this->jsonCreater->setAdditionalData("fromAirport",$fromAirport);
    $this->jsonCreater->setAdditionalData("toAirport",$toAirport);
    $this->jsonCreater->setData($data);
    $result = $this->jsonCreater->createResponse();

    return $result;
    //end of function
  }

  /**
  **Adds a flight to a tripId
  **Checks if a trip exists between fromAirport &toAirport
  **And creates a new trip if the trip doesnt aready exists
  **If the trip does exist the flight is added to the existing trip
  **@param tripId - Id of the trip
  **@param flightName - Name of the flight
  **@param fromAirport - id of the starting point airport
  **@param toAirport - id of the ending point airport
  **@return Array
  **/

  public function addFlight($flightName,$fromAirport,$toAirport){

    $status = 1;
    $result = null;
    $queryResult = false;
    $responseStatus = 200;
    $message = null;

    //Constraints checks
    $flightName = strip_tags($flightName);
    $fromAirport = strip_tags($fromAirport);
    $toAirport = strip_tags($toAirport);
    ///Checking if the parameters meets the constraints
    $parameterCheck = is_numeric($fromAirport)&&
    is_numeric($toAirport)&&($fromAirport!==$toAirport);

    if(!$parameterCheck){
      //Constraint checks failed
      $responseStatus = 400;
      $message = "Invalid Arguments";
    }

    $connection = $this->db->connect();
    if(!$connection){
      //connecction failed
      $responseStatus = 500;
      $message = "An Error Occured";
    }

    //Checking if the fromAirport ID is valid and exists in airports table
    $isValidFromAirport= count($this->airports->getAirportById($fromAirport))>0?true:false;
    $isValidToAirport= count($this->airports->getAirportById($toAirport))>0?true:false;
    if(!$isValidFromAirport||!$isValidToAirport){
      //Invalid fromAirport OR toAirport ID's
      $responseStatus = 422;
      $message = "Invalid airport ID for either fromAirport or toAirport";
    }

    if($responseStatus==200){
      $tripId = null;
      //Checking for existing trip data
      $existingTripData = $this->getTripId($fromAirport,$toAirport);
      $doesTripExist = count($existingTripData)>0?true:false;
      if($doesTripExist){
        $tripId = $existingTripData[0]["tripId"];
      }else{
        //Creates a new trip
        $tripId = $this->createNewTrip($fromAirport,$toAirport);
      }
      //Parameters as name => paramName,value => paramValue ,type => PDO::PARAM_TYPE
      $params = array();
      $params[0] = $this->db->prepData(":flightName",$flightName,PDO::PARAM_STR);
      $params[1] = $this->db->prepData(":tripId",$tripId,PDO::PARAM_INT);
      $params[2] = $this->db->prepData(":fromAirport",$fromAirport,PDO::PARAM_INT);
      $params[3] = $this->db->prepData(":toAirport",$toAirport,PDO::PARAM_INT);
      $params[4] = $this->db->prepData(":status",$status,PDO::PARAM_INT);

      $sql = "INSERT INTO `flights`(`flightName`, `tripId`, `fromAirport`, `toAirport`, `status`) VALUES (:flightName,:tripId,:fromAirport,:toAirport,:status)";
      $stmt = $this->db->buildQuery($sql,$params);

      //Executing the prepared statement
      try{

        $queryResult = $stmt->execute();  //Result of execution - true/false
        $result["successful"] = $queryResult;

      }catch(PDOException $e) {
        $responseStatus = 500;
        $message = "An error Occurred";
      }
    }

    //Response Format Creation
    $this->jsonCreater->setStatus($responseStatus,$message);
    $this->jsonCreater->setData($result);
    $result = $this->jsonCreater->createResponse();

    return $result;
    //end of function
  }

  /**
  * Deletes the flight bases on ID
  **@param flightId
  **@return Array
  **/

  public function deleteFlight($tripId,$flightId){

    $queryResult = false;
    $responseStatus = 200;
    $message = null;
    $result = null;
    //Escaping the variable for an added layer of security
    $parameterCheck = is_numeric($flightId);
    if(!$parameterCheck){
      //Constraint checks failed
      $responseStatus = 400;
      $message = "Invalid Arguments";
    }

    $connection = $this->db->connect();
    if(!$connection){
      //connecction failed
      $responseStatus = 500;
      $message = "An Error Occured";
    }
    //Retrieves the flight by Id to check if it exists in the database
    $idExists = count($this->getFlightById($flightId))>0?true:false;
    if(!$idExists){
      //Id does not exist
      $responseStatus = 422;
      $message = "ID does not exist OR Have already been deleted";
    }

    if($responseStatus==200){
      //Parameters as name => paramName,value => paramValue ,type => PDO::PARAM_TYPE
      $params = array();
      $params[0] = $this->db->prepData(":tripId",$tripId,PDO::PARAM_STR);
      $params[1] = $this->db->prepData(":flightId",$flightId,PDO::PARAM_STR);
      $params[2] = $this->db->prepData(":status",0,PDO::PARAM_INT);
      $sql = "UPDATE flights SET status = :status WHERE flightId = :flightId AND tripId = :tripId";

      //Executing the prepared statement
      try{
        $stmt = $this->db->buildQuery($sql,$params);
        $queryResult = $stmt->execute();
      }catch(PDOException $e){
        $responseStatus = 500;
        $message = "An error Occurred";
      }
      ///Setting the result of the query in the response
      $result["successful"] = $queryResult;

    }

    //Json Response Creation
    $this->jsonCreater->setStatus($responseStatus,$message);
    $this->jsonCreater->setData($result);
    $result = $this->jsonCreater->createResponse();

    return $result;
    //end of function
  }
  /**
  * Retrieves the flight bases on Id
  **@param flightId
  **@return Array/false
  **/
  private function getFlightById($flightId){

    $result = false;
    $connection = $this->db->connect();
    if($connection){

      //Parameters as name => paramName,value => paramValue ,type => PDO::PARAM_TYPE
      $params = array();
      $params[0] = $this->db->prepData(":flightId",$flightId,PDO::PARAM_STR);
      $params[1] = $this->db->prepData(":status",1,PDO::PARAM_INT);
      $sql = "SELECT flightId FROM flights  WHERE status = :status AND flightId = :flightId";

      try{
        $stmt = $this->db->buildQuery($sql,$params);
        //Executing the prepared statement
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
      }catch(PDOException $e){
        $result = false;
      }
    }else {
      $result = false;
    }
    return $result;
    //end of function
  }
  /**
  **Retrieves the trip from trips table based on fromAirport and toAirport
  **@param fromAirport
  **@param toAirport
  **@return Array/false
  **/
  private function getTripId($fromAirport,$toAirport){

    $result = false;
    $connection = $this->db->connect();
    if($connection){

      //Parameters as name => paramName,value => paramValue ,type => PDO::PARAM_TYPE
      $params = array();
      $params[0] = $this->db->prepData(":fromAirport",$fromAirport,PDO::PARAM_STR);
      $params[1] = $this->db->prepData(":toAirport",$toAirport,PDO::PARAM_STR);
      $params[2] = $this->db->prepData(":status",1,PDO::PARAM_INT);
      $sql = "SELECT tripId FROM trips  WHERE status = :status AND fromAirport = :fromAirport AND toAirport = :toAirport";
      try{
        $stmt = $this->db->buildQuery($sql,$params);
        //Executing the prepared statement
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
      }catch(PDOException $e){
        $result = false;
      }
    }else {
      $result = false;
    }
    return $result;
    //end of function
  }
  /**
  * Create a new trip based on fromAirport and toAirport
  **@param fromAirport
  **@param toAirport
  **@return newly created tripId/false
  **/
  private function createNewTrip($fromAirport,$toAirport){

    $result = false;
    $connection = $this->db->connect();
    if($connection){
      //Parameters as name => paramName,value => paramValue ,type => PDO::PARAM_TYPE
      $params = array();
      $params[0] = $this->db->prepData(":fromAirport",$fromAirport,PDO::PARAM_STR);
      $params[1] = $this->db->prepData(":toAirport",$toAirport,PDO::PARAM_STR);
      $params[2] = $this->db->prepData(":status",1,PDO::PARAM_INT);
      $sql = "INSERT INTO `trips`(`fromAirport`, `toAirport`, `status`) VALUES (:fromAirport,:toAirport,:status)";
      try{
        $stmt = $this->db->buildQuery($sql,$params);
        //Executing the prepared statement
        $queryResult = $stmt->execute();
        $tripId = $this->db->lastInsertId();
        if($queryResult){
          $result = $tripId;
        }
      }catch(PDOException $e){
        $result = false;
      }
    }else {
      $result = false;
    }
    return $result;
    //end of function
  }
  //end of class
}
?>
