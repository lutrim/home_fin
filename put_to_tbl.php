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
//Если это приход средств для погашения кредита, то отправляем на добавление и проставляем флаг,
// чтоб была еще одна операция снятия. Кроме того включаем крыж, кредита, чтобы операция покрасилась
$flag_credit = 0;
if ($_POST["op_group"] === "-21") {
	$_POST["op_summ"] = abs($_POST["op_summ"]);
	$_POST["credit_check"] = "on";
	$flag_credit = 1;
}

add_operation($_POST);
//проверяем, если это приход средств для погашения кредита, то проводим еще одну операцию для снятия денег с основного остатка, 
//по статье "кредит". Отдельная статья выделена для простоты добавления выбора в процедуру "add_operation"
if ($flag_credit === 1) {
	$_POST["op_summ"] = -1 * abs($_POST["op_summ"]);
	$_POST["op_group"] = -21;
	$_POST["credit_check"] = "off";
	add_operation($_POST);
}

mysqli_close($link);
header('Location: index.php');

?>