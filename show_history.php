<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>История операций.</title>


	<script type="text/javascript" src="Tools/jquery_menu/jquery.min.js"></script>
    <script type="text/javascript" src="Tools/jquery_menu/jquery.date_input.js"></script>
    <script type="text/javascript">$($.date_input.initialize);</script>
	<link rel="stylesheet" href="Tools/jquery_menu/date_input.css" type="text/css"/>	
	<link rel="stylesheet" href="style.css" type="text/css"/>	
	
</head>
<BODY>
<?php
//блок коннекта к базе данных

$db_name="lut_fin";	//база данных
$host="mysql.lutrim.com";	//хост
$user="fin_root";    //логин
$pass="fin_prog";		//password

//законнектимся - получаем link-идентификатор или вывод номера и текста ошибки
//с последующим прерыванием работы скрипта (die())
$link=mysql_connect($host,$user,$pass) or die(mysql_errno($link).mysql_error($link));
//выбираем базу данных fin, созданную нами ранее
$db=mysql_select_db($db_name,$link) or die(mysql_errno($link).mysql_error($link));
//установка региональных настроек, кодировка, часовой пояс
mysql_set_charset('utf8',$link); 
$result=mysql_query("SET time_zone='+4:00';",$link) or die(mysql_errno($link).mysql_error($link));
?>

<TABLE class="main">


<?php
//print_r($_POST); echo "</br>";
//print_r($_POST["op_group"]); echo "</br>"; die; 
?>

<TR><TD>
	<form action="show_history.php" method="post"> 
		Показать операции за указннный интервал: </br>
		от <input type="text" name="s_op_date" class="date_input" value="<?php echo $_POST['s_op_date']?>"/> 
		до <input type="text" name="f_op_date" class="date_input" value="<?php echo $_POST['f_op_date']?>"/> </br>
		фильтровать по указанным группам: </br>
			<select name="op_group[]" multiple size="6">
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
		<input type="submit" value="Показать" />
	</form>
<TR></TD>
<TR><TD>
<?php
//блок преобразования дат в удобоваримый для php & mysql
$date_tmp=explode(" ",$_POST["s_op_date"]);
$s_date_invers=$date_tmp[2];
for ($i=1;$i>=0;$i--){
	if (strlen($date_tmp[$i])==1) {$date_tmp[$i] = "0".$date_tmp[$i];};
	$s_date_invers .="-".$date_tmp[$i];
}
$date_tmp=explode(" ",$_POST["f_op_date"]);
$f_date_invers=$date_tmp[2];
for ($i=1;$i>=0;$i--){
	if (strlen($date_tmp[$i])==1) {$date_tmp[$i] = "0".$date_tmp[$i];};
	$f_date_invers .="-".$date_tmp[$i];
}
//блок преобразования дат завершен

//echo date_create($f_date_invers)->format('d-m-Y')."   <    ".date_create($s_date_invers)->format('d-m-Y')."</br>";
//блок проверок. Проверка на правильность параметров:
$data=date_create($f_date_invers);
if (!$data) {
	echo "Один из параметров введен не верно. Еще раз? </br>";
	echo "<a href=index.php> Вернуться на главную страницу</a>";
	die;
}
$data=date_create($s_date_invers);
if (!$data) {
	echo "Один из параметров введен не верно. Еще раз? </br>";
	echo "<a href=index.php> Вернуться на главную страницу</a>";
	die;
}
//проверка на правильность интервала:
if (date_create($f_date_invers) < date_create($s_date_invers)) {
	echo "Дата окончания интервала  меньше даты начала интервала, внимательнее надо быть. Введи еще раз параметры </br>";
	echo "<a href=index.php> Вернуться на главную страницу</a>";
	die;
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
	<table class="info">
	<!--заголовки -->
		<tr>
			<th class='info'> Дата </br> операции</th>
			<th class='info'> Сумма </br> операции</th>
			<th class='info'> Комментарии</th>
			<th class='info'> группа операции</th>
		</tr>
	<?php
	$result=mysql_query("select DATE_FORMAT(a.op_date,'%d-%m-%Y') as op_date,a.op_summ,a.comment,b.priznak_text 
							from main_history as a INNER JOIN dir_pr as b
								on a.priznak=b.priznak
							where (a.op_date between '".$s_date_invers."' and '".$f_date_invers."') and 
							(a.priznak in ('".implode("','",$_POST["op_group"])."')) order by a.op_date ASC",$link) or die(mysql_errno($link)." ".mysql_error($link));
	while ($oper=mysql_fetch_row($result)) {
		echo "<tr><td class='info'>".$oper[0]."</td><td class='info'>".$oper[1]."</td><td class='info'>".$oper[2]."</td><td class='info'>".$oper[3]."</td></tr>";
		}
	$result=mysql_query("select sum(op_summ) from main_history 
							where (op_date between '".$s_date_invers."' and '".$f_date_invers."') and
							(priznak in ('".implode("','",$_POST["op_group"])."'))",$link) or die(mysql_errno($link)." ".mysql_error($link));
	$total=mysql_fetch_row($result);
	echo "<tr><th class='info'>Итого</th><th class='info' colspan='3' align='left'>".$total[0]."</th>";
	?>
	</table>
</td>
<td>


<?php mysql_close($link); ?>
</TR></TD>
</TABLE>
	<a href=index.php> Вернуться на главную страницу</a>
</BODY>
</html>