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
//Если это приход средств для погашения кредита, то изменяем пост так, чтобы он не отображался в истории
if ($_POST["credit_check"] === "on" and $_POST["op_group"] === "2") {
	$_POST["op_summ"] = abs($_POST["op_summ"]);
	$_POST["op_group"] = 0;
}

add_operation($_POST);
//проверяем, если это приход средств для погашения кредита, то проводим еще одну операцию для снятия денег с основного остатка
if ($_POST["credit_check"] === "on" and $_POST["op_group"] === "2") {
	$_POST["op_summ"] = -1 * abs($_POST["op_summ"]);
	$_POST["op_group"] = 0;
	$_POST["credit_check"] = "off";
	add_operation($_POST);
}

mysqli_close($link);
header('Location: index.php');

?>