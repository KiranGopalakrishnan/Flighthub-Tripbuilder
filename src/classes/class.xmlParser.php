<?php
class xmlParser{
  //Everything Related to XmlParsing
  public $simpleXmlobject;
  function loadXml($filepath = null):simpleXMLElement{ //Returns a SimpleXmlObject
    $response=false;
    if($filepath !== null){ //Check if filepath is empty
    $contents = file_get_contents($filepath);
    $simpleXml = simplexml_load_string($contents) or die("Couldn't Create the simpleXmlObject");
    if ($simpleXml !== false) {
      $this->simpleXmlobject = $simpleXml;
      $response = $simpleXml;
    } else {
      //TODO: Remove this block for production code
      /*echo "Failed loading XML: ";
      foreach(libxml_get_errors() as $error) {
        echo "<br>", $error->message;
      }*/
    }
  }
    return $response;
  }
  //Getters for all the fields that we need to retrieve from the xmlObject
  function getSingleUser($index):simpleXMLElement{
    return $this->simpleXmlobject->member[$index];
  }
}
?>
