<?php
  require "secret.php";
function convertdate($phpdate){
  $a = explode("/", $phpdate);
  return $a[2] . "-" . $a[1] . "-" . $a[0];
}

if(isset($_GET["id"])){
  $reason = 0;
  if(isset($_GET["optional"])){
    $reason = 1;
  }
  $conn = new mysqli($servername, $username, $password, $dbname);

  if ($conn->connect_error) {
   die("Connection failed: " . $conn->connect_error);
 }


 $sql = "UPDATE bills SET name='".$_GET["name"]."', payday='".convertdate($_GET["payday"])."', limitday='".convertdate($_GET["limit"])."', cash_value=".$_GET["cash"].", reason=".$reason." WHERE id=".$_GET["id"];

 if ($conn->query($sql) === TRUE) {
   $conn->close();
   header('Location: '.urldecode($_GET["return"]));
 } else {
   $conn->close();
   die("Error updating record: " . $conn->error);
 }
}

?>
