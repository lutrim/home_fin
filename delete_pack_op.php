<?php 
$link;
putenv("TZ=Europe/Moscow");
$bdhost; $bdname; $bduser; $bdpass;
include 'bdpass.php';
include 'function_lib.php';
//-----------------------------> Connect on DB
connect_to_db($bdname, $bdhost, $bduser, $bdpass);



//print_r($_POST); echo " ПОСТ </br></br></br>";

//print_r(array_keys($_POST)); echo " Ключи </br></br></br>";

//	$delete_array = implode(",",array_keys($_POST));
//	$delete_array = array_keys($_POST);

//	$POST_to_delete = array("op_date" => "29 10 2012", "op_summ" => "-900", "op_comm" => "del ".$delete_array[0], "op_group" => "0");

	foreach ($_POST as $key => $value) {
		$result=mysqli_query($link,"select DATE_FORMAT(op_date,'%e %m %Y'), op_summ, comment from main_history where n_op=".$key) 
			or die(mysqli_errno($link)." : ".mysqli_error($link));
		$op_to_delete=mysqli_fetch_row($result);
//		print_r($result); echo " </br>";
		//проверяем, была ли эта операция кредитной и проставляем ей соотв флаг для удаления из соотв таблицы остатков
		$check_credit = "off";
		if (substr($op_to_delete[2],0,6) === "CrEdIt") {
			$check_credit = "on";
		};
		$POST_to_delete = array("op_date" => $op_to_delete[0], "op_summ" => ((-1)*$op_to_delete[1]), "op_comm" => "del ".$key, "op_group" => "0", "credit_check" => $check_credit);
//		print_r($POST_to_delete); echo " </br>";
		add_operation($POST_to_delete);
		$result=mysqli_query($link,"UPDATE main_history set priznak=0 where n_op = ".$key) or die(mysqli_errno($link)." : ".mysqli_error($link));
	}	
mysqli_close($link);
header('Location: '.$_SERVER["HTTP_REFERER"]);

?>