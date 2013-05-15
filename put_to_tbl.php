<?php
$link;
putenv("TZ=Europe/Moscow");
$bdhost; $bdname; $bduser; $bdpass;
include 'bdpass.php';
include 'function_lib.php';
//-----------------------------> Connect on DB
connect_to_db($bdname, $bdhost, $bduser, $bdpass);

//print_r($_POST);
//echo "</br>";
//echo $_POST["new-group-radio"];
//die;

add_operation($_POST);
mysqli_close($link);
header('Location: index.php');

?>