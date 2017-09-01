<?php
/*
*Contains validation methodes
*/
class constraints{
  function __construct(){

  }

  function escapeVariable($variable){
    $variable = strip_tags($variable);
    return htmlentities($variable);
  }

  //function to check the parameter meets constraints or not
  function IntParameterCheck($value){
    return ($value!=""||$value!=null?true:false)&&(is_numeric($value)&&isset($value));
  }
}
?>
