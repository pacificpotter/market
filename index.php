<?php
session_start();
// session_destroy();
require_once("dbconfig.php");
$dbObj = new dbconfig();
require_once('market.php'); 
?>