<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>История операций.</title>
	<link rel="stylesheet" href="style.css" type="text/css"/>
	<!-- Bootstrap -->
	<link href="Tools/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
	<!-- addons bootstrap-->
	<link href="Tools/datepicker/css/datepicker.css" rel="stylesheet" media="screen">
	<link href="Tools/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" media="screen">
	<script src="http://code.jquery.com/jquery-latest.js"></script>
	<script src="Tools/bootstrap/js/bootstrap.min.js"></script>	
	<script src="Tools/datepicker/js/bootstrap-datepicker.js"></script>
	<script src="Tools/bootstrap-select/bootstrap-select.min.js"></script>
<!-- добавляем оверстайл, для изменения некоторых стилей bootstrap -->    
	<style>
    	body { background: #fcf8e3; }
		.table-nonfluid { width: auto; }
		.bootstrap-select {float: left !important;}
	</style>	
</head>
<BODY>
<?php
$link;
putenv("TZ=Europe/Moscow");
$bdhost; $bdname; $bduser; $bdpass;
include 'bdpass.php';
include 'function_lib.php';
//-----------------------------> Connect on DB
connect_to_db($bdname, $bdhost, $bduser, $bdpass);
?>
<TABLE class="table table-bordered">
<?php
//print_r($_POST); echo "</br>";
//print_r($_POST["op_group"]); echo "</br>"; die; 
?>
<TR><TD>
	<form class="form-horizontal" action="show_history.php" method="post"> 
		<label>Показать операции за указннный интервал:</label>
		<div class="control-group">
			<div class="controls controls-row">
				<input class="datepicker input-small span2" type="text" name="s_op_date" value="<?php echo $_POST['s_op_date']?>"/> 
				<input class="datepicker input-small span2" type="text" name="f_op_date" value="<?php echo $_POST['f_op_date']?>"/> </br>
			</div>
		</div>
		<label>фильтровать по указанным группам:</label>
			<div class="control-group">
				<div class="controls controls-row">
					<select class="selectpicker span2" name="op_group[]" multiple data-selected-text-format="count>2" data-size="6">
						<?php  
							$result=mysql_query("SELECT * FROM dir_pr", $link) or die(mysql_errno($link).mysql_error($link));
							while($data_pr=mysql_fetch_row($result)) {
		//					echo "data_pr ->> ".$data_pr[0]." op_group ->> "; print_r($_POST["op_group"]); echo " </br>";
								if (in_array($data_pr[0],$_POST["op_group"])) {
									echo "<option selected value=".$data_pr[0].">".$data_pr[1]."</option>";}
									else {echo "<option value=".$data_pr[0].">".$data_pr[1]."</option>";};
							}
						?>	 			
					</select>
					<button class="btn span2" type="submit">Показать</button>
				</div>
			</div>
	</form>
</TD></TR>
<TR><TD>
<?php
//блок преобразования дат в удобоваримый для php & mysql
$s_date_invers=inverse_date($_POST["s_op_date"]);
$f_date_invers=inverse_date($_POST["f_op_date"]);

//блок преобразования дат завершен

//echo date_create($f_date_invers)->format('d-m-Y')."   <    ".date_create($s_date_invers)->format('d-m-Y')."</br>";
//блок проверок. Проверка на правильность параметров или на их отсутствие:
$flag_null=0; //флаг отсусттвия концов интервала(левого, правого или обоих, для формирования последующего запроса)
if ($_POST["s_op_date"] == "") { $flag_null=1;}
	else {
	$data=date_create($s_date_invers);
		if (!$data) {
			echo "Дата начала интервала введена неверно. Еще раз? </br>";
			echo "<a href=index.php> Вернуться на главную страницу</a>";
			die;
		};
	}
if ($_POST["f_op_date"] == "") { $flag_null += 2;}
	else {
	$data=date_create($f_date_invers);
		if (!$data) {
			echo "Дата конца интервала введена неверно. Еще раз? </br>";
			echo "<a href=index.php> Вернуться на главную страницу</a>";
			die;
		};
	}	
//проверка на правильность интервала:
if ($flag_null == 0) {
	if (date_create($f_date_invers) < date_create($s_date_invers)) {
		echo "Дата окончания интервала  меньше даты начала интервала, внимательнее надо быть. Введи еще раз параметры </br>";
		echo "<a href=index.php> Вернуться на главную страницу</a>";
		die;
	};
}
//блок проверок завершен
 
//$date_invers = Date_create($_POST["s_op_date"]);
//echo $date_invers->format('Y-m-d H:i:s')." create ".$s_date_invers; die;

?>

<?php 
	/*echo "select sum(op_summ) from main_history 
							where (op_date between '".$s_date_invers."' and '".$f_date_invers."') 
							group by op_summ </br>"*/
?>	
	<form action="delete_pack_op.php" method="post">
		<table class="table table-bordered table-condensed table-hover table-nonfluid">
		<!--заголовки -->
			<thead>
				<tr>
					<th> Дата </br> операции</th>
					<th> Сумма </br> операции</th>
					<th> Комментарии</th>
					<th> группа операции</th>
					<th> </th>
				</tr>
			</thead>
			<tbody>
		<?php
		switch ($flag_null) {
			case 0:
				$result=mysql_query("select DATE_FORMAT(a.op_date,'%d-%m-%Y') as op_date,a.op_summ,a.comment,b.priznak_text,a.n_op 
										from main_history as a INNER JOIN dir_pr as b
											on a.priznak=b.priznak
										where (a.op_date between '".$s_date_invers."' and '".$f_date_invers."') and 
										(a.priznak in ('".implode("','",$_POST["op_group"])."')) order by a.op_date ASC",$link) 
										or die(mysql_errno($link)." ".mysql_error($link));
				break;
			case 1:
				$result=mysql_query("select DATE_FORMAT(a.op_date,'%d-%m-%Y') as op_date,a.op_summ,a.comment,b.priznak_text,a.n_op 
										from main_history as a INNER JOIN dir_pr as b
											on a.priznak=b.priznak
										where (a.op_date <= '".$f_date_invers."') and 
										(a.priznak in ('".implode("','",$_POST["op_group"])."')) order by a.op_date ASC",$link) 
										or die(mysql_errno($link)." ".mysql_error($link));
				break;
			case 2:
				$result=mysql_query("select DATE_FORMAT(a.op_date,'%d-%m-%Y') as op_date,a.op_summ,a.comment,b.priznak_text,a.n_op 
										from main_history as a INNER JOIN dir_pr as b
											on a.priznak=b.priznak
										where (a.op_date >= '".$s_date_invers."') and 
										(a.priznak in ('".implode("','",$_POST["op_group"])."')) order by a.op_date ASC",$link) 
										or die(mysql_errno($link)." ".mysql_error($link));
				break;
			case 3:
				$result=mysql_query("select DATE_FORMAT(a.op_date,'%d-%m-%Y') as op_date,a.op_summ,a.comment,b.priznak_text,a.n_op 
										from main_history as a INNER JOIN dir_pr as b
											on a.priznak=b.priznak
										where (a.priznak in ('".implode("','",$_POST["op_group"])."')) order by a.op_date ASC",$link) 
										or die(mysql_errno($link)." ".mysql_error($link));
				break;
		}
		$total=0.0;
		while ($oper=mysql_fetch_row($result)) {
			echo "<tr class='info'><td>".$oper[0]."</td><td>".$oper[1]."</td><td>".$oper[2]."</td><td>".$oper[3]."</td>
					<td><label class='checkbox'><input type='checkbox' name='".$oper[4]."'/></label></td></tr>";
			$total += $oper[1];
			}
		/*$result=mysql_query("select sum(op_summ) from main_history 
								where (op_date between '".$s_date_invers."' and '".$f_date_invers."') and
								(priznak in ('".implode("','",$_POST["op_group"])."'))",$link) or die(mysql_errno($link)." ".mysql_error($link));
		$total=mysql_fetch_row($result);*/
		echo "<tfoot><tr class='info'><th>Итого</th><th colspan='4' align='left'>".$total."</th></tfoot>";
		?>
			</tbody>
		</table>
		<!-- <input type="submit" value="удалить отмеченные"/> -->
	</form>


<?php mysql_close($link); ?>
</TD></TR>

<script> 
$('.datepicker').datepicker({
    format: 'd mm yyyy'
});
$('.selectpicker').selectpicker();
</script>

</TABLE>
	<a class="btn" href=index.php> Вернуться на главную страницу</a></br></br></br>
</BODY>
</html>