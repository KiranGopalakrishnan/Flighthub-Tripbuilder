<?php
interface databaseInterface{
  public function connect();
  public function buildQuery($query,$params);
}
?>
