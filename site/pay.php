<?php
  require "secret.php";

  if(!isset($_GET["id"])){
    die("Error, id not defined");
  }
  if(!isset($_GET["return"])){
    die("Error, return not defined");
  }

   $conn = new mysqli($servername, $username, $password, $dbname);

   if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }


  $sql = "UPDATE bills SET paid=1 WHERE id=".$_GET["id"];
  if(isset($_GET["unpay"])){
    $sql = "UPDATE bills SET paid=0 WHERE id=".$_GET["id"];
  }


  if ($conn->query($sql) === TRUE) {
    $conn->close();
    header('Location: '.urldecode($_GET["return"]));
  } else {
    $conn->close();
    die("Error updating record: " . $conn->error);
  }
?>
