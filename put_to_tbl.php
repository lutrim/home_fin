<?php
$link;
putenv("TZ=Europe/Moscow");
//-----------------------------> Connect on DB
include 'function_lib.php';
connect_to_db('lut_fin', 'mysql.lutrim.com', 'fin_root', 'fin_prog');

//Проверка правильности ввода

add_operation($_POST);
mysql_close($link);
header('Location: index.php');

?>