<?php 
$link;
putenv("TZ=Europe/Moscow");
//-----------------------------> Connect on DB
include 'function_lib.php';
connect_to_db('lut_fin', 'mysql.lutrim.com', 'fin_root', 'fin_prog');



//print_r($_POST); echo " ПОСТ </br></br></br>";

//print_r(array_keys($_POST)); echo " Ключи </br></br></br>";

//	$delete_array = implode(",",array_keys($_POST));
//	$delete_array = array_keys($_POST);

//	$POST_to_delete = array("op_date" => "29 10 2012", "op_summ" => "-900", "op_comm" => "del ".$delete_array[0], "op_group" => "0");

	foreach ($_POST as $key => $value) {
		$result=mysql_query("select DATE_FORMAT(op_date,'%e %m %Y'), op_summ from main_history where n_op=".$key, $link) 
			or die(mysql_errno($link)." ".mysql_error($link));
		$op_to_delete=mysql_fetch_row($result);
//		print_r($result); echo " </br>";
		
		$POST_to_delete = array("op_date" => $op_to_delete[0], "op_summ" => ((-1)*$op_to_delete[1]), "op_comm" => "del ".$key, "op_group" => "0");
//		print_r($POST_to_delete); echo " </br>";
		add_operation($POST_to_delete);
		$result=mysql_query("UPDATE main_history set priznak=0 where n_op = ".$key,$link) or die(mysql_errno($link)." ".mysql_error($link));
	}	
mysql_close($link);
header('Location: '.$_SERVER["HTTP_REFERER"]);

?>