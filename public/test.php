<?php
ini_set("display_errors",1);  //TODO: To be removed from the production build
error_reporting(E_ALL);       //TODO: To be removed from the production build

require_once '../src/classes/class.database.php';
$lines = file("./airports.dat");
$rows = array();
$row_pivot = -1;
$data=null;
foreach ($lines as $line) {

    // Split line by ;
    $data = explode(',', trim($line));
    $db = new database();
    //Parameters as name => paramName,value => paramValue ,type => PDO::PARAM_TYPE
    $db->connect();
    $params = array();
    $params[0] = $db->prepData(":name",str_replace('"', "", $data[1]),PDO::PARAM_STR);
    $params[1] = $db->prepData(":city",str_replace('"', "", $data[2]),PDO::PARAM_STR);
    $params[2] = $db->prepData(":country",str_replace('"', "", $data[3]),PDO::PARAM_STR);
    $params[3] = $db->prepData(":iata",str_replace('"', "", $data[4]),PDO::PARAM_STR);
    $params[4] = $db->prepData(":status",1,PDO::PARAM_INT);

    $sql = "INSERT INTO `airports`(`name`, `city`, `country`, `IATA_Code`, `status`) VALUES (:name,:city,:country,:iata,:status)";
    $stmt = $db->buildQuery($sql,$params);

    //Executing the prepared statement
    try{

      $queryResult = $stmt->execute();  //Result of execution - true/false
      echo $queryResult."</br>";
      //$result["successful"] = $queryResult;
    //  var_dump($result);
    }catch(PDOException $e) {
      echo $e->getMessage();
    }
}


?>
